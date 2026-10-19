<?php
/**
 * Contact page (/contact/).
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
dlscalc_trust_page_open( 'Contact DLSCalc' );
?>
<div class="contact-box">
	<p>Email</p>
	<?php echo dlscalc_contact_link( 'DLSCalc' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>
</div>

<h2 id="mistake">Found a wrong number?</h2>
<p>Please tell me. Send the link to the result (every calculator keeps your inputs in the page address, so the link shows exactly what you saw) and the figure you expected, with where it came from, such as the scorecard or the match officials' target. Corrections are checked against the ICC playing conditions and fixed quickly.</p>

<h2 id="ideas">Want a new calculator or feature?</h2>
<p>If you score, organise or follow matches and a tool would save you time, describe what you need. Many of the features on this site, like the qualification scenario on the <a href="<?php echo esc_url( dlscalc_page_url( 'net-run-rate-calculator' ) ); ?>">net run rate calculator</a> and the <a href="<?php echo esc_url( dlscalc_page_url( 'live-dls-par-score' ) ); ?>">live DLS par tracker</a>, came from real match-day problems.</p>

<h2 id="official">Questions about an official result</h2>
<p>I can explain how a target was worked out, but I can't change an official result. For international and franchise matches, the match officials' DLS figure from the ICC software is final. For a local league, check its playing conditions or ask the organiser.</p>

<p>You can also read <a href="<?php echo esc_url( dlscalc_page_url( 'about' ) ); ?>">about DLSCalc</a> and who builds it.</p>
<?php
dlscalc_trust_page_close();
get_footer();
