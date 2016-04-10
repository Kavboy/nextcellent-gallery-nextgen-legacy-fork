<?php

namespace NextCellent\Admin;

/**
 * Class Post_Admin_Page
 *
 * Represents a page that needs to process POST parameters.
 */
abstract class Post_Admin_Page extends Admin_Page {

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