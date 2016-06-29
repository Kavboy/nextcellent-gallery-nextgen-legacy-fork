<?php

namespace NextCellent\Admin\Manage\Galleries;

use NextCellent\Models\Image_Collection;

/**
 * Class Abstract_Image_Manager
 *
 * Contains some common methods to use when displaying images.
 */
abstract class Abstract_Image_Manager extends Abstract_Manager {

	public function display() {

		parent::display();

		if (isset($_POST['update_images'])) {
			$this->handle_update_images();
		}
	}

	/**
	 * @todo Make this better.
	 */
	protected function print_scripts() {
		parent::print_scripts();
		?>
		<script type="text/javascript">

			var defaultAction = function(dialog) {
				jQuery(dialog).dialog('close');
			};

			var doAction = defaultAction;

			/**
			 * Load the content with AJAX.
			 */
			jQuery('a.ngg-dialog').click(function() {
				//Get the spinner.
				var $spinner = jQuery("#spinner");
				var $this = jQuery(this);
				var action = $this.data("action");
				var id = $this.data("id");
				var base_url = "<?php echo plugins_url('actions.php?cmd=', __FILE__) ?>";

				if (!$spinner.length) {
					jQuery("body").append('<div id="spinner"></div>');
				}

				$spinner.fadeIn();

				var dialog = jQuery('<div style="display:none" class="ngg-load-dialog"></div>').appendTo('body');
				// load the remote content
				dialog.load(
					base_url + action + "&id=" + id,
					{},
					function() {
						jQuery('#spinner').hide();
						//The doAction function must be defined in the actions.php file.
						showDialog(dialog, ($this.attr('title')) ? $this.attr('title') : '', doAction);
					}
				);
				//prevent the browser to follow the link
				return false;
			});

			/**
			 * Show a message on the image action modal window.
			 *
			 * @param message string The message.
			 */
			function showMessage(message) {
				jQuery('#thumbMsg').html(message).css({'display': 'block'});
				setTimeout(function() {
					jQuery('#thumbMsg').fadeOut('slow');
				}, 1500);

				var d = new Date();
				var $image = jQuery("#imageToEdit");
				var newUrl = $image.attr("src") + "?" + d.getTime();
				$image.attr("src", newUrl);
			}
		</script>

		<?php
	}
	
	protected function checkReferrer() {
		//Check the bulk options.
		check_admin_referer('bulk-' . Image_List_Table::PLURAL);
	}

	private function handle_update_images() {

		//Check the bulk options.
		$this->checkReferrer();

		$description = isset($_POST['description']) ? $_POST['description'] : [];
		$alt_text    = isset($_POST['alttext']) ? $_POST['alttext'] : [];
		$exclude     = isset($_POST['exclude']) ? $_POST['exclude'] : [];
		$tagList     = isset($_POST['tags']) ? $_POST['tags'] : [];
		$pictures    = isset($_POST['pid']) ? $_POST['pid'] : [];
		$date        = isset($_POST['date']) ? $_POST['date'] : 'now()'; //Not sure if NOW() will work or not but in theory it should
		
		//To int.
		$pictures = array_map('intval', $pictures);
		
		if(!is_array($pictures)) {
			\NextCellent\show_info(__('There was nothing to update.', 'nggallery'));
		}

		//Get the images in one go.
		$images = Image_Collection::inList($pictures)->getImages();

		foreach ($images as $image) {
			$image->description = $description[$image->id];
			$image->date        = $date[ $image->id ];

			//Only update this if needed.
			if ($image->alt_text !== $alt_text[$image->id]) {
				$image->alt_text    = $alt_text[ $image->id ];
				//Fast unique slug
				$image->slug = $image->id . '.' . sanitize_title( $image->alt_text );
			}

			$image->exclude = array_key_exists($image->id, $exclude);

			//Update image.
			$image->save();

			//Remove from cache
			wp_cache_delete($image->id, 'ngg_image');

			// hook for other plugins after image is updated
			//TODO: compat?
			do_action( 'ngg_image_updated', $image );
		}
		
		/**
		 * This is for backwards compatibility.
		 * @deprecated
		 */
		do_action( 'ngg_update_gallery', (int) $_GET['gid'], $_POST);

		foreach ($tagList as $id => $tagsString ) {
			$tags = explode(',', $tagsString);
			wp_set_object_terms($id, $tags, 'ngg_tag');
		}

		\NextCellent\show_success( __( 'Update successful', "nggallery" ) );
	}
}