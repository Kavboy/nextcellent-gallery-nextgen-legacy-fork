<?php

require_once( __DIR__ . '/class-ncg-settings-tab.php' );

class NCG_Settings_Tab_General extends NCG_Settings_Tab {

	/**
	 * @var array The different graphics libraries.
	 */
	private $graphics_library;

	/**
	 * @var array With what to match related images.
	 */
	private $related_match;

	public function __construct($options, $page, $tabs) {
		parent::__construct($options, $page, $tabs);

		$this->graphics_library = array(
			'gd'    => __('GD Library', 'nggallery'),
			'im'    => __('ImageMagick (Experimental)', 'nggallery')
		);

		$this->related_match = array(
			'category'  => __('Categories', 'nggallery'),
			'tags'      => __('Tags', 'nggallery')
		);
	}

	/**
	 * Get the name of this tab.
	 *
	 * @return string The name of this tab.
	 */
	public function get_name() {
		return 'general';
	}

	/**
	 * Render the content that should be displayed in the tab.
	 *
	 * @return null
	 */
	public function render() {
		?>
		<h3><?php _e( 'General settings', 'nggallery' ); ?></h3>
		<form method="post" action="<?php echo $this->page; ?>">
			<?php wp_nonce_field('ncg_settings_general') ?>
			<table class="form-table ngg-options">
				<tr>
					<th><label for="gallerypath"><?php _e('Gallery path','nggallery'); ?></label></th>
					<td>
						<input <?php $this->readonly(is_multisite()); ?> type="text" class="regular-text code" name="gallerypath" id="gallerypath" value="<?php echo $this->options['gallerypath']; ?>" />
						<p class="description"><?php esc_html_e('This is the default path for all galleries','nggallery') ?></p>
					</td>
				</tr>
				<tr>
					<th><?php _e('Silent database upgrade','nggallery'); ?></th>
					<td>
						<input <?php disabled(is_multisite()); ?> type="checkbox" name="silentUpgrade" id="silentUpgrade" value="true" <?php $this->options->checked('silentUpgrade'); ?> />
						<label for="silentUpgrade"><?php _e('Update the database without notice.','nggallery') ?></label>
					</td>
				</tr>
				<tr>
					<th><?php _e('Image files','nggallery'); ?></th>
					<td>
						<input <?php disabled(is_multisite()); ?> type="checkbox" name="deleteImg" id="deleteImg" value="true" <?php $this->options->checked('deleteImg'); ?>>
						<label for="deleteImg">
							<?php _e("Delete files when removing a gallery from the database",'nggallery'); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th><?php _e('Select graphic library','nggallery'); ?></th>
					<td>
						<?php $this->render_radio_options('graphicLibrary', $this->graphics_library); ?>
						<label>
							<?php _e('Path to the ImageMagick library:', 'nggallery'); ?>
							<input <?php $this->readonly(is_multisite()); ?> type="text" class="regular-text code" name="imageMagickDir" value="<?php echo $this->options['imageMagickDir']; ?>">
						</label>
					</td>
				</tr>
				<tr>
					<th><?php _e('Media RSS feed','nggallery'); ?></th>
					<td>
						<input type="checkbox" name="useMediaRSS" id="useMediaRSS" value="true" <?php $this->options->checked('useMediaRSS'); ?>>
						<label for="useMediaRSS"><?php esc_html_e('Add a RSS feed to you blog header. Useful for CoolIris/PicLens','nggallery') ?></label>
					</td>
				</tr>
				<tr>
					<th><?php _e('PicLens/CoolIris','nggallery'); ?> (<a href="http://www.cooliris.com">CoolIris</a>)</th>
					<td>
						<input type="checkbox" id="usePicLens" name="usePicLens" value="true" <?php $this->options->checked( 'usePicLens'); ?>>
						<label for="usePicLens"><?php _e('Include support for PicLens and CoolIris','nggallery'); ?></label>
						<p class="description"><?php _e('When activated, JavaScript is added to your site footer. Make sure that wp_footer is called in your theme.','nggallery') ?></p>
					</td>
				</tr>
			</table>
			<h3><?php _e('Permalinks','nggallery') ?></h3>
			<table class="form-table ngg-options">
				<tr>
					<th><?php _e('Use permalinks','nggallery'); ?></th>
					<td>
						<input type="checkbox" name="usePermalinks" id="usePermalinks" value="true" <?php $this->options->checked('usePermalinks'); ?>>
						<label for="usePermalinks"><?php _e('Adds a static link to all images','nggallery'); ?></label>
						<p class="description"><?php _e('When activating this option, you need to update your permalink structure once','nggallery'); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="permalinkSlug"><?php _e('Gallery slug','nggallery'); ?></label></th>
					<td>
						<input type="text" class="regular-text code" name="permalinkSlug" id="permalinkSlug" value="<?php echo $this->options['permalinkSlug']; ?>">
					</td>
				</tr>
				<tr>
					<th><?php _e('Recreate permalinks','nggallery'); ?></th>
					<td>
						<p class="description">
							<?php _e( 'You should save your new options first, and then recreate the permalinks.', 'nggallery'); ?>
						</p>
						<?php submit_button(__('Start now','nggallery'), 'secondary', 'create_permalinks'); ?>
					</td>
				</tr>
			</table>
			<h3><?php _e('Related images','nggallery'); ?></h3>
			<table class="form-table ngg-options">
				<tr>
					<th><?php _e('Add related images','nggallery'); ?></th>
					<td>
						<input name="activateTags" id="activateTags" type="checkbox" value="true" <?php $this->options->checked( 'activateTags'); ?>>
						<label for="activateTags"><?php _e('This will add related images to every post','nggallery'); ?></label>
					</td>
				</tr>
				<tr>
					<th><?php _e('Match with','nggallery'); ?></th>
					<td>
						<?php $this->render_radio_options('appendType', $this->related_match); ?>
					</td>
				</tr>
				<tr>
					<th><label for="maxImages"><?php _e('Max. number of images','nggallery'); ?></label></th>
					<td>
						<input name="maxImages" id="maxImages" type="number" step="1" min="0" value="<?php echo $this->options['maxImages']; ?>" class="small-text">
						<p class="description"><?php _e('0 will show all images','nggallery'); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
		<?php
	}

	/**
	 * Handle saving the settings.
	 */
	public function processor() {

		check_admin_referer('ncg_settings_general');

		//If we need to recreate the permalinks, we do only that.
		if(isset($_POST['create_permalinks'])) {
			$this->rebuild_permalinks();
			return;
		}

		//Set all boolean values.
		$booleans = array('useMediaRSS', 'usePicLens', 'usePermalinks', 'activateTags');

		//Add only if not multisite
		if(!is_multisite()) {
			array_push($booleans, 'silentUpgrade', 'deleteImg');
		}
		$this->save_booleans($booleans);

		//Set positive integers.
		$this->save_number(array('maxImages'));

		//Set text fields.
		$this->save_text(array('permalinkSlug'));

		//Set options with restricted values.
		$this->save_restricted(array(
			'graphicLibrary'    => array_keys($this->graphics_library),
			'appendType'        => array_keys($this->related_match)
		));

		if(!is_multisite() && isset($_POST['gallerypath'])) {
			$this->options->set_option('gallerypath', trailingslashit($_POST['gallerypath']));
		}

		if(!is_multisite() && isset($_POST['imageMagickDir'])) {
			$this->options->set_option('imageMagickDir', trailingslashit($_POST['imageMagickDir']));
		}

		//Save the options.
		$this->options->save_options();

		$this->success_message();
	}

	/**
	 * Rebuild the permalinks with AJAX.
	 *
	 * @todo Use the general AJAX, not a custom one.
	 */
	private function rebuild_permalinks() {
		global $wpdb;

		$total = array();
		// get the total number of images
		$total['images'] = intval( $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggpictures") );
		$total['gallery'] = intval( $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggallery") );
		$total['album'] = intval( $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->nggalbum") );

		$messages = array(
			'images' => __( 'Rebuild image structure: %s / %s images', 'nggallery' ),
			'gallery' => __( 'Rebuild gallery structure: %s / %s galleries', 'nggallery' ),
			'album' => __( 'Rebuild album structure: %s / %s albums', 'nggallery' ),
		);

		foreach ( array_keys( $messages ) as $key ) {

			$message = sprintf( $messages[ $key ] ,
				"<span class='ngg-count-current'>0</span>",
				"<span class='ngg-count-total'>" . $total[ $key ] . "</span>"
			);

			\NextCellent\Utils\show_notice("$key notice-success", $message, false);
		}

		//Output the done message now.
		\NextCellent\Utils\show_notice('notice-success finished', __('Permalinks updated successfully.', 'nggallery'), false);

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
							$('.album, .finished').toggle();
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
}