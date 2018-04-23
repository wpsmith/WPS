<?php

namespace WPS;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
