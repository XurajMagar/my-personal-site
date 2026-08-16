<?php
/**
 * Fallback template — used for any view without a more specific template
 * (blog index, archives, search). Real archive/single templates come next.
 *
 * @package TrekWays
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_template_part( 'parts/header' );
?>
<section class="tw-section">
	<div class="tw-container">
		<?php if ( have_posts() ) : ?>
			<?php if ( is_home() && ! is_front_page() ) : ?>
				<h1><?php single_post_title(); ?></h1>
			<?php elseif ( is_archive() ) : ?>
				<h1><?php the_archive_title(); ?></h1>
				<?php the_archive_description(); ?>
			<?php elseif ( is_search() ) : ?>
				<h1><?php printf( esc_html__( 'Search results for: %s', 'trekways' ), '<span>' . esc_html( get_search_query() ) . '</span>' ); ?></h1>
			<?php endif; ?>

			<div class="tw-grid tw-grid--3" style="margin-top:24px">
			<?php while ( have_posts() ) : the_post(); ?>
				<article class="tw-card">
					<a class="tw-card__media" href="<?php the_permalink(); ?>">
						<?php
						if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'trekways_card' );
						} else {
							echo '<img src="' . esc_url( get_template_directory_uri() . '/images/trip-placeholder.webp' ) . '" alt="">';
						}
						?>
					</a>
					<div class="tw-card__body">
						<h3 class="tw-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p style="color:var(--tw-muted);font-size:.9rem"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
					</div>
				</article>
			<?php endwhile; ?>
			</div>

			<div style="margin-top:32px"><?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?></div>

		<?php else : ?>
			<h1><?php esc_html_e( 'Nothing found', 'trekways' ); ?></h1>
			<p><?php esc_html_e( 'No content matched your request.', 'trekways' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</section>
<?php get_template_part( 'parts/footer' );
