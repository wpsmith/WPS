<?php

namespace WPS\PostTypes;

use WPS\Core;
use StoutLogic\AcfBuilder\FieldsBuilder;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Service extends Core\Post_Type {

	public $post_type = 'service';
	public $remove_post_type_meta = true;

	public function core_acf_fields( $fields ) {
		$content = new FieldsBuilder( $this->post_type );
		$content
			->addText( 'slide_number_value', array(
				'label'       => __( 'Slide Number Value', SITE_MU_TEXT_DOMAIN ),
				'description' => __( 'Define slide order. Ex. 1,2,3,4,...', SITE_MU_TEXT_DOMAIN ),
			) )
			->addUrl( 'video', array( 'label' => 'Video URL' ) )
			->setLocation( 'post_type', '==', $this->post_type );

		$fields->builder[] = $content;
	}

	/**
	 * Register custom post type
	 */
	public function create_post_type() {

		$labels   = array(
			'name'                  => _x( 'Services', 'Post Type General Name', SITE_MU_TEXT_DOMAIN ),
			'singular_name'         => _x( 'Service', 'Post Type Singular Name', SITE_MU_TEXT_DOMAIN ),
			'menu_name'             => __( 'Services', SITE_MU_TEXT_DOMAIN ),
			'name_admin_bar'        => __( 'Services', SITE_MU_TEXT_DOMAIN ),
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
			'slug'       => $this->post_type,
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
			'genesis-scripts',
			'genesis-layouts',
			'genesis-cpt-archives-settings',
			'genesis-simple-sidebars',
		);
		$args     = array(
			'label'               => __( 'Services', SITE_MU_TEXT_DOMAIN ),
			'description'         => __( 'For Services', SITE_MU_TEXT_DOMAIN ),
			'labels'              => $labels,
			'supports'            => $supports,
			'hierarchical'        => true,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 6.9,
			'menu_icon'           => 'dashicons-star-filled',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'rewrite'             => $rewrite,
			'capability_type'     => 'page',
			'show_in_rest'        => true,
		);
		register_post_type( $this->post_type, $args );

//	new WPS\Schema\Entry_Schema( $this->post_type, 'video' );

//	new WPS\Templates\Simple_Sidebars( $this->post_type );
	}

	/**
	 * Register Custom Taxonomy
	 */
	function create_taxonomy() {

		$labels  = array(
			'name'                       => _x( 'Types', 'Taxonomy General Name', SITE_MU_TEXT_DOMAIN ),
			'singular_name'              => _x( 'Type', 'Taxonomy Singular Name', SITE_MU_TEXT_DOMAIN ),
			'menu_name'                  => __( 'Types', SITE_MU_TEXT_DOMAIN ),
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
			'slug'         => 'service-type',
			'with_front'   => true,
			'hierarchical' => true,
		);
		$args    = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'rewrite'           => $rewrite,
			'show_in_rest'      => true,
		);
		register_taxonomy( 'service-type', array( $this->post_type ), $args );

	}

	// manage columns
	public function manage_posts_columns( $columns ) {
		return array(
			'cb'        => '<input type="checkbox" />',
			'title'     => __( 'Title', SITE_MU_TEXT_DOMAIN ),
			'thumbnail' => __( 'Thumbnail', SITE_MU_TEXT_DOMAIN ),
			'date'      => __( 'Date', SITE_MU_TEXT_DOMAIN ),
		);
	}

	public function manage_posts_custom_column( $column, $post_id ) {
		switch ( $column ) {
			case 'thumbnail' :
				if ( has_post_thumbnail( $post_id ) ) {
					echo get_the_post_thumbnail( $post_id, array( 50, 50 ) );
				}
				break;
		}
	}
}
