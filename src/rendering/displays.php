<?php
/**
 * Display modes for NextCellent.
 */
namespace NextCellent\Rendering;

use NextCellent\Database\Not_Found_Exception;
use NextCellent\Models\Image_Collection;
use NextCellent\Models\Images;
use NextCellent\Models\Gallery;
use NextCellent\Options\Options;

const MAX_IMAGES = PHP_INT_MAX;

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
	
	global $ncg;
	$options = $ncg->options;

	try {
		//Get the gallery.
		$gallery = Gallery::find( $gallery_id );

		$sort_column = $options->get( Options::GAL_SORT );
		$sort_direction = $options->get( Options::GAL_SORT_DIRECTION );
		$hidden = $options->get( Options::GAL_SHOW_ALL_IMAGES );

		$gallery->load_images($sort_column, $sort_direction, $hidden);

		return render_gallery($gallery, $template, $nr_of_images);

	} catch (Not_Found_Exception $e) {
		return Renderer::render_exception( $e );
	}
}

/**
 * Renders a gallery.
 *
 * @param Images $images       The images to display.
 * @param string $template     The name of the template to use.
 * @param bool|int   $nr_of_images The number of images to display.
 *
 * @param bool   $modeLink
 *
 * @return string
 */
function render_gallery(Images $images, $template = 'gallery', $nr_of_images = false, $modeLink = true) {

	//Counter for an unique ID.
	static $counter = 0;

	global $ncg;
	$options = $ncg->options;

	$images_per_page = ($nr_of_images === false) ? $options->get( Options::GAL_IMAGES_PER_PAGE ) : $nr_of_images;
	$page = (int) get_query_var('ncg-page', 1);
	if($nr_of_images === MAX_IMAGES) {
		$start = 0;
	} else {
		$start = ($page - 1) * $images_per_page;
	}

	//Decide mode
	if($modeLink && $options->get( Options::GAL_SHOW_SLIDESHOW )) {
		if(get_query_var( 'ncg-mode', 'gallery' ) === 'slideshow') {
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
		return render_slideshow( $images, true );
	}

	//Gallery mode
	$data = [
		'images'            => $images->getImages($start, $images_per_page),
		'pagination'        => new Pagination( $page, $images_per_page, $images->total() ),
		'anchor'            => 'ngg-gallery-' . $counter++,
		'mode_link'         => $mode_link,
		'mode_link_text'    => $mode_link_text,
	];

	$renderer = new Renderer( $template );
	return $renderer->get_rendered( $data );
}


/**
 * Render a slideshow.
 *
 * @param Images   $images   The images to display.
 * @param string   $template The template to display with.
 * @param array    $args     The arguments.
 * @param bool      $galleryMode
 *
 * @return string
 */
function render_slideshow(Images $images, $galleryMode = false, array $args = [], $template = 'slideshow') {

	//Counter for an unique ID.
	static $counter = 0;

	global $ncg;
	$options = $ncg->options;

	$param = wp_parse_args( $args, [
		'width'       => $options[ Options::SLIDE_WIDTH ],
		'height'      => $options[ Options::SLIDE_HEIGHT ],
		'anchor'      => 'ncg-slideshow-' . $counter++,
		'time'        => $options[ Options::SLIDE_TIME ] * 1000,
		'loop'        => $options[ Options::SLIDE_USE_LOOP ],
		'drag'        => $options[ Options::SLIDE_USE_DRAG ],
		'nav'         => $options[ Options::SLIDE_SHOW_NAV ],
		'auto_play'   => $options[ Options::SLIDE_AUTO_PLAY ],
		'hover'       => $options[ Options::SLIDE_PAUSE_ON_HOVER ],
		'effect'      => $options[ Options::SLIDE_EFFECT ],
		'click'       => $options[ Options::SLIDE_NEXT_ON_CLICK ],
		'auto_dim'    => $options[ Options::SLIDE_FIT_SIZE ],
		'number'      => $options[ Options::SLIDE_NR_OF_IMAGES ],
		'caption'     => $options[ Options::SLIDE_USE_CAPTION ],
		'caption_src' => $options[ Options::SLIDE_CAPTION_SOURCE ]
	] );

	if ($galleryMode) {
		$link = Rewrite::get_link([
			'ncg-mode' => 'gallery'
		]);
		$link_text = $options->get( Options::GAL_GALLERY_TEXT );
	} else {
		$link = false;
		$link_text = false;
	}

	if($param['caption'] === false) {
		$param['caption_src'] = null;
	}

	$data = array_merge( $param, [
		'images'         => $images->getImages(),
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
 * @param null|bool       $nav
 * @param string     $template
 *
 * @return string
 */
function render_slideshow_shortcode($id, $width, $height, $nr_of_images = null, $nav = null, $template = 'slideshow') {

	$data = [];

	if($nr_of_images !== null) {
		$data['number'] = $nr_of_images;
	} else {
		global $ncg;
		$data['number'] = $ncg->options->get( Options::SLIDE_NR_OF_IMAGES );
	}
	
	$col = null;
	if($id === 'random') {
		$col = Image_Collection::random( $data['number'] );
	} elseif ($id === 'recent') {
		$col = Image_Collection::recent($data['number']);
	} elseif (is_numeric( $id )) {
		try {
			$col = Gallery::find( $id );
			$col->load_images();
		} catch (Not_Found_Exception $e) {
			return Renderer::render_exception( $e );
		}
	}
	
	if($width === null && $height === null) {
		$data['auto_dim'] = true;
	} else {
		if($width !== null) {
			$data['width'] = $width;
		}
		if($height !== null) {
			$data['height'] = $height;
		}
	}
	
	if($nav !== null) {
		$data['nav'] = $nav;
	}

	return render_slideshow( $col, false, $data, $template );
}

/**
 * Renders random images.
 *
 * @param int    $maxImages
 * @param string $template The name of the template to use.
 * @param array  $ids
 *
 * @param bool   $modeLink
 *
 * @return string
 */
function render_random_shortcode($maxImages, $template = 'gallery', $ids = null, $modeLink = true) {
	$images = Image_Collection::random($maxImages, $ids);
	return render_gallery($images, $template, MAX_IMAGES, $modeLink);
}

/**
 * Renders recent images.
 *
 * @param int    $maxImages
 * @param string $template The name of the template to use.
 * @param array  $ids
 *
 * @param bool   $modeLink
 *
 * @return string
 */
function render_recent_shortcode($maxImages, $template = 'gallery', $ids = null, $modeLink = true) {
	$images = Image_Collection::recent($maxImages, $ids);
	return render_gallery($images, $template, MAX_IMAGES, $modeLink);
}