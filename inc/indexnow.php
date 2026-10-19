<?php
/**
 * IndexNow: tells Bing (and Yandex, Seznam, Naver) about new and changed pages right after a deploy,
 * so they are crawled within minutes instead of days. ChatGPT search and Copilot use Bing's index.
 *
 * Hostinger deploys by copying files, so there is no deploy hook. Instead, every few minutes the theme
 * compares the modification times of its page files with the ones it saw last; when they differ it pings
 * the pages those files build (a template under template-parts/content/ or a page-*.php file is one page,
 * anything else in inc/ or the theme root may change every page). The key file is served at /<key>.txt.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DLSCALC_INDEXNOW_KEY', 'cec5431b874e4e17491727e7e7ec1d6a' );

/**
 * Body of /<key>.txt. The response also says when the last ping went out, for checking from outside.
 *
 * @return string
 */
function dlscalc_indexnow_key_txt() {
	$last = get_option( 'dlscalc_indexnow_last' );
	if ( is_array( $last ) ) {
		header( sprintf( 'X-IndexNow-Last: %s; status %s; %d URLs', gmdate( 'c', $last['time'] ), $last['code'], $last['urls'] ) );
	}
	return DLSCALC_INDEXNOW_KEY;
}

/**
 * Modification time of every theme file that builds page content, keyed by path relative to the theme.
 *
 * @return array<string, int>
 */
function dlscalc_indexnow_mtimes() {
	$files = array_merge(
		glob( DLSCALC_DIR . '/*.php' ),
		glob( DLSCALC_DIR . '/inc/*.php' ),
		glob( DLSCALC_DIR . '/template-parts/*.php' ),
		glob( DLSCALC_DIR . '/template-parts/content/*.php' )
	);
	$times = array();
	foreach ( $files as $file ) {
		$times[ substr( $file, strlen( DLSCALC_DIR ) + 1 ) ] = (int) filemtime( $file );
	}
	return $times;
}

/**
 * Pages a changed file builds: its own page, or every page (null).
 *
 * @param string $file Path relative to the theme.
 * @return string[]|null Page slugs, or null for every page.
 */
function dlscalc_indexnow_file_pages( $file ) {
	$pages = dlscalc_site_pages();
	if ( 'front-page.php' === $file ) {
		return array( 'home' );
	}
	if ( preg_match( '#^(?:template-parts/content/|page-)([a-z0-9-]+)\.php$#', $file, $m ) && isset( $pages[ $m[1] ] ) ) {
		return array( $m[1] );
	}
	return null;
}

/**
 * Compare the theme files with the last check and queue a ping for the pages that changed.
 */
function dlscalc_indexnow_check() {
	if ( ! DLSCALC_LAUNCHED || wp_doing_ajax() || wp_doing_cron() || get_transient( 'dlscalc_indexnow_checked' ) ) {
		return;
	}
	set_transient( 'dlscalc_indexnow_checked', 1, 5 * MINUTE_IN_SECONDS );

	$now = dlscalc_indexnow_mtimes();
	$old = get_option( 'dlscalc_indexnow_mtimes' );
	if ( $now === $old ) {
		return;
	}
	update_option( 'dlscalc_indexnow_mtimes', $now, false );

	$slugs = array();
	$all   = ! is_array( $old ) || array_diff_key( $old, $now );
	foreach ( $now as $file => $time ) {
		if ( $all ) {
			break;
		}
		if ( isset( $old[ $file ] ) && $old[ $file ] === $time ) {
			continue;
		}
		$pages = dlscalc_indexnow_file_pages( $file );
		if ( null === $pages ) {
			$all = true;
		} else {
			$slugs = array_merge( $slugs, $pages );
		}
	}

	$urls = array();
	foreach ( $all ? array_keys( dlscalc_site_pages() ) : array_unique( $slugs ) as $slug ) {
		if ( 'home' === $slug ) {
			$urls[] = home_url( '/' );
		} elseif ( dlscalc_page_is_live( $slug ) ) {
			$urls[] = dlscalc_page_url( $slug );
		}
	}
	if ( $urls ) {
		wp_schedule_single_event( time(), 'dlscalc_indexnow_ping', array( $urls ) );
	}
}
add_action( 'wp_loaded', 'dlscalc_indexnow_check', 20 );

/**
 * Send the URLs to IndexNow (runs in WP-Cron, so no visitor waits for it). A failed ping, such as one sent
 * while a deploy is still copying files, is tried once more ten minutes later.
 *
 * @param string[] $urls  Absolute URLs on this site.
 * @param bool     $retry Whether this is the second try.
 */
function dlscalc_indexnow_ping( $urls, $retry = false ) {
	$response = wp_remote_post(
		'https://api.indexnow.org/indexnow',
		array(
			'timeout' => 15,
			'headers' => array( 'Content-Type' => 'application/json; charset=utf-8' ),
			'body'    => wp_json_encode(
				array(
					'host'        => wp_parse_url( home_url( '/' ), PHP_URL_HOST ),
					'key'         => DLSCALC_INDEXNOW_KEY,
					'keyLocation' => home_url( '/' . DLSCALC_INDEXNOW_KEY . '.txt' ),
					'urlList'     => array_values( $urls ),
				)
			),
		)
	);
	$code = is_wp_error( $response ) ? $response->get_error_code() : wp_remote_retrieve_response_code( $response );
	update_option(
		'dlscalc_indexnow_last',
		array(
			'time' => time(),
			'code' => $retry ? $code . ' (retry)' : $code,
			'urls' => count( $urls ),
		),
		false
	);
	if ( ! $retry && ! in_array( $code, array( 200, 202 ), true ) ) {
		wp_schedule_single_event( time() + 10 * MINUTE_IN_SECONDS, 'dlscalc_indexnow_ping', array( $urls, true ) );
	}
}
add_action( 'dlscalc_indexnow_ping', 'dlscalc_indexnow_ping', 10, 2 );
