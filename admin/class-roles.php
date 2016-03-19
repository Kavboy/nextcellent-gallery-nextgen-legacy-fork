<?php

namespace NextCellent\Admin;


/**
 * The roles admin screen. This is also the class that generally manages the roles and capabilities.
 */
class Roles extends Admin_Page {
	
	const NAME = 'roles';

	//View the admin dashboard.
	const VIEW_ADMIN_PAGES = 'NextGEN Gallery overview';
	/**
	 * @deprecated This role is no longer used.
	 */
	const USE_NEXTCELLENT_IMAGES = 'NextGEN Use TinyMCE';
	//Upload new images.
	//Note that this does not mean you can create new galleries.
	const UPLOAD_IMAGES = 'NextGEN Upload images';
	//Create and manage your own galleries.
	const MANAGE_GALLERIES = 'NextGEN Manage gallery';
	//Create and manage tags.
	const MANAGE_TAGS = 'NextGEN Manage tags';
	//Manage other's galleries.
	const MANAGE_ALL_GALLERIES = 'NextGEN Manage others gallery';
	//Create and manage albums.
	const MANAGE_ALBUMS = 'NextGEN Edit album';
	//Manage the style option for NextCellent.
	const MANAGE_STYLE = 'NextGEN Change style';
	//Manage the NextCellent options.
	const MANAGE_OPTIONS = 'NextGEN Change options';
	//Network options. This currently not assigned, since super users always have access.
	const MANAGE_NETWORK_OPTIONS = 'nggallery-wpmu';

	public function display() {

		?>
		<div class="wrap">
			<h1><?php _e('Capabilities', 'nggallery') ;?></h1>
			<p class="wp-ui-text-notification">
				<?php _e('Warning: this page is deprecated and will be removed in a future version of NextCellent.', 'nggallery') ?>
			</p>
			<p>
				<?php _e('Maintaining our own system to manage the capabilities is now something we will not do anymore.', 'nggallery') ?> <br>
				<?php _e('While this is not something we are happy about, it was necessary: the old system did not work well, and rewriting it correctly is a lot of work.', 'nggallery') ?><br>
				<?php _e("This way, we can concentrate on what's important: the rest of the plugin.", 'nggallery') ?>
			</p>
			<p>
				<?php printf( __('We recommend using <a href="%s">this plugin</a> to edit roles, but other plugins work just as well.', 'nggallery'), 'https://wordpress.org/plugins/wpfront-user-role-editor/') ?>
			</p>
		</div>
		<?php
	}

	function register_styles() {
		wp_enqueue_style( 'nggadmin' );
	}

	function register_scripts() {
		//None.
	}

	/**
	 * A possibility to add help to the screen.
	 *
	 * @param \WP_Screen $screen The current screen.
	 */
	public function add_help( $screen ) {
		$help = '<p>' . __( 'This page is deprecated and will be removed in the future. Please use another plugin to manage the roles.',
				'nggallery' ) . '</p>';

		$screen->add_help_tab( array(
			'id'      => $screen->id . '-general',
			'title'   => 'Grant permissions',
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
	 * This function will add all NextCellent capabilities to a role.
	 */
	public static function install_roles() {

		/**
		 * @var \WP_Role $admin
		 * @var \WP_Role $editor
		 * @var \WP_Role $author
		 */
		$admin = get_role( 'administrator' );
		$editor = get_role( 'editor' );
		$author = get_role( 'author' );

		if ( is_null( $admin ) ) {
			update_option( "ngg_init_check", __( 'Sorry, NextCellent requires a role called administrator.', "nggallery" ) );
			return;
		}

		//The admin can do anything.
		$admin->add_cap( self::VIEW_ADMIN_PAGES );
		$admin->add_cap( self::UPLOAD_IMAGES );
		$admin->add_cap( self::MANAGE_GALLERIES );
		$admin->add_cap( self::MANAGE_ALL_GALLERIES );
		$admin->add_cap( self::MANAGE_TAGS );
		$admin->add_cap( self::MANAGE_ALBUMS );
		$admin->add_cap( self::MANAGE_STYLE );
		$admin->add_cap( self::MANAGE_OPTIONS );

		//The editor can manage all content and use content.
		if ( $editor != null ) {
			$editor->add_cap( self::VIEW_ADMIN_PAGES );
			$editor->add_cap( self::UPLOAD_IMAGES );
			$editor->add_cap( self::MANAGE_GALLERIES );
			$editor->add_cap( self::MANAGE_ALL_GALLERIES );
			$editor->add_cap( self::MANAGE_TAGS );
			$editor->add_cap( self::MANAGE_ALBUMS );
		}

		//The author and contributor can manage his own content and use content.
		if ( $author != null ) {
			$author->add_cap( self::UPLOAD_IMAGES );
			$author->add_cap( self::MANAGE_GALLERIES );
		}
	}
}