<?php

namespace NextCellent\Admin;

/**
 * @author  Niko Strijbol
 * @version 7/04/2016
 */
abstract class Abstract_Tab_Page extends Post_Admin_Page {

	/**
	 * @var string|Abstract_Tab $current The current page or the name of the current page.
	 */
	protected $current;
	
	public function __construct() {
		
		$tab = $this->default_tab();
		
		if(isset($_GET['tab'])) {
			$tab = $_GET['tab'];
		}
		
		$this->load_page($tab);
	}

	/**
	 * Converts a name of a tab to a page, or sets the name as current.
	 *
	 * @param string $name The name.
	 *
	 * @see NGG_Options::current
	 */
	protected abstract function load_page( $name );

	/**
	 * Render the page content
	 */
	public function display() {

		parent::display();

		// get list of tabs
		$tabs = $this->get_tabs();

		?>
		<div class="wrap">
			<h1><?= $this->page_title() ?></h1>
			<h2 class="nav-tab-wrapper">
				<?php
				foreach($tabs as $tab => $name) {
					$class =  $this->is_active($tab) ? 'nav-tab-active' : '';
					echo "<a class='nav-tab $class' href='" . $this->get_full_url() . "&tab=$tab'>$name</a>";
				}
				?>
			</h2>
			<?php
			//If the current page is a string, we need a third party tab.
			if (is_string($this->current)) {
				if ( method_exists( $this, "tab_$this->current" )) {
					call_user_func( array( $this , "tab_$this->current"));
				} else {
					do_action( 'ngg_tab_content_' . $this->current);
				}
			} else {
				//Display the page.
				$this->current->render();
			}
			?>
		</div>
		<?php
		if(!is_string($this->current)) {
			$this->current->print_scripts();
		}
	}

	/**
	 * @return string The name of the default tab.
	 */
	protected abstract function default_tab();
	
	/**
	 * @return string The title of this page.
	 */
	protected abstract function page_title();

	/**
	 * Check if a tab is active or not.
	 *
	 * @param string $tab The name of the tab.
	 *
	 * @return bool True if active, otherwise false.
	 */
	protected function is_active($tab) {

		if(is_string($this->current)) {
			return $this->current === $tab;
		} else {
			return $this->current->get_name() === $tab;
		}
	}

	/**
	 * Create array for tabs and add a filter for other plugins to inject more tabs
	 *
	 * @return array $tabs
	 */
	protected abstract function get_tabs();

	/**
	 * @deprecated Please see the implementing function for details.
	 */
	protected abstract function old_processor();

	/**
	 * Do the processing.
	 * 
	 * @param string $new_action The new action.
	 * @param string $old_action The old, deprecated action.
	 */
	protected function process($new_action, $old_action) {
		//For legacy pages, we do the old processing.
		if(is_string($this->current)) {
			$this->old_processor();
		} else {

			//Check the referrer.
			check_admin_referer( 'ncg_tab_' . $this->current->get_name() );

			$this->current->processor();

			/**
			 * Fires when settings are updated on a settings page.
			 *
			 * @param array $_POST The post variables. Use this, and not $_POST, as $_POST might
			 *                     be empty in the future.
			 */
			do_action( $new_action . '_' . $this->current->get_name(), $_POST);
		}

		/**
		 * @deprecated Please use the appropriate 'ncg_upload_[PAGE]' action.
		 */
		do_action( $old_action );
	}
}