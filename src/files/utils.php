<?php
/**
 * File utils.
 */
namespace NextCellent\Files\Utils;

/**
 * Return an unique name for a file inside a folder.
 *
 * @param string $fileName The desired name for the file.
 * @param string $path     The path where the file should go.
 *
 * @return string The new file name.
 */
function unique_file_name($fileName, $path) {
	return wp_unique_filename($path, $fileName);
}

/**
 * Create an unique folder name for a given one. If the original folder exists, a new name is made by attaching a
 * vertical dash '-' and a number to the original name.
 * 
 * This function assumes the path exists.
 * 
 * @param string $folderName The original name of the folder.
 * @param string $path The path in which the original folder should be made.
 *
 * @return string The unique name of the folder.
 */
function unique_folder_name($folderName, $path) {

	$start = 1;
	$newName = $folderName;

	if(is_dir($path . $folderName)) {
		do {
			$newName = $folderName . '-' . $start++;
		} while(is_dir($path . $newName));
	}

	return $newName;
}