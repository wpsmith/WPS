<?php
/**
 * WPS Core Branding Class
 *
 * @package    WPS\Core
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 WP Smith, Travis Smith
 * @link       https://github.com/wpsmith/WPS/
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

namespace WPS\Core;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Brand' ) ) {
	/**
	 *  Branding Class
	 *
	 * @package WPS\Core
	 * @author Travis Smith <t@wpsmith.net>
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
		 *  Constructor.
		 *
		 * @since 1.0.0
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
		 *  Returns current home url.
		 *
		 * @since 1.0.0
		 *
		 * @param string $url Default URI.
		 *
		 * @return string           Home URI.
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
		 * @return string            Home URI.
		 */
		public static function login_headertitle() {
			return get_bloginfo( 'name' ) . ' &#124; ' . get_bloginfo( 'description' );
		}

		/**
		 * Implodes array to be key=>value string
		 *
		 * @param array $array Array to implode.
		 * @param string $sep Seperator.
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
		 * @return array
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
		 *  Custom inline login styles.
		 *
		 * @since 1.0.0
		 */
		public function login_styles() {

			// @todo Check login.css exists in theme, if so load it!
			if ( ! empty( $this->args ) && isset( $this->args['login_style'] ) && is_string( $this->args['login_style'] ) ) {
				printf( '<style type="text/css">body.login div#login h1 a { %s }</style>', $this->args['login_style'] );
			} else {
				$defaults = $this->get_defaults();

				$login_style = ! empty( $args ) && is_array( $args['login_style'] ) ? wp_parse_args( $args['login_style'], $defaults ) : $defaults;

				$svg = self::get_logo_svg();
				$svg = $svg ? sprintf( '; background-image: url("%s");', $svg ) : '';

				printf(
					'<style type="text/css">body.login div#login h1 a { %s; }</style>',
					self::implode( $login_style ) . $svg
				);
			}
		}

		/**
		 * Determines whether current page is the login page.
		 *
		 * @since 1.0.0
		 */
		public static function is_login_page() {
			return in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php', ) );
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
		 * @param $ext
		 *
		 * @return bool|string
		 */
		protected static function get_logo( $ext ) {
			// Check Site Identity
			if ( $url = wp_get_attachment_url( get_theme_mod( 'custom_logo' ) ) ) {
				return $url;
			}

			// Check if loaded in options
			if ( function_exists( 'get_field' ) ) {
				$logo = get_field( 'logo', 'options' );
				if ( '' !== $logo ) {
					return $logo['url'];
				}
			}

			// Check uploads
			$upload_dir = wp_upload_dir();
			if ( file_exists( $upload_dir['path'] . '/logo.' . $ext ) ) {
				return $upload_dir['url'] . '/logo.' . $ext;
			}

			// Check Theme
			if ( file_exists( get_stylesheet_directory() . '/logo.' . $ext ) ) {
				return get_stylesheet_directory_uri() . '/logo.' . $ext;
			}

			// Check Theme images folder
			if ( file_exists( get_stylesheet_directory() . '/images/logo.' . $ext ) ) {
				return get_stylesheet_directory_uri() . '/images/logo.' . $ext;
			}

			return false;
		}

		/**
		 * Gets logo.svg.
		 *
		 * @return bool|string
		 */
		public static function get_logo_svg() {
			return self::get_logo( 'svg' );
		}

		/**
		 * Determines whether a SVG or PNG logo exists.
		 *
		 * @return bool|string
		 */
		public static function logo_exists() {
			return ( self::get_logo( 'svg' ) || self::get_logo( 'png' ) );
		}

		/**
		 * Gets logo.png.
		 *
		 * @return bool|string
		 */
		public static function get_logo_png() {
			return self::get_logo( 'png' );
		}

	}
}
