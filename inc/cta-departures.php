<?php
/**
 * Closing CTA with fixed departures.
 *
 * - Trips get a "Fixed departures" box (date, price, seats, minimum group).
 * - The homepage block lists upcoming departures from all trips, filterable
 *   by region and by day, and opens one booking form for both "Book now"
 *   and "Send an enquiry". The form posts to the existing handler in
 *   inc/booking.php (action=trekways_booking).
 *
 * @package TrekWays
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ---------------------------------------------------------------
 * Trip: fixed departures meta box.
 * Stored as one array in _trip_departures:
 *   array( array( date, price, was, seats, booked, min ), ... )
 * ------------------------------------------------------------- */

function trekways_fd_meta_box() {
	add_meta_box( 'trekways_trip_departures', __( 'Fixed departures', 'trekways' ), 'trekways_fd_meta_box_render', 'trip', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'trekways_fd_meta_box' );

function trekways_fd_meta_box_render( $post ) {
	wp_nonce_field( 'trekways_save_departures', 'trekways_fd_nonce' );
	$rows = get_post_meta( $post->ID, '_trip_departures', true );
	$rows = is_array( $rows ) ? $rows : array();
	for ( $i = 0; $i < 3; $i++ ) {
		$rows[] = array();
	}
	$cols = array(
		'date'   => array( __( 'Start date', 'trekways' ), 'date' ),
		'price'  => array( __( 'Price USD', 'trekways' ), 'number' ),
		'was'    => array( __( 'Old price', 'trekways' ), 'number' ),
		'seats'  => array( __( 'Seats', 'trekways' ), 'number' ),
		'booked' => array( __( 'Booked', 'trekways' ), 'number' ),
		'min'    => array( __( 'Min. group', 'trekways' ), 'number' ),
	);
	echo '<p class="description">' . esc_html__( 'One row per departure. Rows without a start date are ignored, so clear the date to remove a row. Save to get more empty rows. Leave price empty to use the trip price. Status is worked out from Seats, Booked and Min. group: Guaranteed once Booked reaches Min. group, Sold out when Booked reaches Seats.', 'trekways' ) . '</p>';
	echo '<table class="widefat striped" style="margin-top:8px"><thead><tr>';
	foreach ( $cols as $c ) {
		echo '<th>' . esc_html( $c[0] ) . '</th>';
	}
	echo '</tr></thead><tbody>';
	foreach ( $rows as $i => $r ) {
		echo '<tr>';
		foreach ( $cols as $key => $c ) {
			$val = isset( $r[ $key ] ) ? $r[ $key ] : '';
			if ( 'number' === $c[1] && '' !== $val && 0 === (int) $val && 'booked' !== $key ) {
				$val = '';
			}
			echo '<td><input type="' . esc_attr( $c[1] ) . '" name="trekways_fd[' . (int) $i . '][' . esc_attr( $key ) . ']" value="' . esc_attr( $val ) . '"' . ( 'number' === $c[1] ? ' min="0" style="width:90px"' : '' ) . '></td>';
		}
		echo '</tr>';
	}
	echo '</tbody></table>';
}

function trekways_fd_meta_save( $post_id ) {
	if ( ! isset( $_POST['trekways_fd_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['trekways_fd_nonce'] ), 'trekways_save_departures' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$in  = isset( $_POST['trekways_fd'] ) && is_array( $_POST['trekways_fd'] ) ? wp_unslash( $_POST['trekways_fd'] ) : array(); // phpcs:ignore -- sanitized per field below.
	$out = array();
	foreach ( $in as $r ) {
		$date = isset( $r['date'] ) ? sanitize_text_field( $r['date'] ) : '';
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			continue;
		}
		$row = array( 'date' => $date );
		foreach ( array( 'price', 'was', 'seats', 'booked', 'min' ) as $k ) {
			$row[ $k ] = isset( $r[ $k ] ) && '' !== $r[ $k ] ? absint( $r[ $k ] ) : 0;
		}
		$out[] = $row;
	}
	usort( $out, function ( $a, $b ) {
		return strcmp( $a['date'], $b['date'] );
	} );
	if ( $out ) {
		update_post_meta( $post_id, '_trip_departures', $out );
	} else {
		delete_post_meta( $post_id, '_trip_departures' );
	}
}
add_action( 'save_post_trip', 'trekways_fd_meta_save' );

/* ---------------------------------------------------------------
 * Customizer.
 * ------------------------------------------------------------- */

function trekways_plan_defaults() {
	return array(
		'eyebrow'   => 'Plan your trek',
		'title'     => "Tell us your dates. We'll plan the rest.",
		'text'      => 'Private trek on your own dates, or join a fixed group departure below. Either way, you talk to the people who will guide you.',
		'btn'       => 'Send an enquiry',
		'whatsapp'  => '',
		'reply'     => '24 hours',
		'fd_title'  => 'Fixed departures',
		'fd_intro'  => 'Join a small group on a set date. Pick a region, or pick a day on the calendar.',
		'fd_all'    => '',
	);
}

function trekways_plan_mod( $key ) {
	$d = trekways_plan_defaults();
	return get_theme_mod( 'trekways_plan_' . $key, isset( $d[ $key ] ) ? $d[ $key ] : '' );
}

function trekways_plan_customizer( $wp_customize ) {
	$d = trekways_plan_defaults();
	$wp_customize->add_section( 'trekways_plan', array(
		'title'       => __( 'Plan Your Trek (CTA)', 'trekways' ),
		'priority'    => 38,
		'description' => __( 'Closing call to action and fixed departures. Departures are added on each trip, in the "Fixed departures" box.', 'trekways' ),
	) );

	$wp_customize->add_setting( 'trekways_plan_enable', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'trekways_plan_enable_ctrl', array(
		'label' => __( 'Show this section', 'trekways' ), 'section' => 'trekways_plan',
		'settings' => 'trekways_plan_enable', 'type' => 'checkbox',
	) );

	$fields = array(
		'eyebrow'  => array( __( 'Small text above the title', 'trekways' ), 'text', 'sanitize_text_field' ),
		'title'    => array( __( 'Title', 'trekways' ), 'text', 'sanitize_text_field' ),
		'text'     => array( __( 'Text', 'trekways' ), 'textarea', 'sanitize_textarea_field' ),
		'btn'      => array( __( 'Enquiry button text', 'trekways' ), 'text', 'sanitize_text_field' ),
		'whatsapp' => array( __( 'WhatsApp number with country code, e.g. 9779800000000. Empty hides the button.', 'trekways' ), 'text', 'sanitize_text_field' ),
		'reply'    => array( __( 'Typical reply time, e.g. 24 hours. Empty hides the line. Keep it in line with the confirmation email.', 'trekways' ), 'text', 'sanitize_text_field' ),
		'fd_title' => array( __( 'Departures title', 'trekways' ), 'text', 'sanitize_text_field' ),
		'fd_intro' => array( __( 'Departures intro', 'trekways' ), 'text', 'sanitize_text_field' ),
		'fd_all'   => array( __( '"View all departures" link (empty hides it)', 'trekways' ), 'url', 'esc_url_raw' ),
	);
	foreach ( $fields as $key => $f ) {
		$wp_customize->add_setting( 'trekways_plan_' . $key, array( 'default' => $d[ $key ], 'sanitize_callback' => $f[2] ) );
		$wp_customize->add_control( 'trekways_plan_' . $key . '_ctrl', array(
			'label' => $f[0], 'section' => 'trekways_plan',
			'settings' => 'trekways_plan_' . $key, 'type' => $f[1],
		) );
	}
}
add_action( 'customize_register', 'trekways_plan_customizer' );

/* ---------------------------------------------------------------
 * Data.
 * ------------------------------------------------------------- */

/**
 * Upcoming departures from all published trips, soonest first.
 *
 * @return array
 */
function trekways_fd_get() {
	$today = current_time( 'Y-m-d' );
	$q     = new WP_Query( array(
		'post_type'      => 'trip',
		'post_status'    => 'publish',
		'posts_per_page' => 200,
		'no_found_rows'  => true,
		'meta_key'       => '_trip_departures', // phpcs:ignore -- small, admin-curated set.
		'meta_compare'   => 'EXISTS',
	) );
	$out = array();
	foreach ( $q->posts as $p ) {
		$rows = get_post_meta( $p->ID, '_trip_departures', true );
		if ( ! is_array( $rows ) ) {
			continue;
		}
		$base_price = (int) preg_replace( '/[^\d]/', '', (string) trekways_meta( $p->ID, '_trip_price' ) );
		$days       = preg_match( '/\d+/', (string) trekways_meta( $p->ID, '_trip_duration' ), $m ) ? (int) $m[0] : 0;
		$terms      = get_the_terms( $p->ID, 'region' );
		$region     = null;
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $t ) {
				if ( 0 === (int) $t->parent ) {
					$region = $t;
					break;
				}
			}
			if ( ! $region ) {
				$region = $terms[0];
			}
		}
		foreach ( $rows as $r ) {
			if ( empty( $r['date'] ) || $r['date'] < $today ) {
				continue;
			}
			$seats  = (int) $r['seats'];
			$booked = (int) $r['booked'];
			$min    = max( 1, (int) $r['min'] );
			$left   = $seats ? max( 0, $seats - $booked ) : null;
			if ( null !== $left && 0 === $left ) {
				$status = 'soldout';
			} elseif ( $booked >= $min ) {
				$status = 'guaranteed';
			} else {
				$status = 'available';
			}
			$out[] = array(
				'trip_id' => $p->ID,
				'trip'    => get_the_title( $p ),
				'url'     => get_permalink( $p ),
				'region'  => $region ? $region->name : '',
				'rslug'   => $region ? $region->slug : 'other',
				'days'    => $days,
				'date'    => $r['date'],
				'price'   => (int) $r['price'] ? (int) $r['price'] : $base_price,
				'was'     => (int) $r['was'],
				'left'    => $left,
				'need'    => max( 0, $min - $booked ),
				'status'  => $status,
			);
		}
	}
	usort( $out, function ( $a, $b ) {
		return strcmp( $a['date'], $b['date'] );
	} );
	return apply_filters( 'trekways_fixed_departures', array_slice( $out, 0, 80 ) );
}

/* ---------------------------------------------------------------
 * Render helpers.
 * ------------------------------------------------------------- */

function trekways_fd_money( $n ) {
	return 'USD ' . number_format_i18n( (int) $n );
}

function trekways_fd_seats_line( $d ) {
	if ( 'soldout' === $d['status'] ) {
		return array( __( 'Fully booked', 'trekways' ), false );
	}
	if ( null === $d['left'] ) {
		return array( __( 'Seats available', 'trekways' ), false );
	}
	if ( $d['left'] <= 3 ) {
		/* translators: %d: seats left. */
		return array( sprintf( _n( 'Only %d seat left', 'Only %d seats left', $d['left'], 'trekways' ), $d['left'] ), true );
	}
	if ( 'guaranteed' === $d['status'] ) {
		/* translators: %d: seats left. */
		return array( sprintf( __( '%d seats left', 'trekways' ), $d['left'] ), false );
	}
	/* translators: 1: seats left, 2: travellers still needed. */
	return array( sprintf( __( '%1$d seats left, %2$d more to guarantee', 'trekways' ), $d['left'], $d['need'] ), false );
}

/**
 * Arrow icon.
 */
function trekways_plan_icon( $dir = 'right', $size = 16 ) {
	$paths = array(
		'right' => 'M3 8h9M8.5 4l4 4-4 4',
		'next'  => 'M6 3.5 10.5 8 6 12.5',
		'prev'  => 'M10 3.5 5.5 8l4.5 4.5',
		'close' => 'M4 4l8 8M12 4l-8 8',
	);
	return '<svg width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 16 16" aria-hidden="true"><path d="' . esc_attr( $paths[ $dir ] ) . '" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/* ---------------------------------------------------------------
 * Section.
 * ------------------------------------------------------------- */

function trekways_plan_section() {
	if ( ! get_theme_mod( 'trekways_plan_enable', true ) ) {
		return;
	}
	$deps    = trekways_fd_get();
	$wa      = preg_replace( '/[^\d]/', '', (string) trekways_plan_mod( 'whatsapp' ) );
	$reply   = trekways_plan_mod( 'reply' );
	$all_url = trekways_plan_mod( 'fd_all' );
	$eyebrow = trekways_plan_mod( 'eyebrow' );
	$text    = trekways_plan_mod( 'text' );

	$regions = array();
	foreach ( $deps as $d ) {
		if ( ! isset( $regions[ $d['rslug'] ] ) ) {
			$regions[ $d['rslug'] ] = array( $d['region'] ? $d['region'] : __( 'Other', 'trekways' ), 0 );
		}
		$regions[ $d['rslug'] ][1]++;
	}

	$i18n = array(
		'next'      => __( 'Next departures', 'trekways' ),
		/* translators: %s: date. */
		'departing' => __( 'Departing %s', 'trekways' ),
		/* translators: %s: region. */
		'in'        => __( 'in %s', 'trekways' ),
		'one'       => __( '1 departure', 'trekways' ),
		/* translators: %d: number of departures. */
		'many'      => __( '%d departures', 'trekways' ),
		'scroll'    => __( 'scroll for more', 'trekways' ),
		/* translators: 1: trip, 2: date. */
		'book'      => __( 'Book %1$s, %2$s', 'trekways' ),
		'enquire'   => trekways_plan_mod( 'btn' ),
		'trek'      => __( 'Trek', 'trekways' ),
		'depdate'   => __( 'Departure date', 'trekways' ),
		/* translators: %d: departures that day. */
		'dayLabel'  => __( '%d departures', 'trekways' ),
	);
	?>
<section class="tw-cta" id="tw-cta" aria-labelledby="tw-cta-title">
	<div class="tw-cta__wrap">
		<div class="tw-cta__band">
			<div>
				<?php if ( $eyebrow ) : ?><small><?php echo esc_html( $eyebrow ); ?></small><?php endif; ?>
				<h2 id="tw-cta-title"><?php echo esc_html( trekways_plan_mod( 'title' ) ); ?></h2>
				<?php if ( $text ) : ?><p><?php echo esc_html( $text ); ?></p><?php endif; ?>
			</div>
			<div class="tw-cta__actions">
				<button type="button" class="tw-pbtn tw-pbtn--amber" data-tw-book="custom"><?php echo esc_html( trekways_plan_mod( 'btn' ) ); ?> <?php echo trekways_plan_icon(); // phpcs:ignore -- static SVG. ?></button>
				<?php if ( $wa ) : ?>
					<a class="tw-pbtn tw-pbtn--ghost" href="<?php echo esc_url( 'https://wa.me/' . $wa ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp us', 'trekways' ); ?></a>
				<?php endif; ?>
				<?php if ( $reply ) : ?>
					<span class="tw-cta__reply"><i aria-hidden="true"></i><?php
						/* translators: %s: reply time, e.g. 24 hours. */
						printf( esc_html__( 'We usually reply within %s', 'trekways' ), '<b>' . esc_html( $reply ) . '</b>' );
					?></span>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $deps ) : ?>
		<div class="tw-fd" id="tw-fd" data-i18n="<?php echo esc_attr( wp_json_encode( $i18n ) ); ?>" data-today="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>">
			<div class="tw-fd__head">
				<h3><?php echo esc_html( trekways_plan_mod( 'fd_title' ) ); ?></h3>
				<?php $fd_intro = trekways_plan_mod( 'fd_intro' ); if ( $fd_intro ) : ?><p><?php echo esc_html( $fd_intro ); ?></p><?php endif; ?>
			</div>

			<?php if ( count( $regions ) > 1 ) : ?>
			<div class="tw-fd__regions">
				<button type="button" class="tw-fd__arrow" id="tw-fd-rprev" aria-label="<?php esc_attr_e( 'Previous regions', 'trekways' ); ?>"><?php echo trekways_plan_icon( 'prev', 14 ); // phpcs:ignore -- static SVG. ?></button>
				<div class="tw-fd__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Regions', 'trekways' ); ?>" id="tw-fd-tabs">
					<button type="button" class="tw-fd__tab" role="tab" data-r="all" aria-selected="true"><?php
						/* translators: %d: number of departures. */
						printf( esc_html__( 'All (%d)', 'trekways' ), count( $deps ) );
					?></button>
					<?php foreach ( $regions as $slug => $r ) : ?>
						<button type="button" class="tw-fd__tab" role="tab" data-r="<?php echo esc_attr( $slug ); ?>" aria-selected="false"><?php echo esc_html( $r[0] ); ?> (<?php echo (int) $r[1]; ?>)</button>
					<?php endforeach; ?>
				</div>
				<button type="button" class="tw-fd__arrow" id="tw-fd-rnext" aria-label="<?php esc_attr_e( 'Next regions', 'trekways' ); ?>"><?php echo trekways_plan_icon( 'next', 14 ); // phpcs:ignore -- static SVG. ?></button>
			</div>
			<?php endif; ?>

			<div class="tw-fd__body">
				<div class="tw-cal" id="tw-cal" aria-label="<?php esc_attr_e( 'Departure calendar', 'trekways' ); ?>">
					<div class="tw-cal__top">
						<button type="button" class="tw-cal__nav" id="tw-cal-prev" aria-label="<?php esc_attr_e( 'Previous month', 'trekways' ); ?>"><?php echo trekways_plan_icon( 'prev', 14 ); // phpcs:ignore -- static SVG. ?></button>
						<b id="tw-cal-title" aria-live="polite"></b>
						<button type="button" class="tw-cal__nav" id="tw-cal-next" aria-label="<?php esc_attr_e( 'Next month', 'trekways' ); ?>"><?php echo trekways_plan_icon( 'next', 14 ); // phpcs:ignore -- static SVG. ?></button>
					</div>
					<div class="tw-cal__grid" id="tw-cal-grid"></div>
					<div class="tw-cal__legend"><span><?php esc_html_e( 'Departure day', 'trekways' ); ?></span><button type="button" class="tw-cal__clear" id="tw-cal-clear" hidden><?php esc_html_e( 'Clear date', 'trekways' ); ?></button></div>
				</div>

				<div>
					<p class="tw-fd__filter" id="tw-fd-filter" aria-live="polite"><?php esc_html_e( 'Next departures', 'trekways' ); ?></p>
					<ul class="tw-fd__list" id="tw-fd-list" tabindex="0" aria-label="<?php esc_attr_e( 'Fixed departures, scrollable', 'trekways' ); ?>">
						<?php foreach ( $deps as $i => $d ) :
							$ts    = strtotime( $d['date'] );
							$end   = $d['days'] ? strtotime( '+' . ( $d['days'] - 1 ) . ' days', $ts ) : 0;
							$seats = trekways_fd_seats_line( $d );
							$label = array(
								'guaranteed' => __( 'Guaranteed', 'trekways' ),
								'available'  => __( 'Available', 'trekways' ),
								'soldout'    => __( 'Sold out', 'trekways' ),
							);
							$sub = array();
							if ( $d['region'] ) {
								$sub[] = $d['region'];
							}
							if ( $d['days'] ) {
								/* translators: %d: number of days. */
								$sub[] = sprintf( _n( '%d day', '%d days', $d['days'], 'trekways' ), $d['days'] );
							}
							$sub[] = date_i18n( 'j M Y', $ts ) . ( $end ? ' ' . __( 'to', 'trekways' ) . ' ' . date_i18n( 'j M Y', $end ) : '' );
							?>
							<li class="tw-dep" style="--i:<?php echo (int) min( $i, 8 ); ?>" data-date="<?php echo esc_attr( $d['date'] ); ?>" data-r="<?php echo esc_attr( $d['rslug'] ); ?>">
								<div class="tw-dep__date"><small><?php echo esc_html( strtoupper( date_i18n( 'M', $ts ) ) ); ?></small><b><?php echo esc_html( date_i18n( 'j', $ts ) ); ?></b><span><?php echo esc_html( date_i18n( 'D', $ts ) ); ?></span></div>
								<div class="tw-dep__trip"><a href="<?php echo esc_url( $d['url'] ); ?>"><?php echo esc_html( $d['trip'] ); ?></a><span><?php echo esc_html( implode( ' / ', $sub ) ); ?></span></div>
								<div class="tw-dep__price">
									<?php if ( $d['price'] ) : ?>
										<small><?php esc_html_e( 'Per person', 'trekways' ); ?></small>
										<?php if ( $d['was'] > $d['price'] ) : ?><s><?php echo esc_html( trekways_fd_money( $d['was'] ) ); ?></s><?php endif; ?>
										<strong><?php echo esc_html( trekways_fd_money( $d['price'] ) ); ?></strong>
									<?php else : ?>
										<strong class="tw-dep__ask"><?php esc_html_e( 'On request', 'trekways' ); ?></strong>
									<?php endif; ?>
								</div>
								<div class="tw-dep__status"><span class="tw-st tw-st--<?php echo esc_attr( $d['status'] ); ?>"><?php echo esc_html( $label[ $d['status'] ] ); ?></span><span class="tw-dep__seats<?php echo $seats[1] ? ' low' : ''; ?>"><?php echo esc_html( $seats[0] ); ?></span></div>
								<?php if ( 'soldout' === $d['status'] ) : ?>
									<span class="tw-dep__btn tw-dep__btn--off"><?php esc_html_e( 'Sold out', 'trekways' ); ?></span>
								<?php else : ?>
									<button type="button" class="tw-dep__btn" data-tw-book="departure" data-trip-id="<?php echo (int) $d['trip_id']; ?>" data-trip="<?php echo esc_attr( $d['trip'] ); ?>" data-date="<?php echo esc_attr( $d['date'] ); ?>" data-date-label="<?php echo esc_attr( date_i18n( get_option( 'date_format' ), $ts ) ); ?>" data-max="<?php echo null === $d['left'] ? '' : (int) $d['left']; ?>"><?php esc_html_e( 'Book now', 'trekways' ); ?></button>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
						<li class="tw-fd__empty" hidden><?php esc_html_e( 'No fixed departures here yet.', 'trekways' ); ?> <button type="button" class="tw-fd__ask" data-tw-book="custom"><?php esc_html_e( 'Ask us about private dates', 'trekways' ); ?></button></li>
					</ul>
					<div class="tw-fd__foot">
						<span id="tw-fd-count"><?php
							/* translators: %d: number of departures. */
							echo esc_html( sprintf( _n( '%d departure', '%d departures', count( $deps ), 'trekways' ), count( $deps ) ) );
						?></span>
						<?php if ( $all_url ) : ?>
							<a class="tw-fd__all" href="<?php echo esc_url( $all_url ); ?>"><?php esc_html_e( 'View all departures', 'trekways' ); ?> <?php echo trekways_plan_icon( 'right', 14 ); // phpcs:ignore -- static SVG. ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>

	<dialog class="tw-bk" id="tw-bk" aria-labelledby="tw-bk-title">
		<form class="tw-bk__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<button type="button" class="tw-bk__close" data-tw-close aria-label="<?php esc_attr_e( 'Close', 'trekways' ); ?>"><?php echo trekways_plan_icon( 'close', 18 ); // phpcs:ignore -- static SVG. ?></button>
			<h3 id="tw-bk-title"><?php echo esc_html( trekways_plan_mod( 'btn' ) ); ?></h3>
			<p class="tw-bk__sub" id="tw-bk-sub"></p>
			<p class="tw-bk__error" id="tw-bk-error" hidden><?php esc_html_e( 'Please add your name and a valid email address, then send again.', 'trekways' ); ?></p>

			<input type="hidden" name="action" value="trekways_booking">
			<?php wp_nonce_field( 'trekways_booking', 'trekways_booking_nonce' ); ?>
			<input type="hidden" name="booking_type" value="custom">
			<input type="hidden" name="trip_id" value="">

			<div class="tw-bk__grid">
				<label class="tw-bk__f tw-bk__f--wide tw-bk__trip"><span><?php esc_html_e( 'Which trek? (optional)', 'trekways' ); ?></span><input type="text" name="trip_name" autocomplete="off"></label>
				<label class="tw-bk__f tw-bk__date"><span><?php esc_html_e( 'Preferred start date', 'trekways' ); ?></span><input type="date" name="departure_date"></label>
				<label class="tw-bk__f"><span><?php esc_html_e( 'Travellers', 'trekways' ); ?></span><input type="number" name="travellers" min="1" value="2" required></label>
				<label class="tw-bk__f"><span><?php esc_html_e( 'Full name', 'trekways' ); ?></span><input type="text" name="full_name" autocomplete="name" required></label>
				<label class="tw-bk__f"><span><?php esc_html_e( 'Email', 'trekways' ); ?></span><input type="email" name="email" autocomplete="email" required></label>
				<label class="tw-bk__f"><span><?php esc_html_e( 'Phone / WhatsApp', 'trekways' ); ?></span><input type="tel" name="phone" autocomplete="tel"></label>
				<label class="tw-bk__f"><span><?php esc_html_e( 'Country', 'trekways' ); ?></span><input type="text" name="country" autocomplete="country-name"></label>
				<label class="tw-bk__f tw-bk__f--wide"><span><?php esc_html_e( 'Message (optional)', 'trekways' ); ?></span><textarea name="message" rows="3"></textarea></label>
			</div>
			<button type="submit" class="tw-pbtn tw-pbtn--amber tw-bk__send"><?php esc_html_e( 'Send request', 'trekways' ); ?> <?php echo trekways_plan_icon(); // phpcs:ignore -- static SVG. ?></button>
			<p class="tw-bk__note"><?php esc_html_e( 'No payment now. We confirm availability and details by email first.', 'trekways' ); ?></p>
		</form>
	</dialog>
</section>
	<?php
}