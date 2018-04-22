<?php

/**
 * WPS HTTP Server Push
 *
 * @since 0.0.6
 *
 * @package   WPS_Core
 * @author    Travis Smith <t@wpsmith.net>
 * @license   GPL-2.0+
 * @link      http://wpsmith.net
 * @copyright 2014 Travis Smith, WP Smith, LLC
 */

namespace WPS\HTTP2;

use WPS\Core;
use WPS;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Server_Push' ) ) {
	/**
	 * Class Server_Push
	 * @package WPS\HTTP2
	 */
	class Server_Push extends Core\Singleton {

		/**
		 * Max header size.
		 */
		const HTTP2_MAX_HEADER_SIZE = 1024 * 4;

		/**
		 * Accumulative header size.
		 * @var int
		 */
		public $header_size = 0;

		/**
		 * Array of source.
		 *
		 * @var array
		 */
		public $srcs = array(
			'script' => array(),
			'style'  => array(),
			'font'   => array(),
			'image'  => array(),
			'media'  => array(),
		);
		public $headers = array();
		public $hints = array();

		public $internal_url = array();
		public $resource_hint_urls = array();

		/**
		 * Server_Push constructor.
		 */
		public function __construct( $hints = array() ) {
			if ( is_admin() || ! is_ssl() ) {
				return;
			}

			$this->internal_url = parse_url( get_bloginfo( 'url' ) );

//			add_filter( 'send_headers', array( $this, 'send_headers' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'prepare_headers' ), 9998 );
			add_action( 'wp_enqueue_scripts', array( $this, 'send_headers' ), 9999 );

			$this->hints = $hints;
			add_action( 'wp_head', array( $this, 'resource_hints' ), 99, 1 );
			add_filter( 'wp_resource_hints', array( $this, 'required_resource_hints' ), 1, 2 );

		}

		/**
		 * Fires once the requested HTTP headers for caching, content type, etc. have been sent.
		 *
		 * @param WP $wp Current WordPress environment instance (passed by reference).
		 */
		public function send_headers( $wp ) {
			header( 'X-WP-Push: 1' );

			foreach ( array_unique( $this->headers ) as $header ) {
				header( $header, false );
			}

		}

		/**
		 * Fires once the requested HTTP headers for caching, content type, etc. have been sent.
		 *
		 * @param WP $wp Current WordPress environment instance (passed by reference).
		 */
		public function prepare_headers( $wp ) {
			// Get all Loaded Scripts (JS)
			$this->each( wp_styles()->queue, array( $this, 'do_script_style' ), 'script' );

			// Get all Loaded Styles (CSS)
			$this->each( wp_styles()->queue, array( $this, 'do_script_style' ), 'style' );

		}

		/**
		 * Gets the script/style item
		 *
		 * @param string $type Type of resource: font, image, style, script
		 * @param string $handle Script/Style handle.
		 *
		 * @return \stdClass WP_Script/WP_Style objects.
		 */
		private function get_item( $type, $handle ) {

			if ( 'style' === $type ) {
				return ( isset( wp_styles()->registered[ $handle ] ) ? wp_styles()->registered[ $handle ] : new \stdClass() );
			}

			return ( isset( wp_scripts()->registered[ $handle ] ) ? wp_scripts()->registered[ $handle ] : new \stdClass() );
		}

		/**
		 * Does the header for the script/style.
		 *
		 * @param string $handle Handle of the script/style.
		 * @param string $type Type of item (e.g., script, style).
		 */
		public function do_script_style( $handle, $type ) {
			$item = $this->get_item( $type, $handle );

			if ( is_a( $item, 'stdClass' ) && ( ! isset( $item->src ) ) || ( isset( $item->src ) && $item->src != '' && ! $this->is_internal_url( $item->src ) ) ) {
				return;
			}

			if ( ! empty( $item->ver ) ) {
				$item->src = add_query_arg( 'ver', $item->ver, $item->src );
			}
			$this->do_header( set_url_scheme( $item->src, 'relative' ), $type );
			if ( ! empty( $item->deps ) ) {
				$this->each( $item->deps, array( $this, 'do_script_style' ), $type );
			}
		}

		/**
		 * Check if a URL is an internal URL or not.
		 *
		 * @param string $url URL being checked.
		 *
		 * @return bool Whether the URL's host is the same as the site host.
		 */
		private function is_internal_url( $url ) {
			if ( substr( $url, 0, 2 ) === "//" ) {
				$url = is_ssl() ? 'https:' . $url : 'http' . $url;
			}
			$u = parse_url( $url );

			if ( ! isset( $u['host'] ) || isset( $u['host'] ) && $u['host'] == $this->internal_url['host'] ) {
				return true;
			}

			return false;
		}

		public function do_header( $path, $as, $args = array() ) {

			// Don't do header if header already done
			if ( in_array( $path, $this->srcs[ $as ] ) ) {
				return;
			}

			// Prepare header
			$header = sprintf( 'Link: <%s>; rel=%s; as="%s"', $path, 'preload', $as );
			if ( 'font' === $as ) {
				$header .= '; crossorigin';
			}

			if ( $this->is_header_size_smaller( strlen( $header ) ) ) {
				// Check if link as exists, if not create it.
				$this->srcs[ $as ]   = isset( $this->srcs[ $as ] ) ? $this->srcs[ $as ] : array();
				$this->srcs[ $as ][] = $path;

				$this->headers[] = $header;

			}

		}

		/**
		 * Loops through array and applies the function with a parameter.
		 *
		 * @param array $arr Array of items to be looped over.
		 * @param callable $fn Callable function.
		 * @param string $type Parameter to pass to function.
		 */
		private function each( $arr, $fn, $type ) {
			WPS\each( $arr, $fn, $type );
		}

		/**
		 * Determine if the plugin should render its own resource hints, or defer to WordPress.
		 * WordPress natively supports resource hints since 4.6. Can be overridden with
		 * 'http2_render_resource_hints' filter.
		 * @return boolean true if the plugin should render resource hints.
		 */
		public function should_render_prefetch_headers() {
			return apply_filters( 'http2_render_resource_hints', ! function_exists( 'wp_resource_hints' ) );
		}

		/**
		 * Determines whether the accumulated header size is smaller than HTTP2_MAX_HEADER_SIZE.
		 *
		 * @access private
		 *
		 * @param int $size New additional header size.
		 *
		 * @return bool
		 */
		private function is_header_size_smaller( $size ) {
			$size = $this->header_size + $size;
			if ( $size < self::HTTP2_MAX_HEADER_SIZE ) {
				$this->header_size += $size;

				return true;
			}

			return false;
		}

		public function do_item_link( $handle, $type ) {
			$item = WPS\get_script_style_dependency( $type, $handle );
			if ( $item->src ) {
				printf( '<link rel="preload" href="%s" as="%s">', $item->handle, esc_url( $item->src ), $type );
			}
		}

		/**
		 * Render "resource hints" in the <head> section of the page.
		 * These encourage preload/prefetch behavior when HTTP/2 support is lacking.
		 */
		public function resource_hints() {
			if ( is_admin() || ! $this->should_render_prefetch_headers() ) {
				return;
			}
			WPS\each( wp_scripts()->queue, array( $this, 'do_item_link' ), 'script' );
			WPS\each( wp_styles()->queue, array( $this, 'do_item_link' ), 'style' );

		}

		/**
		 * Render "resource hints" in the <head> section of the page.
		 *
		 * @param array $urls URLs to print for resource hints.
		 * @param string $relation_type The relation type the URLs are printed for, e.g. 'preconnect' or 'prerender'.
		 *
		 * @return array  $urls URLs to print for resource hints.
		 */
		public function required_resource_hints( $urls, $relation_type ) {
			$this->resource_hint_urls = array_merge( $this->resource_hint_urls, $urls );
			if ( 'dns-prefetch' === $relation_type ) {
				$urls[] = 'secure.gravatar.com';
				$urls[] = 'www.gravatar.com';
				if ( in_array( 'fonts.googleapis.com', $this->resource_hint_urls ) ) {
					$urls[] = 'fonts.gstatic.com';
				}
			}

			if ( 'preconnect' === $relation_type ) {
				if ( in_array( 'fonts.googleapis.com', $this->resource_hint_urls ) ) {
					$urls[] = array(
						'crossorigin',
						'href' => '//fonts.gstatic.com',
					);
				}
			}

			if ( isset( $this->hints[ $relation_type ] ) ) {
				foreach ( (array) $this->hints[ $relation_type ] as $url ) {
					if ( is_array( $url ) && isset( $url['href'] ) && ! in_array( $url['href'], $urls ) ) {
						$urls[] = $url;
					} else if ( ! is_array( $url ) && ! in_array( $url, $urls ) ) {
						$urls[] = $url;
					}
				}
			}

			return $urls;

		}
	}
}