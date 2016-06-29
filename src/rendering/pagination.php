<?php

namespace NextCellent\Rendering;

/**
 * @author  Niko Strijbol
 * @version 16/06/2016
 */
class Pagination {

	private $page;

	private $per_page;

	private $total;

	/**
	 * @param int $page The current page number, starting with 1.
	 * @param int $per_page The number of items per page.
	 * @param int $total The total number of items.
	 */
	public function __construct($page, $per_page, $total) {
		$this->page     = $page;
		$this->per_page = $per_page;
		$this->total    = $total;
	}

	/**
	 * Make pagination in HTML.
	 * 
	 * @return string The HTML output.
	 */
	public function render() {
		return apply_filters('ncg_pagination_output', $this->default_renderer(), $this->page, $this->per_page, $this->total);
	}

	private function default_renderer() {

		//If the amount per page is 0 or less, we don't need navigation.
		//If the amount of items fit on one page, we don't need navigation.
		if($this->per_page <= 0 || $this->total <= $this->per_page) {
			return '<div class="ngg-clear"></div>';
		}

		//Amount of pages we'll need.
		$total_pages = ceil( $this->total / $this->per_page );

		ob_start();

		echo '<div class="ngg-navigation ncg-navigation">';

		//If we are not one page 1, we need a link for the previous page.
		if($this->page > 1) {
			$this->print_previous_link();
		}

		//Do the middle part
		$dots = false;
		for ( $page_num = 1; $page_num <= $total_pages; $page_num++ ) {

			//The current page
			if ( $this->page == $page_num ) {
				echo "<span class='current'>$page_num</span>";
				continue;
			}
			
			$data = [
				'ncg-page'  => $page_num,
			];

			// The other pages
			// We print the first two pages, two before/after the current one and the two last pages.
			if ( $page_num <= 2 || ( $page_num >= $this->page - 2 && $page_num <= $this->page + 2 ) || $page_num >= $total_pages - 2 ) {

				$link = Rewrite::get_link( $data );
				$title = sprintf(__('Go to page %d', 'nggallery'), $page_num);

				echo "<a class='page-numbers' href='$link' title='$title'>$page_num</a>";
				$dots = true;
			}
			//Do the dots.
			elseif($dots) {
				echo '<span class="more">&hellip;</span>';
				$dots = false;
			}
		}

		//If we are not on the lat page, we need a link for the next page.

		if ( $this->page < $total_pages ) {
			$this->print_next_link();
		}

		echo '</div>';

		return ob_get_clean();
	}

	private function print_previous_link() {

		$prev_symbol = apply_filters('ngg_prev_symbol', '&#9668;');

		$previous = $this->page - 1;

		$prev = Rewrite::get_link( [
			'ncg-page'  => $previous
		]);

		$t = __('Go to the previous page', 'nggallery');

		echo "<a class='prev' id='ngg-prev-$previous' href='$prev'>$prev_symbol</a>";
	}

	private function print_next_link() {

		$next_symbol = apply_filters('ngg_prev_symbol', '&#9658;');

		$next_page = $this->page + 1;

		$next = Rewrite::get_link( [
			'ncg-page' => $next_page 
		]);

		$t = __('Go to the next page', 'nggallery');

		echo "<a class='next' id='ngg-next-$next_page' href='$next' title='$t'>$next_symbol</a>";
	}

	public function __toString() {
		return $this->render();
	}
}