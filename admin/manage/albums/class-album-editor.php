<?php

namespace NextCellent\Admin\Manage\Albums;

use NextCellent\Admin\Admin_Page;

class Album_Editor extends Admin_Page {

	const NAME = 'manage-album';

	/**
	 * @var array All galleries
	 */
	private $galleries;

	/**
	 * @var array All albums
	 */
	private $albums;

	/**
	 * @var int Number of galleries
	 */
	private $gallery_count;

	/**
	 * @var int Number of albums
	 */
	private $album_count;

	/**
	 * @var int The current album.
	 */
	private $id;

	/**
	 * @var \stdClass $album The current album
	 */
	private $album;

	public function __construct() {
		$this->id = (int) $_GET['id'];
	}

	public function display() {

		/**
		 * @global \nggdb $nggdb
		 */
		global $nggdb;
		
		if ( isset ( $_POST['update'] ) ) {
			$this->processor();
		}

		//Add the post box
		add_meta_box( 'album-properties', __( 'Album properties', 'nggallery' ), array( $this, 'album_properties' ), null, 'normal');

		// get first all galleries & albums
		$this->albums        = $nggdb->find_all_album();
		$this->galleries     = $nggdb->find_all_galleries();
		$this->album_count   = count( $this->albums );
		$this->gallery_count = count( $this->galleries );
		$this->album = $this->albums[$this->id];

		$this->output();
	}

	/**
	 * Handle the updates.
	 */
	private function processor() {
		global $wpdb;

		wp_verify_nonce( 'ncg_update_album' );

		$gid = array();

		// get variable galleryContainer
		parse_str( $_POST['contents'], $gid );

		if ( is_array( $gid ) ) {
			$serial_sort = serialize( $gid['gid'] );

		} else {
			$serial_sort = 0;
		}

		$name = $_POST['album_name'];
		$desc = $_POST['album_desc'];
		$prev = (int) $_POST['preview_picture'];
		$link = (int) $_POST['page_id'];

		// slug must be unique, we use the title for that
		$slug = \nggdb::get_unique_slug( sanitize_title( $name ), 'album', $this->id );

		$wpdb->update( $wpdb->nggalbum, array(
			'sortorder'  => $serial_sort,
			'slug'       => $slug,
			'name'       => $name,
			'albumdesc'  => $desc,
			'previewpic' => $prev,
			'pageid'     => $link
		), array(
			'id' => $this->id
		) );

		//hook for other plugin to update the fields
		do_action( 'ngg_update_album', $this->id, $_POST );
		/**
		 * @deprecated Use 'ngg_update_album' instead.
		 */
		do_action( 'ngg_update_album_sortorder', $this->id );

		\NextCellent\show_success(__( 'Updated successfully', 'nggallery' ));
	}

	/**
	 * Display the output.
     */
	private function output() {

		//TODO:Code MUST be optimized, how to flag a used gallery better?
		$used_list = $this->get_used_galleries();

		?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				var selectContainer = jQuery('#selectContainer');

				selectContainer.sortable({
					items: '.groupItem',
					placeholder: 'sort_placeholder',
					opacity: 0.7,
					tolerance: 'intersect',
					distance: 2,
					forcePlaceholderSize: true,
					connectWith: ['#galleryContainer']
				});

				jQuery('#galleryContainer').sortable({
					items: '.groupItem',
					placeholder: 'sort_placeholder',
					opacity: 0.7,
					tolerance: 'intersect',
					distance: 2,
					forcePlaceholderSize: true,
					connectWith: ['#selectContainer', '#albumContainer']
				});

				jQuery('#albumContainer').sortable({
					items: '.groupItem',
					placeholder: 'sort_placeholder',
					opacity: 0.7,
					tolerance: 'intersect',
					distance: 2,
					forcePlaceholderSize: true,
					connectWith: ['#galleryContainer']
				});

				var min = jQuery('span.icon');

				min.click(toggleContent);

				// Hide used galleries
				jQuery('a#toggle_used').click(function() {
						selectContainer.find('div.inUse').toggle();
						return false;
					}
				);

				// Maximize All Portlets (whole site, no differentiation)
				jQuery('a#toggle').click(function(e) {
						min.trigger('click');
						e.preventDefault();
					}
				);

				// Auto Minimize if more than 4 (whole site, no differentiation)
				if (min.length > 4) {
					min.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
					jQuery('div.itemContent:visible').hide();
					selectContainer.find('div.inUse').toggle();
				}

				postboxes.add_postbox_toggles();

				jQuery('#update_album').submit(ngg_serialize);
			});

			var toggleContent = function() {
				var targetContent = jQuery('div.itemContent', this.parentNode.parentNode);
				if (targetContent.css('display') == 'none') {
					targetContent.slideDown(100);
					jQuery(this).removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
				} else {
					targetContent.slideUp(100);
					jQuery(this).removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
				}
				return false;
			};

			function ngg_serialize() {
				var serial = jQuery('#galleryContainer').sortable('serialize');
				jQuery('input[name=contents]').val(serial);
			}
		</script>
		<div class="wrap">
			<h1><?php printf( __( 'Album %s', 'nggallery' ), esc_attr($this->album->name)) ?></h1>
			<form id="update_album" method="post" action="<?php echo $this->get_full_url() . '&mode=edit&id=' . $this->id ?>" accept-charset="utf-8">
				<?php wp_nonce_field( 'ncg_update_album' ); ?>
				<input type="hidden" name="update" value="update">
				<input type="hidden" name="contents" value="">
				<div id="poststuff">
					<?php do_meta_boxes( get_current_screen(), 'normal', null ); ?>
				</div>
			</form>
			<div style="float:right;">
				<a href="#" title="<?php _e( 'Show / hide used galleries', 'nggallery' ) ?>" id="toggle_used"><?php _e( '[Show all]', 'nggallery' ) ?></a>
				| <a href="#" title="<?php _e( 'Show / hide the content', 'nggallery' ) ?>" id="toggle"><?php _e( '[Toggle]', 'nggallery' ) ?></a>
			</div>
			<p>
				<?php _e( 'After you create and select a album, you can drag and drop a gallery or another album into your new album below.', 'nggallery' ) ?>
			</p>

			<div class="container">
				<div class="widget widget-right">
					<div class="widget-top">
						<h3><?php _e( 'Albums', 'nggallery' ); ?></h3>
					</div>
					<div id="albumContainer" class="widget-holder">
						<?php
							foreach ( $this->albums as $album ) {
								$this->get_container( 'a' . $album->id );
							}
						?>
					</div>
				</div>

				<div class="widget widget-right">
					<div class="widget-top">
						<h3><?php esc_html_e( 'Galleries', 'nggallery' ); ?></h3>
					</div>
					<div id="selectContainer" class="widget-holder">
						<?php
							//get the array of galleries
							$sort_array = $this->id > 0 ? (array) $this->albums[ $this->id ]->galleries : array();
							foreach ( $this->galleries as $gallery ) {
								if ( ! in_array( $gallery->gid, $sort_array ) ) {
									if ( in_array( $gallery->gid, $used_list ) ) {
										$this->get_container( $gallery->gid, true );
									} else {
										$this->get_container( $gallery->gid, false );
									}
								}
							}
						?>
					</div>
				</div>

				<div class="widget target-album widget-left">
					<div class="widget-top">
						<h3><?php _e( 'Album Contents', 'nggallery' ) ?> </h3>
					</div>
					<div id="galleryContainer" class="widget-holder target">
						<?php
						$sort_array = (array) $this->albums[ $this->id ]->galleries;

						foreach ( $sort_array as $id ) {
							$this->get_container( $id, false );
						}
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Create the album or gallery container
	 *
	 * @param string|int $id The ID. If it is prefixed with 'a', it is an album.
	 * @param bool $used  (object will be hidden)
	 */
	private function get_container( $id = 0, $used = false ) {

		/**
		 * @global \nggdb $nggdb
		 */
		global $nggdb;

		$preview_image = '';

		// if the id started with a 'a', then it's a sub album
		if ( is_string($id) && substr( $id, 0, 1 ) === 'a' ) {

			$id = (int) substr( $id, 1 );

			if ( !array_key_exists($id, $this->albums)) {
				return;
			}

			$album = $this->albums[$id];
			$name = $title = $album->name;
			$class = 'wp-ui-notification';

			// for speed reason we limit it to 50
			//TODO: improve this, only 50 is not good
			if ( $this->album_count < 50 ) {
				if ( $album->previewpic != 0 ) {
					$image         = $nggdb->find_image( $album->previewpic );
					$preview_image = isset( $image->thumbURL ) ? '<div class="inlinepicture"><img src="' . esc_url( $image->thumbURL ) . '" /></div>' : '';
				}
			}

			// this indicates that we have a album container
			$prefix = 'a';

		} else {

			$id = (int) $id;

			if ( ! array_key_exists($id, $this->galleries)) {
				var_dump($this->galleries);
				return;
			}

			$gallery = $this->galleries[$id];

			$name  = $gallery->name;
			$title = $gallery->title;
			$class = 'wp-ui-highlight';

			// for speed reason we limit it to 50
			//TODO: improve this, only 50 is not good
			if ( $this->gallery_count < 50 ) {
				// set image url
				$image         = $nggdb->find_image( $gallery->previewpic );
				$preview_image = isset( $image->thumbURL ) ? '<div class="inlinepicture"><img src="' . esc_url( $image->thumbURL ) . '" /></div>' : '';
			}

			$prefix = '';
		}

		// add class if it's in use in other albums
		$used = $used ? ' inUse' : '';

		?>
		<div id="gid-<?= esc_attr($prefix . $id) ?> " class="groupItem<?= esc_attr($used)?>">
			<div class="innerhandle">
				<div class="item_top <?= $class ?>">
					<span class="icon dashicons-arrow-up-alt2" title="Close"></span>ID: <?= $id ?> | <?= wp_html_excerpt( $title, 25 ) ?>
				</div>
				<div class="itemContent">
					<?= $preview_image ?>
					<p><strong><?php _e( 'Name', 'nggallery' )?>:</strong> <?= esc_html( $name ) ?></p>
					<p><strong><?php _e( 'Title', 'nggallery' )?>:</strong> <?= esc_html( $title ) ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * get all used galleries from all albums
	 *
	 * @return array $used_galleries_ids
	 */
	private function get_used_galleries() {

		$used = array();

		if ( $this->albums ) {
			foreach ( $this->albums as $key => $value ) {
				$sort_array = $this->albums[ $key ]->galleries;
				foreach ( $sort_array as $galleryid ) {
					if ( ! in_array( $galleryid, $used ) ) {
						$used[] = $galleryid;
					}
				}
			}
		}

		return $used;
	}

	public function register_styles() {
		wp_enqueue_style( 'nggadmin' );
	}

	public function register_scripts() {
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'postbox' );
	}

	/**
	 * A possibility to add help to the screen.
	 *
	 * @param \WP_Screen $screen The current screen.
	 */
	public function add_help( $screen ) {
		$help = '<p>' . __( 'Organize your galleries into albums.',
				'nggallery' ) . '</p><p>' . __( 'First select an album from the dropdown and then drag the galleries you want to add or remove from the selected album.',
				'nggallery' ) . '</p>';

		$screen->add_help_tab( array(
			'id'      => $screen->id . '-general',
			'title'   => 'Organize everything',
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
	 * Display the post box.
     *
     * @access private
     */
	public function album_properties() {
		?>
		<table class="form-table">
			<tr>
				<td align="left"><label for="album_name"><?php _e( 'Name' ) ?></label></td>
				<td align="left">
					<input type="text" id="album_name" name="album_name" class="regular-text" value="<?php esc_attr_e( $this->album->name ) ?>"/>
				</td>
				<td align="left"><label for="page_id"><?php _e( 'Page ID', 'nggallery' ) ?></label>
				</td>
				<td align="left">
					<select id="page_id" name="page_id">
						<option value="0"><?php _e( 'Not linked', 'nggallery' ) ?></option>
						<?php parent_dropdown( $this->album->pageid ); ?>
					</select>
				</td>
			</tr>
			<tr>
				<td align="left"><label for="album_desc"><?php _e( 'Description' ) ?></label></td>
				<td align="left">
					<textarea name="album_desc" id="album_desc" cols="46" rows="3"><?php echo $this->album->albumdesc; ?></textarea>
				</td>
				<td align="left">
					<label for="preview_pic"><?php _e( 'Preview image', 'nggallery' ) ?></label>
				</td>
				<td align="left">
					<select id="preview_image" name="preview_image">
						<option value="0"><?php esc_html_e( 'No picture', 'nggallery' ); ?></option>
						<option value="0">TODO: fix this</option>
					</select>
				</td>
			</tr>

			<?php do_action( 'ngg_edit_album_settings', $this->album->id ); ?>
		</table>
		<div class="normal-submit">
			<?php submit_button(); ?>
		</div>
	<?php
	}
}