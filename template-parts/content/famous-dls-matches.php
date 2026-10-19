<?php
/**
 * Famous DLS matches hub (/famous-dls-matches/), also the accuracy page: every match's official
 * target next to our Standard Edition result. Owns "famous dls matches", "dls controversy matches"
 * and "dls calculator accuracy". Match pages sit under it; facts in dlscalc_matches().
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 id="matches">Which rain-hit matches are the most famous?</h2>
<p>Each page below tells the story of one match, shows the numbers at the moment the rain came, and recalculates the target with our <a href="<?php echo esc_url( dlscalc_page_url( 'dls-calculator' ) ); ?>">DLS Calculator</a>, step by step. Every page has a button that opens the same numbers in the calculator, so you can change an input and see what would have happened.</p>
<?php dlscalc_match_cards(); ?>

<h2 id="accuracy">How close is our calculator to the official targets?</h2>
<p>Our calculator uses the ICC's published DLS Standard Edition table. Here is its answer for every match next to the figure used on the day. "Same" means our target or par is exactly the official one.</p>
<?php dlscalc_matches_table(); ?>
<p>Of the eight matches where a DLS or D/L figure was worked out, our calculator gives the same number in three (2003, England vs Ireland and the IPL 2023 final) and is within 3 runs in three more. The two big gaps, 2015 and 2019, both have a high or nearly complete first innings that was cut short. The 1992 row is different: the rule used then was not DLS at all, and the gap shows how unfair it was.</p>

<h2 id="why-differ">Why do some targets differ from the official ones?</h2>
<ul>
	<li><strong>Two editions of DLS.</strong> International matches and big franchise leagues use the Professional Edition, which runs on ICC software and is not published. Most other matches use the Standard Edition, the published table our calculator follows exactly. The two agree in most low and medium-scoring chases.</li>
	<li><strong>High first-innings scores.</strong> When Team 2 has more resources than Team 1, the Standard Edition adds runs at a fixed rate using G50, the average 50-over score of 245. The Professional Edition handles high scores differently. That is why our target for the 2015 semi-final is 307 instead of 298.</li>
	<li><strong>Older matches used older rules.</strong> Before 1997 there was no D/L at all, and D/L itself has been updated several times since, becoming DLS in 2014. India's domestic cricket uses a different rule again, the <a href="<?php echo esc_url( dlscalc_page_url( 'vjd-method' ) ); ?>">VJD method</a>. We recalculate every match with today's table, so for older matches the comparison shows how today's method would have treated the same situation.</li>
	<li><strong>Unofficial figures.</strong> Some targets were never used, like India's targets in the 2019 semi-final, which were reported by the media in case more rain came.</li>
</ul>
<p>The <a href="<?php echo esc_url( dlscalc_page_url( 'dls-method' ) ); ?>">DLS method guide</a> explains the Standard and Professional Editions in more detail. Our engine is also checked against the six worked examples in the ICC playing conditions, and matches all of them.</p>

<h2 id="lessons">What do these matches teach?</h2>
<ul>
	<li><strong>Par ties, the target wins.</strong> South Africa went out of the 2003 World Cup because they reached the par score, not the target. Always know which number you are looking at.</li>
	<li><strong>Wickets are resources too.</strong> Losing wickets just before rain, like South Africa against Pakistan in 2022, makes the revised target much harder. Keeping wickets, like Bangladesh against India the same week, means the target falls by less than the overs.</li>
	<li><strong>A target can be higher than Team 1's score.</strong> When Team 1 loses overs mid-innings, as South Africa did in the 2015 semi-final, Team 2 can be set more runs than Team 1 made.</li>
	<li><strong>Being ahead on par only matters if play never resumes.</strong> Bangladesh were 17 ahead against India in 2022 and still lost the shortened chase.</li>
	<li><strong>Rain results move the points table too.</strong> In 2024 Bangladesh had to chase inside 12.1 overs to pass Afghanistan and Australia on net run rate. The <a href="<?php echo esc_url( dlscalc_page_url( 'net-run-rate-calculator' ) ); ?>">net run rate calculator</a> works out targets like that, and the <a href="<?php echo esc_url( dlscalc_page_url( 'how-to-calculate-nrr' ) . '#special-cases' ); ?>">NRR guide</a> shows how DLS matches count.</li>
</ul>
<p>If you score or captain in a match where rain is likely, keep the <a href="<?php echo esc_url( dlscalc_page_url( 'live-dls-par-score' ) ); ?>">live DLS par tracker</a> open during the chase: it shows after every over who is ahead if play stops.</p>

<h2 id="faq">Famous DLS matches FAQ</h2>
<?php dlscalc_faq_html( 'famous-dls-matches' ); ?>

<h2 id="sources">Sources</h2>
<p>Each match page lists the scorecards and reports its facts were checked against. The method and table come from:</p>
<?php dlscalc_sources_html(); ?>
