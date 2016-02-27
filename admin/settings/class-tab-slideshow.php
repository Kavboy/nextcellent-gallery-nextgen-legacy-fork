<?php

namespace NextCellent\Admin\Settings;

class Tab_Slideshow extends Settings_Tab {

	/**
	 * @var array The possible effects for the slideshow.
	 */
	private $effects;

	public function __construct( $options, $page, $tabs ) {
		parent::__construct( $options, $page, $tabs );

		$this->effects = array(
			__( 'Attention Seekers', 'nggallery' )  => array( "bounce", "flash", "pulse", "rubberBand", "shake", "swing", "tada", "wobble"),
			__( 'Bouncing Entrances', 'nggallery' ) => array( "bounceIn", "bounceInDown", "bounceInLeft", "bounceInRight", "bounceInUp" ),
			__( 'Fading Entrances', 'nggallery' )   => array( "fadeIn", "fadeInDown", "fadeInDownBig", "fadeInLeft", "fadeInLeftBig", "fadeInRight", "fadeInRightBig", "fadeInUp", "fadeInUpBig"),
			__( 'Fading Exits', 'nggallery' )       => array( "fadeOut", "fadeOutDown", "fadeOutDownBig", "fadeOutLeft", "fadeOutLeftBig", "fadeOutRight", "fadeOutRightBig", "fadeOutUp", "fadeOutUpBig"),
			__( 'Flippers', 'nggallery' )           => array( "flip", "flipInX", "flipInY", "flipOutX", "flipOutY" ),
			__( 'Lightspeed', 'nggallery' )         => array( "lightSpeedIn", "lightSpeedOut"),
			__( 'Rotating Entrances', 'nggallery' )	=> array( "rotateIn", "rotateInDownLeft", "rotateInDownRight", "rotateInUpLeft", "rotateInUpRight" ),
			__( 'Rotating Exits', 'nggallery' )     => array( "rotateOut", "rotateOutDownLeft", "rotateOutDownRight", "rotateOutUpLeft", "rotateOutUpRight" ),
			__( 'Specials', 'nggallery' )           => array( "hinge", "rollIn", "rollOut" ),
			__( 'Zoom Entrances', 'nggallery' )     => array( "zoomIn", "zoomInDown", "zoomInLeft", "zoomInRight", "zoomInUp" )
		);
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
		<form name="player_options" method="POST" action="<?php echo $this->page; ?>">
			<?php $this->nonce(); ?>
			<input type="hidden" name="page_options" value="irAutoDim,slideFx,irWidth,irHeight,irRotatetime,irLoop,irDrag,irNavigation,irNavigationDots,irAutoplay,irAutoplayTimeout,irAutoplayHover,irNumber,irClick" />
			<h3><?php _e('Slideshow','nggallery'); ?></h3>
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
						<input <?php $this->readonly('irAutoDim'); ?> type="number" min="0" class="small-text" name="irWidth" id="irWidth" value="<?php echo $this->options['irWidth']; ?>">
						<label for="irHeight"><?php _e('Height','nggallery'); ?></label>
						<input <?php $this->readonly('irAutoDim'); ?> type="number" min="0" class="small-text" name="irHeight" id="irHeight" value="<?php echo $this->options['irHeight']; ?>">
					</td>
				</tr>
				<tr>
					<th><label for="slideFx"><?php _e('Transition / Fade effect','nggallery'); ?></label></th>
					<td>
						<select size="1" name="slideFx" id="slideFx">
							<?php
							foreach( $this->effects as $option => $val ) {
								echo $this->convert_fx_to_optgroup( $val, $option );
							}
							?>
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
					<th><?php _e('Show dots','nggallery') ?></th>
					<td>
						<input type="checkbox" name="irNavigationDots" id="irNavigationDots" value="true" <?php $this->options->checked( 'irNavigationDots' ); ?>>
						<label for="irNavigationDots"><?php _e( "Show dots for each image.", 'nggallery'); ?></label>
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
					<th><label for="irRotatetime"><?php _e('Duration','nggallery') ?></label></th>
					<td>
						<input <?php $this->readonly( 'irAutoplay', false ); ?> type="number" step="1" min="0" class="small-text" name="irRotatetime" id="irRotatetime" value="<?php echo $this->options['irRotatetime'] ?>">
						<?php _e('sec.', 'nggallery') ;?>
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

		$this->save_booleans(array(
			'irAutoDim', 'imgAutoResize', 'thumbfix', 'thumbDifferentSize', 'irLoop', 'irDrag', 'irNavigation',
			'irNavigationDots', 'irAutoplay', 'irAutoplayHover', 'irClick'
		));

		//Set positive integers.
		$this->save_number(array(
			'imgWidth', 'imgHeight', 'imgQuality', 'thumbwidth', 'thumbheight', 'thumbquality', 'irWidth', 'irHeight',
			'irRotatetime',  'irAutoplayTimeout', 'irNumber'
		));

		$effects = array();
		//Flatten the effects array.
		foreach ( $this->effects as $effect ) {
			$effects = array_merge($effects, $effect);
		}

		$this->save_restricted(array(
			'slideFx'  => $effects
		));

		//Save the options.
		$this->options->save_options();

		$this->success_message();
	}
}