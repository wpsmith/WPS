<?php

namespace WPS;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine if a specified template is being used.
 *
 * `is_page_template()` is not available within the loop or any loop that
 * modifies $wp_query because it changes all the conditionals therein.
 * Since the conditionals change, is_page() no longer returns true, thus
 * is_page_template() will always return false.
 *
 * @link http://codex.wordpress.org/Function_Reference/is_page_template#Cannot_Be_Used_Inside_The_Loop
 *
 * @return bool True if Blog template is being used, false otherwise.
 */
function is_page_template( $template ) {

	global $wp_the_query;

	return $template === get_post_meta( $wp_the_query->get_queried_object_id(), '_wp_page_template', true );

}

/**
 * Determines whether a particular page template is active.
 *
 * @param string $pagetemplate Page template.
 *
 * @return bool Whether page template is active anywhere.
 */
function is_pagetemplate_active( $pagetemplate ) {
	global $wpdb;
	$sql    = "select meta_key from $wpdb->postmeta where meta_key like '_wp_page_template' and meta_value like '" . $pagetemplate . "'";
	$result = $wpdb->query( $sql );
	if ( $result ) {
		return true;
	} else {
		return false;
	}
}

//add_action( 'init', 'is_first_time' );
function is_first_time() {
	$cookie = '_wp_' . sanitize_title_with_dashes( get_bloginfo( 'name' ) ) . 'firsttime';
	if ( isset( $_COOKIE[ $cookie ] ) || is_user_logged_in() ) {
		return false;
	} else {
		// expires in 30 days.
		setcookie( $cookie, 1, time() + ( WEEK_IN_SECONDS * 4 ), COOKIEPATH, COOKIE_DOMAIN, false );

		return true;
	}
}

function is_external_host( $host ) {
	return ( $host !== $_SERVER['SERVER_NAME'] );
}

function is_debug() {
	return ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) );
}

function is_cron_disabled() {
	return ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON );
}

function is_doing_cron() {
	return (
		defined( 'DOING_CRON' ) && DOING_CRON ||
		isset( $_GET['doing_wp_cron'] )
	);
}

function has_3_column_layout() {

	$layouts = genesis_get_layouts();

	$three_column_layouts = array(
		'content-sidebar-sidebar',
		'sidebar-content-sidebar',
		'sidebar-sidebar-content',
	);

	foreach ( $three_column_layouts as $layout ) {
		if ( array_key_exists( $layout, $layouts ) ) {
			return true;
		}
	}

	return false;

}