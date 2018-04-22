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

if ( ! class_exists( 'Entry_Schema' ) ) {
	/**
	 * Core Plugin class.
	 *
	 * The class handles the version, slug, and instance of all the
	 * classes that extend it.
	 *
	 * @package WPS_Core
	 * @author  Travis Smith <t@wpsmith.net>
	 */
	class Entry_Schema extends Schema_Core {

		public function init() {

			// This assumes the the context is the post type
			if ( isset( $this->attributes['empty'] ) ) {
				unset( $this->attributes['empty'] );
				add_filter( 'genesis_attr_entry', array( $this, 'remove' ), 11 );
			}

			
			add_filter( 'genesis_attr_entry', array( $this, 'schema' ), 20, 2 );
			add_filter( 'genesis_attr_entry-author-name', array( $this, 'author_name' ), 20, 2 );

			switch( $this->schema ) {
				case 'location':
					add_filter( 'genesis_attr_entry-address', array( $this, 'schema' ), 20, 2 );
					add_filter( 'genesis_attr_entry-street', array( $this, 'schema' ), 20, 2 );
					add_filter( 'genesis_attr_entry-city', array( $this, 'schema' ), 20, 2 );
					add_filter( 'genesis_attr_entry-state', array( $this, 'schema' ), 20, 2 );
					add_filter( 'genesis_attr_entry-country', array( $this, 'schema' ), 20, 2 );
					break;
			}

			// Fixes, @link
			if ( post_type_supports( $this->post_type, 'title' ) && post_type_supports( $this->post_type, 'author' ) ) {
				add_filter( 'genesis_post_title_output', array( $this, 'title_link' ), 20 );
			}
		}

	}
}


