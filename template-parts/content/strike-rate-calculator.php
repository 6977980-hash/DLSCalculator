<?php
/**
 * Article below the strike rate and batting average calculator.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 id="how-to-use">How do you use the strike rate calculator?</h2>
<p>Enter the runs scored and the balls faced, and the strike rate appears at once. Add the innings played and the not outs to get the batting average too. The fours and sixes are optional: with them, the calculator shows how many runs came in boundaries and how often the batter found the rope. The link in the address bar keeps every figure, so you can send the result to a teammate or save it for the season review.</p>
<p>It works the same for one innings, one tournament or a whole career. For a single knock, enter 1 innings and 1 not out if the batter was still in at the end.</p>

<h2 id="strike-rate-formula">How is strike rate calculated in cricket?</h2>
<p>Strike rate = runs scored ÷ balls faced × 100. It is the number of runs a batter would score off 100 balls at the same pace. A batter who makes 45 off 30 balls has a strike rate of 45 ÷ 30 × 100 = <strong>150.00</strong>. Someone who makes 182 off 140 balls across a few matches strikes at <strong>130.00</strong>.</p>
<p>Count the balls the batter actually faced. No-balls count as balls faced, because the batter can score off them, but wides do not, since the ball was out of the batter's reach.</p>
<div class="table-wrap" tabindex="0" role="region" aria-label="Strike rate examples">
<table class="data data--num">
	<caption>Strike rate for common scores</caption>
	<thead><tr><th scope="col">Runs</th><th scope="col">Balls</th><th scope="col">Strike rate</th><th scope="col">Runs a ball</th></tr></thead>
	<tbody>
		<tr><th scope="row">30</th><td>30</td><td>100.00</td><td>1.00</td></tr>
		<tr><th scope="row">45</th><td>30</td><td>150.00</td><td>1.50</td></tr>
		<tr><th scope="row">50</th><td>40</td><td>125.00</td><td>1.25</td></tr>
		<tr><th scope="row">75</th><td>90</td><td>83.33</td><td>0.83</td></tr>
		<tr><th scope="row">100</th><td>55</td><td>181.82</td><td>1.82</td></tr>
	</tbody>
</table>
</div>

<h2 id="batting-average">How do you calculate batting average?</h2>
<p>Batting average = runs scored ÷ number of times out. The number of times out is the innings played minus the not outs. A batter with 182 runs in 5 innings, 1 of them not out, was dismissed 4 times, so the average is 182 ÷ 4 = <strong>45.50</strong>.</p>
<p>Dividing by innings instead would give 36.40, which is the runs per innings. The calculator shows both. A not out innings adds runs but no dismissal, which is why a batter who finishes off many chases can average more than the runs they make per innings. A batter who has never been out has no average yet, however many runs they have made.</p>

<h2 id="strike-rate-vs-average">What is the difference between strike rate and batting average?</h2>
<p>Strike rate measures speed: how quickly runs come. Batting average measures reliability: how many runs come before the batter gets out. A T20 opener might average 28 at a strike rate of 150, while a Test number three averages 50 at 55. Both can be valuable, and the best batters in white-ball cricket combine a high average with a high strike rate.</p>
<p>Bowlers have a strike rate too, but it means balls bowled per wicket and works the other way round: lower is better. The <a href="<?php echo esc_url( dlscalc_page_url( 'bowling-average-calculator' ) ); ?>">bowling average calculator</a> works that out with the economy rate.</p>

<h2 id="good-strike-rate">What is a good strike rate?</h2>
<p>It depends on the format and the batting position. These are rough guides for top-order batters in men's professional cricket today; club and school cricket, tennis ball cricket and women's cricket each have their own levels.</p>
<div class="table-wrap" tabindex="0" role="region" aria-label="Strike rate by format">
<table class="data">
	<caption>Rough guide to batting strike rates by format</caption>
	<thead><tr><th scope="col">Format</th><th scope="col">Typical</th><th scope="col">Fast</th></tr></thead>
	<tbody>
		<tr><th scope="row">T20</th><td>125 to 140</td><td>150 and above</td></tr>
		<tr><th scope="row">ODI</th><td>80 to 95</td><td>100 and above</td></tr>
		<tr><th scope="row">Test</th><td>45 to 60</td><td>70 and above</td></tr>
	</tbody>
</table>
</div>

<h2 id="target-strike-rate">How many runs do you need to reach a target strike rate?</h2>
<p>Enter a target strike rate and the calculator works out the runs needed after 6, 12 and 30 more balls. The sum is: target ÷ 100 × (balls so far + more balls) − runs so far, rounded up. A batter on 182 off 140 who wants to reach 150 needs 46 runs off the next 12 balls, because 150% of 152 balls is 228 runs. Off the next 6 balls it would take 37, more than six sixes, so the calculator marks it as not possible.</p>

<h2 id="boundaries">What do the boundary figures show?</h2>
<p>Runs in fours and sixes = fours × 4 + sixes × 6, shown as a share of all runs. 18 fours and 6 sixes make 108 of 182 runs, or 59.3%. Balls per boundary is the balls faced divided by the number of fours and sixes: 140 ÷ 24 = one boundary every 5.8 balls. A batter with a low boundary share and a good strike rate runs well between the wickets, which matters a lot on big grounds.</p>

<h2 id="more-calculators">Other cricket calculators</h2>
<p>For a whole team's scoring rate in a chase, use the <a href="<?php echo esc_url( dlscalc_page_url( 'run-rate-calculator' ) ); ?>">run rate calculator</a>. For tournament standings, the <a href="<?php echo esc_url( dlscalc_page_url( 'net-run-rate-calculator' ) ); ?>">net run rate calculator</a> works out NRR and what it takes to qualify, and the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-calculator' ) ); ?>">DLS calculator</a> sets the target when rain cuts a match short.</p>

<h2 id="faq">Strike rate and batting average FAQ</h2>
<?php dlscalc_faq_html( 'strike-rate-calculator' ); ?>

<h2 id="sources">Sources</h2>
<?php dlscalc_sources_html( dlscalc_stats_sources() ); ?>
