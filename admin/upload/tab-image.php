<?php

namespace NextCellent\Admin\Upload;

use NextCellent\Database\Not_Found_Exception;
use NextCellent\Models\Gallery;
use NextCellent\Options\Options;
use NextCellent\Upload_Handler;

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

        $upload_url = site_url(\NCG::ENDPOINT . '/' . Upload_Handler::ENDPOINT);
		
		?>
		<!-- upload images -->
		<h3><?php _e('Upload images', 'nggallery') ;?></h3>
		<form id="qq-form" method="POST" action="<?= $upload_url ?>" accept-charset="utf-8">
			<?php $this->nonce() ?>
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
						<?php
							if( is_multisite() && $this->options->get_mu_option(Options::MU_QUOTA_CHECK)) {
								display_space_usage();
							}
						?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
                        <!-- The element where Fine Uploader will exist. -->
                        <div id="fine-uploader"></div>
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

		<form id="complete-form" method="POST" action="<?= $this->page ?>" accept-charset="utf-8">
			<?php $this->nonce() ?>
			<input type="hidden" name="complete" id="complete" value="false">
			<input type="hidden" name="gallery" id="gallery-complete" value="0">
		</form>

        <script type="text/template" id="qq-template">
            <div class="qq-uploader-selector">
                <div id="plupload-upload-ui">
                    <div id="drag-drop-area">
                        <div class="drag-drop-inside">
                            <p class="ngg-dragdrop-info drag-drop-info" >
                                <?php _e('Drop your files in this window', 'nggallery'); ?>
                            </p>
                            <p><?php _e('Or', 'nggallery'); ?></p>
                            <div class="drag-drop-buttons">
                                <div class="qq-upload-button-selector button">
                                    <?php _e('Select Files', 'nggallery'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="qq-total-progress-bar-container-selector qq-total-progress-bar-container">
                    <div role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" class="qq-total-progress-bar-selector qq-progress-bar qq-total-progress-bar"></div>
                </div>
                <div class="qq-upload-drop-area-selector qq-upload-drop-area" qq-hide-dropzone>
                    <span class="qq-upload-drop-area-text-selector"></span>
                </div>
                <span class="qq-drop-processing-selector qq-drop-processing">
                    <span>Processing dropped files...</span>
                    <span class="qq-drop-processing-spinner-selector qq-drop-processing-spinner"></span>
                </span>
                <ul class="qq-upload-list-selector qq-upload-list" aria-live="polite" aria-relevant="additions removals">
                    <li>
                        <div class="qq-progress-bar-container-selector">
                            <div role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" class="qq-progress-bar-selector qq-progress-bar"></div>
                        </div>
                        <span class="qq-upload-spinner-selector qq-upload-spinner"></span>
                        <span class="qq-upload-file-selector qq-upload-file" style="width: initial"></span>
                        <span class="qq-edit-filename-icon-selector qq-edit-filename-icon" aria-label="Edit filename"></span>
                        <input class="qq-edit-filename-selector qq-edit-filename" tabindex="0" type="text">
                        <span class="qq-upload-size-selector qq-upload-size"></span>
                        <button type="button" class="qq-btn qq-upload-cancel-selector qq-upload-cancel">Cancel</button>
                        <button type="button" class="qq-btn qq-upload-retry-selector">Retry</button>
                        <button type="button" class="qq-btn qq-upload-delete-selector qq-upload-delete">Delete</button>
                        <span role="status" class="qq-upload-status-text-selector qq-upload-status-text"></span>
                    </li>
                </ul>
            </div>

        </script>

        <?php
	}

	/**
	 * Handle the processing. This part is called when all images have been uploaded and are ready for processing.
	 */
	public function processor() {
		if ( isset($_POST['complete']) && $_POST['complete'] == "true") {

			$gallery_id = (int) $_POST['gallery'];

			try {
				$gallery = Gallery::find( $gallery_id );
				//TODO: is this a security risk? It now accepts all files. We should only add files we've just uploaded,
				//not everything.
				\nggAdmin::import_gallery( $gallery->path );
			} catch ( Not_Found_Exception $e ) {
				\NextCellent\show_error( sprintf( __( 'The gallery you have selected (%s) does not exist.', 'nggallery' ), $gallery_id ) );
			}
		}
	}

	public function print_scripts()
	{
		// Url to upload to
		$upload_url = site_url(\NCG::ENDPOINT . '/' . Upload_Handler::ENDPOINT);

		// Allowed file types
		$file_types = apply_filters('ngg_allowed_file_types', array('jpg', 'png', 'gif') );
		$json_file_types = json_encode($file_types);

		//Get the max size of an image.
		$max_size = max($this->options->get(Options::IMG_MAX_HEIGHT), $this->options->get(Options::IMG_MAX_WIDTH));

		?>
		<script type="text/javascript">

			// plupload script
			jQuery(document).ready(function($) {

				"use strict";

                var $gallerySelector = $('#gallery-selector');
				var $doneForm = $('#complete-form');

                var uploader = new qq.FineUploader({
                    debug: true,
                    element: document.getElementById('fine-uploader'),
                    request: {
                        endpoint: '<?= esc_js($upload_url) ?>'
                    },
                    validation: {
                        allowedExtensions: <?= $json_file_types ?>
                    },
	                <?php if($this->options->get( Options::IMG_AUTO_RESIZE )): ?>
	                //The scaling
	                scaling: {
		                sendOriginal: false,
		                includeExif: true,
		                defaultQuality: <?= $this->options->get(Options::IMG_QUALITY) ?>,
		                sizes: [{
			                name: '<?php _e('scaled', 'nggallery') ?>',
			                maxSize: <?= $max_size ?>
		                }]
	                },
	                <?php endif; ?>
                    callbacks: {
                        onAllComplete: function(succeeded, failed) {
	                        $doneForm.find('#complete').val("true");
	                        $doneForm.find('#gallery-complete').val($gallerySelector.val());
	                        $doneForm.submit();
                        },
                        onValidateBatch: function(files, button) {
                            //Check that the gallery has been selected.
                            if ($gallerySelector.val() == 0) {
                                alert('<?php _e( 'You didn\'t select a gallery!', 'nggallery' ) ?>');
                                return false;
                            }
                        }
                    }
                });
			});
		</script>
		<?php
	}

	public function register_scripts() {
		wp_enqueue_script( 'ngg-ajax' );
		wp_enqueue_script( 'ngg-progressbar' );
        wp_enqueue_script('fine-uploader');
	}

	public function register_styles() {
		wp_enqueue_style( 'ngg-jqueryui' );
        wp_enqueue_style('fine-uploader');
	}
}