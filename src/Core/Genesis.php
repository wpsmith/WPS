<?php
/**
 * Genesis Class
 *
 * Replaces the Genesis header style functions with a custom header style.
 *
 * You may copy, distribute and modify the software as long as you track changes/dates in source files.
 * Any modifications to or software including (via compiler) GPL-licensed code must also be made
 * available under the GPL along with build & install instructions.
 *
 * @package    WPS\Core
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link       https://github.com/wpsmith/WPS
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\Core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPS\Core\Genesis' ) ) {
	/**
	 * Genesis Class
	 *
	 * Assists in fixing Genesis custom header styles.
	 *
	 * @package WPS\Core
	 * @author  Travis Smith <t@wpsmith.net>
	 */
	class Genesis extends Singleton {

		/**
		 * Hook into plugins_loaded.
		 */
		public function plugins_loaded() {
			add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ), 99 );
		}

		/**
		 * Hook into genesis theme.
		 */
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
		 * It outputs special CSS to the document head
		 * modifying the look of the header based on user input.
		 *
		 * @since 1.6.0
		 *
		 * @return void Return early if `custom-header` not supported, user specified own callback, or no options set.
		 */
		public function genesis_custom_header_style() {

			// Do nothing if custom header not supported or user specifies their own callback.
			if ( ! current_theme_supports( 'custom-header' ) || get_theme_support( 'custom-header', 'wp-head-callback' ) ) {
				return;
			}

			$output = '';

			$header_image = get_header_image();
			$text_color   = get_header_textcolor();

			// If no options set, don't waste the output. Do nothing.
			if (
				empty( $header_image ) &&
				! display_header_text() &&
				get_theme_support( 'custom-header', 'default-text-color' ) === $text_color
			) {
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
			if (
				display_header_text() &&
				get_theme_support( 'custom-header', 'default-text-color' ) !== $text_color
			) {
				$output .= sprintf( '%2$s a, %2$s a:hover, %3$s { color: #%1$s !important; }', $text_color, $title_selector, $desc_selector );
			}

			if ( $output ) {
				// $output is already escaped above.
				printf( '<style type="text/css">%s</style>' . "\n", esc_html( $output ) );
			}

		}
	}
}
