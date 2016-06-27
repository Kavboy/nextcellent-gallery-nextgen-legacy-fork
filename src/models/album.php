<?php

namespace NextCellent\Models;

use NextCellent\Database\Database_Exception;
use NextCellent\Database\Manager;
use NextCellent\Database\Not_Found_Exception;

/**
 * @property int $id The id of the album.
 * @property string $name The name of the album.
 * @property string $slug The slug of the album.
 * @property int $preview_image The ID of the preview image.
 * @property string $description The description of the album.
 * @property array $contents The content of the album.
 * @property int $page_id The page ID of the album.
 */
class Album {
	
	use Savable_Model;

	const ID = 'id';
	const NAME = 'name';
	const SLUG = 'slug';
	const PREVIEW_IMAGE = 'previewpic';
	const DESCRIPTION = 'albumdesc';
	const CONTENTS = 'sortorder';
	const PAGE_ID = 'pageid';
	
	/**
	 * Count all images.
	 *
	 * @return int The number of images.
	 */
	public static function count() {
		return self::count_table(Manager::get()->get_album_table());
	}

	/**
	 * Get an album from the database. This will throw an exception
	 * if the album is not found.
	 *
	 * @param int $id The ID of the image.
	 *
	 * @return Album The image instance.
	 * @throws Not_Found_Exception If the album could not be found.
	 */
	public static function find( $id ) {
		$result = Album::find_or_null($id);

		if($result === null) {
			throw new Not_Found_Exception(__('Album', 'nggallery'), $id);
		}

		return $result;
	}

	/**
	 * Get an album from the database, or null if it does not exist.
	 *
	 * @param int $id
	 *
	 * @return Album|null The album or null.
	 */
	public static function find_or_null($id) {

		$manager = Manager::get();

		$result = $manager->get_row(
			'SELECT * FROM ' . $manager->get_album_table() . ' WHERE ' . self::ID . ' = %d',
			$id
		);

		if($result === null) {
			return null;
		} else {
			return Album::to_album($result);
		}
	}

	/**
	 * Convert a row of results from the database to an Album class.
	 *
	 * @param array $data Associative array of data.
	 *
	 * @internal This function is for internal use only.
	 *
	 * @return Album The album instance.
	 */
	protected static function to_album( $data ) {
		$album = new Album();
		$album->set_properties( array(
			'id'            => (int) $data[ self::ID ],
			'name'          => $data[ self::NAME ],
			'slug'          => $data[ self::SLUG ],
			'preview_image' => $data[ self::PREVIEW_IMAGE ],
			'description'   => $data[ self::DESCRIPTION ],
			'contents'      => unserialize( $data[ self::CONTENTS ] ),
			'page_id'       => $data[ self::PAGE_ID ]
		) );

		return $album;
	}

	/**
	 * Convert to model to an array of data.
	 *
	 * @return array The data.
	 */
	protected function to_array() {
		return array(
			self::ID            => $this->properties['id'],
			self::NAME          => $this->properties['name'],
			self::SLUG          => $this->properties['slug'],
			self::PREVIEW_IMAGE => $this->properties['preview_image'],
			self::DESCRIPTION   => $this->properties['description'],
			self::CONTENTS      => serialize( $this->properties['contents'] ),
			self::PAGE_ID       => $this->properties['page_id']
		);
	}

	public function delete() {
		$manager = Manager::get();

		$result = $manager->delete($manager->get_album_table(), self::ID, $this->id);

		if($result > 1 ) {
			throw new Database_Exception(__('More than one album was deleted!', 'nggallery'));
		}

		if($result < 1) {
			throw new Not_Found_Exception(__('Cannot delete non-existing album.', 'nggallery'));
		}
	}

	public function save() {
		return self::save_model( Manager::get()->get_album_table(), self::ID, $this->id );
	}

	public function __toString() {
		return "NextCellent album #$this->id, $this->name";
	}

	/**
	 * Get all albums.
	 *
	 * @param string $sort
	 * @param string $sort_dir
	 * @param int $start
	 * @param int $per_page
	 *
	 * @return array
	 */
	public static function all($sort = self::ID, $sort_dir = 'ASC', $start = 0, $per_page = 0) {

		$sort_orders = array(
			self::ID,
			self::NAME
		);

		if(!in_array($sort, $sort_orders, true)) {
			$sort = self::ID;
		}

		$order_dir = ( $sort_dir === 'DESC') ? 'DESC' : 'ASC';

		$order_by = " ORDER BY {$sort} {$order_dir}";

		$start = absint($start);

		$limit = $per_page > 0 ? " LIMIT {$start},{$per_page}" : '';

		$manager = Manager::get();

		$ids = $manager->get_results( 'SELECT * FROM ' . $manager->get_album_table() . $order_by . $limit);

		$albums = [];

		foreach ( $ids as $id ) {
			$albums[$id[self::ID]] = self::to_album($id);
		}

		return $albums;
	}
}