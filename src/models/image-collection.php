<?php

namespace NextCellent\Models;

use NextCellent\Database\Manager;

/**
 * @author  Niko Strijbol
 * @version 22/06/2016
 */
class Image_Collection extends Lazy_Images {

	/**
	 * Get a collection that provides random images.
	 *
	 * @param int        $limit
	 * @param null|array $galleries When provided, the random images will be taken from the given galleries only.
	 *
	 * @return Lazy_Images The images.
	 */
	public static function random($limit, $galleries = null) {
		$order = 'rand()';
		return self::common($limit, $galleries, $order);
	}

	/**
	 * Get a collection that provides random images.
	 *
	 * @param int        $limit
	 * @param null|array $galleries When provided, the random images will be taken from the given galleries only.
	 *
	 * @return Lazy_Images The images.
	 */
	public static function recent($limit, $galleries = null) {

		$i = Manager::get()->get_image_table();
		$order = $i . '.' . Image::ID . ' DESC';

		return self::common($limit, $galleries, $order);
	}
	
	/*
	 * The common functions of recent and random.
	 */
	private static function common($limit, $galleries, $order) {
		$i = Manager::get()->get_image_table();
		$g = Manager::get()->get_gallery_table();

		$p = $g . '.' . Gallery::PATH; //Gallery path field.
		$joiner = $i . '.' . Image::GALLERY_ID . '=' . $g . '.' . Gallery::ID;

		if($galleries === null) {
			$where = '';
		} else {
			array_walk($galleries, 'intval');
			$where = 'WHERE ' . $i . '.' . Image::GALLERY_ID . ' IN (' . implode(',', $galleries) . ')';
		}

		$query = "SELECT $i.*, $p FROM $i INNER JOIN $g ON $joiner $where ORDER BY $order";

		return new Lazy_Images($query, (int) $limit);
	}

	/**
	 * Get a list of images for a given list of ids.
	 * 
	 * @param array $ids Ids of the images you want. Assumes existing images, or the count will be wrong.
	 *                   
	 * @return Lazy_Images The images.
	 */
	public static function inList(array $ids) {
		$i = Manager::get()->get_image_table();
		$g = Manager::get()->get_gallery_table();

		$p = $g . '.' . Gallery::PATH; //Gallery path field.
		$joiner = $i . '.' . Image::GALLERY_ID . '=' . $g . '.' . Gallery::ID;
		$where = 'WHERE ' . $i . '.' . Image::ID . ' IN (' . implode(',', $ids) . ')';

		$query = "SELECT $i.*, $p FROM $i INNER JOIN $g ON $joiner $where";

		return new Lazy_Images($query, count($ids));
	}

	/**
	 * @param Gallery $gallery  The gallery or a gallery ID.
	 * @param string  $sort     Column to sort on.
	 * @param string  $sort_dir Sort direction.
	 * @param bool    $exclude
	 *
	 * @return Lazy_Images The images.
	 */
	public static function gallery($gallery, $sort = Image::SORT_ORDER, $sort_dir = 'ASC', $exclude = false) {

		//Possible sort orders
		$sort_orders = [
			Image::SORT_ORDER,
			Image::ID,
			Image::FILENAME,
			Image::ALT_TEXT,
			Image::DATE
		];

		if(!in_array($sort, $sort_orders, true)) {
			$sort = Image::SORT_ORDER;
		}

		//Sort direction
		$order_dir = ( $sort_dir === 'DESC') ? 'DESC' : 'ASC';

		//Exclude stuff
		$exclude_sql = $exclude ? ' AND ' . Image::EXCLUDE . ' == 1' : '';

		$g_id = Image::GALLERY_ID;
		$i_id = Image::ID;
		$i = Manager::get()->get_image_table();

		$query  = "SELECT * FROM $i WHERE $g_id = %d $exclude_sql ORDER BY $sort $order_dir";
		$values = [$gallery->id];

		$counter = "SELECT count($i_id) FROM $i WHERE $g_id = %d";
		$counterValues = [$gallery->id];
		
		return new Lazy_Images($query, $counter, $values, $counterValues, function($results) use($gallery) {
			$converted = [];
			foreach ( $results as $id ) {
				$id[Gallery::PATH] = $gallery->path;
				/** @noinspection PhpInternalEntityUsedInspection */
				$converted[$id[Image::ID]] = Image::to_image($id);
			}
			return $converted;
		});
	}
}