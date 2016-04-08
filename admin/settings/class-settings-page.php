<?php

namespace NextCellent\Admin\Settings;

use NextCellent\Admin\Abstract_Tab_Page;
use NextCellent\Options\Options;
use WP_Screen;

/**
 * The settings page for NextCellent.
 *
 * This is not made with the WordPress Settings API, since we need lots of custom options.
 *
 * Third-party plugins can add their own tab by doing the following:
 *
 * 1) Register the tab with the filter 'ngg_settings_tabs'.
 * 2) Add the callable that will display the settings to the 'ngg_tab_content_$NAME' hook,
 *    where $NAME is the name you registered the tab in the previous step.
 *
 * @example
 * function add_tab( $tabs ) {
 *     $tabs['my_plugin'] = __( 'My plugin', 'my-plugin' );
 *     return $tabs;
 * }
 *
 * add_filter( 'ngg_settings_tabs', 'add_tab' );
 *
 * function display_settings() {
 *     echo '<p>Nice settings here</p>';
 * }
 *
 * add_hook( 'ngg_tab_content_my_plugin', 'display_settings');
 */
class Settings_Page extends Abstract_Tab_Page {
	
	const NAME = 'options';

	/**
	 * @var Options $options The options.
	 */
	private $options;

	public function __construct() {

		global $ngg;

		$this->options = $ngg->options;

		parent::__construct();
	}

	/**
	 * Converts a name of a tab to a page, or sets the name as current.
	 *
	 * @param string $name The name.
	 *
	 * @see NGG_Options::current
	 */
	protected function load_page( $name ) {

		$tabs = $this->get_tabs();

		switch($name) {
			case 'general':
				$this->current = new Tab_General($this->options, $this->get_full_url(), $tabs);
				break;
			case 'images':
				$this->current = new Tab_Images($this->options, $this->get_full_url(), $tabs);
				break;
			case 'gallery':
				$this->current = new Tab_Gallery($this->options, $this->get_full_url(), $tabs);
				break;
			case 'effects':
				$this->current = new Tab_Effects($this->options, $this->get_full_url(), $tabs);
				break;
			case 'watermark':
				$this->current = new Tab_Watermark($this->options, $this->get_full_url(), $tabs);
				break;
			case 'slideshow':
				$this->current = new Tab_Slideshow($this->options, $this->get_full_url(), $tabs);
				break;
			case 'advanced':
				$this->current = new Tab_Advanced($this->options, $this->get_full_url(), $tabs);
				break;
			default:
				/**
				 * Load a settings page.
				 *
				 * @var string $name The name of the settings page to load.
				 *                   
				 * @return Settings_Tab The settings tab.
				 */
				$name = apply_filters( 'ncg_load_settings_page', $name);
				$this->current = $name;
		}
	}

	/**
	 * Save/Load options and add a new hook for plugins
	 */
	protected function processor() {
		$this->process('ncg_settings_updated', 'ngg_update_options_page');
	}

	/**
	 * This function handles the old way to save settings. This is kept because
	 * other plugins may rely on NextCellent to save options.
	 *
	 * @deprecated Please update your plugin to manage your own settings.
	 */
	protected function old_processor() {
		global $nggRewrite;

		$old_state = $this->options['usePermalinks'];
		$old_slug  = $this->options['permalinkSlug'];

		if ( isset($_POST['updateoption']) ) {
			check_admin_referer('ngg_settings');
			// get the hidden option fields, taken from WP core
			if ( $_POST['page_options'] ) {
				$new_options = explode( ',', stripslashes( $_POST['page_options'] ) );
			} else {
				$new_options = false;
			}

			if ($new_options) {
				foreach ($new_options as $option) {
					$option = trim($option);
					$value = false;
					if ( isset( $_POST[ $option ] ) ) {
						$value = trim( $_POST[ $option ] );
						if ($value === "true") {
							$value = true;
						}

						if ( is_numeric( $value ) ) {
							$value = (int) $value;
						}
					}

					$this->options->set_option($option, $value);
				}

				// do not allow a empty string
				if ( empty ( $this->options['permalinkSlug'] ) )
					$this->options->set_option('permalinkSlug', 'nggallery');

				// the path should always end with a slash
				$this->options->set_option('gallerypath', trailingslashit($this->options['gallerypath']));
				$this->options->set_option('imageMagickDir', trailingslashit($this->options['imageMagickDir']));

				// the custom sortorder must be ascending
				$ngg_options['galSortDir'] = ($this->options['galSort'] == 'sortorder') ? 'ASC' : $this->options['galSortDir'];
			}
			// Save options
			$this->options->save_options();

			// Flush Rewrite rules
			if ( $old_state != $this->options['usePermalinks'] || $old_slug != $this->options['permalinkSlug'] ) {
				$nggRewrite->flush();
			}

			\NextCellent\show_success(__('Settings updated successfully','nggallery'));
		}
	}

	/**
	 * Create array for tabs and add a filter for other plugins to inject more tabs
	 *
	 * @return array $tabs
	 */
	protected function get_tabs() {

		$tabs = array();

		$tabs['general'] = __('General', 'nggallery');
		$tabs['images'] = __('Images', 'nggallery');
		$tabs['gallery'] = __( 'Gallery', 'nggallery' );
		$tabs['effects'] = __('Effects', 'nggallery');
		$tabs['watermark'] = __('Watermark', 'nggallery');
		$tabs['slideshow'] = __('Slideshow', 'nggallery');
		$tabs['advanced'] = __('Advanced', 'nggallery');
 
		/**
		 * Add your own settings tab to NextCellent.
		 *
		 * @param array $tabs {
		 *     The tabs already registered.
		 *
		 *     @var string The slug of the option. This is the name you will need to load content.
		 *     @var string The name to be displayed to the user.
		 * }
		 */
		$tabs = apply_filters('ngg_settings_tabs', $tabs);

		return $tabs;
	}

	public function register_styles() {
		wp_enqueue_style( 'nggadmin' );
		wp_enqueue_style( 'ngg-jqueryui' );
	}

	public function register_scripts() {
		wp_enqueue_script( 'ngg-autocomplete' );
	}

	/**
	 * A possibility to add help to the screen.
	 *
	 * @param WP_Screen $screen The current screen.
	 */
	public function add_help( $screen ) {
		$help = '<p>' . __( 'Edit all of NextCellent\'s options. The options are sorted in multiple categories.',
				'nggallery' ) . '</p>';
		$help .= '<p><strong>' . __( 'General',
				'nggallery' ) . '</strong> - ' . __( 'General NextCellent options. Contains options for permalinks and related images.',
				'nggallery' ) . '</p>';
		$help .= '<p><strong>' . __( 'Images',
				'nggallery' ) . '</strong> - ' . __( 'All image-related options. Also contains options for thumbnails.',
				'nggallery' ) . '</p>';
		$help .= '<p><strong>' . __( 'Gallery',
				'nggallery' ) . '</strong> - ' . __( 'Everything about galleries. From sorting options to the number of images, it\'s all in here.',
				'nggallery' ) . '</p>';
		$help .= '<p><strong>' . __( 'Effects',
				'nggallery' ) . '</strong> - ' . __( 'Make your gallery look beautiful.',
				'nggallery' ) . '</p>';
		$help .= '<p><strong>' . __( 'Watermark',
				'nggallery' ) . '</strong> - ' . __( 'Who doesn\'t want theft-proof images?',
				'nggallery' ) . '</p>';
		$help .= '<p><strong>' . __( 'Slideshow',
				'nggallery' ) . '</strong> - ' . __( 'Edit options for the slideshow.', 'nggallery' ) . '</p>';
		$help .= '<p>' . __( 'Don\'t forget to press save!', 'nggallery' ) . '</p>';

		$screen->add_help_tab( array(
			'id'      => $screen->id . '-general',
			'title'   => 'Edit options',
			'content' => $help
		) );
	}


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
	public function get_name() {
		return self::NAME;
	}

	protected function page_title() {
		return __('Settings', 'nggallery');
	}

	/**
	 * @return string The name of the default tab.
	 */
	protected function default_tab() {
		return 'general';
	}
}