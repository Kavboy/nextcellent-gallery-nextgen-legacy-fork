<?php

namespace NextCellent\Models;

use NextCellent\Database\Manager;

/**
 * A collection of images.
 * 
 * @author  Niko Strijbol
 */
interface Displayable_Images {

	/**
	 * Get the images to display.
	 * 
	 * @return array
	 */
	function get_images();
}