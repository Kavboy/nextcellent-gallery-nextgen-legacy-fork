<?php
/**
 * This is a collection of helper methods.
 */

namespace NextCellent;

/**
 * Show a success admin notice.
 *
 * @param string $message
 * @param bool $dismissible
 */
function show_success( $message, $dismissible = false ) {
	show_notice( 'notice-success', $message, $dismissible );
}

/**
 * Show an error admin notice.
 *
 * @param string $message
 * @param bool $dismissible
 */
function show_error( $message, $dismissible = false ) {
	show_notice( 'notice-error', $message, $dismissible );
}

/**
 * Show a warning admin notice.
 *
 * @param string $message
 * @param bool $dismissible
 */
function show_warning( $message, $dismissible = false ) {
	show_notice( 'notice-warning', $message, $dismissible );
}

/**
 * Show an info admin notice.
 *
 * @param string $message
 * @param bool $dismissible
 */
function show_info( $message, $dismissible = false ) {
	show_notice( 'notice-info', $message, $dismissible );
}

/**
 * Show an admin notice.
 *
 * @param string $class     The css type of message. Can be .notice-[info|warning|error|success]
 * @param string $message   The message.
 * @param bool $dismissible Dismissble or not.
 */
function show_notice( $class, $message, $dismissible ) {

	$dismiss = $dismissible ? 'is-dismissible' : '';

	echo "<div class='notice $class $dismiss'><p>$message</p></div>";
}

/**
 * Convert a string to a boolean. If the string is 'true', it will be converted to true,
 * otherwise it will be false.
 *
 * @param string $string The string to convert.
 *
 * @return bool The boolean value of the string.
 */
function convert_to_bool($string) {
	return trim($string) == 'true';
}

/**
 * Convert a boolean to a 'Yes' or 'No' string.
 *
 * @param bool $bool The boolean.
 *
 * @return string Yes or No.
 */
function bool_to_yes_no( $bool ) {
	return $bool ? __( 'Yes', 'nggallery' ) : __( 'No', 'nggallery' );
}