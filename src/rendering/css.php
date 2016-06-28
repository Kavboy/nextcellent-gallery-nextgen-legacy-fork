<?php

namespace NextCellent\Rendering;

use NextCellent\Options\Options;

/**
 * CSS-related stuff.
 *
 * @author  Niko Strijbol
 */
class Css {

	/**
	 * Get the CSS file from a theme.
	 *
	 * @return string|null Get the url to the CSS file to include, or null.
	 */
	static function getThemeCssFile() {
		/**
		 * Filter that allows theme authors to provide a CSS file for NextCellent. This way you can conditionally
		 * include CSS for NextCellent: if NextCellent is not loaded or used, the CSS is not loaded.
		 *
		 * @return string The URL to the stylesheet.
		 */
		$stylesheet = apply_filters( 'ngg_load_stylesheet', false );

		if ( $stylesheet !== false ) {
			return $stylesheet;
		}

		//There is no filter. Maybe the user used the old, deprecated mode?
		if (file_exists(get_stylesheet_directory() . '/nggallery.css')) {
			trigger_error('Using a file named nggallery.css is deprecated and may be removed in the future.', E_USER_DEPRECATED);
			return get_stylesheet_directory_uri() . '/nggallery.css'; //Yes, return uri to the custom style
		}
		//No filter and no custom style. Bye bye
		return false;
	}

	/**
	 * Get the CSS file to use.
	 * 
	 * @return string|null URL to the CSS file.
	 */
	public static function getCssFile() {
		
		$options = Options::getInstance();
		
		if ($css_file = self::getThemeCssFile()) {
			wp_enqueue_style('NextGEN', $css_file , false, '1.0.0', 'screen');
			return $css_file;
		} elseif ($options[Options::STYLE_USE_CSS]) {
			//convert the path to an URL
			$css = str_replace(NCG_PATH, '', $options[Options::STYLE_CSS_FILE]);
			return plugins_url($css, NCG_URL);
		} else {
			return null;
		}
	}
}