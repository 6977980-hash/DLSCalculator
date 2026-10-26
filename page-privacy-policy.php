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
<p>DLSCalc (dlscalculator.com) is a free set of cricket calculators run by Ch Alee. This page explains what data the site handles. In short: the calculators work in your browser, there are no accounts, we store nothing you type, and we use Google Analytics to count visits.</p>

<h2 id="who">Who runs the site</h2>
<p>DLSCalc is run by Ali Ahmad Chaudhry, who writes as Ch Alee, a cricketer and tournament organiser in Pakistan. He decides how the site handles data and is the person to contact about it (the "controller" under data protection laws such as the GDPR). Email <?php echo dlscalc_contact_link( 'Privacy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>.</p>

<h2 id="calculators">What happens to the numbers you enter</h2>
<p>Every calculation runs on your own device. Scores, overs, points tables and team names stay in your browser. When you press Calculate without JavaScript, or open a shared result link, the inputs travel in the page address to our server so it can show the result; they are not stored in a database or linked to you. Like the address of any page, a result link, including any team names in it, appears in the server logs described below.</p>
<p>Result links contain your inputs in the address. Anyone you send a link to can see those inputs, so share links only with people you want to see them.</p>

<h2 id="local-storage">Data saved on your device</h2>
<p>The net run rate calculator's points table and the live DLS par tracker save your latest entries in your browser's local storage, so they are still there when you come back. This data never leaves your device. You can clear it with the "Start a new match" or reset buttons, or by clearing your browser's site data.</p>
<p>So that the calculators still open without a signal, your browser also keeps an offline copy of the site's pages and files (about half a megabyte), managed by a small script called a service worker. It holds only the site's own pages, never what you type, and it is removed when you clear the site's data.</p>

<h2 id="cookies">Cookies</h2>
<p>The site uses Google Analytics, which sets first-party cookies (named <code>_ga</code> and <code>_ga_*</code>, kept for up to two years) to tell new visits from returning ones. In the European Economic Area, the UK and Switzerland these cookies are switched off and Google Analytics counts visits without cookies. The site uses no advertising cookies. WordPress sets cookies only for people who log in to manage the site. Our hosting provider's network may set a short-lived security cookie to protect the site from automated attacks.</p>
<p>You can block or delete cookies in your browser settings, or install the <a href="https://tools.google.com/dlpage/gaoptout" rel="nofollow noopener">Google Analytics opt-out browser add-on</a>. The calculators work the same either way.</p>

<h2 id="analytics">Google Analytics</h2>
<p>To learn which calculators and guides people use, the site uses Google Analytics 4, a service of Google. It records things like the pages you open, how you reached the site, your browser and device type, and your approximate location (country or city). Google Analytics does not log or store IP addresses. The numbers you type into the calculators are not sent to Google Analytics, although the address of a result link, which contains its inputs, is recorded like any other page address.</p>
<p>We see this information only as totals and reports, and use it only to improve the site. Analytics data is kept for up to 14 months. Google processes it under its own <a href="https://policies.google.com/privacy" rel="nofollow noopener">privacy policy</a> and may process it on servers outside your country. More about how Google uses data from sites that use its services: <a href="https://policies.google.com/technologies/partner-sites" rel="nofollow noopener">policies.google.com/technologies/partner-sites</a>.</p>

<h2 id="logs">Server logs and hosting</h2>
<p>The site is hosted by Hostinger. Like any web server, it records basic technical information about each request, such as the IP address, browser type, the page requested and the time, to keep the site secure and working. These logs are kept for a limited time under Hostinger's own policy. We do not copy, analyse or combine them with anything else, and they are not used to identify you.</p>

<h2 id="rights">Legal basis and your rights</h2>
<p>The personal data the site handles is the technical information in the server logs, the visit data collected by Google Analytics, and an email if you write to us. The logs are kept because the site has a legitimate interest in staying secure and working. Analytics is used because the site has a legitimate interest in knowing which tools are useful; where the law requires consent for analytics cookies, they are not set. Emails are used only to answer you and are deleted when they are no longer needed.</p>
<p>You can ask for a copy of any personal data we hold about you, ask for it to be corrected or deleted, or object to how it is used, by emailing the address above. If you live in the UK or the European Union, you can also complain to your data protection authority.</p>

<h2 id="third-parties">Third parties</h2>
<p>The only third-party script the site loads is Google Analytics, described above. It loads no third-party fonts, and the embeddable widget at /embed/dls/ loads no analytics at all. Links to other sites, such as the ICC playing conditions or a WhatsApp share, take you to those services, and their own privacy policies apply there.</p>

<h2 id="changes">Changes to this policy</h2>
<p>If the site ever adds advertising or anything else that handles personal data, this page will be updated first and the date at the top will change.</p>

<h2 id="contact">Contact</h2>
<p>Questions about privacy, or a request about your data: email <?php echo dlscalc_contact_link( 'Privacy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>.</p>
<?php
dlscalc_trust_page_close();
get_footer();
