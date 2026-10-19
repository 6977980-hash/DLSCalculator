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
 * Pages saved for offline use when the service worker installs: home, the calculators and the
 * three guides (audit F47), with the names the offline page lists them by.
 *
 * @return array<string, string> Root-relative URL => name.
 */
function dlscalc_offline_links() {
	$names = array_map(
		function ( $tool ) {
			return $tool['title'];
		},
		dlscalc_tools()
	) + array(
		'dls-par-score-table'  => 'DLS Par Score Table',
		'live-dls-par-score'   => 'Live DLS Par Score',
		'dls-method'           => 'DLS method guide',
		'how-to-calculate-nrr' => 'How to calculate NRR',
		'vjd-method'           => 'VJD method guide',
	);
	$links = array( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) => 'Home' );
	foreach ( $names as $slug => $name ) {
		if ( dlscalc_page_is_live( $slug ) ) {
			$links[ (string) wp_parse_url( dlscalc_page_url( $slug ), PHP_URL_PATH ) ] = $name;
		}
	}
	return $links;
}

/**
 * Pages saved for offline use when the service worker installs.
 *
 * @return string[] Root-relative URLs.
 */
function dlscalc_offline_pages() {
	return array_keys( dlscalc_offline_links() );
}

/**
 * Version for the service worker's cache name: the theme version plus a short hash of every theme
 * file's name, size and change time. Any deploy that changes a file (a font, an icon, an image, CSS
 * or JS) gives a new cache name, so phones drop their old saved copies (audit F41).
 *
 * @return string
 */
function dlscalc_sw_version() {
	$sig   = '';
	$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( DLSCALC_DIR . '/assets', FilesystemIterator::SKIP_DOTS ) );
	foreach ( $files as $file ) {
		$sig .= substr( $file->getPathname(), strlen( DLSCALC_DIR ) ) . ':' . $file->getSize() . ':' . $file->getMTime() . "\n";
	}
	return DLSCALC_VERSION . '.' . substr( md5( $sig ), 0, 10 );
}

/**
 * Serve /sw.js. Its cache name changes whenever a theme file changes (dlscalc_sw_version()), which
 * clears the old saved copies.
 */
function dlscalc_serve_sw() {
	$path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- only compared below.
	$base = rtrim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( $base . '/sw.js' !== $path ) {
		return;
	}
	$js = (string) file_get_contents( DLSCALC_DIR . '/assets/js/sw.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
	$js = str_replace(
		array( "'__VERSION__'", '__PAGES__', '__LINKS__', "'/wp-content/themes/dlscalc/'" ),
		array(
			wp_json_encode( dlscalc_sw_version() ),
			wp_json_encode( dlscalc_offline_pages(), JSON_UNESCAPED_SLASHES ),
			wp_json_encode( (object) dlscalc_offline_links(), JSON_UNESCAPED_SLASHES ),
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
 * Logged-in users get no worker: a browser that registered one before logging in removes it and its
 * saved pages, so pages with the admin bar are never kept for offline use (audit F41).
 */
function dlscalc_register_sw() {
	if ( is_user_logged_in() ) {
		echo "<script>if('serviceWorker' in navigator){navigator.serviceWorker.getRegistrations().then(function(r){r.forEach(function(x){x.unregister()})});if(window.caches){caches.keys().then(function(k){k.forEach(function(n){if(n.indexOf('dlscalc-')===0){caches.delete(n)}})})}}</script>\n";
		return;
	}
	printf(
		"<script>if('serviceWorker' in navigator){addEventListener('load',function(){navigator.serviceWorker.register(%s)})}</script>\n",
		wp_json_encode( (string) wp_parse_url( home_url( '/sw.js' ), PHP_URL_PATH ), JSON_UNESCAPED_SLASHES )
	);
}
add_action( 'wp_footer', 'dlscalc_register_sw', 100 );
add_action( 'admin_footer', 'dlscalc_register_sw', 100 );
