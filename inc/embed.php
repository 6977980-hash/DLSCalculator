<?php
/**
 * Embeddable DLS widget: /embed/dls/ is a small standalone calculator other sites show in an
 * iframe. The embed code puts a credit link next to the iframe, in the host page itself, which
 * is what earns the backlink. The widget page is noindex and is not a WordPress page.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Path of the widget below the home URL.
 *
 * @return string
 */
function dlscalc_embed_path() {
	return 'embed/dls/';
}

/**
 * The code site owners paste: the iframe and a credit link to the DLS Calculator.
 *
 * @return string
 */
function dlscalc_embed_code() {
	return '<iframe src="' . esc_url( home_url( '/' . dlscalc_embed_path() ) ) . '" title="DLS calculator" width="100%" height="460" style="border:0;max-width:480px" loading="lazy"></iframe>' . "\n"
		. '<p style="font-size:13px"><a href="' . esc_url( dlscalc_page_url( 'dls-calculator' ) ) . '">DLS calculator</a> by DLSCalc</p>';
}

/**
 * Widget inputs from the address (n overs per side, r Team 1 score, c Team 2 overs), as in calc().
 *
 * @return array{n: string, r: string, c: string}
 */
function dlscalc_embed_inputs() {
	$in = array(
		'n' => '20',
		'r' => '160',
		'c' => '',
	);
	foreach ( $in as $key => $default ) {
		if ( isset( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only calculator inputs.
			$in[ $key ] = substr( sanitize_text_field( wp_unslash( $_GET[ $key ] ) ), 0, 8 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
	}
	return $in;
}

/**
 * Serve the widget before WordPress looks for a page.
 */
function dlscalc_serve_embed() {
	$path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- only compared below.
	$base = rtrim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( trailingslashit( (string) $path ) !== $base . '/' . dlscalc_embed_path() ) {
		return;
	}
	$in = dlscalc_embed_inputs();
	$r  = dlscalc_dls_calc( $in + array( 'g' => '245' ) );
	status_header( 200 );
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'X-Robots-Tag: noindex, follow' );
	header( 'Cache-Control: public, max-age=3600' );
	require DLSCALC_DIR . '/template-parts/embed-dls.php';
	exit;
}
add_action( 'wp_loaded', 'dlscalc_serve_embed' );

/**
 * "Add this calculator to your website" box on the DLS Calculator page.
 */
function dlscalc_embed_box() {
	?>
	<section class="panel block noprint" id="embed" aria-labelledby="embed-title">
		<h2 id="embed-title">Add this DLS calculator to your website</h2>
		<p class="hint">Free for club, league and cricket blog sites. Paste this code where the calculator should appear. It works on any site, including WordPress (use a Custom HTML block).</p>
		<textarea class="embed-code" rows="4" readonly aria-label="Embed code" data-embed-code><?php echo esc_textarea( dlscalc_embed_code() ); ?></textarea>
		<div class="share">
			<button type="button" class="btn btn--green js-only" data-copy-embed>Copy code</button>
			<a class="btn btn--line" href="<?php echo esc_url( home_url( '/' . dlscalc_embed_path() ) ); ?>" target="_blank" rel="noopener">Preview the widget</a>
		</div>
	</section>
	<?php
}
