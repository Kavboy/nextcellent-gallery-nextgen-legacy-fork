<?php

namespace NextCellent\Admin\Manage\Galleries;

use NextCellent\Models\Gallery;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class NGG_List_Table
 *
 * This class represents the listing of the galleries in the admin menu.
 *
 * This class was written with WP_List_Table from WordPress 4.3.
 * If this doesn't work anymore in the future, it's because that class has changed.
 */
class Gallery_List_Table extends \WP_List_Table {

	private $base;

	public function __construct( $base, $screen = null ) {

		parent::__construct( array( 'screen' => $screen, 'plural' => 'ngg-manager' ) );

		$this->base = $base;
	}

	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */
	public function prepare_items() {

		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		/**
		 * Do the pagination.
		 */
		$currentPage = $this->get_pagenum();
		$perPage     = $this->get_items_per_page('ngg_galleries_per_page', 25);

		/**
		 * Sorting
		 */
		if ( ( isset ( $_GET['order'] ) && $_GET['order'] == 'desc' ) ) {
			$order = 'DESC';
		} else {
			$order = 'ASC';
		}

		if ( ( isset ( $_GET['orderby'] ) && ( in_array( $_GET['orderby'], array( 'gid', 'title', 'author' ) ) ) ) ) {
			$order_by = $_GET['orderby'];
		} else {
			$order_by = 'gid';
		}

		$start       = ( $currentPage - 1 ) * $perPage;
		$this->items = Gallery::all($order_by, $order, $start, $perPage, true);

		$totalItems = Gallery::count();

		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'per_page'    => $perPage
		) );
	}

	/**
	 * Get the hidden columns from the screen options.
	 */
	private function get_hidden_columns() {
		return (array) get_user_option( 'manage' . $this->screen->id . 'columnshidden' );
	}

	/**
	 * The checkbox column.
	 *
	 * @param Gallery $gallery
	 *
	 * @return string
	 */
	protected function column_cb( $gallery ) {
		if ( \nggAdmin::can_manage_this_gallery( $gallery->author ) ) {
			return '<input name="doaction[]" type="checkbox" value="' . $gallery->id . '" />';
		} else {
			return "";
		}
	}

	/**
	 * The title column.
	 *
	 * @param Gallery $gallery
	 *
	 * @return string
	 */
	protected function column_title( $gallery ) {
		if ( \nggAdmin::can_manage_this_gallery( $gallery->author ) ) {
			$out = '<a href="' . wp_nonce_url( $this->base . '&mode=image&gid=' . $gallery->id,
					'ngg_editgallery' ) . '" class="edit" title="' . __( 'Edit' ) . '">';
			$out .= esc_html( $gallery->title );
			$out .= "</a>";
		} else {
			$out = esc_html( $gallery->title );
		}
		$out .= '<div class="row-actions"></div>';

		return $out;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  Gallery $gallery
	 * @param  String $column_name - Current column name
	 *
	 * @return Mixed
	 */
	protected function column_default( $gallery, $column_name ) {
		switch ( $column_name ) {
			case 'id':
				return $gallery->id;
			case 'description':
				return $gallery->description;
			case 'author':
				$author = get_userdata( (int) $gallery->author );
				return $author->display_name;
			case 'page_id':
				return $gallery->page_id;
			case 'quantity':
				return $gallery->count_images();
			default:
				ob_start();
				do_action( 'ngg_manage_gallery_custom_column', $column_name, $gallery->id );

				return ob_get_clean();
		}
	}

	/**
	 * With this we can register the columns in the screen options.
	 */
	public static function get_columns_static() {

		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'id'          => __( 'ID', 'nggallery' ),
			'title'       => __( 'Title', 'nggallery' ),
			'description' => __( 'Description', 'nggallery' ),
			'author'      => __( 'Author', 'nggallery' ),
			'page_id'     => __( 'Page ID', 'nggallery' ),
			'quantity'    => __( 'Images', 'nggallery' )
		);

		/**
		 * Apply a filter to the columns.
		 */
		$columns = apply_filters( 'ngg_manage_gallery_columns', $columns );

		return $columns;

	}

	/**
	 * Get the columns.
	 */
	public function get_columns() {

		return self::get_columns_static();
	}

	/**
	 * Get the sortable columns.
	 */
	protected function get_sortable_columns() {
		return array(
			'id'     => array( 'gid', true ),
			'title'  => array( 'title', false ),
			'author' => array( 'author', false )
		);
	}

	protected function get_bulk_actions() {
		return array(
			'delete_gallery' => __( 'Delete', 'nggallery' ),
			'set_watermark'  => __( 'Set watermark', 'nggallery' ),
			'new_thumbnail'  => __( 'Create new thumbnails', 'nggallery' ),
			'resize_images'  => __( 'Resize images', 'nggallery' ),
			'import_meta'    => __( 'Import metadata', 'nggallery' ),
			'recover_images' => __( 'Recover from backup', 'nggallery' ),
		);
	}
}