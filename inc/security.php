<?php
/**
 * Security hardening (audit F11): keep the admin login name private and send the standard
 * security headers. The embeddable widget (/embed/dls/) is served before these headers are sent,
 * so other sites can still show it in an iframe.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The REST users endpoints list every author's login name. The site has no public use for them,
 * so they exist only for logged-in users (the block editor needs them).
 *
 * @param array $endpoints REST routes.
 * @return array
 */
function dlscalc_hide_rest_users( $endpoints ) {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}
	foreach ( array_keys( $endpoints ) as $route ) {
		if ( 0 === strpos( $route, '/wp/v2/users' ) ) {
			unset( $endpoints[ $route ] );
		}
	}
	return $endpoints;
}
add_filter( 'rest_endpoints', 'dlscalc_hide_rest_users' );

/**
 * oEmbed answers name the page's author and link to the author archive; drop both.
 *
 * @param array $data oEmbed response.
 * @return array
 */
function dlscalc_oembed_no_author( $data ) {
	unset( $data['author_name'], $data['author_url'] );
	return $data;
}
add_filter( 'oembed_response_data', 'dlscalc_oembed_no_author' );

/*
 * XML-RPC is a common password-guessing route and nothing on this site uses it (no apps, no
 * pingbacks), so every method is switched off. The filters leave system.multicall and friends
 * answering, so a request to xmlrpc.php is refused outright (audit F42); the REST API is untouched.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', fn() => array() );
remove_action( 'wp_head', 'rsd_link' );

/**
 * Refuse every XML-RPC request before WordPress answers it.
 */
function dlscalc_block_xmlrpc() {
	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
		status_header( 403 );
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo 'XML-RPC is turned off on this site.';
		exit;
	}
}
add_action( 'init', 'dlscalc_block_xmlrpc', 1 );

/**
 * The site has no posts or comments, so its RSS and Atom feeds (site, comments, page comments) only
 * leaked the WordPress version. Every feed address now redirects to the page it belongs to, or home
 * (audit F42).
 */
function dlscalc_no_feeds() {
	if ( ! is_feed() ) {
		return;
	}
	$id = is_singular() ? get_queried_object_id() : 0;
	wp_safe_redirect( $id ? get_permalink( $id ) : home_url( '/' ), 301 );
	exit;
}
add_action( 'template_redirect', 'dlscalc_no_feeds', 1 );
add_filter( 'the_generator', '__return_empty_string' );

/**
 * HSTS on every response over HTTPS, including the login page, /sw.js, /embed/dls/ and the text
 * files, which are answered before the wp_headers filter below runs (audit F42).
 */
function dlscalc_hsts() {
	if ( is_ssl() && ! headers_sent() ) {
		header( 'Strict-Transport-Security: max-age=31536000' );
	}
}
add_action( 'init', 'dlscalc_hsts', 0 );

/**
 * One login error for a wrong username and a wrong password, so the form does not confirm which
 * usernames exist.
 *
 * @param string $error Error HTML.
 * @return string
 */
function dlscalc_login_error( $error ) {
	global $errors;
	$codes = is_wp_error( $errors ) ? $errors->get_error_codes() : array();
	if ( array_intersect( $codes, array( 'invalid_username', 'invalid_email', 'incorrect_password' ) ) ) {
		return '<strong>Error:</strong> The username or password is not correct.';
	}
	return $error;
}
add_filter( 'login_errors', 'dlscalc_login_error' );

/**
 * Security headers on every page WordPress renders (HSTS is sent for every request by dlscalc_hsts()):
 * - nosniff: files are only used as the type they are sent as;
 * - Referrer-Policy: other sites see only the domain, never full result-permalink URLs;
 * - frame-ancestors / X-Frame-Options: pages can't be framed by other sites (clickjacking);
 *   /embed/dls/ is served earlier without them, so the widget still embeds anywhere;
 * - Permissions-Policy: no camera, microphone or location.
 * Pages for a logged-in user are marked no-store, so neither a cache nor the offline service worker
 * keeps a copy with the admin bar (audit F41). The X-Pingback header and the Link headers to the REST
 * API and the ?p= shortlink are dropped as well.
 *
 * @param array $headers Headers.
 * @return array
 */
function dlscalc_security_headers( $headers ) {
	if ( is_admin() ) {
		return $headers;
	}
	if ( is_user_logged_in() ) {
		$headers = array_merge( $headers, wp_get_nocache_headers() );
	}
	unset( $headers['X-Pingback'] );
	$headers['X-Content-Type-Options']  = 'nosniff';
	$headers['Referrer-Policy']         = 'strict-origin-when-cross-origin';
	$headers['X-Frame-Options']         = 'SAMEORIGIN';
	$headers['Content-Security-Policy'] = "frame-ancestors 'self'";
	$headers['Permissions-Policy']      = 'camera=(), microphone=(), geolocation=()';
	return $headers;
}
add_filter( 'wp_headers', 'dlscalc_security_headers' );
remove_action( 'template_redirect', 'rest_output_link_header', 11 );
remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
