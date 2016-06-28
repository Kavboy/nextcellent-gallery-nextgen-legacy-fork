<?php  

namespace NextCellent\Admin;
use NextCellent\Files\FileException;
use NextCellent\Options\Options;use function NextCellent\Rendering\Css\getCssFilesFrom;
use function NextCellent\Rendering\Css\getThemeCssFile;

/**
* Class Style_Page
 * @package NextCellent\Admin
 *          
 * @todo Rewrite this to a proper page.
 */
class Style_Page extends Post_Admin_Page {

	const NAME = 'style';
	const SEPARATOR = '|&|';

	protected function processor() {

		check_admin_referer('ngg_style');

		var_dump($_POST);

		if(!isset($_POST['mode'])) {
			return;
		}

		if($_POST['mode'] === 'update-css') {

			//Check for permission
			if ( !current_user_can('edit_themes') ) {
				\NextCellent\show_error(__('You do not have sufficient permissions to edit templates for this blog.', 'nggallery'));
				return;
			}

			$activeFile = $_POST['file'];
			$activeFolder = $_POST['folder'];

			$moved = false;
			$options = Options::getInstance();

			//If it is a built in, update the options.
			if($activeFolder === \NextCellent\Rendering\Css\FOLDER_BUILTIN) {
				$options->update_option(Options::STYLE_CSS_FOLDER, \NextCellent\Rendering\Css\FOLDER_STYLES);
				$moved = true;
			}

			//Otherwise we have a built in file, and we copy it.

			//Save the file in the correct location.
			$fullPath = NCG_USER_FOLDER_PATH . $activeFile;

			try {
				\NextCellent\Files\Common\writeToFile($fullPath, $_POST['content']);
			} catch (FileException $e) {
				\NextCellent\show_error(sprintf(__("Could not save file: %s", 'nggallery'), $e->getMessage()));
				if($moved) {
					$options->update_option(Options::STYLE_CSS_FOLDER, \NextCellent\Rendering\Css\FOLDER_BUILTIN);
				}
				return;
			}

			\NextCellent\show_success(__('CSS file successfully updated.','nggallery'));
			return;
		}

		return;

		global $ngg;
		$options = get_option('ngg-options');
		$i = 0;

		if ( isset( $_POST['activate'] ) ) {
			check_admin_referer('ngg_style');
			$file = $_POST['css'];
			$activate = $_POST['activateCSS']; 
			
			// save option now
			$options['activateCSS'] = $activate;
			$options['CSSfile'] = $file;
			update_option('ngg_options', $ngg->options);
			
			if ( isset($activate) ) {
				\nggGallery::show_message(__('Successfully selected CSS file.','nggallery') );
			} else {
				\nggGallery::show_message(__('No CSS file will be used.','nggallery') );
			}
		}

		if (isset($_POST['updatecss'])) {
			
			check_admin_referer('ngg_style');

			if ( !current_user_can('edit_themes') )
				{wp_die('<p>'.__('You do not have sufficient permissions to edit templates for this blog.').'</p>');}

			$newcontent = stripslashes($_POST['newcontent']);
			$old_path = $_POST['file'];
			$folder = $_POST['folder'];
			
			//if the file is in the css folder, copy it.
			if ($folder === 'css') {
				$filename = basename ($old_path, '.css');
				$new_path = NGG_CONTENT_DIR . "/ngg_styles/" . $filename . ".css";
				//check for duplicate files
				while ( file_exists( $new_path ) ) {
					$i++;
					$new_path = NGG_CONTENT_DIR . "/ngg_styles/"  . $filename . "-" . $i . ".css";
				}
				//check if ngg_styles exist or not
				if ( !file_exists(NGG_CONTENT_DIR . "/ngg_styles") ) {
					wp_mkdir_p( NGG_CONTENT_DIR . "/ngg_styles" );
				}
				//copy the file
				if ( copy($old_path, $new_path) ) {
					//set option to new file
					$options['CSSfile'] = $new_path;
					update_option('ngg_options', $ngg->options);
				} else {
					\nggGallery::show_error(__('Could not move file.','nggallery'));
					return;
				}
			}
	
			if ( file_put_contents($old_path, $newcontent) ) {
				\nggGallery::show_message(__('CSS file successfully updated.','nggallery'));
			} else {
				\nggGallery::show_error(__('Could not save file.','nggallery'));
			}
		}
		
		if (isset($_POST['movecss'])) {

			if ( !current_user_can('edit_themes') )
				{wp_die('<p>'.__('You do not have sufficient permissions to edit templates for this blog.').'</p>');}
			
			$old_path = $_POST['oldpath'];
			$new_path = NGG_CONTENT_DIR . "/ngg_styles/nggallery.css";
			
			//check for duplicate files
			while ( file_exists( $new_path ) ) {
				$i++;
				$new_path = NGG_CONTENT_DIR . "/ngg_styles/nggallery-" . $i . ".css";
			}
			
			//move file
			if ( rename( $old_path, $new_path) ) {
				\nggGallery::show_message(__('CSS file successfully moved.','nggallery'));
				//set option to new file
				$options['CSSfile'] = $new_path;
				update_option('ngg_options', $ngg->options);
			} else {
				\nggGallery::show_error(__('Could not move the CSS file.','nggallery'));
			}
		}
	}

	/**
     * Render the page content.
	 *
	 * @since 1.9.22
     *
     */
	public function display() {

		parent::display();

		//Find active CSS file.
		if(getThemeCssFile() !== false) {
			$this->displayShortCss(__('Your CSS file is set by a theme or another plugin.','nggallery'));
			return;
		}

		//Find CSS files
		$files = getCssFilesFrom(NCG_PATH . 'styles');

		if (file_exists(NCG_USER_FOLDER_PATH)) {
			$files = array_merge(getCssFilesFrom(NCG_USER_FOLDER_PATH), $files);
		}

		//Get data for each file.

		$options = Options::getInstance();

		if (!$options[Options::STYLE_USE_CSS]) {
			$this->displayNoCss($files);
			return;
		}

		$activeFile = $options[Options::STYLE_CSS_FILE];
		$activeFolder = $options[Options::STYLE_CSS_FOLDER];

		if($activeFolder === \NextCellent\Rendering\Css\FOLDER_BUILTIN) {
			$fullPath = NCG_PATH . 'styles/' . $activeFile;
		} else {
			$fullPath = NCG_USER_FOLDER_PATH . $activeFile;
		}

		if(!array_key_exists($fullPath, $files)) {
			$this->displayNoCss($files);
			return;
		}

		//Read the data.
		/** @var \SplFileInfo $active */
		$active = $files[$fullPath];
		$data = \NextCellent\Rendering\Css\readData($active);
		
		$readFile = $active->openFile();

		if($readFile->isWritable()) {
			$title = sprintf(__('Editing %s', 'nggallery'), $activeFile);
		} else {
			$title = sprintf(__('Viewing %s', 'nggallery'), $activeFile);
		}

		?>
		<div class="wrap">
			<h2><?php _e('Style Editor','nggallery') ?></h2>
			<div class="fileedit-sub">
				<div class="alignright">
					<form id="theme-selector" name="css-files" method="post">
						<?php wp_nonce_field('ngg_style') ?>
						<label for="activateCSS"><strong><?php _e('Activate and use style sheet:','nggallery') ?></strong></label>
						<input type="checkbox" id="activateCSS" name="activateCSS" value="true" <?php $options->checked(Options::STYLE_USE_CSS); ?>>
						<select name="css" id="theme">
							<?php $this->doDropdown($files, $active) ?>
						</select>
						<input type="hidden" name="mode" value="set-css" />
						<?php submit_button(__('Activate','nggallery'), 'primary', 'submit', false) ?>
					</form>
				</div>
				<div class="alignleft">
					<h3><?= $title ?></h3>
				</div>
				<br class="clear" />
			</div> <!-- fileedit-sub -->
			<div id="templateside">
				<ul>
					<li><strong><?php _e('Author','nggallery') ?>:</strong> <?= $data['author'] ?></li>
					<li><strong><?php _e('Version','nggallery') ?>:</strong> <?= $data['version'] ?></li>
				</ul>
				<p><strong><?php _e('Description','nggallery') ?>:</strong></p>
				<p class="description"><?= $data['desc'] ?></p>
				<p><strong><?php _e('File location','nggallery') ?>:</strong></p>
				<p class="description"><?= $active->getPathname(); ?></p>
			</div>
			<form name="template" id="template" method="post">
				<?php wp_nonce_field('ngg_style') ?>
				<div>
					<textarea cols="70" rows="25" name="content" id="content" tabindex="1"  class="codepress css"><?php foreach($readFile as $line) echo $line ?></textarea>
					<input type="hidden" name="mode" value="update-css" >
					<input type="hidden" name="file" value="<?= $activeFile ?>">
					<input type="hidden" name="folder" value="<?= $activeFolder ?>">
				</div>
				<?php if($readFile->isWritable()): ?>
				<p class="submit"><input class="button-primary action" type="submit" name="submit" value="<?php _e('Update File','nggallery') ?>" tabindex="2" /></p>
				<?php else: ?>
				<p><em><?php _e('If this file were writable you could edit it.','nggallery'); ?></em></p>
				<?php endif; ?>
			</form>
			<div class="clear"></div>
		</div> <!-- wrap-->
		<?php
	}

	private function displayShortCss($message) {
		?>
		<div class="wrap">
		<h2><?php _e('Style Editor', 'nggallery') ?></h2>
		<p><?= $message ?></p>
		</div>
		<?php
	}

	private function displayNoCss($files) {
		?>
		<div class="wrap">
		<h2><?php _e('Style Editor', 'nggallery') ?></h2>
		<p><?php _e('You do not use a CSS file at present. Choose a style below and update to use one.','nggallery') ?></p>
		<form id="themes-selector" name="activate-files" method="post">
			<?php wp_nonce_field('ngg_style') ?>
			<label for="file-selector"><?php _e('Select a file:', 'nggallery') ?></label>
			<select name="file" id="file-selector">
				<?php $this->doDropdown($files); ?>
			</select>
			<input type="hidden" name="mode" value="set-css" />
			<?php submit_button(__('Activate','nggallery')) ?>
		</form>
		</div>
		<?php
	}

	/**
	 * @param \SplFileInfo[]    $files
	 * @param null|\SplFileInfo $active
	 */
	private function doDropdown($files, $active = null) {
		foreach ($files as $file) {
			if(basename($file->getPath()) === 'styles') {
				$value = \NextCellent\Rendering\Css\FOLDER_BUILTIN . self::SEPARATOR . $file->getFilename();
				$display = $file->getFilename() . ' ' . __('(built in)', 'nggallery');
			} else {
				$value = \NextCellent\Rendering\Css\FOLDER_STYLES . self::SEPARATOR . $file->getFilename();
				$display = $file->getFilename() . ' ' .__('(user style)', 'nggallery');
			}
			$value = esc_attr($value);

			if($active === null || $active->getPathname() != $file->getPathname()) {
				echo "<option value='$value'>$display</option>";
			} else {
				echo "<option value='$value' selected>$display</option>";
			}
		}
	}

	public function register_styles() {
		//None.
	}

	public function register_scripts() {
		wp_enqueue_script( 'codepress' );
	}
	/**
	 * A possibility to add help to the screen.
	 *
	 * @param \WP_Screen $screen The current screen.
	 */
	 public function add_help($screen) {
        $help = '<p>' . __( 'You can edit the css file to adjust how your gallery looks.',
						'nggallery' ) . '</p>';
		$help .= '<p>' . __( 'When you save an edited file, NextCellent automatically saves it as a copy in the folder ngg_styles. This protects your changes from upgrades.',
				'nggallery' ) . '</p>';

		$screen->add_help_tab( array(
			'id'      => $screen->id . '-general',
			'title'   => 'Style your gallery',
			'content' => $help
		) );
	}

	/**
	 * Get the name of this page. This is the second part of the full name:
	 *
	 * admin.php?page=[SLUG]-[PAGE_NAME].
	 *
	 * An example is 'admin.php?page=nextcellent-manage-images'
	 *
	 * The 'nextcellent' is the slug, the 'manage-images' is the page name.
	 *
	 * @return string The name.
	 */public function get_name() {
		 return self::NAME;
	 }
}