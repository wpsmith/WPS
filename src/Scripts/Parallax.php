<?php


namespace WPS\Scripts;

class Parallax extends Script {

	public $handle = 'parallax';

	/**
	 * Parallax constructor.
	 *
	 * @param array $args
	 */
	protected function __construct( $args = array() ) {
		$suffix = self::get_suffix();
		$args = wp_parse_args( array(
			'deps'   => array( 'jquery', ),
		) , $this->get_defaults( "/core/assets/js/jquery.parallax{$suffix}.js" ) );

		parent::__construct( $args );
	}

	protected function conditional() {
		return apply_filters( 'wps_parallax_conditional', !is_admin() );
	}
}