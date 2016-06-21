<?php

namespace NextCellent\Rendering;

use NCG;
use NextCellent\Options\Options;

/**
 * @author  Niko Strijbol
 * @version 16/06/2016
 */
class Rewrite {

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
		'page/([0-9]+)' => '&page=$1',
		'image/([^/]+)' => '&image=$1',
		'image/([^/]+)/page/([0-9]+)' => '&image=$1&page=$2',
		'slideshow/' => '&mode=slide',
		'images/' => '&mode=gallery',
		'tags/([^/]+)/' => '&tag=$1',
		'tags/([^/]+)/page/([0-9]+)/?' => '&tag=$1&page=$2',
		'([^/]+)/' => '&album=[matches]',
		'([^/]+)/page/([0-9]+)/?' => '&album=$1&page=$2',
		'([^/]+)/([^/]+)/' => '&album=$1&gallery=$2',
		'([^/]+)/([^/]+)/slideshow/' => '&album=$1&gallery=$2&show=slide',
		'([^/]+)/([^/]+)/images/' => '&album=$1&gallery=$2&show=gallery',
		'([^/]+)/([^/]+)/page/([0-9]+)/?' => '&album=$1&gallery=$2&page=$3',
		'([^/]+)/([^/]+)/page/([0-9]+)/slideshow/' => '&album=$1&gallery=$2&page=$3&show=slide',
		'([^/]+)/([^/]+)/page/([0-9]+)/images/' => '&album=$1&gallery=$2&page=$3&show=gallery',
		'([^/]+)/([^/]+)/image/([^/]+)/' => '&album=$1&gallery=$2&pid=$3'
	];
	
	public static function register() {
		//Add the endpoint
		add_action('init', function() {
			add_rewrite_endpoint( NCG::ENDPOINT, EP_PERMALINK | EP_PAGES);
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

		$processed = array_map( function($item) {
			return '/' . str_replace( '/', '\/', $item ) . '/i';
		}, $keys );

		for ($i = 0; $i < count($keys); $i++) {

			if(preg_match($processed[$i], $string) === 1) {

				$replacement = preg_replace( $processed[$i], self::$rules[ $keys[ $i ] ], $string);

				$data = [];
				parse_str( $replacement, $data );

				$new_data = [];
				foreach ( $data as $key => $dat ) {
					$new_data['ncg-' . $key] = $dat;
				}

				$query->query_vars = array_merge( $query->query_vars, $new_data );
				break;
			}
		}
	}

	public static function get_pagination_link($page) {
		
		global $wp_rewrite, $ncg;

		if(is_page()) {
			$url = get_page_link();
		} else {
			$url = get_permalink();
		}

		if($wp_rewrite->using_permalinks() && $ncg->options->get( Options::USE_PERMALINKS )) {
			return $url . "nextcellent/page/$page";
		} else {
			return add_query_arg('ncg-page', $page, $url);
		}
	}
}