<?php
/**
 * Display modes for NextCellent.
 */
namespace NextCellent\Rendering;

use NextCellent\Models\Gallery;
use NextCellent\Options\Options;

function render_gallery($gallery_id, $template = 'gallery', $nr_of_images = false) {
	
	global $ncg;
	
	$options = $ncg->options;

	//Get data
	$gallery = Gallery::find( $gallery_id );
	$sort_column = $options->get( Options::GAL_SORT );
	$sort_direction = $options->get( Options::GAL_SORT_DIRECTION );
	$images_per_page = ($nr_of_images === false) ? $options->get( Options::GAL_IMAGES_PER_PAGE ) : $nr_of_images;
	$page = (int) get_query_var('ncg-page', 1);
	$start = ($page - 1) * $images_per_page;
	$hidden = $options->get( Options::GAL_SHOW_ALL_IMAGES );

	$gallery->load_images($sort_column, $sort_direction, $start, $images_per_page, $hidden);
	
	//Decide display mode
	//TODO: add slideshow mode
	
	//Gallery mode
	$data = [
		'gallery'       => $gallery,
		'pagination'    => new Pagination( $page, $images_per_page, $gallery->count_images() ),
		'gallery_id'    => 'ngg-gallery-' . $gallery->id,
	];
	
	$renderer = new Renderer( $template );
	return $renderer->get_rendered( $data );
}