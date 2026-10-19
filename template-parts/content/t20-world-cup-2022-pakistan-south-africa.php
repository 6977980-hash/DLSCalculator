<?php
/**
 * 2022 T20 World Cup, Pakistan v South Africa, Sydney: 142 in 14 overs.
 * Owns "pakistan vs south africa dls 2022". Facts in dlscalc_matches().
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 id="what-happened">What happened in Sydney?</h2>
<p>Pakistan played South Africa in the Super 12 stage of the 2022 T20 World Cup at the Sydney Cricket Ground on 3 November. Pakistan needed to win to stay in the tournament. They batted first and made <strong>185 for 9 in 20 overs</strong>, with Shadab Khan hitting 52 off 22 balls.</p>
<p>Just before the rain, Shadab took the wickets of Temba Bavuma and Aiden Markram in the same over. South Africa were <strong>69 for 4 after 9 overs</strong> when rain stopped play, and the DLS par at that point was 84, so Pakistan were 15 runs ahead.</p>
<p>When play resumed, six overs had been lost and the target became <strong>142 in 14 overs</strong>, leaving South Africa 73 to get off the last 5 overs. They finished on 108 for 9, and Pakistan won by 33 runs. Pakistan went on to reach the final.</p>
<p>Some reports give the score at the break as 66 or 67 for 4 and the target as 141. The scorecard, ball-by-ball commentary and the 33-run margin all fit 69 for 4 and 142.</p>

<h2 id="the-target">Why did South Africa need 73 off 5 overs?</h2>
<p>Before the rain, South Africa needed 117 off 11 overs. After it, they needed 73 off 5: the overs fell by more than half, but the runs by only 44. The reason is the four wickets they had already lost.</p>
<p>In the resource table, 11 overs left with 4 wickets down is worth 30.4%, and 5 overs left with 4 down is worth 16.1%. So the six lost overs took away 14.3% of South Africa's resources, about a quarter of a full T20 innings, and the target fell by about a quarter of Pakistan's score. Had South Africa been 69 for 0, the same six overs would have been worth 17.7%, and our calculator gives a target of 128.</p>
<p class="formula">Par = 185 × 42.3 ÷ 56.6 = 138.26, rounded down to 138, so our target is 139</p>
<p>The official target was 142, three runs more. That is within the usual gap between the published Standard Edition and the Professional Edition used at ICC events.</p>

<h2 id="why-famous">What does this match teach about wickets before rain?</h2>
<p>When rain is coming, wickets matter as much as runs. A side that loses wickets just before a stoppage is judged to have used more of its innings, both for the par score if play never resumes and for the revised target if it does. South Africa's two wickets in Shadab's over left them behind on par and facing a much steeper chase after the break.</p>
<p>The <a href="<?php echo esc_url( dlscalc_page_url( 'live-dls-par-score' ) ); ?>">live DLS par tracker</a> shows this as it happens: the par jumps every time a wicket falls.</p>
<?php
dlscalc_match_tail(
	't20-world-cup-2022-pakistan-south-africa',
	array(
		array( 'Wikipedia: 2022 ICC Men\'s T20 World Cup', 'https://en.wikipedia.org/wiki/2022_ICC_Men%27s_T20_World_Cup' ),
		array( 'Khaleej Times (AFP): Pakistan beat South Africa in rain-affected game, November 2022', 'https://www.khaleejtimes.com/sports/t20-world-cup-pakistan-beat-south-africa-in-rain-affected-game-to-keep-slim-semis-hopes-alive' ),
	)
);
