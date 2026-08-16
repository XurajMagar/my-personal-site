<?php
/**
 * Booking + enquiry handling.
 *
 * Decision (locked): every submission is BOTH stored as a private `booking`
 * post (searchable in wp-admin, with a status) AND emailed to the agency and
 * the customer. This is the WordPress-native alternative to a custom DB table.
 *
 * The single-trip template (built later) posts a form to admin-post.php with
 * action=trekways_booking and a matching nonce. Field names below are the
 * contract that form must follow.
 *
 * @package TrekWays
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Handle a booking / enquiry submission.
 */
function trekways_handle_booking() {

	// 1. Verify nonce -----------------------------------------------------
	if ( ! isset( $_POST['trekways_booking_nonce'] ) ||
	     ! wp_verify_nonce( $_POST['trekways_booking_nonce'], 'trekways_booking' ) ) {
		wp_die( esc_html__( 'Security check failed. Please go back and try again.', 'trekways' ) );
	}

	// 2. Collect + sanitise ----------------------------------------------
	$trip_id    = absint( $_POST['trip_id'] ?? 0 );
	$trip_name  = sanitize_text_field( $_POST['trip_name'] ?? '' );
	$full_name  = sanitize_text_field( $_POST['full_name'] ?? '' );
	$email      = sanitize_email( $_POST['email'] ?? '' );
	$phone      = sanitize_text_field( $_POST['phone'] ?? '' );
	$country    = sanitize_text_field( $_POST['country'] ?? '' );
	$travellers = absint( $_POST['travellers'] ?? 1 );
	$dep_date   = sanitize_text_field( $_POST['departure_date'] ?? '' );
	$type       = ( ( $_POST['booking_type'] ?? '' ) === 'departure' ) ? 'departure' : 'custom';
	$message    = sanitize_textarea_field( $_POST['message'] ?? '' );

	// Minimal validation.
	if ( ! $full_name || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'booking', 'error', wp_get_referer() ?: home_url( '/' ) ) );
		exit;
	}

	// Human-friendly reference, e.g. TWN-8F3A2C.
	$booking_ref = 'TWN-' . strtoupper( substr( md5( uniqid( '', true ) ), 0, 6 ) );

	// 3. Store as a private booking post ---------------------------------
	$booking_id = wp_insert_post( array(
		'post_type'   => 'booking',
		'post_status' => 'publish',
		'post_title'  => sprintf( '%s — %s [%s]', $booking_ref, $full_name, $trip_name ?: 'Enquiry' ),
	), true );

	if ( ! is_wp_error( $booking_id ) ) {
		$meta = array(
			'_bk_ref'        => $booking_ref,
			'_bk_status'     => 'new',          // new | contacted | confirmed | paid | cancelled
			'_bk_type'       => $type,
			'_bk_trip_id'    => $trip_id,
			'_bk_trip_name'  => $trip_name,
			'_bk_full_name'  => $full_name,
			'_bk_email'      => $email,
			'_bk_phone'      => $phone,
			'_bk_country'    => $country,
			'_bk_travellers' => $travellers,
			'_bk_departure'  => $dep_date,
			'_bk_message'    => $message,
			'_bk_created'    => current_time( 'mysql' ),
		);
		foreach ( $meta as $k => $v ) {
			update_post_meta( $booking_id, $k, $v );
		}
	}

	// 4. Email the agency -------------------------------------------------
	$admin_to   = get_option( 'admin_email' );
	$site_name  = get_bloginfo( 'name' );
	$type_label = $type === 'departure' ? 'JOIN GROUP DEPARTURE' : 'CUSTOM PRIVATE DATE';

	$admin_subject = sprintf( '[%s] Booking: %s [%s]', $type_label, $trip_name ?: 'Enquiry', $booking_ref );
	$admin_body    = "NEW BOOKING REQUEST\n\n"
		. "Reference:   {$booking_ref}\n"
		. "Type:        {$type_label}\n"
		. "Trip:        {$trip_name}\n"
		. "Departure:   " . ( $dep_date ?: 'Not specified' ) . "\n"
		. "Travellers:  {$travellers}\n\n"
		. "Name:        {$full_name}\n"
		. "Email:       {$email}\n"
		. "Phone:       {$phone}\n"
		. "Country:     {$country}\n\n"
		. "Message:\n{$message}\n\n"
		. "View in admin: " . admin_url( 'post.php?post=' . intval( $booking_id ) . '&action=edit' ) . "\n";

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( is_email( $email ) ) {
		$headers[] = 'Reply-To: ' . $full_name . ' <' . $email . '>';
	}
	wp_mail( $admin_to, $admin_subject, $admin_body, $headers );

	// 5. Confirm to the customer -----------------------------------------
	if ( is_email( $email ) ) {
		$cust_subject = "Booking request received — {$trip_name} [{$booking_ref}]";
		$cust_body    = "Hi {$full_name},\n\n"
			. "Thank you for choosing {$site_name}. We have received your request and will reply within 24 hours.\n\n"
			. "Your reference: {$booking_ref}\n"
			. "Trip: {$trip_name}\n"
			. ( $dep_date ? "Departure: {$dep_date}\n" : '' )
			. "\nPlease keep this email as proof of your request.\n\n"
			. "— {$site_name}";
		wp_mail( $email, $cust_subject, $cust_body, array( 'Content-Type: text/plain; charset=UTF-8' ) );
	}

	// 6. Redirect to a thank-you view ------------------------------------
	$redirect = home_url( '/thank-you/' );
	$redirect = add_query_arg( array( 'ref' => rawurlencode( $booking_ref ) ), $redirect );
	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_post_trekways_booking',        'trekways_handle_booking' );
add_action( 'admin_post_nopriv_trekways_booking', 'trekways_handle_booking' );

/* -------------------------------------------------------------------------
 * Admin: show booking reference + status as columns in the Bookings list,
 * so the agency can triage without opening each one.
 * ---------------------------------------------------------------------- */
function trekways_booking_columns( $cols ) {
	$new = array( 'cb' => $cols['cb'], 'title' => $cols['title'] );
	$new['bk_ref']    = __( 'Reference', 'trekways' );
	$new['bk_status'] = __( 'Status', 'trekways' );
	$new['bk_trip']   = __( 'Trip', 'trekways' );
	$new['bk_when']   = __( 'Received', 'trekways' );
	return $new;
}
add_filter( 'manage_booking_posts_columns', 'trekways_booking_columns' );

function trekways_booking_column_content( $col, $post_id ) {
	switch ( $col ) {
		case 'bk_ref':    echo esc_html( get_post_meta( $post_id, '_bk_ref', true ) ); break;
		case 'bk_status': echo esc_html( ucfirst( get_post_meta( $post_id, '_bk_status', true ) ?: 'new' ) ); break;
		case 'bk_trip':   echo esc_html( get_post_meta( $post_id, '_bk_trip_name', true ) ); break;
		case 'bk_when':   echo esc_html( get_post_meta( $post_id, '_bk_created', true ) ); break;
	}
}
add_action( 'manage_booking_posts_custom_column', 'trekways_booking_column_content', 10, 2 );
