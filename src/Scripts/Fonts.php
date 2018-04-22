<?php


namespace WPS\Scripts;

class Fonts extends Script {

	public $handle = 'fonts';

	protected function __construct( $args = array() ) {
		$suffix = self::get_suffix();

		// @todo check to see if fonts.css file exists.
//		$fonts_url = isset( $args['url'] ) && $args['url'] ? $args['url'] :  sprintf( '%s/css/fonts%s.css', get_stylesheet_directory_uri(), $suffix );

		$args = wp_parse_args( array(
			'inline' => '(function(wps, u){var WPSChildFont=new wps.FontCache({url:u,id: "wps-font-loader"});})(WPS, wpsChild.fontsUrl);',
		) , $this->get_defaults( "/core/assets/js/fonts{$suffix}.js" ) );

		parent::__construct( $args );
	}

	protected function conditional() {
		return apply_filters( 'wps_fonts_conditional', ! is_admin() );
	}
}