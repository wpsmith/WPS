<?php
/**
 * WPS Core Genesis Class
 *
 * @package    WPS\Core
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 WP Smith, Travis Smith
 * @link       https://github.com/wpsmith/WPS/
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\Core;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Genesis' ) ) {
	/**
	 * Genesis Class
	 *
	 * Assists in fixing Genesis custom header styles.
	 *
	 * @package WPS\Core
	 * @author Travis Smith <t@wpsmith.net>
	 */
	class Genesis extends Singleton {
		public function plugins_loaded() {
			add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ), 99 );
		}

		public function after_setup_theme() {
			remove_action( 'wp_head', 'genesis_custom_header_style' );
			add_action( 'wp_head', array( $this, 'genesis_custom_header_style' ) );

			if ( ( defined( 'WP_DEBUG' ) && ! WP_DEBUG ) || ! defined( 'WP_DEBUG' ) ) {
				add_filter( 'genesis_load_deprecated', '__return_false' );
			}
		}

		/**
		 * Custom header callback.
		 *
		 * It outputs special CSS to the document head, modifying the look of the header based on user input.
		 *
		 * @since 1.6.0
		 *
		 * @return void Return early if `custom-header` not supported, user specified own callback, or no options set.
		 */
		function genesis_custom_header_style() {

			// Do nothing if custom header not supported.
			if ( ! current_theme_supports( 'custom-header' ) ) {
				return;
			}

			// Do nothing if user specifies their own callback.
			if ( get_theme_support( 'custom-header', 'wp-head-callback' ) ) {
				return;
			}

			$output = '';

			$header_image = get_header_image();
			$text_color   = get_header_textcolor();

			// If no options set, don't waste the output. Do nothing.
			if ( empty( $header_image ) && ! display_header_text() && $text_color === get_theme_support( 'custom-header', 'default-text-color' ) ) {
				return;
			}

			$header_selector = get_theme_support( 'custom-header', 'header-selector' );
			$title_selector  = genesis_html5() ? '.custom-header .site-title' : '.custom-header #title';
			$desc_selector   = genesis_html5() ? '.custom-header .site-description' : '.custom-header #description';

			// Header selector fallback.
			if ( ! $header_selector ) {
				$header_selector = genesis_html5() ? '.custom-header .site-header' : '.custom-header #header';
			}

			// Header image CSS, if exists.
			if ( $header_image ) {
				$output .= sprintf( '%s{background-image:url(%s) !important; background-repeat:no-repeat !important;}', $header_selector, esc_url( $header_image ) );
			}

			// Header text color CSS, if showing text.
			if ( display_header_text() && $text_color !== get_theme_support( 'custom-header', 'default-text-color' ) ) {
				$output .= sprintf( '%2$s a, %2$s a:hover, %3$s { color: #%1$s !important; }', esc_html( $text_color ), esc_html( $title_selector ), esc_html( $desc_selector ) );
			}

			if ( $output ) {
				printf( '<style type="text/css">%s</style>' . "\n", $output );
			}

		}
	}
}