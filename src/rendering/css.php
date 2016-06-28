<?php

/**
 * CSS-related stuff.
 *
 * @author  Niko Strijbol
 */
namespace NextCellent\Rendering\Css;

use NCG;
use NextCellent\Options\Options;

//Folder values. The folder one is defined as default in the options.
const FOLDER_BUILTIN = 'builtin';
const FOLDER_STYLES  = 'ngg_styles';

/**
 * Get the CSS file from a theme.
 *
 * @return string|null Get the url to the CSS file to include, or null.
 */
function getThemeCssFile() {
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
function getCssFile() {

	$options = Options::getInstance();

	if ($css_file = getThemeCssFile()) {
		wp_enqueue_style('NextGEN', $css_file , false, '1.0.0', 'screen');
		return $css_file;
	} elseif ($options[Options::STYLE_USE_CSS]) {

		$folder = $options[Options::STYLE_CSS_FOLDER];
		if($folder === FOLDER_BUILTIN) {
			return plugins_url('styles/' . $options[Options::STYLE_CSS_FILE], NCG_FILE_PATH);
		} else {    //It is a user value.
			return content_url(NCG::NCG_FOLDER . '/' . $options[Options::STYLE_CSS_FILE]);
		}

	} else {
		return null;
	}
}

/**
 * Find stylesheets for NextCellent.
 *
 * @param string $folder The absolute path to the folder to search.
 *
 * @return array Index array with the key the (normalized) absolute path, and as value a SplFileInfo object.
 */
function getCssFilesFrom($folder) {

	$dir = new \RecursiveDirectoryIterator($folder);

	$files = new \RecursiveCallbackFilterIterator($dir, function(\SplFileInfo $current, $key, \RecursiveIterator $it) {
		if($it->hasChildren()) {
			return true;
		}

		return $current->isFile() && $current->getExtension() === 'css';
	});

	$array = [];

	foreach ($files as $fileName => $fileObject) {
		$array[wp_normalize_path($fileName)] = $fileObject;
	}

	return $array;
}

/**
 * Read data for a file.
 *
 * @param \SplFileInfo $file
 *
 * @return array The data in associative array form.
 */
function readData(\SplFileInfo $file) {

	$data = get_file_data($file->getPathname(), [
		'name'  => 'CSS Name',
		'desc'  => 'Description',
		'author'    => 'Author',
		'version'   => 'Version'
	]);

	//Filter empty stuff
	$data = array_filter($data);

	return wp_parse_args($data, [
		'name'      => __('Unknown', 'nggallery'),
		'desc'      => __('Description was not present.', 'nggallery'),
		'author'    => __('Unknown', 'nggallery'),
		'version'   => __('No version', 'nggallery'),
	]);
}