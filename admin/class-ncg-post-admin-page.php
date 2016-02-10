<?php

include_once( 'class-ncg-admin-page.php' );

/**
 * Class NCG_Post_Admin_Page
 *
 * Represents a page that needs to process POST parameters.
 */
abstract class NCG_Post_Admin_Page extends NCG_Admin_Page {

	/**
	 * @param string $slug The slug for this page. It is recommended you pass this parameter.
	 *                     For example, with slug 'nextcellent', the page is 'nextcellent-[NAME]'.
	 */
	public function __construct($slug) {
		parent::__construct($slug);
	}

	/**
	 * Display the page. Child classes should override this method.
	 */
	public function display() {
		//Handle the post updates.
		if ( isset( $_POST ) && !empty($_POST) ) {
			$this->processor();
		}
	}

	/**
	 * This function must handle the POST parameters, and is automatically called
	 * when there are POST parameters.
	 *
	 * This function is called before the page display is built, so message to the user
	 * and other output will be visible.
	 */
	protected abstract function processor();
}