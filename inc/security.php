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
 * files, which are answered before the wp_headers filter below runs (audit F42). It covers the
 * subdomains too (audit F103): www has HTTPS, and the mail and FTP names are not used in a browser.
 */
function dlscalc_hsts() {
	if ( is_ssl() && ! headers_sent() ) {
		header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains' );
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
 * Headers every response gets, the embeddable widget included (audit F97): nosniff, the referrer
 * policy and the permissions policy. Framing rules are added only for normal pages.
 *
 * @return array<string, string>
 */
function dlscalc_base_security_headers() {
	return array(
		'X-Content-Type-Options' => 'nosniff',
		'Referrer-Policy'        => 'strict-origin-when-cross-origin',
		'Permissions-Policy'     => 'camera=(), microphone=(), geolocation=()',
	);
}

/**
 * Security headers on every page WordPress renders (HSTS is sent for every request by dlscalc_hsts()):
 * - nosniff: files are only used as the type they are sent as;
 * - Referrer-Policy: other sites see only the domain, never full result-permalink URLs;
 * - frame-ancestors / X-Frame-Options: pages can't be framed by other sites (clickjacking);
 *   /embed/dls/ is served earlier without them, so the widget still embeds anywhere;
 * - Permissions-Policy: no camera, microphone or location;
 * - Cross-Origin-Opener-Policy: a page opened from another site cannot reach back into this one
 *   (audit F103). Not on /embed/dls/, which lives inside other sites' pages.
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
	$headers                                 = array_merge( $headers, dlscalc_base_security_headers() );
	$headers['X-Frame-Options']              = 'SAMEORIGIN';
	$headers['Content-Security-Policy']      = "frame-ancestors 'self'";
	$headers['Cross-Origin-Opener-Policy']   = 'same-origin';
	return $headers;
}
add_filter( 'wp_headers', 'dlscalc_security_headers' );
remove_action( 'template_redirect', 'rest_output_link_header', 11 );
remove_action( 'template_redirect', 'wp_shortlink_header', 11 );

/**
 * The login page loads WordPress's own styles and scripts with ?ver=<WordPress version>, which told
 * anyone the exact version (audit F95). The version is swapped for a short hash of it, so the files
 * still change URL when WordPress updates.
 *
 * @param string $src Script or style URL.
 * @return string
 */
function dlscalc_hide_wp_version( $src ) {
	$ver = get_bloginfo( 'version' );
	if ( false === strpos( (string) $src, 'ver=' . $ver ) ) {
		return $src;
	}
	return add_query_arg( 'ver', substr( md5( $ver . wp_salt( 'nonce' ) ), 0, 8 ), $src );
}

/**
 * Hide the version on the login page (front-end pages load no core assets with it).
 */
function dlscalc_login_hide_version() {
	add_filter( 'style_loader_src', 'dlscalc_hide_wp_version' );
	add_filter( 'script_loader_src', 'dlscalc_hide_wp_version' );
}
add_action( 'login_init', 'dlscalc_login_hide_version' );

/*
 * Root files the theme folder cannot cover (audit F95, F98). WordPress already keeps a block of its
 * own rules in the site's root .htaccess; the theme adds one more marked block with
 * insert_with_markers(), the function WordPress uses for its own block:
 * - readme.txt of the parent theme and of plugins, and wp-admin/install.php, are refused (they named
 *   the GeneratePress and WordPress versions);
 * - /favicon.ico, which browsers ask for on their own, gets the theme's icon instead of a 404.
 * Written the next time an admin opens WP admin after a deploy that changes the rules
 * (DLSCALC_HTACCESS_VERSION), and removed if the theme is switched off.
 */
define( 'DLSCALC_HTACCESS_VERSION', 1 );

/**
 * The theme's root .htaccess rules.
 *
 * @return string[]
 */
function dlscalc_htaccess_rules() {
	$icon = wp_make_link_relative( get_stylesheet_directory_uri() ) . '/assets/img/favicon.ico';
	return array(
		'<IfModule mod_rewrite.c>',
		'RewriteEngine On',
		'RewriteRule ^wp-content/(themes|plugins)/[^/]+/readme\.txt$ - [F,L]',
		'RewriteRule ^wp-admin/install\.php$ - [F,L]',
		'RewriteRule ^favicon\.ico$ ' . $icon . ' [L]',
		'</IfModule>',
	);
}

/**
 * Write or remove the theme's block in the root .htaccess. Rules for files that do not exist
 * (/favicon.ico) must run before WordPress's own block sends every unknown address to index.php, so
 * a new block is moved to the top of the file.
 *
 * @param string[] $rules Lines, or an empty array to remove the block.
 * @return bool Whether the file now holds the rules.
 */
function dlscalc_write_htaccess( $rules ) {
	$file = get_home_path() . '.htaccess';
	if ( ! file_exists( $file ) || ! is_writable( $file ) ) {
		return false;
	}
	if ( ! insert_with_markers( $file, 'DLSCalc', $rules ) ) {
		return false;
	}
	if ( ! $rules ) {
		return true;
	}
	$text  = (string) file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local file.
	$start = strpos( $text, '# BEGIN DLSCalc' );
	$end   = strpos( $text, '# END DLSCalc' );
	if ( false === $start || false === $end || 0 === $start ) {
		return false !== $start;
	}
	$end  += strlen( '# END DLSCalc' );
	$block = substr( $text, $start, $end - $start );
	$rest  = trim( substr( $text, 0, $start ) . substr( $text, $end ) );
	return false !== file_put_contents( $file, $block . "\n\n" . $rest . "\n", LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- same file WordPress writes.
}

/**
 * Write the block once per rules version, when an admin opens WP admin.
 */
function dlscalc_maybe_write_htaccess() {
	if ( (int) get_option( 'dlscalc_htaccess_version', 0 ) >= DLSCALC_HTACCESS_VERSION || ! current_user_can( 'manage_options' ) || wp_doing_ajax() ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/misc.php';
	if ( dlscalc_write_htaccess( dlscalc_htaccess_rules() ) ) {
		update_option( 'dlscalc_htaccess_version', DLSCALC_HTACCESS_VERSION );
	}
}
add_action( 'admin_init', 'dlscalc_maybe_write_htaccess' );

/**
 * Take the block out again when another theme is switched on.
 */
function dlscalc_remove_htaccess() {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/misc.php';
	dlscalc_write_htaccess( array() );
	delete_option( 'dlscalc_htaccess_version' );
}
add_action( 'switch_theme', 'dlscalc_remove_htaccess' );
