<?php

/**
 * @todo Add Subtrees for core/plugins/acf-content-analysis-for-yoast-seo, core/plugins/cpt-onomies, core/plugins/post-types-order
 */

namespace WPS\Templates;

use WPS;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Simple_Sidebars {

	public $post_type;

	public function __construct( $post_type ) {
		$this->post_type = $post_type;

		add_action( 'widgets_init', array( $this, 'widgets_init' ) );
		add_action( 'genesis_header', array( $this, 'add_sidebars' ) );
		add_filter( 'sidebars_widgets', array( $this, 'sidebars_widgets_filter' ) );
	}

	private function has_3_column_layout() {

		$layouts = genesis_get_layouts();

		$three_column_layouts = array(
			'content-sidebar-sidebar',
			'sidebar-content-sidebar',
			'sidebar-sidebar-content',
		);

		foreach ( $three_column_layouts as $layout ) {
			if ( array_key_exists( $layout, $layouts ) ) {
				return true;
			}
		}

		return false;

	}

	public function add_sidebars() {
		global $wp_query;

		// For some reason, this doesn't work
		//if ( is_singular( $this->post_type ) || is_post_type_archive( $this->post_type ) ) {
		if ( $wp_query->is_singular( $this->post_type ) || $wp_query->is_post_type_archive( $this->post_type ) ) {
			remove_action( 'genesis_sidebar', 'ss_do_sidebar' );
			add_action( 'genesis_sidebar', array( $this, 'primary_sidebar' ) );

			if ( $this->has_3_column_layout() ) {
				remove_action( 'genesis_sidebar_alt', 'ss_do_sidebar_alt' );
				add_action( 'genesis_sidebar_alt', array( $this, 'secondary_sidebar' ) );
			}
		}
	}

	/**
	 * Filter the widgets in each widget area.
	 *
	 * @since 2.1.0
	 */
	public function sidebars_widgets_filter( $widgets ) {

		$sidebars = array();

		if ( ! is_front_page() && ( is_singular( $this->post_type ) || is_post_type_archive( $this->post_type ) ) ) {

			$sidebars = array(
				'sidebar'     => $this->post_type . '-primary',
				'sidebar-alt' => $this->post_type . '-primary-secondary',
			);

		}

		$widgets = $this->swap_widgets( $widgets, $sidebars );

		return $widgets;

	}

	/**
	 * Take the $widgets array and swap the contents of each widget area with a custom widget area, if specified.
	 *
	 * @since 2.1.0
	 */
	private function swap_widgets( $widgets, $sidebars ) {

		if ( is_admin() ) {
			return $widgets;
		}

		foreach ( (array) $sidebars as $old_sidebar => $new_sidebar ) {

			if ( ! is_registered_sidebar( $old_sidebar ) ) {
				continue;
			}

			if ( $new_sidebar && ! empty( $widgets[ $new_sidebar ] ) ) {
				$widgets[ $old_sidebar ] = $widgets[ $new_sidebar ];
			} else {
				$widgets[ $new_sidebar ] = $widgets[ $old_sidebar ];
			}

		}

		return $widgets;

	}

	private function do_sidebar( $id ) {
		if ( function_exists( 'genesis_widget_area' ) ) {
			genesis_widget_area( $this->post_type . '-' . $id );
		} else {
			dynamic_sidebar( $this->post_type . '-' . $id );
		}
	}

	public function primary_sidebar() {
		$this->do_sidebar( 'primary' );
	}

	public function secondary_sidebar() {
		$this->do_sidebar( 'secondary' );
	}

	/**
	 * Register our sidebars and widgetized areas.
	 *
	 */
	public function widgets_init() {
		$post_type = get_post_type_object( $this->post_type );

		if ( function_exists( 'genesis_register_widget_area' ) ) {
			genesis_register_widget_area( array(
				'name'        => __( $post_type->label . ' Widget Area', SITE_MU_TEXT_DOMAIN ),
				'id'          => $this->post_type . '-primary',
				'description' => sprintf( '%s %s.', __( 'This is the primary sidebar for', SITE_MU_TEXT_DOMAIN ), $post_type->label ),
			) );

			if ( WPS\has_3_column_layout() ) {
				genesis_register_widget_area( array(
					'name'        => __( $post_type->label . ' Widget Area', SITE_MU_TEXT_DOMAIN ),
					'id'          => $this->post_type . '-secondary',
					'description' => sprintf( '%s %s.', __( 'This is the primary sidebar for', SITE_MU_TEXT_DOMAIN ), $post_type->label ),
				) );
			}
		}

	}
}