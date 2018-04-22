<?php

namespace WPS\Core;

class Rewrite_Endpoint {

	/**
	 * Endpoint mask describing the places the endpoint should be added.
	 *
	 * @var int
	 */
	public $places;

	/**
	 * @var string
	 */
	public $template;

	/**
	 * @var string
	 */
	public $var;

	/**
	 * Rewrite_Endpoint constructor.
	 *
	 * @param $args
	 *
	 * @throws Exception
	 */
	public function __construct( $args ) {
		$args = wp_parse_args( $args, $this->defaults() );

		$this->places   = $args['places'];
		$this->template = $args['template'];
		$this->var      = $args['var'];

		if ( '' === $this->template ) {
			return;
//			throw new \Exception('Missing template!');
		}

		if ( '' === $this->var ) {
			return;
//			throw new \Exception('Missing var!');
		}

		add_action( 'init', array( $this, 'add_rewrite_endpoint' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
	}

	public function defaults() {
		return array(
			'places'   => EP_PERMALINK | EP_PAGES,
			'template' => '',
			'var'      => '',
		);
	}

	public function add_rewrite_endpoint() {
		add_rewrite_endpoint( $this->var, EP_PERMALINK | EP_PAGES );
	}

	public function template_redirect() {
		global $wp_query;

		// if this is not a request for json or a singular object then bail
		if ( ! isset( $wp_query->query_vars[ $this->var ] ) || ! is_singular() ) {
			return;
		}

		// include custom template
		include $this->template;
//		include dirname( __FILE__ ) . '/json-template.php';
		exit;
	}

}