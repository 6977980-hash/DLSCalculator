<?php
/**
 * About page (/about/): who builds DLSCalc, how the calculators work and their limits.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlscalc_author = dlscalc_author();

get_header();
dlscalc_trust_page_open( 'About DLSCalc', 'Built and run by ' . $dlscalc_author['name'] . ', ' . strtolower( $dlscalc_author['role'] ) );
?>
<section class="author-box" aria-label="About the author">
	<div class="author-box__badge" aria-hidden="true"><?php echo esc_html( $dlscalc_author['initials'] ); ?></div>
	<div class="author-box__body">
		<div class="author-box__name"><?php echo esc_html( $dlscalc_author['name'] ); ?></div>
		<div class="author-box__role"><?php echo esc_html( $dlscalc_author['role'] ); ?></div>
		<p><?php echo esc_html( $dlscalc_author['bio'] ); ?></p>
	</div>
</section>

<h2 id="why">Why I built this</h2>
<p>I played club cricket for 20 years at National Cricket Club (NCC), a local city-level club registered with the Pakistan Cricket Board (PCB) and owned by the late Professor Nasir Khan. Today I organise tournaments. Ch Alee is the name I write under here; my full name is <?php echo esc_html( $dlscalc_author['alternate'] ); ?>.</p>
<p>Rain has decided more of my matches than I'd like. I built DLSCalc because I needed a quick, accurate way to work out DLS targets and net run rate during live matches, for my own games and for the teams I host.</p>
<p>The DLS and net run rate calculators follow the ICC playing conditions, and the batting and bowling calculators follow the MCC Laws of Cricket. Every tool shows its working, so players, scorers and organisers can check the numbers themselves while the covers are on.</p>

<h2 id="how">How the calculators work</h2>
<ul>
	<li><strong><a href="<?php echo esc_url( dlscalc_page_url( 'dls-calculator' ) ); ?>">DLS</a>:</strong> the ICC Standard Edition resource table, ball by ball, with the ICC formulas and G50 values. It is tested against every worked example in the ICC playing conditions, and the same engine powers the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-target-calculator-t20' ) ); ?>">T20 target</a>, the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-par-score-table' ) ); ?>">par score table</a> and the <a href="<?php echo esc_url( dlscalc_page_url( 'live-dls-par-score' ) ); ?>">live par tracker</a>.</li>
	<li><strong><a href="<?php echo esc_url( dlscalc_page_url( 'net-run-rate-calculator' ) ); ?>">Net run rate</a>:</strong> the standard ICC method, with overs counted in balls and the full quota used when a team is bowled out.</li>
	<li><strong><a href="<?php echo esc_url( dlscalc_page_url( 'run-rate-calculator' ) ); ?>">Run rate</a>:</strong> runs divided by overs, with overs counted in balls.</li>
	<li><strong><a href="<?php echo esc_url( dlscalc_page_url( 'strike-rate-calculator' ) ); ?>">Strike rate</a> and <a href="<?php echo esc_url( dlscalc_page_url( 'bowling-average-calculator' ) ); ?>">bowling average</a>:</strong> the standard scorebook formulas, with no-balls counted as balls faced and wides and no-balls charged to the bowler, as the Laws of Cricket set out.</li>
</ul>
<p>Everything runs in your browser. There is no sign-up and nothing you type is sent to us. The guides explain the rules behind the tools: the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-method' ) ); ?>">DLS method</a>, <a href="<?php echo esc_url( dlscalc_page_url( 'how-to-calculate-nrr' ) ); ?>">how to calculate NRR</a> and the <a href="<?php echo esc_url( dlscalc_page_url( 'vjd-method' ) ); ?>">VJD method</a> used in Indian domestic cricket.</p>

<h2 id="limits">What these tools cannot do</h2>
<p>International and major league matches use the DLS Professional Edition, which runs on ICC software and is not published. Its targets can differ from the Standard Edition by a few runs. For official results in those matches, the match officials' figure is final.</p>

<h2 id="sources">Sources</h2>
<?php dlscalc_sources_html( array_merge( dlscalc_dls_sources(), dlscalc_nrr_sources() ) ); ?>

<h2 id="contact">Contact</h2>
<p>Found a mistake or want a new calculator? Email <?php echo dlscalc_contact_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>. Corrections are checked and fixed quickly.</p>
<?php
dlscalc_trust_page_close();
get_footer();
