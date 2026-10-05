<?php
/**
 * About section: intro text, two photos, and a "Meet the team" slider
 * fed by the team_member post type.
 *
 * @package TrekWays
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ---------------------------------------------------------------
 * Team member: manual ordering, square photo size, Role field.
 * ------------------------------------------------------------- */

/**
 * Let team members be ordered with the "Order" box (menu_order).
 */
function trekways_team_supports() {
	add_post_type_support( 'team_member', 'page-attributes' );
}
add_action( 'init', 'trekways_team_supports', 20 );

/**
 * Square crop for the round team photos. Applies to new uploads;
 * regenerate thumbnails for photos uploaded before this was added.
 */
function trekways_team_image_size() {
	add_image_size( 'trekways_team', 320, 320, true );
}
add_action( 'after_setup_theme', 'trekways_team_image_size', 20 );

/**
 * Role meta box.
 */
function trekways_team_meta_box() {
	add_meta_box( 'trekways_team_role', __( 'Role', 'trekways' ), 'trekways_team_meta_box_render', 'team_member', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'trekways_team_meta_box' );

function trekways_team_meta_box_render( $post ) {
	wp_nonce_field( 'trekways_save_team', 'trekways_team_nonce' );
	$role = get_post_meta( $post->ID, '_team_role', true );
	echo '<label for="trekways_team_role_input" class="screen-reader-text">' . esc_html__( 'Role', 'trekways' ) . '</label>';
	echo '<input type="text" id="trekways_team_role_input" name="_team_role" value="' . esc_attr( $role ) . '" style="width:100%" placeholder="' . esc_attr__( 'e.g. Senior trekking guide', 'trekways' ) . '">';
	echo '<p class="description">' . esc_html__( 'Shown under the name. Set the photo as the Featured Image, and the order in "Page Attributes".', 'trekways' ) . '</p>';
}

function trekways_team_meta_save( $post_id ) {
	if ( ! isset( $_POST['trekways_team_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['trekways_team_nonce'] ), 'trekways_save_team' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['_team_role'] ) ) {
		update_post_meta( $post_id, '_team_role', sanitize_text_field( wp_unslash( $_POST['_team_role'] ) ) );
	}
}
add_action( 'save_post_team_member', 'trekways_team_meta_save' );

/**
 * Role column in the Team list screen.
 */
function trekways_team_columns( $cols ) {
	$out = array();
	foreach ( $cols as $k => $v ) {
		$out[ $k ] = $v;
		if ( 'title' === $k ) {
			$out['tw_role'] = __( 'Role', 'trekways' );
		}
	}
	return $out;
}
add_filter( 'manage_team_member_posts_columns', 'trekways_team_columns' );

function trekways_team_column_value( $col, $post_id ) {
	if ( 'tw_role' === $col ) {
		echo esc_html( get_post_meta( $post_id, '_team_role', true ) );
	}
}
add_action( 'manage_team_member_posts_custom_column', 'trekways_team_column_value', 10, 2 );

/* ---------------------------------------------------------------
 * Customizer: text, photos, link.
 * ------------------------------------------------------------- */

/**
 * Defaults, kept in one place for the Customizer and the template.
 *
 * @return array
 */
function trekways_about_defaults() {
	return array(
		'eyebrow'    => 'About Trek Ways',
		'title'      => 'A small Kathmandu team that plans treks it would walk itself',
		'p1'         => 'Trek Ways is a licensed trekking company based in Thamel. Our guides grew up in the hills these routes cross, and the person who briefs you in Kathmandu is the one who walks with you.',
		'p2'         => 'We plan around your pace and your dates, not a fixed brochure. Prices list exactly what they include, so there are no extra permits or fees to settle at the trailhead.',
		'caption'    => '',
		'link_label' => 'Read our story',
		'link_url'   => '',
		'team_title' => 'Meet the team',
		'team_intro' => 'The people who plan your trek and walk it with you.',
	);
}

function trekways_about_mod( $key ) {
	$d = trekways_about_defaults();
	return get_theme_mod( 'trekways_about_' . $key, isset( $d[ $key ] ) ? $d[ $key ] : '' );
}

function trekways_about_customizer( $wp_customize ) {
	$d = trekways_about_defaults();

	$wp_customize->add_section( 'trekways_about', array(
		'title'       => __( 'About Section', 'trekways' ),
		'priority'    => 36,
		'description' => __( 'Homepage About block. Team members are managed under Team in the admin menu.', 'trekways' ),
	) );

	$wp_customize->add_setting( 'trekways_about_enable', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'trekways_about_enable_ctrl', array(
		'label' => __( 'Show the About section', 'trekways' ), 'section' => 'trekways_about',
		'settings' => 'trekways_about_enable', 'type' => 'checkbox',
	) );

	$text = array(
		'eyebrow'    => array( __( 'Small text above the title', 'trekways' ), 'text' ),
		'title'      => array( __( 'Title', 'trekways' ), 'text' ),
		'p1'         => array( __( 'First paragraph', 'trekways' ), 'textarea' ),
		'p2'         => array( __( 'Second paragraph', 'trekways' ), 'textarea' ),
		'caption'    => array( __( 'Caption on the large photo (optional)', 'trekways' ), 'text' ),
		'link_label' => array( __( 'Link text', 'trekways' ), 'text' ),
		'link_url'   => array( __( 'Link URL (leave empty to hide the link)', 'trekways' ), 'url' ),
		'team_title' => array( __( 'Team title', 'trekways' ), 'text' ),
		'team_intro' => array( __( 'Team intro line', 'trekways' ), 'text' ),
	);
	foreach ( $text as $key => $c ) {
		$san = 'textarea' === $c[1] ? 'sanitize_textarea_field' : ( 'url' === $c[1] ? 'esc_url_raw' : 'sanitize_text_field' );
		$wp_customize->add_setting( 'trekways_about_' . $key, array( 'default' => $d[ $key ], 'sanitize_callback' => $san ) );
		$wp_customize->add_control( 'trekways_about_' . $key . '_ctrl', array(
			'label' => $c[0], 'section' => 'trekways_about',
			'settings' => 'trekways_about_' . $key, 'type' => $c[1],
		) );
	}

	foreach ( array(
		'img_main'  => __( 'Large photo (portrait works best)', 'trekways' ),
		'img_small' => __( 'Small overlapping photo (square works best)', 'trekways' ),
	) as $key => $label ) {
		$wp_customize->add_setting( 'trekways_about_' . $key, array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'trekways_about_' . $key . '_ctrl', array(
			'label'     => $label,
			'section'   => 'trekways_about',
			'settings'  => 'trekways_about_' . $key,
			'mime_type' => 'image',
		) ) );
	}
}
add_action( 'customize_register', 'trekways_about_customizer' );

/* ---------------------------------------------------------------
 * Render.
 * ------------------------------------------------------------- */

/**
 * One About photo: the chosen attachment or the theme placeholder.
 *
 * @param string $key   img_main or img_small.
 * @param string $size  Image size.
 * @param string $sizes sizes attribute.
 * @return string
 */
function trekways_about_img( $key, $size, $sizes ) {
	$id = (int) get_theme_mod( 'trekways_about_' . $key, 0 );
	if ( $id ) {
		$img = wp_get_attachment_image( $id, $size, false, array( 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => $sizes ) );
		if ( $img ) {
			return $img;
		}
	}
	return '<img src="' . esc_url( TREKWAYS_URI . '/images/trip-placeholder.webp' ) . '" alt="" loading="lazy" decoding="async">';
}

function trekways_about_section() {
	if ( ! get_theme_mod( 'trekways_about_enable', true ) ) {
		return;
	}
	$team = new WP_Query( array(
		'post_type'      => 'team_member',
		'post_status'    => 'publish',
		'posts_per_page' => 30,
		'no_found_rows'  => true,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
	) );
	$caption = trekways_about_mod( 'caption' );
	$link    = trekways_about_mod( 'link_url' );
	$eyebrow = trekways_about_mod( 'eyebrow' );
	$p1      = trekways_about_mod( 'p1' );
	$p2      = trekways_about_mod( 'p2' );
	?>
<section class="tw-about" id="tw-about" aria-labelledby="tw-about-title">
	<div class="tw-about__wrap">
		<div class="tw-about__media">
			<div class="tw-about__main">
				<?php echo trekways_about_img( 'img_main', 'large', '(max-width: 900px) 92vw, 520px' ); // phpcs:ignore -- core-escaped. ?>
				<?php if ( $caption ) : ?><span class="tw-about__cap"><?php echo esc_html( $caption ); ?></span><?php endif; ?>
			</div>
			<div class="tw-about__small">
				<?php echo trekways_about_img( 'img_small', 'medium_large', '(max-width: 900px) 42vw, 240px' ); // phpcs:ignore -- core-escaped. ?>
			</div>
		</div>

		<div class="tw-about__text">
			<?php if ( $eyebrow ) : ?><span class="tw-about__eyebrow"><?php echo esc_html( $eyebrow ); ?></span><?php endif; ?>
			<h2 id="tw-about-title"><?php echo esc_html( trekways_about_mod( 'title' ) ); ?></h2>
			<?php if ( $p1 ) : ?><p><?php echo esc_html( $p1 ); ?></p><?php endif; ?>
			<?php if ( $p2 ) : ?><p><?php echo esc_html( $p2 ); ?></p><?php endif; ?>
			<?php if ( $link ) : ?>
				<a class="tw-about__link" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( trekways_about_mod( 'link_label' ) ); ?> <svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h9M8.5 4l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $team->have_posts() ) : ?>
	<div class="tw-team" aria-labelledby="tw-team-title">
		<h3 class="tw-team__title" id="tw-team-title"><?php echo esc_html( trekways_about_mod( 'team_title' ) ); ?></h3>
		<?php $intro = trekways_about_mod( 'team_intro' ); if ( $intro ) : ?><p class="tw-team__intro"><?php echo esc_html( $intro ); ?></p><?php endif; ?>
		<div class="tw-team__row">
			<button type="button" class="tw-team__btn" id="tw-team-prev" aria-label="<?php esc_attr_e( 'Previous team members', 'trekways' ); ?>"><svg width="18" height="18" viewBox="0 0 16 16" aria-hidden="true"><path d="M10 3.5 5.5 8l4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
			<ul class="tw-team__list" id="tw-team-list">
				<?php while ( $team->have_posts() ) : $team->the_post();
					$name = get_the_title();
					$role = trekways_meta( get_the_ID(), '_team_role' );
					$img  = has_post_thumbnail() ? get_the_post_thumbnail( null, 'trekways_team', array( 'alt' => $name, 'loading' => 'lazy', 'decoding' => 'async' ) ) : '';
					?>
					<li class="tw-team__item">
						<span class="tw-team__ring">
							<?php if ( $img ) : echo $img; // phpcs:ignore -- core-escaped.
							else : ?><span class="tw-team__initial" aria-hidden="true"><?php echo esc_html( function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 1 ) : substr( $name, 0, 1 ) ); ?></span><?php endif; ?>
						</span>
						<b><?php echo esc_html( $name ); ?></b>
						<?php if ( $role ) : ?><small><?php echo esc_html( $role ); ?></small><?php endif; ?>
					</li>
				<?php endwhile; ?>
			</ul>
			<button type="button" class="tw-team__btn" id="tw-team-next" aria-label="<?php esc_attr_e( 'Next team members', 'trekways' ); ?>"><svg width="18" height="18" viewBox="0 0 16 16" aria-hidden="true"><path d="M6 3.5 10.5 8 6 12.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
		</div>
	</div>
	<?php endif; ?>
</section>
	<?php
	wp_reset_postdata();
}

/**
 * "Full name" as the title placeholder on team members.
 */
function trekways_team_title_placeholder( $text, $post ) {
	return ( $post && 'team_member' === $post->post_type ) ? __( 'Full name', 'trekways' ) : $text;
}
add_filter( 'enter_title_here', 'trekways_team_title_placeholder', 10, 2 );