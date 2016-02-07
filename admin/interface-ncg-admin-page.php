<?php

/**
 * Almost every page in the admin section must implement this interface.
 */
interface NCG_Admin_Page {

	/**
	 * Display the page.
	 */
	function display();

	/**
	 * Enqueue/register the needed styles.
	 */
	function register_styles();

	/**
	 * Enqueue/register the needed scripts
	 */
	function register_scripts();

	/**
	 * A possibility to add help to the screen.
	 *
	 * @param WP_Screen $screen The current screen.
	 */
	function add_help($screen);
}