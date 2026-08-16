<?php
/**
 * Front page.
 * @package TrekWays
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_template_part( 'parts/header' );
get_template_part( 'parts/hero' );
?>

<!-- FEATURED TRIPS -->
<section class="tw-section">
	<div class="tw-container">
		<p class="tw-eyebrow"><?php esc_html_e( 'In case you missed it', 'trekways' ); ?></p>
		<h2><?php esc_html_e( 'Trending Trips', 'trekways' ); ?></h2>
		<?php
		$trips = new WP_Query( array( 'post_type' => 'trip', 'posts_per_page' => 6, 'no_found_rows' => true ) );
		if ( $trips->have_posts() ) : ?>
			<div class="tw-grid tw-grid--3" style="margin-top:24px">
			<?php while ( $trips->have_posts() ) : $trips->the_post();
				$price = trekways_meta( get_the_ID(), '_trip_price' );
				$dur   = trekways_meta( get_the_ID(), '_trip_duration' ); ?>
				<article class="tw-card">
					<a class="tw-card__media" href="<?php the_permalink(); ?>">
						<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'trekways_card' ); }
						else { echo '<img src="' . esc_url( TREKWAYS_URI . '/images/trip-placeholder.webp' ) . '" alt="">'; } ?>
					</a>
					<div class="tw-card__body">
						<h3 class="tw-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<div class="tw-card__meta">
							<?php if ( $dur ) : ?><span><i class="fa-regular fa-clock"></i> <?php echo esc_html( $dur ); ?></span><?php endif; ?>
							<?php if ( $price ) : ?><span class="tw-card__price"><?php echo esc_html( $price ); ?></span><?php endif; ?>
						</div>
					</div>
				</article>
			<?php endwhile; ?>
			</div>
		<?php else : ?>
			<p style="margin-top:16px;color:#6a6480"><?php esc_html_e( 'No trips yet. Add your first under Trips > Add New.', 'trekways' ); ?></p>
		<?php endif; wp_reset_postdata(); ?>
	</div>
</section>

<!-- DESTINATIONS -->
<section class="tw-section tw-section--soft">
	<div class="tw-container">
		<p class="tw-eyebrow"><?php esc_html_e( 'Where to', 'trekways' ); ?></p>
		<h2><?php esc_html_e( 'Our Destinations', 'trekways' ); ?></h2>
		<?php
		$dests = get_terms( array( 'taxonomy' => 'destination', 'parent' => 0, 'hide_empty' => false ) );
		if ( ! is_wp_error( $dests ) && $dests ) : ?>
			<div class="tw-grid tw-grid--4" style="margin-top:24px">
			<?php foreach ( $dests as $d ) : ?>
				<a class="tw-card" href="<?php echo esc_url( get_term_link( $d ) ); ?>" style="text-decoration:none">
					<div class="tw-card__media"><img src="<?php echo esc_url( TREKWAYS_URI . '/images/trip-placeholder.webp' ); ?>" alt=""></div>
					<div class="tw-card__body"><h3 class="tw-card__title"><?php echo esc_html( $d->name ); ?></h3></div>
				</a>
			<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p style="margin-top:16px;color:#6a6480"><?php esc_html_e( 'Add destinations (Nepal, Bhutan, Tibet, India) under Trips > Destinations.', 'trekways' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php get_template_part( 'parts/footer' );
