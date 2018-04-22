<?php
/**
 * Created by PhpStorm.
 * User: travis.smith
 * Date: 1/7/18
 * Time: 2:30 PM
 */

namespace WPS\Scripts;


class Responsive_Menu extends Script {

	public $handle = 'responsive-menu';

	/**
	 * Responsive_Menu constructor.
	 *
	 * @param array $args
	 */
	protected function __construct( $args = array() ) {
		$suffix = self::get_suffix();
		$args = wp_parse_args( array(
			'deps'   => array( 'jquery', ),
		) , $this->get_defaults( "/core/assets/js/jquery.responsive-menus{$suffix}.js" ) );

		parent::__construct( $args );
	}

	protected function conditional() {
		return apply_filters( 'wps_responsive_menu_conditional', !is_admin() );
	}
}