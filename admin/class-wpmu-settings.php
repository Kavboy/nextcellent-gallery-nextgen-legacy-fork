<?php

namespace NextCellent\Admin;

use NextCellent\Options\Options;

class WPMU_Settings extends Post_Admin_Page {
	
	const NAME = 'wpmu-settings';

	private $options;

	public function __construct() {
		global $ngg;

		$this->options = $ngg->options;
	}

	/**
	 * Enqueue/register the needed styles.
	 */
	public function register_styles() {
		wp_enqueue_style( 'nggadmin' );
	}

	/**
	 * Enqueue/register the needed scripts
	 */
	public function register_scripts() {
		//None
	}

	/**
	 * A possibility to add help to the screen.
	 *
	 * @param \WP_Screen $screen The current screen.
	 */
	public function add_help( $screen ) {

		$help = '<p>This screen allows you to manage some network settings.</p>';

		$screen->add_help_tab( array(
			'id'      => $screen->id . '-general',
			'title'   => 'Network options',
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

	public function get_full_url() {
		return network_admin_url( 'settings.php?page=' . Launcher::sluggify($this->get_name()));
	}

	/**
	 * This function must handle the POST parameters, and is automatically called
	 * when there are POST parameters.
	 *
	 * This function is called before the page display is built, so message to the user
	 * and other output will be visible.
	 */
	protected function processor() {

		check_admin_referer('ncg_network_options');

		$booleans = array(
			Options::MU_SILENT_DB_UPDATE,
			Options::MU_QUOTA_CHECK,
			Options::MU_ALLOW_UPLOAD_ZIP,
			Options::MU_ALLOW_IMPORT_FOLDER,
			Options::MU_ALLOW_CHOICE_STYLE
		);

		$this->mu_save_booleans($booleans);

		if(isset($_POST['gallerypath']) && trim($_POST['gallerypath']) !== '') {
			$this->options->set_mu_option(Options::MU_GALLERY_PATH, trailingslashit($_POST['gallerypath']));
		}

		$this->options->save_mu_options();

		\NextCellent\show_success(__('Network settings saved successfully.', 'nggallery'));
	}
	
	public function display() {
		parent::display();
		?>
		<div class="wrap">
			<h1><?php _e('Network Options','nggallery') ?></h1>
			<p class="wp-ui-text-notification">Warning! Multisite support is currently in alpha! We are still figuring out exactly how to handle everything.</p>

			<form method="POST" action="<?= $this->get_full_url() ?>">
				<?php wp_nonce_field('ncg_network_options') ?>
				<table class="form-table">
					<tr>
						<th><label for="gallerypath"><?php _e('Gallery path','nggallery'); ?></label></th>
						<td>
							<input type="text" class="regular-text code" name="gallerypath" id="gallerypath" value="<?php echo $this->options->get_mu_option('gallerypath'); ?>" />
							<p class="description">
								<?php _e('This is the default path for all galleries.','nggallery') ?><br>
								<?php _e('With the placeholder %BLOG_ID% you can organize the folder structure better.','nggallery'); ?><br>
								<?php printf(__('You can freely choose a folder in the root WordPress folder: %s', 'nggallery'), '<code>' . get_home_path() . '</code>') ?><br>
								<?php _e('On the main site (the first one), the gallery will be in the same place as if it were a single site.', 'nggallery') ?>
							</p>
						</td>
					</tr>
					<tr>
						<th><?php _e('Silent database upgrade','nggallery'); ?></th>
						<td>
							<input type="checkbox" name="silentUpgrade" id="silentUpgrade" value="true" <?php $this->options->mu_checked('silentUpgrade'); ?> />
							<label for="silentUpgrade"><?php _e('Update the database without notice.','nggallery') ?></label>
						</td>
					</tr>
					<tr>
						<th><?php _e('Enable upload quota check','nggallery'); ?></th>
						<td>
							<input name="wpmuQuotaCheck" id="wpmuQuotaCheck" type="checkbox" value="true" <?php $this->options->mu_checked( Options::MU_QUOTA_CHECK ) ?>>
							<label for="wpmuQuotaCheck"><?php _e('Should work if the gallery is bellow the blog.dir','nggallery') ?></label>
						</td>
					</tr>
					<tr>
						<th><?php _e('Enable zip upload option','nggallery'); ?></th>
						<td>
							<input name="wpmuZipUpload" id="wpmuZipUpload" type="checkbox" value="true" <?php $this->options->mu_checked(Options::MU_ALLOW_UPLOAD_ZIP) ?>>
							<label for="wpmuZipUpload"><?php _e('Allow users to upload zip folders.','nggallery') ?></label>
						</td>
					</tr>
					<tr>
						<th><?php _e('Enable import function','nggallery'); ?></th>
						<td>
							<input name="wpmuImportFolder" id="wpmuImportFolder" type="checkbox" value="true" <?php $this->options->mu_checked(Options::MU_ALLOW_IMPORT_FOLDER) ?>>
							<label for="wpmuImportFolder"><?php _e('Allow users to import images folders from the server.','nggallery'); ?></label>
						</td>
					</tr>
					<tr>
						<th><?php _e('Enable style selection','nggallery'); ?></th>
						<td>
							<input name="wpmuStyle" id="wpmuStyle" type="checkbox" value="true" <?php $this->options->mu_checked(Options::MU_ALLOW_CHOICE_STYLE); ?>>
							<label for="wpmuStyle"><?php _e('Allow users to choose a style for the gallery.','nggallery'); ?></label>
						</td>
					</tr>
					<tr>
						<th><label for="wpmuCSSfile"><?php _e('Default style','nggallery'); ?></label></th>
						<td>
							<p>This will be available again shortly.</p>
<!--							<select name="wpmuCSSfile" id="wpmuCSSfile">-->
<!--								--><?php //\NextCellent\Admin\Style_Page::output_css_files_dropdown($csslist, $act_cssfile); ?>
<!--							</select>-->
<!--							<p class="description">-->
<!--								--><?php //_e('Choose the default style for the galleries.','nggallery') ?>
<!--								--><?php //_e('Note: between brackets is the folder in which the file is.','nggallery') ?>
<!--							</p>-->
						</td>
					</tr>
				</table>
				<?php submit_button() ?>
			</form>
		</div>
		<?php
	}

	//TODO: all these functions need to move to a trait.

	/**
	 * Convert and set given positive integer options.
	 *
	 * @param array $options The options in an array.
	 *
	 * @throws \NextCellent\Options\InvalidOptionException
	 */
	protected function mu_save_number($options) {
		foreach ( $options as $option ) {
			if(isset($_POST[$option])) {
				$this->options->set_mu_option($option, absint($_POST[$option]));
			}
		}
	}

	/**
	 * Sanitize and set given text options.
	 *
	 * @param $options
	 */
	protected function mu_save_text($options) {
		foreach ( $options as $option ) {
			if(isset($_POST[$option])) {
				$this->options->set_mu_option($option, sanitize_text_field($_POST[$option]));
			}
		}
	}

	/**
	 * Save options that are restricted to a set of possibilities.
	 *
	 * @param array $options Contains the name of the option mapped to the possibilities.
	 */
	protected function mu_save_restricted($options) {
		foreach ( $options as $option => $possibilities ) {
			if(isset($_POST[$option]) && in_array($_POST[$option], $possibilities)) {
				$this->options->set_mu_option($option, $_POST[$option]);
			}
		}
	}

	/**
	 * Convert and set given boolean options.
	 *
	 * @param array $options The options in an array.
	 */
	protected function mu_save_booleans($options) {
		foreach ( $options as $option ) {
			$this->options->set_mu_option($option, isset($_POST[$option]) && \NextCellent\convert_to_bool($_POST[$option]));
		}
	}
}