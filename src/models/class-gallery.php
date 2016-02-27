<?php

namespace NextCellent\Models;

use NextCellent\Database\Database_Exception;
use NextCellent\Database\Manager;
use NextCellent\Database\Not_Found_Exception;

/**
 * @property int $id The id.
 * @property string $name The name.
 * @property string $slug The slug of the gallery.
 * @property string $path The path of the gallery.
 * @property string $title The title of the gallery.
 * @property string $description The description.
 * @property int $page_id The page associated with this gallery.
 * @property int $preview The preview image of the gallery.
 * @property int $author The ID of the gallery author.
 * @property-read Image[] $images The images in this gallery.
 * @property-read Image $preview_image The preview image.
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
	 * @var array $image_map Associative array of the ID -> Image Object.
	 */
	private $image_map = array();

	/**
	 * Count all galleries.
	 *
	 * @return int The number of galleries.
	 */
	public static function count() {
		return parent::count_table(Manager::get()->get_gallery_table());
	}

	/**
	 * Get a gallery from the database, or null if it does not exist.
	 *
	 * @param int $id The ID of the gallery.
	 * @param bool $images Whether to load the images or not.
	 *
	 * @return Gallery|null The image or null.
	 */
	public static function find_or_null($id, $images = false) {

		$manager = Manager::get();

		$result = $manager->get_row(
			'SELECT * FROM ' . $manager->get_gallery_table() . ' WHERE ' . self::ID . ' = %d',
			$id
		);

		if($result === null) {
			return null;
		} else {
			$gallery = Gallery::to_gallery($result);

			if($images) {
				$gallery->load_images();
			}

			return $gallery;
		}
	}

	/**
	 * Get a gallery from the database.
	 *
	 * @param int $id         The ID of the gallery.
	 * @param bool $images Whether to load the images or not.
	 *
	 * @return Gallery The image or null.
	 * @throws Not_Found_Exception If the gallery does not exist.
	 */
	public static function find($id, $images = false) {
		$result = Gallery::find_or_null($id, $images);

		if($result === null) {
			throw new Not_Found_Exception(__('Gallery', 'nggallery'), $id);
		}

		return $result;
	}

	/**
	 * Count all images in this gallery.
	 *
	 * @return int The number of images, inclusive the excluded ones.
	 */
	public function count_images() {
		$manager = Manager::get();

		return $manager->get_int(
			'SELECT COUNT(*) FROM ' . $manager->get_image_table() . ' WHERE ' . Image::GALLERY_ID . ' = %d',
			array($this->id)
		);
	}

	/**
	 * Get an URL to an image.
	 *
	 * @param Image $image The image.
	 *
	 * @return string The URL.
	 */
	public function image_url($image) {
		return site_url() . '/' . $this->path . '/' . $image->filename;
	}

	/**
	 * Get the path to an image.
	 *
	 * @param Image $image
	 *
	 * @return string The path.
	 */
	public function image_path($image) {
		return WINABSPATH . $this->path . '/' . $image->filename;
	}

	/**
	 * Get an URL to an image.
	 *
	 * @param Image $image The image.
	 *
	 * @return string The URL.
	 */
	public function thumbnail_url($image) {
		return site_url() . '/' . $this->path . '/thumbs/thumbs_' . $image->filename;
	}

	/**
	 * Load the images of this gallery from the database, with the given constraints.
	 *
	 * @param string $sort     The column to sort on.
	 * @param string $sort_dir The sorting direction.
	 * @param int $start       The start position. If the number of images is 0, this is ignored.
	 * @param int $per_page    The number of images.
	 * @param bool $exclude Whether to include the hidden images or not.
	 */
	public function load_images($sort = Image::SORT_ORDER, $sort_dir = 'ASC', $start = 0, $per_page = 0, $exclude = false ) {

		$sort_orders = array(
			Image::SORT_ORDER,
			Image::ID,
			Image::FILENAME,
			Image::ALT_TEXT,
			Image::DATE
		);

		if(!in_array($sort, $sort_orders)) {
			$sort = Image::SORT_ORDER;
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
			$exclude_sql = ' AND ' . Image::EXCLUDE . ' == 1';
		} else {
			$exclude_sql = '';
		}

		//var_dump('SELECT * FROM ' . $this->manager->get_image_table() . ' WHERE ' . Image::GALLERY_ID . ' = %d' . $exclude_sql . $order_by . $limit);

		$manager = Manager::get();
		$ids = $manager->get_results(
			'SELECT * FROM ' . $manager->get_image_table() . ' WHERE ' . Image::GALLERY_ID . ' = %d' . $exclude_sql . $order_by . $limit,
			$this->id
		);

		foreach ( $ids as $id ) {
			/** @noinspection PhpInternalEntityUsedInspection */
			$this->image_map[$id[Image::ID]] = Image::to_image($id);
		}
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
			'id'          => (int) $data[ self::ID ],
			'name'        => $data[ self::NAME ],
			'slug'  => $data[ self::SLUG ],
			'path'    => $data[ self::PATH ],
			'title' => $data[ self::TITLE ],
			'description'        => $data[ self::DESCRIPTION ],
			'page_id'     => (bool) $data[ self::PAGE_ID ],
			'preview'  => (bool) $data[ self::PREVIEW ],
			'author'   => (int) $data[ self::AUTHOR ]
		) );

		return $image;
	}

	protected function get_images() {
		if(empty($this->image_map)) {
			$this->load_images();
		}

		return $this->image_map;
	}

	protected function get_preview_image() {
		if(empty($this->image_map)) {
			return Image::find($this->preview);
		} else {
			return $this->image_map[$this->preview];
		}
	}

	/**
	 * Convert to model to an array of data.
	 *
	 * @return array The data.
	 */
	protected function to_array() {
		return array(
			self::ID          => $this->properties['id'],
			self::NAME        => $this->properties['name'],
			self::SLUG        => $this->properties['slug'],
			self::PATH        => $this->properties['path'],
			self::TITLE       => $this->properties['title'],
			self::DESCRIPTION => $this->properties['description'],
			self::PAGE_ID     => $this->properties['page_id'],
			self::PREVIEW     => $this->properties['preview'],
			self::AUTHOR      => $this->properties['author']
		);
	}

	public function delete() {
		$manager = Manager::get();
		$result = $manager->delete($manager->get_gallery_table(), self::ID, $this->id);

		if($result > 1 ) {
			throw new Database_Exception(__('More than one gallery was deleted!', 'nggallery'));
		}

		if($result < 1) {
			throw new Not_Found_Exception(__('Cannot delete non-existing image.', 'nggallery'));
		}
	}
}