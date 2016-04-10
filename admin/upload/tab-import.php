<?php

namespace NextCellent\Admin\Upload;

use NextCellent\Models\Gallery;
use NextCellent\Options\Options;

/**
 * @author  Niko Strijbol
 * @version 7/04/2016
 */
class Tab_Import extends Upload_Tab {

	/**
	 * Render the content that should be displayed in the tab.
	 */
	public function render() {

		?>
		<h3><?php _e('Import an image folder', 'nggallery') ;?></h3>
		<script type="text/javascript">

			//jQuery Tabs script
			jQuery(document).ready(function(){

				jQuery("#import-folder").click(function() {
					return confirm(
						'<?php echo esc_js(__("This will change folder and file names (e.g. remove spaces, special characters, ...)","nggallery") )?>' +
						'\n\n' +
						'<?php echo esc_js( __("You will need to update your URLs if you link directly to the images.","nggallery") )?>' +
						'\n\n' +
						'<?php echo esc_js( __("Press OK to proceed, and Cancel to stop.","nggallery") )?>'
					);
				});
			});

			// File Tree implementation
			jQuery(function() {
				jQuery("span.browsefiles").show().click(function(){
					var browser = jQuery("#file-browser");
					browser.fileTree({
						script: "admin-ajax.php?action=ngg_file_browser&nonce=<?= wp_create_nonce( 'ngg-ajax' ) ?>",
						root: jQuery("#gallery-folder").val()
					}, function(folder) {
						jQuery("#gallery-folder").val( folder );
					});
					browser.show('slide');
				});
			});

		</script>
		<form method="POST" action="<?= $this->page ?>" accept-charset="utf-8" >
			<?php $this->nonce() ?>
			<table class="form-table">
				<tr>
					<th><label for="gallery-folder"><?php _e('Import from server:', 'nggallery') ?></label></th>
					<td>
						<input type="text" name="gallery_folder" id="gallery-folder" class="regular-text code" value="<?= $this->options->get(Options::GALLERY_PATH) ?>">
						<span class="browsefiles button" style="display:none"><?php _e('Browse...', 'nggallery'); ?></span>
						<br>
						<div id="file-browser"></div>
						<p class="description"><?php _e('Note: you can change the default path in the gallery settings', 'nggallery') ;?></p>
					</td>
				</tr>
			</table>
			<?php submit_button( __('Import folder', 'nggallery') ) ?>
		</form>
		<?php
	}

	/**
	 * Handle the processing.
	 */
	public function processor() {
		
		if(!$this->options->get_mu_option(Options::MU_ALLOW_IMPORT_FOLDER)) {
			\NextCellent\show_warning( __( 'You are not allowed to import folders files.', 'nggallery' ) );
			return;
		}

		if ( isset($_POST['gallery_folder']) && $this->options->get(Options::GALLERY_PATH) != $_POST['gallery_folder'] ) {
			\nggAdmin::import_gallery( $_POST['gallery_folder'] );
		}
	}

	public function register_scripts() {
		wp_enqueue_style( 'jqueryFileTree' );
	}

	public function register_styles() {
		wp_enqueue_script( 'jqueryFileTree' );
	}
}