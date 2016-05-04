<?php

namespace NextCellent\Models;

use NextCellent\Database\Database_Exception;
use NextCellent\Database\Manager;
use NextCellent\Database\Not_Found_Exception;

/**
 * The NextCellent image model.
 *
 * @property int $id The id of the image.
 * @property int $gallery_id The id of the gallery this image is in.
 * @property string $filename The filename.
 * @property string $description The description.
 * @property string $alt_text The alt/title text.
 * @property string $date The date when this image was taken. In MySQL format.
 * @property bool $exclude If the image should be excluded or not.
 * @property string $slug The slug.
 * @property int $sort_order The order of the image in the gallery.
 * @property array $meta_data The metadata saved in the database.
 *
 * These properties are read-only, but some are marked as normal due to a bug in phpStorm.
 * @property-read string $tags The image tags.
 * @property string $url The URL.
 * @property string $path The path.
 * @property string $thumb_url URL to the thumbnail
 * @property string $thumb_path Path to the thumbnail
 */
class Image extends Abstract_Model {

	/**
	 * Define the database column names.
	 */
	const ID = 'pid';
	const SLUG = 'image_slug';
	const POST_ID = 'post_id';
	const GALLERY_ID = 'galleryid';
	const FILENAME = 'filename';
	const DESCRIPTION = 'description';
	const ALT_TEXT = 'alttext';
	const DATE = 'imagedate';
	const EXCLUDE = 'exclude';
	const SORT_ORDER = 'sortorder';
	const META_DATA = 'meta_data';

	/**
	 * @var string $path The folder in which this image is present.
	 * @var string $url The URL to this image.
	 * @var string $thumb_url The URL to the thumbnail of this image.
	 * @var string $thumb_path The path to the thumbnail of this image.
	 */
	private $path;
	private $url;
	private $thumb_url;
	private $thumb_path;

	/**
	 * Count all images.
	 *
	 * @return int The number of images.
	 */
	public static function count() {
		return parent::count_table(Manager::get()->get_image_table());
	}

	/**
	 * Get an image from the database. This will throw an exception
	 * if the image is not found.
	 *
	 * @param int $id The ID of the image.
	 *
	 * @return Image The image instance.
	 * @throws Not_Found_Exception If the image could not be found.
	 */
	public static function find( $id ) {
		$result = Image::find_or_null($id);

		if($result === null) {
			throw new Not_Found_Exception(__('Image', 'nggallery'), $id);
		}

		return $result;
	}

	/**
	 * Get an image from the database, or null if it does not exist.
	 *
	 * @param int $id
	 *
	 * @return Image|null The image or null.
	 */
	public static function find_or_null($id) {

		$manager = Manager::get();

		$result = $manager->get_row(
			'SELECT * FROM ' . $manager->get_image_table() . ' INNER JOIN ' . $manager->get_gallery_table() . ' ON ' . Image::GALLERY_ID . ' = ' . Gallery::ID . ' WHERE ' . self::ID . ' = %d',
			$id
		);

		if($result === null) {
			return null;
		} else {
			return Image::to_image($result);
		}
	}

	/**
	 * Get all images.
	 *
	 * @param string $sort
	 * @param string $sort_dir
	 * @param int    $start
	 * @param int    $per_page
	 * @param bool   $exclude
	 *
	 * @return array
	 */
	public static function all($sort = self::ID, $sort_dir = 'ASC', $start = 0, $per_page = 0, $exclude = true ) {

		$sort_orders = array(
			self::ID,
			self::DATE,
			self::GALLERY_ID,
		);

		if(!in_array($sort, $sort_orders)) {
			$sort = self::ID;
		}

		$order_dir = ( $sort_dir === 'DESC') ? 'DESC' : 'ASC';

		$order_by = " ORDER BY {$sort} {$order_dir}";

		$start = absint($start);

		if($per_page > 0) {
			$limit = " LIMIT {$start},{$per_page}";
		} else {
			$limit = '';
		}
		
		if($exclude) {
			$excluding = ' WHERE ' . self::EXCLUDE . ' = 0';
		} else {
			$excluding = '';
		}

		$manager = Manager::get();

		$ids = $manager->get_results( 'SELECT * FROM ' . $manager->get_image_table() . $excluding . $order_by . $limit);

		$images = array();

		foreach ( $ids as $id ) {
			$images[$id[self::ID]] = self::to_image($id);
		}

		return $images;
	}

	/**
	 * Convert a row of results from the database to an Image class.
	 *
	 * @param array $data Associative array of data.
	 *
	 * @internal This function is for internal use only.
	 *
	 * @return Image The image instance.
	 */
	public static function to_image( $data ) {
		$image = new Image();
		$image->set_properties( array(
			'id'          => (int) $data[ self::ID ],
			'slug'        => $data[ self::SLUG ],
			'gallery_id'  => (int) $data[ self::GALLERY_ID ],
			'filename'    => $data[ self::FILENAME ],
			'description' => $data[ self::DESCRIPTION ],
			'alt_text'    => $data[ self::ALT_TEXT ],
			'date'        => $data[ self::DATE ],
			'exclude'     => (bool) $data[ self::EXCLUDE ],
			'sort_order'  => (bool) $data[ self::EXCLUDE ],
			'meta_data'   => unserialize( $data[ self::META_DATA ] )
		) );

		$gallery_path = $data[Gallery::PATH];


		$image->path = WINABSPATH . $gallery_path . '/' . $image->filename;
		$image->url = site_url() . '/' . $gallery_path . '/' . $image->filename;
		$image->thumb_url = site_url() . '/' . $gallery_path . '/thumbs/thumbs_' . $image->filename;
		$image->thumb_path = WINABSPATH . $gallery_path . '/thumbs/thumbs_' . $image->filename;

		return $image;
	}

	/**
	 * Convert this class to an array for saving the data.
	 *
	 * @return array The associative array.
	 */
	protected function to_array() {
		return array(
			self::ID          => $this->properties['id'],
			self::SLUG        => $this->properties['slug'],
			self::GALLERY_ID  => $this->properties['gallery_id'],
			self::FILENAME    => $this->properties['filename'],
			self::DESCRIPTION => $this->properties['description'],
			self::ALT_TEXT    => $this->properties['alt_text'],
			self::DATE        => $this->properties['date'],
			self::EXCLUDE     => $this->properties['exclude'],
			self::SORT_ORDER  => $this->properties['sort_order'],
			self::META_DATA   => serialize( $this->properties['meta_data'] )
		);
	}

	/**
	 * Delete an image from the database.
	 *
	 * @throws Database_Exception If something went wrong or more than one image was deleted.
	 * @throws Not_Found_Exception If the image does not exist.
	 */
	public function delete() {

		$manager = Manager::get();

		$result = $manager->delete($manager->get_image_table(), self::ID, $this->id);

		if($result > 1 ) {
			throw new Database_Exception(__('More than one image was deleted!', 'nggallery'));
		}

		if($result < 1) {
			throw new Not_Found_Exception(__('Cannot delete non-existing image.', 'nggallery'));
		}
	}

	public function save() {
		return parent::save_model( Manager::get()->get_image_table(), self::ID, $this->id );
	}

	public function __toString() {
		return "NextCellent image #$this->id, $this->filename";
	}

	protected function get_tags() {
		return wp_get_object_terms($this->id, 'ngg_tag', 'fields=all');
	}

	protected function get_path() {
		return $this->path;
	}

	protected function get_url() {
		return $this->url;
	}

	protected function get_thumb_url() {
		return $this->thumb_url;
	}

	protected function get_thumb_path() {
		return $this->thumb_path;
	}
}