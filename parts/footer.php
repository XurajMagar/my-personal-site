<?php
/**
 * Footer part. Included via get_template_part( 'parts/footer' ).
 *
 * @package TrekWays
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
</main><!-- #main -->

<footer class="tw-footer">
	<div class="tw-container">
		<div class="tw-footer__cols">
			<div>
				<h4><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h4>
				<p>Trekking &amp; tour agency in Nepal. Tailor-made adventures across Nepal, Bhutan, Tibet and India.</p>
			</div>
			<div>
				<h4><?php esc_html_e( 'Destinations', 'trekways' ); ?></h4>
				<ul style="list-style:none;padding:0;margin:0;line-height:2">
				<?php
				$dests = get_terms( array( 'taxonomy' => 'destination', 'parent' => 0, 'hide_empty' => false, 'number' => 6 ) );
				if ( ! is_wp_error( $dests ) && $dests ) {
					foreach ( $dests as $d ) {
						echo '<li><a href="' . esc_url( get_term_link( $d ) ) . '">' . esc_html( $d->name ) . '</a></li>';
					}
				} else {
					echo '<li>Add destinations in wp-admin</li>';
				}
				?>
				</ul>
			</div>
			<div>
				<h4><?php esc_html_e( 'Company', 'trekways' ); ?></h4>
				<?php
				if ( has_nav_menu( 'footer' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => '',
						'items_wrap'     => '<ul style="list-style:none;padding:0;margin:0;line-height:2">%3$s</ul>',
						'depth'          => 1,
					) );
				} else {
					echo '<ul style="list-style:none;padding:0;margin:0;line-height:2"><li>Set a Footer menu under Appearance &gt; Menus</li></ul>';
				}
				?>
			</div>
			<div>
				<h4><?php esc_html_e( 'Contact', 'trekways' ); ?></h4>
				<p>Thamel, Kathmandu, Nepal<br>
				<a href="tel:+9779841666232">+977 9841666232</a></p>
			</div>
		</div>

		<div class="tw-footer__bottom">
			<span>&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. All rights reserved.</span>
			<span><?php esc_html_e( 'Built on a custom WordPress theme.', 'trekways' ); ?></span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
