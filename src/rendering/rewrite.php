<?php

namespace NextCellent\Rendering;

use NCG;
use NextCellent\Options\Options;

/**
 * @author  Niko Strijbol
 * @version 16/06/2016
 */
class Rewrite {

	//The endpoint for NextCellent
	const ENDPOINT = 'nextcellent';

	private static $query_vars = [
		'ncg-image',
		'ncg-page',
		'ncg-gallery',
		'ncg-album',
		'ncg-tag',
		'ncg-mode',
	];

	//The rewrite rules
	private static $rules = [
		'#page/([0-9]+)/?#i' => '&page=$1',
//		'image/([^/]+)' => '&image=$1',
//		'image/([^/]+)/page/([0-9]+)' => '&image=$1&page=$2',
		'#slideshow/?#i'       => '&mode=slideshow',
		'#gallery/?#i'         => '&mode=gallery',
//		'tags/([^/]+)/' => '&tag=$1',
//		'tags/([^/]+)/page/([0-9]+)/?' => '&tag=$1&page=$2',
//		'([^/]+)/' => '&album=[matches]',
//		'([^/]+)/page/([0-9]+)/?' => '&album=$1&page=$2',
//		'([^/]+)/([^/]+)/' => '&album=$1&gallery=$2',
//		'([^/]+)/([^/]+)/slideshow/' => '&album=$1&gallery=$2&show=slideshow',
//		'([^/]+)/([^/]+)/images/' => '&album=$1&gallery=$2&show=gallery',
//		'([^/]+)/([^/]+)/page/([0-9]+)/?' => '&album=$1&gallery=$2&page=$3',
//		'([^/]+)/([^/]+)/page/([0-9]+)/slideshow/' => '&album=$1&gallery=$2&page=$3&show=slideshow',
//		'([^/]+)/([^/]+)/page/([0-9]+)/images/' => '&album=$1&gallery=$2&page=$3&show=gallery',
//		'([^/]+)/([^/]+)/image/([^/]+)/' => '&album=$1&gallery=$2&pid=$3'
	];
	
	public static function register() {
		//Add the endpoint
		add_action('init', function() {
			add_rewrite_endpoint( self::ENDPOINT, EP_PERMALINK | EP_PAGES);
		});

		//Add the query vars.
		add_filter('query_vars', function($vars) {
			return array_merge( $vars, self::$query_vars );
		});

		//Add the query parser
		add_action( 'parse_query', [self::class, 'parse_queries']);
	}

	/**
	 * Register the rewrites here.
	 *
	 * @param \WP_Query $query
	 */
	public static function parse_queries( $query ) {

		$string = $query->get( 'nextcellent' );

		$keys = array_keys( self::$rules );

		for ($i = 0; $i < count($keys) && $string != ''; $i++) {

			if(preg_match($keys[$i], $string) === 1) {

				$replacement = preg_replace( $keys[$i], self::$rules[ $keys[$i] ], $string);
				$string = preg_replace( $keys[$i], '', $string);

				$data = [];
				parse_str( $replacement, $data );

				$new_data = [];
				foreach ( $data as $key => $dat ) {
					$new_data['ncg-' . $key] = $dat;
				}

				$query->query_vars = array_merge( $query->query_vars, $new_data );
			}
		}
	}

	/**
	 * @return bool True if permalinks are enabled.
	 */
	private static function using_permalink() {
		global $wp_rewrite, $ncg;

		return $wp_rewrite->using_permalinks() && $ncg->options->get( Options::USE_PERMALINKS );
	}

	/**
	 * Get an URL based on the given and existing arguments. The order in which the arguments are
	 * added to the URL is not specified.
	 *
	 * @param string[] $args The arguments.
	 *
	 * @return string The URL.
	 */
	public static function get_link($args = []) {

		$defaults = [];

		foreach ( self::$query_vars as $query_var ) {
			$defaults[$query_var] = get_query_var( $query_var, null );
		}

		$args = wp_parse_args( $args, $defaults );

		$is_permalink = self::using_permalink();

		if(is_page()) {
			$url = get_page_link();
		} else {
			$url = get_permalink();
		}

		//Add our endpoint when using permalinks
		if($is_permalink) {
			$url .= self::ENDPOINT;
		}

		//Add the album if necessary
		if($args['ncg-album'] !== null) {
			if($is_permalink) {
				$url .= '/album/' . $args['ncg-album'];
			} else {
				$url = add_query_arg('ncg-album', $args['ncg-album'], $url);
			}
		}

		//Add gallery if necessary
		if($args['ncg-gallery'] !== null) {
			if($is_permalink) {
				$url .= '/gallery' . $args['ncg-gallery'];
			} else {
				$url = add_query_arg('ncg-gallery', $args['ncg-gallery'], $url);
			}
		}

		//Add the tag if necessary
		if($args['ncg-tag'] !== null) {
			if($is_permalink) {
				$url .= '/tags/' . $args['ncg-tag'];
			} else {
				$url = add_query_arg('ncg-tag', $args['ncg-tag'], $url);
			}
		}

		//Add image if necessary
		if($args['ncg-image'] !== null) {
			if($is_permalink) {
				$url .= '/image/' . $args['ncg-image'];
			} else {
				$url = add_query_arg('ncg-image', $args['ncg-tag'], $url);
			}
		}
		
		//Add display mode if necessary
		if($args['ncg-mode'] !== null && $args['ncg-mode'] !== 'gallery') {
			if($is_permalink) {
				$url .= '/' . $args['ncg-mode'];
			} else {
				$url = add_query_arg('ncg-mode', $args['ncg-mode'], $url);
			}
		}

		//Add page if necessary
		if($args['ncg-page'] !== null) {
			if($is_permalink) {
				$url .= '/page/' . $args['ncg-page'];
			} else {
				$url = add_query_arg('ncg-page', $args['ncg-page'], $url);
			}
		}

		return $url;
	}
}