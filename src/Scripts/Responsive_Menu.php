<?php
/**
 * Responsive Menu Script Class File
 *
 * Adds Responsive Menu support.
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

if ( ! class_exists( 'WPS\Scripts\Responsive_Menu' ) ) {
	/**
	 * Class Responsive_Menu.
	 *
	 * @package WPS\Scripts
	 */
	class Responsive_Menu extends Script {

		/**
		 * Script Handle.
		 *
		 * @var string
		 */
		public $handle = 'responsive-menu';

		/**
		 * Responsive_Menu constructor.
		 *
		 * @param array $args Array of script args.
		 */
		protected function __construct( $args = array() ) {
			$suffix = self::get_suffix();
			$args   = wp_parse_args( array(
				'deps' => array( 'jquery' ),
			), $this->get_defaults( "/core/assets/js/jquery.responsive-menus{$suffix}.js" ) );

			parent::__construct( $args );
		}
	}
}
