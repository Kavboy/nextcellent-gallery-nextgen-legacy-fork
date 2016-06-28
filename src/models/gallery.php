<?php

namespace NextCellent\Models;

use NextCellent\Admin\Roles;
use NextCellent\Database\Database_Exception;
use NextCellent\Database\Manager;
use NextCellent\Database\Not_Found_Exception;

/**
 * A gallery. To find images in the gallery, you need to do the following:
 * 
 * 1. Call {@link #load_images()} to prepare the gallery.
 * 2. Call getImages().
 * 
 * Once you have loaded the images, you can call get images an unlimited amount of times.
 * 
 * If you need the images directly, there is a helper method called retrieveImages(), that does all this in one go.
 * 
 * 
 * @property int $id The id.
 * @property string $name The name.
 * @property string $slug The slug of the gallery.
 * @property string $path The path of the gallery.
 * @property string       $title The title of the gallery.
 * @property string       $description The description.
 * @property int          $page_id The page associated with this gallery.
 * @property int          $preview The preview image of the gallery.
 * @property int          $author The ID of the gallery author.
 * @property string       $abs_path The absolute path to this gallery.
 * @property string       $abs_thumb_path The absolute path to the thumbnail folder.
 * @property-read Image[] $images The images in this gallery.
 * @property-read Image   $preview_image The preview image.
 */
class Gallery implements Images {

	use Savable_Model;

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
	
	const THUMBNAIL_FOLDER = 'thumb';

	/**
	 * @var null|Images
	 */
	private $imageCollection;

	private $absolutePath;

	/**
	 * Load the images of this gallery from the database, with the given constraints.
	 *
	 * @param string $sort     The column to sort on.
	 * @param string $sort_dir The sorting direction.
	 * @param bool   $exclude  Whether to include the hidden images or not.
	 */
	public function load_images($sort = Image::SORT_ORDER, $sort_dir = 'ASC', $exclude = false ) {
		$this->imageCollection = Image_Collection::gallery($this, $sort, $sort_dir, $exclude);
	}

	/**
	 * Count all galleries.
	 *
	 * @return int The number of galleries.
	 */
	public static function count() {
		return self::count_table(Manager::get()->get_gallery_table());
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
	 * @return int The total number of images.
	 */
	public function total() {

		if($this->imageCollection === null ) {
			return Image_Collection::gallery($this)->total();
		}
		
		return $this->imageCollection->total();
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
	 * Get some portion of the images. The first image is at index 0.
	 *
	 * THe obvious use for this is pagination.
	 *
	 * @param int $start  At which image to start. Must be non-negative.
	 * @param int $number How many images are needed. If not set, all images are returned. Must be non-negative.
	 *
	 * @return Image[] The requested images. If there are no images, an empty array is returned.
	 * @throws \LogicException
	 */
	public function getImages($start = 0, $number = PHP_INT_MAX) {
		
		if($this->imageCollection === null ) {
			throw new \LogicException('You must first call load_images().');
		}
		
		return $this->imageCollection->getImages($start, $number);
	}

	/**
	 * Get the images of this gallery from the database, with the given constraints.
	 *
	 * @param int    $start
	 * @param int    $perPage
	 * @param string $sort     The column to sort on.
	 * @param string $sort_dir The sorting direction.
	 * @param bool   $exclude  Whether to include the hidden images or not.
	 *
	 * @return Image[]
	 */
	public function retrieveImages($start = 0, $perPage = PHP_INT_MAX, $sort = Image::SORT_ORDER, $sort_dir = 'ASC', $exclude = false ) {
		$this->imageCollection = Image_Collection::gallery($this, $sort, $sort_dir, $exclude);
		return $this->getImages($start, $perPage);
	}

	/**
	 * Get all galleries.
	 *
	 * @param string $sort
	 * @param string $sort_dir
	 * @param int $start
	 * @param int $per_page
	 * @param bool $count_images
	 *
	 * @return Gallery[]
	 */
	public static function all($sort = self::ID, $sort_dir = 'ASC', $start = 0, $per_page = 0, $count_images = false ) {

		$sort_orders = array(
			self::ID,
			self::TITLE,
			self::AUTHOR,
		);

		if(!in_array($sort, $sort_orders, true)) {
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

		$manager = Manager::get();

		$ids = $manager->get_results( 'SELECT * FROM ' . $manager->get_gallery_table() . $order_by . $limit);

		$galleries = array();
		$gallery_ids = array();

		foreach ( $ids as $id ) {
			$galleries[$id[self::ID]] = self::to_gallery($id);
			$gallery_ids[] = $id[self::ID];
		}

		if($count_images) {
			$id_string = implode(',', $gallery_ids);
			$results = $manager->get_results(
				'SELECT COUNT(*) FROM ' . $manager->get_image_table() . ' WHERE '. Image::GALLERY_ID . ' IN (' . $id_string . ') GROUP BY ' . Image::GALLERY_ID
			);

			$counter = 0;

			foreach($gallery_ids as $gallery_id) {
				$galleries[$gallery_id]->image_count = (int)$results[$counter]['COUNT(*)'];
				$counter++;
			}
		}

		return $galleries;
	}

	/**
	 * Convert a row of results from the database to an Gallery class.
	 *
	 * @param array $data Associative array of data.
	 *
	 * @return Gallery The image instance.
	 */
	private static function to_gallery( $data ) {
		$gallery = new Gallery();
		$gallery->set_properties(array(
			'id'          => (int) $data[ self::ID ],
			'name'        => $data[ self::NAME ],
			'slug'        => $data[ self::SLUG ],
			'path'        => trailingslashit($data[ self::PATH ]),
			'title'       => $data[ self::TITLE ],
			'description' => $data[ self::DESCRIPTION ],
			'page_id'     => (int) $data[ self::PAGE_ID ],
			'preview'     => (bool) $data[ self::PREVIEW ],
			'author'      => (int) $data[ self::AUTHOR ]
		));

		$gallery->absolutePath = trailingslashit(NCG_ABSPATH . $gallery->path);

		return $gallery;
	}

	protected function get_preview_image() {
		if(empty($this->imageMap)) {
			return Image::find($this->preview);
		} else {
			return $this->imageMap[$this->preview];
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

	/**
	 * @throws Database_Exception
	 * @throws Not_Found_Exception
	 */
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

	public function save() {
		return $this->save_model( Manager::get()->get_gallery_table(), self::ID, $this->id );
	}

	public function can_manage($user = null) {
		
		if($user === null) {
			$user = get_current_user_id();
		}
		
		return user_can($user, Roles::MANAGE_ALL_GALLERIES) || (user_can($user, Roles::MANAGE_GALLERIES) && $this->author === get_current_user_id());
	}
	
	public function get_images() {
		return $this->imageCollection;
	}

	public function get_abs_path() {
		return $this->absolutePath;
	}

	public function get_abs_thumb_path() {
		return trailingslashit($this->absolutePath . self::THUMBNAIL_FOLDER);
	}

	/**
	 * Get the absolute path to an image. This is useful when you have the filename, but the image is not yet
	 * in the gallery. If it is in the gallery, it is recommended to use the {@link Image#path} property.
	 *
	 * @param string $image_name Name of the image.
	 *
	 * @return string The absolute path.
	 */
	public function path_to_image($image_name) {
		return $this->absolutePath . $image_name;
	}
}