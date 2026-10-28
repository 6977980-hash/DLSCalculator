<?php
/**
 * VJD method guide (/vjd-method/). Owns "vjd method", "vjd method in cricket", "vjd vs dls" and
 * "jayadevan method". No VJD calculator: the only published VJD table is the 2002 version, and the
 * BCCI's current version is not published. Facts were checked against the sources listed below.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 id="what-is-vjd">What is the VJD method in cricket, and what is its full form?</h2>
<p>VJD stands for <strong>V. Jayadevan</strong>, the name of its inventor, and the method is also called the Jayadevan system. It is a rain rule: when overs are lost in a limited-overs match, it sets a revised target for the chasing team, or decides the winner if the match cannot be finished. It does the same job as DLS but uses a different model of how teams score.</p>
<p>Jayadevan is a civil engineer from Thrissur in Kerala. He began working on a better rain rule in the early 1990s, after the rules then in use produced some notoriously unfair targets, and he published the full method in the Indian journal <em>Current Science</em> in 2002. He later taught as a visiting professor of civil engineering in Thrissur.</p>

<h2 id="how-vjd-works">How does the VJD method work?</h2>
<p>VJD is built on two curves that show what share of its runs a team scores by each point of its innings:</p>
<ul>
	<li><strong>The normal curve</strong> describes an uninterrupted innings, over by over. It is drawn from close, completed one-day matches, split into the phases of a real innings: settling in, the fielding restrictions, consolidation, acceleration and the final slog. It also has a "normal" fall of wickets, so a team that has lost more wickets than normal at a given point is judged to have used more of its innings.</li>
	<li><strong>The target curve</strong> describes a shortened innings. Jayadevan's idea is that a team that knows it has fewer overs will play its most productive phases, so the same share of overs is worth a bigger share of runs than on the normal curve. The target curve does not depend on wickets.</li>
</ul>
<p>The two rules of use are simple. For the overs already played before an interruption, read the normal curve for the wickets that have fallen. For the overs still to come after the interruption, read the target curve. The published target table gives both values for every 1% of the overs.</p>

<h2 id="example">VJD target example</h2>
<p>Jayadevan's paper gives this example. Team 1 scores 250 in 50 overs, and rain cuts Team 2's innings to 40 overs, which is 80% of Team 1's overs. The target table gives 87.6% for 80% of overs, so Team 2's target is set from 87.6% of 250, about 219 runs.</p>
<p>Under DLS, the same 40-over chase is worth 89.3% of a full innings, so the par score is 223 and the target 224. The two methods are close, but VJD asks for slightly fewer runs in this case.</p>
<p>The paper's second example is an abandoned match. Team 2 is 165 for 6 after 30 of its 50 overs, chasing 250, when play stops for good. The table gives 70% for 60% of overs with 6 wickets down, so the par score is 70% of 250 = 175, and Team 1 wins. DLS gives a par of 173 at the same point (6 down with 20 overs left is 30.8%, so Team 2 has used 69.2%), and Team 1 wins under both methods.</p>

<h2 id="vjd-vs-dls">What is the difference between VJD and DLS?</h2>
<div class="table-wrap" tabindex="0" role="region" aria-label="VJD and DLS compared">
<table class="data">
	<caption>VJD and DLS compared</caption>
	<thead><tr><th scope="col"><span class="screen-reader-text">Compared</span></th><th scope="col">VJD (Jayadevan system)</th><th scope="col">DLS (Duckworth-Lewis-Stern)</th></tr></thead>
	<tbody>
		<tr><th scope="row">Created by</th><td>V. Jayadevan, India</td><td>Frank Duckworth, Tony Lewis and Steven Stern</td></tr>
		<tr><th scope="row">Used by</th><td>BCCI domestic cricket in India</td><td>ICC for all international cricket, and most leagues</td></tr>
		<tr><th scope="row">Model</th><td>Two curves: a normal curve and a target curve</td><td>One resource table for both innings</td></tr>
		<tr><th scope="row">Wickets</th><td>Count for the overs already played</td><td>Count for both the overs played and the overs left</td></tr>
		<tr><th scope="row">Innings phases</th><td>Built in: powerplay, consolidation and slog overs</td><td>A smooth curve of resources</td></tr>
		<tr><th scope="row">Published</th><td>2002 table published; current version not published in full</td><td>Standard Edition table published; Professional Edition is ICC software</td></tr>
	</tbody>
</table>
</div>
<p>The biggest difference is how the two methods think about the chase after rain. DLS assumes a team's remaining overs and wickets are worth a fixed share of its resources, wherever they fall. VJD assumes a team with a shortened innings will reorganise and bat as if it were in its highest-scoring phases. Supporters of VJD say this matches how teams really play after an interruption. Supporters of DLS point out that wickets in hand still matter in a short chase, and VJD's target curve ignores them.</p>
<p>The older Duckworth-Lewis (D/L) method was the one VJD was first compared with. The <a href="<?php echo esc_url( dlscalc_page_url( 'dls-method' ) ); ?>">DLS method guide</a> explains how D/L became DLS in 2014 and how resources work.</p>

<h2 id="where-used">Where is the VJD method used?</h2>
<p>The BCCI has used VJD in Indian domestic cricket since 2007, after Sunil Gavaskar recommended it. Match reports show it still deciding domestic matches: the 2021-22 Vijay Hazare Trophy final and a Vijay Hazare Trophy quarter-final in January 2026.</p>
<p>International matches use DLS under the ICC playing conditions, and the IPL uses DLS under its own playing conditions. Reports in 2010 said VJD would be used in the IPL, but that never happened.</p>

<h2 id="famous-matches">Which matches were decided by the VJD method?</h2>
<ul>
	<li><strong>Vijay Hazare Trophy final, December 2021:</strong> Tamil Nadu made 314. Himachal Pradesh were 299 for 4 after 47.3 overs when bad light stopped play, and won by 11 runs on VJD, their first title.</li>
	<li><strong>Vijay Hazare Trophy quarter-final, January 2026:</strong> Uttar Pradesh made 310 for 8. Saurashtra were 238 for 3 after 40.1 overs when rain stopped play, and won by 17 runs on VJD.</li>
</ul>

<h2 id="icc-decision">Why doesn't the ICC use the VJD method?</h2>
<p>The ICC Cricket Committee reviewed VJD in detail at its meeting at Lord's in May 2012. It unanimously found no significant flaws in the Duckworth-Lewis method and did not believe VJD offered any improvement, so it kept D/L, which later became DLS. Jayadevan said he was not invited to present his method, and Gavaskar argued that VJD deserved at least a trial in international cricket.</p>
<p>Independent comparisons since then have not found that either method is clearly better overall. Each gives sensible targets in most matches, and each has situations where its targets look odd.</p>

<h2 id="vjd-calculator">Is there a VJD calculator?</h2>
<p>Not on this site, on purpose. The only complete VJD table that has been published is the one in Jayadevan's 2002 paper, and he noted it could change. The version the BCCI uses today, including the T20 version, has not been published in full, so a calculator built from the 2002 table would not reliably match official targets. We will only add one if a current table is published.</p>
<p>For any match played under ICC rules, and for local matches that follow them, use the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-calculator' ) ); ?>">DLS calculator</a> or the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-target-calculator-t20' ) ); ?>">T20 DLS target calculator</a>.</p>

<h2 id="faq">VJD method FAQ</h2>
<?php dlscalc_faq_html( 'vjd-method' ); ?>

<h2 id="sources">Sources</h2>
<?php
dlscalc_sources_html(
	array(
		array( 'V. Jayadevan, "A new method for the computation of target scores in interrupted, limited-over cricket matches", Current Science 83(5), 2002 (PDF)', 'https://www.currentscience.ac.in/Volumes/83/05/0577.pdf' ),
		array( 'ICC: results of the ICC Cricket Committee meeting at Lord\'s, June 2012', 'https://www.icc-cricket.com/news/results-of-the-icc-cricket-committee-meeting-at-lords' ),
		array( 'The Daily Star (AFP): Jayadevan on the ICC decision, May 2012', 'https://www.thedailystar.net/node/939373' ),
		array( 'The Quint: Himachal Pradesh beat Tamil Nadu on VJD to win the Vijay Hazare Trophy, December 2021', 'https://thequint.com/sports/cricket/vijay-hazare-trophy-himachal-defeat-tamil-nadu-by-11-runs-to-win-first-title' ),
		array( 'Outlook: Saurashtra beat Uttar Pradesh on VJD, Vijay Hazare Trophy quarter-final, January 2026', 'https://www.outlookindia.com/sports/cricket/uttar-pradesh-vs-saurashtra-live-streaming-vijay-hazare-trophy-2025-26-elite-2nd-quarterfinal-toss-playing-xis-result' ),
	)
);
?>
