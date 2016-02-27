<?php
/**
 * The auto loader for the admin part of NextCellent Gallery.
 *
 * This assumes files follow the WordPress file naming conventions,
 * and are in the folder admin.
 *
 * NOTE: this file is almost identical to the main auto loader. However,
 * for now we have chose to keep this file here, to simply things.
 * Once the code allows for the admin part to move to the src folder,
 * this auto loader will be removed.
 *
 * This is based on http://www.php-fig.org/psr/psr-4/examples/
 */
spl_autoload_register(function ($class) {

	// project-specific namespace prefix
	$prefix = 'NextCellent\\Admin';

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
	$relative_class = substr($class, $len + 1);

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