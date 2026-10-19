<?php
/**
 * Privacy Policy page (/privacy-policy/). The text lives here, not in the editor, so WordPress's
 * sample text in the page never shows.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
dlscalc_trust_page_open( 'Privacy Policy', 'Last updated ' . dlscalc_updated_date( 'j F Y' ) );
?>
<p>DLSCalc (dlscalculator.com) is a free set of cricket calculators run by Ch Alee. This page explains what data the site handles. In short: the calculators work in your browser, there are no accounts, and nothing you type into a calculator is sent to us.</p>

<h2 id="calculators">What happens to the numbers you enter</h2>
<p>Every calculation runs on your own device. Scores, overs, points tables and team names stay in your browser. When you press Calculate without JavaScript, or open a shared result link, the inputs travel in the page address to our server so it can show the result; they are not stored in a database or linked to you.</p>
<p>Result links contain your inputs in the address. Anyone you send a link to can see those inputs, so share links only with people you want to see them.</p>

<h2 id="local-storage">Data saved on your device</h2>
<p>The net run rate calculator's points table and the live DLS par tracker save your latest entries in your browser's local storage, so they are still there when you come back. This data never leaves your device. You can clear it with the "Start a new match" or reset buttons, or by clearing your browser's site data.</p>

<h2 id="cookies">Cookies</h2>
<p>The site itself does not set cookies for visitors and does not use advertising or tracking cookies. WordPress sets cookies only for people who log in to manage the site. Our hosting provider's network may set a short-lived security cookie to protect the site from automated attacks.</p>

<h2 id="logs">Server logs and hosting</h2>
<p>The site is hosted by Hostinger. Like any web server, it records basic technical information about each request, such as the IP address, browser type, the page requested and the time, to keep the site secure and working. These logs are kept for a limited time by the host and are not used to identify you.</p>

<h2 id="third-parties">Third parties</h2>
<p>The site loads no third-party scripts, fonts or analytics. Links to other sites, such as the ICC playing conditions or a WhatsApp share, take you to those services, and their own privacy policies apply there.</p>

<h2 id="changes">Changes to this policy</h2>
<p>If the site ever adds analytics, advertising or anything else that handles personal data, this page will be updated first and the date at the top will change.</p>

<h2 id="contact">Contact</h2>
<p>Questions about privacy, or a request about your data: email <?php echo dlscalc_contact_link( 'Privacy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>.</p>
<?php
dlscalc_trust_page_close();
get_footer();
