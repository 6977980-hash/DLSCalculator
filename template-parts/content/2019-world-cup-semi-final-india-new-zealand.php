<?php
/**
 * 2019 World Cup semi-final, India v New Zealand, Manchester: the DLS targets India never chased.
 * Owns "2019 world cup semi final dls". Facts in dlscalc_matches().
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlscalc_reported = array(
	'46' => 237,
	'40' => 223,
	'35' => 209,
	'30' => 192,
	'25' => 172,
	'20' => 148,
);
?>
<h2 id="what-happened">What happened at Old Trafford in 2019?</h2>
<p>India met New Zealand in the first semi-final of the 2019 World Cup at Old Trafford, Manchester, on 9 July. New Zealand won the toss and batted. At 2:01 pm rain stopped play with New Zealand on <strong>211 for 5 after 46.1 overs</strong>, Ross Taylor on 67, and 23 balls of their innings left.</p>
<p>The rain did not stop. Overs would start to be lost from about 4:31 pm, and DLS targets for India were worked out and shared while fans waited. Play was finally called off for the day at 6:22 pm, and the match moved to the reserve day.</p>

<h2 id="targets">What DLS targets would India have faced?</h2>
<p>If New Zealand had not batted again, India would have chased a revised target in fewer overs. The figures below were tweeted by statistician Mohandas Menon and reported by Indian media on the day. They are not official ICC figures, because no target was ever set. Next to them are the targets from our calculator, with New Zealand's innings ending at 211 for 5 in 46.1 overs.</p>
<div class="table-wrap">
	<table class="data data--num">
		<caption>India's DLS target by overs available, if New Zealand's innings ended at 211 for 5</caption>
		<thead><tr><th scope="col">India's overs</th><th scope="col">Reported target</th><th scope="col">Our calculator</th></tr></thead>
		<tbody>
			<?php
			foreach ( $dlscalc_reported as $dlscalc_overs => $dlscalc_target ) :
				$dlscalc_r = dlscalc_dls_calc(
					array(
						'n' => '50',
						'r' => '211',
						'g' => '245',
						'c' => (string) $dlscalc_overs,
						'a' => '46.1-5-3.5',
					)
				);
				?>
				<tr><th scope="row"><?php echo esc_html( $dlscalc_overs ); ?></th><td><?php echo (int) $dlscalc_target; ?></td><td><?php echo (int) $dlscalc_r['target']; ?></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<p>A 20-over chase was the shortest that could still give a result, since Team 2 must face at least 20 overs in a 50-over match. It had a cut-off time of 6:36 pm, and the umpires abandoned play for the day just before it.</p>

<h2 id="why-differ">Why are our targets lower?</h2>
<p>Our calculator gives New Zealand 87.7% of their resources: they lost the last 3.5 overs with 5 wickets in hand, which is worth 12.3%. A 20-over chase with 10 wickets is worth 56.6%, so the par is 211 × 56.6 ÷ 87.7 = 136, and the target 137.</p>
<p>The reported figures are 5 to 12 runs higher, with the gap widest for the shortest chases. They would have come from the Professional Edition, the ICC software used in international matches. Its values are not published, so the gap cannot be traced step by step. What the numbers do show is that the two editions can differ by more than a few runs when Team 1's innings is cut short near the end. For a local match decided by the Standard Edition, our figures are the official ones.</p>

<h2 id="reserve-day">What happened on the reserve day?</h2>
<p>The match resumed on 10 July from exactly where it stopped, with no overs lost, so DLS was never needed. New Zealand added 28 more runs to finish on <strong>239 for 8 in 50 overs</strong>, and India chased the full 240.</p>
<p>India collapsed to 5 for 3 and then 92 for 6. Ravindra Jadeja and MS Dhoni brought them close, but Dhoni was run out by a direct hit from Martin Guptill and India were all out for 221 in 49.3 overs. New Zealand won by 18 runs. It was Dhoni's last one-day international.</p>
<?php
dlscalc_match_tail(
	'2019-world-cup-semi-final-india-new-zealand',
	array(
		array( 'Wikipedia: 2019 Cricket World Cup, semi-finals', 'https://en.wikipedia.org/wiki/2019_Cricket_World_Cup' ),
		array( 'National Herald: what India will need to chase if New Zealand do not bat again, July 2019', 'https://www.nationalheraldindia.com/amp/story/icc-cricket-world-cup-2019/here-is-what-india-will-need-to-chase-if-kiwi-do-not-get-to-bat-again' ),
	)
);
