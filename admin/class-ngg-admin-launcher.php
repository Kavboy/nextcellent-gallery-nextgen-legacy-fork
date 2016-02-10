<?php

/**
 * NGG_Admin_Launcher - Admin Section for NextGEN Gallery
 *
 * @since   1.9.30
 */
class NGG_Admin_Launcher {

	/**
	 * @var string $base_slug The base slug for admin pages.
	 * @since 1.9.31
	 */
	private $base_slug;

	/**
	 * @var NCG_Admin_Page $page The page we want to display.
	 * @since 1.9.31
	 */
	private $page;

	/**
	 * The admin launcher isn't more than a bunch of functions that run when certain actions/filters are executed.
	 *
	 * See the link for the order in which the hooks are fired, as this is important.
	 * @link https://codex.wordpress.org/Plugin_API/Action_Reference#Actions_Run_During_an_Admin_Page_Request
	 *
	 * @param string $slug The base slug for the admin pages URL.
	 */
	public function __construct($slug) {

		$this->base_slug = $slug;

		//Register the settings we need.
		add_action( 'admin_init', array($this, 'register_settings'));

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
		add_menu_page( __( 'Galleries', 'nggallery' ), __( 'Galleries', 'nggallery' ),
			'NextGEN Gallery overview', $this->base_slug, array( $this, 'show_menu' ), 'dashicons-format-gallery' );

		add_submenu_page( $this->base_slug, __( 'Overview', 'nggallery' ), __( 'Overview', 'nggallery' ),
			'NextGEN Gallery overview',
			$this->base_slug, array( $this, 'show_menu' ) );

		add_submenu_page( $this->base_slug, __( 'Add Gallery / Images', 'nggallery' ),
			__( 'Add Gallery / Images', 'nggallery' ), 'NextGEN Upload images', $this->sluggify( 'add-gallery' ),
			array( $this, 'show_menu' ) );

		add_submenu_page( $this->base_slug, __( 'Galleries', 'nggallery' ), __( 'Galleries', 'nggallery' ),
			'NextGEN Manage gallery', $this->sluggify( 'manage' ),
			array( $this, 'show_menu' ) );

		add_submenu_page( $this->base_slug, __( 'Albums', 'nggallery' ), __( 'Albums', 'nggallery' ), 'NextGEN Edit album',
			$this->sluggify( 'manage-album' ),
			array( $this, 'show_menu' ) );

		add_submenu_page( $this->base_slug, __( 'Tags', 'nggallery' ), __( 'Tags', 'nggallery' ), 'NextGEN Manage tags',
			$this->sluggify('tags'),
			array( $this, 'show_menu' ) );

		add_submenu_page( $this->base_slug, __( 'Settings', 'nggallery' ), __( 'Settings', 'nggallery' ),
			'NextGEN Change options', $this->sluggify('options'),
			array( $this, 'show_menu' ) );

		if ( wpmu_enable_function( 'wpmuStyle' ) ) {
			add_submenu_page( $this->base_slug, __( 'Style', 'nggallery' ), __( 'Style', 'nggallery' ), 'NextGEN Change style',
				$this->sluggify('style'),
				array( $this, 'show_menu' ) );
		}
		if ( wpmu_enable_function( 'wpmuRoles' ) || is_super_admin() ) {
			add_submenu_page( $this->base_slug, __( 'Roles', 'nggallery' ), __( 'Roles', 'nggallery' ), 'activate_plugins',
				$this->sluggify('roles'),
				array( $this, 'show_menu' ) );
		}

		if ( ! is_multisite() || is_super_admin() ) {
			add_submenu_page( $this->base_slug, __( 'Reset / Uninstall', 'nggallery' ), __( 'Reset / Uninstall', 'nggallery' ),
				'activate_plugins', $this->sluggify('setup'),
				array( $this, 'show_menu' ) );
		}
	}

	/**
	 * Create a admin page slug.
	 *
	 * @param string $page The page to create a slug for.
	 *
	 * @return string A slug.
	 */
	private function sluggify($page) {
		return $this->base_slug . '-' . $page;
	}

	/**
	 * Strip the slug from a page.
	 *
	 * @param string $page The page to strip from.
	 *
	 * @return string Unslugged name.
	 */
	private function unslug($page) {
		return str_replace($this->base_slug . '-', '', $page);
	}

	/**
	 * Add the network pages to the network menu.
	 */
	public function add_network_admin_menu() {
		add_menu_page( __( 'Galleries', 'nggallery' ), __( 'Galleries', 'nggallery' ), 'nggallery-wpmu',
			$this->base_slug, array( $this, 'show_network_settings' ), 'dashicons-format-gallery' );

		add_submenu_page( $this->base_slug, __( 'Network settings', 'nggallery' ), __( 'Network settings', 'nggallery' ),
			'nggallery-wpmu',
			$this->base_slug, array( $this, 'show_network_settings' ) );

		add_submenu_page( $this->base_slug, __( 'Reset / Uninstall', 'nggallery' ), __( 'Reset / Uninstall', 'nggallery' ),
			'activate_plugins',
			'nggallery-setup', array( $this, 'show_menu' ) );
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
	 * Show the network pages.
	 */
	public function show_network_settings() {
		$this->show_upgrade_page();
		include_once( __DIR__ . '/class-ngg-style.php' );
		include_once( __DIR__ . '/wpmu.php' );
		nggallery_wpmu_setup();
	}

	/**
	 * Make the screen object for the screen we want to display.
	 *
	 * @param WP_Screen $current_screen The current screen.
	 */
	public function make_page($current_screen) {

		$i18n = strtolower( __( 'Galleries', 'nggallery' ) );

		$slug = $this->unslug(str_replace("{$i18n}_page_" , '', $current_screen->id));

		switch ( $slug ) {
			case "add-gallery" :
				require_once( __DIR__ . '/functions.php' );
				require_once( __DIR__ . '/class-ngg-adder.php' );
				$this->page = new NCG_Adder($this->base_slug);
				break;
			case "manage":
				require_once( __DIR__ . '/functions.php' );
				$this->page = $this->get_manager();
				break;
			case "manage-album" :
				require_once( __DIR__ . '/class-ngg-album-manager.php' );
				$this->page = new NGG_Album_Manager($this->base_slug);
				break;
			case "options" :
				require_once( __DIR__ . '/options/class-ncg-options-page.php' );
				$this->page = new NCG_Options_Page($this->base_slug);
				break;
			case "tags" :
				require_once( __DIR__ . '/class-ngg-tag-manager.php' );
				$this->page = new NGG_Tag_Manager($this->base_slug);
				break;
			case "style" :
				require_once( __DIR__ . '/class-ngg-style.php' );
				$this->page = new NCG_Style($this->base_slug);
				break;
			case "setup" :
				require_once( __DIR__ . '/class-ngg-setup.php' );
				$this->page = new NCG_Setup($this->base_slug);
				break;
			case "roles" :
				require_once( __DIR__ . '/class-ngg-roles.php' );
				$this->page = new NCG_Roles($this->base_slug);
				break;
			case "toplevel_page_" . $this->base_slug:
				require_once( __DIR__ . '/class-ngg-overview.php' );
				$this->page = new NGG_Overview($this->base_slug);
				break;
			default: //Not our page
				$this->page = null;
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
	 * Switch between the different management modes:
	 * - a list of all galleries,
	 * - a list of all images in a gallery,
	 * - sort mode of a gallery,
	 * - search mode.
	 *
	 * @return NCG_Admin_Page The correct managing page or null if the page could not be found.
	 */
	private function get_manager() {

		if ( ! isset( $_GET['mode'] ) || $_GET['mode'] === 'gallery' ) {

			//Display the normal page.
			include_once( 'manage/class-ngg-gallery-manager.php' );

			return new NCG_Gallery_Manager($this->base_slug);

		} elseif ( $_GET['mode'] == 'image' ) {

			//Display overview of a gallery.
			include_once( 'manage/class-ngg-image-manager.php' );

			return new NGG_Image_Manager($this->base_slug);

		} elseif ( $_GET['mode'] == 'sort' ) {

			//Display sort page.
			include_once( 'manage/class-ngg-sort-manager.php' );

			return new NGG_Sort_Manager($this->base_slug);

		} elseif ( $_GET['mode'] == 'search' ) {

			//Display search results.
			include_once( 'manage/class-ngg-search-manager.php' );

			return new NGG_Search_Manager($this->base_slug);
		} else {
			return null;
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
		wp_register_script( 'ngg-plupload-handler', plugins_url( 'js/plupload.handler.js', __FILE__), array( 'plupload-all' ), '0.0.1' );
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
		wp_register_script( 'ngg-progressbar', plugins_url( 'js/ngg.progressbar.js', __FILE__), array( 'jquery' ),
			'2.0.1' );
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
	 * @param WP_Screen $screen The current screen.
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

	public function register_settings() {
		// First, we register a section. This is necessary since all future options must belong to one.
		register_setting( 'ngg_options2', 'ngg_options2', function($arg) {
			return $arg;
		} );
		add_settings_section('plugin_main', 'Main Settings', function() {
			echo "Nice function here!";
		}, 'plugin');
		add_settings_field('plugin_text_string', 'Plugin Text Input', function() {
			echo "<input id='plugin_text_string' name='plugin_options[text_string]' size='40' type='text' value='ok' />";
		}, 'plugin', 'plugin_main');
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
