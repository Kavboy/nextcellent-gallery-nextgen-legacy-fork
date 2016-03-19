<?php

namespace NextCellent\Admin\Manage\Albums;

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
class Album_List_Table extends \WP_List_Table {

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
		$perPage     = $this->get_items_per_page('ngg_albums_per_page', 25);

		/**
		 * Sorting
		 */
		if ( ( isset ( $_GET['order'] ) && $_GET['order'] == 'desc' ) ) {
			$order = 'DESC';
		} else {
			$order = 'ASC';
		}

		if ( ( isset ( $_GET['orderby'] ) && ( in_array( $_GET['orderby'], array( 'id', 'name') ) ) ) ) {
			$order_by = $_GET['orderby'];
		} else {
			$order_by = 'id';
		}

		/**
		 * @global \nggdb $nggdb
		 */
		global $nggdb;

		$start       = ( $currentPage - 1 ) * $perPage;
		$this->items = $nggdb->find_all_album($order_by, $order, $perPage, $start);

		$totalItems = $nggdb->count_albums();

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
	 * @param \stdClass $album
	 *
	 * @return string
	 */
	protected function column_cb( $album ) {
		return '<input name="doaction[]" type="checkbox" value="' . $album->id . '" />';
	}

	/**
	 * The title column.
	 *
	 * @param \stdClass $album
	 *
	 * @return string
	 */
	protected function column_title( $album ) {

		$out = '<a href="' . wp_nonce_url( $this->base . '&mode=edit&id=' . $album->id,
				'ncg_edit_album' ) . '" class="edit" title="' . __( 'Edit' ) . '">';
		$out .= esc_html( $album->name );
		$out .= "</a>";

		return $out;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  \stdClass $album
	 * @param  String $column_name - Current column name
	 *
	 * @return Mixed
	 */
	protected function column_default( $album, $column_name ) {

		//var_dump($album);

		switch ( $column_name ) {
			case 'id':
				return $album->id;
			case 'description':
				return $album->albumdesc;
			case 'previewpic':
				return $album->previewpic;
			case 'page_id':
				return $album->pageid;
			default:
				ob_start();
				do_action( 'ngg_manage_album_custom_column', $column_name, $album->id );

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
			'previewpic'      => __( 'Preview image', 'nggallery' ),
			'page_id'     => __( 'Page ID', 'nggallery' ),
		);

		/**
		 * Apply a filter to the columns.
		 */
		$columns = apply_filters( 'ngg_manage_albums_columns', $columns );

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
			'id'     => array( 'id', true ),
			'title'  => array( 'title', false ),
		);
	}

	protected function get_bulk_actions() {
		return array(
			'delete_albums' => __( 'Delete', 'nggallery' )
		);
	}
}