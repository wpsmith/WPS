<?php
/**
 * WPS Core Post Type Class
 *
 * @package    WPS\Core
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 WP Smith, Travis Smith
 * @link       https://github.com/wpsmith/WPS/
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\Core;

use WPS;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Post_Type' ) ) {
	/**
	 * Post Type Abstract Class
	 *
	 * Assists in creating and managing Post Types.
	 *
	 * @package WPS\Core
	 * @author Travis Smith <t@wpsmith.net>
	 */
	abstract class Post_Type extends Singleton {

		/**
		 * Post Type registered name
		 *
		 * @var string
		 */
		public $post_type;

		/**
		 * Whether to add envira gallery after the entry content.
		 *
		 * @var bool
		 */
		public $gallery;

		/**
		 * What metaboxes to remove.
		 *
		 * Supports 'genesis-cpt-archives-layout-settings', 'genesis-cpt-archives-seo-settings',
		 * and 'genesis-cpt-archives-settings'.
		 *
		 * @var array
		 */
		public $remove_metaboxes = array();

		/**
		 * Whether to remove meta functions from post type display.
		 *
		 * @var bool
		 */
		public $remove_post_type_meta = false;

		/**
		 * Post_Type constructor.
		 */
		protected function __construct() {
			add_action( 'init', array( $this, 'create_post_type' ), 0 );

			if ( $this->remove_post_type_meta ) {
				add_action( 'genesis_header', array( $this, 'remove_post_type_meta' ) );
			}

			if ( method_exists( $this, 'init' ) ) {
				$this->init();
			}

			if ( method_exists( $this, 'create_taxonomy' ) ) {
				add_action( 'init', array( $this, 'create_taxonomy' ), 0 );
			}

			if ( method_exists( $this, 'core_acf_fields' ) ) {
				add_action( 'core_acf_fields', array( $this, 'core_acf_fields' ) );
			}

			if ( method_exists( $this, 'manage_posts_columns' ) ) {
				add_filter( "manage_{$this->post_type}_posts_columns", array( $this, 'manage_posts_columns' ) );
			}

			if ( method_exists( $this, 'manage_posts_custom_column' ) ) {
				add_action( "manage_{$this->post_type}_posts_custom_column", array(
					$this,
					'manage_posts_custom_column'
				), 10, 2 );
			}

			if ( $this->gallery ) {
				add_filter( 'envira_gallery_pre_data', array( $this, 'envira_gallery_pre_data' ), 10, 2 );
				add_action( 'genesis_entry_content', array( $this, 'envira_gallery' ), 15 );
			}


			foreach ( $this->remove_metaboxes as $metabox ) {
				switch ( $metabox ) {
					case 'layout':
					case 'genesis-cpt-archives-layout-settings':
						add_action( 'genesis_cpt_archives_settings_metaboxes', array(
							$this,
							'genesis_remove_cpt_archives_layout_settings_metaboxes'
						) );
						break;
					case 'seo':
					case 'genesis-cpt-archives-seo-settings':
						add_action( 'genesis_cpt_archives_settings_metaboxes', array(
							$this,
							'genesis_remove_cpt_archives_seo_settings_metaboxes'
						) );
						break;
					case 'settings':
					case 'genesis-cpt-archives-settings':
						add_action( 'genesis_cpt_archives_settings_metaboxes', array(
							$this,
							'genesis_remove_cpt_archives_settings_metaboxes'
						) );
						break;
				}
			}
		}

		/**
		 * Removes Genesis Layouts Metabox
		 * @param string $pagehook Page hook for the CPT archive settings page.
		 */
		public function genesis_remove_cpt_archives_layout_settings_metaboxes( $pagehook ) {
			remove_meta_box( 'genesis-cpt-archives-layout-settings', $pagehook, 'main' );
		}

		/**
		 * Removes Genesis SEO Settings Metabox.
		 *
		 * @param string $pagehook Page hook for the CPT archive settings page.
		 */
		public function genesis_remove_cpt_archives_seo_settings_metaboxes( $pagehook ) {
			remove_meta_box( 'genesis-cpt-archives-seo-settings', $pagehook, 'main' );
		}

		/**
		 * Removes Genesis CPT Archives Metabox
		 * @param string $pagehook Page hook for the CPT archive settings page.
		 */
		public function genesis_remove_cpt_archives_settings_metaboxes( $pagehook ) {
			remove_meta_box( 'genesis-cpt-archives-settings', $pagehook, 'main' );
		}

		/**
		 * Register custom post type
		 */
		abstract public function create_post_type();

		/**
		 * Remove Genesis Meta functions from Post Type.
		 */
		public function remove_post_type_meta() {
			if ( $this->post_type === get_post_type() ) {
				WPS\remove_post_type_meta();
			}
		}

		/*
		 * Add the gallery after the end of the content
		 */
		public function envira_gallery() {
			if ( $this->post_type !== get_post_type() ) {
				return;
			}

			$gallery = get_post_meta( get_the_ID(), 'gallery' );

			// If we have something output the gallery
			if ( is_array( $gallery[0] ) ) {
				if ( shortcode_exists( 'envira-gallery' ) ) {
					echo do_shortcode( '[envira-gallery slug="envira-dynamic-gallery"]' );
				} else { // Fall back to WordPress inbuilt gallery
					$image_ids = $gallery[0];
					$shortcode = '[gallery ids="' . implode( ',', $image_ids ) . '" envira="true"]';
					echo do_shortcode( $shortcode );
				}
			} else {
				$gallery = get_post_meta( get_the_ID(), 'related_gallery', true );
				if ( shortcode_exists( 'envira-gallery' ) ) {
					echo do_shortcode( sprintf( '[envira-gallery id="%s"]', $gallery ) );
				}
			}

		}

		/*
		 * Filter the envira gallery $data and replace with the image data for our images in the ACF gallery field
		 */
		public function envira_gallery_pre_data( $data, $gallery_id ) {

			if ( $this->post_type !== get_post_type() || ( $data['config']['type'] != 'fc' ) ) {
				return $data;
			}

			$newdata = array();

			// Don't lose the original gallery id and configuration
			$newdata["id"]     = $data["id"];
			$newdata["config"] = $data["config"];

			// Get list of images from our ACF gallery field
			$gallery   = get_post_meta( get_the_ID(), 'gallery' );
			$image_ids = $gallery[0]; // It's an array within an array

			// If we have some images loop around and populate a new data array
			if ( is_array( $image_ids ) ) {

				foreach ( $image_ids as $image_id ) {

					$newdata["gallery"][ $image_id ]["status"] = 'active';
					$newdata["gallery"][ $image_id ]["src"]    = esc_url( wp_get_attachment_url( $image_id ) );
					$newdata["gallery"][ $image_id ]["title"]  = esc_html( get_the_title( $image_id ) );
					$newdata["gallery"][ $image_id ]["link"]   = esc_url( wp_get_attachment_url( $image_id ) );
					$newdata["gallery"][ $image_id ]["alt"]    = trim( strip_tags( get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ) );
					$newdata["gallery"][ $image_id ]["thumb"]  = esc_url( wp_get_attachment_thumb_url( $image_id ) );

				}
			}

			return $newdata;
		}
	}
}
