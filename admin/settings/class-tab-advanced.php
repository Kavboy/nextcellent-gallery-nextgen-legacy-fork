<?php

namespace NextCellent\Admin\Settings;

class Tab_Advanced extends Settings_Tab {

	/**
	 * Render the content that should be displayed in the tab.
	 *
	 * @return null
	 */
	public function render() {

		?>
		<h3><?php _e( 'Reset settings', 'nggallery' ); ?></h3>
		<form method="POST" action="<?php echo $this->page ?>">
			<?php $this->nonce(); ?>
			<p>
				<?php _e( 'Reset all options and settings to their default values.', 'nggallery' ); ?><br>
				<span class="wp-ui-text-notification"><?php _e('Note that this has no impact on the galleries or images. This action only affects the settings.', 'nggallery') ?></span>
			</p>
			<?php submit_button(__( 'Reset settings', 'nggallery' )) ?>
		</form>
		<?php
	}

	/**
	 * Handle saving the settings. The referrer is already checked at this
	 * point, so you do not need to do that.
	 */
	public function processor() {

		require_once( dirname( __DIR__ ) . '/class-installer.php' );

		\NextCellent\show_success( __( 'All settings were reset to their default value.', 'nggallery' ) );
	}
}