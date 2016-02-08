<?php

require_once( __DIR__ . '/class-ncg-option-tab.php' );

class NCG_Option_Tab_General extends NCG_Option_Tab {

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
		<form name="generaloptions" method="post" action="<?php echo $this->page; ?>">
			<?php wp_nonce_field('ngg_settings') ?>
			<input type="hidden" name="page_options" value="gallerypath,silentUpgrade,deleteImg,useMediaRSS,usePicLens,usePermalinks,permalinkSlug,graphicLibrary,imageMagickDir,activateTags,appendType,maxImages" />
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
						<fieldset>
							<label>
								<input name="graphicLibrary" type="radio" value="gd" <?php $this->options->checked('graphicLibrary', 'gd'); ?>>
								<?php _e('GD Library', 'nggallery');?>
							</label><br>
							<label>
								<input name="graphicLibrary" type="radio" value="im" <?php $this->options->checked('graphicLibrary', 'im'); ?>>
								<?php _e('ImageMagick (Experimental)', 'nggallery'); ?>
							</label>
						</fieldset>
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
					<th><label for="permalinkSlug"><?php _e('Gallery slug:','nggallery'); ?></label></th>
					<td>
						<input type="text" class="regular-text code" name="permalinkSlug" id="permalinkSlug" value="<?php echo $this->options['permalinkSlug']; ?>">
					</td>
				</tr>
				<tr>
					<th><label for="createslugs"><?php _e('Recreate URLs','nggallery'); ?></label></th>
					<td>
						<input type="submit" name="createslugs" id="createslugs" class="button-secondary"  value="<?php _e('Start now &raquo;','nggallery') ;?>"/>
						<p class="description"><?php _e( "If you've changed these settings, you'll have to recreate the URLs.",'nggallery'); ?></p>
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
						<fieldset>
							<label>
								<input name="appendType" type="radio" value="category" <?php $this->options->checked('appendType', 'category'); ?>>
								<?php _e('Categories', 'nggallery') ;?>
							</label>
							<br>
							<label>
								<input name="appendType" type="radio" value="tags" <?php $this->options->checked('appendType', 'tags'); ?>>
								<?php _e('Tags', 'nggallery') ;?>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th><label for="maxImages"><?php _e('Max. number of images','nggallery'); ?></label></th>
					<td>
						<input name="maxImages" id="maxImages" type="number" step="1" min="1" value="<?php echo $this->options['maxImages']; ?>" class="small-text">
						<p class="description"><?php _e('0 will show all images','nggallery'); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button( __('Save Changes'), 'primary', 'updateoption' ); ?>
		</form>
		<?php
	}
}