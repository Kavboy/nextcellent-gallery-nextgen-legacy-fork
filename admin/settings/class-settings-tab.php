<?php

namespace NextCellent\Admin\Settings;

use NCG_Options;

/**
 * A tab in the options screen.
 */
abstract class Settings_Tab {

	/**
	 * @var NCG_Options $options The options.
	 */
	protected $options;

	/**
	 * @var string The relative URL to the current page, e.g. admin.php?page=nextcellent-options&tab=images
	 */
	protected $page;

	/**
	 * @var array All tab slugs mapped to their name.
	 */
	private $tabs;

	public function __construct($options, $page, $tabs) {
		$this->options = $options;
		$this->page = $page . '&tab=' . $this->get_name();
		$this->tabs = $tabs;
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
	 * Handle saving the settings. The referrer is already checked at this
	 * point, so you do not need to do that.
	 */
	public abstract function processor();

	/**
	 * Compare two values and echo readonly if they are.
	 *
	 * @param mixed $current The current value. If it is a string, this function assumes it is an option.
	 * @param mixed $other The other value.
	 */
	protected function readonly($current, $other = true) {

		if(is_string($current)) {
			$current = $this->options[$current];
		}

		if ( $current == $other ) {
			echo 'readonly="readonly"';
		}
	}

	/**
	 * Show a success message.
	 */
	protected function success_message() {
		\NextCellent\Utils\show_success(sprintf(__('The settings "%s" were saved successfully.', 'nggallery'), $this->tabs[$this->get_name()]));
	}

	/**
	 * Convert and set given boolean options.
	 *
	 * @param array $options The options in an array.
	 */
	protected function save_booleans($options) {
		foreach ( $options as $option ) {
			$this->options->set_option($option, isset($_POST[$option]) && \NextCellent\Utils\convert_to_bool($_POST[$option]));
		}
	}

	/**
	 * Convert and set given positive integer options.
	 *
	 * @param array $options The options in an array.
	 */
	protected function save_number($options) {
		foreach ( $options as $option ) {
			if(isset($_POST[$option])) {
				$this->options->set_option($option, absint($_POST[$option]));
			}
		}
	}

	/**
	 * Sanitize and set given text options.
	 *
	 * @param $options
	 */
	protected function save_text($options) {
		foreach ( $options as $option ) {
			if(isset($_POST[$option])) {
				$this->options->set_option($option, sanitize_text_field($_POST[$option]));
			}
		}
	}

	/**
	 * Save options that are restricted to a set of possibilities.
	 *
	 * @param array $options Contains the name of the option mapped to the possibilities.
	 */
	protected function save_restricted($options) {
		foreach ( $options as $option => $possibilities ) {
			if(isset($_POST[$option]) && in_array($_POST[$option], $possibilities)) {
				$this->options->set_option($option, $_POST[$option]);
			}
		}
	}

	/**
	 * Render the select options.
	 *
	 * @param string $option The option for which the select is made.
	 * @param array $values The possible values and their name.
	 */
	protected function render_select_options($option, $values) {
		foreach ( $values as $value => $name ) {
			echo "<option value='$value' " . $this->options->selected($option, $value, false) . ">$name</option>";
		}
	}

	/**
	 * Render the radio options.
	 *
	 * @param string $option The option for which the radio is made.
	 * @param array $values The possible values.
	 */
	protected function render_radio_options($option, $values) {
		$out = '<fieldset>';
		foreach ( $values as $possibility => $name ) {
			$out .= '<label>';
			$out .= "<input name='$option' type='radio' value='$possibility'" . $this->options->checked($option, $possibility, false) . "> $name";
			$out .= '</label><br>';
		}
		$out .= '</fieldset>';
		echo $out;
	}

	/**
	 * Do the WordPress nonce for this page.
	 */
	protected function nonce() {
		wp_nonce_field( 'ncg_settings_' . $this->get_name() );
	}

	/**
	 * Print the JavaScript to the page.
	 */
	public function print_scripts() {
		//No JavaScript
	}
}