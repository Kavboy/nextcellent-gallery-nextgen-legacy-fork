<?php

namespace NextCellent;

/**
 * The registry is responsible for enabling dependency injection, for use in NextCellent.
 *
 * @since 1.9.31
 */
class NCG_Registry {
	/**
	 * @var array $storage The storage of the classes.
	 */
	private $storage = array();

	/**
	 * Add a class to the registry.
	 *
	 * @param string $id    The unique name of the class. If the name exists already, the existing entry will be
	 *                      overwritten with the new class.
	 * @param object $class The object to save.
	 */
	public function add( $id, $class ) {
		$this->storage[ $id ] = $class;
	}

	/**
	 * Get an entry from the registry.
	 *
	 * @param string $id The name of the entry.
	 *
	 * @return null|object Null if the entry does not exists, otherwise the object.
	 */
	public function get( $id ) {
		return array_key_exists( $id, $this->storage ) ? $this->storage[ $id ] : null;
	}
}