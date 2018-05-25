<?php
/**
 * Smooth Scroll Script Class File
 *
 * Adds Smooth Scroll support.
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

if ( ! class_exists( 'WPS\Scripts\Smooth_Scroll' ) ) {
	/**
	 * Class Smooth_Scroll
	 *
	 * @package WPS\Scripts
	 */
	class Smooth_Scroll extends Script {

		/**
		 * Script Handle.
		 *
		 * @var string
		 */
		public $handle = 'zenscroll';

		/**
		 * Smooth_Scroll constructor.
		 *
		 * @param array $args Script args.
		 */
		protected function __construct( $args = array() ) {
			$suffix = self::get_suffix();
			$args   = wp_parse_args( array(
				'inline' => '(function (zs){zs.setup(500, 90);})(zenscroll);' . $this->get_inline(),
			), $this->get_defaults( "/core/assets/js/zenscroll{$suffix}.js" ) );

			parent::__construct( $args );
		}

		/**
		 * Inline JS.
		 *
		 * @return string
		 */
		protected function get_inline() {
			return '(function($,zs){setTimeout(function(){if(location.hash){window.scrollTo(0,0);target=location.hash.split("#");smoothScrollTo($("#"+target[1]))}},1);$("a[href*=#]:not([href=#])").click(function(){if(location.pathname.replace(/^\//,"")==this.pathname.replace(/^\//,"")&&location.hostname==this.hostname){smoothScrollTo($(this.hash));return false}});function smoothScrollTo(target){var $target=target.length?target:$("[name="+this.hash.slice(1)+"]");if(target.length){zs.to($target.get(0))}}$(document).ready(function(){if($(".site-title a").attr("href")===window.location.href){$(".site-title a").attr("href","#")}$(".menu-item a").each(function(i,elem){var $elem=$(elem);if($elem.attr("href")===window.location.href){$elem.attr("href","#")}})})})(jQuery,zenscroll);';
		}
	}
}
