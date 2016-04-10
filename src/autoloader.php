<?php
/**
 * The auto loader for NextCellent Gallery.
 *
 * This assumes files follow the WordPress file naming conventions,
 * and are in the folder src.
 *
 * This is based on http://www.php-fig.org/psr/psr-4/examples/
 */
class Autoloader {

	/**
	 * @var string
	 */
	private $prefix;
	/**
	 * @var string
	 */
	private $base_dir;

	/**
	 * Autoloader constructor.
	 *
	 * @param string $prefix The namespace prefix.
	 * @param string $base_dir The base directory that corresponds with the prefix.
	 */
	public function __construct($prefix, $base_dir) {
		$this->prefix = $prefix;
		$this->base_dir = $base_dir;
	}

	/**
	 * Register loader with SPL autoloader stack.
	 */
	public function register() {
		spl_autoload_register(array($this, 'loadClass'));
	}

	/**
	 * Loads the class file for a given class name.
	 *
	 * @param string $class The fully-qualified class name.
	 */
	public function loadClass($class) {
		// does the class use the namespace prefix?
		$len = strlen($this->prefix);
		if (strncmp($this->prefix, $class, $len) !== 0) {
			// no, move to the next registered autoloader
			return;
		}

		$class = strtolower($class);

		// get the relative class name
		$relative_class = substr($class, $len);

		//Do the WordPress stuff
		$relative_class = str_replace('_', '-', $relative_class);

		// replace the namespace prefix with the base directory, replace namespace
		// separators with directory separators in the relative class name, append
		// with .php
		$file = $this->base_dir . str_replace('\\', '/', $relative_class) . '.php';

		// if the file exists, require it
		if (file_exists($file)) {
			require $file;
		}
	}
}