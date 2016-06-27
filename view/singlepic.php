<?php 
/**
 * 
 * This is the base template for a single picture.
 *
 * Note: please edit this file carefully. Syntax errors in this file will cause PHP to stop execution,
 * and in most cases, fail silently.
 * 
 * @var \NextCellent\Models\Image $image The image to display.
 * @var bool $caption If you should display a caption or not.
 * @var string $captionText The text to use as caption.
**/
?>
<div class="ncg-image-wrapper">
	<a href="<?= $image->url ?>" title="<?= esc_attr($image->alt_text) ?>">
		<img class="ncg-image" src="<?= $image->thumb_url ?>" alt="<?= esc_attr($image->alt_text) ?>">
	</a>
	<?php if($caption): ?>
	<span><?= $captionText ?></span>
	<?php endif; ?>
</div>