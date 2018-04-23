<?php
/**
 * WPS Core Shortcode Abstract Class
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


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'Shortcode' ) ) {
	/**
	 * Shortcode Abstract Class
	 *
	 * Assists in creating Shortcodes.
	 *
	 * @package WPS\Core
	 * @author Travis Smith <t@wpsmith.net>
	 */
	abstract class Shortcode extends Singleton {

		public $name;
		public $is_active = false;

		protected function __construct() {
			add_shortcode( $this->name, array( $this, 'shortcode' ) );
			add_filter( 'nav_menu_link_attributes', array( $this, 'nav_menu_link_attributes' ), 10, 4 );
			add_filter( 'nav_menu_item_title', array( $this, 'nav_menu_item_title' ), 99, 4 );
		}

		protected function get_atts( $atts ) {
			return shortcode_atts( $this->get_defaults(), $atts );
		}

		public function nav_menu_item_title( $atts, $item, $args, $depth ) {
			if ( has_shortcode( $atts, $this->name ) ) {
				$this->is_active = true;
				$v               = do_shortcode( $atts );

				return $v;
			}

			return $atts;
		}

		public function nav_menu_link_attributes( $atts, $item, $args, $depth ) {
			if ( isset( $atts['href'] ) ) {
				foreach ( $atts as $key => $att ) {
					$att = urldecode( $att );
					if ( has_shortcode( $att, $this->name ) ) {
						$this->is_active = true;
						$atts[ $key ]    = do_shortcode( $att );
					}
				}
			}

			return $atts;
		}

		protected function get_defaults() {
			return array();
		}

		abstract public function shortcode( $atts, $content = null );

		public function is_active( $post_id = null ) {
			if ( ! $post_id ) {
				$post_id = get_the_ID();
			}

			$post = get_post( $post_id );

			return ( has_shortcode( $post->post_content, $this->name ) );
		}
	}
}