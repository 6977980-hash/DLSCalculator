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

define( 'DLSCALC_PAGES_VERSION', 5 );

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
			'vjd-method'           => 'VJD Method in Cricket',
			'about'                => 'About DLSCalc',
			'privacy-policy'       => 'Privacy Policy',
			'contact'              => 'Contact',
			'famous-dls-matches'   => 'Famous DLS Matches',
		),
		wp_list_pluck( dlscalc_matches(), 'title' )
	);
}

/**
 * Create missing pages and make Home the front page.
 */
function dlscalc_create_pages() {
	foreach ( dlscalc_site_pages() as $slug => $title ) {
		$path     = dlscalc_page_path( $slug );
		$existing = get_page_by_path( $path, OBJECT, 'page' );
		// Never touch a page that already exists. WordPress's own draft Privacy Policy is published by
		// the owner in WP admin; the theme template replaces its sample text, and the footer link appears then.
		if ( $existing ) {
			continue;
		}
		// Child pages (the match pages under /famous-dls-matches/) come after their parent in the list.
		$parent = dirname( $path );
		$parent = '.' === $parent ? null : get_page_by_path( $parent, OBJECT, 'page' );
		wp_insert_post(
			array(
				'post_parent'  => $parent ? $parent->ID : 0,
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
 * A deploy that adds pages runs creation again the next time an admin opens WP admin. WP admin
 * fires several requests at once (heartbeat, ajax), so an option acts as a lock: add_option() only
 * succeeds for one request, and the others skip. Without it two requests made two /vjd-method/ pages.
 */
function dlscalc_maybe_create_pages() {
	if ( (int) get_option( 'dlscalc_pages_version', 0 ) >= DLSCALC_PAGES_VERSION || ! current_user_can( 'manage_options' ) || wp_doing_ajax() ) {
		return;
	}
	$lock = 'dlscalc_pages_lock';
	if ( ! add_option( $lock, time(), '', 'no' ) ) {
		// A lock left by a request that died is cleared after a minute.
		if ( time() - (int) get_option( $lock ) < 60 ) {
			return;
		}
		update_option( $lock, time(), false );
	}
	dlscalc_create_pages();
	delete_option( $lock );
}
add_action( 'admin_init', 'dlscalc_maybe_create_pages' );
