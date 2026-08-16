<?php
/**
 * Taxonomies for Trek Ways trips.
 *
 * Mirrors the trekwaysnepal.com information architecture:
 *   - destination  : hierarchical. Country (Nepal) as parent, activity
 *                    ("Trekking in Nepal", "Tour in Nepal") as children.
 *                    URLs: /nepal , /nepal/trekking-in-nepal
 *   - region       : Everest / Annapurna / Langtang (carried over from
 *                    Desire Adventure). URLs: /region/everest-region
 *   - travel_style : Family / Solo / Group / Corporate / Religious.
 *                    URLs: /travel-style/family-trip
 *
 * @package TrekWays
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function trekways_register_taxonomies() {

	/* Destination — hierarchical (country -> activity) */
	register_taxonomy( 'destination', array( 'trip' ), array(
		'labels' => array(
			'name'          => __( 'Destinations', 'trekways' ),
			'singular_name' => __( 'Destination', 'trekways' ),
			'menu_name'     => __( 'Destinations', 'trekways' ),
		),
		'hierarchical' => true,               // acts like categories
		'public'       => true,
		'show_admin_column' => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => '', 'with_front' => false, 'hierarchical' => true ),
		// slug '' + hierarchical gives /nepal and /nepal/trekking-in-nepal
	) );

	/* Region — flat-ish taxonomy under /region/ */
	register_taxonomy( 'region', array( 'trip' ), array(
		'labels' => array(
			'name'          => __( 'Regions', 'trekways' ),
			'singular_name' => __( 'Region', 'trekways' ),
			'menu_name'     => __( 'Regions', 'trekways' ),
		),
		'hierarchical' => true,
		'public'       => true,
		'show_admin_column' => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'region', 'with_front' => false ),
	) );

	/* Travel style */
	register_taxonomy( 'travel_style', array( 'trip' ), array(
		'labels' => array(
			'name'          => __( 'Travel Styles', 'trekways' ),
			'singular_name' => __( 'Travel Style', 'trekways' ),
			'menu_name'     => __( 'Travel Styles', 'trekways' ),
		),
		'hierarchical' => true,
		'public'       => true,
		'show_admin_column' => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'travel-style', 'with_front' => false ),
	) );
}
add_action( 'init', 'trekways_register_taxonomies' );
