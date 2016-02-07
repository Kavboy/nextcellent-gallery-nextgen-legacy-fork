<?php

/**
 * This class manages NextCellent's options. When used inside NextCellent, the instance should be taken from
 * the registry:
 *  global $ncg_registry;
 *  $options = $ncg_registry->get('options');
 *
 * If used outside NextCellent itself, please also try to use the registry. If not possible, take care to
 * save the options at the right time, to prevent overwrite.
 *
 * This class implements the ArrayAccess interface. In this implementation, you are able to
 * access the normal options with the array notation (but NOT the multisite options).
 *
 * @api
 * @since 1.9.31
 */
class NCG_Options implements ArrayAccess {

	/**
	 * The name of the option NextCellent saves the array to.
	 */
	const FIELD = 'ngg_options';

	/**
	 * @var array $options The options.
	 */
	private $options;

	/**
	 * @var array $mu_options The multisite options.
	 */
	private $mu_options;

	/**
	 * @var array $default_options The default values for the options.
	 */
	private $default_options;

	/**
	 * @var array $default_mu_options The default values for the multisite options.
	 */
	private $default_mu_options;

	/**
	 * The constructor is private to prevent new instances.
	 *
	 * @internal See the class description. You should almost never make an instance of this class.
	 */
	public function __construct() {
		$defaults = array(
			//General stuff
			'gallerypath'       => 'wp-content/gallery/',   //Set the default path to the gallery.
			'deleteImg'         => true,                    //Delete images from disk?
			'swfUpload'         => true,                    //Activate the batch upload?
			'usePermalinks'     => false,                   //Use permalinks for parameters?
			'permalinkSlug'     => 'nggallery',             //The default slug for permalinks.
			'graphicLibrary'    => 'gd',                    //The default graphic library.
			'imageMagickDir'    => '/usr/local/bin/',       //The default path to ImageMagick.
			'useMediaRSS'       => false,                   //Activate the global Media RSS file?
			'usePicLens'        => false,                   //Activate the PicLens Link for galleries?
			'silentUpdate'      => false,                   //Should the database be updated silently?

			//Tags & categories
			'activateTags'      => false,                   //Append related images
			'appendType'        => 'tags',                  //look for category or tags
			'maxImages'         => 7,                       //number of images toshow

			// Thumbnail Settings
			'thumbwidth'        => 100,                     //Thumb Width
			'thumbheight'       => 75,                      //Thumb height
			'thumbfix'          => true,                    //Fix the dimension
			'thumbquality'      => 100,                     //Thumb Quality

			// Image Settings
			'imgWidth'          => 800,                     //Image Width
			'imgHeight'         => 600,                     //Image height
			'imgQuality'        => 85,                      //Image Quality
			'imgBackup'         => true,                    //Create a backup
			'imgAutoResize'     => false,                   //Resize after upload

			// Gallery Settings
			'galImages'         => 20,                      //Number of images per page
			'galPagedGalleries' => 0,                       //Number of galleries per page (in a album)
			'galColumns'        => 0,                       //Number of columns for the gallery
			'galShowSlide'      => true,                    //Show slideshow
			'galTextSlide'      => __( '[Show as slideshow]', 'nggallery' ), //Text for slideshow
			'galTextGallery'    => __( '[Show picture list]', 'nggallery' ), //Text for gallery
			'galShowOrder'      => 'gallery',               //Show order
			'galSort'           => 'sortorder',             //Sort order
			'galSortDir'        => 'ASC',                   //Sort direction
			'galNoPages'        => true,                    //use no subpages for gallery
			'galImgBrowser'     => false,                   //Show ImageBrowser, instead effect
			'galHiddenImg'      => false,                   //For paged galleries we can hide image
			'galAjaxNav'        => false,                   //AJAX Navigation for Shutter effect

			// Thumbnail Effect
			'thumbEffect'       => 'shutter',               //select effect
			'thumbCode'         => 'class=>shutterset_%GALLERY_NAME%"',

			// Watermark settings
			'wmPos'             => 'botRight',              //Postion
			'wmXpos'            => 5,                       //X Pos
			'wmYpos'            => 5,                       //Y Pos
			'wmType'            => 'text',                  //Type : 'image' / 'text'
			'wmPath'            => '',                      //Path to image
			'wmFont'            => 'arial.ttf',             //Font type
			'wmSize'            => 10,                      //Font Size
			'wmText'            => get_option( 'blogname' ),//Text
			'wmColor'           => '000000',                //Font Color
			'wmOpaque'          => '100',                   //Font Opaque

			// Slideshow settings
			'slideFx'           => 'fadeIn',                //The effect
			'irWidth'           => 320,                     //Width (in px)
			'irHeight'          => 240,                     //Height (in px)
			'irAutoDim'         => true,                    //Automatically set the dimensions.
			'irRotatetime'      => 3,                       //Duration (in seconds)
			'irLoop'            => true,                    //Loop or not
			'irDrag'            => true,                    //Enable drag or not
			'irNavigation'      => false,                   //Show navigation
			'irNavigationDots'  => false,                   //Show navigation dots
			'irAutoplay'        => true,                    //Autoplay
			'irAutoplayHover'   => true,                    //Pause on hover
			'irNumber'          => 20,                      //Number of images when random or latest
			'irClick'           => true,                    //Go to next on click.

			// CSS Style
			'activateCSS'       => true,                            // activate the CSS file
			'CSSfile'           => plugins_url('css/nggallery.css', dirname(__FILE__)),            // set default css filename
		);

		$mu_defaults = array(
			'gallerypath'  => 'wp-content/blogs.dir/%BLOG_ID%/files/',
			'wpmuCSSfile'  => 'nggallery.css',
			'silentUpdate' => false,
		);

		if(is_multisite()) {
			$defaults['gallerypath'] = str_replace( "%BLOG_ID%", get_current_blog_id(), $mu_defaults['gallerypath'] );
			$defaults['CSSfile']     = $mu_defaults['wpmuCSSfile'];
		}

		$this->default_options = $defaults;
		$this->default_mu_options = $mu_defaults;

		$this->options = get_option(self::FIELD);

		//If the options do not exist.
		if(!$this->options) {
			$this->install_options();
			$this->options = get_option(self::FIELD);
		}

		if(is_multisite()) {
			$this->mu_options = get_site_option(self::FIELD);

			//If the options do not exist.
			if(!$this->mu_options) {
				$this->install_mu_options();
				$this->mu_options = get_site_option(self::FIELD);
			}
		} else {
			$this->mu_options = null;
		}
	}

	/**
	 * Save the default options, which is an empty array.
	 */
	private function install_options() {
		update_option( self::FIELD, array() );
	}

	/**
	 * Save the default site options, which is an empty array.
	 */
	private function install_mu_options() {
		update_site_option( self::FIELD, array() );
	}

	/**
	 * Get a NextCellent option (general options only).
	 *
	 * @param string $option The name of the option.
	 *
	 * @return null|mixed Returns the value of the option, or null if the option does not exist.
	 */
	public function get_option( $option ) {
		return $this->option( $this->options, $this->default_options, $option );
	}

	/**
	 * Get a NextCellent option (general options only).
	 *
	 * @see get_option()
	 *
	 * @param string $option The name of the option.
	 *
	 * @return null|mixed Returns the value of the option, or null if the option does not exist.
	 */
	public function get( $option ) {
		return $this->get_option( $option );
	}

	/**
	 * Update and save an option. Note that every option you save here should have a default value in the defaults.
	 *
	 * @param string $option The name of the option.
	 * @param mixed $value   The value of the option. If not scalar, it should be serialized.
	 *
	 * @see update_option()
	 *
	 * @return bool True if the option was updated, otherwise false.
	 */
	public function update_option( $option, $value ) {
		$this->set_option($option, $value);

		return $this->save_options();
	}

	/**
	 * Update but not save an option. Note that every option you save here should have a default value in the defaults.
	 *
	 * @param string $option The name of the option.
	 * @param mixed $value   The value of the option. If not scalar, it should be serialized.
	 */
	public function set_option( $option, $value ) {
		$this->options[ $option ] = $value;
	}

	/**
	 * Update and save an array of options. Every options should have a default value in the defaults.
	 *
	 * @param array $options An associative array of the options to save.
	 *
	 * @see update_option()
	 *
	 * @return bool True if the option was updated, otherwise false.
	 */
	public function update_options( $options ) {
		$this->set_options($options);

		return $this->save_options();
	}

	/**
	 * Update but not save an array of options. Every options should have a default value in the defaults.
	 *
	 * @param array $options An associative array of the options to save.
	 **/
	public function set_options( $options ) {
		foreach ( $options as $option => $value ) {
			$this->options[ $option ] = $value;
		}
	}

	/**
	 * Save the normal options.
	 *
	 * @see update_option()
	 *
	 * @return bool True if the option was updated, otherwise false.
	 */
	public function save_options() {
		return update_option( self::FIELD, $this->options );
	}

	/**
	 * Delete an option. It will be unset, and the options will be saved. If the option does not exist, nothing will
	 * happen.
	 *
	 * @param string $option The option to delete.
	 */
	public function delete_option( $option ) {
		if ( array_key_exists( $option, $this->options ) ) {
			unset( $this->options[ $option ] );
			$this->save_options();
		}
	}

	/**
	 * Get a multisite option.
	 *
	 * @param string $option The option to look for.
	 *
	 * @return mixed|null Returns the value of the option, or null if the option does not exist.
	 */
	public function get_mu_option( $option ) {
		return $this->option( $this->mu_options, $this->default_mu_options, $option );
	}

	/**
	 * Update and save a multisite option. Note that every option you save here should have
	 * a default value in the defaults.
	 *
	 * @param string $option The name of the option.
	 * @param mixed $value   The value of the option. If not scalar, it should be serialized.
	 *
	 * @see update_site_option()
	 *
	 * @return bool True if the option was updated, otherwise false.
	 */
	public function update_mu_option( $option, $value ) {
		$this->mu_options[ $option ] = $value;

		return $this->save_mu_options();
	}

	/**
	 * Update but not save a multisite option. Note that every option you save here should have
	 * a default value in the defaults.
	 *
	 * @param string $option The name of the option.
	 * @param mixed $value   The value of the option. If not scalar, it should be serialized.
	 */
	public function set_mu_option( $option, $value ) {
		$this->mu_options[ $option ] = $value;
	}

	/**
	 * Update and save an array of multisite options. Every options should have a default value in the defaults.
	 *
	 * @param array $options An associative array of the options to save.
	 *
	 * @return bool True if the option was updated, otherwise false.
	 */
	public function update_mu_options( $options ) {
		$this->set_mu_options($options);

		return $this->save_mu_options();
	}

	/**
	 * Update but not save an array of multisite options. Every options should have a default value in the defaults.
	 *
	 * @param array $options An associative array of the options to save.
	 */
	public function set_mu_options( $options ) {
		foreach ( $options as $option => $value ) {
			$this->mu_options[ $option ] = $value;
		}
	}

	/**
	 * Delete a multisite option. It will be unset, and the options will be saved. If the option does not exist,
	 * nothing will happen.
	 *
	 * @param string $option The option to delete.
	 */
	public function delete_mu_option( $option ) {
		if ( array_key_exists( $option, $this->mu_options ) ) {
			unset( $this->mu_options[ $option ] );
			$this->save_mu_options();
		}
	}

	/**
	 * Save the multisite options.
	 *
	 * @see update_site_option()
	 *
	 * @return bool True if the option was updated, otherwise false.
	 */
	public function save_mu_options() {
		update_site_option( self::FIELD, $this->mu_options );
	}

	/**
	 * Output the HTML checked attribute if the given option is true.
	 *
	 * @param string $option The name of the option.
	 * @param bool $compare  The value to compare to.
	 * @param bool $echo     Whether to echo or return the string.
	 *
	 * @return null|string The checked attribute if true, else an empty string. If echo is set, nothing is returned.
	 */
	public function checked( $option, $compare = true, $echo = true ) {
		return checked( $this->get( $option ), $compare, $echo );
	}

	/**
	 * Output the HTML selected attribute if the given option is true.
	 *
	 * @param string $option The name of the option.
	 * @param bool $compare  The value to compare to.
	 * @param bool $echo     Whether to echo or return the string.
	 *
	 * @return null|string The selected attribute if true, else an empty string. If echo is set, nothing is returned.
	 */
	public function selected( $option, $compare = true, $echo = true ) {
		return selected( $this->get( $option ), $compare, $echo );
	}

	/**
	 * Output the HTML disabled attribute if the given option is true.
	 *
	 * @param string $option The name of the option.
	 * @param bool $compare  The value to compare to.
	 * @param bool $echo     Whether to echo or return the string.
	 *
	 * @return null|string The disabled attribute if true, else an empty string. If echo is set, nothing is returned.
	 */
	public function disabled( $option, $compare = true, $echo = true ) {
		return disabled( $this->get( $option ), $compare, $echo );
	}

	/**
	 * Get an option.
	 *
	 * @param array $options  The options from where to get the options.
	 * @param array $defaults The defaults for the given options.
	 * @param string $option  The option to look for.
	 *
	 * @return null|mixed Null if the option does not exists, else the value of the option.
	 */
	private function option( $options, $defaults, $option ) {
		//If the option is not present in the saved options, it's maybe in the defaults.
		if ( !array_key_exists( $option, $options ) ) {
			if ( !array_key_exists( $option,  $defaults ) ) {
				return null;
			} else {
				return $defaults[ $option ];
			}
		} else {
			return $options[ $option ];
		}
	}

	/**
	 * Whether a offset exists
	 * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param string $offset An offset to check for.
	 *
	 * @return boolean True if it exists, otherwise false.
	 */
	public function offsetExists( $offset ) {
		return array_key_exists($offset, $this->options);
	}

	/**
	 * Offset to retrieve
	 * @link  http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param string $offset The offset to retrieve.
	 *
	 * @return mixed Can return all value types.
	 */
	public function offsetGet( $offset ) {
		return $this->get($offset);
	}

	/**
	 * Offset to set
	 * @link  http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * When an offset is set, the options are saved. This is not efficient for setting a large number of
	 *        options.
	 *
	 * @see update_options() for the alternative that does enable a lot of options.
	 *
	 * @param string $offset The offset to assign the value to.
	 * @param mixed $value  The value to set.
	 *
	 * @return void
	 */
	public function offsetSet( $offset, $value ) {
		$this->update_option($offset, $value);
	}

	/**
	 * Offset to unset
	 * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * This will save the options.
	 *
	 * @param mixed $offset The offset to unset.
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetUnset( $offset ) {
		$this->delete_option($offset);
	}
}