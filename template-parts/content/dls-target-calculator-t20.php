<?php
/**
 * Article below the T20 calculator, with the common-targets table rendered from the engine.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlscalc_img    = DLSCALC_URI . '/assets/img/dls/';
$dlscalc_scores = array( 120, 140, 160, 180, 200, 220 );
$dlscalc_overs  = array( 5, 6, 8, 10, 12, 14, 15, 16, 18 );
?>
<h2 id="how-to-use">How do you use the T20 DLS target calculator?</h2>
<p>Enter the overs per side and Team 1's score, then move the slider to the number of overs lost before Team 2 starts. The revised target and the required run rate change as you slide. If rain also stops the chase, add a stoppage with the overs bowled, wickets down and overs lost.</p>
<p>This page assumes Team 1 batted its full overs, which is how most rain-hit T20s play out: the weather arrives during the break or the chase. If Team 1's innings was interrupted, use the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-calculator' ) ); ?>">full DLS calculator</a>, which handles stoppages in both innings. Overs are written as a scorer writes them, so 8.3 means 8 overs and 3 balls.</p>
<p>Every result has a link of its own. The page address updates as you change the inputs, so the WhatsApp button and the copy link button share the exact target, and opening the link shows the same numbers and par table.</p>

<h2 id="how-t20-target-is-calculated">How is a T20 DLS target calculated?</h2>
<p>A full 20-over innings with 10 wickets is worth 56.6% of the resources of a 50-over innings. When Team 2 loses overs, its share falls, and Team 1's score is scaled by the same ratio. The result is rounded down and one run is added to give the target.</p>
<p>For a chase cut to 15 overs before it starts: 15 overs with no wickets lost are worth 45.2%. Team 1 scored 180 with 56.6%, so the par score is 180 × 45.2 ÷ 56.6 = 143.75, rounded down to 143. The target is <strong>144 in 15 overs</strong>, a required rate of 9.60 an over against 9.05 for the full chase.</p>
<p>The required rate goes up because Team 2 has the same 10 wickets for fewer overs, so it can take more risks. That is the core idea of DLS: wickets and overs together are the resources, and a team with wickets in hand is expected to score faster. The <a href="<?php echo esc_url( dlscalc_page_url( 'dls-method' ) ); ?>">DLS method guide</a> explains the idea in full.</p>

<h2 id="common-targets">What are common T20 DLS targets?</h2>
<p>The table below gives the revised target for common first-innings scores when the chase is shortened before it starts and no more overs are lost. A 160 chase cut to 10 overs needs 91, cut to 12 overs it needs 107, and cut to 15 overs it needs 128. Each figure comes from the same calculation as the calculator.</p>
<div class="table-wrap" tabindex="0" role="region" aria-label="Common T20 DLS targets">
<table class="data data--num">
	<caption>Team 2 target by Team 1 score (rows) and overs for the chase (columns), 20-over match</caption>
	<thead><tr><th scope="col">Score</th>
	<?php foreach ( $dlscalc_overs as $dlscalc_o ) : ?>
		<th scope="col"><?php echo (int) $dlscalc_o; ?> ov</th>
	<?php endforeach; ?>
	</tr></thead>
	<tbody>
	<?php foreach ( $dlscalc_scores as $dlscalc_s ) : ?>
		<tr><th scope="row"><?php echo (int) $dlscalc_s; ?></th>
		<?php foreach ( $dlscalc_overs as $dlscalc_o ) : ?>
			<?php $dlscalc_t = dlscalc_dls_t20( array( 'n' => '20', 'r' => (string) $dlscalc_s, 'l' => (string) ( 20 - $dlscalc_o ) ) ); ?>
			<td><?php echo (int) $dlscalc_t['target']; ?></td>
		<?php endforeach; ?>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
</div>
<p>For any score or overs that are not in the table, type them into the calculator above. For a full par sheet that you can print before a match, open the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-par-score-table' ) ); ?>">DLS par score table</a>.</p>

<h2 id="rain-during-chase">What happens if rain stops a T20 chase?</h2>
<p>The resources Team 2 loses depend on how many overs were left and how many wickets had fallen when play stopped. The calculator takes those lost resources away from Team 2's total and scales Team 1's score again. Losing overs with wickets in hand lowers the target more than losing them late with wickets down.</p>
<p>Say Team 1 makes 180 in 20 overs. Team 2 is 2 down after 10 overs when rain stops play, and 5 overs are lost:</p>
<ul>
	<li>10 overs were left with 2 down, worth 30.8%. After the break, 5 overs are left, worth 16.8%. Team 2 lost 14.0%.</li>
	<li>R2 = 56.6 − 14.0 = 42.6%.</li>
	<li>Par = 180 × 42.6 ÷ 56.6 = 135.48, rounded down to 135. The new target is <strong>136 in 15 overs</strong>.</li>
</ul>
<p>Team 2 needs whatever it has not yet scored of those 136 runs from the 5 overs that are left. The par table on this page then shows where Team 2 stands after each of those overs, in case it rains again.</p>

<h2 id="abandoned">Who wins if a T20 is abandoned in the chase?</h2>
<p>If Team 2 has faced at least 5 overs, the match goes to the DLS par score at the moment play ended. Team 2 wins with more runs than the par, loses with fewer and ties on the par exactly. If Team 2 has faced fewer than 5 overs, there is no result.</p>
<p>Team 1 makes 160 and rain ends the match with Team 2 at 12 overs, 3 down. The 8 overs left with 3 down were worth 24.8%, so Team 2 used 56.6 − 24.8 = 31.8%. The par is 160 × 31.8 ÷ 56.6 = 89.89, rounded down to 89. On 90 or more Team 2 wins. To get this in the calculator, add a stoppage at 12 overs, 3 down, with 8 overs lost.</p>
<figure class="figure">
	<img src="<?php echo esc_url( $dlscalc_img . 't20-par-160.svg' ); ?>" width="760" height="440" loading="lazy" decoding="async" alt="Line chart of the DLS par score in a 20-over chase of 160, after each over from 1 to 19, with lines for 0, 3 and 6 wickets down. After 10 overs the par is 69 with no wickets down, 75 with 3 down and 95 with 6 down.">
	<figcaption>Par score through a 20-over chase of 160. Each wicket that falls pushes the par up.</figcaption>
</figure>

<h2 id="t10-and-short">Can you use this for T10 and shorter matches?</h2>
<p>Yes. Set the overs per side anywhere from 5 to 20, and the slider and par table adjust. A 10-over innings is worth 32.1% of a 50-over innings, so in a T10 where Team 1 scores 120 and the chase is cut to 7 overs, the target is 88 in 7 overs.</p>
<p>The DLS tables are the same for every format. Only the starting resources change: 5 overs are worth 17.2%, 6 overs 20.3%, 8 overs 26.4% and 10 overs 32.1%. The minimum overs for a result in T10 and other short formats are set by each league, so check its playing conditions before relying on a result.</p>

<h2 id="wickets-in-t20">Do wickets matter less in a T20 than in an ODI?</h2>
<p>Early in a short chase, a wicket costs fewer resources than in an ODI, because there are fewer overs left for the lost batter to use. Late in a chase, or after several wickets in a row, the cost rises steeply, which is why the right-hand columns of the par table climb so fast.</p>
<p>In the 160 chase above, the par after 10 overs is 69 with no wickets down but 95 with 6 down. A team that is 80 for 6 after 10 overs is behind, while a team on 80 for 1 is well ahead. That swing is the main reason captains in rain-threatened matches look at the par table, not just the run rate.</p>

<h2 id="leagues">Do the IPL, PSL and T20 World Cup use this method?</h2>
<p>All three use Duckworth-Lewis-Stern, through the Professional Edition software operated by the match officials. This page uses the published Standard Edition, the ICC's official method when the software is not available, so it is the right tool for club, school and tournament T20s and a close guide for televised matches.</p>
<p>For the big leagues, expect the official target to match or be within a run or two of this calculator for typical scores. The editions differ most in very high-scoring matches. Two televised examples where this calculator gives the official figure exactly: the <a href="<?php echo esc_url( dlscalc_page_url( 'ipl-2023-final-csk-gt' ) ); ?>">IPL 2023 final</a>, where Chennai chased 171 in 15 overs, and <a href="<?php echo esc_url( dlscalc_page_url( 't20-world-cup-2022-england-ireland' ) ); ?>">England vs Ireland at the 2022 T20 World Cup</a>, decided on a par of 110.</p>
<p>A rain-hit result also counts in the points table. Team 1 is credited with the revised target minus one run off the overs Team 2 was given, and the <a href="<?php echo esc_url( dlscalc_page_url( 'net-run-rate-calculator' ) ); ?>">net run rate calculator</a> explains how to enter it.</p>

<h2 id="faq">T20 DLS FAQ</h2>
<?php dlscalc_faq_html( 'dls-target-calculator-t20' ); ?>

<h2 id="sources">Sources</h2>
<?php dlscalc_sources_html(); ?>
