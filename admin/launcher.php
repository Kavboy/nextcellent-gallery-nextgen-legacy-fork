<?php

namespace NextCellent\Admin;

use NextCellent\Admin\Manage\Albums\Album_Editor;
use NextCellent\Admin\Manage\Albums\Album_Manager;
use NextCellent\Admin\Manage\Galleries\Gallery_Manager;
use NextCellent\Admin\Manage\Galleries\Image_Manager;
use NextCellent\Admin\Manage\Galleries\Search_Manager;
use NextCellent\Admin\Manage\Galleries\Sort_Manager;
use NextCellent\Admin\Settings\Settings_Page;
use NextCellent\Admin\Upload\Upload_Page;

/**
 * This is the general manager for admin pages of NextCellent.
 * This class registers the menu's and loads the correct page.
 * There are also some helper functions to work with the
 * NextCellent admin page names.
 */
class Launcher {

	/**
	 * @var string $base_slug The base slug for admin pages.
	 * @since 1.9.31
	 */
	private $base_slug;

	/**
	 * @var Admin_Page $page The page we want to display.
	 * @since 1.9.31
	 */
	private $page;

	public function __construct() {
		$this->base_slug = \NCG::ADMIN_BASE;
	}

	/**
	 * Register this class into the WordPress hooks.
	 *
	 * See the link for the order in which the hooks are fired, as this is important.
	 * @link https://codex.wordpress.org/Plugin_API/Action_Reference#Actions_Run_During_an_Admin_Page_Request
	 */
	public function register() {
		//Create the screen object.
		add_action( 'current_screen', array($this, 'make_page'));

		// Add the admin menu
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		//Add the network menu
		add_action( 'network_admin_menu', array( $this, 'add_network_admin_menu' ) );

		// Add the script and style files
		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_styles' ) );

		add_action( 'current_screen', array( $this, 'edit_current_screen' ) );

		// Add WPML hook to register description / alt text for translation
		add_action( 'ngg_image_updated', array( 'nggGallery', 'RegisterString' ) );

		add_filter( 'set-screen-option', array( $this, 'save_options' ), 10, 3 );
	}

	/**
	 * Add all menu pages to the WordPress menu.
	 *
	 * @since 1.9.31 All menu's have the same base slug.
	 */
	public function add_menu() {
		add_menu_page( __( 'Galleries', 'nggallery' ), __( 'Galleries', 'nggallery' ), Roles::VIEW_ADMIN_PAGES, $this->base_slug, array( $this, 'show_menu' ), 'dashicons-format-gallery' );

		add_submenu_page( $this->base_slug, __( 'Overview', 'nggallery' ), __( 'Overview', 'nggallery' ),
			Roles::VIEW_ADMIN_PAGES,
			$this->base_slug, array( $this, 'show_menu' ) );

		add_submenu_page( $this->base_slug, __( 'Add Gallery / Images', 'nggallery' ),
			__( 'Add Gallery / Images', 'nggallery' ), Roles::UPLOAD_IMAGES, $this->sluggify( Upload_Page::NAME ),
			array( $this, 'show_menu' ) );

		add_submenu_page( $this->base_slug, __( 'Galleries', 'nggallery' ), __( 'Galleries', 'nggallery' ),
			Roles::MANAGE_GALLERIES, $this->sluggify( Gallery_Manager::NAME ),
			array( $this, 'show_menu' ) );

		add_submenu_page( $this->base_slug, __( 'Albums', 'nggallery' ), __( 'Albums', 'nggallery' ), Roles::MANAGE_ALBUMS,
			$this->sluggify( Album_Manager::NAME ),
			array( $this, 'show_menu' ) );

		add_submenu_page( $this->base_slug, __( 'Tags', 'nggallery' ), __( 'Tags', 'nggallery' ), Roles::MANAGE_TAGS,
			$this->sluggify( Tag_Manager::NAME ),
			array( $this, 'show_menu' ) );

		add_submenu_page( $this->base_slug, __( 'Settings', 'nggallery' ), __( 'Settings', 'nggallery' ),
			Roles::MANAGE_OPTIONS, $this->sluggify( Settings_Page::NAME),
			array( $this, 'show_menu' ) );

		global $ngg;
		$options = $ngg->options;

		if ( $options->get_mu_option( 'wpmuStyle' ) || is_super_admin() ) {
			add_submenu_page( $this->base_slug, __( 'Style', 'nggallery' ), __( 'Style', 'nggallery' ), Roles::MANAGE_STYLE,
				$this->sluggify(Style_Page::NAME),
				array( $this, 'show_menu' ) );
		}

		add_submenu_page( $this->base_slug, __( 'Capabilities', 'nggallery' ), __( 'Capabilities', 'nggallery' ), Roles::MANAGE_OPTIONS,
			$this->sluggify( Roles::NAME ),
			array( $this, 'show_menu' ) );

	}

	/**
	 * Create a admin page slug.
	 *
	 * @param string $page The page to create a slug for.
	 *
	 * @return string A slug.
	 */
	public static function sluggify($page) {
		if($page == '') {
			return \NCG::ADMIN_BASE;
		} else {
			return \NCG::ADMIN_BASE . '-' . $page;
		}
	}

	/**
	 * Strip the slug from a page.
	 *
	 * @param string $page The page to strip from.
	 *
	 * @return string Unslugged name.
	 */
	public static function unsluggify($page) {
		return str_replace(\NCG::ADMIN_BASE . '-', '', $page);
	}

	/**
	 * Get the relative URL to a page given it's name.
	 *
	 * Note: this works with normal admin pages only - not for the WordPress Network setup.
	 *
	 * @param string $page_name The name of the page.
	 *
	 * @return string The URL.
	 */
	public static function get_url($page_name) {
		return admin_url('admin.php?page=' . self::sluggify($page_name));
	}

	/**
	 * Get the escaped relative URL to a page given it's name.
	 *
	 * @param string $page_name The name of the page.
	 *
	 * @return string The URL.
	 */
	public static function esc_get_url($page_name) {
		return esc_url(self::get_url($page_name));
	}

	/**
	 * Add the network pages to the network menu.
	 *
	 * The page with settings is added as a sub-page of the general settings.
	 */
	public function add_network_admin_menu() {
		add_submenu_page( 'settings.php' , __( 'NextCellent settings', 'nggallery' ), __( 'NextCellent', 'nggallery' ),
			Roles::MANAGE_NETWORK_OPTIONS,
			self::sluggify(WPMU_Settings::NAME), array( $this, 'show_menu' ) );
	}

	/**
	 * Maybe show an upgrade page.
	 */
	private function show_upgrade_page() {

		global $ngg;

		// check for upgrade and show upgrade screen
		if ( get_option( 'ngg_db_version' ) != $ngg::DB_VERSION ) {
			require_once( __DIR__ . '/functions.php' );
			require_once( __DIR__ . '/upgrade.php' );
			nggallery_upgrade_page();
			exit;
		}
	}

	/**
	 * Make the screen object for the screen we want to display.
	 *
	 * @param \WP_Screen $current_screen The current screen.
	 */
	public function make_page($current_screen) {

		//If we are on the network admin site, the page is under the network settings.
		if(is_network_admin()) {
			$slug = str_replace('-network', '', str_replace("settings_page_" , '', $current_screen->id));
		} else {
			$i18n = strtolower( __( 'Galleries', 'nggallery' ) );
			$slug = str_replace("{$i18n}_page_" , '', $current_screen->id);
		}

		$slug = self::unsluggify($slug);

		switch ( $slug ) {
			case Upload_Page::NAME:
				require_once( __DIR__ . '/functions.php' );
				$this->page = new Upload_Page();
				break;
			case Gallery_Manager::NAME:
				require_once( __DIR__ . '/functions.php' );
				$this->page = $this->get_manager();
				break;
			case Album_Manager::NAME:
				$this->page = $this->get_album_manager();
				break;
			case Settings_Page::NAME:
				$this->page = new Settings_Page();
				break;
			case Tag_Manager::NAME:
				$this->page = new Tag_Manager();
				break;
			case Style_Page::NAME:
				$this->page = new Style_Page();
				break;
			case Roles::NAME:
				$this->page = new Roles();
				break;
			case "toplevel_page_" . $this->base_slug:
				$this->page = new Overview_Page();
				break;
			//Network settings
			case WPMU_Settings::NAME:
				$this->page = new WPMU_Settings();
				break;
			default: //Not our page
				$this->page = null;
		}
	}

	/**
	 * Switch between the different management modes:
	 * - a list of all galleries,
	 * - a list of all images in a gallery,
	 * - sort mode of a gallery,
	 * - search mode.
	 *
	 * @return Admin_Page The correct managing page or null if the page could not be found.
	 */
	private function get_manager() {

		if ( ! isset( $_GET['mode'] ) || $_GET['mode'] === 'gallery' ) {

			//Display the normal page.
			return new Gallery_Manager($this->base_slug);

		} elseif ( $_GET['mode'] == 'image' ) {

			//Display overview of a gallery.
			return new Image_Manager();

		} elseif ( $_GET['mode'] == 'sort' ) {

			//Display sort page.
			return new Sort_Manager($this->base_slug);

		} elseif ( $_GET['mode'] == 'search' ) {

			//Display search results.
			return new Search_Manager($this->base_slug);
		} else {
			return null;
		}
	}
	
	private function get_album_manager() {
		if( isset($_GET['mode']) && $_GET['mode'] == 'edit') {
			return new Album_Editor();
		} else {
			return new Album_Manager();
		}
	}

	/**
	 * Show the menu.
	 */
	public function show_menu() {

		//Show the upgrade page if needed.
		$this->show_upgrade_page();

		//Display the page
		if ( $this->page != null ) { //This should never be null
			$this->page->display();
		}
	}

	/**
	 * Register the pages in the admin bar menu.
	 * 
	 * This function is static, since it is called from the main plugin file,
	 * as this also needs to be done on the front-end of the site.
	 */
	public static function admin_bar_menu() {
		// If the current user can't write posts, this is all of no use, so let's not output an admin menu
		if ( !current_user_can( Roles::VIEW_ADMIN_PAGES ) ) {
			return;
		}

		/**
		 * @global  \WP_Admin_Bar $wp_admin_bar
		 */
		global $wp_admin_bar;

		if ( current_user_can( 'NextGEN Upload images' ) ) {
			$wp_admin_bar->add_node( array(
				'parent' => 'new-content',
				'id'     => 'ngg-menu-add-gallery',
				'title'  => __( 'NextCellent Media', 'nggallery' ),
				'href'   => self::get_url(Upload_Page::NAME)
			) );
		}

		//If the user is in the admin screen, there is no need to display this.
		if ( !is_admin() ) {
			$wp_admin_bar->add_node( array(
				'parent' => 'site-name',
				'id'     => 'ngg-menu-overview',
				'title'  => __( 'NextCellent', 'nggallery' ),
				'href'   => self::get_url(Overview_Page::NAME)
			) );
			if ( current_user_can( Roles::MANAGE_GALLERIES ) ) {
				$wp_admin_bar->add_node( array(
					'parent' => 'ngg-menu-overview',
					'id'     => 'ngg-menu-manage-gallery',
					'title'  => __( 'Gallery', 'nggallery' ),
					'href'   => self::get_url(Gallery_Manager::NAME)
				) );
			}
			if ( current_user_can( Roles::MANAGE_ALBUMS ) ) {
				$wp_admin_bar->add_node( array(
					'parent' => 'ngg-menu-overview',
					'id'     => 'ngg-menu-manage-album',
					'title'  => __( 'Albums', 'nggallery' ),
					'href'   => self::get_url(Album_Editor::NAME)
				) );
			}
			if ( current_user_can( Roles::MANAGE_TAGS ) ) {
				$wp_admin_bar->add_node( array(
					'parent' => 'ngg-menu-overview',
					'id'     => 'ngg-menu-tags',
					'title'  => __( 'Tags', 'nggallery' ),
					'href'   => self::get_url(Tag_Manager::NAME)
				) );
			}
			if ( current_user_can( 'NextGEN Change options' ) ) {
				$wp_admin_bar->add_node( array(
					'parent' => 'ngg-menu-overview',
					'id'     => 'ngg-menu-options',
					'title'  => __( 'Settings', 'nggallery' ),
					'href'   => self::get_url(Settings_Page::NAME)
				) );
			}
			if ( current_user_can( 'NextGEN Change style' ) ) {
				$wp_admin_bar->add_node( array(
					'parent' => 'ngg-menu-overview',
					'id'     => 'ngg-menu-style',
					'title'  => __( 'Style', 'nggallery' ),
					'href'   => self::get_url(Style_Page::NAME)
				) );
			}
		}
	}

	/**
	 * Load the scripts on the admin pages.
	 */
	public function load_scripts() {

		//Only if on one of our pages.
		if ( $this->page == null ) {
			return;
		}

		wp_register_script( 'ngg-ajax', plugins_url( 'js/ngg.ajax.js', __FILE__), array( 'jquery' ), '1.4.1' );
		wp_localize_script( 'ngg-ajax', 'nggAjaxSetup', array(
			'url'        => admin_url( 'admin-ajax.php' ),
			'action'     => 'ngg_ajax_operation',
			'nonce'      => wp_create_nonce( 'ngg-ajax' ),
			'permission' => __( 'You do not have the correct permission', 'nggallery' ),
			'error'      => __( 'Unexpected Error', 'nggallery' ),
			'failure'    => __( 'A failure occurred', 'nggallery' )
		) );
		wp_register_script( 'ngg-plupload-handler', plugins_url( 'js/plupload.handler.js', __FILE__), array( 'plupload-all' ), '2.0.0' );
		wp_localize_script( 'ngg-plupload-handler', 'pluploadL10n', array(
			'queue_limit_exceeded'      => __( 'You have attempted to queue too many files.' ),
			'file_exceeds_size_limit'   => __( 'This file exceeds the maximum upload size for this site.' ),
			'zero_byte_file'            => __( 'This file is empty. Please try another.' ),
			'invalid_filetype'          => __( 'This file type is not allowed. Please try another.' ),
			'not_an_image'              => __( 'This file is not an image. Please try another.' ),
			'image_memory_exceeded'     => __( 'Memory exceeded. Please try another smaller file.' ),
			'image_dimensions_exceeded' => __( 'This is larger than the maximum size. Please try another.' ),
			'default_error'             => __( 'An error occurred in the upload. Please try again later.' ),
			'missing_upload_url'        => __( 'There was a configuration error. Please contact the server administrator.' ),
			'upload_limit_exceeded'     => __( 'You may only upload 1 file.' ),
			'http_error'                => __( 'HTTP error.' ),
			'upload_failed'             => __( 'Upload failed.' ),
			'io_error'                  => __( 'IO error.' ),
			'security_error'            => __( 'Security error.' ),
			'file_cancelled'            => __( 'File canceled.' ),
			'upload_stopped'            => __( 'Upload stopped.' ),
			'dismiss'                   => __( 'Dismiss' ),
			'crunching'                 => __( 'Crunching&hellip;' ),
			'deleted'                   => __( 'moved to the trash.' ),
			'error_uploading'           => __( '&#8220;%s&#8221; has failed to upload due to an error' ),
			'no_gallery'                => __( 'You didn\'t select a gallery!', 'nggallery' )
		) );
		wp_register_script( 'ngg-progressbar', plugins_url( 'js/ngg.progressbar.js', __FILE__), array( 'jquery-ui-dialog' ) );
		wp_register_script( 'ngg-autocomplete', plugins_url( 'js/ngg.autocomplete.js', __FILE__ ), array( 'jquery-ui-autocomplete' ), '1.1' );

		wp_register_script( 'jqueryFileTree', plugins_url( 'js/jqueryFileTree/jqueryFileTree.js', __FILE__), array( 'jquery' ), '1.0.1' );

		wp_register_script( 'shutter', plugins_url('shutter/shutter-reloaded.js', __DIR__), false, '1.3.2' );
		wp_localize_script( 'shutter', 'shutterSettings', array(
			'msgLoading' => __( 'L O A D I N G', 'nggallery' ),
			'msgClose'   => __( 'Click to Close', 'nggallery' ),
			'imageCount' => '1'
		) );

		wp_register_script( 'ngg-cropper', plugins_url('js/cropper/cropper.min.js', __FILE__), '2.2.5' );

		//Enqueue scripts.
		$this->page->register_scripts();
	}

	/**
	 * Load the CSS files.
	 */
	public function load_styles() {

		//Only if on one of our pages.
		if ( $this->page == null ) {
			return;
		}

		wp_register_style( 'nggadmin', plugins_url( 'css/nggadmin.css', __FILE__), false, '2.8.1', 'screen' );
		wp_register_style( 'ngg-jqueryui', plugins_url( 'css/jquery.ui.css', __FILE__), false, '1.8.5', 'screen' );
		wp_register_style( 'jqueryFileTree', plugins_url( 'js/jqueryFileTree/jqueryFileTree.css', __FILE__), false, '1.0.1', 'screen');
		wp_register_style( 'nggtabs', plugins_url( 'css/jquery.ui.tabs.css', __FILE__), false, '2.5.0', 'screen' );
		wp_register_style( 'ngg-cropper', plugins_url( 'js/cropper/cropper.min.css', __FILE__), '2.2.5' );
		wp_register_style( 'shutter', plugins_url('shutter/shutter-reloaded.css', __DIR__), false, '1.3.2', 'screen' );
		wp_register_style( 'datepicker', plugins_url('css/jquery.ui.datepicker.css', __FILE__), false, '1.8.2', 'screen' );

		//Enqueue styles.
		$this->page->register_styles();
	}

	/**
	 * Save the screen options.
	 */
	public static function save_options( $status, $option, $value ) {
		return $value;
	}

	/**
	 * Add help and options to the correct screens
	 *
	 * @since 1.9.24
	 *
	 * @param \WP_Screen $screen The current screen.
	 */
	public function edit_current_screen( $screen ) {

		//Not our page.
		if($this->page == null) {
			return;
		}

		if($this->page != null) {
			$this->page->add_help($screen);
		}

		//Set the sidebar (same on all pages)
		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'nggallery' ) . '</strong></p>' .
			'<p><a href="http://codex.wordpress.org/Plugins_Editor_Screen" target="_blank">' . __( 'Support Forums',
				'nggallery' ) . '</a></p>' .
			'<p><a href="https://bitbucket.org/wpgetready/nextcellent" target="_blank">' . __( 'Source Code',
				'nggallery' ) . '</a></p>'
		);
	}
}

/**
 * Check if a function is enabled on multisite.
 *
 * @param string $value
 *
 * @todo Move from here
 *
 * @return bool If it's enabled or not.
 *
 * @deprecated Use the function in NextCellent\Utils
 */
function wpmu_enable_function( $value ) {
	if ( is_multisite() ) {
		$ngg_options = get_site_option( 'ngg_options' );

		if(isset($ngg_options[ $value ])) {
			return $ngg_options[ $value ];
		} else {
			return false;
		}
	}

	// if this is not WPMU, enable it !
	return true;
}
