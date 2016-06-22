<?php
/**
 * Display modes for NextCellent.
 */
namespace NextCellent\Rendering;

use NextCellent\Database\Not_Found_Exception;
use NextCellent\Models\Displayable_Images;
use NextCellent\Models\Gallery;
use NextCellent\Models\Image_Collection;
use NextCellent\Options\Options;

/**
 * Renders a gallery.
 *
 * @param int    $gallery_id The ID of the gallery.
 * @param string $template The name of the template to use.
 * @param bool   $nr_of_images The number of images to display.
 *
 * @return string
 */
function render_gallery_shortcode($gallery_id, $template = 'gallery', $nr_of_images = false) {

	//Counter for an unique ID.
	static $counter = 0;
	
	global $ncg;
	
	$options = $ncg->options;

	//TODO: use null or try catch?
	try {
		//Get the gallery.
		$gallery = Gallery::find( $gallery_id );

		$sort_column = $options->get( Options::GAL_SORT );
		$sort_direction = $options->get( Options::GAL_SORT_DIRECTION );
		$images_per_page = ($nr_of_images === false) ? $options->get( Options::GAL_IMAGES_PER_PAGE ) : $nr_of_images;
		$page = (int) get_query_var('ncg-page', 1);
		$start = ($page - 1) * $images_per_page;
		$hidden = $options->get( Options::GAL_SHOW_ALL_IMAGES );

		//Decide mode
		if($options->get( Options::GAL_SHOW_SLIDESHOW )) {
			if(get_query_var( 'ncg-mode', 'gallery' ) == 'slideshow') {
				$mode = 'slideshow';
				$mode_link = false;
				$mode_link_text = false;
			} else {
				$mode = false;
				$mode_link_text = $options->get( Options::GAL_SLIDESHOW_TEXT );
				$mode_link = Rewrite::get_link(['ncg-mode' => 'slideshow']);
			}

		} else {
			$mode = false;
			$mode_link = false;
			$mode_link_text = false;
		}

		//Do slideshow if needed and stop.
		if($mode === 'slideshow') {
			$gallery->load_images($sort_column, $sort_direction, 0, 0, $hidden);
			return render_slideshow( $gallery, $gallery_id );
		}

		$gallery->load_images($sort_column, $sort_direction, $start, $images_per_page, $hidden);

		//Gallery mode
		$data = [
			'gallery'           => $gallery,
			'pagination'        => new Pagination( $page, $images_per_page, $gallery->count_images() ),
			'gallery_id'        => 'ngg-gallery-' . $gallery->id . '-' . $counter++,
			'mode_link'         => $mode_link,
			'mode_link_text'    => $mode_link_text,
		];

		$renderer = new Renderer( $template );
		return $renderer->get_rendered( $data );
	} catch (Not_Found_Exception $e) {
		return Renderer::render_exception( $e );
	}
}

/**
 * Render a slideshow.
 *
 * @param Displayable_Images $images   The images to display.
 * @param string             $template The template to display with.
 * @param array              $args     The arguments.
 * @param int|null           $gallery_id
 *
 * @return string
 */
function render_slideshow($images, $gallery_id = null, $args = [], $template = 'slideshow') {

	//Counter for an unique ID.
	static $counter = 0;

	global $ncg;
	$options = $ncg->options;

	$param = wp_parse_args( $args, [
		'width'     => $options[ Options::SLIDE_WIDTH ],
		'height'    => $options[ Options::SLIDE_HEIGHT ],
		'anchor'    => 'ncg-slideshow-' . $counter++,
		'time'      => $options[ Options::SLIDE_TIME ] * 1000,
		'loop'      => $options[ Options::SLIDE_USE_LOOP ],
		'drag'      => $options[ Options::SLIDE_USE_DRAG ],
		'nav'       => $options[ Options::SLIDE_SHOW_NAV ],
		'nav_dots'  => $options[ Options::SLIDE_SHOW_NAV_DOTS ],
		'auto_play' => $options[ Options::SLIDE_AUTO_PLAY ],
		'hover'     => $options[ Options::SLIDE_PAUSE_ON_HOVER ],
		'effect'    => $options[ Options::SLIDE_EFFECT ],
		'click'     => $options[ Options::SLIDE_NEXT_ON_CLICK ],
		'auto_dim'  => $options[ Options::SLIDE_FIT_SIZE ],
		'number'    => $options[ Options::SLIDE_NR_OF_IMAGES ]
	] );

	if ( $gallery_id !== null ) {
		$link      = Rewrite::get_link( [
			'ncg-mode' => 'gallery'
		] );
		$link_text = $options->get( Options::GAL_GALLERY_TEXT );
	} else {
		$link      = false;
		$link_text = false;
	}

	$data = array_merge( $param, [
		'images'         => $images->get_images(),
		'mode_link'      => $link,
		'mode_link_text' => $link_text
	] );

	$renderer = new Renderer( $template );
	return $renderer->get_rendered( $data );
}

/**
 * @param int|string $id
 * @param int|null   $width
 * @param int|null   $height
 * @param int|null   $nr_of_images
 * @param string     $template
 *
 * @return string
 */
function render_slideshow_shortcode($id, $width, $height, $nr_of_images, $template = 'slideshow') {

	$data = [];

	if($nr_of_images != null) {
		$data['number'] = $nr_of_images;
	} else {
		global $ncg;
		$data['number'] = $ncg->options->get( Options::SLIDE_NR_OF_IMAGES );
	}
	
	$col = null;
	if($id === 'random') {
		$col = Image_Collection::get_random( $data['number'] );
	} elseif ($id === 'recent') {
		$col = null;
	} elseif (is_numeric( $id )) {
		try {
			$col = Gallery::find( $id );
		} catch (Not_Found_Exception $e) {
			return Renderer::render_exception( $e );
		}
	}

	if($width != null || $height != null) {
		if($width != null) {
			$data['width'] = $width;
		}
		if($height != null) {
			$data['height'] = $height;
		}
	} else {
		$data['auto_dim'] = true;
	}

	return render_slideshow( $col, null, $data, $template );
}