<?php

namespace NextCellent\Admin\Upload;

use NextCellent\Models\Gallery;
use NextCellent\Options\Options;

/**
 * @author  Niko Strijbol
 * @version 7/04/2016
 */
class Tab_Image extends Upload_Tab {

	/**
	 * Render the content that should be displayed in the tab.
	 *
	 * @return null
	 */
	public function render() {
		
		$galleries = Gallery::all();
		
		?>
		<!-- upload images -->
		<h3><?php _e('Upload images', 'nggallery') ;?></h3>
		<form id="uploadimage_form" method="POST" enctype="multipart/form-data" action="<?= $this->page ?>" accept-charset="utf-8" >
			<?php $this->nonce() ?>
			<table class="form-table">
				<tr>
					<td class="gallery-selector">
						<?php _e('in to', 'nggallery') ;?>
						<select name="galleryselect" id="galleryselect">
							<option value="0" ><?php _e('Choose gallery', 'nggallery') ?></option>
							<?php $this->print_galleries($galleries); ?>
						</select>
					</td>
					<td>
						<?php echo \nggGallery::check_memory_limit(); ?>
						<br>
						<?php
							if( is_multisite() && $this->options->get_mu_option(Options::MU_QUOTA_CHECK)) {
								display_space_usage();
							}
						?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<div id="plupload-upload-ui">
							<div id="drag-drop-area">
								<div class="drag-drop-inside">
									<p class="ngg-dragdrop-info drag-drop-info" >
										<?php _e('Drop your files in this window', 'nggallery'); ?>
									</p>
									<p><?php _e('Or', 'nggallery'); ?></p>
									<p class="drag-drop-buttons">
										<input id="plupload-browse-button" type="button" value="<?php esc_attr_e('Select Files', 'nggallery'); ?>" class="button">
									</p>
								</div>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<div id='uploadQueue'></div>
					</td>
				</tr>
			</table>
			<div class="submit">
				<?php submit_button(__('Upload images', 'nggallery'), 'primary', 'upload_images', false, array('id' => 'upload_images')) ?>
				<?php if ($this->options->get(Options::IMG_AUTO_RESIZE)): ?>
					<span class="description">
						<?php printf(
							__( 'Your images will be rescaled to max width %1$dpx or max height %2$dpx.', 'nggallery' ),
							$this->options->get(Options::IMG_MAX_WIDTH),
							$this->options->get(Options::IMG_MAX_HEIGHT)
						) ?>
					</span>
				<?php endif; ?>
			</div>
		</form>
		<?php
	}

	/**
	 * Handle the processing.
	 */
	public function processor() {
		if ( isset( $_POST['upload_images'] ) ) {

			if ( $_FILES['image_files']['error'][0] == 0 ) {
				\nggAdmin::upload_images();
			} else {
				\NextCellent\show_error('Upload failed! ' . \nggAdmin::decode_upload_error( $_FILES['image_files']['error'][0] ), 'nggallery' );
			}
		}
	}

	public function print_scripts() {
		// link for the flash file
		//TODO: add better url
		$upload_url = admin_url('/?nggupload');

		// with this filter you can add custom file types
		$file_types = apply_filters( 'ngg_swf_file_types', '*.jpg;*.jpeg;*.gif;*.png;*.JPG;*.JPEG;*.GIF;*.PNG' );

		// Set the post params, which plupload will post back with the file, and pass them through a filter.
		$post_params = array(
			"auth_cookie" => (is_ssl() ? $_COOKIE[SECURE_AUTH_COOKIE] : $_COOKIE[AUTH_COOKIE]),
			"logged_in_cookie" => $_COOKIE[LOGGED_IN_COOKIE],
			"_wpnonce" => wp_create_nonce('ngg_swfupload'),
			"galleryselect" => "0",
		);

		$post_params_str = json_encode($post_params);


		?>
		<script type="text/javascript">

			"use strict";

			// plupload script
			jQuery(document).ready(function($) {
				window.uploader = new plupload.Uploader({
					browse_button: 'plupload-browse-button',
					container: 'plupload-upload-ui',
					drop_element: 'uploadimage',
					file_data_name: 'Filedata',
					url: '<?php echo esc_js( $upload_url ); ?>',
					flash_swf_url: '<?php echo esc_js( includes_url('js/plupload/plupload.flash.swf') ); ?>',
					silverlight_xap_url: '<?php echo esc_js( includes_url('js/plupload/plupload.silverlight.xap') ); ?>',
					filters: {
						mime_types : [
							{
								title: '<?= esc_js( __('Image Files', 'nggallery') ); ?>',
								extensions: '<?php echo esc_js( str_replace( array('*.', ';'), array('', ','), $file_types)  ) ?>'
							}
						],
						max_file_size: '<?= round( (int) wp_max_upload_size() / 1024 ) ?>kb'
					},
					multipart: true,
					urlstream_upload: true,
					multipart_params : <?= $post_params_str ?>,
					<?php if ($this->options->get(Options::IMG_AUTO_RESIZE)): ?>
					resize: {
						width: <?= esc_js( $this->options->get(Options::IMG_MAX_WIDTH) ) ?>,
						height: <?= esc_js( $this->options->get(Options::IMG_MAX_HEIGHT) ) ?>,
						quality: <?= esc_js( $this->options->get(Options::IMG_QUALITY) ) ?>
					},
					<?php endif; ?>
					debug: false,
					preinit : {
						Init: function(up, info) {
							debug('[Init]', 'Info :', info,  'Features :', up.features);
							if (navigator.appVersion.indexOf("MSIE 10") > -1) {
								up.features.triggerDialog = true;
							}
							initUploader();
						}
					},
					i18n : {
						'remove' : '<?php _e('remove', 'nggallery') ;?>',
						'browse' : '<?php _e('Browse...', 'nggallery') ;?>',
						'upload' : '<?php _e('Upload images', 'nggallery') ;?>'
					}
				});

				uploader.bind('FilesAdded', function(up, files) {
					$.each(files, function(i, file) {
						fileQueued(file);
					});

					up.refresh();
				});

				uploader.bind('BeforeUpload', function(up, file) {
					uploadStart(file);
				});

				uploader.bind('UploadProgress', function(up, file) {
					uploadProgress(file, file.loaded, file.size);
				});

				uploader.bind('Error', function(up, err) {
					uploadError(err.file, err.code, err.message);

					up.refresh();
				});

				uploader.bind('FileUploaded', function(up, file, response) {
					uploadSuccess(file, response);
				});

				uploader.bind('UploadComplete', function(up, file) {
					uploadComplete(file);
				});

				// on load change the upload to plupload
				uploader.init();

				nggAjaxOptions = {
					header: "<?php _e('Upload images', 'nggallery') ;?>",
					maxStep: 100
				};

			});
		</script>
		<?php
	}

	public function register_scripts() {
		// TODO: Implement register_scripts() method.
	}

	public function register_styles() {
		// TODO: Implement register_styles() method.
	}
}