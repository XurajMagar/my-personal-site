<?php
/**
 * Trekways mega menu — reads a registered nav menu location and renders:
 *  - a plain <a> for any top-level item with no children
 *  - a click-to-open panel (left category list, right region/tier/trek content)
 *    for any top-level item that has children, using whatever depth is
 *    actually present in the menu (works with 2, 3, or 4 nested levels).
 *
 * @package TrekWays
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Fetch a nav menu location's items grouped by parent ID.
 *
 * @param string $location Registered theme_location key.
 * @return array<int, array> Items keyed by menu_item_parent.
 */
function trekways_get_menu_tree( $location ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return array();
	}
	$menu = wp_get_nav_menu_object( $locations[ $location ] );
	if ( ! $menu ) {
		return array();
	}
	$items = wp_get_nav_menu_items( $menu->term_id );
	if ( ! $items ) {
		return array();
	}
	$by_parent = array();
	foreach ( $items as $item ) {
		$by_parent[ (int) $item->menu_item_parent ][] = $item;
	}
	foreach ( $by_parent as &$group ) {
		usort(
			$group,
			function ( $a, $b ) {
				return (int) $a->menu_order - (int) $b->menu_order;
			}
		);
	}
	unset( $group );
	return $by_parent;
}

/**
 * Pick a small icon for a tier label based on its title (Normal / Luxury / Heli / Deluxe).
 */
function trekways_tier_icon( $title ) {
	$t = strtolower( $title );
	if ( false !== strpos( $t, 'luxury' ) ) {
		return 'fa-gem';
	}
	if ( false !== strpos( $t, 'heli' ) ) {
		return 'fa-helicopter';
	}
	if ( false !== strpos( $t, 'deluxe' ) ) {
		return 'fa-crown';
	}
	if ( false !== strpos( $t, 'normal' ) || false !== strpos( $t, 'standard' ) ) {
		return 'fa-person-hiking';
	}
	return 'fa-mountain';
}

/**
 * Render one region block (a depth-2 item, e.g. "Everest Region") — its tiers/treks.
 * Children that themselves have children become labelled tiers; children with
 * no children are listed directly under the region with no tier label.
 */
function trekways_render_region( $region, $tree ) {
	$kids = isset( $tree[ $region->ID ] ) ? $tree[ $region->ID ] : array();
	if ( empty( $kids ) ) {
		return '<div class="tw-mega__region"><div class="tw-tier__links"><a href="' . esc_url( $region->url ) . '">' . esc_html( $region->title ) . '</a></div></div>';
	}
	$out   = '<div class="tw-mega__region"><h4>' . esc_html( $region->title ) . '</h4>';
	$loose = array();
	foreach ( $kids as $kid ) {
		$grandkids = isset( $tree[ $kid->ID ] ) ? $tree[ $kid->ID ] : array();
		if ( ! empty( $grandkids ) ) {
			$icon = trekways_tier_icon( $kid->title );
			$out .= '<div class="tw-tier"><div class="tw-tier__label"><i class="fa-solid ' . esc_attr( $icon ) . '"></i>' . esc_html( $kid->title ) . '</div><div class="tw-tier__links">';
			foreach ( $grandkids as $trek ) {
				$out .= '<a href="' . esc_url( $trek->url ) . '">' . esc_html( $trek->title ) . '</a>';
			}
			$out .= '</div></div>';
		} else {
			$loose[] = $kid;
		}
	}
	if ( $loose ) {
		$out .= '<div class="tw-tier"><div class="tw-tier__links">';
		foreach ( $loose as $trek ) {
			$out .= '<a href="' . esc_url( $trek->url ) . '">' . esc_html( $trek->title ) . '</a>';
		}
		$out .= '</div></div>';
	}
	$out .= '</div>';
	return $out;
}

/**
 * Render one category pane (a depth-1 item, e.g. "Trekking in Nepal") — its regions.
 */
function trekways_render_pane( $cat, $tree, $active ) {
	$regions = isset( $tree[ $cat->ID ] ) ? $tree[ $cat->ID ] : array();
	$class   = 'tw-mega__pane' . ( $active ? ' active' : '' );
	$out     = '<div class="' . $class . '" data-pane="cat-' . (int) $cat->ID . '">';
	if ( empty( $regions ) ) {
		$out .= '<div class="tw-tier__links"><a href="' . esc_url( $cat->url ) . '">' . esc_html( $cat->title ) . '</a></div>';
	} else {
		foreach ( $regions as $region ) {
			$out .= trekways_render_region( $region, $tree );
		}
	}
	$out .= '</div>';
	return $out;
}

/**
 * Echo a full nav wing (<ul class="tw-wing ...">) for a theme location.
 * Top items with children get a toggle button + an inline mega panel
 * (kept inside the same <li> so it's valid HTML and sits in-flow for
 * the mobile accordion; desktop CSS lifts it out visually via position:fixed).
 *
 * @param string $location Registered theme_location key.
 * @param string $class    Extra class(es) for the <ul>, e.g. 'tw-wing--left'.
 * @return bool True if anything was rendered.
 */
function trekways_mega_wing( $location, $class ) {
	$tree = trekways_get_menu_tree( $location );
	$top  = isset( $tree[0] ) ? $tree[0] : array();
	if ( empty( $top ) ) {
		return false;
	}
	echo '<ul class="tw-wing ' . esc_attr( $class ) . '">';
	foreach ( $top as $item ) {
		$children = isset( $tree[ $item->ID ] ) ? $tree[ $item->ID ] : array();
		if ( empty( $children ) ) {
			echo '<li><a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a></li>';
			continue;
		}
		$slug = sanitize_title( $item->title . '-' . $item->ID );
		echo '<li class="tw-has-mega">';
		echo '<button type="button" class="tw-topbtn" data-menu="' . esc_attr( $slug ) . '">' . esc_html( $item->title ) . ' <i class="fa-solid fa-chevron-down"></i></button>';
		echo '<div class="tw-mega" id="tw-mega-' . esc_attr( $slug ) . '">';
		echo '<button type="button" class="tw-mega__back"><i class="fa-solid fa-chevron-left"></i> ' . sprintf( esc_html__( 'Back to %s', 'trekways' ), esc_html( $item->title ) ) . '</button>';
		echo '<div class="tw-mega__cats">';
		$first = true;
		foreach ( $children as $cat ) {
			echo '<button type="button" class="tw-mega__cat' . ( $first ? ' active' : '' ) . '" data-pane="cat-' . (int) $cat->ID . '">' . esc_html( $cat->title ) . ' <i class="fa-solid fa-chevron-right"></i></button>';
			$first = false;
		}
		echo '</div><div class="tw-mega__body">';
		$first = true;
		foreach ( $children as $cat ) {
			echo trekways_render_pane( $cat, $tree, $first ); // phpcs:ignore -- pre-escaped in helper.
			$first = false;
		}
		echo '</div></div></li>';
	}
	echo '</ul>';
	return true;
}