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
<section class="tw-trips">
	<div class="tw-clouddiv-top" aria-hidden="true">
		<?php
		$tw_ct = array(
			array(1,'-0s',-1,'.46'), array(2,'-0s',1,'.44'),
			array(3,'-5.2s',-1,'.34'), array(4,'-5.2s',1,'.48'),
			array(5,'-10.4s',-1,'.45'), array(1,'-10.4s',1,'.47'),
			array(2,'-15.6s',-1,'.43'), array(3,'-15.6s',1,'.34'),
			array(4,'-20.8s',-1,'.48'), array(5,'-20.8s',1,'.45'),
		);
		foreach ( $tw_ct as $c ) {
			printf(
				'<img class="tw-cloud-top" src="%s" alt="" loading="lazy" decoding="async" style="--d:%s;--dir:%d;--s0:%s">',
				esc_url( TREKWAYS_URI . '/images/clouds/cloud' . $c[0] . '.webp' ),
				esc_attr( $c[1] ), (int) $c[2], esc_attr( $c[3] )
			);
		}
		?>
	</div>

	<div class="tw-trips__head">
		<p class="tw-trips__eyebrow"><?php esc_html_e( 'In case you missed it', 'trekways' ); ?></p>
		<h2><?php esc_html_e( 'Featured Treks', 'trekways' ); ?></h2>
		<p><?php esc_html_e( 'Hand-picked routes across the Himalaya - guided by the team that knows them best.', 'trekways' ); ?></p>
	</div>

	<?php
		$trips = new WP_Query( array(
		'post_type'      => 'trip',
		'posts_per_page' => 10,
		'no_found_rows'  => true,
		'orderby'        => 'menu_order date',
		'order'          => 'ASC',
		'meta_query'     => array(
			array( 'key' => '_trip_featured', 'value' => '1', 'compare' => '=' ),
		),
	) );
	if ( ! $trips->have_posts() ) {
		$trips = new WP_Query( array( 'post_type' => 'trip', 'posts_per_page' => 10, 'no_found_rows' => true ) );
	}
	if ( $trips->have_posts() ) : ?>
	<div class="tw-stage" id="tw-stage">
        	<div class="tw-vajra" aria-hidden="true">
			<div class="tw-vajra__base"></div>
			<div class="tw-vajra__core"></div>
			<div class="tw-vajra__flow"></div>
			<div class="tw-vajra__flow tw-vajra__flow--l"></div>
			<div class="tw-vajra__flow tw-vajra__flow--late"></div>
			<div class="tw-vajra__flow tw-vajra__flow--l tw-vajra__flow--late"></div>
		</div>
		<?php $i = 0; while ( $trips->have_posts() ) : $trips->the_post();
			$price = trekways_meta( get_the_ID(), '_trip_price' );
			$dur   = trekways_meta( get_the_ID(), '_trip_duration' );
			$diff  = trekways_meta( get_the_ID(), '_trip_difficulty' );
			$regs  = get_the_terms( get_the_ID(), 'region' );
			$reg   = ( $regs && ! is_wp_error( $regs ) ) ? $regs[0]->name : '';
			$img   = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'large' ) : TREKWAYS_URI . '/images/trip-placeholder.webp';
		?>
		<article class="tw-tc" data-i="<?php echo (int) $i; ?>" style="background-image:url('<?php echo esc_url( $img ); ?>')">
			<a class="tw-tc__link" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>"></a>
			<div class="tw-tc__scrim"></div>
			<div class="tw-tc__body">
				<?php if ( $reg ) : ?><span class="tw-tc__region"><i class="fa-solid fa-location-dot"></i><?php echo esc_html( $reg ); ?></span><?php endif; ?>
				<h3><?php the_title(); ?></h3>
				<div class="tw-tc__meta">
					<?php if ( $dur ) : ?><span><?php echo esc_html( $dur ); ?></span><?php endif; ?>
					<?php if ( $dur && $diff ) : ?><span class="dot"></span><?php endif; ?>
					<?php if ( $diff ) : ?><span><?php echo esc_html( $diff ); ?></span><?php endif; ?>
					<?php if ( $price ) : ?><span class="dot"></span><span class="pr"><?php echo esc_html( $price ); ?></span><?php endif; ?>
				</div>
			</div>
		</article>
		<?php $i++; endwhile; ?>
		<button class="tw-nav-btn prev" id="tw-prev" aria-label="<?php esc_attr_e( 'Previous trip', 'trekways' ); ?>"><i class="fa-solid fa-chevron-left"></i></button>
		<button class="tw-nav-btn next" id="tw-next" aria-label="<?php esc_attr_e( 'Next trip', 'trekways' ); ?>"><i class="fa-solid fa-chevron-right"></i></button>
	</div>
	<div class="tw-dots" id="tw-dots"></div>
	<div class="tw-trips__cta"><a href="<?php echo esc_url( get_post_type_archive_link( 'trip' ) ); ?>"><?php esc_html_e( 'View All Trips', 'trekways' ); ?></a></div>
	<?php else : ?>
		<p class="tw-trips__empty"><?php esc_html_e( 'No trips yet. Add your first under Trips > Add New.', 'trekways' ); ?></p>
	<?php endif; wp_reset_postdata(); ?>
</section>

<!-- ASSOCIATIONS + AWARDS -->
    <?php
    $tw_assoc  = trekways_logo_tiles( 'association' );
    $tw_awards = trekways_logo_tiles( 'award' );
    if ( $tw_assoc || $tw_awards ) : ?>
    <section class="tw-logos">
        <?php if ( $tw_assoc ) : ?>
        <div class="tw-logos__row">
            <div class="tw-logos__label"><small><?php esc_html_e( 'Trusted by', 'trekways' ); ?></small><?php esc_html_e( 'Our Associations', 'trekways' ); ?></div>
            <div class="tw-marquee"><div class="tw-track"><?php echo $tw_assoc; // phpcs:ignore -- escaped in helper. ?></div></div>
        </div>
        <?php endif; ?>
        <?php if ( $tw_awards ) : ?>
        <div class="tw-logos__row">
            <div class="tw-logos__label"><small><?php esc_html_e( 'Recognised for', 'trekways' ); ?></small><?php esc_html_e( 'Awards', 'trekways' ); ?></div>
            <div class="tw-marquee tw-marquee--rev"><div class="tw-track"><?php echo $tw_awards; // phpcs:ignore -- escaped in helper. ?></div></div>
        </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>

<!-- DESTINATIONS -->
<?php trekways_destinations_section(); ?>

<!-- WHY TREK WAYS -->
<?php trekways_why_section(); ?>

<!-- REGION PACKAGES -->
<?php trekways_pkgs_section(); ?>

<!-- ABOUT -->
<?php trekways_about_section(); ?>

<?php get_template_part( 'parts/footer' );
