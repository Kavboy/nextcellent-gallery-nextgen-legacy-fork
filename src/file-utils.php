<?php
/**
 * File utils.
 */
namespace NextCellent\Files;

use NextCellent\Models\Gallery;

/**
 * This function returns an unique filename for the given gallery.
 *
 * @see wp_unique_filename()
 *
 * @param Gallery $gallery The gallery in which the image should go.
 * @param string  $name    The name of the imge.
 *
 * @return string The new filename.
 */
function unique_image_name( $gallery, $name ) {
    return wp_unique_filename($gallery->path, $name);
}