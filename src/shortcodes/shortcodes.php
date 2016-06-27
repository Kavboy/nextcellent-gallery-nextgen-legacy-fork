<?php
/**
 * Contains the shortcodes.
 *
 * @author Niko
 */
namespace NextCellent\Shortcodes;

/**
 * Function to show a single picture. Syntax:
 *
 * [ncg_single id="10" float="none|left|right" width="" height="" mode="none|watermark" link="url" template="filename" /]
 * [singlepic  id="10" float="none|left|right" width="" height="" mode="none|watermark" link="url" template="filename" /]
 *
 * where
 *  - id is the ID of an image
 *  - float is the CSS float property to apply to the thumbnail
 *  - width is width of the single picture you want to show (original width if this parameter is missing)
 *  - height is height of the single picture you want to show (original height if this parameter is missing)
 *  - mode is one of none, watermark
 *  - link is optional and could link to a other url instead the full image
 *  - template is a name for a gallery template, which is located in themefolder/nggallery or plugins/nextgen-gallery/view
 *
 * If the tag contains some text, this will be inserted as an additional caption to the picture too. Example:
 *      [singlepic id="10"]This is an additional caption[/singlepic]
 *
 * This tag will show a picture with under it two HTML span elements containing respectively the alttext of the picture
 * and the additional caption specified in the tag.
 *
 * @param array  $attr
 * @param string $content
 *
 * @return string
 */
function show_single_picture($attr, $content = '') {

	$attr = shortcode_atts([
		'id'       => 0,
		'float'    => '',
		'width'    => '',
		'height'   => '',
		'mode'     => '',
		'link'     => '',
		'template' => ''
	], $attr);

	return nggSinglePicture($attr['id'], $attr['width'], $attr['float'], $attr['mode'], $attr['float'], $attr['template'], $content, $attr['link']);
}

/**
 * Function to show a collection of galleries. Syntax:
 *
 * [nggalbum  id="all|10" template="filename" gallery_template="filename"|gallery="filename" /]
 * [ncg_album id="all|10" template="filename" gallery_template="filename" /]
 *
 * where
 * - id of a album or "all" to display an album of all galleries.
 * - template is a name for a album template, which is located in themefolder/nggallery or plugins/nextgen-gallery/view
 * - gallery is the legacy name for gallery_template
 * - gallery_template
 *
 * @param array $attr
 *
 * @return string
 */
function show_album($attr) {

	if(isset($attr['gallery'])) {
		$attr['gallery_template'] = $attr['gallery'];
		unset($attr['gallery']);
	}

	$attr = shortcode_atts([
		'id'                => 0,
		'template'          => 'extend',
		'gallery_template'  => ''
	], $attr);

	return nggShowAlbum($attr['id'], $attr['template'], $attr['gallery_template']);
}

/**
 * Function to show a thumbnail or a set of thumbnails with shortcode of type. Syntax:
 *
 * [nggallery   id="10" template="filename" images="number of images per page" /]
 * [ncg_gallery id="10" template="filename" images="number of images per page" /]
 *
 * where
 * - id of a gallery
 * - images is the number of images per page (optional), 0 will show all images
 * - template is a name for a gallery template, which is located in themefolder/nggallery or plugins/nextgen-gallery/view
 *
 * @param array $attr
 *
 * @return string
 */
function show_gallery($attr) {

	$attr = shortcode_atts([
		'id'       => 0,
		'template' => 'gallery',
		'images'   => false
	], $attr);

	return \NextCellent\Rendering\render_gallery_shortcode( $attr['id'], $attr['template'], $attr['images'] );
}

/**
 * Function to show an image browser. Syntax:
 *
 * [imagebrowser      id="10" template="filename" /]
 * [ncg_image_browser id="10" template="filename" /]
 *
 * where
 * - id of a gallery
 * - template is a name for a gallery template, which is located in themefolder/nggallery or plugins/nextgen-gallery/view
 *
 * @param array $attr
 *
 * @return string
 */
function show_image_browser($attr) {

	$attr = shortcode_atts([
		'id'       => 0,
		'template' => ''
	], $attr);

	return nggShowImageBrowser($attr['id'], $attr['template']);
}

/**
 * Render a slideshow. Syntax:
 *
 * [slideshow id="10|random|recent" width=""|w="" height=""|h="" nav="true|false" /]
 * [ncg_slideshow id="10" width="" height="" images="10" nav="true|false"/]
 *
 * where
 * - id is the id of the gallery or "random" for random images or "recent" for recent images.
 * - width is the width of the slideshow
 * - height is the height of the slideshow
 * - images is the number of images when using random or recent
 *
 * @param array $attr array The shortcode attributes.
 *                    
 * @return string
 */
function show_slideshow($attr) {

	if(isset( $attr['w'] )) {
		$attr['width'] = $attr['w'];
		unset($attr['w']);
	}

	if(isset($attr['h'])) {
		$attr['height'] = $attr['h'];
		unset($attr['h']);
	}

	$attr = shortcode_atts( [
		'id'     => 'random',
		'width'  => null,
		'height' => null,
		'images' => null,
		'nav'    => null
	], $attr);
	
	return \NextCellent\Rendering\render_slideshow_shortcode($attr['id'], $attr['width'], $attr['height'], $attr['images'], $attr['nav']);
}

/**
 * nggtags shortcode implementation
 * 20140120: Improved: template option.
 * Reference: based on improvement of Tony Howden's code
 * http://howden.net.au/thowden/2012/12/nextgen-gallery-wordpress-nggtags-template-caption-option/
 * Included template to galleries and albums
 * Included sorting mode: ASC/DESC/RAND
 * 
 * @todo Documentation
 *
 * @param array $attr
 *
 * @return string
 */
function show_tags($attr) {

	$attr = shortcode_atts([
		'gallery'  => '',
		'album'    => '',
		'template' => '',
		'sort'     => ''
	], $attr);

	//gallery/album contains tag list comma separated of terms to filtering out.
	//Counterintuitive: I'd like something like tags='red,green' and then to specify album/gallery instead.
	$modes = array('ASC', 'DESC', 'RAND');

	$sorting = strtoupper($attr['sort']);

	if ( ! in_array(strtoupper($sorting), $modes)) {
		$sorting = 'NOTSET';
	}

	if ( !empty($attr['album'])) {
		$out = nggShowAlbumTags($attr['album'], $attr['template'], $sorting);
	} else {
		$out = nggShowGalleryTags($attr['gallery'], $attr['template'], $sorting);
	}

	return $out;
}

/**
 * Function to show a thumbnail or a set of thumbnails with shortcode of type:
 *
 * [thumb id="1,2,3..." template="filename" /]
 * [ncg_thumbnails id="1,2,3,..." template="filename" /]
 * 
 * where
 * - id is one or more picture ids
 * - template is a name for a gallery template, which is located in themefolder/nggallery or plugins/nextgen-gallery/view
 *
 * @param array $attr
 *
 * @return string
 */
function show_thumbnails($attr) {

	$attr = shortcode_atts([
		'id'       => '',
		'template' => ''
	], $attr);

	// make an array out of the ids
	$picture_ids = explode(',', $attr['id']);
	
	// Some error checks
	if (count($picture_ids) == 0) {
		return __('[Pictures not found]', 'nggallery');
	}

	$picture_list = \nggdb::find_images_in_list($picture_ids);

	// show gallery
	if (is_array($picture_list)) {
		$out = nggCreateGallery($picture_list, false, $attr['template']);
	} else {
		$out = '';
	}

	return $out;
}

/**
 * Function to show a gallery of random or the most recent images with shortcode of type:
 *
 * [random     max="7" template="filename" id="2" /]
 * [ncg_random max="7" template="filename" id="2,3,..." /]
 *
 * where
 * - max is the maximum number of random or recent images to show
 * - template is a name for a gallery template, which is located in themefolder/nggallery or plugins/nextgen-gallery/view
 * - id are the gallery ids, if the recent/random pictures shall be taken from a specific gallery only
 *
 * @param array $attr
 *
 * @return string
 */
function show_random($attr) {

	if(isset($attr['id'])) {
		$attr['id'] = explode(',', $attr['id']);
	}

	$attr = shortcode_atts([
		'max'      => '',
		'template' => 'gallery',
		'id'       => null
	], $attr);

	if(empty($attr['id'])) {
		$attr['id'] = null;
	}

	return \NextCellent\Rendering\render_random_shortcode($attr['max'], $attr['template'], $attr['id']);
}

/**
 * Function to show a gallery of random or the most recent images with shortcode of type:
 *
 * [recent max="7" template="filename" id="3" mode="date" /]
 * [ncg_recent max="7" template="filename" id="2,3,..." mode="date" /]
 * 
 * where
 * - max is the maximum number of random or recent images to show
 * - template is a name for a gallery template, which is located in themefolder/nggallery or plugins/nextgen-gallery/view
 * - id is the gallery ids, if the recent/random pictures shall be taken from a specific gallery only
 * - mode is either "id" (which takes the latest additions to the databse, default)
 *               or "date" (which takes the latest pictures by EXIF date)
 *               or "sort" (which takes the pictures by user sort order)
 *
 * @param array $attr
 * 
 * @todo Enable id/date/sort things.
 *
 * @return string
 */
function show_recent($attr) {

	if(isset($attr['id'])) {
		$attr['id'] = explode(',', $attr['id']);
	}

	$attr = shortcode_atts([
		'max'      => '',
		'template' => 'gallery',
		'id'       => null
	], $attr);

	if(empty($attr['id'])) {
		$attr['id'] = null;
	}
	
	return \NextCellent\Rendering\render_recent_shortcode($attr['max'], $attr['template'], $attr['id']);
}

/**
 * Shortcode for the Image tag cloud. Syntax:
 * 
 * [tagcloud template="filename" /]
 * [ncg_tag_cloud template="filename" /]
 *
 * @param array $attr
 *
 * @return string
 */
function show_tag_cloud($attr) {

	$attr = shortcode_atts([
		'template' => ''
	], $attr);
	
	return nggTagCloud('', $attr['template']);
}