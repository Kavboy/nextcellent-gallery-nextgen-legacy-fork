<?php

namespace NextCellent\Models;

use NextCellent\Database\Manager;

/**
 * @author  Niko Strijbol
 * @version 24/02/2016
 */
class Gallery extends Model {

	/**
	 * Define the database tables.
	 */
	const ID = 'gid';
	const NAME = 'name';
	const SLUG = 'slug';
	const PATH = 'path';
	const TITLE = 'title';
	const DESCRIPTION = 'galdesc';
	const PAGE_ID = 'pageid';
	const PREVIEW = 'previewpic';
	const AUTHOR = 'author';

	/**
	 * Count all galleries.
	 *
	 * @return int The number of galleries.
	 */
	public static function count() {
		return parent::count(Manager::get()->get_gallery_table());
	}

	/**
	 * Convert a row of results from the database to an Gallery class.
	 *
	 * @param array $data Associative array of data.
	 *
	 * @return Gallery The image instance.
	 */
	private static function to_gallery( $data ) {
		$image = new Gallery();
		$image->set_properties( array(
			'id'          => $data[ self::ID ],
			'name'        => $data[ self::NAME ],
			'slug'  => $data[ self::SLUG ],
			'path'    => $data[ self::PATH ],
			'title' => $data[ self::TITLE ],
			'description'        => $data[ self::DESCRIPTION ],
			'page_id'     => (bool) $data[ self::PAGE_ID ],
			'preview'  => (bool) $data[ self::PREVIEW ],
			'author'   => unserialize( $data[ self::AUTHOR ] )
		) );

		return $image;
	}

	/**
	 * Convert to model to an array of data.
	 *
	 * @return array The data.
	 */
	protected function to_array() {
		// TODO: Implement to_array() method.
	}

	public function delete() {
		// TODO: Implement delete() method.
	}
}