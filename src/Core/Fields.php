<?php
/**
 * WPS Core Fields Class
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

if ( ! class_exists( 'Fields' ) ) {
	/**
	 * Fields Class
	 *
	 * Assists in using ACF.
	 *
	 * @package WPS\Core
	 * @author Travis Smith <t@wpsmith.net>
	 */
	class Fields extends Singleton {

		public $builder = array();

		protected function __construct() {
			if ( did_action( 'init' ) || doing_action( 'init' ) ) {
				$this->create();
			} else {
				add_action( 'init', array( $this, 'create' ) );
			}
		}

		public function create() {
			do_action( 'core_acf_fields', $this );

			if ( did_action( 'acf/init' ) || doing_action( 'acf/init' ) ) {
				$this->init_fields();
			} else {
				add_action( 'acf/init', array( $this, 'init_fields' ) );
			}
		}

		public function init_fields() {
			foreach ( $this->builder as $fields ) {
				acf_add_local_field_group( $fields->build() );
			}
		}

	}
}
