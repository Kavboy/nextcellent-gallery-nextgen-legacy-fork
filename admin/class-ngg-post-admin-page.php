<?php

include_once( 'interface-ncg-admin-page.php' );

/**
 * Class NGG_Post_Admin_Page
 *
 * Represents a simple admin page.
 */
abstract class NGG_Post_Admin_Page implements NCG_Admin_Page {

	protected $page;

	public function __construct() {

		$this->page = admin_url() . 'admin.php?page=' . $_GET['page'];

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
	 * Handle the POST updates. This functions is called by the display() function, if used properly.
	 */
	protected abstract function processor();
}