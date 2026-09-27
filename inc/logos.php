<?php
/**
 * Logos — associations and awards shown in the homepage marquee.
 * Managed under Dashboard > Logos.
 *
 * @package TrekWays
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Register the private "Logos" post type.
 */
function trekways_register_logos() {
	register_post_type( 'tw_logo', array(
		'labels'             => array(
			'name'          => __( 'Associations & Awards', 'trekways' ),
			'singular_name' => __( 'Association / Award', 'trekways' ),
			'add_new'       => __( 'Add New', 'trekways' ),
			'add_new_item'  => __( 'Add New Association or Award', 'trekways' ),
			'edit_item'     => __( 'Edit Association or Award', 'trekways' ),
			'all_items'     => __( 'All Items', 'trekways' ),
			'not_found'     => __( 'None added yet.', 'trekways' ),
			'menu_name'     => __( 'Associations & Awards', 'trekways' ),
		),
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'menu_icon'          => 'dashicons-awards',
		'menu_position'      => 21,
		'supports'           => array( 'title', 'page-attributes' ),
		'publicly_queryable' => false,
		'exclude_from_search'=> true,
	) );
}
add_action( 'init', 'trekways_register_logos' );

/**
 * Placeholder text in the title box.
 */
function trekways_logo_title_placeholder( $text, $post ) {
	return 'tw_logo' === $post->post_type ? __( 'Name (e.g. Nepal Tourism Board)', 'trekways' ) : $text;
}
add_filter( 'enter_title_here', 'trekways_logo_title_placeholder', 10, 2 );

/**
 * Meta box: type, link, show-name.
 */
function trekways_logo_meta_box() {
	add_meta_box( 'trekways_logo_details', __( 'Logo Details', 'trekways' ), 'trekways_render_logo_meta_box', 'tw_logo', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'trekways_logo_meta_box' );

function trekways_render_logo_meta_box( $post ) {
	wp_nonce_field( 'trekways_save_logo', 'trekways_logo_nonce' );
	$type = get_post_meta( $post->ID, '_logo_type', true );
	$link = get_post_meta( $post->ID, '_logo_link', true );
	$name = get_post_meta( $post->ID, '_logo_show_name', true );
	$img  = (int) get_post_meta( $post->ID, '_logo_image', true );
	$src  = $img ? wp_get_attachment_image_url( $img, 'medium' ) : '';
	?>
	<div class="tw-logo-up" style="margin:8px 0 16px">
		<strong><?php esc_html_e( 'Logo image', 'trekways' ); ?></strong>
		<div class="tw-logo-up__preview" style="margin:8px 0;min-height:70px;display:flex;align-items:center;justify-content:center;border:1px dashed #c3c4c7;border-radius:6px;padding:10px;max-width:320px;background:#fafafa">
			<?php if ( $src ) : ?><img src="<?php echo esc_url( $src ); ?>" style="max-height:90px;max-width:100%"><?php else : ?><span style="color:#888"><?php esc_html_e( 'No image yet', 'trekways' ); ?></span><?php endif; ?>
		</div>
		<input type="hidden" name="_logo_image" class="tw-logo-up__id" value="<?php echo esc_attr( $img ?: '' ); ?>">
		<button type="button" class="button button-primary tw-logo-up__pick"><?php esc_html_e( 'Upload / Choose Image', 'trekways' ); ?></button>
		<button type="button" class="button tw-logo-up__clear" <?php echo $img ? '' : 'style="display:none"'; ?>><?php esc_html_e( 'Remove', 'trekways' ); ?></button>
		<p class="description"><?php esc_html_e( 'Transparent PNG, SVG or WebP works best.', 'trekways' ); ?></p>
	</div>
	<p><label for="_logo_type"><strong><?php esc_html_e( 'Show in row', 'trekways' ); ?></strong></label><br>
		<select id="_logo_type" name="_logo_type">
			<option value="association" <?php selected( $type, 'association' ); ?>><?php esc_html_e( 'Our Associations', 'trekways' ); ?></option>
			<option value="award" <?php selected( $type, 'award' ); ?>><?php esc_html_e( 'Awards', 'trekways' ); ?></option>
		</select></p>
	<p><label for="_logo_link"><strong><?php esc_html_e( 'Website link (optional) — where visitors go when they click the logo', 'trekways' ); ?></strong></label><br>
		<input type="url" id="_logo_link" name="_logo_link" value="<?php echo esc_attr( $link ); ?>" style="width:100%" placeholder="https://"></p>
	<p><label><input type="checkbox" name="_logo_show_name" value="1" <?php checked( $name, '1' ); ?>> <?php esc_html_e( 'Show the title text beside the logo', 'trekways' ); ?></label></p>
	<p class="description"><?php esc_html_e( 'Use the Order field on the right to control position in the row.', 'trekways' ); ?></p>
	<?php
}

function trekways_save_logo_meta( $post_id ) {
	if ( ! isset( $_POST['trekways_logo_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['trekways_logo_nonce'] ), 'trekways_save_logo' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$type = isset( $_POST['_logo_type'] ) && 'award' === $_POST['_logo_type'] ? 'award' : 'association';
	update_post_meta( $post_id, '_logo_type', $type );
	update_post_meta( $post_id, '_logo_link', isset( $_POST['_logo_link'] ) ? esc_url_raw( wp_unslash( $_POST['_logo_link'] ) ) : '' );
	update_post_meta( $post_id, '_logo_show_name', isset( $_POST['_logo_show_name'] ) ? '1' : '' );
	update_post_meta( $post_id, '_logo_image', isset( $_POST['_logo_image'] ) ? absint( $_POST['_logo_image'] ) : 0 );
}
add_action( 'save_post_tw_logo', 'trekways_save_logo_meta' );

/**
 * Media uploader on the edit screen.
 */
function trekways_logo_admin_assets( $hook ) {
	$screen = get_current_screen();
	if ( ! $screen || 'tw_logo' !== $screen->post_type || ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	wp_enqueue_media();
	wp_add_inline_script( 'jquery-core', "jQuery(function($){var f;var b=$('.tw-logo-up');b.on('click','.tw-logo-up__pick',function(e){e.preventDefault();if(f){f.open();return;}f=wp.media({title:'Choose logo',button:{text:'Use this image'},library:{type:'image'},multiple:false});f.on('select',function(){var a=f.state().get('selection').first().toJSON();var u=(a.sizes&&a.sizes.medium)?a.sizes.medium.url:a.url;b.find('.tw-logo-up__id').val(a.id);b.find('.tw-logo-up__preview').html('<img src=\"'+u+'\" style=\"max-height:90px;max-width:100%\">');b.find('.tw-logo-up__clear').show();});f.open();});b.on('click','.tw-logo-up__clear',function(e){e.preventDefault();b.find('.tw-logo-up__id').val('');b.find('.tw-logo-up__preview').html('<span style=\"color:#888\">No image yet</span>');$(this).hide();});});" );
}
add_action( 'admin_enqueue_scripts', 'trekways_logo_admin_assets' );

/**
 * Build the tiles for one row. Returns '' when the row has no logos.
 *
 * @param string $type association|award
 */
function trekways_logo_tiles( $type ) {
	$q = new WP_Query( array(
		'post_type'      => 'tw_logo',
		'posts_per_page' => 30,
		'no_found_rows'  => true,
		'orderby'        => 'menu_order date',
		'order'          => 'ASC',
		'meta_query'     => array( array( 'key' => '_logo_type', 'value' => $type ) ),
	) );
	$tiles = array();
	while ( $q->have_posts() ) {
		$q->the_post();
		$iid  = (int) get_post_meta( get_the_ID(), '_logo_image', true );
		$img  = $iid ? wp_get_attachment_image_url( $iid, 'medium' ) : get_the_post_thumbnail_url( get_the_ID(), 'medium' );
		$link = get_post_meta( get_the_ID(), '_logo_link', true );
		$show = get_post_meta( get_the_ID(), '_logo_show_name', true );
		$name = get_the_title();
		if ( ! $img && ! $name ) {
			continue;
		}
		$inner = '';
		if ( $img ) {
			$inner .= '<img src="' . esc_url( $img ) . '" alt="' . esc_attr( $name ) . '" loading="lazy" decoding="async">';
		}
		if ( $show || ! $img ) {
			$inner .= '<span>' . esc_html( $name ) . '</span>';
		}
		$tiles[] = $link
			? '<a class="tw-lg" href="' . esc_url( $link ) . '" target="_blank" rel="noopener nofollow">' . $inner . '</a>'
			: '<div class="tw-lg">' . $inner . '</div>';
	}
	wp_reset_postdata();
	if ( ! $tiles ) {
		return '';
	}
	// Repeat short sets so one half of the track is always wider than the screen.
	$set = $tiles;
	while ( count( $set ) < 10 ) {
		$set = array_merge( $set, $tiles );
	}
	$half = implode( '', $set );
	// Second copy is aria-hidden so screen readers do not hear every logo twice.
	return $half . '<div class="tw-track__dup" aria-hidden="true">' . $half . '</div>';
}