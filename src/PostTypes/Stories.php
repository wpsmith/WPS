<?php

namespace WPS\PostTypes;

use WPS\Core;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Stories extends Core\Post_Type {

	public function __construct() {
		add_action( 'init', array( $this, 'create_post_type' ), 0 );
		add_action( 'init', array( $this, 'create_taxonomy' ), 0 );
		add_action( 'the_content', array( $this, 'the_content' ) );
		add_action( 'genesis_header', array( $this, 'remove_post_type_meta' ) );

		// For provider specific stories
		add_filter( 'genesis_custom_loop_args', array( $this, 'genesis_custom_loop_args' ), 10, 3 );
	}

	/**
	 * Register custom post type
	 */
	public function create_post_type() {

		$labels   = array(
			'name'                  => _x( 'Stories', 'Post Type General Name', SITE_MU_TEXT_DOMAIN ),
			'singular_name'         => _x( 'Story', 'Post Type Singular Name', SITE_MU_TEXT_DOMAIN ),
			'menu_name'             => __( 'Stories', SITE_MU_TEXT_DOMAIN ),
			'name_admin_bar'        => __( 'Stories', SITE_MU_TEXT_DOMAIN ),
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
			'featured_image'        => __( 'Featured Image', SITE_MU_TEXT_DOMAIN ),
			'set_featured_image'    => __( 'Set featured image', SITE_MU_TEXT_DOMAIN ),
			'remove_featured_image' => __( 'Remove featured image', SITE_MU_TEXT_DOMAIN ),
			'use_featured_image'    => __( 'Use as featured image', SITE_MU_TEXT_DOMAIN ),
			'insert_into_item'      => __( 'Insert into item', SITE_MU_TEXT_DOMAIN ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', SITE_MU_TEXT_DOMAIN ),
			'items_list'            => __( 'Items list', SITE_MU_TEXT_DOMAIN ),
			'items_list_navigation' => __( 'Items list navigation', SITE_MU_TEXT_DOMAIN ),
			'filter_items_list'     => __( 'Filter items list', SITE_MU_TEXT_DOMAIN ),
		);
		$rewrite  = array(
			'slug'       => 'story',
			'with_front' => true,
			'pages'      => true,
			'feeds'      => true,
		);
		$supports = array(
			'title',
			'editor',
			'excerpt',
			'thumbnail',
			'genesis-seo',
//		'genesis-scripts',
//		'genesis-layouts',
//		'genesis-cpt-archives-settings',
//		'genesis-simple-sidebars',
		);
		$args     = array(
			'label'               => __( 'Stories', SITE_MU_TEXT_DOMAIN ),
			'description'         => __( 'For Stories', SITE_MU_TEXT_DOMAIN ),
			'labels'              => $labels,
			'supports'            => $supports,
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 6.9,
			'menu_icon'           => 'dashicons-testimonial',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => 'stories',
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'rewrite'             => $rewrite,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
		);
		register_post_type( 'story', $args );

//	new WPS\Schema\Entry_Schema( 'story', 'video' );

//	new WPS\Templates\Simple_Sidebars( 'story' );
	}

	/**
	 * Register Custom Taxonomy
	 */
	function create_taxonomy() {

		$labels  = array(
			'name'                       => _x( 'Providers', 'Taxonomy General Name', SITE_MU_TEXT_DOMAIN ),
			'singular_name'              => _x( 'Provider', 'Taxonomy Singular Name', SITE_MU_TEXT_DOMAIN ),
			'menu_name'                  => __( 'Providers', SITE_MU_TEXT_DOMAIN ),
			'all_items'                  => __( 'All Items', SITE_MU_TEXT_DOMAIN ),
			'parent_item'                => __( 'Parent Item', SITE_MU_TEXT_DOMAIN ),
			'parent_item_colon'          => __( 'Parent Item:', SITE_MU_TEXT_DOMAIN ),
			'new_item_name'              => __( 'New Item Name', SITE_MU_TEXT_DOMAIN ),
			'add_new_item'               => __( 'Add New Item', SITE_MU_TEXT_DOMAIN ),
			'edit_item'                  => __( 'Edit Item', SITE_MU_TEXT_DOMAIN ),
			'update_item'                => __( 'Update Item', SITE_MU_TEXT_DOMAIN ),
			'view_item'                  => __( 'View Item', SITE_MU_TEXT_DOMAIN ),
			'separate_items_with_commas' => __( 'Separate items with commas', SITE_MU_TEXT_DOMAIN ),
			'add_or_remove_items'        => __( 'Add or remove items', SITE_MU_TEXT_DOMAIN ),
			'choose_from_most_used'      => __( 'Choose from the most used', SITE_MU_TEXT_DOMAIN ),
			'popular_items'              => __( 'Popular Items', SITE_MU_TEXT_DOMAIN ),
			'search_items'               => __( 'Search Items', SITE_MU_TEXT_DOMAIN ),
			'not_found'                  => __( 'Not Found', SITE_MU_TEXT_DOMAIN ),
			'no_terms'                   => __( 'No items', SITE_MU_TEXT_DOMAIN ),
			'items_list'                 => __( 'Items list', SITE_MU_TEXT_DOMAIN ),
			'items_list_navigation'      => __( 'Items list navigation', SITE_MU_TEXT_DOMAIN ),
		);
		$rewrite = array(
			'slug'         => 'provider',
			'with_front'   => true,
			'hierarchical' => false,
		);
		$args    = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
			'rewrite'           => $rewrite,
			'show_in_rest'      => true,
		);
		register_taxonomy( 'provider', array( 'story' ), $args );

	}

	public function remove_post_type_meta() {
		if ( 'story' === get_post_type() ) {
			WPS\remove_post_type_meta();
		}
	}

	public function pre_get_posts( $query ) {
		if ( !$query->is_main_query() || is_admin() ) {
			return;
		}

//		WPS\printr($query);
	}

	public function the_content( $content ) {
		if ( 'story' === get_post_type() ) {
			return get_the_excerpt();
		}
		return $content;
	}

}
