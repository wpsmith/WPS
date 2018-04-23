<?php

namespace WPS;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function get_relative_path( $path ) {
	return str_replace( ABSPATH, '/', realpath( $path ) );
}

function get_relative_url( $url ) {
	$parsed_url = parse_url( $url );
	$site_url   = site_url();

//	write_log( $parsed_url['host'], 'parsed host' );
//	write_log( $site_url, '$site_url' );
//	write_log( strpos( $site_url, $parsed_url['host'] ), 'strpos( $site_url, $parsed_url[host] )' );
//	write_log( str_replace( $site_url, '/', $url ), 'str_replace( /, $url )' );

	if ( false !== strpos( $site_url, $parsed_url['host'] ) ) {
		return str_replace( trailingslashit( $site_url ), '/', $url );
	}

//	write_log( str_replace( $parsed_url['host'] , '/', $url ), 'str_replace( $parsed_url[host] , /, $url )' );

	return str_replace( $parsed_url['host'], '/', $url );
}

function each( $arr, $fn, $type ) {
	foreach ( $arr as $item ) {
		if ( is_callable( $fn ) ) {
			call_user_func_array( $fn, array( $item, $type ) );
		}
	}
}

function get_script_style_dependency( $type, $handle ) {
	if ( 'style' === $type ) {
		return ( isset( wp_styles()->registered[ $handle ] ) ? wp_styles()->registered[ $handle ] : new \stdClass() );
	}

	return ( isset( wp_scripts()->registered[ $handle ] ) ? wp_scripts()->registered[ $handle ] : new \stdClass() );
}

function remove_post_type_meta() {
	remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
}