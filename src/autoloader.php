<?php
/**
 * The auto loader for NextCellent Gallery.
 *
 * This assumes files follow the WordPress file naming conventions,
 * and are in the folder src.
 *
 * This is based on http://www.php-fig.org/psr/psr-4/examples/
 */
spl_autoload_register(function ($class) {

	// project-specific namespace prefix
	$prefix = 'NextCellent\\';

	// base directory for the namespace prefix
	$base_dir = __DIR__ . '/';

	// does the class use the namespace prefix?
	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		// no, move to the next registered autoloader
		return;
	}

	$class = strtolower($class);

	// get the relative class name
	$relative_class = substr($class, $len);

	//Do the WordPress stuff
	$relative_class = str_replace('_', '-', $relative_class);
	$pos = strrpos($relative_class, '\\');

	if($pos) {
		$relative_class = substr_replace($relative_class, '\class-', $pos, 1);
	} else {
		$relative_class = 'class-' .  $relative_class;
	}


	// replace the namespace prefix with the base directory, replace namespace
	// separators with directory separators in the relative class name, append
	// with .php
	$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

	// if the file exists, require it
	if (file_exists($file)) {
		require $file;
	}
});