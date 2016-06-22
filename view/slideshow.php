<?php
/**
 * This is the base template for a slideshow. This is currently not modifiable by the user, so this template
 * will always be used.
 *
 * Note: please edit this file carefully. Syntax errors in this file will cause PHP to stop execution,
 * and in most cases, fail silently.
 * 
 * Following variables are available for use:
 * @var array $images The images to display. It contains the images for this page.
 * @var int $width The width of the slideshow
 * @var int $height The height of the slideshow.
 * @var string $class The class of the slideshow
 * @var string $anchor The anchor for the slideshow.
 * @var int $time The time one image is displayed.
 * @var bool $loop If the slideshow should loop or not.
 * @var bool $drag If the slideshow is draggable or not.
 * @var bool $nav If the navigation should be displayed or not.
 * @var bool $nav_dots If the navigation dots should be displayed.
 * @var bool $auto_play If the slideshow should play automatically.
 * @var bool $hover Pause on hover.
 * @var string $effect The effect.
 * @var bool $click Click for next image or not.
 * @var bool $auto_dim Fit the slideshow to the available space.
 * @var int $number Number of images.
 * @var bool $mode_link Link if applicable, otherwise false.
 * @var string $mode_link_text The text for the link.
 */

if( !$auto_dim ) {
	$style = 'style="max-width: ' .  $width . 'px; max-height: '. $height . 'px;"';
	$i_style = 'style="max-width: ' .  $width . 'px; max-height: '. $height . 'px; width: auto; height:auto; margin:auto"';
} else {
	$style   = '';
	$i_style = '';
}
?>

<div class="ncg-slideshow-container">

	<?php if ($mode_link !== false) { ?>
		<!-- Slideshow link -->
		<a class="mode-link" href="<?= $mode_link ?>"><?= $mode_link_text ?></a>
	<?php } ?>

	<div class="owl-carousel" <?= $style ?> id="<?= $anchor ?>">
		<?php foreach ($images as $image): ?>
			<img src="<?= $image->url ?>" alt="<?php esc_attr( $image->alt_text ) ?>" <?= $i_style ?>>
		<?php endforeach; ?>
	</div>

	<script type="text/javascript">
		jQuery(document).ready(function($) {
			var owl = $('#<?= $anchor ?>');
			owl.owlCarousel({
				items: 1,
				autoHeight: <?= var_export($auto_dim, true) ?>,
				<?php if($nav) : ?>
				nav: true,
				navText: ['<?php _e('previous', 'nggallery') ?>','<?php _e('next', 'nggallery') ?>'],
				<?php endif; ?>
				dots: <?= var_export($nav_dots, true) ?>,
				autoplay: <?= var_export($auto_play, true) ?>,
				autoplayTimeout: <?= $time ?>,
				autoplayHoverPause: <?= var_export($hover, true) ?>,
				animateIn: '<?= $effect ?>',
				animateOut: 'fadeOut',
				loop: <?= var_export($loop, true) ?>,
				mouseDrag: <?= var_export($drag, true) ?>,
				touchDrag: <?= var_export($drag, true) ?>
			});
			<?php if($click): ?>
			owl.click(function() {
				owl.trigger( 'next.owl.carousel' );
			});
			<?php endif; ?>
		});
	</script>
</div>