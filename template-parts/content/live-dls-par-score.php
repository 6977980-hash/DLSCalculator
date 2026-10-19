<?php
/**
 * Article below the live DLS par tracker.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 id="what-is-live-par">What is a live DLS par score?</h2>
<p>It is the score the chasing side needs right now to be level with Team 1 if rain ends the match at this ball. Above par, Team 2 is ahead and would win on DLS. Below par, Team 1 is ahead. Exactly on par, the match would be tied. The par changes after every ball and every wicket, which is why captains ask for it the moment dark clouds appear.</p>
<p>Broadcasters show it on screen in international matches. In club and league cricket nobody does, so this page gives scorers the same number from the published ICC table.</p>

<h2 id="how-to-use">How do you track the DLS par during a match?</h2>
<ol>
	<li>Before the chase starts, enter the overs per side, Team 1's final score and, if overs were lost at the break, Team 2's reduced overs. The target appears straight away.</li>
	<li>After each over, press "Add next over". The next over number and the last score are filled in, so you only change the runs and the wickets.</li>
	<li>Read the "Who is ahead" column. Green means Team 2 is ahead of par, red means Team 1 is ahead.</li>
	<li>If rain arrives in the middle of an over, change the overs to the exact ball, like 12.3, and the par is worked out for that point.</li>
</ol>
<p>The tracker keeps the match on your phone, so a reload or a dropped signal loses nothing. Share the link with the other dressing room or the umpires and they see the same numbers.</p>

<h2 id="t20-example">How does the par move in a T20 chase?</h2>
<p>Take a 20-over match where Team 1 makes 160, so Team 2 needs 161. This is the example already loaded above.</p>
<div class="table-wrap" tabindex="0" role="region" aria-label="T20 live par example">
<table class="data data--num">
	<caption>Chasing 161 in 20 overs</caption>
	<thead><tr><th scope="col">Overs</th><th scope="col">Score</th><th scope="col">DLS par</th><th scope="col">Who is ahead</th></tr></thead>
	<tbody>
		<tr><th scope="row">2</th><td>15/0</td><td>12</td><td>Team 2 by 3</td></tr>
		<tr><th scope="row">3</th><td>21/1</td><td>22</td><td>Team 1 by 1</td></tr>
		<tr><th scope="row">5</th><td>41/1</td><td>35</td><td>Team 2 by 6</td></tr>
		<tr><th scope="row">6</th><td>49/2</td><td>45</td><td>Team 2 by 4</td></tr>
	</tbody>
</table>
</div>
<p>Look at the third over. Team 2 scored 6 runs, but the par rose by 10, from 12 to 22: 6 for the over and 4 more for the first wicket, so Team 1 went ahead. Two good overs later Team 2 was 6 runs clear. That swing is exactly what a captain needs to know before deciding whether to attack or protect wickets with rain coming.</p>
<p>Under ICC rules a T20 needs 5 overs in the chase for a result, so until then the tracker warns that a stoppage would mean no result.</p>

<h2 id="odi-example">How does the par move in an ODI chase?</h2>
<p>In a 50-over match where Team 1 makes 250, the target is 251. Team 2 on 95 for 2 after 20 overs is 14 runs ahead of a par of 81. Five overs later on 118 for 4, it is 7 runs behind a par of 125: two wickets cost it more than 20 runs of par. By 30 overs, on 140 for 4, it is back in front of a par of 138.</p>
<p>ODI pars move further from the required run rate than T20 pars, because a side with wickets in hand has so many overs left to use them. A team can be behind the required rate and still well ahead on DLS. An ODI chase needs 20 overs for a result.</p>

<h2 id="why-wickets">Why does a wicket change the par so much?</h2>
<p>DLS treats overs and wickets together as resources. Losing a wicket leaves fewer resources for the rest of the chase, so the method judges that Team 2 has already used more of them and should have more runs on the board. In a T20 chase of 161, the first wicket adds about 3 or 4 runs to the par in the early overs, while the seventh or eighth adds 20 or more.</p>
<p>The full table behind these numbers, and why each wicket matters, is explained in the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-method' ) ); ?>">DLS method guide</a>. For a printable sheet of every over and wicket before the match, use the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-par-score-table' ) ); ?>">DLS par score table</a>.</p>

<h2 id="interruptions">What if rain has already interrupted the match?</h2>
<p>If overs were lost before Team 2 started, enter its reduced overs in the match section. In the same 20-over match, a chase cut to 15 overs has a target of 128, and the par after 5 overs at 1 down is 38 instead of 35.</p>
<p>If rain stops the chase and play then resumes with fewer overs, the target itself changes. Work out the new target in the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-calculator' ) ); ?>">DLS calculator</a>, which also gives the par table for the overs left. The tracker here covers a chase with no interruption once it has started, which is when most of the "are we ahead?" questions come.</p>

<h2 id="faq">Live DLS par score FAQ</h2>
<?php dlscalc_faq_html( 'live-dls-par-score' ); ?>

<h2 id="sources">Sources</h2>
<?php dlscalc_sources_html(); ?>
