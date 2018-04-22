<?php
/**
 * Created by PhpStorm.
 * User: travis.smith
 * Date: 1/7/18
 * Time: 2:30 PM
 */

namespace WPS\Scripts;


class Match_Height extends Script {

	public $handle = 'match-height';

	/**
	 * Match_Height constructor.
	 *
	 * @param array $args
	 */
	protected function __construct( $args = array() ) {
		$suffix = self::get_suffix();
		$args   = wp_parse_args( array(
			'handler' => 'match-height',
			'deps'    => array( 'jquery', ),
			'inline'  => ''
		), $this->get_defaults( "/core/assets/js/jquery.match-height{$suffix}.js" ) );

		parent::__construct( $args );
	}

	protected function conditional() {
		return apply_filters( 'wps_match_height_conditional', ! is_admin() );
	}
}