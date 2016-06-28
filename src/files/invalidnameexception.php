<?php

namespace NextCellent\Files;

/**
 * @author  Niko Strijbol
 * @version 27/06/2016
 */
class InvalidNameException extends FileException {
	public function __construct($name) {
		parent::__construct("$name is not a valid name.");
	}
}