<?php

namespace NextCellent\Models;

use NextCellent\Database\Manager;

/**
 * @author  Niko Strijbol
 * @version 22/06/2016
 */
class Image_Collection implements Displayable_Images {

	/**
	 * @var array $image_map Associative array of the ID -> Image Object.
	 */
	private $image_map = [];

	/**
	 * Load the images of this gallery from the database, with the given constraints.
	 *
	 * @param array  $where
	 * @param string $order
	 * @param int    $start    The start position. If the number of images is 0, this is ignored.
	 * @param int    $per_page The number of images.
	 *
	 * @return Image_Collection
	 */
	public static function load_images($where = [], $order = '', $start = 0, $per_page = 0 ) {

		$images = new Image_Collection();
		
		$order_by = " ORDER BY $order";

		$start = absint($start);

		if($per_page > 0) {
			$limit = " LIMIT {$start},{$per_page}";
		} else {
			$limit = '';
		}
		
		//Prepare where
		if(empty( $where )) {
			$clauses = '';
			$info = [];
		} else {
			$clauses = 'WHERE ' . $where[0];
			$info = $where[1];
		}

		$manager = Manager::get();
		$i = $manager->get_image_table();
		$g = $manager->get_gallery_table();
		$p = $g . '.' . Gallery::PATH;

		$i_i = Image::GALLERY_ID;
		$g_i = Gallery::ID;

		$ids = $manager->get_results(
			"SELECT $i.*, $p FROM $i INNER JOIN $g ON $i_i = $g_i $clauses $order_by $limit",
			$info
		);

		var_dump( $ids );

		foreach ( $ids as $id ) {
			/** @noinspection PhpInternalEntityUsedInspection */
			$images->image_map[$id[Image::ID]] = Image::to_image($id);
		}

		return $images;
	}
	
	public static function get_random($limit, $gallery_id = null) {
		
		if($gallery_id === null) {
			$where = [];
		} else {
			$where = [Image::GALLERY_ID . ' = %d', [$gallery_id]];
		}
		
		return self::load_images($where, 'rand()', 0, $limit);
	}

	/**
	 * Get the images to display.
	 *
	 * @return Image[]
	 */
	public function get_images() {
		return $this->image_map;
	}
}