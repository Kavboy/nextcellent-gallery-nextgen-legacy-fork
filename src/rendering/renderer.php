<?php

namespace NextCellent\Rendering;

use NextCellent\Exception;

/**
 * @author  Niko Strijbol
 * @version 16/06/2016
 */
class Renderer {

	private $template;

	public function __construct($template) {
		$this->template = $template;
	}

	private function get_template() {
		
		// hook into the render feature to allow other plugins to include templates
		$custom_template = apply_filters( 'ngg_render_template', false, $this->template );
		
		if($custom_template != false && file_exists( $custom_template )) {
			return $custom_template;
		}

		//Search in the theme directory. This is a legacy feature!
		if(file_exists( get_stylesheet_directory() . "/nggallery/$this->template.php" )) {
			return get_template_directory() . "/nggallery/$this->template.php";
		}
		
		//TODO: remove dependency on WP_CONTENT_DIR
		if (file_exists(WP_CONTENT_DIR . '/' . \NCG::NCG_FOLDER . "/$this->template.php")) {
			return WP_CONTENT_DIR . '/' . \NCG::NCG_FOLDER . "/$this->template.php";
		}
		
		//Check the default folder
		if (file_exists(NCG_PATH . "view/$this->template.php")) {
			return NCG_PATH . "view/$this->template.php";
		}

		//We did not find a template.
		return null;
	}

	/**
	 * Render a given template.
	 *
	 * @param array $args The data to pass to the template.
	 */
	public function render($args) {
		
		$location = $this->get_template();
		
		if($location == null) {
			self::render_error( __('Template not found.', 'nggallery') );
			return;
		}
		
		extract( $args );
		include $location;
	}

	/**
	 * Return the rendered template as a string.
	 * 
	 * @param array $args The data to pass to the template.
	 *
	 * @return string The rendered HTML.
	 */
	public function get_rendered($args) {
		ob_start();
		$this->render( $args );
		return ob_get_clean();
	}

	/**
	 * Render an error with Exception.
	 * 
	 * @param Exception $exception
	 *
	 * @return string
	 */
	public static function render_exception(Exception $exception) {
		return self::render_error( $exception->getMessage() );
	}

	/**
	 * Render an error with message.
	 * 
	 * @param string $message
	 *
	 * @return string
	 */
	protected static function render_error($message) {
		$error = sprintf( __( 'An error occurred: %s', 'nggallery' ), $message );
		return apply_filters('ncg_render_error', $error);
	}
}