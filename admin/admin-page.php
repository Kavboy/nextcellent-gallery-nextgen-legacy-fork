<?php

namespace NextCellent\Admin;

/**
 * Almost every page in the admin section must implement this interface.
 */
abstract class Admin_Page {

	/**
	 * Display the page.
	 */
	public abstract function display();

	/**
	 * Enqueue/register the needed styles.
	 */
	public abstract function register_styles();

	/**
	 * Enqueue/register the needed scripts
	 */
	public abstract function register_scripts();

	/**
	 * A possibility to add help to the screen.
	 *
	 * @param \WP_Screen $screen The current screen.
	 */
	public abstract function add_help($screen);

	/**
	 * Get the name of this page. This is the second part of the full name:
	 *
	 * admin.php?page=[SLUG]-[PAGE_NAME].
	 *
	 * An example is 'admin.php?page=nextcellent-manage-images'
	 *
	 * The 'nextcellent' is the slug, the 'manage-images' is the page name.
	 *
	 * @return string The name.
	 */
	public abstract function get_name();

	/**
	 * Get the full page name of this page, e.g. 'nextcellent-manage'.
	 *
	 * @return string
	 */
	public function get_full_name() {
		return Launcher::sluggify($this->get_name());
	}

	/**
	 * Returns a full relative URL to this page. Intended use is for forms and the like.
	 *
	 * @example  'admin.php?page=nextcellent-add-gallery'.
	 * @return string The URL.
	 *
	 */
	public function get_full_url() {
		return Launcher::get_url($this->get_name());
	}
}