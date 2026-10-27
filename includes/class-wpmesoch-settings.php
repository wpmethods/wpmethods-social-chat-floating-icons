<?php
/**
 * Shared, validated widget settings.
 *
 * @package WPMESOCH
 */

namespace WPMESOCH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin settings.
 */
class Wpmesoch_Settings {
	/** Get safe settings, including defaults for existing installations. */
	public static function get() {
		return self::sanitize( get_option( 'wpmesoch_settings', array() ) );
	}

	/**
	 * Validate stored values as well as Settings API submissions.
	 *
	 * @param mixed $input Raw settings.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$output   = array();
		$defaults = array(
			'toggle_bg_color'              => '#00da62',
			'toggle_gbg_color'             => '',
			'icon_color'                   => '#ffffff',
			'hover_color'                  => '#128c7e',
			'pulse_animation_border_color' => '#00da62',
		);
		foreach ( $defaults as $key => $default ) {
			$output[ $key ] = self::color( $input[ $key ] ?? '', $default );
		}
		foreach ( array( 'custom_text', 'woo_product_msg', 'chat_greeting' ) as $key ) {
			$output[ $key ] = sanitize_textarea_field( self::text( $input[ $key ] ?? '' ) );
		}
		foreach ( array( 'woo_product_name', 'woo_product_price', 'chat_title' ) as $key ) {
			$output[ $key ] = sanitize_text_field( self::text( $input[ $key ] ?? '' ) );
		}
		$output['welcome_enabled'] = '1' === self::text( $input['welcome_enabled'] ?? '1' ) ? '1' : '0';
		$output['welcome_once']    = '1' === self::text( $input['welcome_once'] ?? '1' ) ? '1' : '0';
		$output['welcome_title']   = sanitize_text_field( self::text( $input['welcome_title'] ?? '' ) );
		$output['welcome_text']    = sanitize_textarea_field( self::text( $input['welcome_text'] ?? '' ) );
		$output['welcome_delay']   = self::number( $input['welcome_delay'] ?? 4, 0, 300, 4 );
		$output['welcome_badge']   = self::number( $input['welcome_badge'] ?? 1, 0, 99, 1 );
		foreach ( array( 'load_fontawesome', 'whatsapp_chatbox', 'messenger_chatbox', 'telegram_chatbox', 'show_labels' ) as $key ) {
			$output[ $key ] = ! isset( $input[ $key ] ) || '1' === self::text( $input[ $key ] ) ? '1' : '0';
		}
		$output['track_woo_product'] = '1' === self::text( $input['track_woo_product'] ?? '' ) ? '1' : '0';
		$output['toggle_icon_class'] = self::icon( $input['toggle_icon_class'] ?? '', 'fas fa-comment-dots' );
		$output['icon_size']         = self::number( $input['icon_size'] ?? 30, 12, 64, 30 );
		$output['height_width']      = self::number( $input['height_width'] ?? 50, 36, 96, 50 );
		$output['position']          = 'left' === ( $input['position'] ?? '' ) ? 'left' : 'right';
		foreach ( array( 'bottom_offset', 'left_offset', 'right_offset' ) as $key ) {
			$value          = self::text( $input[ $key ] ?? '' );
			$output[ $key ] = preg_match( '/^\d{1,4}(?:\.\d{1,2})?(?:px|%|rem|em|vh|vw)$/', $value ) ? $value : '20px';
		}
		$output['wpmesoch_links'] = array();
		$links                    = isset( $input['wpmesoch_links'] ) && is_array( $input['wpmesoch_links'] ) ? $input['wpmesoch_links'] : array();
		foreach ( $links as $link ) {
			if ( ! is_array( $link ) ) {
				continue;
			}
			$url = esc_url_raw( self::text( $link['url'] ?? '' ), array( 'https', 'http', 'tel', 'mailto', 'skype', 'viber', 'weixin' ) );
			if ( '' === $url ) {
				continue;
			}
			$output['wpmesoch_links'][] = array(
				'enabled'    => ! isset( $link['enabled'] ) || '1' === self::text( $link['enabled'] ) ? '1' : '0',
				'url'        => $url,
				'icon'       => self::icon( $link['icon'] ?? '', 'fab fa-whatsapp' ),
				'label'      => sanitize_text_field( self::text( $link['label'] ?? '' ) ),
				'color'      => self::color( $link['color'] ?? '', '#ffffff' ),
				'bg_color'   => self::color( $link['bg_color'] ?? '', '#00da62' ),
				's_bg_color' => self::color( $link['s_bg_color'] ?? '', '' ),
			);
		}
		return $output;
	}

	/**
	 * Prevent malformed arrays from reaching string sanitizers.
	 *
	 * @param mixed $value Input value.
	 * @return string
	 */
	private static function text( $value ) {
		return is_scalar( $value ) ? (string) $value : '';
	}

	/**
	 * Sanitize each CSS class separately.
	 *
	 * @param mixed  $value Input value.
	 * @param string $fallback Default classes.
	 * @return string
	 */
	private static function icon( $value, $fallback ) {
		$classes = implode( ' ', array_filter( array_map( 'sanitize_html_class', preg_split( '/\s+/', trim( self::text( $value ) ) ) ) ) );
		return $classes ? $classes : $fallback;
	}

	/**
	 * Validate a hexadecimal color.
	 *
	 * @param mixed  $value Input value.
	 * @param string $fallback Default value.
	 * @return string
	 */
	private static function color( $value, $fallback ) {
		$color = sanitize_hex_color( self::text( $value ) );
		return $color ? $color : $fallback;
	}

	/**
	 * Clamp numeric settings to supported bounds.
	 *
	 * @param mixed $value Input value.
	 *
	 * @param int   $min Minimum.
	 *
	 * @param int   $max Maximum.
	 *
	 * @param int   $fallback Default value.
	 *
	 * @return int
	 */
	private static function number( $value, $min, $max, $fallback ) {
		return is_numeric( $value ) ? max( $min, min( $max, (int) $value ) ) : $fallback;
	}
}
