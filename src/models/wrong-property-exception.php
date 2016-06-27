<?php

namespace NextCellent\Models;

use NextCellent\Exception;

/**
 * This exception is thrown if you try to access a property that
 * does not exist.
 */
class Wrong_Property_Exception extends Exception {

	/**
	 * @param string $property The property that was tried to access.
	 */
	public function __construct( $property ) {
		parent::__construct( sprintf( 'The property %s does not exist.', $property ) );
	}
}