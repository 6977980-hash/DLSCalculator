<?php
/**
 * /llms.txt (a plain-text map of the site for AI assistants, see llmstxt.org) and /ads.txt.
 *
 * Both are served by WordPress, like robots.txt, so they follow the theme's pages and settings.
 * A real file with the same name in public_html wins over these, so none should exist there.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pages listed in llms.txt, grouped, with a one-line description each. Only published pages appear.
 *
 * @return array<string, array<int, array{0: string, 1: string, 2: string}>> Section => list of [url, title, description].
 */
function dlscalc_llms_sections() {
	$seo      = dlscalc_seo_pages();
	$guides   = function_exists( 'dlscalc_guides' ) ? array_keys( dlscalc_guides() ) : array();
	$tools    = array_merge( array_keys( dlscalc_tools() ), array( 'dls-par-score-table', 'live-dls-par-score' ) );
	$sections = array(
		'Calculators'        => array(),
		'Guides'             => array(),
		'Famous DLS matches' => array(),
		'More pages'         => array(),
		'About'              => array(),
	);

	foreach ( dlscalc_site_pages() as $slug => $title ) {
		if ( 'home' === $slug || 'privacy-policy' === $slug ) {
			continue;
		}
		$page = get_page_by_path( dlscalc_page_path( $slug ), OBJECT, 'page' );
		if ( ! $page || 'publish' !== $page->post_status ) {
			continue;
		}
		$name = isset( $seo[ $slug ] ) ? preg_replace( '/\s*\|\s*DLSCalc$/', '', $seo[ $slug ]['title'] ) : $title;
		$desc = $seo[ $slug ]['description'] ?? '';
		if ( in_array( $slug, $tools, true ) ) {
			$group = 'Calculators';
		} elseif ( 'famous-dls-matches' === $slug || isset( dlscalc_matches()[ $slug ] ) ) {
			$group = 'Famous DLS matches';
		} elseif ( in_array( $slug, $guides, true ) ) {
			$group = 'Guides';
		} elseif ( in_array( $slug, array( 'about', 'contact' ), true ) ) {
			$group = 'About';
		} else {
			$group = 'More pages';
		}
		$sections[ $group ][] = array( dlscalc_page_url( $slug ), $name, $desc );
	}
	return array_filter( $sections );
}

/**
 * The llms.txt body, in Markdown.
 *
 * @return string
 */
function dlscalc_llms_txt() {
	$author = dlscalc_author();
	$out    = array(
		'# DLSCalc (dlscalculator.com)',
		'',
		'> Free cricket calculators: Duckworth-Lewis-Stern (DLS) revised targets and par scores for rain-affected T20, ODI and local matches, a net run rate (NRR) calculator with qualification scenarios, and a run rate calculator. Every result shows its full working.',
		'',
		'- DLS results use the ICC Standard Edition resource table from the ICC playing conditions, ball by ball, with any number of interruptions in either innings. G50 is 245 for men\'s internationals and first-class cricket and 200 for women\'s, associate and U19 matches (ICC); a custom value can be entered.',
		'- Net run rate follows ICC playing conditions (clause 16.10.2): a side that is all out counts the full quota of overs it was entitled to in that match (the reduced overs if rain cut it); no-results are left out.',
		'- Written and checked by ' . $author['name'] . ', a cricketer and tournament organiser. The calculators run in the browser; no sign-up, no app.',
		'- International matches and the major T20 leagues use the DLS Professional Edition (ICC software, formulas not published), so official targets there can differ by a few runs. For club, school and tournament cricket the Standard Edition is the official method.',
		'',
		'## How to link to a result',
		'',
		'Each calculator reads its inputs from the address, so a link opens the worked result:',
		'',
		'- DLS calculator: `n` overs per side, `r` Team 1 runs, `g` G50, `c` Team 2 overs at the start of the chase, `a` and `b` stoppages for Team 1 and Team 2 as `overs-wickets-lost`, joined with `_`, optional `f` format (`odi`, `t20`, `t10`, `custom`) for the minimum overs, optional `k` penalty runs awarded to Team 1 during the chase (they raise the target and every par score). Example: ' . dlscalc_page_url( 'dls-calculator' ) . '?n=50&r=250&g=245&c=50&b=12-1-10_22-3-2 (Team 2 stopped at 12 overs, 1 wicket down, 10 overs lost; then at 22 overs, 3 down, 2 overs lost).',
		'- T20 DLS target: `n`, `r`, `l` overs lost before the chase, `b`. Example: ' . dlscalc_page_url( 'dls-target-calculator-t20' ) . '?n=20&r=180&l=5',
		'- DLS par score table: `o` overs per side, `r` Team 1 runs. Example: ' . dlscalc_page_url( 'dls-par-score-table' ) . '?o=20&r=160',
		'- Net run rate: `q` overs per side, `x` matches as `scored-overs-conceded-overs` joined with `_` (add `a` after overs for all out, `-15` for a match cut to 15 overs a side, `-n` for a no result). Example: ' . dlscalc_page_url( 'net-run-rate-calculator' ) . '?q=20&x=180-20-150-20_140-18.3-145-20',
		'- Run rate: `u` `c` chasing or `f` batting first, `n` overs per side, `t` target, `r` runs, `o` overs bowled. Example: ' . dlscalc_page_url( 'run-rate-calculator' ) . '?u=c&n=20&t=181&r=95&o=11.2',
	);

	foreach ( dlscalc_llms_sections() as $section => $items ) {
		$out[] = '';
		$out[] = '## ' . $section;
		$out[] = '';
		foreach ( $items as $item ) {
			$out[] = '- [' . $item[1] . '](' . $item[0] . ')' . ( $item[2] ? ': ' . $item[2] : '' );
		}
	}
	$out[] = '';
	$out[] = 'Contact: ' . dlscalc_contact_email();
	return implode( "\n", $out ) . "\n";
}

/**
 * Answer /llms.txt and /ads.txt before WordPress looks for a page.
 */
function dlscalc_serve_txt_files() {
	$path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- only compared below.
	$base = rtrim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	$map  = array(
		$base . '/llms.txt' => 'dlscalc_llms_txt',
		$base . '/ads.txt'  => 'dlscalc_ads_txt',
		$base . '/' . DLSCALC_INDEXNOW_KEY . '.txt' => 'dlscalc_indexnow_key_txt',
	);
	if ( ! isset( $map[ $path ] ) ) {
		return;
	}
	status_header( 200 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'X-Robots-Tag: noindex' );
	header( 'Cache-Control: public, max-age=3600' );
	echo call_user_func( $map[ $path ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plain text.
	exit;
}
add_action( 'wp_loaded', 'dlscalc_serve_txt_files' );
