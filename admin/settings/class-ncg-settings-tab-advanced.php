<?php

require_once( __DIR__ . '/class-ncg-settings-tab.php' );

class NCG_Settings_Tab_Advanced extends NCG_Settings_Tab {

	/**
	 * Render the content that should be displayed in the tab.
	 *
	 * @return null
	 */
	public function render() {

		?>
		<h3><?php _e( 'Reset settings', 'nggallery' ); ?></h3>
		<form method="POST" action="<?php echo $this->page ?>">
			<?php wp_nonce_field( 'ncg_settings_reset' ) ?>
			<p>
				<?php _e( 'Reset all options and settings to their default values.', 'nggallery' ); ?><br>
				<span class="wp-ui-text-notification"><?php _e('Note that this has no impact on the galleries or images. This action only affects the settings.', 'nggallery') ?></span>
			</p>
			<?php submit_button(__( 'Reset settings', 'nggallery' )) ?>
		</form>
		<?php
	}

	/**
	 * Handle saving the settings.
	 *
	 * @return null
	 */
	public function processor() {

		check_admin_referer( 'ncg_settings_reset' );

		require_once( dirname( __DIR__ ) . '/class-ngg-installer.php' );

		NextCellent\Utils\show_success( __( 'All settings were reset to their default value.', 'nggallery' ) );
	}
}