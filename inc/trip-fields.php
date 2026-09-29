<?php
/**
 * Trip detail fields (price, duration, difficulty, group size, best season).
 *
 * @package TrekWays
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * The fields shown on the Trip edit screen.
 */
function trekways_trip_fields() {
	return array(
        '_trip_featured'   => array( 'label' => __( 'Show in Featured Trips (homepage carousel)', 'trekways' ), 'type' => 'checkbox' ),
		'_trip_price'      => array( 'label' => __( 'Price (e.g. USD 1,350)', 'trekways' ), 'type' => 'text' ),
		'_trip_price_was'  => array( 'label' => __( 'Old price, for the discount (e.g. USD 1,590)', 'trekways' ), 'type' => 'text' ),
		'_trip_tier'       => array(
			'label'   => __( 'Package tier (for the comparison section)', 'trekways' ),
			'type'    => 'select',
			'options' => array( '' => '— Not in comparison —', 'standard' => 'Standard', 'best' => 'Most booked', 'luxury' => 'Luxury' ),
		),
		'_trip_includes'   => array( 'label' => __( "What's included — one per line", 'trekways' ), 'type' => 'textarea' ),
		'_trip_excludes'   => array( 'label' => __( "What's not included — one per line", 'trekways' ), 'type' => 'textarea' ),
		'_trip_duration'   => array( 'label' => __( 'Duration (e.g. 14 Days)', 'trekways' ), 'type' => 'text' ),
		'_trip_difficulty' => array(
			'label'   => __( 'Difficulty', 'trekways' ),
			'type'    => 'select',
			'options' => array( '' => '— Select —', 'Easy' => 'Easy', 'Moderate' => 'Moderate', 'Challenging' => 'Challenging', 'Strenuous' => 'Strenuous' ),
		),
		'_trip_group_size' => array( 'label' => __( 'Group size (e.g. 2–12 people)', 'trekways' ), 'type' => 'text' ),
		'_trip_altitude'   => array( 'label' => __( 'Max altitude (e.g. 5,364 m)', 'trekways' ), 'type' => 'text' ),
		'_trip_season'     => array( 'label' => __( 'Best season (e.g. Mar–May, Sep–Nov)', 'trekways' ), 'type' => 'text' ),
		'_trip_badge'      => array( 'label' => __( 'Badge (e.g. Best Seller, New — leave empty for none)', 'trekways' ), 'type' => 'text' ),
	);
}

/**
 * Register the meta box.
 */
function trekways_add_trip_meta_box() {
	add_meta_box(
		'trekways_trip_details',
		__( 'Trip Details', 'trekways' ),
		'trekways_render_trip_meta_box',
		'trip',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'trekways_add_trip_meta_box' );

/**
 * Render the meta box.
 */
function trekways_render_trip_meta_box( $post ) {
	wp_nonce_field( 'trekways_save_trip', 'trekways_trip_nonce' );
	echo '<style>.tw-fields{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;padding:6px 0}.tw-fields label{display:block;font-weight:600;margin-bottom:5px}.tw-fields input,.tw-fields select,.tw-fields textarea{width:100%}.tw-fields .wide{grid-column:1/-1}</style>';
	echo '<div class="tw-fields">';
	foreach ( trekways_trip_fields() as $key => $f ) {
		$val = get_post_meta( $post->ID, $key, true );
		echo '<div><label for="' . esc_attr( $key ) . '">' . esc_html( $f['label'] ) . '</label>';
        if ( 'checkbox' === $f['type'] ) {
			echo '<input type="checkbox" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="1"' . checked( $val, '1', false ) . '> <span>' . esc_html__( 'Yes', 'trekways' ) . '</span>';
		} elseif ( 'textarea' === $f['type'] ) {
			echo '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" rows="7" style="width:100%">' . esc_textarea( $val ) . '</textarea>';
		} elseif ( 'select' === $f['type'] ) {
			echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
			foreach ( $f['options'] as $ov => $ol ) {
				echo '<option value="' . esc_attr( $ov ) . '"' . selected( $val, $ov, false ) . '>' . esc_html( $ol ) . '</option>';
			}
			echo '</select>';
		} else {
			echo '<input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '">';
		}
		echo '</div>';
	}
	echo '</div>';
}

/**
 * Save the fields.
 */
function trekways_save_trip_meta( $post_id ) {
	if ( ! isset( $_POST['trekways_trip_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['trekways_trip_nonce'] ), 'trekways_save_trip' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
		foreach ( trekways_trip_fields() as $key => $f ) {
		if ( 'checkbox' === $f['type'] ) {
			update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '' );
		} elseif ( 'textarea' === $f['type'] ) {
			update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : '' );
		} elseif ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
}
add_action( 'save_post_trip', 'trekways_save_trip_meta' );