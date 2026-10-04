<?php
/**
 * Region packages — homepage grid of trip cards filtered by the "region" taxonomy.
 *
 * @package TrekWays
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Customizer: on/off, heading text, minimum trips per region.
 */
function trekways_pkgs_customizer( $wp_customize ) {
	$wp_customize->add_section( 'trekways_pkgs', array(
		'title'       => __( 'Region Packages Section', 'trekways' ),
		'priority'    => 35,
		'description' => __( 'Homepage grid of trip packages, filtered by region.', 'trekways' ),
	) );

	$wp_customize->add_setting( 'trekways_pkgs_enable', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'trekways_pkgs_enable_ctrl', array(
		'label' => __( 'Show the region packages section', 'trekways' ), 'section' => 'trekways_pkgs',
		'settings' => 'trekways_pkgs_enable', 'type' => 'checkbox',
	) );

	$wp_customize->add_setting( 'trekways_pkgs_eyebrow', array( 'default' => 'Packages by region', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'trekways_pkgs_eyebrow_ctrl', array(
		'label' => __( 'Small text above the title', 'trekways' ), 'section' => 'trekways_pkgs',
		'settings' => 'trekways_pkgs_eyebrow', 'type' => 'text',
	) );

	$wp_customize->add_setting( 'trekways_pkgs_title', array( 'default' => 'Pick a region. Pick your trek.', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'trekways_pkgs_title_ctrl', array(
		'label' => __( 'Section title', 'trekways' ), 'section' => 'trekways_pkgs',
		'settings' => 'trekways_pkgs_title', 'type' => 'text',
	) );

	$wp_customize->add_setting( 'trekways_pkgs_intro', array( 'default' => 'Fixed prices in USD, permits and guide included. Every trip below runs on demand from Kathmandu.', 'sanitize_callback' => 'sanitize_textarea_field' ) );
	$wp_customize->add_control( 'trekways_pkgs_intro_ctrl', array(
		'label' => __( 'Intro text', 'trekways' ), 'section' => 'trekways_pkgs',
		'settings' => 'trekways_pkgs_intro', 'type' => 'textarea',
	) );

	$wp_customize->add_setting( 'trekways_pkgs_min', array( 'default' => 3, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'trekways_pkgs_min_ctrl', array(
		'label'       => __( 'Hide regions with fewer trips than', 'trekways' ),
		'description' => __( 'Regions with only one or two trips leave the grid mostly empty.', 'trekways' ),
		'section'     => 'trekways_pkgs',
		'settings'    => 'trekways_pkgs_min',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 1, 'max' => 8 ),
	) );
}
add_action( 'customize_register', 'trekways_pkgs_customizer' );

/**
 * Regions with their trips. Each item: term, posts (max 8), total.
 *
 * @return array
 */
function trekways_pkgs_data() {
	$terms = get_terms( apply_filters( 'trekways_pkgs_region_args', array(
		'taxonomy'   => 'region',
		'parent'     => 0,
		'hide_empty' => true,
		'number'     => 10,
		'orderby'    => 'count',
		'order'      => 'DESC',
	) ) );
	if ( is_wp_error( $terms ) || ! $terms ) {
		return array();
	}

	$min  = max( 1, (int) get_theme_mod( 'trekways_pkgs_min', 3 ) );
	$data = array();
	foreach ( $terms as $t ) {
		$q = new WP_Query( array(
			'post_type'      => 'trip',
			'post_status'    => 'publish',
			'posts_per_page' => 8,
			'orderby'        => 'menu_order date',
			'order'          => 'ASC',
			'tax_query'      => array( array(
				'taxonomy'         => 'region',
				'field'            => 'term_id',
				'terms'            => $t->term_id,
				'include_children' => true,
			) ),
		) );
		$total = (int) $q->found_posts;
		if ( $total >= $min ) {
			$data[] = array( 'term' => $t, 'posts' => $q->posts, 'total' => $total );
		}
	}
	return $data;
}

/**
 * One package card.
 *
 * @param WP_Post $post Trip.
 * @param int     $i    Position, drives the stagger delay.
 * @return string
 */
function trekways_pkgs_card( $post, $i ) {
	$id     = $post->ID;
	$price  = trekways_meta( $id, '_trip_price' );
	$was    = trekways_meta( $id, '_trip_price_was' );
	$dur    = trekways_meta( $id, '_trip_duration' );
	$diff   = trekways_meta( $id, '_trip_difficulty' );
	$alt    = trekways_meta( $id, '_trip_altitude' );
	$group  = trekways_meta( $id, '_trip_group_size' );
	$season = trekways_meta( $id, '_trip_season' );
	$badge  = trekways_meta( $id, '_trip_badge' );

	$img_attr = array(
		'class'    => 'tw-pcard__ph',
		'loading'  => 'lazy',
		'decoding' => 'async',
		'sizes'    => '(max-width: 640px) 82vw, (max-width: 1024px) 42vw, 25vw',
		'alt'      => '',
	);
	$img = has_post_thumbnail( $id ) ? get_the_post_thumbnail( $id, 'trekways_card', $img_attr ) : '';
	if ( ! $img ) {
		$img = '<img class="tw-pcard__ph" src="' . esc_url( TREKWAYS_URI . '/images/trip-placeholder.webp' ) . '" alt="" loading="lazy" decoding="async">';
	}

	$facts = array(
		array( 'fa-signal', $diff ),
		array( 'fa-mountain', $alt ),
		array( 'fa-user-group', $group ),
		array( 'fa-calendar', $season ),
	);

	$out  = '<a class="tw-pcard" href="' . esc_url( get_permalink( $id ) ) . '" style="--i:' . (int) $i . '">';
	$out .= '<div class="tw-pcard__img">' . $img;
	if ( $badge ) {
		$out .= '<span class="tw-pcard__badge">' . esc_html( $badge ) . '</span>';
	}
	if ( $dur ) {
		$out .= '<span class="tw-pcard__days"><i class="fa-regular fa-clock" aria-hidden="true"></i>' . esc_html( $dur ) . '</span>';
	}
	$out .= '</div><div class="tw-pcard__body"><h3>' . esc_html( get_the_title( $id ) ) . '</h3>';

	$li = '';
	foreach ( $facts as $f ) {
		if ( $f[1] ) {
			$li .= '<li><i class="fa-solid ' . esc_attr( $f[0] ) . '" aria-hidden="true"></i>' . esc_html( $f[1] ) . '</li>';
		}
	}
	if ( $li ) {
		$out .= '<ul class="tw-pcard__facts">' . $li . '</ul>';
	}

	$out .= '<div class="tw-pcard__foot"><div class="tw-pcard__price">';
	if ( $price ) {
		$out .= '<small>' . esc_html__( 'From, per person', 'trekways' ) . '</small>';
		if ( $was ) {
			$out .= '<s>' . esc_html( $was ) . '</s>';
		}
		$out .= '<strong>' . esc_html( $price ) . '</strong>';
	} else {
		$out .= '<strong class="tw-pcard__ask">' . esc_html__( 'Price on request', 'trekways' ) . '</strong>';
	}
	$out .= '</div><span class="tw-pcard__go" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span></div>';
	$out .= '</div></a>';
	return $out;
}

/**
 * Render the section. Echoes nothing when there is nothing to show.
 */
function trekways_pkgs_section() {
	if ( ! get_theme_mod( 'trekways_pkgs_enable', true ) ) {
		return;
	}
	$regions = trekways_pkgs_data();
	if ( ! $regions ) {
		return;
	}
	$eyebrow = get_theme_mod( 'trekways_pkgs_eyebrow', 'Packages by region' );
	$title   = get_theme_mod( 'trekways_pkgs_title', 'Pick a region. Pick your trek.' );
	$intro   = get_theme_mod( 'trekways_pkgs_intro', 'Fixed prices in USD, permits and guide included. Every trip below runs on demand from Kathmandu.' );
	$archive = get_post_type_archive_link( 'trip' );

	/* Same files the Featured Trips cloud divider loads, so they come from cache. */
	$clouds = '';
	foreach ( array( 2, 5, 1 ) as $n ) {
		$clouds .= '<img src="' . esc_url( TREKWAYS_URI . '/images/clouds/cloud' . $n . '.webp' ) . '" alt="" loading="lazy" decoding="async">';
	}
	?>
<section class="tw-pkgs" id="tw-pkgs" aria-labelledby="tw-pkgs-title">
	<div class="tw-fog" aria-hidden="true">
		<div class="tw-fog__glow"></div>
		<div class="tw-fog__sag">
			<div class="tw-fog__top">
				<div class="tw-fog__set"><?php echo $clouds; // phpcs:ignore -- escaped above. ?></div>
				<div class="tw-fog__set"><?php echo $clouds; // phpcs:ignore -- escaped above. ?></div>
			</div>
		</div>
	</div>

	<div class="tw-pkgs__wrap">
		<div class="tw-pkgs__head">
			<div>
				<?php if ( $eyebrow ) : ?><small><?php echo esc_html( $eyebrow ); ?></small><?php endif; ?>
				<h2 id="tw-pkgs-title"><?php echo esc_html( $title ); ?></h2>
				<?php if ( $intro ) : ?><p><?php echo esc_html( $intro ); ?></p><?php endif; ?>
			</div>
			<?php if ( $archive ) : ?>
				<a class="tw-pkgs__all" href="<?php echo esc_url( $archive ); ?>"><?php esc_html_e( 'All packages', 'trekways' ); ?></a>
			<?php endif; ?>
		</div>

		<div class="tw-rtabs">
			<button type="button" class="tw-rbtn" id="tw-rprev" aria-label="<?php esc_attr_e( 'Previous regions', 'trekways' ); ?>"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
			<div class="tw-rtabs__scroll" id="tw-rscroll" role="tablist" aria-label="<?php esc_attr_e( 'Regions', 'trekways' ); ?>">
				<?php foreach ( $regions as $i => $r ) : ?>
					<button type="button" class="tw-rtab" role="tab" id="tw-rtab-<?php echo (int) $i; ?>" aria-controls="tw-rpanel-<?php echo (int) $i; ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" tabindex="<?php echo 0 === $i ? '0' : '-1'; ?>">
						<?php echo esc_html( $r['term']->name ); ?><span><?php echo (int) $r['total']; ?></span>
					</button>
				<?php endforeach; ?>
			</div>
			<button type="button" class="tw-rbtn" id="tw-rnext" aria-label="<?php esc_attr_e( 'Next regions', 'trekways' ); ?>"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
		</div>

		<?php foreach ( $regions as $i => $r ) :
			$more  = $r['total'] > 8;
			$posts = $more ? array_slice( $r['posts'], 0, 7 ) : $r['posts'];
			$link  = get_term_link( $r['term'] );
			?>
			<div class="tw-pgrid<?php echo 0 === $i ? ' play' : ''; ?>" id="tw-rpanel-<?php echo (int) $i; ?>" role="tabpanel" aria-labelledby="tw-rtab-<?php echo (int) $i; ?>"<?php echo 0 === $i ? '' : ' hidden'; ?>>
				<?php
				foreach ( $posts as $k => $p ) {
					echo trekways_pkgs_card( $p, $k ); // phpcs:ignore -- escaped in helper.
				}
				if ( $more && ! is_wp_error( $link ) ) :
					?>
					<a class="tw-pcard tw-pmore" href="<?php echo esc_url( $link ); ?>" style="--i:<?php echo count( $posts ); ?>">
						<b><?php
							/* translators: %s: region name. */
							printf( esc_html__( 'View all %s packages', 'trekways' ), esc_html( $r['term']->name ) );
						?></b>
						<em><?php
							/* translators: %d: number of trips. */
							printf( esc_html( _n( '%d trip in this region', '%d trips in this region', $r['total'], 'trekways' ) ), (int) $r['total'] );
						?></em>
						<span aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
					</a>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
		<div class="tw-pbar" aria-hidden="true"><i id="tw-pbar"></i></div>
	</div>
</section>
	<?php
	wp_reset_postdata();
}