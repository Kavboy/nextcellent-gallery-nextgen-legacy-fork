<?php

namespace NextCellent\Models;

use NextCellent\Database\Manager;

/**
 * @author  Niko Strijbol
 * @version 23/02/2016
 */
abstract class Model {

	/**
	 * @var Manager The database manager.
	 */
	protected $manager;

	protected $properties = array();

	/**
	 * @param Manager $manager The database manager.
	 */
	public function __construct($manager = null) {
		if($manager == null) {
			$this->manager = Manager::get();
		} else {
			$this->manager = $manager;
		}
	}

	/**
	 * Count all rows in a given table.
	 *
	 * @param string $table The table name.
	 *
	 * @return int The number of rows.
	 */
	protected static function count($table) {
		$manager = Manager::get();

		return $manager->get_int('SELECT COUNT(*) FROM ' . $table);
	}

	public function __get( $name ) {
		if(array_key_exists($name, $this->properties)) {
			return $this->properties[$name];
		} else {
			throw new Wrong_Property_Exception($name);
		}
	}

	public function __set( $name, $value ) {
		$this->properties[$name] = $value;
	}

	protected function set_properties($properties) {
		foreach ( $properties as $property => $value ) {
			$this->properties[$property] = $value;
		}
	}

	public function save($table, $column, $id) {
		return $this->manager->update($table, $this->to_array(), array(
			$column => $id
		));
	}

	/**
	 * Convert to model to an array of data.
	 *
	 * @return array The data.
	 */
    protected abstract function to_array();

	public abstract function delete();
}