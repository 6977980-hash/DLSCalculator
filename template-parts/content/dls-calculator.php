<?php
/**
 * Article below the DLS Calculator. Every H2 is a question answered in its first paragraph.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlscalc_img = DLSCALC_URI . '/assets/img/dls/';
?>
<h2 id="how-to-use">How do you use this DLS calculator?</h2>
<p>Choose the format, enter Team 1's final score, then add a row for every stoppage. Each row needs the overs bowled when play stopped, the wickets down at that moment and the overs lost. Enter the overs Team 2 had at the start of its chase, and the revised target appears straight away.</p>
<ol>
	<li><strong>Match.</strong> Pick ODI, T20, T10 or custom overs. Type the overs per side as they were when the match started, not after any rain.</li>
	<li><strong>Team 1 score.</strong> Enter the runs Team 1 finished on, whether they batted all their overs, were bowled out or were stopped by rain.</li>
	<li><strong>Team 1 stoppages.</strong> Leave this empty if Team 1 batted without a break. If rain ended Team 1's innings early, add a stoppage and put every remaining over in "overs lost".</li>
	<li><strong>Team 2 overs.</strong> Enter the overs Team 2 was given when its innings began. This is often the same as Team 1's overs, or fewer if overs were lost during the break.</li>
	<li><strong>Team 2 stoppages.</strong> Add a row for each break in the chase. If the match is abandoned, put all the remaining overs in "overs lost" and the result panel switches to the par score that decides the match.</li>
</ol>
<p>Write overs the way a scorer does: 32.4 means 32 overs and 4 balls, so the number after the point is always 0 to 5. The result panel, the working and the par table update as you type, and the page address changes with your inputs, so you can share or bookmark the exact result.</p>

<h2 id="what-it-shows">What does the DLS calculator show?</h2>
<p>It shows Team 2's revised target and the overs to get it in, the par score that ties the match, and the resources each team had, called R1 and R2. Below that is every step of the working and a par table for each remaining over and wicket of the chase.</p>
<p>The <strong>target</strong> is what Team 2 must reach to win. The <strong>par to tie</strong> is one run less. <strong>R1</strong> and <strong>R2</strong> are the share of a full 50-over, 10-wicket innings that each team had, after taking away what the stoppages cost. If Team 2 cannot face the minimum overs for a result, a warning appears above the numbers: pick the format and type the overs the match started with, so a 40-over ODI still needs 20 overs for Team 2. If play cannot restart, enter the last stoppage with every remaining over lost and the par score at that moment decides the match; the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-method' ) . '#minimum-overs' ); ?>">DLS method guide</a> explains the minimum overs and abandoned matches.</p>
<p>The par table answers the question scorers get asked every time the covers come on: "where do we stand if it does not stop raining?" Find the completed overs on the left and the wickets down across the top. If play ends for good at that point, Team 2 wins when its score is above the number in the cell.</p>

<h2 id="how-target-is-worked-out">How does the calculator work out the target?</h2>
<p>It reads each team's resources from the ICC Standard Edition table, using overs left and wickets lost, and takes away the resources lost in every stoppage. If Team 2 had fewer resources than Team 1, Team 1's score is scaled down by R2 ÷ R1. The result is rounded down, then one run is added.</p>
<p>If Team 2 had more resources than Team 1, which happens when Team 1's innings was cut short, the extra resources are worth G50 runs per 100%. The par score becomes Team 1's score + G50 × (R2 − R1) ÷ 100. In both cases the target is the par score rounded down, plus one.</p>
<figure class="figure">
	<img src="<?php echo esc_url( $dlscalc_img . 'resource-curves.svg' ); ?>" width="760" height="440" loading="lazy" decoding="async" alt="Line chart of DLS resources remaining against overs left, from 0 to 50 overs, with one line each for 0, 2, 4, 6 and 8 wickets lost. With no wickets lost, 50 overs left is 100%, 20 overs is 56.6% and 10 overs is 32.1%. The lines flatten as wickets fall.">
	<figcaption>Resources remaining from the ICC Standard Edition table. The more wickets lost, the less the remaining overs are worth.</figcaption>
</figure>
<p>Rain during the chase cuts the target by the share of resources lost, not the share of overs. In a 40-over match where Team 1 makes 230 and Team 2 loses 8 overs at 15.3 overs with 2 down, Team 2 loses a fifth of its overs but the target falls by only 37 runs, from 231 to <strong>194 in 32 overs</strong>, because those overs came with eight wickets in hand. The <a href="<?php echo esc_url( dlscalc_page_url( 'dls-method' ) . '#examples' ); ?>">worked examples in the DLS method guide</a> go through cases like this step by step.</p>
<p>Resources are rounded to one decimal place after each innings, and every stoppage in the same innings is added up. That is exactly how the ICC playing conditions describe the Standard Edition, so the working on this page can be checked line by line against the table. For the background to resources and why wickets matter as much as overs, read <a href="<?php echo esc_url( dlscalc_page_url( 'dls-method' ) ); ?>">the DLS method explained</a>.</p>

<h2 id="icc-examples">Does this calculator match the ICC's own examples?</h2>
<p>Yes. The ICC playing conditions include six worked examples of the Standard Edition, and this calculator gives the same resources, par scores and targets for all six. The buttons above the calculator load four of them. "Rain during chase" is Example 3: Team 1 scores 250 in 50 overs and Team 2 loses 10 overs when it is 1 down after 12.</p>
<div class="table-wrap" tabindex="0" role="region" aria-label="ICC worked examples">
<table class="data">
	<caption>The six ICC worked examples, with G50 = 245</caption>
	<thead><tr><th scope="col">Example</th><th scope="col">What happened</th><th scope="col">ICC result</th><th scope="col">This calculator</th></tr></thead>
	<tbody>
		<tr><th scope="row">1</th><td>50 overs. Team 1 stopped at 20 overs, 3 down, 10 overs lost, ends on 180. Team 2 gets 40 overs.</td><td>R1 87.5, R2 89.3, target 185</td><td>185</td></tr>
		<tr><th scope="row">2</th><td>45 overs. Team 1 scores 212. Team 2 gets 35 overs.</td><td>R1 95.0, R2 82.7, target 185</td><td>185</td></tr>
		<tr><th scope="row">3</th><td>50 overs. Team 1 scores 250. Team 2 stopped at 12 overs, 1 down, 10 lost.</td><td>R2 86.8, target 218 in 40 overs</td><td>218</td></tr>
		<tr><th scope="row">4</th><td>As Example 3, then a stop at 22 overs, 3 down, 2 lost, and the match ends at 30.2 overs, 6 down.</td><td>R2 63.8, par 159</td><td>159</td></tr>
		<tr><th scope="row">5</th><td>50 overs. Team 1 ended at 47.1 overs, 8 down, on 226. Team 2 gets 33 overs.</td><td>R1 93.1, R2 79.8, target 194</td><td>194</td></tr>
		<tr><th scope="row">6</th><td>As Example 5, plus a Team 2 stop at 25 overs, 2 down, 5 lost.</td><td>R2 64.7, target 158</td><td>158</td></tr>
	</tbody>
</table>
</div>
<p>Each example is part of the test suite that runs before any change to this site goes live, in both the browser code and the server code that prints the first result on the page.</p>

<h2 id="worked-example-first-innings">Why can the target be higher than Team 1's score?</h2>
<p>When Team 1 loses overs in the middle of its innings, it batted those early overs without knowing the innings would be shorter. If Team 2 then gets the same reduced overs from the start, Team 2 has more resources than Team 1, so the target is raised above Team 1's score to make up for it.</p>
<p>This is ICC Example 1. In a 50-over match, Team 1 is 3 down after 20 overs when 10 overs are lost. It finishes on 180 from 40 overs:</p>
<ul>
	<li>R1 = 100 − (61.6 − 49.1) = 87.5%. Team 2 starts with 40 overs and 10 wickets, so R2 = 89.3%.</li>
	<li>R2 is more than R1, so par = 180 + 245 × (89.3 − 87.5) ÷ 100 = 184.41, rounded down to 184.</li>
	<li>The target is <strong>185 in 40 overs</strong>, five more than Team 1 scored.</li>
</ul>

<h2 id="g50">What is G50 and which value should you use?</h2>
<p>G50 is the average score expected in a full 50-over first innings. It is only used when Team 2 has more resources than Team 1. The ICC sets 245 for matches involving full-member nations and between first-class teams, and 200 for women's internationals, Under-19 and Under-15 internationals and matches between associate members, but competitions can choose their own value.</p>
<p>The ECB's 2026 methodology lists 245 for Under-19 internationals, so the right value for a youth match depends on who runs it. For club and school cricket, a league can set G50 to a typical first-innings score at its level and use the custom option here. When Team 2 has fewer resources than Team 1, as in most chases cut short by rain, G50 does not change the target at all.</p>

<h2 id="standard-vs-professional">Why can an official target differ from this calculator?</h2>
<p>International matches and the major T20 leagues use the DLS Professional Edition, which runs on ICC software and adjusts for high-scoring matches. Its formulas are not published. This calculator uses the Standard Edition, the published table the ICC names as the method when the software is not available.</p>
<p>For most matches the two editions agree or are within a few runs. The gap grows in very high-scoring games, where the Professional Edition assumes a different pace of scoring. Local leagues, school cricket and tournaments without the ICC software usually use the Standard Edition, since it needs nothing but the table, and this page is built for exactly that. Check your competition's playing conditions for the edition it names.</p>

<h2 id="faq">DLS calculator FAQ</h2>
<?php dlscalc_faq_html( 'dls-calculator' ); ?>

<h2 id="sources">Sources</h2>
<p>The resource table, formulas and worked examples come from the ICC playing conditions. G50 values are cross-checked against the ECB's published methodology.</p>
<?php dlscalc_sources_html(); ?>
