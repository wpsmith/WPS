<?php
/**
 * Schema Core Abstract Class
 *
 * Base for dealing with schemas in Genesis.
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

if ( ! class_exists( 'WPS\Schema\Schema_Core' ) ) {
	/**
	 * Class Schema_Core.
	 *
	 * @package WPS\Schema
	 */
	abstract class Schema_Core {

		/**
		 * Post Type.
		 *
		 * @var string
		 */
		public $post_type;

		/**
		 * Schema.
		 *
		 * @var string
		 */
		public $schema;

		/**
		 * Schema Attributes.
		 *
		 * @var array
		 */
		public $attributes = array();

		/**
		 * Schema_Core constructor.
		 *
		 * @param string $post_type  Post Type.
		 * @param string $schema     Schema name.
		 * @param array  $attributes Schema attributes.
		 */
		public function __construct( $post_type, $schema = '', $attributes = array() ) {
			$schema       = '' === $schema ? $post_type : $schema;
			$this->schema = $schema;

			// Store Post Type.
			$this->post_type = $post_type;

			// Save Schema.
			$s = $this->get_schema( $schema );
			if ( ! empty( $s ) ) {
				$this->attributes = wp_parse_args( $attributes, $s );
			} else {
				$this->attributes = $attributes;
			}

			if ( method_exists( $this, 'init' ) ) {
				add_action( 'wp_loaded', array( $this, 'init' ) );
			}

		}

		/**
		 * Gets schemas.
		 *
		 * @param string $schema Schema.
		 *
		 * @return array Array of schemas.
		 */
		protected function get_schema( $schema ) {

			$schemas = Schemas::get_instance();

			return $schemas->get_schema( $schema );

		}

		/**
		 * Gets an array of genesis HTML5 entry hooks.
		 *
		 * @return array
		 */
		protected function get_genesis_attr_entry_hooks() {
			return array(
				'entry',
				'entry-title',
				'entry-content',
				'entry-author',
			);

		}

		/**
		 * Adds schema to Genesis attributes.
		 *
		 * @param string $context    Context.
		 * @param array  $attributes Array of HTML elements.
		 *
		 * @return array
		 */
		public function add_schema( $context, $attributes ) {
			if (
				! empty( $this->post_type ) &&
				(
					is_array( $this->attributes ) &&
					! empty( $this->attributes )
				)
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
		 * @param array  $attributes Existing attributes.
		 * @param string $context    Attributes context.
		 *
		 * @return array Amended attributes.
		 */
		public function schema( $attributes = array(), $context = '' ) {

			// Make sure we have the correct post type.
			if ( get_post_type() !== $this->post_type ) {
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

		/**
		 * Removes specific attributes from genesis attributes.
		 *
		 * @param array $attributes Array of Genesis attributes.
		 *
		 * @return array
		 */
		public static function remove( $attributes ) {
			foreach (
				array(
					'itemtype',
					'itemprop',
					'itemscope',
				) as $attr
			) {
				self::remove_attr( $attr, $attributes );
			}

			return $attributes;
		}

		/**
		 * Removes specific key from array.
		 *
		 * @param string $attribute  Attribute to be removed.
		 * @param array  $attributes Array of attributes.
		 */
		public static function remove_attr( $attribute, $attributes ) {
			if ( isset( $attributes[ $attribute ] ) ) {
				$attributes[ $attribute ] = '';
			}
		}

		/**
		 * Removes author name from attributes.
		 *
		 * @param array  $attributes Array of Genesis attributes.
		 * @param string $context    Attributes context.
		 *
		 * @return mixed
		 */
		public function remove_author_name( $attributes, $context ) {
			self::remove_attr( 'name', $attributes );

			return $attributes;
		}

		/**
		 * Customizes Title Link.
		 *
		 * @param string $output Title HTML link tag.
		 *
		 * @return string
		 */
		public static function title_link( $output ) {
			return str_replace( 'rel="author"', 'itemprop="author"', $output );
		}

		/**
		 * Remove the rel="author" and change it to itemprop="author" as the Structured Data Testing Tool doesn't understand
		 * rel="author" in relation to Schema, even though it should according to the spec.
		 *
		 * @param string $output Output HTML.
		 *
		 * @return mixed
		 */
		public static function author( $output ) {
			return str_replace( 'rel="bookmark"', 'rel="bookmark" itemprop="itemprop"', $output );
		}
	}
}


