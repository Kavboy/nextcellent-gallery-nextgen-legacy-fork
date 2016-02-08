<?php

require_once( __DIR__ . '/class-ncg-option-tab.php' );

class NCG_Option_Tab_Effects extends NCG_Option_Tab {

	/**
	 * Get the name of this tab.
	 *
	 * @return string The name of this tab.
	 */
	public function get_name() {
		return 'effects';
	}

	/**
	 * Render the content that should be displayed in the tab.
	 *
	 * @return null
	 */
	public function render() {
		?>
		<h3><?php _e('Effects','nggallery'); ?></h3>
		<p>
			<?php _e('Here you can select the thumbnail effect, NextCellent Gallery will integrate the required HTML code in the images. Please note that only the Shutter and Thickbox effect will automatic added to your theme.','nggallery'); ?>
			<?php _e('There are some placeholders available you can use in the code below.','nggallery'); ?>
		</p>
		<ul style="list-style: inside">
			<li><strong>%GALLERY_NAME%</strong> - <?php _e('The gallery name.', 'nggallery'); ?></li>
			<li><strong>%IMG_WIDTH%</strong> - <?php _e('The width of the image.', 'nggallery'); ?></li>
			<li><strong>%IMG_HEIGHT%</strong> - <?php _e('The height of the image.', 'nggallery'); ?></li>
		</ul>
		<form name="effectsform" method="POST" action="<?php echo $this->page . '#effects'; ?>">
			<?php wp_nonce_field('ngg_settings') ?>
			<input type="hidden" name="page_options" value="thumbEffect,thumbCode">
			<table class="form-table ngg-options">
				<tr>
					<th><label for="thumbEffect"><?php _e('JavaScript Thumbnail effect','nggallery') ?></label></th>
					<td>
						<select size="1" id="thumbEffect" name="thumbEffect" onchange="insertcode(this.value)">
							<option value="none" <?php $this->options->selected('thumbEffect', 'none'); ?>><?php _e('None', 'nggallery') ;?></option>
							<option value="thickbox" <?php $this->options->selected('thumbEffect', 'thickbox'); ?>><?php _e('Thickbox', 'nggallery') ;?></option>
							<option value="lightbox" <?php $this->options->selected('thumbEffect', 'lightbox'); ?>><?php _e('Lightbox', 'nggallery') ;?></option>
							<option value="highslide" <?php $this->options->selected('thumbEffect', 'highslide'); ?>><?php _e('Highslide', 'nggallery') ;?></option>
							<option value="shutter" <?php $this->options->selected('thumbEffect', 'shutter'); ?>><?php _e('Shutter', 'nggallery') ;?></option>
							<option value="photoSwipe" <?php $this->options->selected('thumbEffect', 'photoSwipe'); ?>><?php _e('PhotoSwipe', 'nggallery') ;?></option>
							<option value="custom" <?php $this->options->selected('thumbEffect', 'custom'); ?>><?php _e('Custom', 'nggallery') ;?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="thumbCode"><?php _e('Link Code line','nggallery'); ?></label></th>
					<td>
						<textarea class="normal-text code" id="thumbCode" name="thumbCode" cols="50" rows="5"><?php echo htmlspecialchars(stripslashes($this->options['thumbCode'])); ?></textarea>
					</td>
				</tr>
			</table>
			<?php submit_button( __('Save Changes'), 'primary', 'updateoption' ) ?>
			<p id="effects-more"></p>
		</form>
		<?php
	}
}