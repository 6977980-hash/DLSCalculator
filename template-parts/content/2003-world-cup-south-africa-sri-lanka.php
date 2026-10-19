<?php
/**
 * 2003 World Cup, South Africa v Sri Lanka, Durban: the D/L par score read as the target.
 * Owns "south africa vs sri lanka 2003 duckworth lewis". Facts in dlscalc_matches().
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 id="what-happened">What happened in Durban in 2003?</h2>
<p>South Africa hosted the 2003 World Cup and had to beat Sri Lanka at Kingsmead, Durban, on 3 March to stay in it. Sri Lanka batted first and made <strong>268 for 9 in 50 overs</strong>, with 124 from Marvan Atapattu.</p>
<p>With rain on the way, the South African dressing room worked out the Duckworth-Lewis figure for 45 overs and sent the number out to the batters: 229. Mark Boucher took South Africa to 229 for 6 with one ball of the 45th over left, then blocked the last ball, believing the job was done.</p>
<p>Rain arrived at the end of the over and no more play was possible. South Africa were <strong>229 for 6 after 45 overs</strong>, exactly on the D/L par score. The match was tied, and the tie knocked the hosts out of their own World Cup. Shaun Pollock lost the captaincy soon after.</p>

<h2 id="the-numbers">Why did 229 only tie the match?</h2>
<p>Sri Lanka batted their full 50 overs, so they used 100% of their resources. When play ended, South Africa had 5 overs left with 6 wickets down, which is worth 14.3% in the resource table. They had used the other <strong>85.7%</strong>.</p>
<p class="formula">Par = 268 × 85.7 ÷ 100 = 229.68, rounded down to 229</p>
<p>The par score is the score that <em>ties</em> the match if play ends there. To win, South Africa needed one more run: 230. Their sheet was correct; it was read as the winning target instead of the tie.</p>
<p>The timing made it worse. If the rain had come one ball earlier, after 44.5 overs, the par would have been 228, and 229 would have won the match.</p>

<h2 id="why-famous">What is the lesson for every team?</h2>
<p>Know the difference between the par score and the target, and know which one you are looking at. A <a href="<?php echo esc_url( dlscalc_page_url( 'dls-par-score-table' ) ); ?>">DLS par score table</a> shows the score to tie at each over; beat it by one run to be ahead. During a chase, the <a href="<?php echo esc_url( dlscalc_page_url( 'live-dls-par-score' ) ); ?>">live DLS par tracker</a> tells you after every over whether you are ahead, level or behind if play stops now.</p>
<p>Our calculator, using today's Standard Edition table, gives the same par for this match: 229.</p>
<?php
dlscalc_match_tail(
	'2003-world-cup-south-africa-sri-lanka',
	array(
		array( 'ICC: Cricket World Cup memories, Sri Lanka v South Africa 2003', 'https://www.icc-cricket.com/tournaments/cricketworldcup/news/cricket-world-cup-memories-sri-lanka-v-south-africa' ),
		array( 'Wikipedia: Duckworth-Lewis-Stern method, the 2003 worked example', 'https://en.wikipedia.org/wiki/Duckworth%E2%80%93Lewis%E2%80%93Stern_method' ),
		array( 'Wikipedia: 2003 Cricket World Cup', 'https://en.wikipedia.org/wiki/2003_Cricket_World_Cup' ),
		array( 'The Papare: Durban will always hold a special place for Sri Lanka', 'https://www.thepapare.com/durban-will-always-hold-a-special-place-for-sri-lanka/' ),
	)
);
