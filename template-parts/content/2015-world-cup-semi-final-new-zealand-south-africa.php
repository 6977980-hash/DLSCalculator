<?php
/**
 * 2015 World Cup semi-final, New Zealand v South Africa, Auckland: target 298 in 43 overs.
 * Owns "2015 world cup semi final dls" and "298 in 43 overs". Facts in dlscalc_matches().
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 id="what-happened">What happened at Eden Park in 2015?</h2>
<p>New Zealand played South Africa in the first semi-final of the 2015 World Cup at Eden Park, Auckland, on 24 March. South Africa won the toss and batted, and were <strong>216 for 3 after 38 overs</strong>, with Faf du Plessis on 82 and AB de Villiers on 60, when rain stopped play for more than an hour.</p>
<p>Seven overs were lost and South Africa's innings was cut to 43 overs, so they had only five more overs to bat. David Miller hit 49 off 18 balls and they finished on <strong>281 for 5 in 43 overs</strong>.</p>
<p>New Zealand's target was set at <strong>298 in 43 overs</strong>, 17 more than South Africa scored. They got there with one ball to spare: Grant Elliott, on 84 not out, hit Dale Steyn for six from the second-last ball. It was the highest successful chase in a World Cup knockout match, and it sent New Zealand to their first World Cup final.</p>

<h2 id="why-higher">Why was the target higher than South Africa's score?</h2>
<p>When the rain came, South Africa had 12 overs and 7 wickets left: the part of an innings where a set side scores fastest. Losing 7 of those overs took away far more than 7 overs' worth of an average innings. In the resource table, 12 overs left with 3 wickets down is worth 34.3%, and 5 overs left is worth 16.5%, so South Africa lost <strong>17.8%</strong> of their resources and finished with 82.2%.</p>
<p>New Zealand knew from the first ball that they had 43 overs and 10 wickets, which is 92.8% of a full innings. They had more resources than South Africa, and when Team 2 has more, DLS adds runs to Team 1's score instead of scaling it down. This is the case covered in the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-method' ) ); ?>">DLS method guide</a> under "R2 more than R1".</p>
<p>The extra runs are fair: if South Africa had known from the start that they had 43 overs, they would have paced their innings for 43 overs and scored faster earlier.</p>

<h2 id="why-differ">Why does the published table give 307?</h2>
<p>With the Standard Edition, the extra runs are worked out with G50, the average 50-over score (245): 281 + 245 × (92.8 − 82.2) ÷ 100 = 306.97, so the target is 307. The ICC used the Professional Edition, which runs on unpublished ICC software and handles high-scoring innings differently, and it gave 298.</p>
<p>This is the biggest gap among the matches we recalculated, and it comes from the situation where the two editions differ most: a high first-innings score that was cut short. For club and league matches without the ICC software, the Standard Edition is the official method, and our calculator follows it exactly.</p>
<?php
dlscalc_match_tail(
	'2015-world-cup-semi-final-new-zealand-south-africa',
	array(
		array( 'New Zealand Cricket scorecard archive: New Zealand v South Africa, 2015 World Cup semi-final', 'https://archive.nzc.nz/Scorecards/560/560900.html' ),
		array( 'Wikipedia: 2015 Cricket World Cup, semi-finals', 'https://en.wikipedia.org/wiki/2015_Cricket_World_Cup' ),
	)
);
