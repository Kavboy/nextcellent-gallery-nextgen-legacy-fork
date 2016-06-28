<?php

namespace NextCellent\Files\Common;

use NextCellent\Files\FileException;
use NextCellent\Files\InvalidNameException;
use NextCellent\Files\InvalidPathException;
use NextCellent\Models\Gallery;
use NextCellent\Models\Image;
use NextCellent\Options\Options;

/**
 * Create a folder that satisfies some common things such as cleaned file name, ...
 *
 * @param string $folderName The name of the folder.
 * @param string $path       The relative path to the parent directory of the folder (with trailing slash).
 *
 * @return string The full path to the folder.
 *
 * @throws FileException If something goes wrong while creating the directory.
 * @throws InvalidNameException If the name is invalid.
 * @throws InvalidPathException If the path is invalid.
 */
function createFolder($folderName, $path) {

	//Sanitize the name.
	$name = sanitize_file_name($folderName);

	//Sanitize slashes and add trailing slash.
	$path = trailingslashit($path);

	//Check the name
	if (empty($name)) {
		throw new InvalidNameException($folderName);
	}

	//If the path doesn't exist, attempt to create it.
	if (!is_dir($path)) {
		if ( !wp_mkdir_p($path)) {
			throw new InvalidPathException($path);
		}
	}

	//Get an unique name
	$uniqueName = \NextCellent\Files\Utils\unique_folder_name($name, $path);

	//Create the folder
	if (!wp_mkdir_p($path . $uniqueName)) {
		throw new FileException("Creating folder $uniqueName failed.");
	}

	return trailingslashit($path . $uniqueName);
}

/**
 * Create a thumbnail folder if necessary.
 *
 * @param Gallery $gallery The gallery to make a path for.
 *
 * @throws FileException If the folder creation fails.
 */
function createThumbnailFolder(Gallery $gallery) {

	if(!is_dir($gallery->abs_thumb_path)) {
		if(!wp_mkdir_p($gallery->abs_thumb_path)) {
			throw new FileException('Could not make the thumbnail folder.');
		}
	}
}

/**
 * Scan a directory for images. This does not allow for recursion.
 *
 * @param string $path The absolute path to the folder.
 *
 * @return String[] All the found files.
 */
function scanFolder( $path ) {

	$ext = apply_filters('ngg_allowed_file_types', ['jpeg', 'jpg', 'png', 'gif']);

	$dir = new \FilesystemIterator($path);
	$files = new \CallbackFilterIterator($dir, function(\SplFileInfo $current, $key, $it) use($ext) {
		return $current->isFile() && in_array($current->getExtension(), $ext);
	});

	return iterator_to_array($files);
}

/**
 * Rename a folder.
 *
 * @param string $current Full path to the folder.
 * @param string $newName New name of the folder.
 * @param string $newPath The new path of the folder. If null, the old path is used.
 *
 * @return string The full path to the renamed folder.
 *
 * @throws FileException If something goes wrong while renaming.
 * @throws InvalidNameException If the new name is invalid.
 */
function safeRenameFolder( $current, $newName, $newPath = null ) {

	//Get path if necessary
	if($newPath === null) {
		$newPath = dirname($current);
	}

	//Sanitize name.
	$name = sanitize_file_name($newName);
	if(empty($name)) {
		throw new InvalidNameException($newName);
	}
	//Unique name.
	$name = \NextCellent\Files\Utils\unique_folder_name($name, $newPath);

	$fullOld = trailingslashit($current);
	$fullNew = trailingslashit($newPath . $name);

	if (!rename($fullOld, $fullNew)) {
		throw new FileException("Could not rename folder $current.");
	}

	return $fullNew;
}

/**
 * Rename a file.
 *
 * @param string $current Full path to the file.
 * @param string $newName New name of the file.
 * @param string $newPath The new path of the file. If null, the old path is used.
 *
 * @return string The full path to the renamed file.
 *
 * @throws FileException If something goes wrong while renaming.
 * @throws InvalidNameException If the new name is invalid.
 */
function safeRenameFile( $current, $newName, $newPath = null ) {

	//Get path if necessary
	if($newPath === null) {
		$newPath = dirname($current);
	}

	//Sanitize name.
	$name = sanitize_file_name($newName);
	if(empty($name)) {
		throw new InvalidNameException($newName);
	}
	//Unique name.
	$name = \NextCellent\Files\Utils\unique_file_name($name, $newPath);

	return renameFile($current, $name, $newPath);
}

/**
 * Rename a file. Does not check for duplicates, existing files, and such.
 *
 * @param string $current Full path to the file.
 * @param string $newName New name of the file.
 * @param string $newPath The new path of the file. If null, the old path is used.
 *
 * @return string The full path to the renamed file.
 *
 * @throws FileException If something goes wrong while renaming.
 * @throws InvalidNameException If the new name is invalid.
 */
function renameFile( $current, $newName, $newPath = null ) {

	//Get path if necessary
	if($newPath === null) {
		$newPath = dirname($current);
	}

	$fullNew = $newPath . $newName;

	if (!rename($current, $fullNew)) {
		throw new FileException("Could not rename file $current.");
	}

	return $fullNew;
}

/**
 * Sanitize an existing image. It sanitizes the name, and renames the file.
 *
 * @param string $path Full path to the image.
 *
 * @return string Full path to the new image.
 */
function sanitizeExistingImage($path) {
	$file = new \SplFileObject($path);
	return safeRenameFile($path, $file->getFilename());
}

/**
 * Maybe make a backup of an image, depending on the options.
 *
 * @param Image $image The image or the id of the image.
 *
 * @return bool True if the backup was made.
 *              
 * @throws FileException If something went wrong while copying files.
 */
function makeBackup(Image $image) {

	//We don't need backups.
	if(!Options::getInstance()->get(Options::IMG_USE_BACKUP)) {
		return false;
	}
	
	//If the backup exists already.
	if(file_exists($image->path . Image::BACKUP_SUFFIX)) {
		return false;
	}
	
	if(!copy($image->path, $image->path . Image::BACKUP_SUFFIX)) {
		throw new FileException("Could not create backup for image at $image->path");
	}

	return true;
}

/**
 * Recover image from backup copy and reprocess it.
 *
 * @param Image $image The image.
 *
 * @return bool True if recovered, false if there is no backup.
 *
 * @throws FileException If something went wrong while copying the file.
 */
function recoverBackup(Image $image) {

	if ( !file_exists($image->path . Image::BACKUP_SUFFIX)) {
		return false;
	}

	if ( !copy($image->path . Image::BACKUP_SUFFIX, $image->path)) {
		throw new FileException("Could not restore backup for image at $image->path");
	}

	$image->readMetaData();
	$image->save();

	return true;
}

/**
 * Move images from one gallery to another gallery. After moving, you should get fresh images from the database, as
 * paths in the image object are not updated.
 *
 * @param Image[] $images      The images to move.
 * @param Gallery $destination The destination gallery.
 *
 * @throws FileException If the folder is not writeable.
 */
function moveImages(array $images, Gallery $destination) {

	//Check that we can write in the gallery.
	if ( !is_writeable($destination->abs_path)) {
		throw new FileException("The folder $destination->abs_path is not writeable.");
	}

	foreach ($images as $image) {

		//Move the file.
		$newPath = safeRenameFile($image->path, $image->filename, $destination->abs_path);
		$newName = basename($newPath);

		//Move a backup file.
		if (file_exists( $image->backup_path ) ) {
			rename( $image->backup_path, $newPath . Image::BACKUP_SUFFIX );
		}

		//Move a thumbnail file.
		if (file_exists($image->thumb_path)) {
			renameFile($image->thumb_path, $newName, $destination->abs_thumb_path );
		}

		$image->gallery_id = $destination->id;
		$image->filename = $newName;
		$image->save();
	}
}

/**
 * Write to a file. If the file doesn't exist, attempt to make it.
 *
 * @param string $path Full path to the file.
 *
 * @param string $content
 *
 * @throws FileException
 */
function writeToFile($path, $content) {

	$dir = dirname($path);

	//If the path doesn't exist, attempt to create it.
	if (!is_dir($dir)) {
		if ( !wp_mkdir_p($dir)) {
			throw new FileException('Could not create folder: ' . $dir);
		}
	}
	
	if(file_put_contents($path, $content) === false) {
		throw new FileException("Could not write to file $path");
	}
}