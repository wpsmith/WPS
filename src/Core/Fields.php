<?php
/**
 * Fields Class
 *
 * Assists in using and instantiating ACF.
 *
 * You may copy, distribute and modify the software as long as you track changes/dates in source files.
 * Any modifications to or software including (via compiler) GPL-licensed code must also be made
 * available under the GPL along with build & install instructions.
 *
 * @package    WPS\Core
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link       https://github.com/wpsmith/WPS
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\Core;

use StoutLogic\AcfBuilder\FieldsBuilder;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPS\Core\Fields' ) ) {
	/**
	 * Fields Class
	 *
	 * Assists in using ACF.
	 *
	 * @package WPS\Core
	 * @author  Travis Smith <t@wpsmith.net>
	 */
	class Fields extends Singleton {

		/**
		 * Holds the various fields builders.
		 *
		 * @var array Array of StoutLogic\AcfBuilder\FieldsBuilder
		 */
		public $builder = array();

		/**
		 * Whether ACF is instantiated.
		 *
		 * @var bool
		 */
		public $instantiated = false;

		/**
		 * Fields constructor.
		 */
		protected function __construct() {
			if ( did_action( 'init' ) || doing_action( 'init' ) ) {
				$this->create();
			} else {
				add_action( 'init', array( $this, 'create' ), ~PHP_INT_MAX );
			}
		}

		/**
		 * Adds a builder to the array.
		 *
		 * @param FieldsBuilder $builder Builder to be added.
		 */
		public function add( $builder ) {
			$this->builder[] = $builder;
		}

		/**
		 * Creates the fields.
		 */
		public function create() {
			/**
			 * Does the core_acf_fields action.
			 *
			 * @param Fields $this Fields object to builder the Fields Builder.
			 */
			do_action( 'core_acf_fields', $this );

			if ( did_action( 'acf/init' ) || doing_action( 'acf/init' ) ) {
				$this->init_fields();
			} else {
				add_action( 'acf/init', array( $this, 'init_fields' ) );
			}
		}

		/**
		 * Adds the custom fields/metaboxes
		 *
		 * @uses acf_add_local_field_group
		 */
		public function init_fields() {
			foreach ( $this->builder as $fields ) {
				if ( function_exists( 'acf_add_local_field_group' ) ) {
					acf_add_local_field_group( $fields->build() );
				}
			}
		}

		/**
		 * Instantiates a new FieldsBuilder
		 *
		 * @param string $key  Key for fields.
		 * @param array  $args Args for fields.
		 *
		 * @return FieldsBuilder Fields builder.
		 */
		public function new_fields_builder( $key = '', $args = array() ) {

			return new FieldsBuilder( $key, $args );

		}

	}
}
