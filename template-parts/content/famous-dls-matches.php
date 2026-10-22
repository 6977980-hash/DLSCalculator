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

<h2 id="real-matches">How close is it across 32 recent rain-hit matches?</h2>
<p>To test the calculator beyond the famous games, we took 32 rain-hit matches from 2015 to 2025, from T20 and ODI World Cups, the Champions Trophy, the Asia Cup, bilateral series and the IPL, BBL and PSL, and entered the numbers from each scorecard. Two of them had both a revised target and a final par, so there are 34 figures to compare with the official ones.</p>
<div class="table-wrap">
	<table class="data data--num">
		<caption>Official figure (Professional Edition) against our Standard Edition calculator</caption>
		<thead><tr><th scope="col">Format</th><th scope="col">Figures checked</th><th scope="col">Exactly the same</th><th scope="col">Within 3 runs</th><th scope="col">Largest gap</th></tr></thead>
		<tbody>
			<tr><th scope="row">T20</th><td>17</td><td>5</td><td>17</td><td>3 runs</td></tr>
			<tr><th scope="row">ODI</th><td>17</td><td>1</td><td>8</td><td>23 runs</td></tr>
		</tbody>
	</table>
</div>
<p>In T20 cricket the two editions are practically the same: no figure was more than 3 runs apart. In ODIs, 10 of the 17 were within 5 runs, but six were 9 runs or more apart, nearly all after a first innings of 300 or more or rain cutting a long stretch of either innings:</p>
<div class="table-wrap">
	<table class="data">
		<caption>ODI figures 9 runs or more from the official one</caption>
		<thead><tr><th scope="col">Match</th><th scope="col">Official</th><th scope="col">Our calculator</th><th scope="col">Difference</th></tr></thead>
		<tbody>
			<tr><th scope="row">New Zealand vs Pakistan, 2023 World Cup (par when play ended, Team 1 401)</th><td>179</td><td>156</td><td>23 lower</td></tr>
			<tr><th scope="row">India vs Pakistan, 2019 World Cup (target, Team 1 336)</th><td>302</td><td>320</td><td>18 higher</td></tr>
			<tr><th scope="row">England vs Australia, 5th ODI 2024 (par, Team 1 309)</th><td>116</td><td>103</td><td>13 lower</td></tr>
			<tr><th scope="row">India vs Pakistan, 2017 Champions Trophy (target, Team 1 319)</th><td>289</td><td>299</td><td>10 higher</td></tr>
			<tr><th scope="row">New Zealand vs South Africa, 2015 World Cup semi-final (target)</th><td>298</td><td>307</td><td>9 higher</td></tr>
			<tr><th scope="row">West Indies vs India, 3rd ODI 2019 (target)</th><td>255</td><td>264</td><td>9 higher</td></tr>
		</tbody>
	</table>
</div>
<p>The full list below shows exactly what we typed into the calculator for each match, so you can open the same numbers yourself and check them against the scorecard. Each figure was compared on 19 October 2026; we recheck the list when the engine changes.</p>
<?php dlscalc_real_matches_list(); ?>
<p>So for a T20, this calculator is a reliable guide to the official target. For an international ODI with a big first innings, treat it as an estimate and expect the official figure to differ, sometimes by 9 to 23 runs. In club, school and tournament cricket that uses the Standard Edition, our answer is the official one.</p>

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
