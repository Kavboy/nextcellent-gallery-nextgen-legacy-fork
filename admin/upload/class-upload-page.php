<?php

namespace NextCellent\Admin\Upload;

use NextCellent\Admin\Abstract_Tab;
use NextCellent\Admin\Abstract_Tab_Page;
use NextCellent\Admin\Post_Admin_Page;
use NextCellent\Admin\Roles;
use function NextCellent\Admin\wpmu_enable_function;
use NextCellent\Models\Gallery;
use NextCellent\Options\Options;

/**
 * Class Upload
 *
 * Add new stuff to NextCellent.
 *
 * @todo The whole system with the plupload needs a rework.
 */
class Upload_Page extends Abstract_Tab_Page {

	const NAME = 'upload';

	private $options;

	public function __construct() {
		global $ngg;

		$this->options = $ngg->options;

		parent::__construct();
	}

	/**
	 * Perform the upload and add a new hook for plugins
	 */
	protected function processor() {
		$this->process('ncg_upload', 'ngg_update_addgallery_page' );
	}
	
	protected function old_processor() {
		/**
		 * @global \nggdb $nggdb
		 */
		global $nggdb;

		$options = get_option( 'ngg_options' );

		$default_path = $options['gallerypath'];

		if ( isset( $_POST['zipupload'] ) ) {
			check_admin_referer( 'ngg_addgallery' );

			if ( ! \nggGallery::current_user_can( 'NextGEN Upload a zip' ) ) {
				wp_die( __( 'Cheatin&#8217; uh?' ) );
			}

			if ( $_FILES['zipfile']['error'] == 0 || ( ! empty( $_POST['zipurl'] ) ) ) {
				\nggAdmin::import_zipfile( intval( $_POST['zipgalselect'] ) );
			} else {
				\nggGallery::show_error( __( 'Upload failed!', 'nggallery' ) );
			}
		}

		if ( isset( $_POST['importfolder'] ) ) {
			check_admin_referer( 'ngg_addgallery' );

			if ( ! \nggGallery::current_user_can( 'NextGEN Import image folder' ) ) {
				wp_die( __( 'Cheatin&#8217; uh?' ) );
			}

			$galleryfolder = $_POST['galleryfolder'];
			if ( ( ! empty( $galleryfolder ) ) AND ( $default_path != $galleryfolder ) ) {
				\nggAdmin::import_gallery( $galleryfolder );
			}
		}

		if ( isset( $_POST['swf_callback'] ) ) {
			if ( $_POST['galleryselect'] == '0' ) {
				\nggGallery::show_error( __( 'You didn\'t select a gallery!', 'nggallery' ) );
			} else {
				if ( $_POST['swf_callback'] == '-1' ) {
					\nggGallery::show_error( __( 'Upload failed!', 'nggallery' ) );
				} else {
					$gallery = $nggdb->find_gallery( (int) $_POST['galleryselect'] );
					\nggAdmin::import_gallery( $gallery->path );
				}
			}
		}
	}

	/**
	 * Render the page content
	 *
	 * @return void
	 */
	public function display() {

		parent::display();

		/**
		 * @global \nggdb $nggdb
		 */
		global $nggdb;

		$args = array(
			'max_size'  => \nggGallery::check_memory_limit(),
			'galleries' => $nggdb->find_all_galleries('gid', 'DESC'),
			'options'   => get_option('ngg_options')

		);

		


		?>

		<script type="text/javascript">
		
		//jQuery Tabs script
			jQuery(document).ready(function(){
				jQuery("#zip-upload").click(function() {
					checkZipFile();
				});

				jQuery("#import-folder").click(function() {
					return confirm(
						'<?php echo esc_js(__("This will change folder and file names (e.g. remove spaces, special characters, ...)","nggallery") )?>' +
						'\n\n' +
						'<?php echo esc_js( __("You will need to update your URLs if you link directly to the images.","nggallery") )?>' +
						'\n\n' +
						'<?php echo esc_js( __("Press OK to proceed, and Cancel to stop.","nggallery") )?>'
					);
				});
			});

			// File Tree implementation
			jQuery(function() {
				jQuery("span.browsefiles").show().click(function(){
					var browser = jQuery("#file_browser");
					browser.fileTree({
						script: "admin-ajax.php?action=ngg_file_browser&nonce=<?php echo wp_create_nonce( 'ngg-ajax' ) ;?>",
						root: jQuery("#galleryfolder").val()
					}, function(folder) {
						jQuery("#galleryfolder").val( folder );
					});
					browser.show('slide');
				});
			});

			

			//Check if the user has selected a zip file
			function checkZipFile() {
				if( !(document.getElementById('zipfile').value || document.getElementById("zipurl").value) ) {
					alert("<?php _e('You didn\'t select a file!','nggallery')?>");
					event.preventDefault();
				}
			}

			//Check if the user has selected an image file
			function checkImgFile() {
				if( !document.getElementById('imagefiles').value ) {
					alert("<?php _e('You didn\'t select a file!','nggallery')?>");
					event.preventDefault();
				}
			}
		</script>
		<?php

	}

	private function tab_zip($args) {
		?>
		<!-- zip-file operation -->
		<h3><?php _e('Upload a ZIP File', 'nggallery') ;?></h3>
		<form name="zipupload" id="zipupload_form" method="POST" enctype="multipart/form-data" action="<?php echo $this->get_full_url().'#zipupload'; ?>" accept-charset="utf-8" >
			<?php wp_nonce_field('ngg_addgallery') ?>
			<table class="form-table">
				<tr>
					<th><?php _e('Select ZIP file', 'nggallery') ;?>:</th>
					<td>
						<input type="file" name="zipfile" id="zipfile" class="uploadform">
						<p class="description">
							<?php _e('Upload a ZIP file with images', 'nggallery') ;?>
						</p>
					</td>
				</tr>
				<?php if (function_exists('curl_init')) : ?>
					<tr>
						<th><?php _e('or enter URL', 'nggallery') ;?>:</th>
						<td>
							<input type="text" name="zipurl" id="zipurl" class="regular-text code uploadform">
							<p class="description">
								<?php _e('Import a ZIP file from a URL', 'nggallery') ;?>
							</p>
						</td>
					</tr>
				<?php endif; ?>
				<tr>
					<th><?php _e('in to', 'nggallery') ;?></th>
					<td>
						<select name="zipgalselect" id="zipgalselect">
							<option value="0" ><?php _e('a new gallery', 'nggallery') ?></option>
							<?php $this->print_galleries($args['galleries']); ?>
						</select>
						<br><?php echo $args['max_size']; ?>
						<p class="description">
							<?php printf( __('Note: the upload limit on your server is <strong>%s MB</strong>.', 'nggallery'), wp_max_upload_size() / (1024 * 1024)); ?>
						</p>
						<br>
						<?php if ( is_multisite() && wpmu_enable_function('wpmuQuotaCheck') ) {
							display_space_usage();
						}  ?>
					</td>
				</tr>
			</table>
			<div class="submit">
				<input class="button-primary" type="submit" name="zipupload" id="zip-upload" value="<?php _e('Start upload', 'nggallery') ;?>">
			</div>
		</form>
		<?php
	}

	protected function tab_folder($args) {
		?>
		<!-- import folder -->
		<h3><?php _e('Import an image folder', 'nggallery') ;?></h3>
		<form name="importfolder" id="importfolder_form" method="POST" action="<?php echo $this->get_full_url().'#importfolder'; ?>" accept-charset="utf-8" >
			<?php wp_nonce_field('ngg_addgallery') ?>
			<table class="form-table">
				<tr>
					<th><?php _e('Import from server:', 'nggallery') ;?></th>
					<td>
						<input type="text" id="galleryfolder" class="regular-text code" name="galleryfolder" value="<?php echo $args['options']['gallerypath']; ?>">
						<span class="browsefiles button" style="display:none"><?php _e('Browse...', 'nggallery'); ?></span>
						<br>
						<div id="file_browser"></div>
						<p class="description"><?php _e('Note: you can change the default path in the gallery settings', 'nggallery') ;?></p>
						<br><?php echo $args['max_size']; ?>
					</td>
				</tr>
			</table>
			<div class="submit">
				<input class="button-primary" type="submit" name= "importfolder" id="import-folder" value="<?php _e('Import folder', 'nggallery') ;?>">
			</div>
		</form>
		<?php
	}

	protected function tab_images() {
		?>
		
		<?php
	}

	public function register_styles() {
		wp_enqueue_style( 'jqueryFileTree' );
		wp_enqueue_style( 'nggadmin' );
		wp_enqueue_style( 'wp-color-picker' );
		
		if(!is_string($this->current)) {
			$this->current->register_styles();
		}
	}

	public function register_scripts() {
		wp_enqueue_script( 'ngg-plupload-handler' );
		wp_enqueue_script( 'ngg-ajax' );
		wp_enqueue_script( 'ngg-progressbar' );
		wp_enqueue_script( 'jqueryFileTree' );

		if(!is_string($this->current)) {
			$this->current->register_scripts();
		}
	}

	/**
	 * A possibility to add help to the screen.
	 *
	 * @param \WP_Screen $screen The current screen.
	 */
	public function add_help( $screen ) {
		/**
		 * @global \nggdb $nggdb
		 */
		global $nggdb;
		$gallerylist = $nggdb->find_all_galleries( 'gid', 'DESC' ); //look for galleries

		$help = '<p>' . __( 'On this page you can add galleries and pictures to those galleries.',
				'nggallery' ) . '</p>';
		if ( \nggGallery::current_user_can( 'NextGEN Add new gallery' ) ) {
			$help .= '<p><strong>' . __( 'New gallery',
					'nggallery' ) . '</strong> - ' . __( 'Add new galleries to NextCellent.',
					'nggallery' ) . '</p>';
		}
		if ( empty ( $gallerylist ) ) {
			$help .= '<p><strong>' . __( 'You must add a gallery before adding images!',
					'nggallery' ) . '</strong>';
		} else {
			$help .= '<p><strong>' . __( 'Images',
					'nggallery' ) . '</strong> - ' . __( 'Add new images to a gallery.', 'nggallery' ) . '</p>';
		}
		if ( wpmu_enable_function( 'wpmuZipUpload' ) && \nggGallery::current_user_can( 'NextGEN Upload a zip' ) && ! empty ( $gallerylist ) ) {
			$help .= '<p><strong>' . __( 'ZIP file',
					'nggallery' ) . '</strong> - ' . __( 'Add images from a ZIP file.', 'nggallery' ) . '</p>';
		}
		if ( wpmu_enable_function( 'wpmuImportFolder' ) && \nggGallery::current_user_can( 'NextGEN Import image folder' ) ) {
			$help .= '<p><strong>' . __( 'Import folder',
					'nggallery' ) . '</strong> - ' . __( 'Import a folder from the server as a new gallery.',
					'nggallery' ) . '</p>';
		}

		$screen->add_help_tab( array(
			'id'      => $screen->id . '-general',
			'title'   => 'Add things',
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

	/**
	 * Converts a name of a tab to a page, or sets the name as current.
	 *
	 * @param string $name The name.
	 *
	 * @see NGG_Options::current
	 */
	protected function load_page( $name ) {
		switch($name) {
			case 'gallery':
				$this->current = new Tab_Gallery($this->options, $this->get_full_url());
				break;
			case 'images':
				$this->current = new Tab_Image($this->options, $this->get_full_url());
				break;
			default:
				/**
				 * Load a settings page.
				 *
				 * @var string $name The name of the settings page to load.
				 *
				 * @return Abstract_Tab The abstract tab.
				 */
				$name = apply_filters( 'ncg_load_upload_page', $name);
				$this->current = $name;
		}
	}

	/**
	 * @return string The name of the default tab.
	 */
	protected function default_tab() {
		return 'gallery';
	}

	/**
	 * @return string The title of this page.
	 */
	protected function page_title() {
		return __( 'Add Gallery / Images', 'nggallery');
	}

	/**
	 * Create array for tabs and add a filter for other plugins to inject more tabs
	 *
	 * @return array $tabs
	 */
	protected function get_tabs() {
		$tabs = array();

		if(current_user_can(Roles::MANAGE_GALLERIES)) {
			$tabs['gallery'] = __('New gallery', 'nggallery');
		}

		if(Gallery::count() > 0) {
			$tabs['images'] = __('Images', 'nggallery');
		}

		if($this->options->get_mu_option(Options::MU_ALLOW_UPLOAD_ZIP)) {
			$tabs['zip'] = __( 'ZIP file', 'nggallery' );
		}

		if($this->options->get_mu_option(Options::MU_ALLOW_IMPORT_FOLDER)) {
			$tabs['folder'] = __('Import folder', 'nggallery');
		}


		/**
		 * Add your own upload tab to NextCellent.
		 *
		 * @param array $tabs {
		 *     The tabs already registered.
		 *
		 *     @var string The slug of the option. This is the name you will need to load content.
		 *     @var string The name to be displayed to the user.
		 * }
		 */
		$tabs = apply_filters('ngg_addgallery_tabs', $tabs);

		return $tabs;
	}
}