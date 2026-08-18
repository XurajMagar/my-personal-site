<?php
/**
 * Header part — glass logo-reveal navbar.
 * @package TrekWays
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'trekways' ); ?></a>

<div class="tw-mega-overlay" id="tw-mega-overlay"></div>
<div class="tw-navdock">
	<div class="tw-utilbar"><div class="tw-utilrow">
        <?php
        $cta_on = get_theme_mod( 'trekways_cta_enable', true );
			if ( $cta_on ) :
				$cta_label = get_theme_mod( 'trekways_cta_label', 'Need help? Call us' );
				$np_num  = get_theme_mod( 'trekways_cta_np_number', '+977 9841666232' );
				$np_name = get_theme_mod( 'trekways_cta_np_name', '' );
				$np_flag = get_theme_mod( 'trekways_cta_np_flag', TREKWAYS_URI . '/images/flags/np.svg' );
				$us_num  = get_theme_mod( 'trekways_cta_us_number', '+1-651-703-8181' );
				$us_name = get_theme_mod( 'trekways_cta_us_name', '' );
				$us_flag = get_theme_mod( 'trekways_cta_us_flag', TREKWAYS_URI . '/images/flags/us.svg' );
				if ( $np_num || $us_num ) :
			?>
			<div class="tw-wa">
				<?php if ( $cta_label ) : ?><span class="tw-wa__lbl"><?php echo esc_html( $cta_label ); ?></span><?php endif; ?>
				<div class="tw-wa__nums">
				<?php if ( $np_num ) : ?>
				<a class="tw-wa__row" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $np_num ) ); ?>">
					<img class="tw-wa__flag" src="<?php echo esc_url( $np_flag ); ?>" alt="Nepal">
					<span><?php echo esc_html( $np_num ); ?><?php if ( $np_name ) : ?> <b>(<?php echo esc_html( $np_name ); ?>)</b><?php endif; ?></span>
				</a>
				<?php endif; ?>
				<?php if ( $us_num ) : ?>
				<a class="tw-wa__row" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $us_num ) ); ?>">
					<img class="tw-wa__flag" src="<?php echo esc_url( $us_flag ); ?>" alt="USA">
					<span><?php echo esc_html( $us_num ); ?><?php if ( $us_name ) : ?> <b>(<?php echo esc_html( $us_name ); ?>)</b><?php endif; ?></span>
				</a>
				<?php endif; ?>
				</div>
			</div>
            <?php endif; endif; ?>
			<?php
			$pay_on  = get_theme_mod( 'trekways_pay_enable', true );
			$pay_img = get_theme_mod( 'trekways_pay_1', '' );
			$pay_url = get_theme_mod( 'trekways_pay_url', '' );
			$pay_lbl = get_theme_mod( 'trekways_pay_label', 'Online Payment' );
			if ( $pay_on && $pay_img ) : ?>
			<div class="tw-pays">
				<?php if ( $pay_lbl ) : ?><span class="tw-pays__lbl"><?php echo esc_html( $pay_lbl ); ?></span><?php endif; ?>
				<?php if ( $pay_url ) : ?><a href="<?php echo esc_url( $pay_url ); ?>" target="_blank" rel="noopener nofollow"><?php endif; ?>
				<img class="tw-pay" src="<?php echo esc_url( $pay_img ); ?>" alt="">
				<?php if ( $pay_url ) : ?></a><?php endif; ?>
			</div>
			<?php endif; ?>
    </div></div>
	<nav class="tw-nav" aria-label="<?php esc_attr_e( 'Primary', 'trekways' ); ?>">

		<?php
		if ( has_nav_menu( 'primary_left' ) ) {
			trekways_mega_wing( 'primary_left', 'tw-wing--left' );
		} else {
			echo '<ul class="tw-wing tw-wing--left">';
			echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></li>';
			echo '<li><a href="' . esc_url( get_post_type_archive_link( 'trip' ) ) . '">Destinations</a></li>';
			echo '<li><a href="' . esc_url( home_url( '/region/' ) ) . '">Trek by Region</a></li>';
			echo '<li><a href="#">Day Tours</a></li>';
			echo '</ul>';
		}
		?>

		<?php $tw_logo = get_theme_mod( 'custom_logo' ); $tw_logo_url = $tw_logo ? wp_get_attachment_image_url( $tw_logo, 'full' ) : ''; ?>
		<a class="tw-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<span class="tw-brand__mark">
				<?php if ( $tw_logo_url ) : ?>
					<img class="tw-brand__logoimg" src="<?php echo esc_url( $tw_logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<?php else : ?>
					<svg width="27" height="21" viewBox="0 0 40 30" fill="none"><path d="M2 28 L14 6 L20 15 L26 3 L38 28 Z" fill="#F5F2FC"/><path d="M14 6 L11 11 L17 11 Z" fill="#8B6FE8"/><path d="M26 3 L23 9 L29 9 Z" fill="#8B6FE8"/></svg>
				<?php endif; ?>
				<span class="tw-brand__name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
			</span>
		</a>

		<div class="tw-rside">
			<?php
			if ( has_nav_menu( 'primary_right' ) ) {
				trekways_mega_wing( 'primary_right', 'tw-wing--right' );
			} else {
				echo '<ul class="tw-wing tw-wing--right">';
				echo '<li><a href="' . esc_url( home_url( '/blog/' ) ) . '">Blogs</a></li>';
				echo '<li><a href="#">Contact</a></li>';
				echo '</ul>';
			}
			?>
		</div>

		<button class="tw-burger" aria-label="<?php esc_attr_e( 'Toggle menu', 'trekways' ); ?>" aria-expanded="false"><i class="fa-solid fa-bars"></i></button>
	</nav>
</div>

<main id="main">