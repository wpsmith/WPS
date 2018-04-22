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

//use WPS\Core\Singleton;
//use WPS\Core\Dynamic_Url;
use WPS;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SW_File' ) ) {
	/**
	 * Class Worker
	 * @package WPS\SW
	 */
	class SW_File extends Core\Singleton {

		/**
		 * Default version of the file (WP Version).
		 *
		 * @var string|void
		 */
		public $default_version;


		public $version;
		public $replacements = array();
		public $file;

		public function __construct( $args = array(), $replacements = array() ) {
			$this->file            = $this->create_url( $args );
			$this->default_version = get_bloginfo( 'version' );
			$this->replacements    = (array) $replacements;
		}

		private function create_url( $args = array() ) {
			$defaults = array(
				'filename'  => null,
				'filepath'  => null,
				'generator' => array( $this, 'generator' ),
			);

			$file = ! empty( $args ) ? wp_parse_args( $args, $defaults ) : $defaults;

			return new Core\Dynamic_Url( $file['filename'], $file['filepath'], $file['generator'] );
		}

		/**
		 * Generator for SW.
		 *
		 * @return array Array of variable.
		 */
		public function generator() {
			return array(
				'contentType' => 'application/javascript',
				'content'     => self::get_replaced_contents(
					$this->file->get_file(),
					$this->replacements
				)
			);
		}

		public function get_file() {
			return $this->file->get_file();
		}

		/**
		 * Render file contents replacing PHP variables.
		 *
		 * @access private
		 *
		 * @param string $path Filepath string.
		 * @param array $replacements Array of strings to replace in file contents.
		 *
		 * @return mixed|string
		 */
		public static function get_replaced_contents( $path, $replacements = array(), $version = null ) {
			$contents = file_get_contents( $path );
			if ( empty( $replacements ) ) {
				return $contents;
			}
			$incremental_hash = hash_init( 'md5' );
			hash_update( $incremental_hash, $contents );
			foreach ( (array) $replacements as $key => $replacement ) {
				if ( '$version' === $key ) {
					continue;
				}
				$value = json_encode( $replacement );
				hash_update( $incremental_hash, $value );
				$contents = str_replace( $key, $value, $contents );
			}

			if ( $version || isset( $replacements['$version'] ) ) {
				$version  = hash_final( $incremental_hash );
				$version  = json_encode( $version );
				$contents = str_replace( '$version', $version, $contents );
			}

			return $contents;
		}

	}
}