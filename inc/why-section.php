<?php
/**
 * "Why Trek Ways" — package comparison by region.
 * Cards are Trips that have a Package tier set, grouped by their Region term.
 *
 * @package TrekWays
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Tier order used for left / centre / right.
 */
function trekways_why_tiers() {
	return array( 'standard' => 'Standard', 'best' => 'Most booked', 'luxury' => 'Luxury' );
}

/**
 * Customizer controls.
 */
function trekways_why_customizer( $wp_customize ) {
	$wp_customize->add_section( 'trekways_why', array(
		'title'       => __( 'Why Trek Ways Section', 'trekways' ),
		'priority'    => 34,
		'description' => __( 'Heading text for the package comparison. The packages themselves come from Trips that have a Package tier set.', 'trekways' ),
	) );

	$fields = array(
		'trekways_why_enable'  => array( 'Show this section', 'checkbox', true ),
		'trekways_why_eyebrow' => array( 'Small text above the title', 'text', 'Why Trek Ways' ),
		'trekways_why_title'   => array( 'Title', 'text', 'One price. Everything on the mountain included.' ),
		'trekways_why_text'    => array( 'Paragraph', 'textarea', "Most Nepal quotes look cheap until the Lukla flight, the permits and the porter get added back. Ours don't. Pick a region and compare all three ways to trek it." ),
	);
	foreach ( $fields as $key => $f ) {
		list( $label, $type, $default ) = $f;
		$wp_customize->add_setting( $key, array(
			'default'           => $default,
			'sanitize_callback' => 'checkbox' === $type ? 'wp_validate_boolean' : ( 'textarea' === $type ? 'sanitize_textarea_field' : 'sanitize_text_field' ),
		) );
		$wp_customize->add_control( $key . '_ctrl', array(
			'label' => __( $label, 'trekways' ), // phpcs:ignore -- static strings.
			'section' => 'trekways_why', 'settings' => $key, 'type' => $type,
		) );
	}
}
add_action( 'customize_register', 'trekways_why_customizer' );

/**
 * Split a textarea value into clean lines.
 *
 * @param string $raw Raw textarea content.
 * @return string[]
 */
function trekways_lines( $raw ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
		$line = trim( $line );
		if ( '' !== $line ) {
			$out[] = $line;
		}
	}
	return $out;
}

/**
 * Regions that have at least one tiered package, with their trips in tier order.
 *
 * @return array[] [ 'name' => string, 'trips' => WP_Post[] ]
 */
function trekways_why_groups() {
	$terms = get_terms( array( 'taxonomy' => 'region', 'hide_empty' => true, 'number' => 10, 'orderby' => 'term_order' ) );
	if ( is_wp_error( $terms ) || ! $terms ) {
		return array();
	}
	$order  = array_keys( trekways_why_tiers() );
	$groups = array();
	foreach ( $terms as $term ) {
		$q = new WP_Query( array(
			'post_type'      => 'trip',
			'posts_per_page' => 3,
			'no_found_rows'  => true,
			'tax_query'      => array( array( 'taxonomy' => 'region', 'field' => 'term_id', 'terms' => $term->term_id ) ),
			'meta_query'     => array( array( 'key' => '_trip_tier', 'value' => '', 'compare' => '!=' ) ),
		) );
		if ( ! $q->posts ) {
			continue;
		}
		$trips = $q->posts;
		usort( $trips, function ( $a, $b ) use ( $order ) {
			$ia = array_search( trekways_meta( $a->ID, '_trip_tier' ), $order, true );
			$ib = array_search( trekways_meta( $b->ID, '_trip_tier' ), $order, true );
			return ( false === $ia ? 9 : $ia ) - ( false === $ib ? 9 : $ib );
		} );
		$groups[] = array( 'name' => $term->name, 'trips' => $trips );
	}
	return $groups;
}

/**
 * One package card.
 */
function trekways_why_card( $post, $index ) {
	$tiers = trekways_why_tiers();
	$tier  = trekways_meta( $post->ID, '_trip_tier' );
	$label = isset( $tiers[ $tier ] ) ? $tiers[ $tier ] : '';
	$best  = 'best' === $tier;
	$now   = trekways_meta( $post->ID, '_trip_price' );
	$was   = trekways_meta( $post->ID, '_trip_price_was' );
	$dur   = trekways_meta( $post->ID, '_trip_duration' );
	$diff  = trekways_meta( $post->ID, '_trip_difficulty' );
	$inc   = trekways_lines( trekways_meta( $post->ID, '_trip_includes' ) );
    	$book  = trekways_meta( $post->ID, '_trip_book_url' );
	if ( ! $book ) {
		$book = add_query_arg( 'trip', $post->post_name, get_permalink( $post ) );
	}
	$exc   = trekways_lines( trekways_meta( $post->ID, '_trip_excludes' ) );

	$off = 0;
	$n   = (float) preg_replace( '/[^0-9.]/', '', (string) $now );
	$w   = (float) preg_replace( '/[^0-9.]/', '', (string) $was );
	if ( $w > 0 && $n > 0 && $n < $w ) {
		$off = (int) round( ( 1 - $n / $w ) * 100 );
	}
	?>
	<article class="tw-pk<?php echo $best ? ' tw-pk--best' : ''; ?>" style="--o:<?php echo (int) $index; ?>">
		<?php if ( $label ) : ?><span class="tw-pk__flag<?php echo $best ? '' : ' alt'; ?>"><?php echo esc_html( $label ); ?></span><?php endif; ?>
		<?php if ( $off ) : ?><span class="tw-pk__off"><?php echo (int) $off; ?>% off</span><?php endif; ?>
		<h3><?php echo esc_html( get_the_title( $post ) ); ?></h3>
		<?php if ( $now ) : ?>
		<div class="tw-pk__price">
			<?php if ( $was ) : ?><span class="was"><?php echo esc_html( $was ); ?></span><?php endif; ?>
			<strong><?php echo esc_html( $now ); ?></strong>
			<small><?php esc_html_e( 'per person', 'trekways' ); ?></small>
		</div>
		<?php endif; ?>
		<?php if ( $dur || $diff ) : ?>
		<div class="tw-pk__meta">
			<?php if ( $dur ) : ?><span><i class="fa-regular fa-clock"></i><?php echo esc_html( $dur ); ?></span><?php endif; ?>
			<?php if ( $diff ) : ?><span><i class="fa-solid fa-gauge-simple"></i><?php echo esc_html( $diff ); ?></span><?php endif; ?>
		</div>
		<?php endif; ?>
        <a class="tw-pk__book" href="<?php echo esc_url( $book ); ?>"><?php esc_html_e( 'Book now', 'trekways' ); ?></a>
		<ul class="tw-pk__list">
			<?php foreach ( $inc as $x ) : ?><li class="in"><i class="fa-solid fa-circle-check"></i><?php echo esc_html( $x ); ?></li><?php endforeach; ?>
			<?php foreach ( $exc as $x ) : ?><li class="ex"><i class="fa-solid fa-circle-xmark"></i><?php echo esc_html( $x ); ?></li><?php endforeach; ?>
		</ul>
		<a class="tw-pk__cta" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php esc_html_e( 'Know more', 'trekways' ); ?> <i class="fa-solid fa-arrow-right"></i></a>
	</article>
	<?php
}

/**
 * Render the section.
 */
function trekways_why_section() {
	if ( ! get_theme_mod( 'trekways_why_enable', true ) ) {
		return;
	}
	$groups = trekways_why_groups();
	if ( ! $groups ) {
		return;
	}
	$eyebrow = get_theme_mod( 'trekways_why_eyebrow', 'Why Trek Ways' );
	$title   = get_theme_mod( 'trekways_why_title', 'One price. Everything on the mountain included.' );
	$text    = get_theme_mod( 'trekways_why_text', '' );
	?>
<section class="tw-why" id="tw-why">
	<div class="tw-why__head">
		<?php if ( $eyebrow ) : ?><small><?php echo esc_html( $eyebrow ); ?></small><?php endif; ?>
		<?php if ( $title ) : ?><h2><?php echo esc_html( $title ); ?></h2><?php endif; ?>
		<?php if ( $text ) : ?><p><?php echo esc_html( $text ); ?></p><?php endif; ?>
	</div>

	<?php if ( count( $groups ) > 1 ) : ?>
	<div class="tw-pktabs">
		<button class="tw-pkbtn" id="tw-pkprev" aria-label="<?php esc_attr_e( 'Previous region', 'trekways' ); ?>"><i class="fa-solid fa-chevron-left"></i></button>
		<div class="tw-pktabs__scroll">
			<?php foreach ( $groups as $i => $g ) : ?>
				<button class="tw-pktab<?php echo 0 === $i ? ' on' : ''; ?>" data-i="<?php echo (int) $i; ?>"><?php echo esc_html( $g['name'] ); ?></button>
			<?php endforeach; ?>
		</div>
		<button class="tw-pkbtn" id="tw-pknext" aria-label="<?php esc_attr_e( 'Next region', 'trekways' ); ?>"><i class="fa-solid fa-chevron-right"></i></button>
	</div>
	<?php endif; ?>

	<div class="tw-pkstage">
		<?php foreach ( $groups as $i => $g ) : ?>
		<div class="tw-pkslide<?php echo 0 === $i ? ' on' : ''; ?>" data-i="<?php echo (int) $i; ?>">
			<?php foreach ( $g['trips'] as $j => $p ) { trekways_why_card( $p, $j ); } ?>
		</div>
		<?php endforeach; ?>
	</div>
</section>
	<?php
}