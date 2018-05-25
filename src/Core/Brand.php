<?php
/**
 * Branding Class
 *
 * Brands the login screen for WordPress and replaces the header link to the main site.
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

if ( ! class_exists( 'WPS\Core\Brand' ) ) {
	/**
	 * Branding Class
	 *
	 * Assists in applying branding to the login page.
	 *
	 * @package WPS\Core
	 * @author  Travis Smith <t@wpsmith.net>
	 * @todo    Support Global Options for logo.
	 */
	class Brand extends Singleton {

		/**
		 *  Class Args.
		 *
		 * @var array
		 *
		 * @since 1.0.0
		 */
		public $args = array();

		/**
		 * Brand constructor.
		 *
		 * @param array $args Array of styling properties.
		 */
		protected function __construct( $args = array() ) {

			if ( ! empty( $args ) ) {
				$this->args = $args;
			}
			add_action( 'login_enqueue_scripts', array( $this, 'login_styles' ) );
			add_filter( 'login_headerurl', array( __NAMESPACE__ . '\Brand', 'login_headerurl' ) );
			add_filter( 'login_headertitle', array( __NAMESPACE__ . '\Brand', 'login_headertitle' ) );
		}

		/**
		 *
		 * Returns current home url.
		 *
		 * @param string $url Default URL.
		 *
		 * @return string Home URL.
		 */
		public static function login_headerurl( $url ) {
			return get_bloginfo( 'url' );
		}

		/**
		 *  Returns current home url.
		 *
		 * @since 1.0.0
		 *
		 * @param string $url URI.
		 *
		 * @return string     Blog Title and Description.
		 */

		/**
		 * Returns the Blog Title and Description for the login header.
		 *
		 * @return string Blog Title and Description.
		 */
		public static function login_headertitle() {
			return get_bloginfo( 'name' ) . ' &#124; ' . get_bloginfo( 'description' );
		}

		/**
		 * Implodes array to be key=>value string
		 *
		 * @param array  $array Array to implode.
		 * @param string $sep   Seperator.
		 *
		 * @return string Imploded array.
		 */
		public static function implode( $array, $sep = ';' ) {
			$r = '';
			foreach ( $array as $key => $value ) {
				$r .= sprintf( '%s: %s%s ', $key, $value, $sep );
			}

			return trim( $r );
		}

		/**
		 * Gets the login style defaults.
		 *
		 * @return array Array of defaults.
		 */
		public function get_defaults() {
			$defaults = array(
				'box-sizing'       => 'border-box',
				'background-image' => sprintf( 'url("%s");', self::get_logo_png() ),
				'background-size'  => 'contain',
				'height'           => '100px',
				'width'            => '320px',
				'padding-bottom'   => '20px',

			);

			if ( isset( $this->args['defaults'] ) ) {
				return wp_parse_args( $this->args['defaults'], $defaults );
			}

			return $defaults;
		}

		/**
		 * Custom inline login styles.
		 */
		public function login_styles() {

			// @todo Check login.css exists in theme, if so load it!
			if ( ! empty( $this->args ) && isset( $this->args['login_style'] ) && is_string( $this->args['login_style'] ) ) {

				$css = sprintf( 'body.login div#login h1 a { %s }', $this->args['login_style'] );
				wp_add_inline_style( 'login', $css );

			} else {

				$defaults = $this->get_defaults();

				$login_style = ! empty( $args ) && is_array( $args['login_style'] ) ? wp_parse_args( $args['login_style'], $defaults ) : $defaults;

				$svg = self::get_logo_svg();
				$svg = $svg ? sprintf( '; background-image: url("%s");', $svg ) : '';
				$css = sprintf( 'body.login div#login h1 a { %s; }', self::implode( $login_style ) . $svg );

				wp_add_inline_style( 'login', $css );

			}
		}

		/**
		 * Determines whether current page is the login page.
		 *
		 * @since 1.0.0
		 */
		public static function is_login_page() {
			return in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ), true );
		}

		/**
		 * Gets a logo by extension.
		 *
		 * Gets a logo image by extension returning the first one found.
		 * It looks for a file named `logo` with the given extension.
		 * It checks the following locations:
		 *  Site Identity via custom_logo
		 *  ACF field: logo from options
		 *  Upload Directory
		 *  Child Theme root
		 *  Child Theme images folder
		 *
		 * @param string $ext Extension of the file.
		 *
		 * @return bool|string
		 */
		protected static function get_logo( $ext ) {
			$url = wp_get_attachment_url( get_theme_mod( 'custom_logo' ) );

			// Check Site Identity.
			if ( $url ) {
				return $url;
			}

			// Check if loaded in options.
			if ( function_exists( 'get_field' ) ) {
				$logo = get_field( 'logo', 'options' );
				if ( '' !== $logo ) {
					return $logo['url'];
				}
			}

			// Check uploads.
			$upload_dir = wp_upload_dir();
			if ( file_exists( $upload_dir['path'] . '/logo.' . $ext ) ) {
				return $upload_dir['url'] . '/logo.' . $ext;
			}

			// Check Theme.
			if ( file_exists( get_stylesheet_directory() . '/logo.' . $ext ) ) {
				return get_stylesheet_directory_uri() . '/logo.' . $ext;
			}

			// Check Theme images folder.
			if ( file_exists( get_stylesheet_directory() . '/images/logo.' . $ext ) ) {
				return get_stylesheet_directory_uri() . '/images/logo.' . $ext;
			}

			return false;
		}

		/**
		 * Gets logo.svg.
		 *
		 * @return bool|string SVG image URL.
		 */
		public static function get_logo_svg() {
			return self::get_logo( 'svg' );
		}

		/**
		 * Determines whether a SVG or PNG logo exists.
		 *
		 * @return bool|string SVG or PNG image URL.
		 */
		public static function logo_exists() {
			return ( self::get_logo( 'svg' ) || self::get_logo( 'png' ) );
		}

		/**
		 * Gets logo.png.
		 *
		 * @return bool|string PNG image URL.
		 */
		public static function get_logo_png() {
			return self::get_logo( 'png' );
		}

	}
}
