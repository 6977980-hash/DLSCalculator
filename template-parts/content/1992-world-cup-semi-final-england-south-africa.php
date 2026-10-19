<?php
/**
 * 1992 World Cup semi-final, England v South Africa, Sydney: the most productive overs rule.
 * Owns "1992 world cup semi final rain rule" and "22 off 1 ball". Facts in dlscalc_matches().
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 id="what-happened">What happened in the 1992 World Cup semi-final?</h2>
<p>England met South Africa in the second semi-final at the Sydney Cricket Ground on 22 March 1992. South Africa, back in international cricket after isolation, won the toss and fielded. They bowled their overs so slowly that both innings were cut from 50 to 45 overs, a time penalty rather than rain.</p>
<p>England made <strong>252 for 6 in 45 overs</strong>, so South Africa needed 253. They kept up with the rate, and with 13 balls left they needed 22 runs, a hard but real chance. Then, before the last ball of the 43rd over, rain stopped play for 12 minutes and two overs were lost.</p>
<p>When the players came back, South Africa had one ball left. The official requirement was <strong>21 runs off 1 ball</strong>. The scoreboard at the SCG first showed 22 off 7 and then 22 off 1, which is how most fans remember it. South Africa took a single, finished on 232 for 6, and England won by 19 runs.</p>

<h2 id="the-rule">How did the most productive overs rule work?</h2>
<p>In 1992 the rule was simple: when Team 2 lost overs, its target was cut by the runs Team 1 had scored in its <strong>least productive overs</strong>, as many of them as Team 2 had lost. Team 1's most productive overs were kept, which is where the name comes from.</p>
<p>South Africa lost two overs, so the target came down by the runs England scored in their two lowest-scoring overs. Those two overs had cost England just one run, so the target fell by one run while South Africa lost 12 balls. The rule looked only at runs. It ignored that South Africa still had four wickets in hand and would have used those 12 balls to attack.</p>
<p>That is the flaw the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-method' ) ); ?>">DLS method</a> fixes. DLS treats overs and wickets together as resources, so taking 12 balls away from a team with four wickets left takes away a fair share of what it could still score.</p>

<h2 id="why-famous">Why did this match change cricket?</h2>
<p>The 1992 semi-final is the reason the Duckworth-Lewis method exists. Frank Duckworth and Tony Lewis started work on a fairer system after it. Their method was first used in international cricket in 1997, and the ICC formally adopted it in 1999. It became Duckworth-Lewis-Stern in 2014 and is still the ICC's rain rule today.</p>
<p>It also shows why rain rules matter most in close finishes. A target that is slightly off in the 20th over hardly matters; a target that is wrong with one ball left decides a World Cup semi-final.</p>
<p>Below we enter the same match into our DLS calculator. With both innings treated as 45 overs, South Africa would have needed 4 off the last ball to win and 3 to tie. The D/L figure usually quoted, on Wikipedia's page about the method, is a target of 236: 5 to win and 4 to tie. Treat both as estimates. England's innings was cut to 45 overs while it was under way, and a real D/L calculation would have counted that interruption, which can only be approximated now. What is certain is that South Africa would have needed a boundary, not 21 off one ball.</p>
<?php
dlscalc_match_tail(
	'1992-world-cup-semi-final-england-south-africa',
	array(
		array( 'Wikipedia: 1992 Cricket World Cup, semi-finals', 'https://en.wikipedia.org/wiki/1992_Cricket_World_Cup' ),
		array( 'Wikipedia: Duckworth-Lewis-Stern method, history', 'https://en.wikipedia.org/wiki/Duckworth%E2%80%93Lewis%E2%80%93Stern_method' ),
	)
);
