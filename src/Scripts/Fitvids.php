<?php

namespace WPS\Scripts;

class Fitvids extends Script {

	public $handle = 'fitvids';

	protected function __construct( $args = array() ) {
		add_action( 'the_content', array( $this, 'check_content' ) );
		add_action( 'embed_oembed_html', array( $this, 'embed_oembed_html' ) );

		$suffix = self::get_suffix();
		$args = wp_parse_args( array(
			'deps'   => array( 'jquery', ),
			'inline' => '(function($){$(document).ready(function(){$(".entry-content,.wp-video-shortcode,.flex-caption").fitVids()})})(jQuery);'
		) , $this->get_defaults( "/core/assets/js/jquery.fitvids{$suffix}.js" ) );

		parent::__construct( $args );
	}

	public function embed_oembed_html( $html ) {
		if ( preg_match( '/<iframe[^>]*src=\"[^\"]*(youtu[.]?be|vimeo.com).*<\/iframe>/', $html ) ) {
			add_filter( 'wps_fitvids_conditional', '__return_true' );
			$this->enqueue();
		}

		return $html;
	}

	public function check_content( $content ) {
		if ( has_shortcode( $content, 'video' ) ) {
			add_filter( 'wps_fitvids_conditional', '__return_true' );
		}
		return $content;
	}

	protected function conditional() {
		$post = get_post();

		return apply_filters( 'wps_fitvids_conditional', ( has_category( 'video', $post ) || is_front_page() ) );
	}
}