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
 * pingbacks), so every method is switched off.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', fn() => array() );

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
 * Security headers on every page WordPress renders:
 * - HSTS: browsers use HTTPS only for a year (the site is HTTPS-only already);
 * - nosniff: files are only used as the type they are sent as;
 * - Referrer-Policy: other sites see only the domain, never full result-permalink URLs;
 * - frame-ancestors / X-Frame-Options: pages can't be framed by other sites (clickjacking);
 *   /embed/dls/ is served earlier without them, so the widget still embeds anywhere;
 * - Permissions-Policy: no camera, microphone or location.
 * The Link headers to the REST API and the ?p= shortlink are dropped as well.
 *
 * @param array $headers Headers.
 * @return array
 */
function dlscalc_security_headers( $headers ) {
	if ( is_admin() ) {
		return $headers;
	}
	if ( is_ssl() ) {
		$headers['Strict-Transport-Security'] = 'max-age=31536000';
	}
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
