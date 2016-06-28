<?php

namespace NextCellent\Files;

/**
 * @author  Niko Strijbol
 * @version 27/06/2016
 */
class InvalidPathException extends FileException {
	
	public function __construct($path) {
		parent::__construct("$path is not a valid path or is not writeable.");
	}

}