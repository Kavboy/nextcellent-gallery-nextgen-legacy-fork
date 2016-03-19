<?php

namespace NextCellent\Admin\Manage\Albums;

use NextCellent\Admin\Admin_Page;
use NextCellent\Admin\Roles;

/**
 * Class Gallery_Manager
 *
 * Display the gallery managing page.
 */
class Album_Manager extends Admin_Page {

	const NAME = 'manage-album';

	/**
	 * Display the page.
	 */
	public function display() {

		/**
		 * Display the actual table.
		 */
		$table = new Album_List_Table( $this->get_full_url() );
		$table->prepare_items();
		?>
		<div class="wrap">
			<h2><?php _e( 'Albums', 'nggallery' ); ?>
				<?php if ( current_user_can( Roles::MANAGE_ALBUMS ) ) { ?>
					<a class="add-new-h2" id="new-album" href="#"><?php _e( 'Add new album', 'nggallery' ) ?></a>
				<?php }; ?>
			</h2>

			<form method="post">
				<input type="hidden" id="page_type" name="page_type" value="album"/>
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * A possibility to add help to the screen.
	 *
	 * @param \WP_Screen $screen The current screen.
	 */
	public function add_help( $screen ) {
		add_filter( 'manage_' . $screen->id . '_columns', array( __NAMESPACE__ . '\\Album_List_Table', 'get_columns_static' ), 0 );
		$args = array(
			'label'   => __( 'Albums', 'nggallery' ),
			'default' => 25,
			'option'  => 'ngg_albums_per_page'
		);

		$screen->add_option( 'per_page', $args );
	}

	/**
	 * Enqueue/register the needed styles.
	 */
	public function register_styles() {
		// TODO: Implement register_styles() method.
	}

	/**
	 * Enqueue/register the needed scripts
	 */
	public function register_scripts() {
		// TODO: Implement register_scripts() method.
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
}