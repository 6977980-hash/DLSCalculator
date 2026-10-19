<?php
/**
 * Article below the bowling average and economy rate calculator.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 id="how-to-use">How do you use the bowling average calculator?</h2>
<p>Enter the overs bowled, the runs conceded and the wickets taken. The calculator gives the bowling average, the economy rate and the bowling strike rate together, and the dot balls are optional. Write overs the way the scorebook shows them: 38.4 means 38 overs and 4 balls, not 38.4 in decimal. The link in the address bar keeps the figures, so you can share them or come back to them later.</p>
<p>It works for one spell, one match or a whole career. For a T20 spell of 4 overs, 31 runs and 2 wickets, enter 4, 31 and 2.</p>

<h2 id="bowling-average-formula">How is bowling average calculated?</h2>
<p>Bowling average = runs conceded ÷ wickets taken. It is the number of runs a bowler gives away for each wicket, so lower is better. A bowler who has conceded 212 runs for 9 wickets has an average of 212 ÷ 9 = <strong>23.56</strong>. A bowler with no wickets has no average yet, so the calculator shows the economy rate instead.</p>

<h2 id="economy-rate">How do you calculate economy rate in cricket?</h2>
<p>Economy rate = runs conceded ÷ overs bowled, with the overs turned into balls first. 38.4 overs is 38 × 6 + 4 = 232 balls, which is 38.67 overs, so 212 runs gives an economy rate of 212 ÷ 38.67 = <strong>5.48</strong> an over. Using 38.4 as a decimal would give 5.52, which is wrong.</p>
<p>A T20 spell of 4 overs for 31 runs has an economy rate of 31 ÷ 4 = 7.75. Economy matters most in T20 cricket, where a bowler bowls only four overs and every run saved counts.</p>

<h2 id="bowling-strike-rate">What is bowling strike rate?</h2>
<p>Bowling strike rate = balls bowled ÷ wickets taken: how many balls a bowler needs, on average, for each wicket. 232 balls for 9 wickets is a bowling strike rate of <strong>25.8</strong>, a wicket every four overs or so. It is the bowler's version of the <a href="<?php echo esc_url( dlscalc_page_url( 'strike-rate-calculator' ) ); ?>">batting strike rate</a>, but lower is better.</p>
<p>The three numbers are linked: bowling average = economy rate × bowling strike rate ÷ 6. A bowler can keep a good average by taking wickets often even when expensive, or by being hard to score off even without many wickets.</p>

<h2 id="what-counts">Which runs and wickets count for the bowler?</h2>
<p>Wides and no-balls count against the bowler, along with every run scored off the bat. Byes and leg byes do not, because they are not the bowler's fault. Wides and no-balls are not legal balls, so they are not counted in the overs.</p>
<p>The bowler is credited with a wicket when the batter is bowled, caught, leg before wicket, stumped or hit wicket. Run outs and the other ways of getting out count for the team but not in the bowler's figures.</p>

<h2 id="good-figures">What are good bowling figures?</h2>
<p>These are rough guides for men's professional cricket today. In club, school and tennis ball cricket, compare a bowler with others in the same league.</p>
<div class="table-wrap" tabindex="0" role="region" aria-label="Bowling figures by format">
<table class="data">
	<caption>Rough guide to good bowling figures by format</caption>
	<thead><tr><th scope="col">Format</th><th scope="col">Economy rate</th><th scope="col">Bowling average</th><th scope="col">Bowling strike rate</th></tr></thead>
	<tbody>
		<tr><th scope="row">T20</th><td>under 7.5</td><td>under 22</td><td>under 18</td></tr>
		<tr><th scope="row">ODI</th><td>under 5</td><td>under 28</td><td>under 33</td></tr>
		<tr><th scope="row">Test</th><td>under 3</td><td>under 28</td><td>under 60</td></tr>
	</tbody>
</table>
</div>

<h2 id="dot-balls">Why count dot balls?</h2>
<p>A dot ball is a legal ball with no run scored off it. Dot ball percentage = dot balls ÷ balls bowled × 100. 130 dots in 232 balls is 56.0%. In T20 cricket it is one of the best signs of a bowler building pressure, because batters who face many dots take more risks and give more chances.</p>

<h2 id="more-calculators">Other cricket calculators</h2>
<p>To see what a chasing side needs, use the <a href="<?php echo esc_url( dlscalc_page_url( 'run-rate-calculator' ) ); ?>">run rate calculator</a>. For a points table, the <a href="<?php echo esc_url( dlscalc_page_url( 'net-run-rate-calculator' ) ); ?>">net run rate calculator</a> shows each team's NRR and what it needs to qualify.</p>

<h2 id="faq">Bowling average and economy rate FAQ</h2>
<?php dlscalc_faq_html( 'bowling-average-calculator' ); ?>

<h2 id="sources">Sources</h2>
<?php dlscalc_sources_html( dlscalc_stats_sources() ); ?>
