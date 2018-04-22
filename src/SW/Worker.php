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

use Mozilla\WP_SW_Manager;
use WPS\Core as Core;

use WPS;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Worker' ) ) {
	/**
	 * Class Worker
	 * @package WPS\SW
	 */
	class Worker extends Core\Singleton {

		private $SW_SCRIPT_URL = 'js/sw/sw';
		private $SW_TOOLBOX_SCRIPT_URL = 'js/sw-toolbox/sw-toolbox';

		/**
		 * Service Worker Object.
		 *
		 * @var SW_File
		 */
		public $sw;

		/**
		 * Service Worker Toolbox Object.
		 *
		 * @var Dynamic_Url
		 */
		public $toolbox;

		/**
		 * Default version.
		 * @var string
		 */
		public $default_version;

		/**
		 * Service Worker Scope.
		 *
		 * @access private
		 * @var string
		 */
		private $scope = '/';

		/**
		 * Service Worker Options
		 *
		 * @var array
		 */
		public $options = array(
			'enabled'         => true,
			'race_enabled'    => false,
			'debug'           => true,
			'network_timeout' => 1000,
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

			$suffix = $this->is_debug() ? '.js' : '.min.js';
			$this->SW_SCRIPT_URL .= $suffix;
			$this->SW_TOOLBOX_SCRIPT_URL .= $suffix;

			$this->hooks();
			$this->default_version = get_bloginfo( 'version' );

			// Create /sw.js path
			$this->sw = new SW_File(
				array(
					'filename' => 'sw.js',
					'filepath' => plugin_dir_path( __FILE__ ) . $this->SW_SCRIPT_URL,
				),
				$this->get_sw_content_replacements()
			);

			// Create /sw-toolbox.js path
			$this->toolbox = new SW_File(
				array(
					'filename' => 'sw-toolbox.js',
					'filepath' => plugin_dir_path( __FILE__ ) . $this->SW_TOOLBOX_SCRIPT_URL,
				)
			);

			// Set options dynamically.
			// Set scope optionally.
			$this->scope = isset( $args['scope'] ) ? $args['scope'] : site_url( '/', 'relative' );
			foreach ( $this->options as $key => $val ) {
				$this->options[ $key ] = isset( $args[ $key ] ) ? $args[ $key ] : $val;
			}

			$this->setup_sw();

			$cron = new Core\WP_Cron( 'wps_create_sw', array(
				'name'     => 'five_seconds',
				'interval' => 5,
				'display'  => esc_html__( 'Every Five Seconds' ),
			), array( $this, 'wps_create_sw' ), 'now' );

		}

		private function is_debug() {
			return (
				( defined( 'WP_DEBUG' ) && WP_DEBUG ) ||
				( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
			);
		}

		/** SW */
		// @see https://github.com/mozilla/wp-sw-manager/blob/master/README.md
		private function setup_sw() {
			WP_SW_Manager::get_manager()
			             ->sw( $this->get_scope() )
			             ->add_content( array( $this, 'render_sw' ) );
		}

		private function get_scope() {
			return home_url( '/', 'relative' );
		}

		public function render_sw() {
			$contents = $this->sw->generator();

			echo $contents['content'];
		}

		/** GENERATORS */
		private function _js_generator( $file ) {
			$contents = file_get_contents( $file );

			return array(
				'content'     => $contents,
				'contentType' => 'application/javascript'
			);
		}

		public function sw_js_generator() {
			return $this->_js_generator( $this->get_sw_js_file() );
		}

		public function sw_toolbox_js_generator() {
			return $this->_js_generator( $this->get_sw_toolbox_file() );
		}

		private function get_sw_js_file() {
			return plugin_dir_path( __FILE__ ) . $this->SW_SCRIPT_URL;
		}

		private function get_sw_toolbox_file() {
			return plugin_dir_path( __FILE__ ) . $this->SW_TOOLBOX_SCRIPT_URL;
		}

		/** CRON */

		public function wps_create_sw() {
			WPS\write_log( 'wps_create_sw' );
			WPS\write_log( DOING_CRON, 'DOING_CRON' );
			if ( ! defined( 'DOING_CRON' ) || ! isset( $_GET['doing_wp_cron'] ) ) {
				return;
			}

			$this->set_routes_transient();
			$this->set_pages_transient();
			$this->set_scripts_transient();
		}

		public function hooks() {

			// Set in CRON, no longer needed.
			if ( Core\WP_Cron::is_cron_disabled() ) {
				if ( ! Core\WP_Cron::is_doing_cron() ) {
					WPS\write_log( 'hooking set_scripts_transient' );
					add_action( 'wp_enqueue_scripts', array( $this, 'set_scripts_transient' ), 9999 );
				}

				WPS\write_log( 'hooking set_pages_transient' );
				WPS\write_log( 'hooking set_routes_transient' );
				add_action( 'init', array( $this, 'set_pages_transient' ), 99 );
				add_action( 'after_setup_theme', array( $this, 'set_routes_transient' ), 99 );
			}

			add_filter( 'wps_core_sw_resource_list', array( $this, 'get_scripts' ) );
			add_filter( 'wps_core_sw_resource_list', array( $this, 'get_pages' ) );

			add_filter( 'wps_core_sw_precache_list', array( $this, 'get_precache' ) );
		}

		/**
		 * Gets the vars and its replacements for the SW.
		 *
		 * @return array Array of vars and replacements.
		 */
		private function get_sw_content_replacements() {
			return array(
				'$debug'           => boolval( $this->options['debug'] ),
				'$raceEnabled'     => boolval( $this->options['race_enabled'] ),
				'$networkTimeout'  => intval( $this->options['network_timeout'] ),
				'$fileversion'     => filemtime( $this->get_sw_js_file() ),
				'$defaultStrategy' => apply_filters( 'wps_sw_cache_strategy_default', 'networkFirst', $this ),
//				'$defaultStrategy' => apply_filters( 'wps_sw_cache_strategy_default', 'cacheFirst', $this ),
				'$resources'       => $this->get_resource_list(),
				'$precache'        => $this->get_precache_list(),
				'$routes'          => $this->get_routes_list(),
				'$excludedPaths'   => $this->get_excluded_paths(),
				'$version'         => true,
			);
		}

		/** FILTERABLE REPLACEMENT PARAMS */
		/**
		 * Gets an array of path exclusions.
		 *
		 * Defaults to only the admin_url().
		 * Use filter to add content_url() or includes_url().
		 *
		 * @return array
		 */
		private function get_excluded_paths() {
			return apply_filters( 'wps_core_sw_excluded_paths', array( admin_url() ) );
		}


		/**
		 * Gets a list of items to precache.
		 *
		 * @return array Array of published pages & scripts/styles.
		 */
		private function get_precache_list() {
			return apply_filters( 'wps_core_sw_precache_list', array(), $this );
		}

		/**
		 * Gets a list of items to precache.
		 *
		 * @return array Array of published pages & scripts/styles.
		 */
		private function get_resource_list() {
			return apply_filters( 'wps_core_sw_resource_list', array(), $this );
		}

		public function get_precache() {
			$resources = $this->get_pages( $this->get_scripts() );

			return wp_list_pluck( $resources, 'url' );
		}

		/** ROUTES */
		private function register_route( $pattern, $strategy, $args = array() ) {

			$default_strategies = array(
				'networkFirst',
				'networkOnly',
				'cacheFirst',
				'cacheOnly',
				'fastest',
			);

			$strategy       = in_array( $strategy, $default_strategies ) ? $strategy : $default_strategies[0];
			$this->routes[] = array(
				'pattern'  => $pattern,
				'strategy' => $strategy,
				'args'     => $args,
			);
		}

		public function get_routes_list() {
			return apply_filters( 'wps_core_sw_routes_list', $this->get_routes(), $this );
		}

		public function get_routes() {
			$routes = Core\AsyncTransients\get_stale_transient( 'transient-key-' . $this->transient_names['routes'] );

			return $routes;
		}

		public function set_routes_transient() {
			$theme = wp_get_theme();
			$slug  = sanitize_title_with_dashes( $theme->get( 'Name' ) );

			// Defaults
			$cache_strategy = ( defined( 'WP_DEBUG' ) && WP_DEBUG || defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? 'networkFirst' : 'cacheFirst';
			$cache_strategy = apply_filters( 'wps_sw_cache_strategy', $cache_strategy, $this );

			$cache_default_args = apply_filters( 'wps_sw_cache_default_args', array(
				'cache'  => array(
					'name'          => $slug,
					'maxEntries'    => 40,
					'maxAgeSeconds' => DAY_IN_SECONDS,
				),
				'origin' => rtrim( site_url( '/' ), '/' ),
			) );

			// Theme routes
			$theme_slug       = sanitize_title_with_dashes( $theme->get( 'Name' ) );
			$theme_cache_args = wp_parse_args( array(
				'cache' => array(
					'name' => $theme_slug,
				),
			), $cache_default_args );
			$this->register_route( WPS\get_relative_url( trailingslashit( get_template_directory_uri() ) ) . '(.*)', $cache_strategy, $theme_cache_args );

			if ( get_stylesheet_directory_uri() ) {
				$this->register_route( WPS\get_relative_url( trailingslashit( get_stylesheet_directory_uri() ) ) . '(.*)', $cache_strategy, $theme_cache_args );
			}

			// Uploads routes
			$uploads_cache_args = wp_parse_args( array(
				'cache' => array(
					'name' => $slug . '-uploads',
				),
			), $cache_default_args );
			$upload_dir         = wp_upload_dir();
			$this->register_route( WPS\get_relative_url( trailingslashit( $upload_dir['baseurl'] ) ) . '(.*)', $cache_strategy, $uploads_cache_args );

//			// Membership area

			// Plugin Routes
			$plugins_cache_args = wp_parse_args( array(
				'cache' => array(
					'name' => $slug . '-plugins',
				),
			), $cache_default_args );
			$this->register_route( WPS\get_relative_url( trailingslashit( plugins_url() ) ) . '(.*)', $cache_strategy, $plugins_cache_args );


			// WP-ADMIN
			$admin_cache_args = wp_parse_args( array(
				'cache' => array(
					'name' => $slug . '-wp-admin',
				),
			), $cache_default_args );
			$this->register_route( trailingslashit( admin_url( 'css', 'relative' ) ) . '(.*)', $cache_strategy, $admin_cache_args );
			$this->register_route( trailingslashit( admin_url( 'images', 'relative' ) ) . '(.*)', $cache_strategy, $admin_cache_args );
			$this->register_route( trailingslashit( admin_url( 'js', 'relative' ) ) . '(.*)', $cache_strategy, $admin_cache_args );
			$this->register_route( trailingslashit( admin_url( '', 'relative' ) ) . '(.*)', 'networkOnly', $admin_cache_args );

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
				$url        = $items->registered[ $handle ]->src;
				$parsed_url = parse_url( $items->registered[ $handle ]->src );

				if ( ! isset( $parsed_url['host'] ) && substr( $items->registered[ $handle ]->src, 0, 2 ) !== '//' ) {
					$url = site_url( $items->registered[ $handle ]->src );
				}
				$this->items[] = array(
					'url'      => $url,
					'handle'   => $handle,
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