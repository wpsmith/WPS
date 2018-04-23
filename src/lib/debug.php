<?php

namespace WPS;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function write_log( $log, $title = '' ) {
	if ( $title ) {
		$title .= ': ';
	}
	if ( is_array( $log ) || is_object( $log ) ) {
		error_log( $title . print_r( $log, true ) );
	} else {
		error_log( $title . $log );
	}
}

function printr( $args, $name = '' ) {
	if ( apply_filters( 'debug_off', false ) ) {
		return;
	}
	if ( is_string( $name ) && '' != $name ) {
		echo '<strong>' . $name . '</strong><br/>';
	}
	echo '<pre>', htmlspecialchars( print_r( $args, true ) ), "</pre>\n";
}

function wps_die( $args, $name = '' ) {

	printr( $args, $name );
	wp_die();

}
