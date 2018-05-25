<?php
/**
 * WPS Serve File
 *
 * @since     0.0.6
 *
 * @package   WPS_Core
 * @author    Travis Smith <t@wpsmith.net>
 * @license   GPL-2.0+
 * @link      http://wpsmith.net
 * @copyright 2014 Travis Smith, WP Smith, LLC
 */

namespace WPS\Core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPS\Core\WP_Serve_File' ) ) {
	/**
	 * Class WP_Serve_File
	 *
	 * @package WPS\Core
	 */
	class WP_Serve_File extends Singleton {

		/**
		 * Whether to use the file system.
		 *
		 * @var bool
		 */
		private static $use_filesystem;

		/**
		 * Array of files.
		 *
		 * @var array
		 */
		private $files = array();

		/**
		 * WP_Serve_File constructor.
		 */
		public function __construct() {
			require_once ABSPATH . 'wp-admin/includes/file.php';

			$upload_dir = wp_upload_dir();
			if ( get_filesystem_method( array(), $upload_dir['basedir'] ) !== 'direct' || ! WP_Filesystem( request_filesystem_credentials( admin_url() ) ) ) {
				self::$use_filesystem = false;

				add_action( 'wp_ajax_wpservefile', array( $this, 'serve_file' ) );
				add_action( 'wp_ajax_nopriv_wpservefile', array( $this, 'serve_file' ) );
			} else {
				self::$use_filesystem = true;
			}
		}

		/**
		 * Regenerates the file.
		 *
		 * @global \WP_Filesystem_Base $wp_filesystem
		 *
		 * @param string $name Filename.
		 *
		 * @return mixed|null
		 */
		private function regenerate_file( $name ) {
			$generator_fn = $this->files[ $name ];
			if ( ! $generator_fn ) {
				// The file isn't registered.
				return null;
			}

			$file = call_user_func( $generator_fn );
			if ( empty( $file['lastModified'] ) ) {
				$file['lastModified'] = gmdate( 'D, d M Y H:i:s', time() ) . ' GMT';
			}

			if ( self::$use_filesystem ) {
				// @global \WP_Filesystem_Base Filesystem.
				global $wp_filesystem;
				$upload_dir = wp_upload_dir();
				$dir        = trailingslashit( $upload_dir['basedir'] ) . 'wpservefile_files/';

				$wp_filesystem->mkdir( $dir );
				$wp_filesystem->put_contents( $dir . $name, $file['content'] );
			} else {
				set_transient( 'wpservefile_files_' . $name, $file, YEAR_IN_SECONDS );
			}

			return $file;
		}

		/**
		 * Serves file.
		 *
		 * @param string $name File name.
		 */
		public function serve_file( $name = '' ) {
			$name = $name ? $name : $_GET['wpservefile_file'];

			$file = get_transient( 'wpservefile_files_' . $name );
			if ( empty( $file ) ) {
				$file = $this->regenerate_file( $name );
				if ( empty( $file ) ) {
					return;
				}
			}

			$content       = $file['content'];
			$content_type  = $file['contentType'];
			$last_modified = $file['lastModified'];

			$max_age = DAY_IN_SECONDS;
			$etag    = md5( $last_modified );

			if (
				( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) && strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) >= strtotime( $last_modified ) ) ||
				( isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag )
			) {
				header( 'HTTP/1.1 304 Not Modified' );
				exit;
			}

			header( 'HTTP/1.1 200 OK' );
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + $max_age ) . ' GMT' );
			header( 'Cache-Control: max-age=' . $max_age . ', public' );
			header( 'Last-Modified: ' . $last_modified );
			header( 'ETag: ' . $etag );
			header( 'Pragma: cache' );
			header( 'Content-Type: ' . $content_type );
			echo $content;
			die();
		}

		/**
		 * Adds file to files.
		 *
		 * @param string   $name         File name.
		 * @param callback $generator_fn Generator callback function.
		 */
		public function add_file( $name, $generator_fn ) {
			$this->files[ $name ] = $generator_fn;
		}

		/**
		 * Invalidates the files.
		 *
		 * @param string[] $names Array of file names.
		 */
		public function invalidate_files( $names ) {
			foreach ( $names as $name ) {
				$this->regenerate_file( $name );
			}
		}

		/**
		 * Gets relative path to host root.
		 *
		 * @param string $name File name.
		 *
		 * @return string Relative URL.
		 */
		public static function get_relative_to_host_root_url( $name ) {
			if ( self::$use_filesystem ) {
				$upload_dir = wp_upload_dir();

				return trailingslashit( $upload_dir['baseurl'] ) . 'wpservefile_files/' . $name;
			} else {
				return admin_url( 'admin-ajax.php', 'relative' ) . '?action=wpservefile&wpservefile_file=' . $name;
			}
		}

		/**
		 * Relative path to WP Root URL.
		 *
		 * @param string $name File name.
		 *
		 * @return bool|string Relative URL.
		 */
		public static function get_relative_to_wp_root_url( $name ) {
			$url      = self::get_relative_to_host_root_url( $name );
			$site_url = site_url( '', 'relative' );
			if ( substr( $url, 0, strlen( $site_url ) ) === $site_url ) {
				$url = substr( $url, strlen( $site_url ) );
			}

			return $url;
		}
	}

}

