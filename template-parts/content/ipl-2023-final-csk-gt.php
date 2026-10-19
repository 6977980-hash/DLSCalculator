<?php
/**
 * IPL 2023 final, Chennai Super Kings v Gujarat Titans, Ahmedabad: 171 in 15 overs.
 * Owns "ipl 2023 final dls target" and "csk vs gt final dls". Facts in dlscalc_matches().
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Targets posted during the delay, compared with our engine below.
$dlscalc_posted = array(
	5  => 66,
	10 => 123,
	15 => 171,
);
?>
<h2 id="what-happened">What happened in the IPL 2023 final?</h2>
<p>The IPL 2023 final between Chennai Super Kings and Gujarat Titans was due on 28 May at the Narendra Modi Stadium, Ahmedabad, but rain washed out the whole day without a toss. It was played on the reserve day, 29 May. CSK won the toss and bowled, and Gujarat made <strong>214 for 4 in 20 overs</strong>, with Sai Sudharsan hitting 96 off 47 balls.</p>
<p>Three balls into the chase, with CSK on <strong>4 for 0</strong>, rain returned at 9:50 pm. Play did not restart until 12:10 am. Five overs had been lost, and CSK's target became <strong>171 in 15 overs</strong>, with a 4-over powerplay and a limit of 3 overs per bowler.</p>
<p>The chase went to the last ball. CSK needed 10 off the final two balls, and Ravindra Jadeja hit a six and then a four to finish on 171 for 5. Chennai won by 5 wickets on DLS, their fifth IPL title, level with Mumbai Indians. It was also Ambati Rayudu's last IPL match.</p>

<h2 id="the-target">Why was the target 171 in 15 overs?</h2>
<p>A simple run-rate cut would give 215 × 15 ÷ 20, about 161. DLS asks for 10 more because CSK kept all 10 wickets for a shorter innings. A full 20-over innings is worth 56.6%; with 3 balls gone and no wickets down, CSK had 55.5% left, and after the rain they had 14.3 overs, worth 44.0%. They lost 11.5%, leaving them 45.1% in all.</p>
<p class="formula">Par = 214 × 45.1 ÷ 56.6 = 170.52, rounded down to 170. Target = 171</p>
<p>Our calculator gives exactly the official target. The 10 extra runs over a run-rate cut reflect what every T20 side knows: with 10 wickets and 15 overs, you can bat with less fear than with 10 wickets and 20 overs.</p>

<h2 id="other-targets">What other targets were posted during the delay?</h2>
<p>During the long wait, DLS targets for shorter chases were posted, down to 5 overs, the shortest chase that still gives a result in a T20. Here they are next to our calculator, with CSK 4 for 0 after 3 balls.</p>
<div class="table-wrap">
	<table class="data data--num">
		<caption>CSK's revised target by overs available</caption>
		<thead><tr><th scope="col">CSK's overs</th><th scope="col">Posted on the day</th><th scope="col">Our calculator</th></tr></thead>
		<tbody>
			<?php foreach ( $dlscalc_posted as $dlscalc_overs => $dlscalc_target ) : ?>
				<tr><th scope="row"><?php echo (int) $dlscalc_overs; ?></th><td><?php echo (int) $dlscalc_target; ?></td><td><?php echo (int) dlscalc_dls_calc( array( 'n' => '20', 'r' => '214', 'g' => '245', 'b' => '0.3-0-' . ( 20 - $dlscalc_overs ) ) )['target']; ?></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<p>The 15-over target, the one that was used, matches exactly. For the 5-over and 10-over chases our targets are 2 runs lower, the usual gap between the published Standard Edition and the Professional Edition used in top-level cricket.</p>
<?php
dlscalc_match_tail(
	'ipl-2023-final-csk-gt',
	array(
		array( 'IPL: TATA IPL 2023 final, CSK vs GT match report', 'https://www.iplt20.com/news/article/tata-ipl-2023-final-csk-vs-gt-match-report' ),
		array( 'Wikipedia: 2023 Indian Premier League final', 'https://en.wikipedia.org/wiki/2023_Indian_Premier_League_final' ),
	)
);
