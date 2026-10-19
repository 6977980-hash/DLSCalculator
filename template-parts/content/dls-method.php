<?php
/**
 * DLS method guide (/dls-method/). Owns "dls method", "how dls method works", "dls method formula",
 * "dls full form", "dls resource table", "duckworth lewis table" and "d/l vs dls". The calculator
 * pages own the "calculator" terms and link here for the method. Table values come from the engine.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlscalc_calc = dlscalc_page_url( 'dls-calculator' );
$dlscalc_img  = DLSCALC_URI . '/assets/img/dls/';

/**
 * One row of the resource table: overs left, then resources for 0 to 9 wickets lost.
 *
 * @param int $overs Overs left.
 */
$dlscalc_row = function ( $overs ) {
	echo '<tr><th scope="row">' . (int) $overs . '</th>';
	for ( $w = 0; $w < 10; $w++ ) {
		echo '<td>' . esc_html( number_format( dlscalc_dls_res( $overs * 6, $w ), 1 ) ) . '</td>';
	}
	echo '</tr>';
};
$dlscalc_head = '<thead><tr><th scope="col">Overs left</th>';
for ( $dlscalc_w = 0; $dlscalc_w < 10; $dlscalc_w++ ) {
	$dlscalc_head .= '<th scope="col">' . $dlscalc_w . '</th>';
}
$dlscalc_head .= '</tr></thead>';
?>
<h2 id="full-form">What is the full form of DLS in cricket?</h2>
<p>DLS stands for <strong>Duckworth-Lewis-Stern</strong>, named after the three statisticians behind it. Frank Duckworth and Tony Lewis created the Duckworth-Lewis (D/L) method in the 1990s, and Professor Steven Stern later updated it for modern scoring. It has been called Duckworth-Lewis-Stern since 2014.</p>
<p>The method was a response to the rain rules that came before it. The best-known failure was the <a href="<?php echo esc_url( dlscalc_page_url( '1992-world-cup-semi-final-england-south-africa' ) ); ?>">1992 World Cup semi-final</a> in Sydney: South Africa needed 22 runs from 13 balls when rain came, and the "most productive overs" rule then in use left them needing 21 from one ball. Simply scaling the target by run rate was no better, because it ignored wickets. Duckworth and Lewis set out to build a rule that treated both teams fairly.</p>
<p>D/L was first used in international cricket on 1 January 1997, and the ICC formally adopted it in 1999. A <em>Professional Edition</em> has been used in all international one-day matches since early 2004. When Stern took over the research, the formula was tuned so that targets in high-scoring modern matches, especially big totals and T20s, better match how teams actually bat. Today the same DLS method sets targets in every limited-overs format, from 50-over internationals to T20 leagues and local matches, through one of its two editions (see <a href="#editions">Standard and Professional Edition</a>).</p>

<h2 id="resources">How does the DLS method work?</h2>
<p>DLS treats a team's batting as two <strong>resources</strong>: the overs it has left and the wickets it has in hand. A full 50-over innings with all 10 wickets is 100% of resources. When rain takes overs away, a team loses part of its resources, and its target is scaled to the resources it actually had.</p>
<p>The key idea is that overs and wickets work together. Ten overs left with no wickets lost are worth 32.1%, because a team with all its wickets can attack. Ten overs left with eight wickets down are worth only 11.4%, because the tail cannot bat as freely. That is why losing the same overs costs very different amounts at different moments in an innings.</p>
<ul>
	<li><strong>Overs matter less early on.</strong> Taking 10 overs from the start of a 50-over innings costs 10.7% (100 down to 89.3), not 20%, because a team with fewer overs simply bats faster.</li>
	<li><strong>Wickets matter more as overs run out.</strong> With 20 overs left, a team is worth 56.6% with no wickets down and 30.8% with six down.</li>
	<li><strong>A T20 is a short ODI.</strong> A full 20-over innings starts at 56.6% of resources, the value of 20 overs left with no wickets down. That is the same table the ICC uses for 50-over cricket.</li>
</ul>
<figure class="figure">
	<img src="<?php echo esc_url( $dlscalc_img . 'resource-curves.svg' ); ?>" width="760" height="440" loading="lazy" decoding="async" alt="Line chart of DLS resources remaining against overs left, from 0 to 50 overs, with one line each for 0, 2, 4, 6 and 8 wickets lost. With no wickets lost, 50 overs left is 100%, 20 overs is 56.6% and 10 overs is 32.1%. The lines flatten as wickets fall.">
	<figcaption>Resources remaining in the ICC Standard Edition table. The more wickets lost, the less the remaining overs are worth.</figcaption>
</figure>

<h2 id="formula">What is the DLS formula?</h2>
<p>DLS compares the resources each team had. R1 is the resources Team 1 had, R2 the resources Team 2 had, and S is Team 1's score. The target depends on which team had more.</p>
<p class="formula">If R2 ≤ R1: par score = S × R2 ÷ R1</p>
<p class="formula">If R2 &gt; R1: par score = S + G50 × (R2 − R1) ÷ 100</p>
<p>The target is the par score rounded down, plus one run. If Team 2 finishes exactly on the par score, the match is tied.</p>
<p>The first formula is used most of the time: rain hits the chase, Team 2 gets fewer resources, and Team 1's score is scaled down. The second formula applies when Team 1's own innings was cut short, so Team 2 has more resources. Scaling up would reward a big score too much, so instead each extra 1% of resources is worth a fixed number of runs, based on <strong>G50</strong>, the average score in a 50-over innings. The ICC sets G50 at 245 for matches involving full-member nations and between first-class teams, and 200 for women's internationals, Under-19 and Under-15 internationals and matches between associate members; the ECB's 2026 methodology lists 245 for Under-19 internationals, so check your competition's rules.</p>
<p>To find a team's resources after a stoppage, read the table at the moment play stopped, then read it again for the overs left after the restart, with the same wickets down. The difference is the resources lost. Add up the losses for every stoppage in an innings and take them away from the resources the innings started with.</p>

<h2 id="resource-table">DLS resource table (Standard Edition)</h2>
<p>This is the percentage of resources remaining for each number of overs left and wickets lost, from the ICC Standard Edition table. The ICC publishes it ball by ball; the rows below are every whole over from 50 down to 1. Columns are wickets lost.</p>
<div class="table-wrap par-wrap" tabindex="0" role="region" aria-label="DLS resource table, 50 overs">
<table class="data data--num">
	<caption>DLS resources remaining (%) by overs left and wickets lost, 50-over table</caption>
	<?php echo $dlscalc_head; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed markup. ?>
	<tbody>
		<?php
		for ( $dlscalc_o = 50; $dlscalc_o >= 1; $dlscalc_o-- ) {
			$dlscalc_row( $dlscalc_o );
		}
		?>
	</tbody>
</table>
</div>
<h3 id="t20-table">DLS table for T20 matches</h3>
<p>A T20 uses the bottom of the same table. The innings starts at 20 overs left, worth 56.6%, so a T20 team losing four overs at the start of its chase drops to 47.6% (16 overs left, no wickets down) and its target is scaled by that change.</p>
<div class="table-wrap" tabindex="0" role="region" aria-label="DLS resource table, 20 overs">
<table class="data data--num">
	<caption>DLS resources remaining (%) for a 20-over innings</caption>
	<?php echo $dlscalc_head; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed markup. ?>
	<tbody>
		<?php
		for ( $dlscalc_o = 20; $dlscalc_o >= 1; $dlscalc_o-- ) {
			$dlscalc_row( $dlscalc_o );
		}
		?>
	</tbody>
</table>
</div>
<h3 id="resource-lookup">Look up the resources for any ball</h3>
<p>Need a value part-way through an over, like 32.4 overs left? Enter the overs left and the wickets lost to read the full ball-by-ball table.</p>
<?php dlscalc_resource_lookup_html(); ?>
<p>The <a href="<?php echo esc_url( $dlscalc_calc ); ?>">DLS calculator</a> reads the same table and shows every value it looks up. For par scores over by over, see the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-par-score-table' ) ); ?>">DLS par score table</a>.</p>

<h2 id="examples">How is a DLS target calculated? Worked examples</h2>
<p>These examples come from the ICC playing conditions. Each one shows a different part of the method, and the DLS calculator gives the same answers for all of them.</p>
<h3>Rain during Team 2's innings</h3>
<p>Team 1 scores 250 in 50 overs. Team 2 is 40 for 1 after 12 overs when 10 overs are lost. With 38 overs left and 1 wicket down, Team 2 had 82.0% of its resources left; after the delay, with 28 overs left, it has 68.8%. The stoppage cost 13.2%, so R2 = 100 − 13.2 = 86.8%.</p>
<p>R2 is less than R1 (100%), so the par score is 250 × 86.8 ÷ 100 = 217, and the <strong>target is 218 in 40 overs</strong>.</p>
<h3>Rain before Team 2's innings</h3>
<p>A 45-over match. Team 1 bats its full 45 overs and makes 212, using 95.0% of resources. Rain then cuts Team 2's innings to 35 overs, worth 82.7%. The par score is 212 × 82.7 ÷ 95.0 = 184.5, rounded down to 184, so the <strong>target is 185 in 35 overs</strong>.</p>
<h3>Rain during Team 1's innings</h3>
<p>Team 1 is 79 for 3 after 20 overs of a 50-over match when 20 overs are lost, 10 from each side. With 30 overs left and 3 down, Team 1 had 61.6%; with 20 left it has 49.1%. It loses 12.5%, so R1 = 87.5%, and it finishes on 180 in 40 overs. Team 2 starts with 40 overs: R2 = 89.3%.</p>
<p>Team 1 had its innings cut while batting, so Team 2 has more resources. Using G50 = 245, the par score is 180 + 245 × (89.3 − 87.5) ÷ 100 = 184.4, rounded down to 184, and the <strong>target is 185</strong>, five more than Team 1 scored.</p>
<h3>Match abandoned during the chase</h3>
<p>Go back to the first example. Two more breaks leave Team 2 at 154 for 6 with 7.4 overs of its innings left when play is abandoned. Its resources have fallen to 63.8%, so the par score is 250 × 63.8 ÷ 100 = 159. Team 2 is 5 runs short, so <strong>Team 1 wins by 5 runs</strong>.</p>
<p>The same steps decided famous matches. The <a href="<?php echo esc_url( dlscalc_page_url( 'ipl-2023-final-csk-gt' ) ); ?>">IPL 2023 final</a> was cut before the chase began, like the second example. In <a href="<?php echo esc_url( dlscalc_page_url( 't20-world-cup-2024-afghanistan-bangladesh' ) ); ?>">Afghanistan vs Bangladesh at the 2024 T20 World Cup</a>, an over was lost during the chase, like the first. <a href="<?php echo esc_url( dlscalc_page_url( 't20-world-cup-2022-england-ireland' ) ); ?>">England vs Ireland at the 2022 T20 World Cup</a> was abandoned during the chase and settled on the par score, like the last.</p>

<h2 id="minimum-overs">What is the minimum number of overs for a DLS result?</h2>
<p>For a match to have a result, Team 2 must be able to face at least <strong>20 overs in a 50-over match</strong> and <strong>5 overs in a 20-over match</strong>, unless the result is already decided. Other formats, such as T10 or local 8 and 12-over matches, set their own minimum in their playing conditions.</p>
<p>Some knockout matches ask for more. At the ICC Men's T20 World Cup, the semi-finals and the final need at least 10 overs a side for a result, double the usual T20 minimum. Always check the playing conditions of the event.</p>
<p>If play is abandoned after the minimum has been reached, the par score at that moment decides the match. Team 2 wins if its score is above par, loses if it is below, and the match is tied if it is exactly on par. If the minimum has not been reached, the match is a no result, unless Team 2 has already passed the target or been bowled out. A result decided on par counts in the points table too, and the <a href="<?php echo esc_url( dlscalc_page_url( 'net-run-rate-calculator' ) ); ?>">net run rate calculator</a> shows how to enter it.</p>
<p>The match officials decide how many overs each side gets after a stoppage, using the time lost and the playing conditions. DLS only sets the target for the overs they decide.</p>

<h2 id="dl-vs-dls">What is the difference between the D/L and DLS methods?</h2>
<p>The idea is the same: both use resources made of overs and wickets, and the same two formulas. The difference is in the numbers. Stern's update changed how resources are valued in high-scoring innings, because modern teams, especially in T20s, score faster at the end of an innings than the original D/L data assumed.</p>
<div class="table-wrap" tabindex="0" role="region" aria-label="D/L and DLS compared">
<table class="data">
	<caption>Duckworth-Lewis and Duckworth-Lewis-Stern compared</caption>
	<thead><tr><th scope="col"></th><th scope="col">Duckworth-Lewis (D/L)</th><th scope="col">Duckworth-Lewis-Stern (DLS)</th></tr></thead>
	<tbody>
		<tr><th scope="row">In use</th><td>1997 to 2014</td><td>Since 2014</td></tr>
		<tr><th scope="row">Built by</th><td>Frank Duckworth and Tony Lewis</td><td>Updated by Steven Stern</td></tr>
		<tr><th scope="row">Resources</th><td>Overs left and wickets in hand</td><td>Overs left and wickets in hand</td></tr>
		<tr><th scope="row">Scoring data</th><td>Based on older, lower-scoring matches</td><td>Retuned for modern scoring rates and big totals</td></tr>
		<tr><th scope="row">Editions</th><td>Standard from 1997, Professional from 2004</td><td>Standard and Professional</td></tr>
	</tbody>
</table>
</div>
<p>In everyday use the two give very similar targets in low and medium-scoring matches. The gap shows in high-scoring games, where the older method could leave a chasing side a target that was too easy or too hard.</p>

<h2 id="editions">What is the difference between the Standard and Professional Edition?</h2>
<p>The <strong>Standard Edition</strong> uses the published resource table and the formulas above, so anyone can work out a target with a pencil. The <strong>Professional Edition</strong> runs on ICC software, adjusts the resources for how high the scoring is in that match, and is not published. International matches and major leagues use the Professional Edition. The ICC names the Standard Edition only as the fallback when the software is not available, but because it needs no software it is what most local leagues and schools can use; their own playing conditions say which edition applies.</p>
<p>The two usually agree or are within a few runs. They drift apart mainly when Team 1 makes a very large score. If you are checking an international target, expect the official figure to differ slightly from a Standard Edition calculation.</p>

<h2 id="criticism">Is the DLS method fair?</h2>
<p>DLS is far fairer than the rules it replaced, such as average run rate and most productive overs, because it counts wickets as well as overs. But no rain rule is perfect, and three criticisms come up often.</p>
<ul>
	<li><strong>It can feel unfair to the side batting when rain arrives.</strong> A team that loses wickets just before a stoppage sees its position fall sharply, even if it was scoring quickly.</li>
	<li><strong>T20 chases are hard to model.</strong> In a short chase, teams with wickets in hand attack from the first ball, and critics argue the method can favour the chasing side in some situations.</li>
	<li><strong>The Professional Edition is a black box.</strong> Because it is not published, players and fans cannot check international targets themselves.</li>
</ul>
<p>Alternatives have been proposed, most notably the <a href="<?php echo esc_url( dlscalc_page_url( 'vjd-method' ) ); ?>">VJD method</a> by V. Jayadevan, which India's BCCI used in domestic cricket. The ICC has kept DLS for international cricket.</p>

<h2 id="faq">DLS method FAQ</h2>
<?php dlscalc_faq_html( 'dls-method' ); ?>

<h2 id="sources">Sources</h2>
<p>The resource table, formulas and worked examples come from the ICC playing conditions. G50 values are cross-checked against the ECB's published methodology.</p>
<?php dlscalc_sources_html(); ?>
