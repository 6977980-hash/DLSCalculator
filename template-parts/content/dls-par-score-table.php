<?php
/**
 * Article below the par score table tool.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 id="what-is-par">What is a DLS par score?</h2>
<p>The DLS par score is the score Team 2 needs at a given over and wicket count to be exactly level with Team 1 if the match ends there. Above par, Team 2 is ahead and would win if rain stopped play for good. Below par, Team 1 is ahead. On par exactly, the match is tied.</p>
<p>Par is not the same as the run rate. A team chasing 160 in 20 overs needs 8 an over, but after 10 overs its par with no wickets down is only 69, not 80. That is because a team with all its wickets in hand still has most of its batting to come, so DLS expects it to score faster in the second half of the chase.</p>

<h2 id="how-to-use">How do you use this par score table?</h2>
<p>Pick the overs per side with a quick button or type any number from 5 to 50, then enter Team 1's final score. The table lists the par after every completed over of the chase, with one column for each number of wickets down, from 0 to 9. Print it and give a copy to the scorers.</p>
<p>To read it during a match, find the overs Team 2 has completed in the left column and the wickets it has lost across the top. If Team 2's score is above the number in that cell, it is ahead on DLS. If rain arrives in the middle of an over, use the row for the overs already completed, or enter the exact stoppage in the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-calculator' ) ); ?>">DLS calculator</a> for a ball-by-ball answer.</p>
<p>If overs were lost before the chase began, enter the reduced overs for Team 2. The target and every par score in the table are then worked out for that shorter chase.</p>

<h2 id="how-calculated">How is the DLS par score calculated?</h2>
<p>Each cell starts from the resources Team 2 still has at that over and wicket count, taken from the ICC Standard Edition table. Those are subtracted from Team 2's total resources to give the resources used. Team 1's score is then scaled by resources used ÷ Team 1's resources and rounded down.</p>
<p>In a 50-over match where Team 1 makes 250, Team 2 at 25 overs with 2 wickets down has 25 overs left worth 60.5%, so it has used 39.5%. The par is 250 × 39.5 ÷ 100 = 98.75, rounded down to <strong>98</strong>. With 5 wickets down at the same point, 25 overs left are worth only 42.2%, so Team 2 has used 57.8% and the par is <strong>144</strong>.</p>
<p>The full method, including why each wicket changes the value of the overs left, is in the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-method' ) ); ?>">DLS method guide</a>.</p>

<h2 id="t20-par">What is the DLS par score in a T20?</h2>
<p>For a 20-over chase of 160, the par after 5 overs is 32 with no wickets down and 39 with 2 down. After 10 overs it is 69 with no wickets down, 75 with 3 down and 95 with 6 down. Choose 20 overs above and enter your own score to get every over.</p>
<div class="table-wrap" tabindex="0" role="region" aria-label="Sample T20 par scores">
<table class="data data--num">
	<caption>Sample par scores, 20-over chase of 160</caption>
	<thead><tr><th scope="col">Overs</th><th scope="col">0 down</th><th scope="col">2 down</th><th scope="col">4 down</th><th scope="col">6 down</th><th scope="col">8 down</th></tr></thead>
	<tbody>
	<?php
	$dlscalc_t = dlscalc_dls_par_table(
		array(
			'o' => '20',
			'r' => '160',
		)
	);
	foreach ( array( 5, 8, 10, 12, 15 ) as $dlscalc_o ) {
		$dlscalc_c = $dlscalc_t['rows'][ $dlscalc_o - 1 ]['cells'];
		printf( '<tr><th scope="row">%d</th><td>%d</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td></tr>', (int) $dlscalc_o, (int) $dlscalc_c[0], (int) $dlscalc_c[2], (int) $dlscalc_c[4], (int) $dlscalc_c[6], (int) $dlscalc_c[8] );
	}
	?>
	</tbody>
</table>
</div>
<p>Look at how little the par changes between 0 and 2 wickets early on, and how sharply it climbs after 6 wickets. Late in a T20 chase, with few overs left, wickets matter less, so the columns move closer together again.</p>

<h2 id="odi-par">What is the DLS par score in an ODI?</h2>
<p>In a 50-over match where Team 1 scores 250, the par after 20 overs is 62 with no wickets down, 81 with 2 down and 114 with 4 down. After 30 overs it is 108 with no wickets down and 138 with 4 down. The full ODI table runs from over 1 to over 49.</p>
<p>ODI pars move further from the plain run rate than T20 pars, because a team has so many overs left to use its wickets. That is why a side 2 down after 20 overs can be behind the required rate yet comfortably ahead on DLS.</p>

<h2 id="short-matches">What are DLS par scores for 8, 10, 12 and 15-over matches?</h2>
<p>Short matches use the same table, starting from fewer resources. In a 10-over match where Team 1 scores 100, the par after 5 overs is 46 with no wickets down and 52 with 5 down. In an 8-over match of 90, the par after 4 overs is 42 with no wickets down.</p>
<p>The quick buttons above cover the lengths that rain-shortened club and tournament games most often start with: 8, 10, 12, 15 and 20 overs, plus a full 50-over ODI. Any other length from 5 to 50 overs works too, so a 30-over or 40-over league match is one number away.</p>
<p>Short matches are where the par table helps most. With only a few overs in the chase, one shower can decide the game, and in a 12-over match of 120 the par after 6 overs climbs from 55 with no wickets down to 67 with 6 down. Knowing that before the umpires take the players off tells a batting side whether to attack or protect wickets.</p>

<h2 id="reduced-chase">How does a shorter chase change the par score table?</h2>
<p>When overs are lost before the chase starts, Team 2 has fewer resources, so the target falls and every par score in the table is lower too. Enter the reduced overs in "Overs for Team 2" and the whole table is worked out for the shorter chase.</p>
<p>Take a 20-over match where Team 1 makes 160 and rain cuts the chase to 15 overs. Team 2's resources drop to 45.2%, so the target becomes 128 in 15 overs. After 5 overs of that chase the par is 37 with no wickets down and 53 with 5 down. After 10 overs it is 79 with no wickets down. These are higher than the pars at the same point of a full 20-over chase, because Team 2 has fewer overs left to make up the runs.</p>
<p>If rain interrupts the chase after it has started, the overs and wickets at the stoppage matter as well, so use the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-calculator' ) ); ?>">DLS calculator</a> for that. It gives a new par table for the overs left after every stoppage.</p>

<h2 id="par-vs-target">How is the par score different from the target?</h2>
<p>The target is a single number for the whole chase: the par score for Team 2's full overs, plus one. The par score changes after every over and every wicket. It only decides the match if play cannot continue, and it is what a captain checks when dark clouds come over.</p>
<p>When overs are lost during the chase, the target changes as well. Enter the stoppage in the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-calculator' ) ); ?>">DLS calculator</a> or, for a 20-over match, in the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-target-calculator-t20' ) ); ?>">T20 target calculator</a>, which give the new target and a fresh par table for the overs that are left. Once a rain-hit match has a result, the <a href="<?php echo esc_url( dlscalc_page_url( 'net-run-rate-calculator' ) ); ?>">net run rate calculator</a> shows how to enter it in the points table.</p>

<h2 id="before-the-match">Why print a par table before the match?</h2>
<p>Rain rarely waits for anyone to find a phone. Organisers and scorers who print the par sheet before the first ball can tell both captains where they stand the moment the umpires take the players off. It also settles arguments, because everyone is reading the same published ICC numbers.</p>
<p>To make one, choose the overs, enter Team 1's score at the innings break and press print: the page prints only the result and the table.</p>

<h2 id="faq">DLS par score FAQ</h2>
<?php dlscalc_faq_html( 'dls-par-score-table' ); ?>

<h2 id="sources">Sources</h2>
<?php dlscalc_sources_html(); ?>
