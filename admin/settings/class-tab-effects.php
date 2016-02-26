<?php

namespace NextCellent\Admin\Settings;

require_once( __DIR__ . '/class-settings-tab.php' );

/**
 * The effects tab.
 */
class Tab_Effects extends Settings_Tab {

	/**
	 * @var array The possible effects. This is used to generate options and sanitize input.
	 */
	private $effects;

	public function __construct($options, $page, $tabs) {
		parent::__construct($options, $page, $tabs);

		$this->effects = array(
			'none'  => __('None', 'nggallery'),
			'thickbox'  => __('Thickbox', 'nggalllery'),
			'lightbox'  => __('Lightbox', 'nggallery'),
			'highslide' => __('Highslide', 'nggallery'),
			'shutter'   => __('Shutter', 'nggallery'),
			'photoSwipe'=> __('PhotoSwipe', 'nggallery'),
			'custom' => __('Custom', 'nggallery')
		);
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
		<ul>
			<li><code>%GALLERY_NAME%</code> - <?php _e('The gallery name.', 'nggallery'); ?></li>
			<li><code>%IMG_WIDTH%</code> - <?php _e('The width of the image.', 'nggallery'); ?></li>
			<li><code>%IMG_HEIGHT%</code> - <?php _e('The height of the image.', 'nggallery'); ?></li>
		</ul>
		<form method="POST" action="<?php echo $this->page; ?>">
			<?php $this->nonce() ?>
			<table class="form-table ngg-options">
				<tr>
					<th><label for="thumbEffect"><?php _e('JavaScript Thumbnail effect','nggallery') ?></label></th>
					<td>
						<select size="1" id="thumbEffect" name="thumbEffect" onchange="inserCode(this.value)">
							<?php $this->render_select_options('thumbEffect', $this->effects); ?>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="thumbCode"><?php _e('Effect Code','nggallery'); ?></label></th>
					<td>
						<textarea class="normal-text code" id="thumbCode" name="thumbCode" cols="50" rows="5"><?php echo stripslashes(esc_textarea($this->options['thumbCode'])); ?></textarea>
					</td>
				</tr>
			</table>
			<p id="effects-more"></p>
			<?php submit_button(); ?>
		</form>
		<?php
	}

	/**
	 * Handle saving the settings. The referrer is already checked at this
	 * point, so you do not need to do that.
	 */
	public function processor() {

		//Set options with restricted data.
		$this->save_restricted(array(
			'thumbEffect'   => array_keys($this->effects)
		));

		//Set the thumbnail code.
		if(isset($_POST['thumbCode'])) {
			$this->options->set_option('thumbCode', $_POST['thumbCode']);
		}

		//Save the options.
		$this->options->save_options();

		$this->success_message();
	}

	/**
	 * Print the JavaScript to the page.
	 */
	public function print_scripts() {
		?>
		<script type="text/javascript">
			function inserCode(value) {
				var effectCode = "";
				var extra = "";
				switch (value) {
					case 'none':
						break;
					case "thickbox":
						effectCode = 'class="thickbox" rel="%GALLERY_NAME%"';
						break;
					case "lightbox":
						effectCode = 'rel="lightbox[%GALLERY_NAME%]"';
						break;
					case "highslide":
						effectCode = 'class="highslide" onclick="return hs.expand(this, { slideshowGroup: %GALLERY_NAME% })"';
						break;
					case "shutter":
						effectCode = 'class="shutterset_%GALLERY_NAME%"';
						break;
					case "photoSwipe":
						effectCode = 'data-size="%IMG_WIDTH%x%IMG_HEIGHT%"';
						extra = 'Works with <a href="https://wordpress.org/plugins/photo-swipe/">PhotoSwipe</a>.';
						break;
					default:
						break;
				}
				jQuery("#thumbCode").val(effectCode);
				jQuery("#effects-more").html(extra);
			}
		</script>
		<?php
	}
}