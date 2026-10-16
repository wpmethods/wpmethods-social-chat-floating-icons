<?php
/**
 * Plugin Name: Wpmethods Social Chat Floating Icons
 * Description: Display live chat floating icons of any social media like WhatsApp, Messenger, Telegram, etc on your WordPress website.
 * Plugin URI: https://wpmethods.com/product/wpmethods-social-chat-floating-icons
 * Author: WP Methods
 * Author URI: https://wpmethods.com/about
 * Version: 1.1.0
 * Text Domain: wpmethods-social-chat-floating-icons
 * Contributors: wpmethods,ajharrashed
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.9
 * Tested up to: 7.1
 * Requires PHP: 7.4
 *
 * @package WPMESOCH
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin path and url.
define( 'WPMESOCH_WPMETHODS_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPMESOCH_WPMETHODS_URL', plugin_dir_url( __FILE__ ) );

define( 'WPMESOCH_PLUGIN_VERSION', '1.1.0' );

// Autoload Classes.
require_once WPMESOCH_WPMETHODS_PATH . 'file_autoloader/autoload.php';

// Initialize.
add_action(
	'plugins_loaded',
	function () {
		if ( is_admin() ) {
			new WPMESOCH\Wpmesoch_Admin_Settings();
		} else {
			new WPMESOCH\Wpmesoch_Front_End();
		}
	}
);


// Allowed Custom Protocols.
add_filter(
	'kses_allowed_protocols',
	function ( $protocols ) {
		$protocols[] = 'skype';
		$protocols[] = 'viber';
		$protocols[] = 'weixin';
		return $protocols;
	}
);
