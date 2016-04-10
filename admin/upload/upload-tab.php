<?php

namespace NextCellent\Admin\Upload;

use NextCellent\Admin\Abstract_Tab;
use NextCellent\Models\Gallery;

/**
 * @author  Niko Strijbol
 * @version 7/04/2016
 */
abstract class Upload_Tab extends Abstract_Tab {

	/**
	 * Print all given galleries as HTML options, taking into account
	 * the permissions.
	 * 
	 * @param Gallery[] $galleries
	 */
	protected function print_galleries($galleries) {
		foreach($galleries as $gallery) {
			if ( $gallery->can_manage() ) {
				$name = ( empty( $gallery->title ) ) ? $gallery->name : $gallery->title;
				echo '<option value="' . $gallery->id . '" >' . $gallery->id . ' - ' . esc_attr( $name ) . '</option>';
			}
		}
	}
}