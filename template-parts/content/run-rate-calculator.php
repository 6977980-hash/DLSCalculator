<?php
/**
 * Article below the run rate calculator.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 id="how-to-use">How do you use the run rate calculator?</h2>
<p>Choose whether the batting side is chasing a target or batting first, then enter the overs per side, the runs scored so far and the overs bowled so far. When chasing, add the target too. The answer updates as you type, and the link in the address bar reproduces it for anyone you send it to.</p>
<p>Write overs the way the scoreboard shows them: 12.4 means 12 overs and 4 balls. The calculator counts in balls, so it never mixes up 12.4 overs with 12.4 in decimal.</p>

<h2 id="current-run-rate">How is the current run rate calculated?</h2>
<p>Current run rate = runs scored ÷ overs bowled, with the overs turned into balls first. A team on 92 after 12.4 overs has faced 76 balls, which is 12.67 overs, so its current run rate is 92 ÷ 12.67 = <strong>7.26</strong> runs an over.</p>
<p>Using 12.4 as a decimal would give 7.42, which is wrong by more than a run every six overs. That is why scorers and broadcasters always work in balls.</p>

<h2 id="required-run-rate">How do you calculate the required run rate?</h2>
<p>Required run rate = runs needed ÷ overs left. Runs needed is the target minus the runs already scored, and overs left are the balls left divided by six.</p>
<p>Chasing 181 in a T20, a team on 92 after 12.4 overs needs 89 more runs. There are 44 balls left, or 7.2 overs, which is 7.33 in decimal. The required run rate is 89 ÷ 7.33 = <strong>12.14</strong> an over, or 2.02 runs a ball. The current rate is 7.26, so the batting side must score 4.87 an over faster than it has so far.</p>
<p>In a 50-over chase of 281, a team on 120 after 25 overs needs 161 off 150 balls. Its required rate is <strong>6.44</strong> against a current rate of 4.80, a gap of 1.64 an over with half the innings still to come.</p>
<div class="table-wrap" tabindex="0" role="region" aria-label="Required run rate examples">
<table class="data data--num">
	<caption>Required run rate at the start of a chase</caption>
	<thead><tr><th scope="col">Format</th><th scope="col">Target</th><th scope="col">Required rate</th><th scope="col">Runs a ball</th></tr></thead>
	<tbody>
		<tr><th scope="row">T20, 20 overs</th><td>161</td><td>8.05</td><td>1.34</td></tr>
		<tr><th scope="row">T20, 20 overs</th><td>201</td><td>10.05</td><td>1.68</td></tr>
		<tr><th scope="row">Rain-cut, 15 overs</th><td>144</td><td>9.60</td><td>1.60</td></tr>
		<tr><th scope="row">ODI, 50 overs</th><td>281</td><td>5.62</td><td>0.94</td></tr>
		<tr><th scope="row">ODI, 50 overs</th><td>321</td><td>6.42</td><td>1.07</td></tr>
	</tbody>
</table>
</div>

<h2 id="runs-per-ball">What does "runs needed per ball" tell you?</h2>
<p>It is the required rate divided by six, and it is how most players think about a close finish. Anything under 1 run a ball is comfortable with wickets in hand. Between 1.5 and 2 needs boundaries every over. Above 2 a ball, the chase usually needs a big over or two to stay alive.</p>
<p>The calculator also shows the gap between the required and current rates. A gap under 1 an over is easy to close with a couple of good overs; a gap of 4 or more late in a T20 is the point where most chases fall away.</p>

<h2 id="projected-score">How is the projected score worked out?</h2>
<p>When batting first, the calculator projects the final total: runs so far + current run rate × overs left. At 92 after 12.4 overs of a T20, the team scores <strong>145</strong> if it keeps going at 7.26 an over, 165 at 10 an over and 180 at 12 an over.</p>
<p>The projections assume no wickets change the pace, so treat them as a guide for setting a target, not a forecast. Most T20 sides score faster in the last five overs than at any other stage, so the "+2" or "+3" line is often the more realistic one.</p>

<h2 id="rain">Does the required rate change when rain cuts the overs?</h2>
<p>Yes, because both the target and the overs change. Take the DLS target from the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-target-calculator-t20' ) ); ?>">T20 DLS target calculator</a> or the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-calculator' ) ); ?>">DLS calculator</a>, enter it here as the target with the reduced overs per side, and the required rate is worked out for the new chase.</p>
<p>A 20-over match where Team 1 makes 180 and the chase is cut to 15 overs gives a target of 144. After 5 overs on 40, the chasing side needs 104 off 60 balls, a required rate of <strong>10.40</strong>. To see whether it would be ahead if the rain came back, use the <a href="<?php echo esc_url( dlscalc_page_url( 'live-dls-par-score' ) ); ?>">live DLS par tracker</a>.</p>

<h2 id="overs-balls">How do you convert overs to balls?</h2>
<p>Multiply the whole overs by six and add the balls after the point: 12.4 overs is 12 × 6 + 4 = 76 balls. To go back, divide by six: 76 balls is 12 overs and 4 balls left over, or 12.4 overs. The converter above does both, and also gives the decimal overs used in run rate sums: 76 balls is 12.67 overs.</p>

<h2 id="nrr">How is run rate different from net run rate?</h2>
<p>Run rate measures one innings. Net run rate measures a whole tournament: a team's runs per over across every match, minus its opponents' runs per over against it. Leagues use it to separate teams level on points, and the <a href="<?php echo esc_url( dlscalc_page_url( 'net-run-rate-calculator' ) ); ?>">net run rate calculator</a> works it out from your matches and shows the margin you need to move up the table.</p>

<h2 id="faq">Run rate calculator FAQ</h2>
<?php dlscalc_faq_html( 'run-rate-calculator' ); ?>

<h2 id="sources">Sources</h2>
<?php dlscalc_sources_html( dlscalc_nrr_sources() ); ?>
