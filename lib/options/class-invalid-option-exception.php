<?php

namespace NextCellent\Lib\Options;


/**
 * This exception is thrown when a non-existing option is saved.
 *
 * @package NextCellent\Lib\Exceptions
 */
class InvalidOptionException extends \Exception {

	private $option;

	/**
	 * InvalidOptionException constructor.
	 *
	 * @param string $option The option that was not valid.
	 * @param \Exception $previous The previous Exception.
	 */
	public function __construct( $option, \Exception $previous = null ) {
		$this->option = $option;
		parent::__construct( sprintf(__("The option $option is not a valid option.", 'nggallery')), 0, $previous );
	}

	/**
	 * @return string The option.
	 */
	public function get_option() {
		return $this->option;
	}
}