<?php
/**
 * Custom post types for Trek Ways.
 *
 * @package TrekWays
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Register all CPTs. Called on init and on theme activation.
 */
function trekways_register_post_types() {

	/* ---------------------------------------------------------------
	 * TRIPS  (packages)
	 * Public. Permalink is root-level (/everest-base-camp-trek) to
	 * mirror trekwaysnepal.com — see the rewrite filter below.
	 * ------------------------------------------------------------- */
	register_post_type( 'trip', array(
		'labels' => array(
			'name'          => __( 'Trips', 'trekways' ),
			'singular_name' => __( 'Trip', 'trekways' ),
			'add_new_item'  => __( 'Add New Trip', 'trekways' ),
			'edit_item'     => __( 'Edit Trip', 'trekways' ),
			'menu_name'     => __( 'Trips', 'trekways' ),
		),
		'public'       => true,
		'has_archive'  => 'trips',
		'menu_icon'    => 'dashicons-palmtree',
		'menu_position'=> 5,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'rewrite'      => array( 'slug' => 'trip', 'with_front' => false ), // placeholder; real URL is root (below)
		'show_in_rest' => true,
	) );

	/* ---------------------------------------------------------------
	 * TEAM MEMBER
	 * ------------------------------------------------------------- */
		/* ---------------------------------------------------------------
	 * TEAM MEMBER
	 * Admin-only list that feeds the "Meet the team" slider on the
	 * homepage. No public single pages: they had no template.
	 * ------------------------------------------------------------- */
	register_post_type( 'team_member', array(
		'labels' => array(
			'name'                  => __( 'Team', 'trekways' ),
			'singular_name'         => __( 'Team Member', 'trekways' ),
			'menu_name'             => __( 'Team', 'trekways' ),
			'all_items'             => __( 'All Team Members', 'trekways' ),
			'add_new'               => __( 'Add Team Member', 'trekways' ),
			'add_new_item'          => __( 'Add Team Member', 'trekways' ),
			'edit_item'             => __( 'Edit Team Member', 'trekways' ),
			'new_item'              => __( 'New Team Member', 'trekways' ),
			'view_item'             => __( 'View Team Member', 'trekways' ),
			'search_items'          => __( 'Search Team', 'trekways' ),
			'not_found'             => __( 'No team members yet.', 'trekways' ),
			'not_found_in_trash'    => __( 'No team members in Trash.', 'trekways' ),
			'item_published'        => __( 'Team member published.', 'trekways' ),
			'item_updated'          => __( 'Team member updated.', 'trekways' ),
			'featured_image'        => __( 'Photo', 'trekways' ),
			'set_featured_image'    => __( 'Set photo', 'trekways' ),
			'remove_featured_image' => __( 'Remove photo', 'trekways' ),
			'use_featured_image'    => __( 'Use as photo', 'trekways' ),
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'menu_icon'           => 'dashicons-groups',
		'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
	) );

	/* ---------------------------------------------------------------
	 * BOOKING  (private — admin only)
	 * Every submitted booking/enquiry is stored here as a post so you
	 * get a searchable, filterable Bookings list in wp-admin, AND the
	 * emails still go out. Not publicly viewable.
	 * ------------------------------------------------------------- */
	register_post_type( 'booking', array(
		'labels' => array(
			'name'          => __( 'Bookings', 'trekways' ),
			'singular_name' => __( 'Booking', 'trekways' ),
			'menu_name'     => __( 'Bookings', 'trekways' ),
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'menu_icon'           => 'dashicons-tickets-alt',
		'menu_position'       => 6,
		'supports'            => array( 'title' ),
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
	) );
}
add_action( 'init', 'trekways_register_post_types' );

/* -------------------------------------------------------------------------
 * Root-level permalinks for trips.
 *
 * WordPress can't natively put a CPT at the site root, so we:
 *   1. rewrite the printed permalink to /{slug}/
 *   2. add a low-priority catch-all rule so /{slug}/ resolves to a trip
 * Pages and blog posts are matched first, so they win any slug clash.
 *
 * NOTE: keep blog posts under /blog/ (Settings > Permalinks) to avoid
 * colliding with this catch-all.
 * ---------------------------------------------------------------------- */
function trekways_trip_permalink( $post_link, $post ) {
	if ( $post->post_type === 'trip' && $post->post_status === 'publish' ) {
		return home_url( '/' . $post->post_name . '/' );
	}
	return $post_link;
}
add_filter( 'post_type_link', 'trekways_trip_permalink', 10, 2 );

function trekways_trip_rewrite_rule() {
	add_rewrite_rule( '^([^/]+)/?$', 'index.php?trip=$matches[1]', 'bottom' );
}
add_action( 'init', 'trekways_trip_rewrite_rule' );
