<?php

namespace NextCellent\Admin\Settings;

require_once( __DIR__ . '/class-settings-tab.php' );

class Tab_Watermark extends Settings_Tab {

	/**
	 * Get the name of this tab.
	 *
	 * @return string The name of this tab.
	 */
	public function get_name() {
		return 'watermark';
	}

	/**
	 * Render the content that should be displayed in the tab.
	 *
	 * @return null
	 */
	public function render() {
		// take the first image as sample
		$image_array = \nggdb::find_last_images(0, 1);
		$ngg_image = $image_array[0];
		$imageID  = $ngg_image->pid;

		?>

		<h3><?php _e('Watermark','nggallery'); ?></h3>
		<p><?php _e('Please note: you can only activate the watermark under -> Manage Galleries. This action cannot be undone.', 'nggallery') ?></p>
		<form method="POST" action="<?php echo $this->page; ?>">
			<?php $this->nonce(); ?>
			<div id="wm-preview">
				<h3><?php esc_html_e('Preview','nggallery') ?></h3>
				<label for="wm-preview-select"><?php _e('Select an image','nggallery'); ?></label>
				<select id="wm-preview-select" name="wm-preview-img" style="width: 200px">
					<?php echo '<option value="' . $ngg_image->pid . '">' . $ngg_image->pid . ' - ' . $ngg_image->alttext . '</option>'; ?>
				</select>
				<div id="wm-preview-container">
					<a id="wm-preview-image-url" href="<?php echo home_url( 'index.php' ); ?>?callback=image&pid=<?php echo intval( $imageID ); ?>&mode=watermark" target="_blank" title="<?php _e("View full image", 'nggallery'); ?>">
						<img id="wm-preview-image" src="<?php echo home_url( 'index.php' ); ?>?callback=image&pid=<?php echo intval( $imageID ); ?>&mode=watermark" />
					</a>
				</div>
				<h3><?php _e('Position','nggallery') ?></h3>
				<table id="wm-position">
					<tr>
						<td>
							<strong><?php _e('Position','nggallery') ?></strong>
							<table>
								<tr>
									<td><input type="radio" name="wmPos" value="topLeft" <?php $this->options->checked('wmPos', 'topLeft'); ?> /></td>
									<td><input type="radio" name="wmPos" value="topCenter" <?php $this->options->checked('wmPos', 'topCenter'); ?> /></td>
									<td><input type="radio" name="wmPos" value="topRight" <?php $this->options->checked('wmPos', 'topRight'); ?> /></td>
								</tr>
								<tr>
									<td><input type="radio" name="wmPos" value="midLeft" <?php $this->options->checked('wmPos', 'midLeft'); ?> /></td>
									<td><input type="radio" name="wmPos" value="midCenter" <?php $this->options->checked('wmPos', 'midCenter'); ?> /></td>
									<td><input type="radio" name="wmPos" value="midRight" <?php $this->options->checked('wmPos', 'midRight'); ?> /></td>
								</tr>
								<tr>
									<td><input type="radio" name="wmPos" value="botLeft" <?php $this->options->checked('wmPos', 'botLeft'); ?> /></td>
									<td><input type="radio" name="wmPos" value="botCenter" <?php $this->options->checked('wmPos', 'botCenter'); ?> /></td>
									<td><input type="radio" name="wmPos" value="botRight" <?php $this->options->checked('wmPos', 'botRight'); ?> /></td>
								</tr>
							</table>
						</td>
						<td>
							<strong><?php _e('Offset','nggallery') ?></strong>
							<table border="0">
								<tr>
									<td><label for="wmXpos">x:</label></td>
									<td><input type="number" step="1" min="0" class="small-text" name="wmXpos" id="wmXpos" value="<?php echo $this->options['wmXpos'] ?>">px</td>
								</tr>
								<tr>
									<td><label for="wmYpos">y:</label></td>
									<td><input type="number" step="1" min="0" class="small-text" name="wmYpos" id="wmYpos" value="<?php echo $this->options['wmYpos'] ?>" />px</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<h3><label><input type="radio" name="wmType" value="image" <?php $this->options->checked('wmType', 'image'); ?>><?php _e('Use image as watermark','nggallery') ?></label></h3>
			<table class="wm-table form-table">
				<tr>
					<th><label for="wmPath"><?php _e('URL to file','nggallery'); ?></label></th>
					<td><input type="text" class="regular-text code" name="wmPath" id="wmPath" value="<?php echo $this->options['wmPath']; ?>"><br>
				</tr>
			</table>
			<h3><label><input type="radio" name="wmType" value="text" <?php $this->options->checked('wmType', 'text'); ?>><?php _e('Use text as watermark','nggallery') ?></label></h3>
			<table class="wm-table form-table">
				<tr>
					<th><?php _e('Font','nggallery') ?></th>
					<td>
						<select name="wmFont" size="1">
							<?php
							$font_list = $this->get_fonts();
							foreach ( $font_list as $font ) {
								echo "\n".'<option value="'.$font.'" '. $this->options->selected('wmFont', $font).' >'.$font.'</option>';
							}
							?>
						</select><br>
						<span>
							<?php if ( !function_exists('ImageTTFBBox') ) {
								_e( 'This function will not work, cause you need the FreeType library', 'nggallery' );
							} else {
								printf( __( 'You can upload more fonts in the folder <code>%s</code>', 'nggallery' ), NCG_PATH . 'fonts/');
							} ?>
						</span>
					</td>
				</tr>
				<tr>
					<th><label for="wmSize"><?php _e('Size','nggallery'); ?></label></th>
					<td><input type="number" step="1" min="0" class="small-text" name="wmSize" id="wmSize" value="<?php echo $this->options['wmSize']; ?>">px</td>
				</tr>
				<tr>
					<th><label for="wmColor"><?php _e('Color','nggallery'); ?></label></th>
					<td><input type="color" id="wmColor" name="wmColor" value="#<?php echo $this->options['wmColor'] ?>">
				</tr>
				<tr>
					<th><label for="wmText"><?php _e('Text','nggallery'); ?></label></th>
					<td><textarea name="wmText" id="wmText" cols="50" rows="5" class="normal-text"><?php echo $this->options['wmText'] ?></textarea></td>
				</tr>
				<tr>
					<th><label for="wmOpaque"><?php _e('Opaque','nggallery'); ?></label></th>
					<td><input type="number" step="1" min="0" max="100" class="small-text" name="wmOpaque" id="wmOpaque" value="<?php echo $this->options['wmOpaque'] ?>">%</td>
				</tr>
			</table>
			<div class="clear"></div>
			<?php submit_button(); ?>
		</form>

		<?php
	}

	/**
	 * Get the fonts for the slideshow.
	 *
	 * @return array The fonts.
	 */
	private function get_fonts() {

		$ttf_fonts = array ();

		// Files in wp-content/plugins/nggallery/fonts directory
		$plugin_root = NCG_PATH . '/fonts';

		$plugins_dir = dir($plugin_root);
		if ($plugins_dir) {
			while (($file = $plugins_dir->read()) !== false) {
				if (preg_match('|^\.+$|', $file))
					continue;
				if (is_dir($plugin_root.'/'.$file)) {
					$plugins_subdir = dir($plugin_root.'/'.$file);
					if ($plugins_subdir) {
						while (($subfile = $plugins_subdir->read()) !== false) {
							if (preg_match('|^\.+$|', $subfile)) {
								continue;
							}
							if (preg_match('|\.ttf$|', $subfile)) {
								$ttf_fonts[] = "$file/$subfile";
							}
						}
					}
				} else {
					if (preg_match('|\.ttf$|', $file))
						$ttf_fonts[] = $file;
				}
			}
		}

		return $ttf_fonts;
	}

	/**
	 * Handle saving the settings. The referrer is already checked at this
	 * point, so you do not need to do that.
	 */
	public function processor() {

		$this->save_booleans(array(
			'irAutoDim', 'imgAutoResize', 'thumbfix', 'thumbDifferentSize', 'irLoop', 'irDrag', 'irNavigation',
			'irNavigationDots', 'irAutoplay', 'irAutoplayHover', 'irClick'
		));

		//Set positive integers.
		$this->save_number( array( 'wmXpos', 'wmYpos', 'wmSize', 'wmOpaque' ) );

		$this->save_text( array('wmPath', 'wmText', 'wmFont') );

		$this->save_restricted( array(
			'wmPos'  => array(
				'topLeft', 'topCenter', 'topRight', 'midLeft', 'midCenter', 'midRight',
				'botLeft', 'botCenter', 'botRight'
			),
			'wmType'    => array('image', 'text')
		));

		if(isset($_POST['wmColor'])) {
			$colour = $_POST['wmColor'];
			//Try matching.
			if(preg_match('/^#[a-f0-9]{6}$/i', $colour)) {
				$this->options->set_option('wmColor', ltrim($colour, '#'));
			} elseif(preg_match('/^[a-f0-9]{6}$/i', $colour)) {
				$this->options->set_option('wmColor', $colour);
			}
		}

		//Save the options.
		$this->options->save_options();

		$this->success_message();
	}

	public function print_scripts() {
		?>
		<script type="text/javascript">
			jQuery(document).ready( function($) {
				//Set preview for watermark.
				$('#wm-preview-select').on("nggAutocompleteDone", function() {
					$('#wm-preview-image').attr("src", '<?php echo home_url( 'index.php' ); ?>' + '?callback=image&pid=' + this.value + '&mode=watermark');
					$('#wm-preview-image-url').attr("href", '<?php echo home_url( 'index.php' ); ?>' + '?callback=image&pid=' + this.value + '&mode=watermark');
				});

				jQuery("#wm-preview-select").nggAutocomplete( {
					type: 'image',domain: "<?php echo home_url('index.php', is_ssl() ? 'https' : 'http'); ?>"
				});
			});
		</script>
		<?php
	}
}