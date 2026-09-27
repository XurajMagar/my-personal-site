<?php
/**
 * Destinations showcase — homepage slider built from the "destination" taxonomy.
 *
 * @package TrekWays
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Top-level destination terms used by the showcase.
 *
 * @return WP_Term[]
 */
function trekways_destination_terms() {
	$terms = get_terms( array(
		'taxonomy'   => 'destination',
		'parent'     => 0,
		'hide_empty' => false,
		'number'     => 6,
		'orderby'    => 'term_order',
	) );
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Customizer: heading text plus one background and one card image per destination.
 */
function trekways_destinations_customizer( $wp_customize ) {
	$wp_customize->add_section( 'trekways_destinations', array(
		'title'       => __( 'Destinations Section', 'trekways' ),
		'priority'    => 33,
		'description' => __( 'Heading text and the images used by the homepage destinations slider.', 'trekways' ),
	) );

	$wp_customize->add_setting( 'trekways_dest_enable', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'trekways_dest_enable_ctrl', array(
		'label' => __( 'Show the destinations section', 'trekways' ), 'section' => 'trekways_destinations',
		'settings' => 'trekways_dest_enable', 'type' => 'checkbox',
	) );

	$wp_customize->add_setting( 'trekways_dest_eyebrow', array( 'default' => 'Where to', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'trekways_dest_eyebrow_ctrl', array(
		'label' => __( 'Small text above the title', 'trekways' ), 'section' => 'trekways_destinations',
		'settings' => 'trekways_dest_eyebrow', 'type' => 'text',
	) );

	$wp_customize->add_setting( 'trekways_dest_title', array( 'default' => 'Our Destinations', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'trekways_dest_title_ctrl', array(
		'label' => __( 'Section title', 'trekways' ), 'section' => 'trekways_destinations',
		'settings' => 'trekways_dest_title', 'type' => 'text',
	) );

	foreach ( trekways_destination_terms() as $t ) {
		$wp_customize->add_setting( 'trekways_dest_bg_' . $t->term_id, array( 'sanitize_callback' => 'esc_url_raw' ) );
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'trekways_dest_bg_' . $t->term_id . '_ctrl', array(
			/* translators: %s: destination name. */
			'label'    => sprintf( __( '%s — background image', 'trekways' ), $t->name ),
			'section'  => 'trekways_destinations',
			'settings' => 'trekways_dest_bg_' . $t->term_id,
		) ) );

		$wp_customize->add_setting( 'trekways_dest_card_' . $t->term_id, array( 'sanitize_callback' => 'esc_url_raw' ) );
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'trekways_dest_card_' . $t->term_id . '_ctrl', array(
			/* translators: %s: destination name. */
			'label'    => sprintf( __( '%s — centre card image (portrait)', 'trekways' ), $t->name ),
			'section'  => 'trekways_destinations',
			'settings' => 'trekways_dest_card_' . $t->term_id,
		) ) );
	}
}
add_action( 'customize_register', 'trekways_destinations_customizer' );

/**
 * Trips belonging to one destination term.
 *
 * @param int $term_id Destination term ID.
 * @return WP_Post[]
 */
function trekways_destination_trips( $term_id ) {
	$q = new WP_Query( array(
		'post_type'      => 'trip',
		'posts_per_page' => 8,
		'no_found_rows'  => true,
		'orderby'        => 'menu_order date',
		'order'          => 'ASC',
		'tax_query'      => array( array(
			'taxonomy'         => 'destination',
			'field'            => 'term_id',
			'terms'            => $term_id,
			'include_children' => true,
		) ),
	) );
	return $q->posts;
}

/**
 * One trip card.
 */
function trekways_destination_card( $post ) {
	$img = get_the_post_thumbnail_url( $post->ID, 'medium_large' );
	if ( ! $img ) {
		$img = TREKWAYS_URI . '/images/trip-placeholder.webp';
	}
	$dur = trekways_meta( $post->ID, '_trip_duration' );
	$out  = '<a class="tw-dcard" href="' . esc_url( get_permalink( $post ) ) . '" style="background-image:url(\'' . esc_url( $img ) . '\')">';
	$out .= '<span class="tw-dcard__sc"></span><span class="tw-dcard__t">' . esc_html( get_the_title( $post ) );
	if ( $dur ) {
		$out .= '<small>' . esc_html( $dur ) . '</small>';
	}
	$out .= '</span></a>';
	return $out;
}

/**
 * Render the whole section. Echoes nothing when there is nothing to show.
 */
function trekways_destinations_section() {
	if ( ! get_theme_mod( 'trekways_dest_enable', true ) ) {
		return;
	}
	$terms = trekways_destination_terms();
	if ( ! $terms ) {
		return;
	}

	$slides = array();
	foreach ( $terms as $t ) {
		$trips = trekways_destination_trips( $t->term_id );
		$slides[] = array( 'term' => $t, 'trips' => $trips );
	}
	if ( ! $slides ) {
		return;
	}
	$eyebrow = get_theme_mod( 'trekways_dest_eyebrow', 'Where to' );
	$title   = get_theme_mod( 'trekways_dest_title', 'Our Destinations' );
	?>
<section class="tw-dest">
	<?php foreach ( $slides as $i => $s ) :
		$bg = get_theme_mod( 'trekways_dest_bg_' . $s['term']->term_id, '' );
		?>
		<div class="tw-dbg" data-i="<?php echo (int) $i; ?>" style="<?php echo $bg ? 'background-image:url(\'' . esc_url( $bg ) . '\');' : ''; ?>opacity:<?php echo 0 === $i ? '1' : '0'; ?>"></div>
	<?php endforeach; ?>
	<div class="tw-dest__ov"></div>

	<div class="tw-dest__head">
		<?php if ( $eyebrow ) : ?><small><?php echo esc_html( $eyebrow ); ?></small><?php endif; ?>
		<?php if ( $title ) : ?><h2><?php echo esc_html( $title ); ?></h2><?php endif; ?>
	</div>

	<div class="tw-dstage">
		<?php foreach ( $slides as $i => $s ) :
			$t     = $s['term'];
			$trips = $s['trips'];
			$card  = get_theme_mod( 'trekways_dest_card_' . $t->term_id, '' );
			if ( ! $card ) {
				$card = ! empty( $trips[0] ) ? get_the_post_thumbnail_url( $trips[0]->ID, 'large' ) : '';
			}
			if ( ! $card ) {
				$card = TREKWAYS_URI . '/images/region-placeholder.webp';
			}
			$left  = array_slice( $trips, 0, 4 );
			$right = array_slice( $trips, 4, 4 );
			$count = count( $trips );
			?>
		<div class="tw-dslide<?php echo 0 === $i ? ' on' : ''; ?>" data-i="<?php echo (int) $i; ?>">
			<div class="tw-dside tw-dside--l">
				<?php foreach ( $left as $p ) { echo trekways_destination_card( $p ); /* phpcs:ignore -- escaped in helper. */ } ?>
			</div>
			<a class="tw-dhero" href="<?php echo esc_url( get_term_link( $t ) ); ?>" style="background-image:url('<?php echo esc_url( $card ); ?>')">
				<span class="tw-dhero__sc"></span>
				<span class="tw-dhero__b">
					<small><?php esc_html_e( 'Destination', 'trekways' ); ?></small>
					<strong><?php echo esc_html( $t->name ); ?></strong>
					<em><?php
						/* translators: %d: number of trips. */
						printf( esc_html( _n( '%d trip', '%d trips', $count, 'trekways' ) ), (int) $count );
					?></em>
					<span class="tw-dhero__go"><?php
						/* translators: %s: destination name. */
						printf( esc_html__( 'Explore %s', 'trekways' ), esc_html( $t->name ) );
					?> <i class="fa-solid fa-arrow-right"></i></span>
				</span>
			</a>
			<div class="tw-dside tw-dside--r">
				<?php foreach ( $right as $p ) { echo trekways_destination_card( $p ); /* phpcs:ignore -- escaped in helper. */ } ?>
			</div>
		</div>
		<?php endforeach; ?>
	</div>

	<?php if ( count( $slides ) > 1 ) : ?>
	<div class="tw-dnav">
		<button class="tw-dbtn" id="tw-dprev" aria-label="<?php esc_attr_e( 'Previous destination', 'trekways' ); ?>"><i class="fa-solid fa-chevron-left"></i></button>
		<?php foreach ( $slides as $i => $s ) : ?>
			<button class="tw-ddot<?php echo 0 === $i ? ' on' : ''; ?>" data-i="<?php echo (int) $i; ?>"><?php echo esc_html( $s['term']->name ); ?></button>
		<?php endforeach; ?>
		<button class="tw-dbtn" id="tw-dnext" aria-label="<?php esc_attr_e( 'Next destination', 'trekways' ); ?>"><i class="fa-solid fa-chevron-right"></i></button>
	</div>
	<?php endif; ?>
</section>
	<?php
}