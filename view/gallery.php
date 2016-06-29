<?php
/**
 * This is the base template for a gallery.
 *
 * Note: please edit this file carefully. Syntax errors in this file will cause PHP to stop execution,
 * and in most cases, fail silently.
 * 
 * Following variables are available for use:
 * @var \NextCellent\Models\Image[] $images The images. It contains the images for this page.
 * @var \NextCellent\Rendering\Pagination $pagination The pagination. It implements toString, so you can just echo it.
 * @var string $anchor An id for the gallery.
 * @var string|bool $mode_link The link to the other mode or false if disabled.
 * @var string $mode_link_text The text for the link.
 */
?>

<div class="ngg-galleryoverview ncg-gallery-overview" id="<?= $anchor ?>">

	<?php if ($mode_link !== false) { ?>
		<!-- Slideshow link -->
		<div class="slideshowlink ncg-mode-link-wrapper">
			<a class="slideshowlink ncg-mode-link" href="<?= $mode_link ?>"><?= $mode_link_text ?></a>
		</div>
	<?php } ?>

	<!-- Thumbnails -->
	<?php foreach ( $images as $image ) : ?>

	<div id="ngg-image-<?= $image->id ?>" class="ngg-gallery-thumbnail-box ncg-thumbnail-wrapper">
		<div class="ngg-gallery-thumbnail ncg-thumbnail" >
			<a href="<?= $image->url ?>" title="<?= $image->description ?>">
				<img title="<?= $image->alt_text ?>" alt="<?= $image->alt_text ?>" src="<?= $image->thumb_url ?>" />
			</a>
		</div>
	</div>

	<?php endforeach; ?>

	<!-- Pagination -->
	<?= $pagination ?>
</div>