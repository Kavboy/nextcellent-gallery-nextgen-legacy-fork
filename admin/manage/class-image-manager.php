<?php

namespace NextCellent\Admin\Manage;

use NextCellent\Models\Gallery;
use NextCellent\Models\Image;

/**
 * Class Gallery_Manager
 *
 * Display the gallery managing page.
 */
class Image_Manager extends Abstract_Image_Manager {

	/**
	 * @var Gallery $gallery The gallery.
	 */
	private $gallery;
	private $id;

	/**
	 * @param string $slug The slug for this page. It is recommended you pass this parameter.
	 *                     For example, with slug 'nextcellent', the page is 'nextcellent-[NAME]'.
	 */
	public function __construct($slug) {
		parent::__construct($slug);
		$this->id = (int) $_GET['gid'];
	}

	/**
	 * Display the page.
	 */
	public function display() {

		parent::display();

		if ( isset( $_POST['form'] ) && $_POST['form'] === "gallery" ) {
			if (isset ($_POST['add-new-page'])) {
				$this->create_page();
			} else {
				$this->handle_update_gallery();
			}
		}

		if ( isset( $_POST['scan_folder'] ) ) {
			$this->handle_scan_folder();
		}

		if ( isset( $_GET['action'] ) ) {
			$this->handle_row_action();
		}

		if ( isset( $_GET['paged'] ) ) {
			$page = $_GET['paged'];
		} else {
			$page = '';
		}

		$this->gallery = Gallery::find($this->id);

		/**
		 * Display the actual table.
		 */
		$table = new Image_List_Table( $this->get_full_url(), $this->gallery );
		$table->prepare_items();
		?>
		<div class="wrap">
			<form id="update_gallery" class="nggform" method="post" action="<?php echo $this->get_full_url() . '&mode=image&gid=' . $this->id . '&paged=' . $page; ?>" accept-charset="utf-8">
				<?php wp_nonce_field( 'ngg-update-gallery', '_ngg_nonce_gallery' ); ?>
				<input type="hidden" name="form" value="gallery">
				<?php $this->print_gallery_overview( $table->items ) ?>
			</form>
			<!-- TODO Add a search inside a gallery form -->
			<form id="update_images" class="nggform" method="post" action="<?php echo $this->get_full_url() . '&mode=image&gid=' . $this->id . '&paged=' . $page; ?>" accept-charset="utf-8">
				<?php wp_nonce_field( 'ngg-update-images', '_ngg_nonce_images' ); var_dump("OK"); ?>
				<input type="hidden" id="page_type" name="page_type" value="image">
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
		$this->print_dialogs();
		$this->print_scripts();
	}

	/**
	 * @todo Make this better.
	 */
	protected function print_scripts() {
		parent::print_scripts();
		?>
		<script type="text/javascript">
			/**
			 * Confirm the scan operation.
			 */
			jQuery("#scan_folder").click(function() {
				return confirm(
					"<?php _e( 'This will change folder and file names (e.g. remove spaces, special characters, ...)', 'nggallery' ) ?>" +
					"\n\n" +
					"<?php _e( 'You will need to update your URLs if you link directly to the images.', 'nggallery' ) ?>" +
					"\n\n" +
					"<?php _e( 'Press OK to proceed, and Cancel to stop.', 'nggallery' ) ?>"
				);
			});

			/**
			 * For the row actions.
			 */
			jQuery(".confirm_recover").click(function() {
				var fileName = jQuery(this).data('file');
				return confirm( '<?php _e( 'Recover "{}"?', 'nggallery' ) ?>'.replace('{}', fileName));
			});

			jQuery(".confirm_delete").click(function() {
				var fileName = jQuery(this).data('file');
				return confirm( '<?php _e( 'Delete "{}"?', 'nggallery' ) ?>'.replace('{}', fileName));
			});

			/**
			 * Redirect to the sorting UI.
			 */
			jQuery("#sort_gallery").click(function() {
				location.href = "<?php echo esc_js($this->get_full_url()) . '&mode=sort&gid=' . $this->id ?>";
			});
		</script>

		<?php
	}

	/**
	 * Handle a request to scan the folder for new images.
	 */
	private function handle_scan_folder() {
		if ( wp_verify_nonce( $_POST['_ngg_nonce_gallery'], 'ngg-update-gallery' ) === false ) {
			\nggGallery::show_error( __( 'You waited too long, or you cheated.', 'nggallery' ) );

			return;
		}

		global $wpdb;

		$gallery_path = $wpdb->get_var( $wpdb->prepare( "SELECT path FROM $wpdb->nggallery WHERE gid = %d",
			$this->id ) );
		\nggAdmin::import_gallery( $gallery_path );
	}

	/**
	 * @param Image[] $images The images.
	 */
	private function print_gallery_overview( $images ) {

		global $ncg;

		$disabled = $title = "";
		$options  = $ncg->options;

		if ( $options['galSort'] != "sortorder" ) {
			//Disable sort button and provide feedback why is disabled
			$disabled = "disabled ";
			$title    = "title='" . __( 'To enable manual Sort set Custom Order Sort.See Settings->Gallery Settings->Sort Options',
					'nggallery' ) . "'";
		}
		?>
		<h2><?php _e( 'Gallery', 'nggallery' ) ?><?php esc_html_e( $this->gallery->title ) ?></h2>
		<?php if ( \nggGallery::current_user_can( 'NextGEN Edit gallery options' ) ) { ?>
			<div id="poststuff">
				<div id="gallerydiv" class="postbox <?php echo postbox_classes( 'gallery_div', 'ngg-manage' ); ?>">
					<h3 class="hndle"><?php _e( 'Gallery settings', 'nggallery' ) ?></h3>

					<div class="inside">
						<table class="form-table" id="gallery-properties">
							<tr>
								<td align="left"><label for="title"><?php _e( 'Title' ) ?></label></td>
								<td align="left">
									<input type="text" id="title" name="title" class="regular-text" value="<?php esc_attr_e( $this->gallery->title ) ?>"/>
								</td>
								<td align="right"><label for="page_id"><?php _e( 'Page Link', 'nggallery' ) ?></label>
								</td>
								<td align="left">
									<select id="page_id" name="page_id">
										<option value="0"><?php _e( 'Not linked', 'nggallery' ) ?></option>
										<?php parent_dropdown( $this->gallery->page_id ); ?>
									</select>
								</td>
							</tr>
							<tr>
								<td align="left"><label for="gallery_desc"><?php _e( 'Description' ) ?></label></td>
								<td align="left">
									<textarea name="gallery_desc" id="gallery_desc" cols="46" rows="3"><?php echo $this->gallery->description; ?></textarea>
								</td>
								<td align="right">
									<label for="preview_pic"><?php _e( 'Preview image', 'nggallery' ) ?></label>
								</td>
								<td align="left">
									<select name="preview_pic" id="preview_pic">
										<option value="0"><?php _e( 'No Picture', 'nggallery' ) ?></option>
										<?php
										// ensure that a preview pic from a other page is still shown here
										foreach ( $images as $picture ) {
											if ( $picture->exclude ) {
												continue;
											}
											$selected = ( $picture->id == $this->gallery->preview ) ? 'selected' : '';
											echo '<option value="' . $picture->id . '" ' . $selected . ' >' . $picture->id . ' - ' . esc_attr( $picture->filename ) . '</option>' . "\n";
										}

										?>
									</select>
								</td>
							</tr>
							<tr>
								<td align="left"><label for="path"><?php _e( 'Path', 'nggallery' ) ?></label></td>
								<td align="left">
									<input <?php if ( is_multisite() ) {
										echo 'readonly = "readonly"';
									} ?> type="text" name="path" class="regular-text code" id="path" value="<?php echo $this->gallery->path; ?>"/>
								</td>
								<td align="right"><label for="author"><?php _e( 'Author', 'nggallery' ); ?></label></td>
								<td align="left"><?php echo get_userdata( (int) $this->gallery->author )->display_name ?></td>
							</tr>
							<tr>
								<td align="left"><?php _e( 'Gallery ID', 'nggallery' ) ?>:</td>
								<td align="right"><?php echo $this->gallery->id; ?></td>
								<?php if ( current_user_can( 'publish_pages' ) ) { ?>
									<td align="right"><label for="parent_id"><?php _e( 'Create new page',
												'nggallery' ) ?></label></td>
									<td align="left">
										<select name="parent_id" id="parent_id">
											<option value="0"><?php _e( 'Main page (No parent)',
													'nggallery' ); ?></option>
											<?php if ( get_post() ) {
												parent_dropdown();
											} ?>
										</select>
										<input class="button-secondary action" type="submit" name="add-new-page" value="<?php _e( 'Add page',
											'nggallery' ); ?>" id="group"/>
									</td>
								<?php } ?>
							</tr>
							<?php do_action( 'ngg_manage_gallery_settings', $this->id ); ?>
						</table>
						<div class="submit">
							<button class='button-secondary' type='button' <?php echo $disabled, $title ?> id='sort_gallery'>
								<?php _e( 'Sort gallery', 'nggallery' ) ?>
							</button>
							<button type="submit" class="button-secondary" name="scan_folder" id="scan_folder">
								<?php _e( "Scan folder for new images", 'nggallery' ); ?>
							</button>
							<button type="submit" class="button-primary action">
								<?php _e( "Save Changes", 'nggallery' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div> <!-- poststuff -->
			<?php
		}
	}

	private function handle_update_gallery() {

		if ( wp_verify_nonce( $_POST['_ngg_nonce_gallery'], 'ngg-update-gallery' ) === false ) {
			\nggGallery::show_error( __( 'You waited too long, or you cheated.', 'nggallery' ) );

			return;
		}

		global $wpdb;

		if ( \nggGallery::current_user_can( 'NextGEN Edit gallery options' ) ) {

			if ( \nggGallery::current_user_can( 'NextGEN Edit gallery title' ) ) {
				// don't forget to update the slug
				$slug = \nggdb::get_unique_slug( sanitize_title( $_POST['title'] ), 'gallery', $this->id );
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->nggallery SET title= '%s', slug= '%s' WHERE gid = %d",
					esc_attr( $_POST['title'] ), $slug, $this->id ) );
			}
			if ( \nggGallery::current_user_can( 'NextGEN Edit gallery path' ) ) {
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->nggallery SET path= '%s' WHERE gid = %d",
					untrailingslashit( str_replace( '\\', '/', trim( stripslashes( $_POST['path'] ) ) ) ),
					$this->id ) );
			}
			if ( \nggGallery::current_user_can( 'NextGEN Edit gallery description' ) ) {
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->nggallery SET galdesc= '%s' WHERE gid = %d",
					esc_attr( $_POST['gallery_desc'] ), $this->id ) );
			}
			if ( \nggGallery::current_user_can( 'NextGEN Edit gallery page id' ) ) {
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->nggallery SET pageid= '%d' WHERE gid = %d",
					(int) $_POST['page_id'], $this->id ) );
			}
			if ( \nggGallery::current_user_can( 'NextGEN Edit gallery preview pic' ) ) {
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->nggallery SET previewpic= '%d' WHERE gid = %d",
					(int) $_POST['preview_pic'], $this->id ) );
			}
			if ( isset ( $_POST['author'] ) && \nggGallery::current_user_can( 'NextGEN Edit gallery author' ) ) {
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->nggallery SET author= '%d' WHERE gid = %d",
					(int) $_POST['author'], $this->id ) );
			}

			wp_cache_delete( $this->id, 'ngg_gallery' );

		}

		do_action( 'ngg_update_gallery', $this->id, $_POST );

		\nggGallery::show_message( __( 'Update successful', "nggallery" ) );

	}

	private function handle_row_action() {

		check_admin_referer('ngg_row_action');

		/**
		 * @global \nggdb $nggdb
		 */
		global $nggdb;

		// Delete a picture
		if ( $_GET['action'] == 'delete' ) {

			$pid = (int) $_GET['pid'];
			$options = get_option( 'ngg_options' );

			//TODO:Remove also Tag reference
			$image = $nggdb->find_image( $pid );
			if ($image) {
				if ($options['deleteImg']) {
					@unlink($image->imagePath);
					@unlink($image->thumbPath);
					@unlink($image->imagePath . '_backup' );
				}
				do_action('ngg_delete_picture', $pid);
				$result = \nggdb::delete_image ( $pid );
			} else {
				$result = false;
			}

			if ($result) {
				\nggGallery::show_message(
					sprintf( __( 'Picture %d deleted successfully.', 'nggallery' ), $pid )
				);
			} else {
				\nggGallery::show_error(
					sprintf( __( 'Picture %d could not be deleted.', 'nggallery' ), $pid )
				);
			}

			return;
		}

		// Recover picture from backup
		if ( $_GET['action'] == 'recover' ) {

			$image = $nggdb->find_image( (int) $_GET['pid'] );
			// bring back the old image
			\nggAdmin::recover_image( $image );
			\nggAdmin::create_thumbnail( $image );

			\nggGallery::show_message( __( 'Operation successful. Please clear your browser cache.', "nggallery" ) );

			return;
		}
	}

	/**
	 * Create a page with the same title as the current gallery, and include a shortcode to this
	 * gallery.
	 */
	private function create_page()
	{
		if ( wp_verify_nonce( $_POST['_ngg_nonce_gallery'], 'ngg-update-gallery' ) === false ) {
			\nggGallery::show_error( __( 'You waited too long, or you cheated.', 'nggallery' ) );

			return;
		}

		global $wpdb;

		$parent_id      = esc_attr($_POST['parent_id']);
		$gallery_title  = esc_attr($_POST['title']);
		$gallery_name   = $wpdb->get_var("SELECT name FROM $wpdb->nggallery WHERE gid = '$this->id' ");

		// Create a WP page
		global $user_ID;

		$page['post_type']    = 'page';
		$page['post_content'] = '[nggallery id=' . $this->id . ']';
		$page['post_parent']  = $parent_id;
		$page['post_author']  = $user_ID;
		$page['post_status']  = 'publish';
		$page['post_title']   = $gallery_title == '' ? $gallery_name : $gallery_title;
		$page = apply_filters('ngg_add_new_page', $page, $this->id);

		$gallery_pageid = wp_insert_post ($page);
		if ($gallery_pageid != 0) {
			$result = $wpdb->query("UPDATE $wpdb->nggallery SET title= '$gallery_title', pageid = '$gallery_pageid' WHERE gid = '$this->id'");
			wp_cache_delete($this->id, 'ngg_gallery');
			\nggGallery::show_message( sprintf( __( 'New page <strong>%s</strong> (ID: %s) created.','nggallery'),  $gallery_title, $gallery_pageid ));
		}

		do_action('ngg_gallery_addnewpage', $this->gid);
	}

	/**
	 * A possibility to add help to the screen.
	 *
	 * @param \WP_Screen $screen The current screen.
	 */
	public function add_help( $screen ) {
		add_filter( 'manage_' . $screen->id . '_columns',
			array( 'Image_List_Table', 'get_columns_static' ), 0 );
		$args = array(
			'label'   => __( 'Images', 'nggallery' ),
			'default' => 50,
			'option'  => 'ngg_images_per_page'
		);

		$help = '<p>' . __( 'This box contains information and the various options a gallery had.', 'nggallery') . '</p>';

		$screen->add_help_tab( array(
			'id'      => $screen->id . '-general',
			'title'   => __( 'Overview', 'nggallery'),
			'content' => $help
		) );

		$help = '<p>' . __( 'Manage a single gallery and the images it contains:', 'nggallery' ) . '</p>';
		$help .= '<dl class="ncg-dl">';

		$help .= '<dt>' . __( 'Title', 'ngallery') . '</dt>';
		$help .= '<dd>' . __( 'The title of the gallery. This can be visible to the users of the website. This has no effect on the gallery path.', 'nggallery') .'</dd>';

		$help .= '<dt>' . __( 'Description', 'ngallery') . '</dt>';
		$help .= '<dd>' . __( 'The description of the gallery. Albums using the "extend" template may display this on the website. The description cannot contain HTML.', 'nggallery') .'</dd>';

		$help .= '<dt>' . __( 'Path', 'ngallery') . '</dt>';
		$help .= '<dd>' . __( 'The path on the server to the folder containing this gallery. If you change this, NextCellent will not move the gallery for you.', 'nggallery') .'</dd>';

		$help .= '<dt>' . __( 'Gallery ID', 'ngallery') . '</dt>';
		$help .= '<dd>' . __( 'The internal ID used by NextCellent to represent this gallery. This information can be useful for developers. A gallery ID should never change.', 'nggallery') .'</dd>';

		$help .= '<dt>' . __( 'Page Link', 'ngallery') . '</dt>';
		$help .= '<dd>' . __( 'With this option you can select the behavior when an user clicks on a gallery in an album. If the option is set to "not linked", the gallery will be displayed on the same page. If you do select a page, the user will be redirected to that page.', 'nggallery');
		$help .= ' '. sprintf( __( 'More information about this is available on this webpage: %s', 'nggallery'), '<a target="_blank" href="http://www.nextgen-gallery.com/link-to-page/">' . __('page', 'nggallery') . '</a>') . '</dd>';

		$help .= '<dt>' . __( 'Preview image', 'ngallery') . '</dt>';
		$help .= '<dd>' . __( 'This image will be shown when the gallery is shown on the website and it needs a preview, e.g. an album. If you do not select a preview image, NextCellent will use the last uploaded image of the gallery.', 'nggallery') .'</dd>';

		$help .= '<dt>' . __( 'Author', 'ngallery') . '</dt>';
		$help .= '<dd>' . __( 'The user who created this gallery.', 'nggallery') .'</dd>';

		$help .= '<dt>' . __( 'Create new page', 'ngallery') . '</dt>';
		$help .= '<dd>' . __( 'This will create a new page with the same name as the gallery, and include a shortcode for this gallery in it.', 'nggallery') .'</dd>';
		$help .= '</dl>';

		$screen->add_help_tab( array(
			'id'      => $screen->id . '-options',
			'title'   => __( 'Gallery settings', 'nggallery'),
			'content' => $help
		) );

		$help = '<p>' . __( 'There are three buttons:', 'nggallery') . '</p>';
		$help .= '<dl class="ncg-dl">';

		$help .= '<dt>' . __( 'Sort gallery', 'ngallery') . '</dt>';
		$help .= '<dd>' . __( 'Allows you to manually set the order of the images in the gallery. This will only be enabled if you have selected the option "Custom sort order" in the NextCellent settings.', 'nggallery') .'</dd>';

		$help .= '<dt>' . __( 'Scan folder for new images', 'ngallery') . '</dt>';
		$help .= '<dd>' . __( 'Scan the folder (the path of the gallery) for new images and add them to the gallery. <strong>Warning!</strong> This will normalize and rename the images that are added, e.g. spaces are removed.', 'nggallery') .'</dd>';

		$help .= '<dt>' . __( 'Save', 'ngallery') . '</dt>';
		$help .= '<dd>' . __( 'Save changes you have made to the gallery options.', 'nggallery') .'</dd>';

		$help .= '</dl>';

		$screen->add_help_tab( array(
			'id'      => $screen->id . '-buttons',
			'title'   => __( 'Buttons', 'nggallery'),
			'content' => $help
		) );

		$screen->add_option( 'per_page', $args );
	}
}