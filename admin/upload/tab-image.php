<?php

namespace NextCellent\Admin\Upload;

use NextCellent\Database\Not_Found_Exception;
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
		<form id="upload-image-form" method="POST" action="<?= $this->page ?>" accept-charset="utf-8" >
			<?php $this->nonce() ?>
			<input type="hidden" name="plupload_result" value="0">
			<table class="form-table">
				<tr>
					<td class="gallery-selector">
						<?php _e('in to', 'nggallery') ;?>
						<select name="gallery_selector" id="gallery-selector" title="<?php _e('Choose gallery', 'nggallery') ?>">
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
						<div id='upload-queue'></div>
					</td>
				</tr>
			</table>
			<div class="submit">
				<?php submit_button(__('Upload images', 'nggallery'), 'primary', 'upload_images', false, array('id' => 'upload-images')) ?>
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
		if ( isset( $_POST['plupload_result'] ) ) {
			$result = (int) $_POST['plupload_result'];

			//Plupload has not run yet.
			if ( $result === 0 ) {
				\NextCellent\show_warning( __( 'There was a JavaScript error. Nothing has happened.', 'nggallery' ) );
			}
			//Something went wrong while uploading to the server.
			elseif ( $result < 0 ) {
				\NextCellent\show_error( __( 'Something went wrong while uploading the files to the server.', 'nggallery' ) );
			} else {
				assert( $result > 0, "How is this possible?" );
				$id = (int) $_POST['gallery_selector'];
				try {
					$gallery = Gallery::find( $id );
					//TODO: is this a security risk? It now accepts all files. We should only add files we've just uploaded,
					//not everything.
					\nggAdmin::import_gallery( $gallery->path );
				} catch ( Not_Found_Exception $e ) {
					\NextCellent\show_error( sprintf( __( 'The gallery you have selected (%s) does not exist.', 'nggallery' ), $id ) );
				}
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
			// plupload script
			jQuery(document).ready(function ($) {

				"use strict";

				var $gallerySelector = jQuery('#gallery-selector');

				window.uploader = new plupload.Uploader({
					browse_button: 'plupload-browse-button',
					container: 'plupload-upload-ui',
					drop_element: 'drag-drop-area',
					file_data_name: 'Filedata',
					url: '<?= esc_js( $upload_url ); ?>',
					filters: {
						mime_types: [
							{
								title: '<?= esc_js( __( 'Image Files', 'nggallery' ) ); ?>',
								extensions: '<?php echo esc_js( str_replace( array( '*.', ';' ), array( '', ',' ),
									$file_types ) ) ?>'
							}
						],
						max_file_size: '<?= round( (int) wp_max_upload_size() / 1024 ) ?>kb'
					},
					multipart: true,
					urlstream_upload: true,
					multipart_params: <?= $post_params_str ?>,
					<?php if ($this->options->get( Options::IMG_AUTO_RESIZE )): ?>
					resize: {
						width: <?= esc_js( $this->options->get( Options::IMG_MAX_WIDTH ) ) ?>,
						height: <?= esc_js( $this->options->get( Options::IMG_MAX_HEIGHT ) ) ?>,
						quality: <?= esc_js( $this->options->get( Options::IMG_QUALITY ) ) ?>
					},
					<?php endif; ?>
					debug: true,
					preinit: {
						Init: function (up, info) {
							debug('[Init]', 'Info :', info, 'Features :', up.features);

							//Add the upload button.
							jQuery("#upload-images").click(function (e) {
								if ($gallerySelector.val() == 0) {
									e.preventDefault();
									alert(pluploadL10n.no_gallery);
								} else {
									up.start();
								}
							});
						}
					},
					i18n: {
						'remove': '<?php _e( 'remove', 'nggallery' );?>',
						'browse': '<?php _e( 'Browse...', 'nggallery' );?>',
						'upload': '<?php _e( 'Upload images', 'nggallery' );?>'
					}
				});

				uploader.bind('FilesAdded', function (up, files) {
					$.each(files, function (i, file) {
						debug('[FilesAdded]', file);

						var fileSize = " (" + plupload.formatSize(file.size) + ") ";

						jQuery("#upload-queue")
							.append("<div id='" + file.id + "' class='nggUploadItem'> [<a href=''>" + up.settings.i18n.remove + "</a>] " + file.name + fileSize + "</div>")
							.children("div:last").slideDown("slow");

						jQuery('#' + file.id + ' a').click(function (e) {
							jQuery('#' + file.id).remove();
							uploader.removeFile(file);
							e.preventDefault();
						});
					});

					up.refresh();
				});

				uploader.bind('BeforeUpload', function (up, file) {

					debug('[uploadStart]');
					//Start the progress bar
					nggProgressBar.init({
						header: "<?php _e( 'Upload images', 'nggallery' ) ?>",
						maxStep: 100
					});
					debug('[gallery selected]');
					//Get the selected gallery.
					up.settings.multipart_params.galleryselect = $gallerySelector.val();
				});

				uploader.bind('UploadProgress', function (up, file) {
					var percent = Math.ceil((file.loaded / file.size) * 100);

					debug('[uploadProgress]', file.name + ' : ' + percent + "%");

					nggProgressBar.increase(percent);

					jQuery("#progressbar").find("span").text(percent + "% - " + file.name);
				});

				uploader.bind('Error', function (up, err) {
					debug('[uploadError]', err.code, err.message);
					var errorName = err.file.name + ': ';
					var message = '';
					switch (errorCode) {
						case plupload.FAILED:
							message = pluploadL10n.upload_failed;
							break;
						case plupload.FILE_EXTENSION_ERROR:
							message = pluploadL10n.invalid_filetype;
							break;
						case plupload.FILE_SIZE_ERROR:
							message = pluploadL10n.file_exceeds_size_limit;
							break;
						case plupload.IMAGE_FORMAT_ERROR:
							message = pluploadL10n.not_an_image;
							break;
						case plupload.IMAGE_MEMORY_ERROR:
							message = pluploadL10n.image_memory_exceeded;
							break;
						case plupload.IMAGE_DIMENSIONS_ERROR:
							message = pluploadL10n.image_dimensions_exceeded;
							break;
						case plupload.GENERIC_ERROR:
							message = pluploadL10n.upload_failed;
							break;
						case plupload.IO_ERROR:
							message = pluploadL10n.io_error;
							break;
						case plupload.HTTP_ERROR:
							message = pluploadL10n.http_error;
							break;
						case plupload.SECURITY_ERROR:
							message = pluploadL10n.security_error;
							break;
						case plupload.UPLOAD_ERROR.UPLOAD_STOPPED:
						case plupload.UPLOAD_ERROR.FILE_CANCELLED:
							break;
						default:
							$('#plupload-upload-ui').append('Something went wrong!');
					}
					nggProgressBar.addNote("<strong>ERROR " + error_name + " </strong>: " + message);
					jQuery('#plupload-upload-ui').prepend('<div id="file-' + err.file.id + '" class="error"><p style="margin: auto;">' + errorName + message + '</p></div>');
					jQuery("#" + err.file.id).hide("slow").remove();

					up.refresh();
				});

				uploader.bind('FileUploaded', function (up, file, response) {
					debug('[uploadSuccess]', response);

					if (response.response != 0) {
						nggProgressBar.addNote("<strong>ERROR</strong>: " + file.name + ": " + response.response);
					}

					jQuery("#" + file.id).hide("slow").remove();
				});

				uploader.bind('UploadComplete', function (up, file) {
					debug('[uploadComplete]');

					// Upload the next file until queue is empty
					if (up.total.queued == 0) {

						var form = jQuery('#upload-image-form');

						form.children('input[name="plupload_result"]').val(1);
						nggProgressBar.finished();
						form.submit();
					}
				});

				// on load change the upload to plupload
				uploader.init();
			});
		</script>
		<?php
	}

	public function register_scripts() {
		wp_enqueue_script( 'ngg-plupload-handler' );
		wp_enqueue_script( 'ngg-ajax' );
		wp_enqueue_script( 'ngg-progressbar' );
	}

	public function register_styles() {
		wp_enqueue_style( 'ngg-jqueryui' );
	}
}