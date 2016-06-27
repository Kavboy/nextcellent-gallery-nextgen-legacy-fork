<?php

namespace NextCellent\Models;

/**
 * A collection of images, from which it is possible to extract a subset, and know how many images there
 * are in total.
 * 
 * @author  Niko Strijbol
 */
interface Images {

	/**
	 * Get some portion of the images. The first image is at index 0.
	 *
	 * THe obvious use for this is pagination.
	 *
	 * @param int $start At which image to start. Must be non-negative.
	 * @param int $number How many images are needed. If not set, all images are returned. Must be non-negative.
	 *
	 * @return Image[] The requested images. If there are no images, an empty array is returned.
	 */
	public function getImages($start = 0, $number = PHP_INT_MAX);

	/**
	 * @return int The total number of images.
	 */
	public function total();
}