<?php

namespace Site\PostTypes;
use WPS\Core;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Testimonial extends Core\Post_Type {

	/**
	 * Post Type registered name
	 *
	 * @var string
	 */
	public $post_type = 'testimonial';

	/**
	 * Singular Post Type registered name
	 *
	 * @var string
	 */
	public $singular = 'testimonial';

	/**
	 * Plural Post Type registered name
	 *
	 * @var string
	 */
	public $plural = 'testimonials';

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
	public $remove_post_type_entry_meta = true;

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
//			'label'               => __( 'Testimonials', SITE_MU_TEXT_DOMAIN ),
//			'description'         => __( 'For Testimonials', SITE_MU_TEXT_DOMAIN ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 6.9,
			'menu_icon'           => 'dashicons-format-quote',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'register_rating'		=> 'add_rating_metabox',
			'show_in_rest'        => true,
		) );
	}

	/**
	 * Register custom post type
	 */
	public function create_post_type_bak() {

		$labels   = array(
			'name'                  => _x( 'Testimonials', 'Post Type General Name', SITE_MU_TEXT_DOMAIN ),
			'singular_name'         => _x( 'Testimonial', 'Post Type Singular Name', SITE_MU_TEXT_DOMAIN ),
			'menu_name'             => __( 'Testimonials', SITE_MU_TEXT_DOMAIN ),
			'name_admin_bar'        => __( 'Testimonials', SITE_MU_TEXT_DOMAIN ),
			'archives'              => __( 'Item Archives', SITE_MU_TEXT_DOMAIN ),
			'attributes'            => __( 'Item Attributes', SITE_MU_TEXT_DOMAIN ),
			'parent_item_colon'     => __( 'Parent Item:', SITE_MU_TEXT_DOMAIN ),
			'all_items'             => __( 'All Items', SITE_MU_TEXT_DOMAIN ),
			'add_new_item'          => __( 'Add New Item', SITE_MU_TEXT_DOMAIN ),
			'add_new'               => __( 'Add New', SITE_MU_TEXT_DOMAIN ),
			'new_item'              => __( 'New Item', SITE_MU_TEXT_DOMAIN ),
			'edit_item'             => __( 'Edit Item', SITE_MU_TEXT_DOMAIN ),
			'update_item'           => __( 'Update Item', SITE_MU_TEXT_DOMAIN ),
			'view_item'             => __( 'View Item', SITE_MU_TEXT_DOMAIN ),
			'view_items'            => __( 'View Items', SITE_MU_TEXT_DOMAIN ),
			'search_items'          => __( 'Search Item', SITE_MU_TEXT_DOMAIN ),
			'not_found'             => __( 'Not found', SITE_MU_TEXT_DOMAIN ),
			'not_found_in_trash'    => __( 'Not found in Trash', SITE_MU_TEXT_DOMAIN ),
			'testimoniald_image'        => __( 'Testimoniald Image', SITE_MU_TEXT_DOMAIN ),
			'set_testimoniald_image'    => __( 'Set testimoniald image', SITE_MU_TEXT_DOMAIN ),
			'remove_testimoniald_image' => __( 'Remove testimoniald image', SITE_MU_TEXT_DOMAIN ),
			'use_testimoniald_image'    => __( 'Use as testimoniald image', SITE_MU_TEXT_DOMAIN ),
			'insert_into_item'      => __( 'Insert into item', SITE_MU_TEXT_DOMAIN ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', SITE_MU_TEXT_DOMAIN ),
			'items_list'            => __( 'Items list', SITE_MU_TEXT_DOMAIN ),
			'items_list_navigation' => __( 'Items list navigation', SITE_MU_TEXT_DOMAIN ),
			'filter_items_list'     => __( 'Filter items list', SITE_MU_TEXT_DOMAIN ),
		);
		$supports = array(
			'title',
			'editor',
//			'excerpt',
			'thumbnail',
//		'genesis-seo',
//		'genesis-scripts',
//		'genesis-layouts',
//		'genesis-cpt-archives-settings',
//		'genesis-simple-sidebars',
		);
		$args     = array(
			'label'               => __( 'Testimonials', SITE_MU_TEXT_DOMAIN ),
			'description'         => __( 'For Testimonials', SITE_MU_TEXT_DOMAIN ),
			'labels'              => $labels,
			'supports'            => $supports,
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 6.9,
			'menu_icon'           => 'dashicons-format-quote',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'register_rating'		=> 'add_rating_metabox',
			'show_in_rest'        => true,
		);
		register_post_type( 'testimonial', $args );

//	new WPS\Schema\Entry_Schema( 'testimonial', 'video' );

//	new WPS\Templates\Simple_Sidebars( 'testimonial' );
	}

	protected function get_supports() {
		return array(
			'title',
			'editor',
//			'excerpt',
			'thumbnail',
//		'genesis-seo',
//		'genesis-scripts',
//		'genesis-layouts',
//		'genesis-cpt-archives-settings',
//		'genesis-simple-sidebars',
		);
	}

}
