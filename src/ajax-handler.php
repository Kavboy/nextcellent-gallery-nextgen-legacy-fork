<?php

namespace NextCellent;

use NextCellent\Options\Options;

/**
 * Contains  ajax-related functions.
 *
 * @author Niko
 */

class Ajax_Handler {
	
	const UPLOAD_ACTION = 'ncg_admin_upload';
	
	private $options;

	/**
	 * @param Options $options The options.
	 */
	public function __construct(Options $options) {
		$this->options = $options;
	}

	/**
	 * Register the ajax handlers
	 */
	public function register() {
		add_action( 'wp_ajax_' . self::UPLOAD_ACTION, [$this, 'handle_upload']);
	}
	
	public function handle_upload() {
		(new Upload_Handler($_POST))->handleUpload();
	}
}