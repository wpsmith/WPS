<?php

namespace WPS\Scripts;

use WPS\Core;

abstract class Script extends Core\Singleton {

	public $handle = '';
	public $src = '';
	public $file = '';
	public $deps = array();
	public $version = '';
	public $inline = '';
	public $priority = 10;
	public $inline_added = false;
	public $conditional_cb;
	public $localize = array( 'name' => '', 'object' => array() );

	protected function __construct( $args = array() ) {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

		$this->handle   = $args['handle'];
		$this->src      = $args['src'];
		$this->version  = filemtime( $args['file'] );
		$this->deps     = isset( $args['deps'] ) ? $args['deps'] : array();
		$this->inline   = $args['inline'];
		$this->priority = $args['priority'];

	}

	public static function get_suffix() {
		return ( ( defined( 'STYLE_DEBUG' ) && STYLE_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ) ? '' : '.min';
	}

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

	public function plugins_loaded() {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_script' ), $this->priority );
	}

	public function register() {
		wp_register_script( $this->handle, $this->src, $this->deps, $this->version, true );
	}

	public function add_conditional( $conditional ) {
		$this->conditional_cb = $conditional;
	}

	public function add_inline( $inline ) {
		$this->inline = $inline;
	}

	public function maybe_enqueue_script() {
		if ( $this->conditional_cb && is_callable( $this->conditional_cb ) && call_user_func( $this->conditional_cb ) ) {
			$this->enqueue();
		} elseif ( method_exists( $this, 'conditional' ) && is_callable( array(
				$this,
				'conditional'
			) ) && call_user_func( array( $this, 'conditional' ) )
		) {
			$this->enqueue();

		} elseif (
			! $this->conditional_cb ||
			( $this->conditional_cb && ! is_callable( $this->conditional_cb ) )
		) {
			$this->enqueue();
		}
	}

	abstract protected function conditional();

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