<?php

namespace NextCellent\Admin;

use NextCellent\Options\Options;

/**
 * @author  Niko Strijbol
 * @version 7/04/2016
 */
abstract class Abstract_Tab {

	/**
	 * @var Options $options The options.
	 */
	protected $options;

	/**
	 * @var string The relative URL to the current page, e.g. admin.php?page=nextcellent-options&tab=images
	 */
	protected $page;
	
	private $default_name;

	/**
	 * Abstract_Tab constructor.
	 *
	 * @param Options $options The options.
	 * @param string $page The page name.
	 * @param string $default_name The name of the tab if the URL contains no tab.
	 */
	public function __construct($options, $page, $default_name) {
		$this->options = $options;
		$this->default_name = $default_name;
		$this->page = $page . '&tab=' . $this->get_name();
	}

	/**
	 * Render the content that should be displayed in the tab.
	 *
	 * @return null
	 */
	public abstract function render();

	/**
	 * Get the name of this tab (as it appears in the URL).
	 *
	 * @return string The name of this tab.
	 */
	public function get_name() {
		return isset( $_GET['tab'] ) ? $_GET['tab'] : $this->default_name;
	}

	/**
	 * Print the JavaScript to the page.
	 */
	public function print_scripts() {
		//No JavaScript
	}
	
	public abstract function register_scripts();
	
	public abstract function register_styles();

	/**
	 * Handle saving the settings.
	 */
	public abstract function processor();

	/**
	 * Do the WordPress nonce for this page.
	 */
	protected function nonce() {
		wp_nonce_field( 'ncg_tab_' . $this->get_name() );
	}
}