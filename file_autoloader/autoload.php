<?php
/**
 * Autoload plugin classes.
 *
 * @package WPMESOCH
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
spl_autoload_register(
	function ( $class_name ) {
		$prefix   = 'WPMESOCH\\';
		$base_dir = WPMESOCH_WPMETHODS_PATH . 'includes/';

		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class_name, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class_name, $len );
		$file           = $base_dir . 'class-' . strtolower( str_replace( '_', '-', $relative_class ) ) . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);
