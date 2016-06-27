<?php

namespace NextCellent\Database\Query;

/**
 * A simple query builder to help with the SQL queries. The query builder only supports simple SQL queries.
 *
 * This is a fluent api. Calling a function twice when not supported will cause the old value to be overwritten.
 *
 * @author  Niko Strijbol
 */
class Builder {

	private $function;
	private $table;

	public function __construct() {}

	public function select() {
		$this->function = 'SELECT';
		return $this;
	}

	public function update() {
		$this->function = 'UPDATE';
	}

	public function count() {
		$this->function = 'COUNT';
	}

	public function delete() {
		$this->function = 'DELETE';
	}

	/**
	 * @param string $table
	 * @return self
	 */
	public function table($table) {
		$this->table = $table;
		return $this;
	}



}