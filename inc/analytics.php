<?php
/**
 * Google Analytics 4 (gtag.js).
 *
 * The tag is kept off the critical path: a tiny inline stub queues the config at once, and gtag.js
 * itself is fetched only after the page has finished loading, so it never delays the calculators or
 * the Lighthouse scores. It is not printed for logged-in users (the site owner's own visits would
 * skew the numbers), on previews, or in the /embed/dls/ widget, which is served without wp_head
 * inside other sites' pages.
 *
 * Visitors in the EEA, the UK and Switzerland get Google Consent Mode with analytics storage denied:
 * no analytics cookies are set for them and Google receives only cookieless pings. If a consent
 * banner is added later, it should call gtag('consent', 'update', {...}) when a visitor agrees.
 *
 * The Privacy Policy (page-privacy-policy.php) describes this; change it in the same commit as
 * anything here.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'DLSCALC_GA_ID' ) ) {
	define( 'DLSCALC_GA_ID', 'G-XT5JL42RV1' );
}

/**
 * Whether the analytics tag is printed on this request.
 *
 * @return bool
 */
function dlscalc_analytics_on() {
	return (bool) preg_match( '/^G-[A-Z0-9]{4,16}$/', DLSCALC_GA_ID )
		&& ! is_admin()
		&& ! is_user_logged_in()
		&& ! is_preview()
		&& ! is_customize_preview();
}

/**
 * The tag, printed at the end of <head>.
 */
function dlscalc_analytics_tag() {
	if ( ! dlscalc_analytics_on() ) {
		return;
	}
	$id      = DLSCALC_GA_ID;
	$regions = array( 'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IS', 'IE', 'IT', 'LV', 'LI', 'LT', 'LU', 'MT', 'NL', 'NO', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB', 'CH' );
	?>
<script>
window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}
gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied',region:<?php echo wp_json_encode( $regions ); ?>});
gtag('js',new Date());gtag('config',<?php echo wp_json_encode( $id ); ?>);
addEventListener('load',function(){setTimeout(function(){var s=document.createElement('script');s.async=true;s.src=<?php echo wp_json_encode( 'https://www.googletagmanager.com/gtag/js?id=' . $id ); ?>;document.head.appendChild(s);},1500);});
</script>
	<?php
}
add_action( 'wp_head', 'dlscalc_analytics_tag', 99 );
