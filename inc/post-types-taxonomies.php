<?php
/**
 * Register custom post types and taxonomies.
 *
 * @package UnderstrapChild
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register Business custom post type.
 */
function cff_register_business_post_type() {

	$labels = array(
		'name'                  => __( 'Businesses', 'understrap-child' ),
		'singular_name'         => __( 'Business', 'understrap-child' ),
		'menu_name'             => __( 'Businesses', 'understrap-child' ),
		'name_admin_bar'        => __( 'Business', 'understrap-child' ),
		'add_new'               => __( 'Add New', 'understrap-child' ),
		'add_new_item'          => __( 'Add New Business', 'understrap-child' ),
		'new_item'              => __( 'New Business', 'understrap-child' ),
		'edit_item'             => __( 'Edit Business', 'understrap-child' ),
		'view_item'             => __( 'View Business', 'understrap-child' ),
		'all_items'             => __( 'All Businesses', 'understrap-child' ),
		'search_items'          => __( 'Search Businesses', 'understrap-child' ),
		'parent_item_colon'     => __( 'Parent Businesses:', 'understrap-child' ),
		'not_found'             => __( 'No businesses found.', 'understrap-child' ),
		'not_found_in_trash'    => __( 'No businesses found in Trash.', 'understrap-child' ),
		'featured_image'        => __( 'Business Logo', 'understrap-child' ),
		'set_featured_image'    => __( 'Set business logo', 'understrap-child' ),
		'remove_featured_image' => __( 'Remove business logo', 'understrap-child' ),
		'use_featured_image'    => __( 'Use as business logo', 'understrap-child' ),
		'archives'              => __( 'Business Archives', 'understrap-child' ),
		'insert_into_item'      => __( 'Insert into business', 'understrap-child' ),
		'uploaded_to_this_item' => __( 'Uploaded to this business', 'understrap-child' ),
		'filter_items_list'     => __( 'Filter businesses list', 'understrap-child' ),
		'items_list_navigation' => __( 'Businesses list navigation', 'understrap-child' ),
		'items_list'            => __( 'Businesses list', 'understrap-child' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
		'show_in_admin_bar'  => true,
		'show_in_rest'       => true,
		'menu_position'      => 22,
		'menu_icon'          => 'dashicons-store',
		'has_archive'        => true,
		'rewrite'            => array(
			'slug'       => 'business-directory',
			'with_front' => false,
		),
		'supports'           => array(
			'title',
			'editor',
			'thumbnail',
			'excerpt',
			'revisions',
		),
		'taxonomies'         => array( 'business_category' ),
		'hierarchical'       => false,
		'exclude_from_search'=> false,
		'capability_type'    => 'post',
		'menu_position'      => 22,
	);

	register_post_type( 'business', $args );
}
add_action( 'init', 'cff_register_business_post_type' );

/**
 * Register Business Category taxonomy.
 */
function cff_register_business_category_taxonomy() {

	$labels = array(
		'name'              => __( 'Business Categories', 'understrap-child' ),
		'singular_name'     => __( 'Business Category', 'understrap-child' ),
		'search_items'      => __( 'Search Business Categories', 'understrap-child' ),
		'all_items'         => __( 'All Business Categories', 'understrap-child' ),
		'parent_item'       => __( 'Parent Business Category', 'understrap-child' ),
		'parent_item_colon' => __( 'Parent Business Category:', 'understrap-child' ),
		'edit_item'         => __( 'Edit Business Category', 'understrap-child' ),
		'update_item'       => __( 'Update Business Category', 'understrap-child' ),
		'add_new_item'      => __( 'Add New Business Category', 'understrap-child' ),
		'new_item_name'     => __( 'New Business Category Name', 'understrap-child' ),
		'menu_name'         => __( 'Business Categories', 'understrap-child' ),
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'public'            => true,
		'publicly_queryable'=> true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => false,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
		'rewrite'           => array(
			'slug'       => 'business-category',
			'with_front' => false,
		),
	);

	register_taxonomy( 'business_category', array( 'business' ), $args );
}
add_action( 'init', 'cff_register_business_category_taxonomy' );