<?php

namespace NextCellent\Models;

use NextCellent\Database\Manager;

/**
 * @author  Niko Strijbol
 */
trait Savable_Model {

	/**
	 * Count all rows in a given table.
	 *
	 * @param string $table The table name.
	 *
	 * @return int The number of rows.
	 */
	protected static function count_table($table) {
		$manager = Manager::get();

		return $manager->get_int("SELECT COUNT(*) FROM $table");
	}
	
	protected $properties = [];

	public function __get( $name ) {
		if(array_key_exists($name, $this->properties)) {
			return $this->properties[$name];
		} elseif(method_exists($this, 'get_' . $name)) {
			return call_user_func([$this, 'get_' . $name]);
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

	protected function save_model($table, $column, $id) {
		return Manager::get()->update($table, $this->to_array(), array(
			$column => $id
		));
	}

	/**
	 * Convert to model to an array of data.
	 *
	 * @return array The data.
	 */
	protected abstract function to_array();
}