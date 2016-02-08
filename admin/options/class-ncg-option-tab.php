<?php

/**
 * A tab in the options screen.
 */
abstract class NCG_Option_Tab {

	/**
	 * @var NCG_Options $options The options.
	 */
	protected $options;

	/**
	 * @var string The relative URL to the current page, e.g. http://example.com/wordpress/wp-admin/admin.php?page=nextcellent-options&tab=images
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
	public abstract function get_name();

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
}