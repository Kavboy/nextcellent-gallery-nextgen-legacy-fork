<?php

namespace NextCellent;

use NextCellent\Models\Gallery;

/**
 * Handles the upload from the upload page.
 *
 * @see NextCellent\Admin\Upload\Tab_Image
 */
class Upload_Handler {

	//The name of the nonce of the image tab. This is hardcoded, since the process function is not called until
	//after everything is uploaded.
	const NONCE = 'ncg_tab_images';

	/**
	 * Do the actual uploading.
	 */
	public static function handle_upload() {

		self::check_nonce();

		// Get the gallery.
		$gallery_id = (int) $_POST['gallery_selector'];
		$gallery    = Gallery::find( $gallery_id );

		self::check_can_upload( $gallery );

		self::upload_to_gallery( $gallery );
	}

	/**
	 * Check the nonce, so we can only upload from the upload page.
	 */
	private static function check_nonce() {
		//check_admin_referer( 'ncg_tab_images' );
		check_ajax_referer( 'ncg_tab_images' );
	}

	/**
	 * Check if the current user can upload to the selected gallery.
	 *
	 * @param Gallery $gallery
	 */
	private static function check_can_upload( $gallery ) {
		if ( ! $gallery->can_manage() ) {
			wp_die( __( 'You cannot upload to this gallery.', 'nggallery' ), 403 );
		}
	}

	/**
	 * Upload an image to a gallery. This function puts the file in the correct folder. The image is not added to the
	 * gallery itself.
	 *
	 * @param Gallery $gallery
	 */
	private static function upload_to_gallery( $gallery ) {
		$filename = $_POST['qqfilename'];

		// Check if the file is present.
		if ( ! isset( $_FILES['qqfile'] ) || ! isset( $_FILES['qqfile']['name'] ) ) {
			wp_die( null, 422 );
		}

		$temp_file = $_FILES['qqfile']['tmp_name'];

		//Check the file type and use the proper name.
		$filename = self::check_file_type( $temp_file, $filename );

		//If the image is scaled, we remove the "scaled" indicator from the name.
		$filename = str_replace( ' (scaled)', '', $filename );

		//Sanitize the file name
		$filename = \NextCellent\Files\unique_image_name( $gallery, $filename );

		$path = $gallery->path_to_image( $filename );

		//Move the uploaded file to the gallery folder.
		if ( ! move_uploaded_file( $temp_file, $path ) ) {
			wp_die( null, 500 );
		}

		wp_send_json_success();
	}

	/**
	 * Check the file type.
	 *
	 * @param string $file     Path to the file.
	 * @param string $filename Filename.
	 *
	 * @return string The filename with the correct extension.
	 */
	private static function check_file_type( $file, $filename ) {

		//Allowed file types
		$ext = apply_filters( 'ngg_allowed_file_types', array( 'jpg', 'png', 'gif' ) );

		$validate = wp_check_filetype_and_ext( $file, $filename );

		if ( in_array( $validate['ext'], $ext ) ) {
			if ( $validate['proper_filename'] === false ) {
				return $filename;
			} else {
				return $validate['proper_filename'];
			}
		} else {
			wp_die( null, 415 );

			return "";
		}
	}
}