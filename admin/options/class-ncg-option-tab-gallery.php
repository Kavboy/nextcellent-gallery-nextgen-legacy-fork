<?php

require_once( __DIR__ . '/class-ncg-option-tab.php' );

class NCG_Option_Tab_Gallery extends NCG_Option_Tab {

	/**
	 * Get the name of this tab.
	 *
	 * @return string The name of this tab.
	 */
	public function get_name() {
		return 'gallery';
	}

	/**
	 * Render the content that should be displayed in the tab.
	 *
	 * @return null
	 */
	public function render() {
		?>
		<h3><?php _e('Gallery settings','nggallery'); ?></h3>
		<form name="galleryform" method="POST" action="<?php echo $this->page . '#gallery'; ?>">
			<?php wp_nonce_field('ngg_settings') ?>
			<input type="hidden" name="page_options" value="galNoPages,galImages,galColumns,galShowSlide,galTextSlide,galTextGallery,galShowOrder,galImgBrowser,galSort,galSortDir,galHiddenImg,galAjaxNav">
			<table class="form-table ngg-options">
				<tr>
					<th><?php _e('Inline gallery','nggallery') ?></th>
					<td>
						<input name="galNoPages" id="galNoPages" type="checkbox" value="true" <?php $this->options->checked( 'galNoPages' ); ?>>
						<label for="galNoPages"><?php _e('Galleries will not be shown on a subpage, but on the same page.','nggallery') ?></label>
					</td>
				</tr>
				<tr>
					<th><label for="galImages"><?php _e('Images per page','nggallery'); ?></label></th>
					<td>
						<input type="number" step="1" min="0" class="small-text" name="galImages" id="galImages" value="<?php echo $this->options['galImages']; ?>">
						<?php _e( 'images', 'nggallery'); ?>
						<p class="description"><?php _e('0 will disable pagination and show all images on one page.','nggallery') ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="galColumns"><?php esc_html_e('Columns','nggallery'); ?></label></th>
					<td>
						<input type="number" step="1" min="0" class="small-text" name="galColumns" id="galColumns" value="<?php echo $this->options['galColumns']; ?>">
						<?php _e( 'columns per page', 'nggallery'); ?>
						<p class="description"><?php _e('0 will display as much columns as possible. This is normally only required for captions below the images.','nggallery') ?></p>
					</td>
				</tr>
				<tr>
					<th><?php _e('Slideshow','nggallery'); ?></th>
					<td>
						<label>
							<input name="galShowSlide" type="checkbox" value="true" <?php $this->options->checked( 'galShowSlide' ); ?>>
							<?php _e('Enable slideshow','nggallery'); ?>
						</label>
						<br>
						<label>
							<?php _e('Text to show:','nggallery'); ?>
							<input type="text" class="regular-text" name="galTextSlide" value="<?php echo $this->options['galTextSlide'] ?>">
						</label>
						<input type="text" name="galTextGallery" value="<?php echo $this->options['galTextGallery'] ?>" class="regular-text">
						<p class="description"> <?php _e('This is the text the visitors will have to click to switch between display modes.','nggallery'); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php _e('Show first','nggallery'); ?></th>
					<td>
						<fieldset>
							<label>
								<input name="galShowOrder" type="radio" value="gallery" <?php $this->options->checked( 'galShowOrder', 'gallery'); ?>>
								<?php _e('Thumbnails', 'nggallery') ;?>
							</label>
							<br>
							<label>
								<input name="galShowOrder" type="radio" value="slide" <?php $this->options->checked('galShowOrder', 'slide'); ?>>
								<?php _e('Slideshow', 'nggallery') ;?>
							</label>
						</fieldset>
						<p class="description"><?php _e( 'Choose what visitors will see first.', 'nggallery'); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php _e('ImageBrowser','nggallery'); ?></th>
					<td>
						<label>
							<input name="galImgBrowser" type="checkbox" value="true" <?php $this->options->checked( 'galImgBrowser' ); ?>>
							<?php _e('Use ImageBrowser instead of another effect.', 'nggallery'); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th><?php _e('Hidden images','nggallery'); ?></th>
					<td>
						<label>
							<input name="galHiddenImg" type="checkbox" value="true" <?php $this->options->checked( 'galHiddenImg' ); ?>>
							<?php _e('Loads all images for the modal window, when pagination is used (like Thickbox, Lightbox etc.).','nggallery'); ?>
						</label>
						<p class="description"> <?php _e('Note: this increases the page load (possibly a lot)', 'nggallery'); ?>
					</td>
				</tr>
				<tr>
					<th><?php _e('AJAX pagination','nggallery'); ?></th>
					<td>
						<label>
							<input name="galAjaxNav" type="checkbox" value="true" <?php $this->options->checked( 'galAjaxNav' ); ?>>
							<?php _e('Use AJAX pagination to browse images without reloading the page.','nggallery'); ?>
						</label>
						<p class="description"><?php esc_html_e('Note: works only in combination with the Shutter effect.', 'nggallery'); ?></p>
					</td>
				</tr>
			</table>
			<h3><?php _e('Sort options','nggallery'); ?></h3>
			<table class="form-table ngg-options">
				<tr>
					<th><?php _e('Sort thumbnails','nggallery'); ?></th>
					<td>
						<fieldset>
							<label>
								<input name="galSort" type="radio" value="sortorder" <?php $this->options->checked( 'galSort', 'sortorder' ); ?>>
								<?php _e('Custom order', 'nggallery'); ?>
							</label><br>
							<label>
								<input name="galSort" type="radio" value="pid" <?php $this->options->checked('galSort', 'pid' ); ?>>
								<?php _e('Image ID', 'nggallery'); ?>
							</label><br>
							<label>
								<input name="galSort" type="radio" value="filename" <?php $this->options->checked('galSort', 'filename'); ?>>
								<?php _e('File name', 'nggallery') ;?>
							</label><br>
							<label>
								<input name="galSort" type="radio" value="alttext" <?php $this->options->checked('galSort', 'alttext'); ?>>
								<?php _e('Alt / Title text', 'nggallery') ;?>
							</label><br>
							<label>
								<input name="galSort" type="radio" value="imagedate" <?php $this->options->checked('galSort', 'imagedate' ); ?>>
								<?php _e('Date / Time', 'nggallery') ;?>
							</label>
						</fieldset>

					</td>
				</tr>
				<tr>
					<th><?php _e('Sort direction','nggallery') ?></th>
					<td>
						<label>
							<input name="galSortDir" type="radio" value="ASC" <?php $this->options->checked('galSortDir', 'ASC'); ?>>
							<?php _e('Ascending', 'nggallery') ;?>
						</label><br>
						<label>
							<input name="galSortDir" type="radio" value="DESC" <?php $this->options->checked('galSortDir', 'DESC'); ?>>
							<?php _e('Descending', 'nggallery') ;?>
						</label>
					</td>
				</tr>
			</table>
			<?php submit_button( __('Save Changes'), 'primary', 'updateoption' ); ?>
		</form>
		<?php
	}
}