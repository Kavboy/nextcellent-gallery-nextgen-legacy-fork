<?php

namespace NextCellent\Database;

use NextCellent\Exception;

/**
 * The NextCellent database connection manager.
 */
class Manager {

	/**
	 * The base name of the database tables.
	 */
	const IMAGES = 'ngg_pictures';
	const GALLERIES = 'ngg_gallery';
	const ALBUMS = 'ngg_album';

	/**
	 * @var \wpdb The database connection.
	 */
	private $connection;

	/**
	 * @var string The name of the picture table.
	 */
	protected $images;

	/**
	 * @var string The name of the gallery table.
	 */
	protected $galleries;

	/**
	 * @var string The name of the album table.
	 */
	protected $albums;

	/**
	 * @param $wpdb \wpdb The connection.
	 *
	 * @internal This object should not be constructed.
	 */
	public function __construct( $wpdb ) {
		$this->connection = $wpdb;

		//Set the table names.
		$this->images  = $this->connection->prefix . self::IMAGES;
		$this->galleries = $this->connection->prefix . self::GALLERIES;
		$this->albums    = $this->connection->prefix . self::ALBUMS;

		//These values are set for backwards compatibility.

		$wpdb->nggpictures = $this->images;
		$wpdb->nggallery   = $this->galleries;
		$wpdb->nggalbum    = $this->albums;
	}

	/**
	 * Get the instance of the manager
	 *
	 * @return Manager
	 */
	public static function get() {
		global $ngg;

		return $ngg->manager;
	}

	/**
	 * Get an int value from the database, e.g. for count.
	 *
	 * The query syntax is the same for the WordPress prepare function.
	 *
	 * @see wpdb::prepare()
	 *
	 * @param string $query The query.
	 * @param array|mixed $args The arguments that do need escaping.
	 *
	 * @return int The result.
	 */
	public function get_int($query, $args = array()) {
		$prepared = $this->connection->prepare($query, $args);
		return (int) $this->connection->get_var($prepared);
	}

	/**
	 * Get a row as associative array.
	 *
	 * The query syntax is the same for the WordPress prepare function.
	 *
	 * @see wpdb::prepare()
	 *
	 * @param string $query The query.
	 * @param array|mixed $args  The arguments.
	 *
	 * @return array|null The data or null if not found.
	 */
	public function get_row($query, $args = array()) {
		$prepared = $this->connection->prepare($query, $args);

		return $this->connection->get_row($prepared, ARRAY_A);
	}

	/**
	 * Get an array of results.
	 *
	 * The query syntax is the same for the WordPress prepare function.
	 *
	 * @see wpdb::prepare()
	 * @see wpdb::get_result()
	 *
	 * @param string $query The query.
	 * @param array $args
	 *
	 * @return array|null The data or null if not found.
	 */
	public function get_results($query, $args = array()) {
		$prepared = $this->connection->prepare($query, $args);

		return $this->connection->get_results($prepared, ARRAY_A);
	}

	/**
	 * Execute a query.
	 *
	 * The query syntax is the same for the WordPress prepare function.
	 *
	 * @see wpdb::prepare()
	 * @see wpdb::query()
	 *
	 * @param string $query The query.
	 *                    such as table and column name.
	 * @param array|mixed $args  The arguments.
	 *
	 * @return int|false The number of rows affected or false on error.
	 */
	public function query($query, $args = array()) {
		$prepared = $this->connection->prepare($query, $args);

		return $this->connection->query($prepared);
	}

	/**
	 * Update a row in the database.
	 *
	 * @see wpdb::update()
	 *
	 * @param string $table The table name.
	 * @param array $data Associative array of the data.
	 * @param array $where Associative array of where clauses.
	 *
	 * @return int The number of affected rows.
	 * @throws SQL_Exception If something went wrong while updating the database.
	 */
	public function update($table, $data, $where) {
		$result = $this->connection->update($table, $data, $where);

		if($result === false) {
			throw new SQL_Exception($this->connection->last_error);
		}

		return $result;
	}

	/**
	 * Delete a row.
	 *
	 * @see wpdb::delete()
	 *
	 * @param string $table The table name.
	 * @param string $column Name of the column.
	 * @param mixed $value The value of the column to match.
	 *
	 * @return int The number of rows affected.
	 * @throws SQL_Exception
	 */
	public function delete($table, $column, $value) {
		$result = $this->connection->delete($table, array($column => $value));

		if($result === false) {
			throw new SQL_Exception($this->connection->last_error);
		}

		return $result;
	}

	/**
	 * @return string
	 */
	public function get_image_table() {
		return $this->images;
	}

	/**
	 * @return string
	 */
	public function get_gallery_table() {
		return $this->galleries;
	}

	/**
	 * @return string
	 */
	public function get_album_table() {
		return $this->albums;
	}
}