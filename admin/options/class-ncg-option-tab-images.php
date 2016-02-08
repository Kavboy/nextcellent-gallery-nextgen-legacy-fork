<?php

require_once( __DIR__ . '/class-ncg-option-tab.php' );

class NCG_Option_Tab_Images extends NCG_Option_Tab {

	/**
	 * Get the name of this tab.
	 *
	 * @return string The name of this tab.
	 */
	public function get_name() {
		return 'images';
	}

	/**
	 * Render the content that should be displayed in the tab.
	 *
	 * @return null
	 */
	public function render() {
		?>
		<h3><?php _e('Image settings','nggallery'); ?></h3>
		<form name="imagesettings" method="POST" action="<?php echo $this->page; ?>">
			<?php wp_nonce_field('ngg_settings') ?>
			<input type="hidden" name="page_options" value="imgResize,imgWidth,imgHeight,imgQuality,imgBackup,imgAutoResize,thumbwidth,thumbheight,thumbfix,thumbquality,thumbDifferentSize">
			<table class="form-table ngg-options">
				<tr>
					<th><?php _e('Resize images','nggallery') ?></th>
					<td>
						<label for="imgWidth"><?php _e('Width','nggallery') ?></label>
						<input type="number" step="1" min="0" class="small-text" name="imgWidth" id="imgWidth" value="<?php echo $this->options['imgWidth']; ?>">
						<label for="imgHeight"><?php _e('Height','nggallery') ?></label>
						<input type="number" step="1" min="0" class="small-text" name="imgHeight" id="imgHeight" value="<?php echo $this->options['imgHeight']; ?>">
						<p class="description"><?php _e('Width and height (in pixels). NextCellent Gallery will keep the ratio size.','nggallery') ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="imgQuality"><?php _e('Image quality','nggallery'); ?></label></th>
					<td><input type="number" step="1" min="0" max="100" class="small-text" name="imgQuality" id="imgQuality" value="<?php echo $this->options['imgQuality']; ?>">%</td>
				</tr>
				<tr>
					<th><?php _e('Backup original','nggallery'); ?></th>
					<td>
						<label>
							<input type="checkbox" name="imgBackup" value="true" <?php $this->options->checked( 'imgBackup'); ?>>
							<?php _e('Create a backup for the resized images','nggallery'); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th><?php _e('Automatically resize','nggallery'); ?></th>
					<td>
						<label>
							<input type="checkbox" name="imgAutoResize" value="1" <?php $this->options->checked( 'imgAutoResize'); ?>>
							<?php _e('Automatically resize images on upload.','nggallery') ?>
						</label>
					</td>
				</tr>
			</table>
			<h3><?php _e('Thumbnail settings','nggallery'); ?></h3>
			<table class="form-table ngg-options">
				<tr>
					<th><?php _e('Different sizes','nggallery'); ?></th>
					<td>
						<input type="checkbox" name="thumbDifferentSize" id="thumbDifferentSize" value="true" <?php $this->options->checked( 'thumbDifferentSize'); ?>>
						<label for="thumbDifferentSize"><?php _e('Allows you to make thubnails with dimensions that differ from the rest of the gallery.','nggallery') ?></label>
					</td>
				</tr>
			</table>
			<p><?php _e('Please note: if you change the settings below settings, you need to recreate the thumbnails under -> Manage Gallery .', 'nggallery') ?></p>
			<table class="form-table ngg-options">
				<tr>
					<th><?php _e('Thumbnail size','nggallery'); ?></th>
					<td>
						<label for="thumbwidth"><?php _e('Width','nggallery') ?></label>
						<input type="number" step="1" min="0" class="small-text" name="thumbwidth" id="thumbwidth" value="<?php echo $this->options['thumbwidth']; ?>">
						<label for="thumbheight"><?php _e('Height','nggallery') ?></label>
						<input type="number" step="1" min="0" class="small-text" name="thumbheight" id="thumbheight" value="<?php echo $this->options['thumbheight']; ?>">
						<p class="description"><?php _e('These values are maximum values.','nggallery'); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php _e('Fixed size','nggallery'); ?></th>
					<td>
						<input type="checkbox" name="thumbfix" id="thumbfix" value="true" <?php $this->options->checked( 'thumbfix' ); ?>>
						<label for="thumbfix"><?php _e('Ignore the aspect ratio, so no portrait thumbnails.','nggallery') ?></label>
					</td>
				</tr>
				<tr>
					<th><label for="thumbquality"><?php _e('Thumbnail quality','nggallery'); ?></label></th>
					<td><input type="number" step="1" min="0" max="100" class="small-text" name="thumbquality" id="thumbquality" value="<?php echo $this->options['thumbquality']; ?>">%</td>
				</tr>
			</table>
			<h3><?php _e('Single picture','nggallery') ?></h3>
			<table class="form-table ngg-options">
				<tr>
					<th><?php _e('Clear cache folder','nggallery'); ?></th>
					<td><input type="submit" name="clearcache" class="button-secondary"  value="<?php _e('Proceed now &raquo;','nggallery') ;?>"/></td>
				</tr>
			</table>
			<?php submit_button( __('Save Changes'), 'primary', 'updateoption' ); ?>
		</form>
		<?php
	}
}