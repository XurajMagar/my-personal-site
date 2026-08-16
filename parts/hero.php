<?php
/**
 * Hero banner part.
 * @package TrekWays
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$poster   = get_theme_mod( 'trekways_hero_poster', TREKWAYS_URI . '/images/hero-banner.jpg' );
$vid_desk = ( $id = get_theme_mod( 'trekways_hero_video' ) )        ? wp_get_attachment_url( $id ) : '';
$vid_mob  = ( $id = get_theme_mod( 'trekways_hero_video_mobile' ) ) ? wp_get_attachment_url( $id ) : '';

$search_ph = get_theme_mod( 'trekways_hero_search_ph', __( 'Search treks, tours, regions…', 'trekways' ) );

$btn1_text = get_theme_mod( 'trekways_hero_btn1_text', 'Plan My Trip' );
$btn1_url  = get_theme_mod( 'trekways_hero_btn1_url', '#' );
$btn2_text = get_theme_mod( 'trekways_hero_btn2_text', 'View Packages' );
$btn2_url  = get_theme_mod( 'trekways_hero_btn2_url', '' );
if ( ! $btn2_url ) { $btn2_url = get_post_type_archive_link( 'trip' ) ?: '#'; }

$awards_label = get_theme_mod( 'trekways_awards_label', 'Award-winning' );

/** Render one award slot as a glossy 3D coin (defaults to the bundled TripAdvisor badge). */
function trekways_award_slot( $i, $extra_class = '' ) {
	$img = get_theme_mod( "trekways_award_{$i}", TREKWAYS_URI . '/images/award-tripadvisor-2025.png' );
	echo '<div class="tw-award ' . esc_attr( $extra_class ) . '">';
	echo '<div class="tw-award__inner" data-tilt data-tilt-max="16" data-tilt-scale="1.05" data-tilt-speed="450" data-tilt-perspective="900" data-tilt-gyroscope="false">';
	echo '<div class="tw-award__face"><img src="' . esc_url( $img ) . '" alt="' . esc_attr__( 'Award', 'trekways' ) . '"></div>';
	echo '<span class="tw-award__gloss"></span>';
	echo '</div></div>';
}
?>

<section class="tw-hero" style="background-image:url('<?php echo esc_url( $poster ); ?>')">

	<?php if ( $vid_desk || $vid_mob ) : ?>
		<video class="tw-hero__video" autoplay muted loop playsinline preload="none"
			poster="<?php echo esc_url( $poster ); ?>"
			data-src-desktop="<?php echo esc_url( $vid_desk ); ?>"
			data-src-mobile="<?php echo esc_url( $vid_mob ); ?>"></video>
	<?php endif; ?>

	<div class="tw-hero__scrim"></div>

	<div class="tw-hero__body">
		<div class="tw-hero__layout">
			<div class="tw-hero__center">

				<div class="tw-awards-wrap tw-anim-fade tw-d0">
					<?php if ( $awards_label ) : ?>
						<div class="tw-awards__label"><span class="tw-awards__ln"></span><?php echo esc_html( $awards_label ); ?><span class="tw-awards__ln"></span></div>
					<?php endif; ?>
					<div class="tw-awards">
						<?php
						trekways_award_slot( 1 );
						trekways_award_slot( 2, 'tw-award--mid' );
						trekways_award_slot( 3 );
						?>
					</div>
				</div>

				<form class="tw-searchbar tw-anim-slide tw-d1" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<i class="fa-solid fa-magnifying-glass"></i>
					<input type="search" name="s" placeholder="<?php echo esc_attr( $search_ph ); ?>" aria-label="<?php esc_attr_e( 'Search', 'trekways' ); ?>">
					<input type="hidden" name="post_type" value="trip">
					<button type="submit"><?php esc_html_e( 'Search', 'trekways' ); ?></button>
				</form>

				<div class="tw-hero__cta tw-anim-slide tw-d2">
					<?php if ( $btn1_text ) : ?>
						<a href="<?php echo esc_url( $btn1_url ); ?>" class="tw-hbtn tw-hbtn--primary"><i class="fa-solid fa-mountain-sun"></i> <?php echo esc_html( $btn1_text ); ?></a>
					<?php endif; ?>
					<?php if ( $btn2_text ) : ?>
						<a href="<?php echo esc_url( $btn2_url ); ?>" class="tw-hbtn tw-hbtn--glass"><?php echo esc_html( $btn2_text ); ?></a>
					<?php endif; ?>
				</div>

			</div>
		</div>
	</div>
</section>
