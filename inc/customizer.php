<?php
/**
 * Customizer controls for Trek Ways.
 * @package TrekWays
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function trekways_customize_register( $wp_customize ) {

	/* ---------------- Branding & Colors ---------------- */
	$wp_customize->add_section( 'trekways_branding', array(
		'title'    => __( 'Branding & Colors', 'trekways' ),
		'priority' => 25,
	) );

	$wp_customize->add_setting( 'trekways_logo_color', array( 'default' => '#F5F2FC', 'sanitize_callback' => 'sanitize_hex_color' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'trekways_logo_color_ctrl', array(
		'label' => __( 'Logo text color', 'trekways' ), 'section' => 'trekways_branding', 'settings' => 'trekways_logo_color',
	) ) );

	$wp_customize->add_setting( 'trekways_logo_sub_color', array( 'default' => '#B9A6F5', 'sanitize_callback' => 'sanitize_hex_color' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'trekways_logo_sub_color_ctrl', array(
		'label' => __( 'Logo subtitle (NEPAL) color', 'trekways' ), 'section' => 'trekways_branding', 'settings' => 'trekways_logo_sub_color',
	) ) );

	/* ---------------- Hero Banner ---------------- */
	/* ---------------- Help / Call Numbers ---------------- */
	$wp_customize->add_section( 'trekways_cta', array( 'title' => __( 'Help / Call Numbers', 'trekways' ), 'priority' => 32 ) );
	$wp_customize->add_setting( 'trekways_cta_enable', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'trekways_cta_enable_ctrl', array( 'label' => __( 'Show call numbers in navbar', 'trekways' ), 'section' => 'trekways_cta', 'settings' => 'trekways_cta_enable', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'trekways_cta_label', array( 'default' => 'Need help? Call us', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'trekways_cta_label_ctrl', array( 'label' => __( 'Small label above numbers', 'trekways' ), 'section' => 'trekways_cta', 'settings' => 'trekways_cta_label', 'type' => 'text' ) );
	// Nepal
	$wp_customize->add_setting( 'trekways_cta_np_number', array( 'default' => '+977 9841666232', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'trekways_cta_np_number_ctrl', array( 'label' => __( 'Nepal number', 'trekways' ), 'section' => 'trekways_cta', 'settings' => 'trekways_cta_np_number', 'type' => 'text' ) );
	$wp_customize->add_setting( 'trekways_cta_np_name', array( 'default' => 'Tika', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'trekways_cta_np_name_ctrl', array( 'label' => __( 'Nepal agent name (optional)', 'trekways' ), 'section' => 'trekways_cta', 'settings' => 'trekways_cta_np_name', 'type' => 'text' ) );
	$wp_customize->add_setting( 'trekways_cta_np_flag', array( 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'trekways_cta_np_flag_ctrl', array( 'label' => __( 'Nepal flag (optional — overrides default)', 'trekways' ), 'section' => 'trekways_cta', 'settings' => 'trekways_cta_np_flag' ) ) );
	// USA
	$wp_customize->add_setting( 'trekways_cta_us_number', array( 'default' => '+1-651-703-8181', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'trekways_cta_us_number_ctrl', array( 'label' => __( 'USA number', 'trekways' ), 'section' => 'trekways_cta', 'settings' => 'trekways_cta_us_number', 'type' => 'text' ) );
	$wp_customize->add_setting( 'trekways_cta_us_name', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'trekways_cta_us_name_ctrl', array( 'label' => __( 'USA agent name (optional)', 'trekways' ), 'section' => 'trekways_cta', 'settings' => 'trekways_cta_us_name', 'type' => 'text' ) );
	$wp_customize->add_setting( 'trekways_cta_us_flag', array( 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'trekways_cta_us_flag_ctrl', array( 'label' => __( 'USA flag (optional — overrides default)', 'trekways' ), 'section' => 'trekways_cta', 'settings' => 'trekways_cta_us_flag' ) ) );

	/* Payment / security badges */
	$wp_customize->add_setting( 'trekways_pay_enable', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'trekways_pay_enable_ctrl', array( 'label' => __( 'Show payment/security badges', 'trekways' ), 'section' => 'trekways_cta', 'settings' => 'trekways_pay_enable', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'trekways_pay_url', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( 'trekways_pay_url_ctrl', array( 'label' => __( 'Badges link URL (optional)', 'trekways' ), 'section' => 'trekways_cta', 'settings' => 'trekways_pay_url', 'type' => 'url' ) );
		$wp_customize->add_setting( 'trekways_pay_label', array( 'default' => 'Online Payment', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'trekways_pay_label_ctrl', array( 'label' => __( 'Text beside badge', 'trekways' ), 'section' => 'trekways_cta', 'settings' => 'trekways_pay_label', 'type' => 'text' ) );
	$wp_customize->add_setting( 'trekways_pay_1', array( 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'trekways_pay_1_ctrl', array( 'label' => __( 'Payment badge image', 'trekways' ), 'section' => 'trekways_cta', 'settings' => 'trekways_pay_1' ) ) );

	$wp_customize->add_section( 'trekways_hero_section', array(
		'title'    => __( 'Hero Banner', 'trekways' ),
		'priority' => 30,
	) );

	$wp_customize->add_setting( 'trekways_hero_poster', array( 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'trekways_hero_poster_ctrl', array(
		'label' => __( 'Poster image (shown before/without video)', 'trekways' ), 'section' => 'trekways_hero_section', 'settings' => 'trekways_hero_poster',
	) ) );

	$wp_customize->add_setting( 'trekways_hero_video', array( 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'trekways_hero_video_ctrl', array(
		'label' => __( 'PC / desktop video', 'trekways' ), 'description' => __( 'MP4. Screens ≥ 1024px.', 'trekways' ),
		'section' => 'trekways_hero_section', 'settings' => 'trekways_hero_video', 'mime_type' => 'video',
	) ) );

	$wp_customize->add_setting( 'trekways_hero_video_mobile', array( 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'trekways_hero_video_mobile_ctrl', array(
		'label' => __( 'Mobile video', 'trekways' ), 'description' => __( 'Screens < 1024px. Empty = poster on mobile.', 'trekways' ),
		'section' => 'trekways_hero_section', 'settings' => 'trekways_hero_video_mobile', 'mime_type' => 'video',
	) ) );

	/* Awards */
	$wp_customize->add_setting( 'trekways_awards_label', array( 'default' => 'Award-winning', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'trekways_awards_label_ctrl', array(
		'label' => __( 'Awards label (small text above badges — leave empty to hide)', 'trekways' ), 'section' => 'trekways_hero_section', 'settings' => 'trekways_awards_label', 'type' => 'text',
	) );

	foreach ( array( 1, 2, 3 ) as $i ) {
		$mid = ( $i === 2 ) ? ' (center — larger)' : '';
		$wp_customize->add_setting( "trekways_award_{$i}", array( 'sanitize_callback' => 'esc_url_raw' ) );
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, "trekways_award_{$i}_ctrl", array(
			'label' => sprintf( __( 'Award %d%s', 'trekways' ), $i, $mid ), 'section' => 'trekways_hero_section', 'settings' => "trekways_award_{$i}",
		) ) );
	}

	$wp_customize->add_setting( 'trekways_hero_search_ph', array( 'default' => __( 'Search treks, tours, regions…', 'trekways' ), 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'trekways_hero_search_ph_ctrl', array( 'label' => __( 'Search placeholder text', 'trekways' ), 'section' => 'trekways_hero_section', 'settings' => 'trekways_hero_search_ph', 'type' => 'text' ) );

	$wp_customize->add_setting( 'trekways_hero_btn1_text', array( 'default' => 'Plan My Trip', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'trekways_hero_btn1_text_ctrl', array( 'label' => __( 'Button 1 text', 'trekways' ), 'section' => 'trekways_hero_section', 'settings' => 'trekways_hero_btn1_text', 'type' => 'text' ) );
	$wp_customize->add_setting( 'trekways_hero_btn1_url', array( 'default' => '#', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( 'trekways_hero_btn1_url_ctrl', array( 'label' => __( 'Button 1 link', 'trekways' ), 'section' => 'trekways_hero_section', 'settings' => 'trekways_hero_btn1_url', 'type' => 'url' ) );

	$wp_customize->add_setting( 'trekways_hero_btn2_text', array( 'default' => 'View Packages', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'trekways_hero_btn2_text_ctrl', array( 'label' => __( 'Button 2 text', 'trekways' ), 'section' => 'trekways_hero_section', 'settings' => 'trekways_hero_btn2_text', 'type' => 'text' ) );
	$wp_customize->add_setting( 'trekways_hero_btn2_url', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( 'trekways_hero_btn2_url_ctrl', array( 'label' => __( 'Button 2 link (default: all trips)', 'trekways' ), 'section' => 'trekways_hero_section', 'settings' => 'trekways_hero_btn2_url', 'type' => 'url' ) );
}
add_action( 'customize_register', 'trekways_customize_register' );

/* Inline CSS driven by Customizer color choices */
function trekways_inline_customizer_css() {
	$name = get_theme_mod( 'trekways_logo_color', '#F5F2FC' );
	$sub  = get_theme_mod( 'trekways_logo_sub_color', '#B9A6F5' );
	printf(
		'<style id="trekways-inline">.tw-brand__name{color:%s}.tw-brand__sub{color:%s}</style>' . "\n",
		esc_attr( $name ), esc_attr( $sub )
	);
}
add_action( 'wp_head', 'trekways_inline_customizer_css', 20 );