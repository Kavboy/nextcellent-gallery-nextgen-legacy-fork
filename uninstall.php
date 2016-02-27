<?php
/**
 * Uninstall procedure for NextCellent.
 *
 * We use this method instead of the register_uninstall_hook() function,
 * because using the uninstall hook adds an extra query to all pages.
 *
 * This is because WordPress apparently does not autoload the options it
 * uses to save these hooks.
 *
 * This is usually relatively cheap considering the option is cached once
 * called it is called at least one time (and since most installs have multiple
 * plugins that use this).
 *
 * However, if we can avoid it by putting the code here, this is a small fix.
 */

//If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

//Remove transients.
require_once( __DIR__ . '/nggallery.php' );
NCG::remove_transients();


//Run the uninstaller.
include_once( dirname( __FILE__ ) . '/admin/class-installer.php' );
NGG_Installer::uninstall();