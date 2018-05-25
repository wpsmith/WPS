<?php

namespace Site\PostTypes;

use WPS;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Feature extends WPS\Core\Post_Type {

	/**
	 * Post Type registered name
	 *
	 * @var string
	 */
	public $post_type = 'feature';

	/**
	 * Plural Post Type registered name
	 *
	 * @var string
	 */
	public $plural = 'features';

	/**
	 * What metaboxes to remove.
	 *
	 * Supports 'genesis-cpt-archives-layout-settings', 'genesis-cpt-archives-seo-settings',
	 * and 'genesis-cpt-archives-settings'.
	 *
	 * @var array
	 */
	public $remove_metaboxes = array(
		'genesis-cpt-archives-layout-settings'
	);

	/**
	 * Whether to remove meta functions from post type display.
	 *
	 * @var bool
	 */
	public $remove_post_type_meta = true;

	/**
	 * Whether to create a related types taxonomy.
	 *
	 * @var bool
	 */
	public $types = true;

	/**
	 * Register custom post type
	 */
	public function create_post_type() {

		$this->register_post_type( array(
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 6.9,
			'menu_icon'           => 'dashicons-star-filled',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'register_rating'     => 'add_rating_metabox',
			'show_in_rest'        => true,
		) );

	}

	/**
	 * Gets supports array.
	 *
	 * @return array Array of post type supports.
	 */
	protected function get_supports() {
		return array(
			'title',
			'editor',
			'excerpt',
			'thumbnail',
		);
	}

}
