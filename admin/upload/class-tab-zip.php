<?php

namespace NextCellent\Admin\Upload;

use NextCellent\Models\Gallery;
use NextCellent\Options\Options;

/**
 * @author  Niko Strijbol
 * @version 7/04/2016
 */
class Tab_Zip extends Upload_Tab {

	/**
	 * Render the content that should be displayed in the tab.
	 */
	public function render() {

		$galleries = Gallery::all();

		?>
		<!-- zip-file operation -->
		<h3><?php _e('Upload a ZIP File', 'nggallery') ?></h3>
		<form method="POST" enctype="multipart/form-data" action="<?= $this->page ?>" accept-charset="utf-8" >
			<?php $this->nonce() ?>
			<table class="form-table">
				<tr>
					<th><label for="zip-file"><?php _e('Select ZIP file:', 'nggallery') ?></label></th>
					<td>
						<input type="file" name="zip_file" id="zip-file">
						<p class="description">
							<?php _e('Upload a ZIP file with images', 'nggallery') ?>
						</p>
					</td>
				</tr>
				<tr>
					<th><label for="gallery-selector"><?php _e('in to', 'nggallery') ?></label></th>
					<td>
						<select name="gallery_selector" id="gallery-selector">
							<option value="0" ><?php _e('a new gallery', 'nggallery') ?></option>
							<?php $this->print_galleries($galleries) ?>
						</select>
						<p class="description">
							<?php printf( __('Note: the upload limit on your server is <strong>%d MB</strong>.', 'nggallery'), wp_max_upload_size() / 1024 / 1024); ?>
						</p>
						<br>
						<?php
						if( is_multisite() && $this->options->get_mu_option(Options::MU_QUOTA_CHECK)) {
							display_space_usage();
						}
						?>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Start upload', 'nggallery' ) ) ?>
		</form>
		<?php
	}

	/**
	 * Handle the processing.
	 */
	public function processor() {

		if( !$this->options->get_mu_option(Options::MU_ALLOW_UPLOAD_ZIP) ) {
			\NextCellent\show_warning( __( 'You are not allowed to upload ZIP files.', 'nggallery' ) );
			return;
		}
		
		switch ($_FILES['zip_file']['error']) {
			//Everything is OK!
			case UPLOAD_ERR_OK:
				\nggAdmin::import_zipfile( intval( $_POST['gallery_selector'] ) );
				return; //no error handling
			//Errors!
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$message = __('The file is too big.', 'nggallery');
				break;
			case UPLOAD_ERR_PARTIAL:
				$message = __('The file was only partially uploaded.', 'nggallery');
				break;
			case UPLOAD_ERR_NO_FILE:
				$message = __('No file was selected.', 'nggallery');
				break;
			default:
				$message = sprintf( __('Unknown error %s. See <a href="http://php.net/manual/en/features.file-upload.errors.php">the PHP documentation</a>', 'nggallery'), $_FILES['zip_file']['error']);
		}

		\NextCellent\show_error( sprintf( __( 'ZIP upload failed: %s', 'nggallery' ),  $message ) );
	}

	public function register_scripts() {
		//None
	}

	public function register_styles() {
		//None.
	}
}