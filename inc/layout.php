<?php
/**
 * Swaps GeneratePress's header, navigation and footer for ours, and adds
 * breadcrumbs, byline and author box around page content.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pre-launch switch: false keeps the site out of search results. Launched on 19 October 2026 (phase 4).
 * Visitors can still open every page; only the robots meta changes.
 */
if ( ! defined( 'DLSCALC_LAUNCHED' ) ) {
	define( 'DLSCALC_LAUNCHED', true );
}

add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( ! DLSCALC_LAUNCHED ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		} elseif ( is_singular() && ! is_front_page() && ! isset( dlscalc_site_pages()[ dlscalc_current_slug() ] ) ) {
			// WordPress's "Sample Page" and "Hello world!" are thin content; only the theme's pages are indexed.
			$robots['noindex'] = true;
		}
		return $robots;
	}
);

/**
 * Replace the parent theme's header, menu and footer.
 */
function dlscalc_replace_parent_structure() {
	remove_action( 'generate_header', 'generate_construct_header' );
	remove_action( 'generate_after_header', 'generate_add_navigation_after_header', 5 );
	remove_action( 'generate_before_header', 'generate_add_navigation_before_header', 5 );
	remove_action( 'generate_before_header', 'generate_top_bar', 5 );
	remove_action( 'generate_footer', 'generate_construct_footer' );
	remove_action( 'generate_footer', 'generate_construct_footer_widgets', 5 );

	add_action( 'generate_header', 'dlscalc_site_header' );
	add_action( 'generate_footer', 'dlscalc_site_footer' );
}
add_action( 'after_setup_theme', 'dlscalc_replace_parent_structure', 20 );

/**
 * Site header.
 */
function dlscalc_site_header() {
	get_template_part( 'template-parts/site-header' );
}

/**
 * Site footer.
 */
function dlscalc_site_footer() {
	get_template_part( 'template-parts/site-footer' );
}

/**
 * Slugs of pages that carry a byline and the author box: the tools and the guides.
 *
 * @return string[]
 */
function dlscalc_authored_slugs() {
	return array_merge( array_keys( dlscalc_tools() ), array( 'dls-par-score-table', 'live-dls-par-score' ), array_keys( dlscalc_guides() ) );
}

/**
 * Whether the current page is a tool or guide page.
 *
 * @return bool
 */
function dlscalc_is_authored_page() {
	if ( ! is_page() || is_front_page() ) {
		return false;
	}
	$post = get_queried_object();
	return $post instanceof WP_Post && in_array( $post->post_name, dlscalc_authored_slugs(), true );
}

add_action(
	'generate_before_page_title',
	function () {
		if ( ! is_front_page() ) {
			dlscalc_breadcrumbs();
		}
	}
);

add_action(
	'generate_after_page_title',
	function () {
		if ( dlscalc_is_authored_page() ) {
			dlscalc_byline();
		}
	}
);

add_action(
	'generate_after_content',
	function () {
		if ( dlscalc_is_authored_page() ) {
			dlscalc_author_box();
		}
	}
);

/**
 * Pages whose tool or guide ships in a later phase show a short notice instead of a blank body.
 *
 * @param string $content Page content.
 * @return string
 */
function dlscalc_placeholder_content( $content ) {
	if ( ! is_page() || is_front_page() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	if ( '' !== trim( wp_strip_all_tags( $content ) ) ) {
		return $content;
	}
	return '<p class="notice-soon">This page is being built and will be ready soon. Meanwhile, see the <a href="' . esc_url( home_url( '/' ) ) . '">home page</a> for the other calculators.</p>';
}
add_filter( 'the_content', 'dlscalc_placeholder_content', 20 );
