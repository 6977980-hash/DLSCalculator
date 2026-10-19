<?php
/**
 * Creates the site's pages on theme activation, so nothing is built by hand in WP admin.
 * Existing pages are never changed; bump DLSCALC_PAGES_VERSION when a phase adds a page.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DLSCALC_PAGES_VERSION', 3 );

/**
 * Pages the theme owns: slug => title. Content comes from theme templates, not the editor.
 *
 * @return array<string, string>
 */
function dlscalc_site_pages() {
	$pages = array( 'home' => 'Home' );
	foreach ( dlscalc_tools() as $slug => $tool ) {
		$pages[ $slug ] = $tool['title'];
	}
	return array_merge(
		$pages,
		array(
			'dls-par-score-table'  => 'DLS Par Score Table',
			'live-dls-par-score'   => 'Live DLS Par Score',
			'dls-method'           => 'DLS Method Explained',
			'how-to-calculate-nrr' => 'How to Calculate NRR',
			'about'                => 'About DLSCalc',
			'privacy-policy'       => 'Privacy Policy',
			'contact'              => 'Contact',
		)
	);
}

/**
 * Create missing pages and make Home the front page.
 */
function dlscalc_create_pages() {
	foreach ( dlscalc_site_pages() as $slug => $title ) {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		// Never touch a page that already exists. WordPress's own draft Privacy Policy stays a
		// draft until phase 4 replaces its sample text, so the footer hides that link until then.
		if ( $existing ) {
			continue;
		}
		wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_content' => '',
			)
		);
	}

	$home = get_page_by_path( 'home', OBJECT, 'page' );
	if ( $home && 'page' !== get_option( 'show_on_front' ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home->ID );
	}

	if ( '' === get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	flush_rewrite_rules();

	update_option( 'dlscalc_pages_version', DLSCALC_PAGES_VERSION );
}
add_action( 'after_switch_theme', 'dlscalc_create_pages' );

/**
 * A deploy that adds pages runs creation again the next time an admin opens WP admin.
 */
function dlscalc_maybe_create_pages() {
	if ( (int) get_option( 'dlscalc_pages_version', 0 ) < DLSCALC_PAGES_VERSION && current_user_can( 'manage_options' ) ) {
		dlscalc_create_pages();
	}
}
add_action( 'admin_init', 'dlscalc_maybe_create_pages' );
