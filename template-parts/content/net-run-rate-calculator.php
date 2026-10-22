<?php
/**
 * Article below the net run rate calculator. The full formula lesson belongs to the NRR guide
 * (/how-to-calculate-nrr/), so this page explains using the tool and the qualification maths.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 id="how-to-use">How do you use the net run rate calculator?</h2>
<p>Enter the overs per side for your tournament, then one row for each match your team has played: the runs it scored and the overs it faced, and the runs it conceded and the overs it bowled. The net run rate updates as you type, with the full working underneath.</p>
<ol>
	<li>Write overs the cricket way: 19.4 means 19 overs and 4 balls. The calculator counts in balls, so you never have to convert to decimals.</li>
	<li>If your team was bowled out, tick "We were all out". If you bowled the other side out, tick "They were all out". The full quota of overs is then counted for that innings, as the ICC playing conditions require.</li>
	<li>If rain cut a match, type the overs each side was given in "Overs per side in this match". A side bowled out in that match is then counted as facing those overs, not the tournament's full overs.</li>
	<li>If a match ended with no result, tick "No result". It stays in your list but is left out of the totals.</li>
	<li>If DLS changed the target, enter Team 1's innings as the revised target minus one, off the overs Team 2 was given: when a <a href="<?php echo esc_url( dlscalc_page_url( 'dls-target-calculator-t20' ) ); ?>">T20 DLS target</a> of 144 in 15 overs follows a score of 180, type 143 in 15 overs, not 180 in 20. The <a href="<?php echo esc_url( dlscalc_page_url( 'how-to-calculate-nrr' ) . '#special-cases' ); ?>">NRR guide</a> explains the rain rules, including abandoned matches.</li>
	<li>Share the result on WhatsApp or copy the link. The link carries every match, so it opens the same answer for anyone you send it to.</li>
</ol>

<h2 id="how-it-works">How does the calculator work out NRR?</h2>
<p>Net run rate is the runs a team scores per over across the whole competition, minus the runs scored against it per over. The calculator adds up runs and balls for every match with a result before it divides, so a big win in a short match does not count for more than it should. The <a href="<?php echo esc_url( dlscalc_page_url( 'how-to-calculate-nrr' ) ); ?>">NRR guide</a> walks through the formula itself step by step.</p>
<p>We test it on a real tournament too. Entering every Super 12 match of the 2022 T20 World Cup this way, including bowled-out sides, two DLS finishes, England against Ireland abandoned with a DLS result and four no-results, gives all 12 teams exactly the net run rate in the official table, from New Zealand's +2.113 to Ireland's −1.615.</p>
<p>Take the example already in the calculator, three T20 matches:</p>
<div class="table-wrap" tabindex="0" role="region" aria-label="Example matches">
<table class="data data--num">
	<caption>Example: three 20-over matches</caption>
	<thead><tr><th scope="col">Match</th><th scope="col">Scored</th><th scope="col">Overs faced</th><th scope="col">Conceded</th><th scope="col">Overs bowled</th></tr></thead>
	<tbody>
		<tr><th scope="row">1</th><td>168</td><td>20</td><td>151</td><td>20</td></tr>
		<tr><th scope="row">2</th><td>142 all out</td><td>18.3, counted as 20</td><td>145</td><td>20</td></tr>
		<tr><th scope="row">3</th><td>155</td><td>20</td><td>139 all out</td><td>17.2, counted as 20</td></tr>
		<tr><th scope="row">Total</th><td>465</td><td>60</td><td>435</td><td>60</td></tr>
	</tbody>
</table>
</div>
<p>The team scored 465 runs in 60 overs, 7.750 an over, and conceded 435 in 60 overs, 7.250 an over. Its net run rate is 7.750 − 7.250 = <strong>+0.500</strong>. Without the all-out rule, match 2 would count as 142 in 18.3 overs and the answer would be wrong.</p>

<h2 id="qualification">How many runs do you need to win by to improve NRR?</h2>
<p>That is the question the qualification scenario answers. Fill in the points table with each team's played, won and points columns, plus runs and overs for and against, which most league tables show in full. Then choose your team, the team you want to pass and your next opponent, and say whether you will bat first or chase.</p>
<p>Batting first, enter the score you expect to make. The calculator tries every score the opponent could make and finds the highest one that still lifts your net run rate above your rival's. That gives the margin you need: <strong>win by at least this many runs</strong>. The opponent's innings counts as the full overs whether it is bowled out or not, which is the all-out rule working for you.</p>
<p>In the example table, Team C is on 4 points with an NRR of +0.063 and Team A is on 6 points with +0.670, so a win would draw Team C level on points and NRR decides. Batting first with 180 against another team, Team C must hold the opponent to 117 or fewer, a <strong>63-run win</strong>, to reach +0.680 and pass Team A's +0.670.</p>

<h2 id="chase-overs">Within how many overs do you need to chase?</h2>
<p>Chasing, enter the opponent's score. The calculator works out the target and finds the slowest winning chase that still puts your NRR above the rival's, ball by ball. The answer reads like "reach 161 within 12.2 overs". Every ball quicker only helps.</p>
<p>Using the same table, if the opponent scores 160, Team C needs to reach 161 within <strong>12.2 overs</strong> to pass Team A. At 12.3 overs its NRR would be +0.661, just short of +0.670.</p>
<p>If even the fastest possible chase or a win by every run is not enough, the calculator says so. That is useful too: it tells a captain to look at other results or to protect points first.</p>

<h2 id="head-to-head">What if your next opponent is the team you need to pass?</h2>
<p>Then both net run rates move, and the calculator handles that when you pick the same team as your next opponent. Your runs scored count against the rival, and its runs count against you. A head-to-head win is worth much more: in the example, Team C batting first with 180 against Team A needs to win by only <strong>25 runs</strong>, because Team A's NRR drops from +0.670 to +0.285 at the same time.</p>
<p>The scenario also checks points first. If a win takes you clear on points, the margin does not matter and the calculator says "Any win". If a win still leaves you behind on points, net run rate cannot help yet, and it says that instead.</p>

<h2 id="leagues">Can you use it for the IPL, PSL, T20 World Cup and local leagues?</h2>
<p>Yes. Every major league and ICC event uses the same net run rate rule, and so do most club and school competitions. During the IPL or PSL, copy the "for" and "against" columns from the official points table into the qualification scenario and you can test any result before the match starts. For your own tournament, keep the table here: the page remembers it on your device, so you only need to add the latest match.</p>
<p>Check one rule in your competition's playing conditions: what comes first when teams are level on points. Many leagues go straight to net run rate. ICC events compare the number of wins first, then net run rate. Pick the rule under "Level on points" in the qualification scenario: the standings and the answer follow it, and the result says which rule it used.</p>

<h2 id="faq">Net run rate calculator FAQ</h2>
<?php dlscalc_faq_html( 'net-run-rate-calculator' ); ?>

<h2 id="sources">Sources</h2>
<?php dlscalc_sources_html( dlscalc_nrr_sources() ); ?>
