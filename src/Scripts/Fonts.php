<?php
/**
 * Fonts Script Class File
 *
 * Adds Fonts support.
 *
 * You may copy, distribute and modify the software as long as you track changes/dates in source files.
 * Any modifications to or software including (via compiler) GPL-licensed code must also be made
 * available under the GPL along with build & install instructions.
 *
 * @package    WPS\Scripts
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link       https://github.com/wpsmith/WPS
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\Scripts;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPS\Scripts\Fonts' ) ) {
	class Fonts extends Script {

		/**
		 * Script Handle.
		 *
		 * @var string
		 */
		public $handle = 'fonts';

		/**
		 * Fonts constructor.
		 *
		 * @param array $args Array of script args.
		 */
		protected function __construct( $args = array() ) {
			$suffix = self::get_suffix();

			// @todo check to see if fonts.css file exists.
			// $fonts_url = isset( $args['url'] ) && $args['url'] ? $args['url'] :  sprintf( '%s/css/fonts%s.css', get_stylesheet_directory_uri(), $suffix );.

			$args = wp_parse_args( array(
				'inline' => '(function(wps, u){var WPSChildFont=new wps.FontCache({url:u,id: "wps-font-loader"});})(WPS, wpsChild.fontsUrl);',
			), $this->get_defaults( "/core/assets/js/fonts{$suffix}.js" ) );

			parent::__construct( $args );
		}
	}
}
