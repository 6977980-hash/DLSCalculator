<?php
/**
 * Terms and Disclaimer page (/terms/, audit F94). The text lives here, not in the editor. The page
 * itself is made by the owner in WP admin (an empty page titled "Terms"); the footer link appears
 * once it is published.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
dlscalc_trust_page_open( 'Terms and Disclaimer', 'Last updated ' . dlscalc_updated_date( 'j F Y' ) );
?>
<p>DLSCalc (dlscalculator.com) is a free set of cricket calculators. By using the site you accept these terms. They are short because the site is simple: no accounts, no payments and nothing to sign up for.</p>

<h2 id="independent">An independent tool, not an official one</h2>
<p>DLSCalc is not affiliated with the ICC, any cricket board or any league. The DLS calculators use the DLS Standard Edition, the method the ICC publishes in its playing conditions. International and many franchise matches use the Professional Edition software, which is not public, so their official targets can differ from the figures here. <?php echo esc_html( dlscalc_accuracy_line() ); ?></p>

<h2 id="official-figures">The official figure is final</h2>
<p>In any match, the target, par score or result set by the match officials, or by the rules of your competition, is final. Use DLSCalc to plan, to check and to understand a decision, not to overrule one. For a local league, the league's own playing conditions decide which method and which minimum overs apply.</p>

<h2 id="no-warranty">No warranty</h2>
<p>The calculators are checked against the ICC's worked examples and real matches, and mistakes are fixed as soon as they are reported. Even so, the site is provided as it is, without any promise that it is free of errors or always available. Check the numbers that matter, such as a target that decides a final or a net run rate that decides who qualifies, against the scorecard and the rules of your competition.</p>

<h2 id="liability">Limits on liability</h2>
<p>As far as the law allows, DLSCalc and its author are not liable for any loss or dispute that comes from using the site or relying on its results, including decisions made in a match or a tournament. Nothing in these terms limits any right you have that the law does not allow to be limited.</p>

<h2 id="your-use">Using the calculators and the widget</h2>
<p>You may use the calculators for any match, print the par sheets and share result links. Result links carry your inputs in the address, so anyone with the link sees them. Other sites may show the free <a href="<?php echo esc_url( dlscalc_page_url( 'dls-calculator' ) . '#embed' ); ?>">DLS widget</a> with the embed code as given, including its credit link. Please do not use the site in a way that harms it or other visitors, such as automated mass requests.</p>

<h2 id="content">Text and code</h2>
<p>The guides, tables and calculators are the work of DLSCalc. Quoting a short part with a link back is welcome. Copying whole pages or tools to another site is not allowed without asking first.</p>

<h2 id="privacy">Privacy</h2>
<p>How the site handles data, including the offline copy and the entries saved in your browser, is explained in the <a href="<?php echo esc_url( dlscalc_page_url( 'privacy-policy' ) ); ?>">privacy policy</a>.</p>

<h2 id="changes">Changes to these terms</h2>
<p>If these terms change, the date at the top changes too. Using the site after a change means you accept the new version.</p>

<h2 id="contact">Contact</h2>
<p>Found a wrong answer, or a question about these terms: email <?php echo dlscalc_contact_link( 'Terms' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>.</p>
<?php
dlscalc_trust_page_close();
get_footer();
