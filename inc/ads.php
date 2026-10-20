<?php
/**
 * Ad slots and ads.txt.
 *
 * The slots are reserved in the templates but print nothing until an AdSense publisher ID is set
 * below: no empty boxes, no extra requests and no layout shift for visitors before then.
 *
 * To switch ads on:
 * 1. Set DLSCALC_ADSENSE_CLIENT to the publisher ID, e.g. 'ca-pub-1234567890123456'.
 *    That alone turns on AdSense Auto ads and writes the Google line into /ads.txt.
 * 2. Optional: put manual ad unit IDs (data-ad-slot) in dlscalc_ad_units() to fill the fixed slots.
 * 3. Update the Privacy Policy (page-privacy-policy.php), which says the site has no ads or third-party
 *    cookies, and set up a Google-certified consent banner for visitors in the EEA and UK.
 *
 * Slots: 'top' is printed where the article starts, under the calculator on tool pages and under
 * the contents box on guides; 'bottom' is printed after the article, before the author box.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'DLSCALC_ADSENSE_CLIENT' ) ) {
	define( 'DLSCALC_ADSENSE_CLIENT', '' );
}

/**
 * Manual AdSense ad unit IDs per slot. Empty means the slot prints nothing.
 *
 * @return array<string, string>
 */
function dlscalc_ad_units() {
	return array(
		'top'    => '',
		'bottom' => '',
	);
}

/**
 * Whether ads may show: a valid publisher ID, the site is launched, and a page a visitor reads.
 *
 * @return bool
 */
function dlscalc_ads_on() {
	return DLSCALC_LAUNCHED
		&& (bool) preg_match( '/^ca-pub-\d{10,20}$/', DLSCALC_ADSENSE_CLIENT )
		&& ! is_admin()
		&& ! is_404()
		&& ! is_page( array( 'privacy-policy', 'terms', 'contact', 'about' ) );
}

/**
 * AdSense script, async, only once a publisher ID is set.
 */
function dlscalc_ads_script() {
	if ( ! dlscalc_ads_on() ) {
		return;
	}
	printf(
		'<script async src="%s" crossorigin="anonymous"></script>' . "\n",
		esc_url( 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . DLSCALC_ADSENSE_CLIENT )
	);
}
add_action( 'wp_head', 'dlscalc_ads_script', 20 );

/**
 * Print one ad slot. Nothing at all is printed until ads are on and the slot has a unit ID.
 * The box keeps a fixed minimum height so a loading ad does not push the page down.
 *
 * @param string $slot Slot name from dlscalc_ad_units().
 */
function dlscalc_ad_slot( $slot ) {
	$units = dlscalc_ad_units();
	if ( empty( $units[ $slot ] ) || ! dlscalc_ads_on() ) {
		return;
	}
	?>
	<aside class="ad noprint" aria-label="Advertisement" style="margin:1.5rem 0;min-height:280px;text-align:center">
		<ins class="adsbygoogle" style="display:block" data-ad-client="<?php echo esc_attr( DLSCALC_ADSENSE_CLIENT ); ?>" data-ad-slot="<?php echo esc_attr( $units[ $slot ] ); ?>" data-ad-format="auto" data-full-width-responsive="true"></ins>
		<script>(adsbygoogle=window.adsbygoogle||[]).push({});</script>
	</aside>
	<?php
}

/**
 * Top slot: fires as the article template part loads on tool pages and guides.
 *
 * @param string $slug Template part slug, 'template-parts/content/<page>'.
 */
add_action(
	'get_template_part',
	function ( $slug ) {
		if ( 0 === strpos( $slug, 'template-parts/content/' ) ) {
			dlscalc_ad_slot( 'top' );
		}
	}
);
add_action(
	'dlscalc_before_author_box',
	function () {
		dlscalc_ad_slot( 'bottom' );
	}
);

/**
 * The ads.txt body. Until a publisher ID is set it holds only comments, which authorises no seller.
 *
 * @return string
 */
function dlscalc_ads_txt() {
	$lines = array( '# ads.txt for ' . wp_parse_url( home_url(), PHP_URL_HOST ) );
	if ( preg_match( '/^ca-pub-(\d{10,20})$/', DLSCALC_ADSENSE_CLIENT, $m ) ) {
		$lines[] = 'google.com, pub-' . $m[1] . ', DIRECT, f08c47fec0942fa0';
	} else {
		$lines[] = '# Placeholder: no ad sellers yet. The Google AdSense line is added when the publisher ID is set in the theme (inc/ads.php).';
	}
	return implode( "\n", $lines ) . "\n";
}
