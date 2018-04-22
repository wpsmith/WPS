<?php

/**
 * WPS Service Worker
 *
 * @since 0.0.6
 *
 * @package   WPS_Core
 * @author    Travis Smith <t@wpsmith.net>
 * @license   GPL-2.0+
 * @link      http://wpsmith.net
 * @copyright 2014 Travis Smith, WP Smith, LLC
 */

namespace WPS\SW;

//use Mozilla\WP_SW_Manager;
use WPS\Core as Core;

use WPS;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WorkboxWorker' ) ) {
	/**
	 * Class WorkboxWorker
	 * @package WPS\SW
	 */
	class WorkboxWorker extends Core\Singleton {

		/**
		 * Service Worker Object.
		 *
		 * @var SW_File
		 */
		public $sw;

		/**
		 * Service Worker Registrar Object.
		 *
		 * @var SW_File
		 */
		public $sw_registrar;

		/**
		 * Workbox Object.
		 * @var Core\Dynamic_Url
		 */
		public $workbox;

		/**
		 * Default version.
		 * @var string
		 */
		public $default_version;

		/**
		 * Service Worker Options
		 *
		 * @var array
		 */
		public $options = array(
			'enabled'         => true,
			'race_enabled'    => true,
			'debug'           => true,
			'network_timeout' => 500,
		);

		/**
		 * Array of transient names.
		 * @var array
		 */
		public $transient_names = array(
			'pages'   => 'sw_pages',
			'scripts' => 'sw_registered_scripts',
			'routes'  => 'sw_routes',
		);

		/**
		 * Array of routes to precache.
		 * @var array
		 */
		public $routes = array();

		/**
		 * Array of scripts & styles to precache.
		 * @var array
		 */
		public $items = array();

		/**
		 * Array of URLs to Precache.
		 *
		 * @var array
		 */
		public $precache_urls = array();

		/**
		 * Worker constructor.
		 *
		 * Creates dynamic handling of sw.js and sw-toolbox.js.
		 *
		 * @uses Dynamic_Url Creates the URLs to handle these files.
		 */
		public function __construct( $args = array() ) {

			$this->hooks();
			$this->default_version = get_bloginfo( 'version' );

			$workbox_version_suffix = '.v1.1.0.js';
			if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ) {
				$workbox = 'workbox-sw.dev';
			} else {
				$workbox = 'workbox-sw.prod';
			}
			$workbox .= $workbox_version_suffix;

			$this->workbox = new Core\Dynamic_Url(
				$workbox,
				plugin_dir_path( __FILE__ ) . '../../node_modules/workbox-sw/build/importScripts/' . $workbox
			);

			$this->sw = new SW_File(
				array(
					'filename' => 'sw.js',
					'filepath' => plugin_dir_path( __FILE__ ) . 'js/sw.js',
				),
				$this->get_sw_content_replacements()
			);
//WPS\printr($this->sw, 'sw');

			$this->sw_registrar = new SW_File(
				array(
					'filename' => 'register.js',
					'filepath' => plugin_dir_path( __FILE__ ) . 'js/register.js',
				)
			);

			add_action( 'wp_head', array( $this, 'add_registrar' ) );

			new Core\WP_Cron( 'wps_create_sw', array(
				'name'     => 'five_seconds',
				'interval' => 5,
				'display'  => esc_html__( 'Every Five Seconds' ),
			), array( $this, 'wps_create_sw' ) );
		}

		public function wps_create_sw() {
			WPS\write_log( DOING_CRON, 'DOING_CRON' );
			WPS\write_log( $_GET['doing_wp_cron'], '$_GET[doing_wp_cron]' );
			if ( ! defined( 'DOING_CRON' ) || ! isset( $_GET['doing_wp_cron'] ) ) {
				return;
			}

			$this->set_routes_transient();
			$this->set_pages_transient();
			$this->set_scripts_transient();
		}

		public function hooks() {
			// Set in CRON, no longer needed.
			if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'set_scripts_transient' ), 9999 );
				add_action( 'init', array( $this, 'set_pages_transient' ), 99 );
				add_action( 'after_setup_theme', array( $this, 'set_routes_transient' ), 99 );
			}

			add_filter( 'wps_core_sw_precache_list', array( $this, 'get_scripts' ) );
			add_filter( 'wps_core_sw_precache_list', array( $this, 'get_pages' ) );
		}

		public function add_registrar() {
			$contents = file_get_contents( $this->sw_registrar->get_file() );

			printf( '<script id="wps-service-registrar" type="text/javascript">%s</script>', $contents );
		}

		/**
		 * Gets the vars and its replacements for the SW.
		 *
		 * @return array Array of vars and replacements.
		 */
		private function get_sw_content_replacements() {
			return array(
				'$enabled'        => boolval( $this->options['enabled'] ),
				'$workbox'        => $this->workbox->get_relative_url(),
				'$cacheId'        => sanitize_title_with_dashes( get_bloginfo( 'name' ) ),
				'$ignoreParams'   => array(),
//				'$ignoreParams'   => array(
//					's'
//				),
//				'$appshell'       => array(
//					'shell'     => $this->options['shell'],
//					'whitelist' => $this->options['whitelist'],
//					'blacklist' => $this->options['blacklist'],
//				),
				'$resources'      => $this->get_precache_list(),
				'$networkTimeout' => intval( $this->options['network_timeout'] ),
				'$routes'         => $this->get_routes(),
			);
		}



		/** FILTERABLE REPLACEMENT PARAMS */

		/**
		 * Gets a list of items to precache.
		 *
		 * @return array Array of published pages & scripts/styles.
		 */
		private function get_precache_list() {
			return apply_filters( 'wps_core_sw_precache_list', array(), $this );
		}

		/** ROUTES */

		private function register_route( $pattern, $strategy, $args = array() ) {
			$default_strategies = array(
				'networkFirst',
				'networkOnly',
				'cacheFirst',
				'cacheOnly',
				'staleWhileRevalidate',
			);

			$strategy       = in_array( $strategy, $default_strategies ) ? $strategy : $default_strategies[0];
			$this->routes[] = array(
				'pattern'  => $pattern,
				'strategy' => $strategy,
				'args'     => $args,
			);
		}

		public function get_routes() {
			$routes = Core\AsyncTransients\get_stale_transient( 'transient-key-' . $this->transient_names['routes'] );

			return $routes;
		}

		public function set_routes_transient() {
			$theme = wp_get_theme();
			$slug  = sanitize_title_with_dashes( $theme->get( 'Name' ) );

			$cache_default_args = array(
				'cacheName'            => $slug,
				'cacheExpiration'      => array(
					'maxEntries'    => 20,
					'maxAgeSeconds' => DAY_IN_SECONDS,
				),
				'cacheableResponse'    => array(
					'statuses' => array( 0, 200 )
				),
				'broadcastCacheUpdate' => array(
					'channelName' => $slug . '-channel',
				),
			);

			// Theme routes
			$theme_slug       = sanitize_title_with_dashes( $theme->get( 'Name' ) );
			$theme_cache_args = wp_parse_args( array(
				'cacheName'            => $theme_slug,
				'broadcastCacheUpdate' => array(
					'channelName' => $theme_slug . '-channel',
				),
			), $cache_default_args );
			$this->register_route( trailingslashit( get_template_directory_uri() ) . '(.*)', 'cacheFirst', $theme_cache_args );

			if ( get_stylesheet_directory_uri() ) {
				$this->register_route( trailingslashit( get_stylesheet_directory_uri() ) . '(.*)', 'cacheFirst', $theme_cache_args );
			}

			// Uploads routes
			$uploads_cache_args = wp_parse_args( array(
				'cacheName' => $slug . '-uploads',
				'broadcastCacheUpdate' => array(
					'channelName' => $slug . '-uploads-channel',
				),
			), $cache_default_args );
			$upload_dir       = wp_upload_dir();
			$this->register_route( trailingslashit( $upload_dir['baseurl'] ) . '(.*)', 'cacheFirst', $uploads_cache_args );

//			// Membership area
//			$this->register_route( trailingslashit( $upload_dir['basedir'] ) . '(.*)', 'networkFirst', array(
//				'networkTimeoutSeconds' => intval( $this->options['network_timeout'] ),
//			) );

//			WPS\write_log( $this->routes, 'routes' );
			Core\AsyncTransients\set_async_transient( 'transient-key-' . $this->transient_names['routes'], $this->routes, DAY_IN_SECONDS );
		}

		/** PAGES */
		public function get_pages( $list = array() ) {
			$pages = Core\AsyncTransients\get_stale_transient( 'transient-key-' . $this->transient_names['pages'] );

			return array_merge( (array) $list, (array) $pages );
		}

		public function set_pages_transient() {
			$pages = $ids = array();

			// Pages
			foreach ( (array) get_pages() as $page ) {
				if ( is_a( $page, 'WP_Post' ) ) {
					$pages[] = array(
						'url'      => get_page_link( $page ),
						'revision' => $this->get_revision_hash( $page->post_modified ),
					);
					$ids[]   = $page->ID;
				}
			}

			// Most recent blog post
			$latest_post = wp_get_recent_posts( array( 'numberposts' => 1, ), ARRAY_A );
			if ( ! empty( $latest_post ) && is_a( $latest_post[0], 'WP_Post' ) ) {
				$pages[] = array(
					'url'      => get_post_permalink( $latest_post[0]->ID ),
					'revision' => $this->get_revision_hash( $latest_post[0]->post_modified ),
				);
				$ids[]   = $latest_post[0]->ID;
			}

			// Get menu items
			$menus = get_registered_nav_menus();
			foreach ( $menus as $location => $description ) {
				$items = wp_get_nav_menu_items( $location );
				if ( empty( $items ) ) {
					continue;
				}
				foreach ( $items as $menu_item ) {
					if ( in_array( $menu_item->object_id, $ids ) ) {
						continue;
					}
					$pages[] = array(
						'url'      => $menu_item->url,
						'revision' => $this->get_revision_hash( $menu_item->post_modified ),
					);

				}
			}

//			WPS\write_log( $pages, 'pages' );
			Core\AsyncTransients\set_async_transient( 'transient-key-' . $this->transient_names['pages'], $pages, DAY_IN_SECONDS );
		}

		/** SCRIPTS */
		public function get_scripts( $list = array() ) {
			$scripts = Core\AsyncTransients\get_stale_transient( 'transient-key-' . $this->transient_names['scripts'] );

			return array_merge( (array) $list, (array) $scripts );
		}

		public function get_script_style( $handle, $type ) {
			$item = WPS\get_script_style_dependency( $type, $handle );

			// Get proper global items
			if ( 'script' === $type ) {
				$items = wp_scripts();
			} else {
				$items = wp_styles();
			}

			// Get the version
			$version = $items->registered[ $handle ]->ver ? $items->registered[ $handle ]->ver : $this->default_version;

			// Do deps
			if ( ! empty( $item->deps ) ) {
				WPS\each( $item->deps, array( $this, 'get_script_style' ), $type );
			}

			// If URL, then add
			if ( $items->registered[ $handle ]->src ) {
				$this->items[] = array(
					'url'      => $items->registered[ $handle ]->src,
					'revision' => $this->get_revision_hash( $version ),
				);
			}

		}

		public function set_scripts_transient() {

			if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
				ob_start();
				wp_enqueue_scripts();
				WPS\write_log( ob_get_clean(), 'did wp_enqueue_scripts' );
			}

			WPS\each( wp_scripts()->queue, array( $this, 'get_script_style' ), 'script' );
			WPS\each( wp_styles()->queue, array( $this, 'get_script_style' ), 'style' );

			Core\AsyncTransients\set_async_transient( 'transient-key-' . $this->transient_names['scripts'], $this->items, DAY_IN_SECONDS );
		}

		/** UTILS */
		private function get_revision_hash( $date ) {
			return hash_hmac( 'md5', $date, NONCE_SALT );
		}
	}
}