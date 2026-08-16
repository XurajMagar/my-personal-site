<?php
/**
 * Trek Ways Nepal — theme bootstrap.
 * @package TrekWays
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'TREKWAYS_VERSION', '0.2.0' );
define( 'TREKWAYS_DIR', get_template_directory() );
define( 'TREKWAYS_URI', get_template_directory_uri() );

/* 1. Theme supports + menus ------------------------------------------- */
function trekways_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array( 'height' => 60, 'width' => 200, 'flex-height' => true, 'flex-width' => true ) );

	add_image_size( 'trekways_card', 640, 480, true );
	add_image_size( 'trekways_hero', 1920, 1080, true );

	register_nav_menus( array(
		'primary_left'  => __( 'Primary — Left of logo', 'trekways' ),
		'primary_right' => __( 'Primary — Right of logo', 'trekways' ),
		'footer'        => __( 'Footer menu', 'trekways' ),
	) );
}
add_action( 'after_setup_theme', 'trekways_setup' );

/* 2. Front-end assets -------------------------------------------------- */
function trekways_assets() {
	// Google Fonts — Bricolage Grotesque (display) + Manrope (body)
	wp_enqueue_style( 'trekways-fonts', 'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,700;12..96,800&family=Manrope:wght@400;500;600;700&display=swap', array(), null );

	wp_enqueue_style( 'trekways-style', get_stylesheet_uri(), array( 'trekways-fonts' ), filemtime( TREKWAYS_DIR . '/style.css' ) );

	wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css', array(), '6.4.2' );

	// Tilt library loads on non-touch devices only — zero cost on phones/tablets.
	if ( ! wp_is_mobile() ) {
		wp_enqueue_script( 'vanilla-tilt', TREKWAYS_URI . '/js/vanilla-tilt.js', array(), '1.8.1', true );
	}
	wp_enqueue_script( 'trekways-main', TREKWAYS_URI . '/js/main.js', array(), filemtime( TREKWAYS_DIR . '/js/main.js' ), true );
}
add_action( 'wp_enqueue_scripts', 'trekways_assets' );

/* 3. Feature modules --------------------------------------------------- */
require TREKWAYS_DIR . '/inc/mega-menu.php';
require TREKWAYS_DIR . '/inc/post-types.php';
require TREKWAYS_DIR . '/inc/taxonomies.php';
require TREKWAYS_DIR . '/inc/booking.php';
require TREKWAYS_DIR . '/inc/customizer.php';

/* 4. Flush rewrites on activation ------------------------------------- */
function trekways_flush_rewrites() {
	trekways_register_post_types();
	trekways_register_taxonomies();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'trekways_flush_rewrites' );

/* 5. Helper ------------------------------------------------------------ */
function trekways_meta( $post_id, $key, $default = '' ) {
	$val = get_post_meta( $post_id, $key, true );
	return ( $val === '' || $val === false ) ? $default : $val;
}