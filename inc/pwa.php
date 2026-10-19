<?php
/**
 * Offline mode: a service worker at /sw.js (it must sit at the site root to cover every page)
 * saves the calculator pages and their files, so they open with no signal at the ground.
 * The web app manifest (assets/site.webmanifest) makes the site installable.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pages saved for offline use when the service worker installs: home and the calculators.
 *
 * @return string[] Root-relative URLs.
 */
function dlscalc_offline_pages() {
	$slugs = array_merge( array_keys( dlscalc_tools() ), array( 'dls-par-score-table', 'live-dls-par-score' ) );
	$pages = array( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) );
	foreach ( $slugs as $slug ) {
		if ( dlscalc_page_is_live( $slug ) ) {
			$pages[] = (string) wp_parse_url( dlscalc_page_url( $slug ), PHP_URL_PATH );
		}
	}
	return $pages;
}

/**
 * Serve /sw.js. Its cache name changes with the theme version or the worker file, which clears
 * the old saved copies.
 */
function dlscalc_serve_sw() {
	$path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- only compared below.
	$base = rtrim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( $base . '/sw.js' !== $path ) {
		return;
	}
	$js = (string) file_get_contents( DLSCALC_DIR . '/assets/js/sw.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
	$js = str_replace(
		array( "'__VERSION__'", '__PAGES__', "'/wp-content/themes/dlscalc/'" ),
		array(
			wp_json_encode( dlscalc_asset_version( 'assets/js/sw.js' ) ),
			wp_json_encode( dlscalc_offline_pages(), JSON_UNESCAPED_SLASHES ),
			wp_json_encode( trailingslashit( (string) wp_parse_url( DLSCALC_URI, PHP_URL_PATH ) ), JSON_UNESCAPED_SLASHES ),
		),
		$js
	);
	status_header( 200 );
	header( 'Content-Type: application/javascript; charset=utf-8' );
	header( 'Cache-Control: no-cache' );
	header( 'X-Robots-Tag: noindex' );
	echo $js; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JavaScript file.
	exit;
}
add_action( 'wp_loaded', 'dlscalc_serve_sw' );

/**
 * Register the service worker after the page has loaded, so it never slows the first visit.
 */
function dlscalc_register_sw() {
	if ( is_user_logged_in() ) {
		return;
	}
	printf(
		"<script>if('serviceWorker' in navigator){addEventListener('load',function(){navigator.serviceWorker.register(%s)})}</script>\n",
		wp_json_encode( (string) wp_parse_url( home_url( '/sw.js' ), PHP_URL_PATH ), JSON_UNESCAPED_SLASHES )
	);
}
add_action( 'wp_footer', 'dlscalc_register_sw', 100 );
