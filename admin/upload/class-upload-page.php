<?php

namespace NextCellent\Admin\Upload;

use NextCellent\Admin\Abstract_Tab;
use NextCellent\Admin\Abstract_Tab_Page;
use NextCellent\Admin\Roles;
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
	}

	public function register_styles() {
		wp_enqueue_style( 'nggadmin' );

		if(!is_string($this->current)) {
			$this->current->register_styles();
		}
	}

	public function register_scripts() {
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

		$help = '<p>' . __( 'On this page you can add galleries and pictures to those galleries.', 'nggallery' ) . '</p>';
		$help .= '<dl>';

		if(current_user_can(Roles::MANAGE_GALLERIES)) {
			$help .= '<dt>' . __( 'New gallery', 'nggallery' ) . '</dt><dd>' . __( 'Add new galleries to NextCellent.', 'nggallery' ) . '</dd>';
		}

		$help .= '<dt>' . __( 'Images', 'nggallery' ) . '</dt><dd>' . __( 'Add new images to a gallery. This is only shown if there are galleries', 'nggallery' ) . '</dd>';

		if ( $this->options->get_mu_option(Options::MU_ALLOW_UPLOAD_ZIP) ) {
			$help .= '<dt>' . __( 'ZIP file', 'nggallery' ) . '</dt><dd>' . __( 'Add images from a ZIP file.', 'nggallery' ) . '</dd>';
		}

		if( $this->options->get_mu_option(Options::MU_ALLOW_IMPORT_FOLDER) ) {
			$help .= '<dt>' . __( 'Import folder', 'nggallery' ) . '</dt><dd>' . __( 'Import a folder from the server as a new gallery.', 'nggallery' ) . '</dd>';
		}

		$screen->add_help_tab( array(
			'id'      => $screen->id . '-general',
			'title'   => __('General', 'nggallery'),
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
				$this->current = new Tab_Gallery($this->options, $this->get_full_url(), $this->default_tab());
				break;
			case 'images':
				$this->current = new Tab_Image($this->options, $this->get_full_url(), $this->default_tab());
				break;
			case 'zip':
				$this->current = new Tab_Zip($this->options, $this->get_full_url(), $this->default_tab());
				break;
			case 'folder':
				$this->current = new Tab_Import($this->options, $this->get_full_url(), $this->default_tab());
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
		if(current_user_can(Roles::MANAGE_GALLERIES)) {
			return 'gallery';
		} else {
			return 'images';
		}
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

		//TODO: check if there are galleries or not.
		$tabs['images'] = __('Images', 'nggallery');

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