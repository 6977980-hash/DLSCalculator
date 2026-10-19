<?php
/**
 * 2022 T20 World Cup, England v Ireland, MCG: Ireland win by 5 runs on a par of 110.
 * Owns "england vs ireland dls 2022". Facts in dlscalc_matches().
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 id="what-happened">What happened at the MCG?</h2>
<p>England played Ireland in the Super 12 stage of the 2022 T20 World Cup at the Melbourne Cricket Ground on 26 October. Rain delayed the start, and England chose to bowl. Ireland had another short break at 11 for 0, with no overs lost, and went on to make <strong>157 all out in 19.2 overs</strong>, led by Andrew Balbirnie's 62.</p>
<p>England lost early wickets in the chase. At 6:42 pm they were <strong>105 for 5 after 14.3 overs</strong>, needing 53 off 33 balls, when heavy rain came. The cut-off for a restart was 6:56 pm. The ground did not dry in time, and the result was declared at 6:54 pm.</p>
<p>Because the chase never restarted, there was no revised target. The result came from the DLS par score at the moment play stopped: <strong>110</strong>. England were 5 runs short of it, so Ireland won by 5 runs.</p>

<h2 id="the-par">How is the par of 110 worked out?</h2>
<p>Ireland were bowled out, so they used all of their resources. A full 20-over innings is worth 56.6% on the DLS scale, and an all-out innings counts as the full 56.6% even though it ended four balls early.</p>
<p>When the rain came, England had 5.3 overs left with 5 wickets down, which is worth 16.6%. They lost all of it, so they used 56.6 − 16.6 = <strong>40.0%</strong>.</p>
<p class="formula">Par = 157 × 40.0 ÷ 56.6 = 110.95, rounded down to 110</p>
<p>England needed 111 to win at that point and 110 to tie. On 105 they were behind. Our calculator gives exactly the same par as the official figure. You can check the par for every over and wicket of a chase like this in the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-par-score-table' ) ); ?>">DLS par score table</a>.</p>

<h2 id="why-famous">Why does this match still matter?</h2>
<p>It was one of the great upsets of T20 World Cups: Ireland beat the team that went on to win the tournament, at the biggest ground in the game. England beat Pakistan in the final at the same ground a few weeks later.</p>
<p>It also shows how the par moves with wickets. At 14.3 overs of this chase, the par for each number of wickets down was:</p>
<ul>
	<?php foreach ( array( 0, 1, 3, 5 ) as $dlscalc_w ) : ?>
		<li><?php echo (int) $dlscalc_w; ?> down: par <?php echo (int) dlscalc_dls_calc( array( 'n' => '20', 'r' => '157', 'g' => '245', 'b' => '14.3-' . $dlscalc_w . '-5.3' ) )['par']; ?></li>
	<?php endforeach; ?>
</ul>
<p>So England would have needed to be almost unbeaten to be ahead on 105. The real damage was the run rate: they were well behind the required rate when the rain came, and DLS only confirmed it.</p>
<?php
dlscalc_match_tail(
	't20-world-cup-2022-england-ireland',
	array(
		array( 'ICC: rain interferes late as Ireland hold off strong England challenge', 'https://www.icc-cricket.com/news/rain-interferes-late-as-ireland-hold-off-strong-england-challenge' ),
		array( 'Wikipedia: 2022 ICC Men\'s T20 World Cup', 'https://en.wikipedia.org/wiki/2022_ICC_Men%27s_T20_World_Cup' ),
	)
);
