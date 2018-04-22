<?php
/**
 * WP Smith Schema Class
 *
 * @package   WPS_Core
 * @author    Travis Smith <t@wpsmith.net>
 * @license   GPL-2.0+
 * @link      http://wpsmith.net
 * @copyright 2014 Travis Smith, WP Smith, LLC
 */

namespace WPS\Schema;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core Plugin class.
 *
 * The class handles the version, slug, and instance of all the
 * classes that extend it.
 *
 * @package WPS_Core
 * @author  Travis Smith <t@wpsmith.net>
 */
abstract class Schema_Core {

	public $post_type;
	public $schema;
	public $attributes = array();

	/**
	 * Constructor method
	 *
	 * @since  1.0.0
	 * @date   2014-06-05
	 * @author Travis Smith <t(at)wpsmith.net>}
	 *
	 * @param  string $type Schema context.
	 * @param  array $attributes Array of attributes to add.
	 *
	 * @access private
	 */
	public function __construct( $type, $schema = '', $attributes = array() ) {
		$this->schema = $schema = '' === $schema ? $type : $schema;

		// Store Post Type
		$this->post_type = $type;

		// Save Schema
		if ( ! empty( $this->get_schema( $schema ) ) ) {
			$this->attributes = wp_parse_args( $attributes, $this->get_schema( $schema ) );
		} else {
			$this->attributes = $attributes;
		}

		if ( method_exists( $this, 'init' ) ) {
			add_action( 'wp_loaded', array( $this, 'init' ) );
		}

	}

	protected function get_schema( $schema ) {
		return Schemas::get_instance()->get_schema( $schema );

	}

	protected function get_genesis_attr_entry_hooks() {
		return array(
//				'content',
			'entry',
			'entry-title',
			'entry-content',
			'entry-author',
		);

	}

	public function add_schema( $context, $attributes ) {
		if (
			! empty( $this->post_type ) &&
			( is_array( $this->attributes ) &&
			  ! empty( $this->attributes ) )
		) {
			$attributes = wp_parse_args( $this->attributes, $attributes );
		}

		$class = ( false !== strpos( $attributes['class'], $this->post_type ) ) ? trim( $attributes['class'] . ' ' . $this->post_type ) : $attributes['class'];

		$attributes['class'] = apply_filters( 'wps_schema_class_' . $this->post_type, $class, $attributes );

		return $attributes;
	}

	/**
	 * Add attributes for site title element.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes Existing attributes.
	 *
	 * @return array Amended attributes.
	 */
	public function schema( $attributes = array(), $context = '' ) {

		// Make sure we have the correct post type
		if ( $this->post_type !== get_post_type() ) {
			return $attributes;
		}

		switch ( $context ) {
			case 'entry':
			case 'title':
			case 'content':
			case 'author':
				return $this->add_schema( $context, $attributes );
			default:

				if ( isset( $this->attributes[ $context ] ) ) {
					return $this->add_schema( $context, $attributes );
				}

				break;
		}

		return $attributes;
	}

	public static function remove( $attributes ) {
		if ( isset( $attributes['itemtype'] ) ) {
			$attributes['itemtype'] = '';
		}
		if ( isset( $attributes['itemprop'] ) ) {
			$attributes['itemprop'] = '';
		}
		if ( isset( $attributes['itemscope'] ) ) {
			$attributes['itemscope'] = '';
		}

		return $attributes;
	}

	public function author_name( $attributes, $context ) {
		$attributes['name'] = '';

		return $attributes;
	}

	/**
	 * Customizes Title Link
	 *
	 * @param $output
	 *
	 * @return mixed
	 */
	public static function title_link( $output ) {
		return str_replace( 'rel="author"', 'itemprop="author"', $output );
	}

	/**
	 * Remove the rel="author" and change it to itemprop="author" as the Structured Data Testing Tool doesn't understand
	 * rel="author" in relation to Schema, even though it should according to the spec.
	 *
	 * @param $output
	 *
	 * @return mixed
	 */
	public static function author( $output ) {
		return str_replace( 'rel="bookmark"', 'rel="bookmark" itemprop="itemprop"', $output );
	}
}


