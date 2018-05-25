<?php
/**
 * Schema File
 *
 * Assist in setting up cron jobs within WordPress.
 *
 * You may copy, distribute and modify the software as long as you track changes/dates in source files.
 * Any modifications to or software including (via compiler) GPL-licensed code must also be made
 * available under the GPL along with build & install instructions.
 *
 * @package    WPS\Schema
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link       https://github.com/wpsmith/WPS
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\Schema;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPS\Schema\Entry_Schema' ) ) {
	/**
	 * Class Entry_Schema
	 *
	 * @package WPS\Schema
	 */
	class Entry_Schema extends Schema_Core {

		/**
		 * Initializes class.
		 */
		public function init() {

			// This assumes the the context is the post type.
			if ( isset( $this->attributes['empty'] ) ) {
				unset( $this->attributes['empty'] );
				add_filter( 'genesis_attr_entry', array( $this, 'remove' ), 11 );
			}

			add_filter( 'genesis_attr_entry', array( $this, 'schema' ), 20, 2 );
			add_filter( 'genesis_attr_entry-author-name', array( $this, 'author_name' ), 20, 2 );

			switch ( $this->schema ) {
				case 'location':
					add_filter( 'genesis_attr_entry-address', array( $this, 'schema' ), 20, 2 );
					add_filter( 'genesis_attr_entry-street', array( $this, 'schema' ), 20, 2 );
					add_filter( 'genesis_attr_entry-city', array( $this, 'schema' ), 20, 2 );
					add_filter( 'genesis_attr_entry-state', array( $this, 'schema' ), 20, 2 );
					add_filter( 'genesis_attr_entry-country', array( $this, 'schema' ), 20, 2 );
					break;
			}

			// Fixes, @link.
			if ( post_type_supports( $this->post_type, 'title' ) && post_type_supports( $this->post_type, 'author' ) ) {
				add_filter( 'genesis_post_title_output', array( $this, 'title_link' ), 20 );
			}
		}

	}
}


