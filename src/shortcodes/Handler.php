<?php

namespace NextCellent\Shortcodes;

use NextCellent\Options\Options;

require_once('shortcodes.php');

/**
 * @author  Niko Strijbol
 * @version 15/06/2016
 */
class Handler {

	private $options;

	/**
	 * @param Options $options
	 */
	public function __construct($options) {
		$this->options = $options;
	}

	public function register_shortcodes() {
		//Support legacy shortcodes. Disabled by default.
		if($this->options->get(Options::LEGACY_SHORTCODES)) {
			add_filter('the_content', array($this, 'legacy_convert'));
		}
		
		//Support older shortcodes, enabled by default. Turn this off in case of conflict.
		if($this->options->get(Options::OLD_SHORTCODES)) {
			add_shortcode( 'singlepic', __NAMESPACE__ . '\\show_single_picture' );
			add_shortcode( 'album', __NAMESPACE__ . '\\show_album' );
			add_shortcode( 'nggalbum', __NAMESPACE__ . '\\show_album' );
			add_shortcode( 'nggallery',  __NAMESPACE__ . '\\show_gallery' );
			add_shortcode( 'imagebrowser',  __NAMESPACE__ . '\\show_image_browser' );
			add_shortcode( 'slideshow',  __NAMESPACE__ . '\\show_slideshow' );
			add_shortcode( 'nggtags', __NAMESPACE__ . '\\show_tags' );
			add_shortcode( 'thumb', __NAMESPACE__ . '\\show_thumbnails' );
			add_shortcode( 'random', __NAMESPACE__ . '\\show_random' );
			add_shortcode( 'recent', __NAMESPACE__ . '\\show_recent' );
			add_shortcode( 'tagcloud', __NAMESPACE__ . '\\show_tag_cloud' );
		}

		add_shortcode( 'ncg_single', __NAMESPACE__ . '\\show_single_picture' );
		add_shortcode( 'ncg_album', __NAMESPACE__ . '\\show_album' );
		add_shortcode( 'ncg_gallery',  __NAMESPACE__ . '\\show_gallery' );
		add_shortcode( 'ncg_image_browser',  __NAMESPACE__ . '\\show_image_browser' );
		add_shortcode( 'ncg_slideshow',  __NAMESPACE__ . '\\show_slideshow' );
		add_shortcode( 'ncg_tags', __NAMESPACE__ . '\\show_tags' );
		add_shortcode( 'ncg_thumbnails', __NAMESPACE__ . '\\show_thumbnails' );
		add_shortcode( 'ncg_random', __NAMESPACE__ . '\\show_random' );
		add_shortcode( 'ncg_recent', __NAMESPACE__ . '\\show_recent' );
		add_shortcode( 'ncg_tag_cloud', __NAMESPACE__ . '\\show_tag_cloud' );
	}

	/**
	 * Convert the legacy shortcodes.
	 *
	 * @param string $content The content.
	 * @return string The converted content.
	 */
	public function legacy_convert($content) {
		if (stripos( $content, '[singlepic' ) !== false) {
			$search = "@\[singlepic=(\d+)(|,\d+|,)(|,\d+|,)(|,watermark|,web20|,)(|,right|,center|,left|,)\]@i";
			if (preg_match_all($search, $content, $matches, PREG_SET_ORDER)) {

				foreach ($matches as $match) {
					// remove the comma
					$match[2] = ltrim($match[2], ',');
					$match[3] = ltrim($match[3], ',');
					$match[4] = ltrim($match[4], ',');
					$match[5] = ltrim($match[5], ',');
					$replace = "[singlepic id=\"{$match[1]}\" w=\"{$match[2]}\" h=\"{$match[3]}\" mode=\"{$match[4]}\" float=\"{$match[5]}\" ]";
					$content = str_replace ($match[0], $replace, $content);
				}
			}
		}

		if ( stripos( $content, '[album' ) !== false) {
			$search = "@(?:<p>)*\s*\[album\s*=\s*(\w+|^\+)(|,extend|,compact)\]\s*(?:</p>)*@i";
			if (preg_match_all($search, $content, $matches, PREG_SET_ORDER)) {

				foreach ($matches as $match) {
					// remove the comma
					$match[2] = ltrim($match[2],',');
					$replace = "[nggalbum id=\"{$match[1]}\" template=\"{$match[2]}\"]";
					$content = str_replace ($match[0], $replace, $content);
				}
			}
		}

		if ( stripos( $content, '[gallery' ) !== false) {
			$search = "@(?:<p>)*\s*\[gallery\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i";
			if (preg_match_all($search, $content, $matches, PREG_SET_ORDER)) {

				foreach ($matches as $match) {
					$replace = "[nggallery id=\"{$match[1]}\"]";
					$content = str_replace ($match[0], $replace, $content);
				}
			}
		}

		if ( stripos( $content, '[imagebrowser' ) !== false) {
			$search = "@(?:<p>)*\s*\[imagebrowser\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i";
			if (preg_match_all($search, $content, $matches, PREG_SET_ORDER)) {

				foreach ($matches as $match) {
					$replace = "[imagebrowser id=\"{$match[1]}\"]";
					$content = str_replace ($match[0], $replace, $content);
				}
			}
		}

		if ( stripos( $content, '[slideshow' ) !== false) {
			$search = "@(?:<p>)*\s*\[slideshow\s*=\s*(\w+|^\+)(|,(\d+)|,)(|,(\d+))\]\s*(?:</p>)*@i";
			if (preg_match_all($search, $content, $matches, PREG_SET_ORDER)) {

				foreach ($matches as $match) {
					// remove the comma
					$match[3] = ltrim($match[3],',');
					$match[5] = ltrim($match[5],',');
					$replace = "[slideshow id=\"{$match[1]}\" w=\"{$match[3]}\" h=\"{$match[5]}\"]";
					$content = str_replace ($match[0], $replace, $content);
				}
			}
		}

		if ( stripos( $content, '[tags' ) !== false) {
			$search = "@(?:<p>)*\s*\[tags\s*=\s*(.*?)\s*\]\s*(?:</p>)*@i";
			if (preg_match_all($search, $content, $matches, PREG_SET_ORDER)) {

				foreach ($matches as $match) {
					//$replace = "[nggtags gallery=\"{$match[1]}\"]";
					$replace = "[nggtags gallery=\"{$match[1]}\" template=\"{$match[2]}\"]";
					$content = str_replace ($match[0], $replace, $content);
				}
			}
		}

		if ( stripos( $content, '[albumtags' ) !== false) {
			$search = "@(?:<p>)*\s*\[albumtags\s*=\s*(.*?)\s*\]\s*(?:</p>)*@i";
			if (preg_match_all($search, $content, $matches, PREG_SET_ORDER)) {

				foreach ($matches as $match) {
					$replace = "[nggtags album=\"{$match[1]}\"]";
					$content = str_replace ($match[0], $replace, $content);
				}
			}
		}

		// attach related images based on category or tags
		if ($this->options->get(Options::USE_RELATED_IMAGES)) {
			$content .= nggShowRelatedImages();
		}

		return $content;
	}
}