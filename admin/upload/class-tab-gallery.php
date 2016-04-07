<?php

namespace NextCellent\Admin\Upload;

use NextCellent\Admin\Roles;
use NextCellent\Options\Options;

/**
 * @author  Niko Strijbol
 * @version 7/04/2016
 */
class Tab_Gallery extends Upload_Tab {

	/**
	 * Render the content that should be displayed in the tab.
	 *
	 * @return null
	 */
	public function render() {
		?>
		<!-- create gallery -->
		<h3><?php _e('Add a new gallery', 'nggallery') ;?></h3>
		<form method="POST" action="<?= $this->page ?>" accept-charset="utf-8" >
			<?php $this->nonce() ?>
			<table class="form-table">
				<tr>
					<th><label for="gallery-name"><?php _e('Name', 'nggallery') ;?>:</label></th>
					<td>
						<input type="text" class="regular-text" name="gallery_name" id="gallery-name" required>
						<br>
						<p>
							<?php printf( __('Create a new, empty gallery in the folder \'<strong>%s</strong>\'', 'nggallery'), $this->options->get('gallerypath') ) ?>
						</p>
						<p class="description">
							<?php printf( __('Allowed characters for file and folder names are "%s".', 'nggallery'), 'a-z, A-Z, 0-9, -, _' ) ?>
						</p>
					</td>
				</tr>
				<tr>
					<th><label for="gallery-desc"><?php _e('Description', 'nggallery') ;?>:</label></th>
					<td>
						<textarea name="gallery_desc" id="gallery-desc" cols="50" rows="3"></textarea>
						<p class="description">
							<?php _e('Add a description. This is optional and can be changed later.', 'nggallery') ;?>
						</p>
					</td>
				</tr>
				<?php do_action('ngg_add_new_gallery_form'); ?>
			</table>
			<?php submit_button(__('Add gallery', 'nggallery')); ?>
		</form>
		<?php
	}

	/**
	 * Handle the processing.
	 */
	public function processor() {

		//Check permissions.
		if(!current_user_can(Roles::MANAGE_GALLERIES)) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		$new_gallery = esc_attr( $_POST['gallery_name'] );
		$description = esc_attr( $_POST['gallery_desc'] );

		if ( !empty( $new_gallery ) ) {
			\nggAdmin::create_gallery( $new_gallery, $this->options->get(Options::GALLERY_PATH), true, $description );
		} else {
			\NextCellent\show_warning( __('The gallery name cannot be empty.', 'nggallery') );
		}
	}

	public function register_scripts() {
		// TODO: Implement register_scripts() method.
	}

	public function register_styles() {
		// TODO: Implement register_styles() method.
	}
}