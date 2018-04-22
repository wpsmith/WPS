<?php
/**
 * Created by PhpStorm.
 * User: travis.smith
 * Date: 1/7/18
 * Time: 2:30 PM
 */

namespace WPS\Scripts;


class Smooth_Scroll extends Script {

	public $handle = 'zenscroll';

	/**
	 * Smooth_Scroll constructor.
	 *
	 * @param array $args
	 */
	protected function __construct( $args = array() ) {
		$suffix = self::get_suffix();
		$args = wp_parse_args( array(
			'inline' => '(function (zs){zs.setup(500, 90);})(zenscroll);' . $this->get_inline(),
		) , $this->get_defaults( "/core/assets/js/zenscroll{$suffix}.js" ) );

		parent::__construct( $args );
	}

	protected function conditional() {
		return apply_filters( 'wps_smooth_scroll_conditional', !is_admin() );
	}

	protected function get_inline() {
		return '(function($,zs){setTimeout(function(){if(location.hash){window.scrollTo(0,0);target=location.hash.split("#");smoothScrollTo($("#"+target[1]))}},1);$("a[href*=#]:not([href=#])").click(function(){if(location.pathname.replace(/^\//,"")==this.pathname.replace(/^\//,"")&&location.hostname==this.hostname){smoothScrollTo($(this.hash));return false}});function smoothScrollTo(target){var $target=target.length?target:$("[name="+this.hash.slice(1)+"]");if(target.length){zs.to($target.get(0))}}$(document).ready(function(){if($(".site-title a").attr("href")===window.location.href){$(".site-title a").attr("href","#")}$(".menu-item a").each(function(i,elem){var $elem=$(elem);if($elem.attr("href")===window.location.href){$elem.attr("href","#")}})})})(jQuery,zenscroll);';
	}
}