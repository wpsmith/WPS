<?php
/**
 * Image Defaults Class File
 *
 * Site Options for Image Defaults.
 *
 * You may copy, distribute and modify the software as long as you track changes/dates in source files.
 * Any modifications to or software including (via compiler) GPL-licensed code must also be made
 * available under the GPL along with build & install instructions.
 *
 * @package    WPS\Widgets
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link       https://github.com/wpsmith/WPS
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\Site;

use StoutLogic\AcfBuilder\FieldsBuilder;
use WPS\Core\Singleton;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPS\Site\ImageDefaults' ) ) {
	/**
	 * Class ImageDefaults
	 * @package WPS\Site
	 */
	class ImageDefaults extends Singleton {

		/**
		 * Prefix
		 *
		 * @var string
		 */
		public $prefix = 'wps';

		/**
		 * ImageDefaults constructor.
		 */
		public function __construct() {
			add_action( 'acf/init', array( $this, 'options_page' ) );
			add_filter( 'genesis_pre_get_image', array( $this, 'default_featured_image' ), 10, 3 );
		}

		/**
		 * Options Page addition for Default Images
		 *
		 * @global array $wp_post_types Array of registered post types.
		 * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
		 */
		public function options_page() {
			$images = new FieldsBuilder( 'images', array(
				'title' => __( 'Image Settings', SITECORE_PLUGIN_DOMAIN ),
			) );

			global $wp_post_types;
			$post_types = array_keys( $wp_post_types );

			foreach ( $post_types as $post_type ) {
				if (
					'revision' === $post_type ||
					'nav_menu_item' === $post_type ||
					'customize_changeset' === $post_type ||
					'oembed_cache' === $post_type ||
					'user_request' === $post_type ||
					'wp_log' === $post_type ||
					'custom_css' === $post_type
				) {
					continue;
				}
				$images->addImage( "default-image-$post_type", array(
					'label' => __( 'Default Image for ' . $wp_post_types[ $post_type ]->label . ' (' . $post_type . ')', SITECORE_PLUGIN_DOMAIN ),
				) );
			}
			$images->setLocation( 'options_page', '==', $this->prefix . '-settings' );

			if ( function_exists( 'acf_add_local_field_group' ) ) {
				acf_add_local_field_group( $images->build() );
			}
		}

		/**
		 * Filters genesis_pre_get_image with default image if no thumbnail exists.
		 *
		 * @param bool     $pre  Whether to short curcuit image.
		 * @param array    $args Array of image args.
		 * @param \WP_Post $post Post object of current post.
		 *
		 * @return mixed|null|string|void
		 */
		public function default_featured_image( $pre, $args, $post ) {

			if (
				! is_a( $post, 'WP_Post' ) ||
				is_a( $post, 'WP_Post' ) && has_post_thumbnail( $post->ID ) ||
				( is_array( $args ) && isset( $args['context'] ) && 'header-image' !== $args['context'] ) ||
				! function_exists( 'get_field' )
			) {
				return $pre;
			}

			$thumbnail = get_field( "default-image-$post->post_type", 'option' );
			$size      = isset( $args['size'] ) ? $args['size'] : '';
			$url       = '' !== $size ? $thumbnail['sizes'][ $size ] : $thumbnail['url'];
			$srcset    = wp_get_attachment_image_srcset( $thumbnail['ID'], $size );

			switch ( $args['format'] ) {

				case 'html' :
					return '<img src="' . $url . '" class="attachment-' . esc_attr( $size ) . '" srcset="' . esc_attr( $srcset ) . '" alt="' . get_the_title( $post->ID ) . '" />';
					break;
				case 'url' :
					return $url;
					break;
				default :
					return $thumbnail;
					break;
			}

		}

	}
}
