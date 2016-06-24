<?php

namespace NextCellent\Admin\Settings;

class Tab_Slideshow extends Settings_Tab {

	/**
	 * @var array The possible effects for the slideshow.
	 */
	private $effects;

	public function __construct( $options, $page, $tabs ) {
		parent::__construct( $options, $page, $tabs );

		//Not all effects are usable
		$this->effects = [
			__( 'Attention Seekers', 'nggallery' )  => [ "bounce", "flash", "pulse", "rubberBand", "shake", "swing", "tada", "wobble" ],
			__( 'Bouncing Entrances', 'nggallery' ) => [ "bounceIn", "bounceInDown", "bounceInLeft", "bounceInRight", "bounceInUp" ],
			__( 'Fading Entrances', 'nggallery' )   => [ "fadeIn", "fadeInDown", "fadeInDownBig", "fadeInLeft", "fadeInLeftBig", "fadeInRight", "fadeInRightBig", "fadeInUp", "fadeInUpBig" ],
			__( 'Fading Exits', 'nggallery' )       => [ "fadeOut", "fadeOutDown", "fadeOutDownBig", "fadeOutLeft", "fadeOutLeftBig", "fadeOutRight", "fadeOutRightBig", "fadeOutUp", "fadeOutUpBig" ],
			__( 'Flippers', 'nggallery' )           => [ "flip", "flipInX", "flipInY", "flipOutX", "flipOutY" ],
			__( 'Flippers', 'nggallery' )           => [ "flip", "flipInX", "flipInY"],
			__( 'Lightspeed', 'nggallery' )         => [ "lightSpeedIn", "lightSpeedOut" ],
			__( 'Lightspeed', 'nggallery' )         => [ "lightSpeedIn" ],
			__( 'Rotating Entrances', 'nggallery' )	=> [ "rotateIn", "rotateInDownLeft", "rotateInDownRight", "rotateInUpLeft", "rotateInUpRight" ],
			__( 'Rotating Exits', 'nggallery' )     => [ "rotateOut", "rotateOutDownLeft", "rotateOutDownRight", "rotateOutUpLeft", "rotateOutUpRight" ],
			__( 'Specials', 'nggallery' )           => [ "hinge", "rollIn", "rollOut" ],
			__( 'Specials', 'nggallery' )           => [ "rollIn", "rollOut" ],
			__( "Sliding Entrances", 'nggallery' )  => [ "slideInUp", "slideInDown", "slideInLeft", "slideInRight" ],
			__( 'Zoom Entrances', 'nggallery' )     => [ "zoomIn", "zoomInDown", "zoomInLeft", "zoomInRight", "zoomInUp" ]
		];
	}

	/**
	 * Get the name of this tab.
	 *
	 * @return string The name of this tab.
	 */
	public function get_name() {
		return 'slideshow';
	}

	/**
	 * Render the content that should be displayed in the tab.
	 *
	 * @return null
	 */
	public function render() {
		?>
		<form method="POST" action="<?php echo $this->page; ?>">
			<?php $this->nonce(); ?>
			<h3><?php _e('Size', 'nggallery'); ?></h3>
			<p class="description">
				<?php _e("How big (or small) should it be? You can override these settings on a per slideshow basis.", 'nggallery'); ?>
			</p>
			<table class="form-table ngg-options">
				<tr>
					<th><?php _e('Fit to space','nggallery'); ?></th>
					<td>
						<input type="checkbox" name="irAutoDim" id="irAutoDim" value="true" <?php $this->options->checked( 'irAutoDim'); ?>">
						<label for="irAutoDim"><?php _e( "Let the slideshow fit in the available space.", 'nggallery'); ?></label>
					</td>
				</tr>
				<tr>
					<th><?php _e('Default size','nggallery'); ?></th>
					<td>
						<label for="irWidth"><?php _e('Width','nggallery'); ?></label>
						<input <?php $this->readonly('irAutoDim'); ?> type="number" min="0" class="small-text" name="irWidth" id="irWidth" value="<?= $this->options['irWidth']; ?>">
						<label for="irHeight"><?php _e('Height','nggallery'); ?></label>
						<input <?php $this->readonly('irAutoDim'); ?> type="number" min="0" class="small-text" name="irHeight" id="irHeight" value="<?= $this->options['irHeight']; ?>">
					</td>
				</tr>
			</table>
			<h3><?php _e('Behavior','nggallery'); ?></h3>
			<p class="description">
				<?php _e("Change how the slideshow behaves.", 'nggallery'); ?>
			</p>
			<table class="form-table ngg-options">
				<tr>
					<th><label for="slideFx"><?php _e('Transition / Fade effect','nggallery'); ?></label></th>
					<td>
						<select size="1" name="slideFx" id="slideFx">
							<?php foreach( $this->effects as $option => $val ): ?>
								<?= $this->convert_fx_to_optgroup( $val, $option ); ?>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<?php _e("These effects are powered by"); ?> <strong>animate.css</strong>. <a target="_blank" href="http://daneden.github.io/animate.css/"><?php _e("Click here for examples of all effects and to learn more."); ?></a></p>
					</td>
				</tr>
				<tr>
					<th><?php _e('Loop','nggallery') ?></th>
					<td>
						<input type="checkbox" name="irLoop" id="irLoop" value="true" <?php $this->options->checked( 'irLoop'); ?>">
						<label for="irLoop"><?php _e( "Infinity loop. Duplicate last and first items to get loop illusion.", 'nggallery'); ?></label>
					</td>
				</tr>
				<tr>
					<th><?php _e('Autoplay','nggallery') ?></th>
					<td>
						<input type="checkbox" name="irAutoplay" id="irAutoplay" value="true" <?php $this->options->checked( 'irAutoplay' ); ?>>
						<label for="irAutoplay"><?php _e( "Automatically play the images.", 'nggallery'); ?></label>
					</td>
				</tr>
				<tr>
					<th><?php _e('Pause on hover','nggallery') ?></th>
					<td>
						<input <?php $this->options->disabled('irAutoplay', false); ?> type="checkbox" name="irAutoplayHover" id="irAutoplayHover" value="true" <?php $this->options->checked('irAutoplayHover'); ?>>
						<label for="irAutoplayHover"><?php _e( "Pause when hovering over the slideshow.", 'nggallery'); ?></label>
					</td>
				</tr>
				<tr>
					<th><?php _e('Click for next','nggallery') ?></th>
					<td>
						<input type="checkbox" name="irClick" id="irClick" value="true" <?php $this->options->checked( 'irClick' ); ?>>
						<label for="irClick"><?php _e( "Click to go to the next image.", 'nggallery'); ?></label></td>
				</tr>
				<tr>
					<th><?php _e('Number of images','nggallery') ?></th>
					<td>
						<input type="number" step="1" min="1" class="small-text" name="irNumber" id="irNumber" value="<?php echo $this->options['irNumber'] ?>">
						<label for="irNumber"><?php _e('images', 'nggallery') ;?></label>
						<p class="description"><?php _e( "Number of images to display when using random or latest.", 'nggallery'); ?></p>
					</td>
				</tr>
			</table>
			<h3><?php _e('Controls', 'nggallery') ?></h3>
			<p class="description">
				<?php _e("Options for controlling the slideshow.", 'nggallery'); ?>
			</p>
			<table class="form-table ngg-options">
				<tr>
					<th><?php _e('Mouse/touch drag','nggallery') ?></th>
					<td>
						<input type="checkbox" name="irDrag" id="irDrag" value="true" <?php $this->options->checked('irDrag'); ?>">
						<label for="irDrag"><?php _e( "Enable dragging with the mouse (or touch).", 'nggallery'); ?></label>
					</td>
				</tr>
				<tr>
					<th><?php _e('Previous / Next','nggallery') ?></th>
					<td>
						<input type="checkbox" name="irNavigation" id="irNavigation" value="true" <?php $this->options->checked( 'irNavigation' ); ?>>
						<label for="irNavigation"><?php _e( "Show next/previous buttons.", 'nggallery'); ?></label>
					</td>
				</tr>
				<tr>
					<th><label for="irRotatetime"><?php _e('Duration','nggallery') ?></label></th>
					<td>
						<input <?php $this->readonly( 'irAutoplay', false ); ?> type="number" step="1" min="0" class="small-text" name="irRotatetime" id="irRotatetime" value="<?php echo $this->options['irRotatetime'] ?>">
						<?php _e('sec.', 'nggallery') ;?>
					</td>
				</tr>
			</table>
			<h3><?php _e('Captions','nggallery'); ?></h3>
			<p class="description">
				<?php _e("The caption is shown underneath the image and does not count towards the size limit above (if set).", 'nggallery'); ?>
				TODO! This does not work at present!
			</p>
			<table class="form-table ngg-options">
				<tr>
					<th><?php _e('Display captions','nggallery'); ?></th>
					<td>
						<input type="checkbox" name="irCaption" id="irCaption" value="true" <?php $this->options->checked( 'irCaption' ); ?>">
						<label for="irCaption"><?php _e( "Display captions underneath the slideshow.", 'nggallery'); ?></label>
					</td>
				</tr>
				<tr>
					<th><label for="irCaptionSrc"><?php _e('Caption source','nggallery'); ?></label></th>
					<td>
						<select name="irCaptionSrc" id="irCaptionSrc">
							<option value="title" <?php $this->options->selected( 'irCaptionSrc', 'title')?>><?php _e( "Title", 'nggallery'); ?></option>
							<option value="desc" <?php $this->options->selected( 'irCaptionSrc', 'desc')?>><?php _e( "Description", 'nggallery'); ?></option>
						</select>
						<p class="description">
							<?php _e("Choose whether to use the title or the description as the caption.", 'nggallery'); ?>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
		<?php
	}

	/**
	 * Convert an array of slideshow styles to a html dropdown group.
	 *
	 * @param array $data   The option values (and display).
	 * @param string $title The label of the optgroup.
	 *
	 * @return string The output.
	 */
	private function convert_fx_to_optgroup( $data, $title = null ) {

		if ( is_null( $title ) ) {
			$out = null;
		} else {
			$out = "<optgroup label='$title'>";
		}

		foreach ( $data as $option ) {
			$out .= '<option value="' . $option . '" ' . $this->options->selected( 'slideFx', $option ) . '>' . $option . '</option>';
		}

		if ( ! is_null( $title ) ) {
			$out .= '</optgroup>';
		}

		return $out;
	}

	/**
	 * Handle saving the settings. The referrer is already checked at this
	 * point, so you do not need to do that.
	 */
	public function processor() {

		$this->save_booleans( [
			'irAutoDim', 'imgAutoResize', 'thumbfix', 'thumbDifferentSize', 'irLoop', 'irDrag', 'irNavigation',
			'irAutoplay', 'irAutoplayHover', 'irCaption', 'irClick'
		] );

		//Set positive integers.
		$this->save_number( [
			'imgWidth', 'imgHeight', 'imgQuality', 'thumbwidth', 'thumbheight', 'thumbquality', 'irWidth', 'irHeight',
			'irRotatetime',  'irAutoplayTimeout', 'irNumber'
		] );

		$effects = [ ];
		//Flatten the effects array.
		foreach ( $this->effects as $effect ) {
			$effects = array_merge($effects, $effect);
		}

		$this->save_restricted( [
			'slideFx'  => $effects,
			'irCaptionSrc' => ['desc', 'title'],
		] );

		//Save the options.
		$this->options->save_options();

		$this->success_message();
	}
}