<?php
/**
 * 2022 T20 World Cup, India v Bangladesh, Adelaide: 151 in 16 overs.
 * Owns "india vs bangladesh dls 2022". Facts in dlscalc_matches().
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Targets posted in the Scroll.in live blog during the rain break, by overs left to Bangladesh.
$dlscalc_break = array(
	18 => 169,
	15 => 142,
	13 => 122,
	10 => 89,
);
?>
<h2 id="what-happened">What happened in Adelaide?</h2>
<p>India played Bangladesh in the Super 12 stage of the 2022 T20 World Cup at Adelaide Oval on 2 November. Bangladesh chose to bowl, and India made <strong>184 for 6 in 20 overs</strong>, with Virat Kohli 64 not out. During that innings Kohli passed Mahela Jayawardene as the leading run-scorer in T20 World Cup history.</p>
<p>Bangladesh started the chase fast. Litton Das hit 60 off 27 balls and they were <strong>66 for 0 after 7 overs</strong> when rain arrived just before 9 pm. At that moment they were 17 runs ahead of the DLS par score of 49, so if no more play had been possible, Bangladesh would have won.</p>
<p>The break lasted about 52 minutes. Four overs were lost and the target became <strong>151 in 16 overs</strong>, leaving Bangladesh 85 to get off 9 overs with all 10 wickets. Soon after the restart Litton was run out by a direct hit from KL Rahul, wickets fell regularly, and Bangladesh finished on 145 for 6. India won by 5 runs.</p>

<h2 id="the-break">What targets were posted during the rain break?</h2>
<p>While the covers were on, the targets for different lengths of chase were posted, for example in the Scroll.in live blog. Here they are next to our calculator's targets for the same overs, with Bangladesh 66 for 0 after 7 overs.</p>
<div class="table-wrap">
	<table class="data data--num">
		<caption>Bangladesh's revised target by overs available</caption>
		<thead><tr><th scope="col">Bangladesh's overs</th><th scope="col">Posted on the day</th><th scope="col">Our calculator</th></tr></thead>
		<tbody>
			<?php
			foreach ( $dlscalc_break as $dlscalc_overs => $dlscalc_target ) :
				$dlscalc_r = dlscalc_dls_calc(
					array(
						'n' => '20',
						'r' => '184',
						'g' => '245',
						'b' => '7-0-' . ( 20 - $dlscalc_overs ),
					)
				);
				?>
				<tr><th scope="row"><?php echo (int) $dlscalc_overs; ?></th><td><?php echo (int) $dlscalc_target; ?></td><td><?php echo (int) $dlscalc_r['target']; ?></td></tr>
			<?php endforeach; ?>
			<tr><th scope="row">16 (used)</th><td>151</td><td><?php echo (int) dlscalc_match_result( dlscalc_matches()['t20-world-cup-2022-india-bangladesh'] )['target']; ?></td></tr>
		</tbody>
	</table>
</div>
<p>Our targets are 1 to 2 runs lower for every length of chase, which is the usual gap between the published Standard Edition and the Professional Edition used at ICC events.</p>

<h2 id="why-lost">Why did Bangladesh lose from 17 runs ahead?</h2>
<p>Being ahead on par only matters if play never resumes. Once it did, Bangladesh faced a new chase: 85 off 54 balls. Before the rain they needed 119 off 13 overs, 9.15 an over; after it, 9.44 an over.</p>
<p>The target fell by only 34 runs for 4 lost overs because Bangladesh had all 10 wickets. DLS assumes a team with wickets in hand can attack harder in a shorter innings, so it cut their overs by almost a third but their runs needed by much less. Wickets then fell quickly, and the par that had favoured them before the break turned against them.</p>
<p>During a chase like this, the <a href="<?php echo esc_url( dlscalc_page_url( 'live-dls-par-score' ) ); ?>">live DLS par tracker</a> shows who is ahead after every over, and the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-target-calculator-t20' ) ); ?>">T20 DLS target calculator</a> gives the revised target for any overs lost.</p>
<?php
dlscalc_match_tail(
	't20-world-cup-2022-india-bangladesh',
	array(
		array( 'cricket.com.au: Kohli stars as India hold off Bangladesh in last-over thriller', 'https://www.cricket.com.au/news/3319553/kohli-stars-as-india-hold-off-bangladesh-in-last-over-thriller' ),
		array( 'Scroll.in: India vs Bangladesh as it happened (targets during the rain break)', 'https://scroll.in/field/1036437/icc-mens-t20-world-cup-2022-india-vs-bangladesh-live-score-updates-stats-and-more-can-rohit-sharma-and-co-return-to-winning-ways' ),
		array( 'Wikipedia: 2022 ICC Men\'s T20 World Cup', 'https://en.wikipedia.org/wiki/2022_ICC_Men%27s_T20_World_Cup' ),
	)
);
