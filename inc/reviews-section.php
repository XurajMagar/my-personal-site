<?php
/**
 * Reviews section: Tripadvisor reviews shown as airmail postcards.
 *
 * Reviews are entered by hand under Reviews in the admin. Everything the
 * template needs comes from trekways_reviews_get(), so a Tripadvisor
 * Content API source can replace that one function later.
 *
 * @package TrekWays
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ---------------------------------------------------------------
 * Post type: admin-only list of reviews.
 * ------------------------------------------------------------- */

function trekways_reviews_post_type() {
	register_post_type( 'tw_review', array(
		'labels' => array(
			'name'               => __( 'Reviews', 'trekways' ),
			'singular_name'      => __( 'Review', 'trekways' ),
			'menu_name'          => __( 'Reviews', 'trekways' ),
			'all_items'          => __( 'All Reviews', 'trekways' ),
			'add_new'            => __( 'Add Review', 'trekways' ),
			'add_new_item'       => __( 'Add Review', 'trekways' ),
			'edit_item'          => __( 'Edit Review', 'trekways' ),
			'new_item'           => __( 'New Review', 'trekways' ),
			'search_items'       => __( 'Search Reviews', 'trekways' ),
			'not_found'          => __( 'No reviews yet.', 'trekways' ),
			'not_found_in_trash' => __( 'No reviews in Trash.', 'trekways' ),
			'item_published'     => __( 'Review published.', 'trekways' ),
			'item_updated'       => __( 'Review updated.', 'trekways' ),
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'menu_icon'           => 'dashicons-format-quote',
		'supports'            => array( 'title', 'page-attributes' ),
	) );
}
add_action( 'init', 'trekways_reviews_post_type' );

function trekways_reviews_title_placeholder( $text, $post ) {
	return ( $post && 'tw_review' === $post->post_type ) ? __( 'Review title, as written on Tripadvisor', 'trekways' ) : $text;
}
add_filter( 'enter_title_here', 'trekways_reviews_title_placeholder', 10, 2 );

/**
 * Field definitions: key => array( label, type ).
 *
 * @return array
 */
function trekways_reviews_fields() {
	return array(
		'_rev_text'     => array( __( 'Review text', 'trekways' ), 'textarea' ),
		'_rev_rating'   => array( __( 'Rating (bubbles)', 'trekways' ), 'rating' ),
		'_rev_name'     => array( __( 'Reviewer name', 'trekways' ), 'text' ),
		'_rev_location' => array( __( 'Reviewer home town', 'trekways' ), 'text' ),
		'_rev_contrib'  => array( __( 'Contributions', 'trekways' ), 'number' ),
		'_rev_exp'      => array( __( 'Date of experience (e.g. April 2026)', 'trekways' ), 'text' ),
		'_rev_trip'     => array( __( 'Trip type', 'trekways' ), 'trip' ),
		'_rev_written'  => array( __( 'Written on', 'trekways' ), 'date' ),
		'_rev_url'      => array( __( 'Link to this review on Tripadvisor', 'trekways' ), 'url' ),
	);
}

function trekways_reviews_trip_types() {
	return array( 'Family', 'Couples', 'Solo', 'Business', 'Friends' );
}

function trekways_reviews_meta_box() {
	add_meta_box( 'trekways_review_fields', __( 'Review details', 'trekways' ), 'trekways_reviews_meta_box_render', 'tw_review', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'trekways_reviews_meta_box' );

function trekways_reviews_meta_box_render( $post ) {
	wp_nonce_field( 'trekways_save_review', 'trekways_review_nonce' );
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( trekways_reviews_fields() as $key => $f ) {
		$val = get_post_meta( $post->ID, $key, true );
		$id  = 'tw' . $key;
		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $f[0] ) . '</label></th><td>';
		switch ( $f[1] ) {
			case 'textarea':
				echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" rows="7" class="large-text">' . esc_textarea( $val ) . '</textarea>';
				break;
			case 'rating':
				$val = $val ? (int) $val : 5;
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '">';
				for ( $r = 5; $r >= 1; $r-- ) {
					echo '<option value="' . (int) $r . '"' . selected( $val, $r, false ) . '>' . (int) $r . '</option>';
				}
				echo '</select>';
				break;
			case 'trip':
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '"><option value="">' . esc_html__( 'Not set', 'trekways' ) . '</option>';
				foreach ( trekways_reviews_trip_types() as $t ) {
					echo '<option value="' . esc_attr( $t ) . '"' . selected( $val, $t, false ) . '>' . esc_html( $t ) . '</option>';
				}
				echo '</select>';
				break;
			case 'number':
				echo '<input type="number" min="0" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" class="small-text">';
				break;
			case 'date':
				echo '<input type="date" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '">';
				break;
			case 'url':
				echo '<input type="url" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" class="large-text" placeholder="https://www.tripadvisor.com/...">';
				break;
			default:
				echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" class="regular-text">';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
	echo '<p class="description">' . esc_html__( 'Order on the homepage follows "Order" in Page Attributes (lower first), then newest first.', 'trekways' ) . '</p>';
}

function trekways_reviews_meta_save( $post_id ) {
	if ( ! isset( $_POST['trekways_review_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['trekways_review_nonce'] ), 'trekways_save_review' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( trekways_reviews_fields() as $key => $f ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $key ] );
		switch ( $f[1] ) {
			case 'textarea':
				$val = sanitize_textarea_field( $raw );
				break;
			case 'rating':
				$val = min( 5, max( 1, absint( $raw ) ) );
				break;
			case 'number':
				$val = '' === $raw ? '' : absint( $raw );
				break;
			case 'trip':
				$val = in_array( $raw, trekways_reviews_trip_types(), true ) ? $raw : '';
				break;
			case 'date':
				$val = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ? $raw : '';
				break;
			case 'url':
				$val = esc_url_raw( $raw );
				break;
			default:
				$val = sanitize_text_field( $raw );
		}
		update_post_meta( $post_id, $key, $val );
	}
}
add_action( 'save_post_tw_review', 'trekways_reviews_meta_save' );

/* Admin list: show reviewer and rating. */
function trekways_reviews_columns( $cols ) {
	$out = array();
	foreach ( $cols as $k => $v ) {
		$out[ $k ] = $v;
		if ( 'title' === $k ) {
			$out['tw_rev_name']   = __( 'Reviewer', 'trekways' );
			$out['tw_rev_rating'] = __( 'Rating', 'trekways' );
		}
	}
	return $out;
}
add_filter( 'manage_tw_review_posts_columns', 'trekways_reviews_columns' );

function trekways_reviews_column_value( $col, $post_id ) {
	if ( 'tw_rev_name' === $col ) {
		echo esc_html( get_post_meta( $post_id, '_rev_name', true ) );
	} elseif ( 'tw_rev_rating' === $col ) {
		echo esc_html( get_post_meta( $post_id, '_rev_rating', true ) );
	}
}
add_action( 'manage_tw_review_posts_custom_column', 'trekways_reviews_column_value', 10, 2 );

/* ---------------------------------------------------------------
 * Customizer: heading and the Tripadvisor summary card.
 * ------------------------------------------------------------- */

function trekways_reviews_defaults() {
	return array(
		'title'       => 'What trekkers wrote after coming home',
		'intro'       => 'Recent reviews from Tripadvisor, shown as written.',
		'score'       => '',
		'count'       => '',
		'listing_url' => '',
		'max'         => 12,
	);
}

function trekways_reviews_mod( $key ) {
	$d = trekways_reviews_defaults();
	return get_theme_mod( 'trekways_reviews_' . $key, isset( $d[ $key ] ) ? $d[ $key ] : '' );
}

function trekways_reviews_customizer( $wp_customize ) {
	$d = trekways_reviews_defaults();

	$wp_customize->add_section( 'trekways_reviews', array(
		'title'       => __( 'Reviews Section', 'trekways' ),
		'priority'    => 37,
		'description' => __( 'Reviews themselves are added under Reviews in the admin menu. Copy the score and count from your Tripadvisor listing.', 'trekways' ),
	) );

	$wp_customize->add_setting( 'trekways_reviews_enable', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'trekways_reviews_enable_ctrl', array(
		'label' => __( 'Show the reviews section', 'trekways' ), 'section' => 'trekways_reviews',
		'settings' => 'trekways_reviews_enable', 'type' => 'checkbox',
	) );

	$fields = array(
		'title'       => array( __( 'Title', 'trekways' ), 'text', 'sanitize_text_field' ),
		'intro'       => array( __( 'Line under the title', 'trekways' ), 'text', 'sanitize_text_field' ),
		'score'       => array( __( 'Tripadvisor score (e.g. 5.0). Empty hides the summary card.', 'trekways' ), 'text', 'trekways_reviews_sanitize_score' ),
		'count'       => array( __( 'Number of Tripadvisor reviews', 'trekways' ), 'number', 'trekways_reviews_sanitize_count' ),
		'listing_url' => array( __( 'Your Tripadvisor listing URL', 'trekways' ), 'url', 'esc_url_raw' ),
		'max'         => array( __( 'Maximum reviews to show', 'trekways' ), 'number', 'absint' ),
	);
	foreach ( $fields as $key => $f ) {
		$wp_customize->add_setting( 'trekways_reviews_' . $key, array( 'default' => $d[ $key ], 'sanitize_callback' => $f[2] ) );
		$wp_customize->add_control( 'trekways_reviews_' . $key . '_ctrl', array(
			'label' => $f[0], 'section' => 'trekways_reviews',
			'settings' => 'trekways_reviews_' . $key, 'type' => $f[1],
		) );
	}
}
add_action( 'customize_register', 'trekways_reviews_customizer' );

function trekways_reviews_sanitize_score( $v ) {
	$v = trim( (string) $v );
	if ( '' === $v || ! is_numeric( $v ) ) {
		return '';
	}
	return number_format( min( 5, max( 0, (float) $v ) ), 1 );
}

function trekways_reviews_sanitize_count( $v ) {
	return '' === trim( (string) $v ) ? '' : absint( $v );
}

/* ---------------------------------------------------------------
 * Data. Swap this function for an API source later.
 * ------------------------------------------------------------- */

/**
 * Reviews in display order.
 *
 * @return array Each item: title, text, rating, name, location, contrib, exp, trip, written (Y-m-d), url.
 */
function trekways_reviews_get() {
	$max = max( 1, (int) trekways_reviews_mod( 'max' ) );
	$q   = new WP_Query( array(
		'post_type'      => 'tw_review',
		'post_status'    => 'publish',
		'posts_per_page' => min( 30, $max ),
		'no_found_rows'  => true,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	) );
	$out = array();
	foreach ( $q->posts as $p ) {
		$out[] = array(
			'title'    => get_the_title( $p ),
			'text'     => get_post_meta( $p->ID, '_rev_text', true ),
			'rating'   => (int) get_post_meta( $p->ID, '_rev_rating', true ),
			'name'     => get_post_meta( $p->ID, '_rev_name', true ),
			'location' => get_post_meta( $p->ID, '_rev_location', true ),
			'contrib'  => get_post_meta( $p->ID, '_rev_contrib', true ),
			'exp'      => get_post_meta( $p->ID, '_rev_exp', true ),
			'trip'     => get_post_meta( $p->ID, '_rev_trip', true ),
			'written'  => get_post_meta( $p->ID, '_rev_written', true ),
			'url'      => get_post_meta( $p->ID, '_rev_url', true ),
		);
	}
	return apply_filters( 'trekways_reviews', $out );
}

/* ---------------------------------------------------------------
 * Render.
 * ------------------------------------------------------------- */

/**
 * Bubble rating markup.
 *
 * @param float  $n     Rating, 0-5, halves allowed.
 * @param string $extra Extra class.
 * @return string
 */
function trekways_reviews_bubbles( $n, $extra = '' ) {
	$n   = max( 0, min( 5, (float) $n ) );
	/* translators: %s: rating, e.g. 4.5. */
	$out = '<span class="tw-bub' . ( $extra ? ' ' . esc_attr( $extra ) : '' ) . '" role="img" aria-label="' . esc_attr( sprintf( __( '%s of 5 bubbles', 'trekways' ), $n ) ) . '">';
	for ( $i = 1; $i <= 5; $i++ ) {
		$cls  = $n >= $i ? 'on' : ( $n >= $i - 0.5 ? 'half' : '' );
		$out .= '<span' . ( $cls ? ' class="' . $cls . '"' : '' ) . '></span>';
	}
	return $out . '</span>';
}

/**
 * Tripadvisor's word for an overall score.
 */
function trekways_reviews_word( $score ) {
	$s = (float) $score;
	if ( $s >= 4.5 ) {
		return __( 'Excellent', 'trekways' );
	}
	if ( $s >= 3.5 ) {
		return __( 'Very good', 'trekways' );
	}
	if ( $s >= 2.5 ) {
		return __( 'Average', 'trekways' );
	}
	if ( $s >= 1.5 ) {
		return __( 'Poor', 'trekways' );
	}
	return __( 'Terrible', 'trekways' );
}

function trekways_reviews_section() {
	if ( ! get_theme_mod( 'trekways_reviews_enable', true ) ) {
		return;
	}
	$reviews = trekways_reviews_get();
	if ( ! $reviews ) {
		return;
	}
	$score   = trekways_reviews_mod( 'score' );
	$count   = trekways_reviews_mod( 'count' );
	$listing = trekways_reviews_mod( 'listing_url' );
	$intro   = trekways_reviews_mod( 'intro' );
	$stamp   = '<svg viewBox="0 0 52 64" aria-hidden="true"><rect width="52" height="64" fill="#3a2276"/><circle cx="38" cy="16" r="6" fill="#FF9E45"/><path d="M0 46 L14 30 L22 38 L32 24 L52 44 L52 64 L0 64Z" fill="#8B6FE8"/><path d="M32 24 L36 29 L33 28.5 L31 31 L29 28 Z" fill="#fff"/><path d="M0 54 L18 44 L34 52 L52 46 L52 64 L0 64Z" fill="#1E0F42"/></svg>';
	?>
<section class="tw-rev" id="tw-rev" aria-labelledby="tw-rev-title">
	<div class="tw-rev__wrap">
		<div class="tw-rev__head">
			<div>
				<h2 id="tw-rev-title"><?php echo esc_html( trekways_reviews_mod( 'title' ) ); ?></h2>
				<?php if ( $intro ) : ?><p><?php echo esc_html( $intro ); ?></p><?php endif; ?>
			</div>
			<?php if ( '' !== $score ) : ?>
				<div class="tw-rev__sum">
					<div class="tw-rev__score"><?php echo esc_html( $score ); ?></div>
					<div>
						<?php echo trekways_reviews_bubbles( $score, 'tw-bub--lg' ); // phpcs:ignore -- escaped in helper. ?>
						<small><b><?php echo esc_html( trekways_reviews_word( $score ) ); ?></b><?php
						if ( '' !== $count ) {
							/* translators: %s: number of reviews. */
							echo esc_html( sprintf( _n( ', based on %s review', ', based on %s reviews', (int) $count, 'trekways' ), number_format_i18n( (int) $count ) ) );
						}
						?></small>
						<?php if ( $listing ) : ?>
							<a class="tw-ta" href="<?php echo esc_url( $listing ); ?>" target="_blank" rel="noopener"><i aria-hidden="true"></i><?php esc_html_e( 'Read all on Tripadvisor', 'trekways' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<div class="tw-rev__row">
			<button type="button" class="tw-rev__btn" id="tw-rev-prev" aria-label="<?php esc_attr_e( 'Previous reviews', 'trekways' ); ?>"><svg width="18" height="18" viewBox="0 0 16 16" aria-hidden="true"><path d="M10 3.5 5.5 8l4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
			<ul class="tw-rev__list" id="tw-rev-list">
				<?php foreach ( $reviews as $i => $r ) :
					$ts       = $r['written'] ? strtotime( $r['written'] ) : 0;
					$contrib  = '' !== (string) $r['contrib'] ? (int) $r['contrib'] : null;
					$from     = trim( $r['name'] . ( $r['location'] ? ', ' . $r['location'] : '' ), ', ' );
					?>
					<li class="tw-pc" style="--i:<?php echo (int) $i; ?>">
						<div class="tw-pc__in">
							<span class="tw-pc__stamp" aria-hidden="true"><?php echo $stamp; // phpcs:ignore -- static SVG. ?></span>
							<?php if ( $ts ) : ?>
								<span class="tw-pc__mark"><span class="screen-reader-text"><?php esc_html_e( 'Written', 'trekways' ); ?> <?php echo esc_html( date_i18n( get_option( 'date_format' ), $ts ) ); ?></span><span aria-hidden="true"><?php esc_html_e( 'WRITTEN', 'trekways' ); ?><br><?php echo esc_html( strtoupper( date_i18n( 'M j', $ts ) ) ); ?><br><?php echo esc_html( date_i18n( 'Y', $ts ) ); ?></span></span>
							<?php endif; ?>
							<p class="tw-pc__greet"><?php esc_html_e( 'Greetings from the Himalaya', 'trekways' ); ?></p>
							<?php echo trekways_reviews_bubbles( $r['rating'] ); // phpcs:ignore -- escaped in helper. ?>
							<h3><?php echo esc_html( $r['title'] ); ?></h3>
							<?php if ( $r['exp'] || $r['trip'] ) : ?>
								<p class="tw-pc__meta">
									<?php if ( $r['exp'] ) : ?><b><?php esc_html_e( 'Date of experience:', 'trekways' ); ?></b> <?php echo esc_html( $r['exp'] ); ?><?php endif; ?>
									<?php if ( $r['exp'] && $r['trip'] ) : ?><br><?php endif; ?>
									<?php if ( $r['trip'] ) : ?><b><?php esc_html_e( 'Trip type:', 'trekways' ); ?></b> <?php echo esc_html( $r['trip'] ); ?><?php endif; ?>
								</p>
							<?php endif; ?>
							<p class="tw-pc__text"><?php echo esc_html( $r['text'] ); ?></p>
							<button type="button" class="tw-pc__more" aria-expanded="false" data-more="<?php esc_attr_e( 'Read more', 'trekways' ); ?>" data-less="<?php esc_attr_e( 'Show less', 'trekways' ); ?>" hidden><?php esc_html_e( 'Read more', 'trekways' ); ?></button>
							<?php if ( $from ) : ?>
								<div class="tw-pc__from"><?php esc_html_e( 'From', 'trekways' ); ?> <b><?php echo esc_html( $r['name'] ); ?></b><?php echo $r['location'] ? ', ' . esc_html( $r['location'] ) : ''; ?></div>
							<?php endif; ?>
							<div class="tw-pc__foot">
								<span><?php
								if ( null !== $contrib ) {
									/* translators: %s: number of contributions. */
									echo esc_html( sprintf( _n( '%s contribution', '%s contributions', $contrib, 'trekways' ), number_format_i18n( $contrib ) ) );
								}
								?></span>
								<?php if ( $r['url'] ) : ?>
									<a href="<?php echo esc_url( $r['url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'On Tripadvisor', 'trekways' ); ?></a>
								<?php endif; ?>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
			<button type="button" class="tw-rev__btn" id="tw-rev-next" aria-label="<?php esc_attr_e( 'Next reviews', 'trekways' ); ?>"><svg width="18" height="18" viewBox="0 0 16 16" aria-hidden="true"><path d="M6 3.5 10.5 8 6 12.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
		</div>
		<p class="tw-rev__legal"><?php esc_html_e( 'Reviews are the subjective opinions of Tripadvisor members and not of Tripadvisor LLC.', 'trekways' ); ?></p>
	</div>
</section>
	<?php
}