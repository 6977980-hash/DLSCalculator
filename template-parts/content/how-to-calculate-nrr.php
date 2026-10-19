<?php
/**
 * NRR guide (/how-to-calculate-nrr/). Owns "how to calculate nrr", "net run rate formula",
 * "nrr formula", "nrr full form" and "what is nrr in cricket". The calculator page owns the
 * "calculator" terms and the qualification maths. Examples are checked against the NRR engine.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlscalc_nrr = dlscalc_page_url( 'net-run-rate-calculator' );
?>
<h2 id="full-form">What is the full form of NRR in cricket?</h2>
<p>NRR stands for <strong>net run rate</strong>. It measures how much faster a team scores than the teams it plays against, across a whole tournament. A positive NRR means a team scores faster than its opponents do against it; a negative NRR means it scores slower.</p>
<p>NRR matters because points tables in cricket are often tight. When two teams finish level on points, net run rate is the usual way to separate them, so it decides who reaches the semi-finals or the playoffs in the IPL, the PSL, the T20 World Cup and most local leagues. Some competitions, including ICC events, compare the number of wins first and use NRR after that.</p>

<h2 id="formula">What is the net run rate formula?</h2>
<p class="formula">NRR = (total runs scored ÷ total overs faced) − (total runs conceded ÷ total overs bowled)</p>
<p>The first part is the team's <strong>run rate for</strong>: how fast it scores. The second part is its <strong>run rate against</strong>: how fast its opponents score against it. NRR is the difference between the two, usually given to three decimal places, like +0.104.</p>
<p>All the matches in the tournament so far are added together first, and only then divided. You do not work out NRR for each match and average them. A team that wins one match by a huge margin and loses two narrowly can still have a negative NRR, because every run and every over counts the same.</p>
<p>The rule is set out in the ICC playing conditions (clause 16.10.2 of the Men's T20I conditions), and leagues around the world use the same wording.</p>

<h2 id="step-by-step">How do you calculate NRR step by step?</h2>
<ol>
	<li>Add up all the runs your team has scored in the tournament, and all the overs it has faced.</li>
	<li>Convert overs into a number you can divide by. The number after the point is balls, so 17.3 overs is 17 and 3/6, which is 17.5 overs.</li>
	<li>If your team was bowled out in a match, count the full quota of overs for that innings (20 in a T20, 50 in an ODI), not the overs actually faced.</li>
	<li>Divide runs scored by overs faced. This is your run rate for.</li>
	<li>Do the same for runs conceded and overs bowled, again using the full quota for any side you bowled out. This is your run rate against.</li>
	<li>Subtract the run rate against from the run rate for. The answer is your NRR.</li>
</ol>
<p>The digit after the point in overs is balls, not tenths, so 17.3 overs is 17.5 in the formula. Working in balls avoids the most common NRR mistake: runs ÷ balls × 6 gives the run rate per over. To check a value, use the overs converter on the <a href="<?php echo esc_url( dlscalc_page_url( 'run-rate-calculator' ) ); ?>">run rate calculator</a>.</p>

<h2 id="example">NRR example over two matches</h2>
<p>Here is a team that has played two 20-over matches. It won the first. In the second it was bowled out for 120 in 17.3 overs, and the opponents chased down 121 in 18.2 overs to win.</p>
<div class="table-wrap" tabindex="0" role="region" aria-label="NRR example">
<table class="data data--num">
	<caption>Example: two 20-over matches</caption>
	<thead><tr><th scope="col">Match</th><th scope="col">Runs scored</th><th scope="col">Overs counted</th><th scope="col">Runs conceded</th><th scope="col">Overs counted</th></tr></thead>
	<tbody>
		<tr><th scope="row">1 (won)</th><td>168</td><td>20</td><td>151</td><td>20</td></tr>
		<tr><th scope="row">2 (lost, all out in 17.3)</th><td>120</td><td>20</td><td>121</td><td>18.2 = 18.333</td></tr>
		<tr><th scope="row">Total</th><td>288</td><td>40</td><td>272</td><td>38.333</td></tr>
	</tbody>
</table>
</div>
<p>Run rate for = 288 ÷ 40 = 7.200. Run rate against = 272 ÷ 38.333 = 7.096. <strong>NRR = 7.200 − 7.096 = +0.104.</strong></p>
<p>Notice that the bowled-out innings counts as 20 overs, not 17.3. That lowers the team's run rate for, which is the penalty for being bowled out. The opponents' chase counts as the 18.2 overs they actually needed, because they were not bowled out.</p>

<h2 id="special-cases">How is NRR calculated when a team is all out, in DLS matches and in no-results?</h2>
<ul>
	<li><strong>All out:</strong> the team that was bowled out is counted as having faced the full quota of overs it was entitled to in that match. A side bowled out for 120 in 15 overs of a T20 counts as 120 runs in 20 overs. If rain had already cut the match to 17 overs a side, it counts as 120 runs in 17 overs.</li>
	<li><strong>DLS matches that finish:</strong> when rain changes the target and the match is completed, Team 1 is credited with the target minus one run, off the overs Team 2 was given. Team 2 is credited with the runs and overs it actually made, or with all the overs it was given if it was bowled out.</li>
	<li><strong>DLS matches that are abandoned:</strong> if the match ends early but DLS decides a result, Team 1 is credited with Team 2's par score off the overs Team 2 actually faced.</li>
	<li><strong>No result:</strong> abandoned matches with no result are left out of NRR completely, both the runs and the overs.</li>
</ul>
<p>For a rain-affected match, the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-calculator' ) ); ?>">DLS calculator</a> gives the target and the par score to use in these rules.</p>

<h2 id="tournament">How is NRR shown in a points table?</h2>
<p>Most points tables show NRR as a single number beside the points. Some, like the IPL and ICC event tables, also show the runs and overs "for" and "against". Those four numbers are all you need to check the NRR yourself, or to work out what changes after the next match.</p>
<p>Take a single 20-over match: a team scores 160 in 20 overs and loses as the other side reaches 161 in 18.4 overs. Its run rate for is 8.000 and its run rate against is 161 ÷ 18.667 = 8.625, so its NRR from that match is <strong>−0.625</strong>. The winning team has exactly the opposite: +0.625. In a single match, the two teams' NRRs always cancel out; across a tournament they do not, because each team plays different opponents.</p>

<h2 id="improve">How can a team improve its NRR?</h2>
<p>Win by bigger margins. Batting first, that means scoring more and holding the other side to less. Chasing, it means reaching the target in fewer overs. Losing narrowly also hurts NRR far less than losing heavily.</p>
<p>Go back to the team in the example, on +0.104 after two matches. If it wins the third match batting first, 180 to 150, its NRR rises to <strong>+0.566</strong>. If it instead chases 151 and gets there in 16 overs, scoring 152, the NRR rises further, to <strong>+0.623</strong>, because the overs saved count as much as runs. Even in defeat the margin matters: chasing 181, finishing on 170 in 20 overs leaves the NRR at −0.115, but crawling to 120 drops it to −0.949.</p>
<p>Before a must-win match, the question is usually "by how much do we need to win?" The <a href="<?php echo esc_url( $dlscalc_nrr ); ?>">net run rate calculator</a> answers it: enter the points table, pick the team you need to pass, and it gives the winning margin or the overs to chase in.</p>

<h2 id="faq">NRR FAQ</h2>
<?php dlscalc_faq_html( 'how-to-calculate-nrr' ); ?>

<h2 id="sources">Sources</h2>
<p>The net run rate rule, the all-out rule and the DLS adjustments come from the ICC playing conditions.</p>
<?php dlscalc_sources_html( dlscalc_nrr_sources() ); ?>
