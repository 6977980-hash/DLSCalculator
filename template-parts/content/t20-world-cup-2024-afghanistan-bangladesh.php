<?php
/**
 * 2024 T20 World Cup, Afghanistan v Bangladesh, St Vincent: 114 in 19 overs.
 * Owns "afghanistan vs bangladesh dls 2024". Facts in dlscalc_matches().
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlscalc_before = dlscalc_dls_calc(
	array(
		'n' => '20',
		'r' => '115',
		'g' => '245',
		'b' => '11.4-7-8.2',
	)
);
?>
<h2 id="what-happened">What happened in St Vincent?</h2>
<p>Afghanistan played Bangladesh in the last Super 8 match of Group 1 at the 2024 T20 World Cup, at Arnos Vale in Kingstown, St Vincent, on 24 June. The result would decide whether Afghanistan, Australia or Bangladesh reached the semi-finals. Afghanistan batted first and made only <strong>115 for 5 in 20 overs</strong>, with Rahmanullah Gurbaz top-scoring on 43.</p>
<p>Bangladesh needed 116 to win, and to win inside 12.1 overs to go through themselves: only a win that fast would have lifted their net run rate above Afghanistan's and Australia's. The <a href="<?php echo esc_url( dlscalc_page_url( 'net-run-rate-calculator' ) ); ?>">net run rate calculator</a> works out targets like this for any points table, and the <a href="<?php echo esc_url( dlscalc_page_url( 'how-to-calculate-nrr' ) . '#special-cases' ); ?>">NRR guide</a> explains how rain-shortened matches count.</p>
<p>Rain interrupted Bangladesh's chase three times:</p>
<ol>
	<li>At 31 for 3 after 3.2 overs. The break lasted about half an hour, but it used up the extra time allowed, so <strong>no overs were lost</strong>.</li>
	<li>At 81 for 7 after 11.4 overs, as Gulbadin Naib went down with cramp. Afghanistan were two runs ahead of the DLS par. <strong>One over was lost</strong> and the target became <strong>114 in 19 overs</strong>.</li>
	<li>At 105 for 9 after 17.4 overs, a brief shower. The players went off and came straight back, with no overs lost.</li>
</ol>
<p>Bangladesh were all out for <strong>105 in 17.5 overs</strong>, with Litton Das 54 not out. Rashid Khan and Naveen-ul-Haq took 4 wickets each. Afghanistan won by 8 runs on DLS and reached their first semi-final at a men's senior ICC event, and the result knocked out both Australia and Bangladesh.</p>

<h2 id="three-breaks">Three rain breaks, but why only two runs off the target?</h2>
<p>A stoppage only changes the target when overs are lost, so only the second break counts in DLS. It came when Bangladesh had 8.2 overs left but only 3 wickets, worth 16.5% in the resource table. Losing one over left 7.2 overs, worth 15.5%, so Bangladesh lost just <strong>1.0%</strong> of their resources.</p>
<p>That is why the target fell by only two runs, from 116 to 114. With 7 wickets down, the last over of the innings was worth very little: a tail-end side rarely gets to use it. Had Bangladesh lost the same over with all 10 wickets in hand, it would have cost them more of their batting: our calculator gives a target of 109 in that case.</p>
<p>Our calculator gives a target of 113, one less than the official 114. If you apply the official target, the par for the 19-over chase was 113, and Bangladesh's 105 all out left them 8 runs short.</p>

<h2 id="gulbadin">What happened with the cramp and the par score?</h2>
<p>As the second shower arrived in the 12th over, Afghanistan coach Jonathan Trott was seen signalling to his players to slow the game down. Moments later Gulbadin Naib went down with cramp, and play stopped for the rain soon after. At that moment Afghanistan were two runs ahead of the DLS par score, so if no more play had been possible they would have won.</p>
<?php if ( empty( $dlscalc_before['err'] ) ) : ?>
<p>The par on the day was about 83. Our Standard Edition calculator gives <?php echo (int) $dlscalc_before['par']; ?> at 81 for 7 after 11.4 overs, which would have made the score level, a tie, rather than an Afghanistan lead. Either way the margin was tiny, which is why every second of delay mattered: a washout at that moment would have decided who reached the semi-finals.</p>
<?php endif; ?>
<p>Many fans and former players called it gamesmanship, while others noted that rain was already falling. The match resumed, so the par score never decided the result, and Afghanistan won the reduced chase on the field. The <a href="<?php echo esc_url( dlscalc_page_url( 'live-dls-par-score' ) ); ?>">live DLS par tracker</a> shows exactly this kind of moment: who is ahead if the next ball is never bowled.</p>
<?php
dlscalc_match_tail(
	't20-world-cup-2024-afghanistan-bangladesh',
	array(
		array( 'Wikipedia: 2024 ICC Men\'s T20 World Cup, Super 8', 'https://en.wikipedia.org/wiki/2024_ICC_Men%27s_T20_World_Cup' ),
		array( 'cricket.com.au: "Funniest thing I\'ve seen": Afghan\'s cramp under scrutiny, June 2024', 'https://www.cricket.com.au/news/4045015/' ),
	)
);
