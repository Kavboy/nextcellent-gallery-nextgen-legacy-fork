<?php

require_once( __DIR__ . '/class-ncg-option-tab.php' );

class NCG_Option_Tab_Advanced extends NCG_Option_Tab {

	/**
	 * Get the name of this tab.
	 *
	 * @return string The name of this tab.
	 */
	public function get_name() {
		return 'advanced';
	}

	/**
	 * Render the content that should be displayed in the tab.
	 *
	 * @return null
	 */
	public function render() {
		?>
		<h2><?php _e('Reset options', 'nggallery') ;?></h2>
		<form name="resetsettings" method="post">
			<?php wp_nonce_field('ngg_uninstall') ?>
			<p><?php _e('Reset all options/settings to the default installation.', 'nggallery') ;?></p>
			<div align="center">
				<input type="submit" class="button" id="reset-to-default" name="resetdefault" value="<?php _e('Reset settings', 'nggallery') ;?>">
			</div>
		</form>
		<?php
	}
}