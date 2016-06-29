<?php
/*
Plugin Name: NextCellent Gallery
Plugin URI: http://www.wpgetready.com/nextcellent-gallery
Description: A Photo Gallery for WordPress providing NextGEN legacy compatibility from version 1.9.13
Author: WPGReady, niknetniko based on Alex Rabe & PhotoCrati work.
Author URI: http://www.wpgetready.com
Version: 1.9.31

Copyright (c) 2007-2011 by Alex Rabe & NextGEN DEV-Team
Copyright (c) 2012 Photocrati Media
Copyright (c) 2013-2014 WPGetReady
Copyright (c) 2014-2016 WPGetReady, niknetniko

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// If this file is called directly, abort.
if ( !defined( 'WPINC' )) {
	die;
}

//If NextGEN is activated, deactivate this plugin, and warn about it!
check_for_nextgen();

/**
 * The main class is only loaded if it or it's alias do not exist yet.
 *
 * !NextCellent only supports PHP branches that are actively maintained!
 * See http://php.net/supported-versions.php
 */
if (!class_exists('NCG')) {

	/**
	 * Class NCG
	 *
	 * @property-read NextCellent\Options\Options    options The options.
	 * @property-read NextCellent\Database\Manager   manager The database manager.
	 * @property-read NextCellent\Shortcodes\Handler shortcodes The shortcode handler.
     */
    class NCG {

	    const VERSION = '1.9.31';
	    const DB_VERSION = '1.8.3';
	    const MINIMUM_WP = '4.0';
	    const MINIMUM_PHP = '5.6';

	    //The base for our admin pages. A page will be 'admin.php?page=nextcellent-[NAME]
	    const ADMIN_BASE = 'nextcellent';
	    
	    //The name of the folder with custom templates/styles
	    const NCG_FOLDER = 'ngg_styles';

	    /**
	     * @var NextCellent\Registry $registry The registry for dependencies.
	     */
	    private $registry;

        /**
         * class constructor
         */
        public function __construct() {

			// Stop the plugin if we missed the requirements
			if ( !$this->required_version() ) {
				return;
			}

	        //Load the PHP files
	        $this->load_dependencies();

	        //Make the registry.
	        $this->registry = new \NextCellent\Registry();

	        //Add things to the registry.
	        $this->set_up_registry();

			//Define constants.
			$this->define_constant();

	        //Define the database tables.
			$this->define_tables();

	        //Register the hooks.
			$this->register_hooks_actions();
		}

	    /**
	     * This resolves to the registry for dependencies. This function is provide to
	     * maintain backwards compatibility.
	     *
	     * @param string $name
	     *
	     * @deprecated 1.9.31
	     *
	     * @return object|null
	     */
	    public function __get( $name ) {
		    switch ($name) {
			    case 'version':
				    return self::VERSION;
			    case 'dbversion':
				    return self::DB_VERSION;
			    default:
				    return $this->get($name);
		    }
	    }

	    /**
	     * Get a dependency from the registry.
	     *
	     * @param string $name
	     *
	     * @return object|null The class instance if found, otherwise null.
	     */
	    public function get( $name ) {
		    return $this->registry->get($name);
	    }

	    /**
	     * Reload the options.
	     */
	    public function reload_options() {
		    /** @noinspection PhpInternalEntityUsedInspection */
		    $this->registry->add('options', new NextCellent\Options\Options());
	    }

	    /**
	     * Add stuff to the registry.
	     */
	    private function set_up_registry() {
		    //Add the options to the registry.
		    /** @noinspection PhpInternalEntityUsedInspection */
		    $this->registry->add('options', new \NextCellent\Options\Options());

		    //Add the database manager.
		    global $wpdb;
		    /** @noinspection PhpInternalEntityUsedInspection */
		    $manager = new \NextCellent\Database\Manager($wpdb);

		    //Add image factories.
		    $this->registry->add('manager', $manager);

		    //Add the shortcode manager.
		    $handler = new \NextCellent\Shortcodes\Handler( $this->options );
		    $this->registry->add( 'shortcodes', $handler );
	    }

	    /**
	     * Register the hooks and actions.
	     */
	    private function register_hooks_actions() {

		    //Register the activation function.
		    register_activation_hook( __FILE__, array($this, 'activate') );
		    //Register the deactivation function.
		    register_deactivation_hook( __FILE__, array($this, 'deactivate') );

		    //Load the text domain
		    add_action( 'plugins_loaded', function() {
			    load_plugin_textdomain( 'nggallery', false, NCG_FOLDER . '/lang');
		    });

		    // Add to the toolbar
		    add_action( 'admin_bar_menu', [\NextCellent\Admin\Launcher::class, 'admin_bar_menu'] );

		    //Register the taxonomy. This MUST be in the init hook.
		    add_action( 'init', [ $this, 'register_taxonomy' ] );

		    //Register an hook when a new blog is made.
		    add_action( 'wpmu_new_blog', [ $this, 'multisite_new_blog' ] );

		    //Add some links on the plugin page
		    add_filter('plugin_row_meta', [ $this, 'add_plugin_links' ], 10, 2);

		    //Actually start the plugin.
		    add_action( 'init', [ $this, 'start_plugin' ] );

		    //Register the admin hooks.
		    if(is_admin() && !defined( 'DOING_AJAX' )) {
			    //The admin hooks are registered in the constructor of Launcher
			    $admin = new NextCellent\Admin\Launcher(self::ADMIN_BASE);
			    $admin->register();
		    }

		    //Register the widgets
		    add_action( 'widgets_init', function() {
			    register_widget(\NextCellent\Widgets\Gallery_Widget::class);
			    register_widget(\NextCellent\Widgets\Media_RSS_Widget::class);
			    register_widget(\NextCellent\Widgets\Slideshow_Widget::class);
		    });

		    //Add the RSS feed
		    \NextCellent\RSS\Generator::registerFeeds();

		    //Register rewrite related things.
		    \NextCellent\Rendering\Rewrite::register();

		    //Register shortcodes
		    $handler = new \NextCellent\Shortcodes\Handler( $this->options );
		    $handler->register_shortcodes();

		    //Register ajax
		    if(is_admin() && defined('DOING_AJAX') && DOING_AJAX) {
			    $handler = new \NextCellent\Ajax_Handler( $this->options );
			    $handler->register();
		    }
	    }

	    public function show_upgrade_message() {
		    if( is_network_admin() ) {
			    $url = network_admin_url('admin.php?page=' . NCG_FOLDER);
		    } else {
			    $url = admin_url('admin.php?page=' . NCG_FOLDER);
		    }
		    ?>
			<div id="message" class="update-nag">
				<p><strong><?php _e('NextCellent Gallery requires a database upgrade.', "nggallery") ?> <a href="<?php echo $url ?>"><?php _e('Upgrade now.', 'nggallery'); ?></a></strong></p>
			</div>
			<?php
	    }

        /**
         * Main start invoked after all plugins are loaded.
         */
        public function start_plugin() {
	        
			// Check if we are in the admin area
			if ( !is_admin() ) {

				// Add the script and style files
				add_action('wp_enqueue_scripts', [ $this, 'load_scripts' ] );
				add_action('wp_enqueue_scripts', [ $this, 'load_styles' ] );
			}

	        //Check for a database upgrade.
	        if( get_option( 'ngg_db_version' ) != self::DB_VERSION && isset($_GET['page']) ) {

		        /**
		         * If the silentUpgrade option is not empty, we try and do the upgrade now.
		         */
		        if ( $this->options->get( \NextCellent\Options\Options::SILENT_DB_UPDATE ) ) {
			        include_once( dirname( __FILE__ ) . '/admin/functions.php' );
			        include_once( dirname( __FILE__ ) . '/admin/upgrade.php' );
			        try {
				        ngg_upgrade();
			        } catch (Exception $e) {
				        add_action( 'admin_notices', function() {
					        echo '<div class="error"><p>' . __( 'Something went wrong while upgrading NextCellent Gallery.', "nggallery" ) . '</p></div>';
				        });
			        }
		        } else {
			        add_action( 'all_admin_notices', [ $this,'show_upgrade_message' ] );
		        }
	        }
		}

        /**
         * Check the required versions for NextCellent (WP and PHP).
         *
         * @todo These messages are displayed before the text domain has been loaded, so we currently cannot translate them.
         *
         * @return bool True if the versions are OK, false otherwise.
         */
        private function required_version() {
			global $wp_version;

	        //Check PHP
	        if(!version_compare(phpversion(), self::MINIMUM_PHP, '>=')) {
		        $minimum_php = self::MINIMUM_PHP;
		        add_action('admin_notices', function() use($minimum_php) {
			        echo "<div class='error'><p>NextCellent requires PHP <strong>$minimum_php</strong> or higher.</p></div>";
		        });
		        return false;
	        }

	        //Check WordPress
	        if( !version_compare($wp_version, self::MINIMUM_WP, '>=')) {
		        $minimum_wp = self::MINIMUM_WP;
		        add_action('admin_notices', function() use($minimum_wp) {
			        echo "<div class='error'><p>NextCellent requires WordPress <strong>$minimum_wp</strong> or higher.</p></div>";
		        });
		        return false;
	        }

			return true;
		}

	    /**
	     * Define the NextCellent database tables.
	     */
	    private function define_tables() {
		    global $wpdb;

		    // add database pointer
		    $wpdb->nggpictures = $wpdb->prefix . 'ngg_pictures';
		    $wpdb->nggallery   = $wpdb->prefix . 'ngg_gallery';
		    $wpdb->nggalbum    = $wpdb->prefix . 'ngg_album';
	    }


	    /**
	     * Register the taxonomy used by NextCellent.
	     *
	     * This function MUST be called during the init hook.
	     */
	    public function register_taxonomy() {
		    // Register the NextGEN taxonomy
		    $args = array(
			    'label'    => __( 'Picture tag', 'nggallery' ),
			    'template' => __( 'Picture tag: %2$l.', 'nggallery' ),
			    'helps'    => __( 'Separate picture tags with commas.', 'nggallery' ),
			    'sort'     => true,
			    'args'     => array( 'orderby' => 'term_order' )
		    );

		    register_taxonomy( 'ngg_tag', 'nggallery', $args );
	    }

	    /**
	     * Define constants. You should use the $ncg instance whenever possible.
	     */
        public function define_constant() {

	        //The NextCellent version. This is provided for easy access in other plugins.
	        define('NCG_VERSION', self::VERSION);
	        //The path to the NextCellent plugin folder.
	        define('NCG_PATH', wp_normalize_path(plugin_dir_path(__FILE__)));
	        //The path to the main NextCellent file, for easy access.
	        define('NCG_FILE_PATH', __FILE__);
	        //The name of the folder in which NextCellent is installed.
	        define('NCG_FOLDER', basename(__DIR__));
	        //The path to the folder.
	        define('NCG_USER_FOLDER_PATH', wp_normalize_path(WP_CONTENT_DIR . '/' . self::NCG_FOLDER . '/'));
	        //The URL to the NextCellent folder
	        define('NCG_URL', plugins_url('', __FILE__));
	        //The basename for this pluing, e.g. 'nextcellent/nggallery.php'
	        define('NCG_BASENAME', plugin_basename(__FILE__));
            //The absolute path to the wordpress install.
            define('NCG_ABSPATH', trailingslashit(wp_normalize_path(ABSPATH)));
	        
	        /**
	         * The other constants are kept for compatibility reasons.
	         */
	        /**
	         * @deprecated
	         */
			define('NGG_DBVERSION', self::DB_VERSION);
	        /**
	         * @deprecated
	         */
			define('WINABSPATH', str_replace("\\", "/", ABSPATH) );
	        /**
	         * @deprecated
	         */
			define('NGGFOLDER', basename( dirname(__FILE__) ) );
	        /**
	         * @deprecated
	         */
			define('NGGALLERY_ABSPATH', trailingslashit( str_replace("\\","/", WP_PLUGIN_DIR . '/' . NGGFOLDER ) ) );
	        /**
	         * @deprecated
	         */
			define('NGGALLERY_URLPATH', trailingslashit( plugins_url( NGGFOLDER ) ) );
		}

	    /**
	     * Load libraries
	     *
	     * @todo We should use an autoloader at some point.
	     */
	    private function load_dependencies() {

		    //Include the auto loader
		    require( __DIR__ . '/src/autoloader.php' );

		    //Include the autoloader for the src folder.
		    self::normal_autoloader();

			//Include the autoloader for the admin folder.
		    self::admin_autoloader();

		    //Include utils
		    require_once( __DIR__ . '/src/ncg-utils.php' );
		    
		    //Include file utils
            require_once(__DIR__ . '/src/files/utils.php');
		    require_once(__DIR__ . '/src/files/common.php');
		    require_once(__DIR__ . '/src/rendering/css.php');

		    // Load global libraries
		    require_once( __DIR__ . '/lib/core.php' );
		    require_once( __DIR__ . '/lib/ngg-db.php' );
		    require_once( __DIR__ . '/lib/image.php' );
		    require_once( __DIR__ . '/lib/tags.php' );
		    require_once( __DIR__ . '/lib/post-thumbnail.php' );
		    require_once( __DIR__ . '/lib/multisite.php' );
		    require_once( __DIR__ . '/lib/sitemap.php' );

		    // Load frontend libraries
		    require_once( __DIR__ . '/lib/navigation.php' );
		    require_once(__DIR__ . '/src/rendering/displays.php');
		    require_once( __DIR__ . '/nggfunctions.php' );

		    //Just needed if you access remote to WordPress
		    if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			    require_once( __DIR__ . '/lib/xmlrpc.php' );
		    }

		    //We don't need all things when doing AJAX.
		    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			    require_once( __DIR__ . '/admin/ajax.php' );
		    } else {
			    require_once( __DIR__ . '/lib/meta.php' );
			    require_once( __DIR__ . '/lib/media-rss.php' );
			    require_once( __DIR__ . '/lib/rewrite.php' );
			    require_once( __DIR__ . '/admin/tinymce/tinymce.php' );
			    if(is_admin()) {
				    //require_once( __DIR__ . '/admin/class-launcher.php' );
				    require_once( __DIR__ . '/admin/media-upload.php' );
			    }
		    }
	    }

	    /**
	     * Register the normal autoloader.
	     */
	    public static function normal_autoloader() {
		    $autoloader = new Autoloader('NextCellent\\',  __DIR__ . '/src/' );
		    $autoloader->register();
	    }

	    /**
	     * Register the autoloader for the admin part.
	     */
	    public static function admin_autoloader() {
		    $admin_loader = new Autoloader('NextCellent\\Admin', __DIR__ . '/admin/' );
		    $admin_loader->register();
	    }

	    /**
	     * Load the scripts NextCellent needs (for the frontend).
	     */
        public function load_scripts() {

	        /**
	         * If you need to prevent the scripts from loading, define NCG_SKIP_LOAD_SCRIPTS.
	         * NGG_SKIP_LOAD_SCRIPTS is only kept for compatibility reasons and will be removed in the future.
	         */
			if ( defined('NGG_SKIP_LOAD_SCRIPTS') || defined('NCG_SKIP_LOAD_SCRIPTS')) {
				return;
			}

	        $options = $this->options;

			//Add thickbox if necessary.
			if ($options['thumbEffect'] == 'thickbox') {
				wp_enqueue_script( 'thickbox' );
				// Load the thickbox images after all other scripts
				//TODO: this is ugly.
				add_action( 'wp_footer', function () {
					echo "\n" . '<script type="text/javascript">tb_pathToImage = "' . site_url() . '/wp-includes/js/thickbox/loadingAnimation.gif";tb_closeImage = "' . site_url() . '/wp-includes/js/thickbox/tb-close.png";</script>' . "\n";
				}, 11 );

			}
			//We need jquery for the lightbox.
			else if ($options['thumbEffect'] == 'lightbox') {
				wp_enqueue_script('jquery');
			}
	        //Do the modified Shutter reloaded effect.
	        else if ( ($this->options['thumbEffect'] == "shutter") && !function_exists('srel_makeshutter') ) {
				wp_register_script('shutter', plugins_url('shutter/shutter-reloaded.js', __FILE__), false ,'1.3.3');
				wp_localize_script('shutter', 'shutterSettings', array(
							'msgLoading' => __('LOADING', 'nggallery'),
							'msgClose' => __('Click to Close', 'nggallery'),
							'imageCount' => '1'
				) );
				wp_enqueue_script( 'shutter' );
			}

			//Files for the slideshow
	        wp_enqueue_script('owl', plugins_url('plugins/owl/owl.carousel.min.js', __FILE__), array('jquery'), '2');

			//Load AJAX navigation script, works only with shutter script as we need to add the listener
	        if ( $options['galAjaxNav'] && ($options['thumbEffect'] == "shutter" || function_exists( 'srel_makeshutter' ) ) ) {
		        wp_enqueue_script( 'ngg_script', plugins_url( 'js/ngg.js', __FILE__ ), array( 'jquery', 'jquery-ui-tooltip' ), '2.1' );
		        wp_localize_script( 'ngg_script', 'ngg_ajax', array(
			        'path'     => NCG_URL,
			        'callback' => trailingslashit( home_url() ) . 'index.php?callback=ngg-ajax',
			        'loading'  => __( 'loading', 'nggallery' ),
		        ) );
	        }

			//If activated, add PicLens/Cooliris javascript to footer
			if ( $options['usePicLens'] ) {
				nggMediaRss::add_piclens_javascript();
			}

	        // Added Qunit for javascript unit testing
	        $nxc = isset( $_GET['nextcellent'] ) ? $_GET['nextcellent'] : "";
	        if ( $nxc ) {
		        wp_enqueue_script( "qunit-init", plugins_url('js/nxc.main.js', __FILE__), array( 'jquery' ) ); //main q-unit call
		        wp_enqueue_script( "qunit", plugins_url('js/qunit-1.16.0.js', __FILE__), array( 'jquery' ) ); //qunit core
		        wp_enqueue_script( "nextcellent-test", plugins_url('js/nxc.test.js', __FILE__), array( 'jquery' ) ); //unit testing specific for nextcellent
	        }
		}

	    /**
	     * Load the NextCellent styles.
	     */
		public function load_styles() {

			$options = $this->options;

			//Register some styles.
			wp_register_style('animate', plugins_url('css/animate.css', __FILE__), [], '3.5.2', 'screen');
			wp_register_style('owl', plugins_url('plugins/owl/assets/owl.carousel.min.css', __FILE__), ['animate'], '2', 'screen');
			
			//Include the slideshow style.
			wp_enqueue_style('owl');
			
			//Include the general style.
			$file = \NextCellent\Rendering\Css\getCssFile();
			if($file !== null) {
				wp_enqueue_style('ncg-style', $file, [], self::VERSION, 'screen');
			}

			//	activate Thickbox
			if ($options['thumbEffect'] == 'thickbox') {
				wp_enqueue_style( 'thickbox');
			}

			// activate modified Shutter reloaded if not use the Shutter plugin
			if ( $this->options['thumbEffect'] == 'shutter' && !function_exists('srel_makeshutter') ) {
				wp_enqueue_style( 'shutter', plugins_url( 'shutter/shutter-reloaded.css', __FILE__ ), false, '1.3.4', 'screen' );
			}
		}

	    /**
	     * Activate NextCellent on new blogs if necessary.
	     *
	     * @author Shiba
	     * @link http://shibashake.com/wordpress-theme/write-a-plugin-for-wordpress-multi-site
	     * @param $blog_id
	     */
		public function multisite_new_blog( $blog_id ) {
			global $wpdb;

			include_once( dirname( __FILE__ ) . '/admin/installer.php' );

			if (is_plugin_active_for_network( NCG_BASENAME )) {
				$current_blog = $wpdb->blogid;
				switch_to_blog($blog_id);
				NextCellent\Admin\Installer::install();
				switch_to_blog($current_blog);
			}
		}

		/**
		 * Removes all transients created by NextCellent. Called during activation
		 * and deactivation routines.
		 *
		 * This function is static and public because we need it during uninstall.
		 */
		public static function remove_transients()
		{
			global $wpdb, $_wp_using_ext_object_cache;

			// Fetch all transients
			$query = "
				SELECT option_name FROM {$wpdb->options}
				WHERE option_name LIKE '%ngg_request%'
			";
			$transient_names = $wpdb->get_col($query);;

			// Delete all transients in the database
			$query = "
				DELETE FROM {$wpdb->options}
				WHERE option_name LIKE '%ngg_request%'
			";
			$wpdb->query($query);

			// If using an external caching mechanism, delete the cached items
			if ($_wp_using_ext_object_cache) {
				foreach ($transient_names as $transient) {
					wp_cache_delete($transient, 'transient');
					wp_cache_delete(substr($transient, 11), 'transient');
				}
			}
		}

	    /**
	     * Runs when NextCellent is activated.
	     */
	    public function activate() {
		    global $wpdb;

		    // Clean up transients
		    self::remove_transients();

		    require_once( __DIR__ . '/admin/installer.php' );

		    if ( is_multisite() ) {
			    $network       = isset( $_SERVER['SCRIPT_NAME'] ) ? $_SERVER['SCRIPT_NAME'] : "";
			    $activate      = isset( $_GET['action'] ) ? $_GET['action'] : "";
			    $is_network    = ( $network == '/wp-admin/network/plugins.php' ) ? true : false;
			    $is_activation = ( $activate == 'deactivate' ) ? false : true;

			    if ( $is_network and $is_activation ) {
				    $old_blog = $wpdb->blogid;
				    $blogids  = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs", null ) );
				    foreach ( $blogids as $blog_id ) {
					    switch_to_blog( $blog_id );
					    NextCellent\Admin\Installer::install();
				    }
				    switch_to_blog( $old_blog );

				    return;
			    }
		    }

		    // check for tables
		    NextCellent\Admin\Installer::install();

		    //Flush the rewrite rules
		    flush_rewrite_rules();
	    }

        /**
         * delete init options and transients
         */
        public function deactivate() {
			// Clean up transients
			self::remove_transients();
		}

		// Add links to Plugins page
	    /**
	     * Add links on the plugin page.
	     *
	     * @param $links
	     * @param $file
	     *
	     * @return array
	     */
		public function add_plugin_links($links, $file) {

			if ( $file == NCG_BASENAME ) {
				$links[] = "<a href='admin.php?page=" . NCG_FOLDER . "'>" . __('Overview', 'nggallery') . '</a>';
				$links[] = '<a href="http://wordpress.org/support/plugin/nextcellent-gallery-nextgen-legacy">' . __('Get help', 'nggallery') . '</a>';
			}
			return $links;
		}
	}

	//Register an alias for backwards compatibility.
	class_alias('NCG', 'nggLoader');

	//Let's start the holy plugin.
	global $ncg;
	$ncg = new NCG();

	/**
	 * @deprecated Use $ncg instead.
	 */
	global $ngg;
	$ngg = $ncg;
}

/**
 * Checks if there is a NextGEN version running. If so, it deactivates itself
 */
function check_for_nextgen() {

	if (!function_exists('get_plugin_data')) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	$nextcellent_plugin= plugin_basename(__FILE__);

	$plugin_list = get_plugins();

	//Loop over all the active plugins
	foreach ( $plugin_list as $plugin_file => $plugin_data ) {
		//If we found nextcellent, skip it
		if ( $plugin_file == $nextcellent_plugin ) {
			continue;
		}
		//If the plugin is deactivated ignore it.
		if ( ! is_plugin_active( $plugin_file ) ) {
			continue;
		}
		if ( strpos( $plugin_file, 'nggallery.php' ) !== false ) {
			$version = $plugin_data['Version'];
			//Check if effectively could be nextgen
			$is_nextgen = ( strpos( strtolower( $plugin_data['Name'] ), 'nextgen' ) !== false );
			if ( $is_nextgen ) { //is it?
				//Yes, display msg on admin console
				add_action( 'admin_notices', function () use ( $version ) {
					echo '<div class="error"><p><strong>' . __( 'Sorry, NextCellent Gallery is deactivated: NextGEN version ' . $version . ' was detected. Deactivate it before running NextCellent!',
							'nggallery' ) . '</strong></p></div>';
				} );
				//Deactivate this plugin
				deactivate_plugins( $nextcellent_plugin );

				return true;
			}
		}
	}
	return false;
}