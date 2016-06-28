<?php

namespace NextCellent\Admin\Settings;

/**
 * The gallery tab.
 */
class Tab_Gallery extends Settings_Tab {

	/**
	 * @var array Possibilities to show first on a page.
	 */
	private $gallery_display;

	/**
	 * @var array Possibilities to order the thumbnails.
	 */
	private $thumbnail_order;

	/**
	 * @var array The sort orders.
	 */
	private $sort_order;

	public function __construct($options, $page, $tabs) {
		parent::__construct($options, $page, $tabs);

		//Initialize the possibilities.

		$this->gallery_display = array(
			'gallery'   => __('Thumbnails', 'nggallery'),
			'slide'     => __('Slideshow', 'nggallery')
		);

		$this->thumbnail_order = array(
			'sortorder' => __( 'Custom order', 'nggallery' ),
			'pid'       => __( 'Image ID', 'nggallery' ),
			'filename'  => __( 'File name', 'nggallery' ),
			'alttext'   => __( 'Alt / Title text', 'nggallery' ),
			'imagedate' => __( 'Image date & time', 'nggallery' )
		);

		$this->sort_order = array(
			'ASC'   => __('Ascending', 'nggallery'),
			'DESC'  => __('Descending', 'nggallery')
		);
	}


	/**
	 * Render the content that should be displayed in the tab.
	 *
	 * @return null
	 */
	public function render() {
		?>
		<h3><?php _e('Gallery settings','nggallery'); ?></h3>
		<form method="POST" action="<?php echo $this->page; ?>">
			<?php $this->nonce(); ?>
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
						<p class="description">
							<?php _e('0 will display as much columns as possible. This is normally only required for captions below the images.','nggallery') ?><br>
							<?php _e('This option is ignored in the default templates.', 'nggallery') ?>
						</p>
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
						<?php $this->render_radio_options('galShowOrder', $this->gallery_display); ?>
						<p class="description"><?php _e( 'The mode the gallery will be in by default. This is what site visitors will see first.', 'nggallery'); ?></p>
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
						<?php $this->render_radio_options('galSort', $this->thumbnail_order); ?>
					</td>
				</tr>
				<tr>
					<th><?php _e('Sort direction','nggallery') ?></th>
					<td>
						<?php $this->render_radio_options('galSortDir', $this->sort_order); ?>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
		<?php
	}

	/**
	 * Handle saving the settings. The referrer is already checked at this
	 * point, so you do not need to do that.
	 */
	public function processor() {

		//Set all boolean values.
		$this->save_booleans(array('galNoPages', 'galShowSlide', 'galImgBrowser', 'galHiddenImg', 'galAjaxNav'));

		//Set positive integers.
		$this->save_number(array('galImages', 'galColumns'));

		//Set text fields.
		$this->save_text(array('galTextSlide', 'galTextGallery'));

		//Set options with restricted values.
		$this->save_restricted(array(
			'galShowOrder'  => array_keys($this->gallery_display),
			'galSort'       => array_keys($this->thumbnail_order),
			'galSortDir'    => array_keys($this->sort_order)

		));

		//Save the options.
		$this->options->save_options();

		$this->success_message();
	}
}