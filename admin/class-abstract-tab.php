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

	public function __construct($options, $page) {
		$this->options = $options;
		$this->page = $page . '&tab=' . $this->get_name();
	}

	/**
	 * Render the content that should be displayed in the tab.
	 *
	 * @return null
	 */
	public abstract function render();

	/**
	 * Get the name of this tab.
	 *
	 * @return string The name of this tab.
	 */
	public function get_name() {
		return isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
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