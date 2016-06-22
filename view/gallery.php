<?php
/**
 * This is the base template for a gallery.
 *
 * Note: please edit this file carefully. Syntax errors in this file will cause PHP to stop execution,
 * and in most cases, fail silently.
 * 
 * Following variables are available for use:
 * @var NextCellent\Models\Gallery $gallery The gallery to display. It contains the images for this page.
 * @var \NextCellent\Rendering\Pagination $pagination The pagination. It implements toString, so you can just echo it.
 * @var string $anchor An id for the gallery.
 * @var string|bool $mode_link The link to the other mode or false if disabled.
 * @var string $mode_link_text The text for the link.
 */
?>

<div class="ngg-galleryoverview" id="<?= $anchor ?>">

	<?php if ($mode_link !== false) { ?>
		<!-- Slideshow link -->
		<a class="mode-link" href="<?= $mode_link ?>"><?= $mode_link_text ?></a>
	<?php } ?>

	<!-- Thumbnails -->
	<?php foreach ( $gallery->images as $image ) : ?>

	<div id="ngg-image-<?= $image->id ?>" class="ngg-gallery-thumbnail-box">
		<div class="ngg-gallery-thumbnail" >
			<a href="<?= $image->url ?>" title="<?= $image->description ?>">
				<img title="<?= $image->alt_text ?>" alt="<?= $image->alt_text ?>" src="<?= $image->thumb_url ?>" />
			</a>
		</div>
	</div>

	<?php endforeach; ?>

	<!-- Pagination -->
	<?= $pagination ?>
</div>