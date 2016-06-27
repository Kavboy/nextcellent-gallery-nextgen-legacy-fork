<?php

namespace NextCellent\Models;

use NextCellent\Database\Manager;

/**
 * An implementation of {@link Images} that lazily loads the images from the database. The class requires a query,
 * and will only get the images from the database when necessary.
 *
 * The number of total images is cached, so you can freely call total.
 *
 * @author  Niko Strijbol
 */
class Lazy_Images implements Images {

	protected $query;
	protected $values;
	protected $counter;
	protected $counterValues;

	protected $processor;

	//Cached query results.
	protected $totalCount = null;
	protected $imageMap = null;
	protected $start = 0;
	protected $number = 0;

	/**
	 * Lazy_Images constructor.
	 *
	 * @param string     $query         The query with which images will be searched. Currently this is implemented with a simple
	 *                                  string. This means the query must be valid after a limit clause has been attached.
	 * @param string|int $counter       The query to count the total number of items. If you pass an int, the int is used.
	 * @param array      $values        The values to use in the query. It is best to use this, as this will get escaped by
	 *                                  WordPress to prevent against attacks.
	 * @param array      $counterValues The values to replace in the counter query.
	 * @param callable   $processor     The function to convert database results to Images.
	 */
	protected function __construct($query, $counter, array $values = [], array $counterValues = [], callable $processor = null) {
		$this->query = $query;
		$this->values = $values;
		$this->counter = $counter;
		$this->counterValues = $counterValues;

		if($processor === null) {
			$this->processor = [$this, 'resultsToImages'];
		} else {
			$this->processor = $processor;
		}
	}

	/**
	 * Get some portion of the images. The first image is at index 0.
	 *
	 * THe obvious use for this is pagination.
	 *
	 * @param int $start  At which image to start. Must be non-negative.
	 * @param int $number How many images are needed. If not set, all images are returned.
	 *
	 * @return Image[] Associative array of the image ID => Image.
	 * @throws \LogicException
	 */
	public function getImages($start = 0, $number = PHP_INT_MAX) {
		
		if($this->query === null || $this->values === null) {
			throw new \LogicException('There needs to be a query.');
		}

		if($this->imageMap !== null && $this->start === $start && $this->number === $number) {
			return $this->imageMap;
		}

		//Local values
		$data = $this->values;
		
		//Get constant number
		if($this->constantTotal() && $number === PHP_INT_MAX) {
			$number = $start + $this->counter;
		}

		// Build the limit sql
		if ($number !== PHP_INT_MAX) {
			$limit = "LIMIT %d,%d";
			$data = array_merge($data, [$start, $number]);
		} else {
			$limit = '';
		}

		//Append to query
		$query = $this->query . ' ' . $limit;

		//Get the results
		$results = Manager::get()->get_results($query, $data);

		$function = $this->processor;
		$converted = $function($results);

		if(!$this->constantTotal() && $start === 0 && $number === PHP_INT_MAX) {
			$this->totalCount = count($converted);
		}

		$this->imageMap = $converted;
		$this->start = $start;
		$this->number = $number;

		return $this->imageMap;
	}

	/**
	 * @return int The total number of images.
	 */
	public function total() {
		
		if($this->constantTotal()) {
			$this->totalCount = $this->counter;
		} elseif($this->totalCount === null) {
			$this->totalCount = Manager::get()->get_int($this->counter, $this->counterValues);
		}

		return $this->totalCount;
	}
	
	protected function constantTotal() {
		return is_numeric($this->counter);
	}

	/**
	 * Convert an array of data to an image. This method is used if the callable given to the constructor is null. This
	 * is used this way because PHP does not support anonymous classes before 7.
	 *
	 * @param array $results The data.
	 *
	 * @return Image[] Associative array of the image ID => Image.
	 */
	protected function resultsToImages($results) {
		$converted = [];

		foreach ( $results as $result ) {
			/** @noinspection PhpInternalEntityUsedInspection */
			$converted[ $result[ Image::ID ] ] = Image::to_image( $result );
		}

		return $converted;
	}
}