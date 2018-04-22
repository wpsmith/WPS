<?php

namespace WPS;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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

function remove_post_type_meta() {
	remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
}

/**
 * Gets description to category / tag / taxonomy archive pages.
 *
 * If we're not on an author archive page, then nothing is returned.
 *
 * @global WP_Query $wp_query Query object.
 *
 * @return string Taxonomy Description.
 */
function get_taxonomy_description() {

	global $wp_query;

	if ( ! is_category() && ! is_tag() && ! is_tax() ) {
		return '';
	}

	$term = is_tax() ? get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ) : $wp_query->get_queried_object();

	if ( ! $term ) {
		return '';
	}

	$intro_text = get_term_meta( $term->term_id, 'intro_text', true );

	return apply_filters( 'genesis_term_intro_text_output', $intro_text ? $intro_text : '' );

}

/**
 * Gets description to author archive pages.
 *
 * If we're not on an author archive page, then nothing is returned.
 *
 * @return string Author description.
 */
function get_author_description() {

	if ( ! is_author() ) {
		return '';
	}

	$intro_text = get_the_author_meta( 'intro_text', (int) get_query_var( 'author' ) );

	return apply_filters( 'genesis_author_intro_text_output', $intro_text ? $intro_text : '' );

}

/**
 * Gets description to relevant custom post type archive pages.
 *
 * If we're not on a post type archive page or post type does not have
 * `genesis-cpt-archives-settings` support, then nothing extra is displayed.
 *
 * @return string Gets post type description.
 */
function get_cpt_archive_description() {

	if ( ! is_post_type_archive() || ! genesis_has_post_type_archive_support() ) {
		return '';
	}

	$intro_text = genesis_get_cpt_option( 'intro_text' );

	return apply_filters( 'genesis_cpt_archive_intro_text_output', $intro_text ? $intro_text : '' );

}

/**
 * Add custom heading and description to blog template pages.
 *
 * If we're not on a blog template page, then nothing extra is displayed.
 *
 * @return string Blog template description.
 */
function get_blog_template_description() {

	if (
		! is_page_template( 'page_blog.php' )
		|| get_queried_object_id() == get_option( 'page_for_posts' )
	) {
		return '';
	}

	return get_the_content();

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

function is_debug() {
	return ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) );
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

function is_external_host( $host ) {
	return ( $host !== $_SERVER['SERVER_NAME'] );
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

function require_plugin( $plugin, $version ) {
	require_once( 'core/plugins/' . $plugin );

	return new Core\Extend_Plugin( $plugin, __FILE__, $version, WPSCORE_PLUGIN_DOMAIN, '/../mu-plugins/core/plugins' );
}

/**
 * Column Classes
 *
 * @param int $columns , how many columns content should be broken into
 * @param int $count , the current post in the loop (starts at 0)
 * @param int $extra_classes , any additional classes to add on all posts
 *
 * @return string $classes
 *
 * @author Bill Erickson
 * @link http://www.billerickson.net/code/get-column-classes/
 */
function get_column_classes( $columns = 2, $count = 0, $extra_classes = '' ) {
	$column_classes = array( '', 'full-width', 'one-half', 'one-third', 'one-fourth', 'one-fifth', 'one-sixth' );
//	$column_classes = array( '', '', 'one-half', 'one-third', 'one-fourth', 'one-fifth', 'one-sixth' );
	$output         = $column_classes[ $columns ];
	if ( 0 == $count || 0 == $count % $columns ) {
		$output .= ' first';
	}
	if ( $extra_classes ) {
		$output .= ' ' . $extra_classes;
	}

	return $output;
}


/**
 * Column Classes
 *
 * @param int $columns , how many columns content should be broken into
 * @param int $count , the current post in the loop (starts at 0)
 * @param int $extra_classes , any additional classes to add on all posts
 *
 * @return string $classes
 */
function get_column_classes_by_column_class( $column_class = '', $count = 0, $extra_classes = '' ) {
	$column_classes = array(
		'',
		'',
		'one-half',
		'one-third',
		'one-fourth',
		'one-fifth',
		'one-sixth',
	);


	$output  = $column_class;
	$columns = array_search( $column_class, $column_classes );
	if ( 0 == $count || 0 == $count % $columns ) {
		$output .= ' first';
	}
	if ( $extra_classes ) {
		$output .= ' ' . $extra_classes;
	}

	return $output;
}

/**
 * Get size information for all currently-registered image sizes.
 *
 * @global $_wp_additional_image_sizes
 * @uses   get_intermediate_image_sizes()
 * @return array $sizes Data for all currently-registered image sizes.
 */
function get_image_sizes() {
	global $_wp_additional_image_sizes;

	$sizes = array();

	foreach ( get_intermediate_image_sizes() as $_size ) {
		if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
			$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
			$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
			$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
			$sizes[ $_size ] = array(
				'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
			);
		}
	}

	return $sizes;
}

/**
 * Get size information for a specific image size.
 *
 * @uses   get_image_sizes()
 * @param  string $size The image size for which to retrieve data.
 * @return bool|array $size Size data about an image size or false if the size doesn't exist.
 */
function get_image_size( $size ) {
	$sizes = get_image_sizes();

	if ( isset( $sizes[ $size ] ) ) {
		return $sizes[ $size ];
	}

	return false;
}

/**
 * Get the width of a specific image size.
 *
 * @uses   get_image_size()
 * @param  string $size The image size for which to retrieve data.
 * @return bool|string $size Width of an image size or false if the size doesn't exist.
 */
function get_image_width( $size ) {
	if ( ! $size = get_image_size( $size ) ) {
		return false;
	}

	if ( isset( $size['width'] ) ) {
		return $size['width'];
	}

	return false;
}

/**
 * Get the height of a specific image size.
 *
 * @uses   get_image_size()
 * @param  string $size The image size for which to retrieve data.
 * @return bool|string $size Height of an image size or false if the size doesn't exist.
 */
function get_image_height( $size ) {
	if ( ! $size = get_image_size( $size ) ) {
		return false;
	}

	if ( isset( $size['height'] ) ) {
		return $size['height'];
	}

	return false;
}