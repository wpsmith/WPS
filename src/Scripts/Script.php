<?php
/**
 * Abstract Script Class File
 *
 * Base Script support.
 *
 * You may copy, distribute and modify the software as long as you track changes/dates in source files.
 * Any modifications to or software including (via compiler) GPL-licensed code must also be made
 * available under the GPL along with build & install instructions.
 *
 * @package    WPS\Scripts
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link       https://github.com/wpsmith/WPS
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\Scripts;

use WPS\Core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPS\Scripts\Script' ) ) {
	/**
	 * Class Script.
	 *
	 * @package WPS\Scripts
	 */
	abstract class Script extends Core\Singleton {

		/**
		 * Script Handle.
		 *
		 * @var string
		 */
		public $handle = '';

		/**
		 * Script Source URL.
		 *
		 * @var string
		 */
		public $src = '';

		/**
		 * File absolute path.
		 *
		 * @var string
		 */
		public $file = '';

		/**
		 * Script dependency handles.
		 *
		 * @var string[]
		 */
		public $deps = array();

		/**
		 * Script version.
		 *
		 * @var bool|int|string
		 */
		public $version = '';

		/**
		 * Inline script.
		 *
		 * @var string
		 */
		public $inline = '';

		/**
		 * Priority of script.
		 *
		 * @var int
		 */
		public $priority = 10;

		/**
		 * Whether inline was added.
		 *
		 * @var bool
		 */
		protected $inline_added = false;

		/**
		 * Conditional callback function.
		 *
		 * @var callback
		 */
		public $conditional_cb;

		/**
		 * Localization array.
		 *
		 * @var array
		 */
		public $localize = array(
			'name'   => '',
			'object' => array(),
		);

		/**
		 * Script constructor.
		 *
		 * @param array $args Script args.
		 */
		protected function __construct( $args = array() ) {
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

			$args = wp_parse_args( $args, $this->get_defaults() );

			$this->handle   = $args['handle'];
			$this->src      = $args['src'];
			$this->version  = filemtime( $args['file'] );
			$this->deps     = isset( $args['deps'] ) ? $args['deps'] : array();
			$this->inline   = $args['inline'];
			$this->priority = $args['priority'];

		}

		/**
		 * Conditionally gets the suffix (.min) for file.
		 *
		 * @return string
		 */
		public static function get_suffix() {
			return ( ( defined( 'STYLE_DEBUG' ) && STYLE_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ) ? '' : '.min';
		}

		/**
		 * Gets the script args defaults.
		 *
		 * @param string $rel_path Relative path to file.
		 *
		 * @return array
		 */
		protected function get_defaults( $rel_path = '' ) {
			return array(
				'handle'   => $this->handle,
				'src'      => plugins_url( $rel_path, WPSCORE_FILE ),
				'file'     => plugin_dir_path( WPSCORE_FILE ) . ltrim( $rel_path, '/' ),
				'deps'     => array(),
				'inline'   => '',
				'priority' => 25,
			);
		}

		/**
		 * Add the hooks on plugins_loaded hook.
		 */
		public function plugins_loaded() {
			add_action( 'init', array( $this, 'register' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_script' ), $this->priority );
		}

		/**
		 * Registers the script.
		 */
		public function register() {
			wp_register_script( $this->handle, $this->src, $this->deps, $this->version, true );
		}

		/**
		 * Adds conditional callback.
		 *
		 * @param callback $conditional Conditional callback function.
		 */
		public function add_conditional( $conditional ) {
			$this->conditional_cb = $conditional;
		}

		/**
		 * Adds inline.
		 *
		 * @param string $inline Inline JS.
		 */
		public function add_inline( $inline ) {
			$this->inline = $inline;
		}

		/**
		 * Conditionally enqueues script based on conditional callback.
		 */
		public function maybe_enqueue_script() {
			if (
				$this->conditional_cb &&
				is_callable( $this->conditional_cb ) &&
				call_user_func( $this->conditional_cb )
			) {
				$this->enqueue();
			} elseif (
				method_exists( $this, 'conditional' ) &&
				is_callable( array( $this, 'conditional' ) ) &&
				call_user_func( array( $this, 'conditional' )
				)
			) {
				$this->enqueue();

			} elseif (
				! $this->conditional_cb ||
				( $this->conditional_cb && ! is_callable( $this->conditional_cb ) )
			) {
				$this->enqueue();
			}
		}

		/**
		 * Conditional callback.
		 *
		 * Adds the script if it is not in the admin.
		 *
		 * @return mixed
		 */
		protected function conditional() {
			return (bool) apply_filters(
				'wps__conditional',
				apply_filters( "wps_{$this->handle}_conditional", ! is_admin() ),
				$this->handle
			);
		}

		/**
		 * Conditionally enqueues, adds inline and localizes script.
		 */
		protected function enqueue() {
			if ( ! $this->conditional() ) {
				return;
			}
			wp_enqueue_script( $this->handle );
			if ( $this->inline && ! $this->inline_added ) {
				wp_add_inline_script( $this->handle, $this->inline );
				$this->inline_added = true;
			}
			if ( $this->localize['name'] && $this->localize['object'] ) {
				wp_localize_script( $this->handle, $this->localize['name'], $this->localize['object'] );
			}
		}
	}
}
