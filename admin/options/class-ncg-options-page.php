<?php

require_once( dirname( __DIR__ ) . '/class-ncg-post-admin-page.php' );
require_once( __DIR__ . '/class-ncg-option-tab.php' );

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
class NCG_Options_Page extends NCG_Post_Admin_Page {

	/**
	 * @var NCG_Options_Page $options The options.
	 */
	private $options;

	/**
	 * @var string|NCG_Option_Tab $current The current page or the name of the current page.
	 */
	private $current;

	/**
	 * NCG_Options_Page constructor.
	 *
	 * @param string $slug The base slug for the page.
	 */
	public function __construct($slug) {
		parent::__construct($slug);

		global $ngg;

		$this->options = $ngg->get('options');

		$tab = 'general';
		if(isset($_GET['tab'])) {
			$tab = $_GET['tab'];
		}
		$this->load_page($tab);
	}

	/**
	 * Converts a name of a tab to a page, or sets the name as current.
	 *
	 * @param string $name The name.
	 *
	 * @see NGG_Options::current
	 */
	private function load_page( $name ) {
		switch($name) {
			case 'general':
				require_once( __DIR__ . '/class-ncg-option-tab-general.php' );
				$this->current = new NCG_Option_Tab_General($this->options, $this->get_full_url());
				break;
			case 'images':
				require_once( __DIR__ . '/class-ncg-option-tab-images.php' );
				$this->current = new NCG_Option_Tab_Images($this->options, $this->get_full_url());
				break;
			case 'gallery':
				require_once( __DIR__ . '/class-ncg-option-tab-gallery.php' );
				$this->current = new NCG_Option_Tab_Gallery($this->options, $this->get_full_url());
				break;
			case 'effects':
				require_once( __DIR__ . '/class-ncg-option-tab-effects.php' );
				$this->current = new NCG_Option_Tab_Effects($this->options, $this->get_full_url());
				break;
			case 'watermark':
				require_once( __DIR__ . '/class-ncg-option-tab-watermark.php' );
				$this->current = new NCG_Option_Tab_Watermark($this->options, $this->get_full_url());
				break;
			case 'slideshow':
				require_once( __DIR__ . '/class-ncg-option-tab-slideshow.php' );
				$this->current = new NCG_Option_Tab_Slideshow($this->options, $this->get_full_url());
				break;
			default:
				$this->current = $name;
		}
	}

	/**
	 * Save/Load options and add a new hook for plugins
	 */
	protected function processor() {

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
			if ( $old_state != $this->options['usePermalinks'] || $old_slug != $this->options['permalinkSlug'] )
				$nggRewrite->flush();

			nggGallery::show_message(__('Settings updated successfully','nggallery'));
		}

		if ( isset($_POST['clearcache']) ) {
			check_admin_referer('ngg_settings');

			$path = WINABSPATH . $this->options['gallerypath'] . 'cache/';

			if (is_dir($path))
				if ($handle = opendir($path)) {
					while (false !== ($file = readdir($handle))) {
						if ($file != '.' && $file != '..') {
						  @unlink($path . '/' . $file);
						}
					}
					closedir($handle);
				}

			nggGallery::show_message(__('Cache cleared','nggallery'));
		}

		if ( isset($_POST['createslugs']) ) {
			check_admin_referer('ngg_settings');
			$this->rebuild_slugs();
		}

		do_action( 'ngg_update_options_page' );
	}

	/**
	 * Render the page content
	 */
	public function display() {

		parent::display();

		// get list of tabs
		$tabs = $this->get_tabs();

		?>
		<div class="wrap">
			<h2><?php _e('Settings', 'nggallery') ?></h2>
			<h2 class="nav-tab-wrapper">
				<?php
				foreach($tabs as $tab => $name) {
					$class =  $this->is_active($tab) ? 'nav-tab-active' : '';
					echo "<a class='nav-tab $class' href='?page=nextcellent-options&tab=$tab'>$name</a>";
				}
				?>
			</h2>
			<?php
			//If the current page is a string, we need a third party tab.
			if (is_string($this->current)) {
				if ( method_exists( $this, "tab_$this->current" )) {
					call_user_func( array( $this , "tab_$this->current"));
				} else {
					do_action( 'ngg_tab_content_' . $this->current);
				}
			} else {
				//Display the page.
				$this->current->render();
			}
			?>
		</div>
		<?php
		$this->print_scripts();
	}

	/**
	 * Check if a tab is active or not.
	 *
	 * @param string $tab The name of the tab.
	 *
	 * @return bool True if active, otherwise false.
	 */
	private function is_active($tab) {
		return $this->current == $tab || (!is_string($this->current) && $this->current->get_name() == $tab);
	}

	/**
	 * Print the JavaScript.
	 */
	private function print_scripts() {
		?>
		<script type="text/javascript">
			function insertcode(value) {
				var effectcode, extra;
				switch (value) {
					case 'none':
						effectcode = "";
						break;
					case "thickbox":
						effectcode = 'class="thickbox" rel="%GALLERY_NAME%"';
						break;
					case "lightbox":
						effectcode = 'rel="lightbox[%GALLERY_NAME%]"';
						break;
					case "highslide":
						effectcode = 'class="highslide" onclick="return hs.expand(this, { slideshowGroup: %GALLERY_NAME% })"';
						break;
					case "shutter":
						effectcode = 'class="shutterset_%GALLERY_NAME%"';
						break;
					case "photoSwipe":
						effectcode = 'data-size="%IMG_WIDTH%x%IMG_HEIGHT%"';
						extra = 'Works with <a href="https://wordpress.org/plugins/photo-swipe/">PhotoSwipe</a>.';
						break;
					default:
						break;
				}
				jQuery("#thumbCode").val(effectcode);
				jQuery("#effects-more").html(extra);
			}

			jQuery(document).ready( function($) {
				//$('html,body').scrollTop(0);
				//Set tabs.
				$('#slider').tabs({ fxFade: true, fxSpeed: 'fast' }).css('display', 'block');

				//Set colorpicker.
				$('.picker').wpColorPicker();

				//Set preview for watermark.
				$('#wm-preview-select').on("nggAutocompleteDone", function() {
					$('#wm-preview-image').attr("src", '<?php echo home_url( 'index.php' ); ?>' + '?callback=image&pid=' + this.value + '&mode=watermark');
                    $('#wm-preview-image-url').attr("href", '<?php echo home_url( 'index.php' ); ?>' + '?callback=image&pid=' + this.value + '&mode=watermark');
				});

                jQuery("#wm-preview-select").nggAutocomplete( {
                    type: 'image',domain: "<?php echo home_url('index.php', is_ssl() ? 'https' : 'http'); ?>"
                });
			});
		</script>
		<?php
	}

	/**
	 * Create array for tabs and add a filter for other plugins to inject more tabs
	 *
	 * @return array $tabs
	 */
	private function get_tabs() {

		$tabs = array();

		$tabs['general'] = __('General', 'nggallery');
		$tabs['images'] = __('Images', 'nggallery');
		$tabs['gallery'] = __( 'Gallery', 'nggallery' );
		$tabs['effects'] = __('Effects', 'nggallery');
		$tabs['watermark'] = __('Watermark', 'nggallery');
		$tabs['slideshow'] = __('Slideshow', 'nggallery');

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

	/**
	 * Rebuild the slugs with an AJAX-request.
	 */
	private function rebuild_slugs() {
		global $wpdb;

		$total = array();
		// get the total number of images
		$total['images'] = intval( $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggpictures") );
		$total['gallery'] = intval( $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggallery") );
		$total['album'] = intval( $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggalbum") );

		$messages = array(
			'images' => __( 'Rebuild image structure : %s / %s images', 'nggallery' ),
			'gallery' => __( 'Rebuild gallery structure : %s / %s galleries', 'nggallery' ),
			'album' => __( 'Rebuild album structure : %s / %s albums', 'nggallery' ),
		);

		foreach ( array_keys( $messages ) as $key ) {

			$message = sprintf( $messages[ $key ] ,
				"<span class='ngg-count-current'>0</span>",
				"<span class='ngg-count-total'>" . $total[ $key ] . "</span>"
			);

			echo "<div class='$key updated'><p class='ngg'>$message</p></div>";
		}

		$ajax_url = add_query_arg( 'action', 'ngg_rebuild_unique_slugs', admin_url( 'admin-ajax.php' ) );
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				var ajax_url = '<?php echo $ajax_url; ?>',
					_action = 'images',
					images = <?php echo $total['images']; ?>,
					gallery = <?php echo $total['gallery']; ?>,
					album = <?php echo $total['album']; ?>,
					total = 0,
					offset = 0,
					count = 50;

				var $display = $('.ngg-count-current');
				$('.finished, .gallery, .album').hide();
				total = images;

				function call_again() {
					if ( offset > total ) {
						offset = 0;
						// 1st run finished
						if (_action == 'images') {
							_action = 'gallery';
							total = gallery;
							$('.images, .gallery').toggle();
							$display.html(offset);
							call_again();
							return;
						}
						// 2nd run finished
						if (_action == 'gallery') {
							_action = 'album';
							total = album;
							$('.gallery, .album').toggle();
							$display.html(offset);
							call_again();
							return;
						}
						// 3rd run finished, exit now
						if (_action == 'album') {
							$('.ngg')
								.html('<?php esc_html_e( 'Done.', 'nggallery' ); ?>')
								.parent('div').hide();
							$('.finished').show();
							return;
						}
					}

					$.post(ajax_url, {'_action': _action, 'offset': offset}, function(response) {
						$display.html(offset);

						offset += count;
						call_again();
					});
				}

				call_again();
			});
		</script>
		<?php
	}

	public function register_styles() {
		wp_enqueue_style( 'nggtabs');
		wp_enqueue_style( 'nggadmin' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'ngg-jqueryui' );
		wp_enqueue_style( 'jqueryFileTree');
	}

	public function register_scripts() {
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'wp-color-picker' );
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
		return 'options';
	}
}