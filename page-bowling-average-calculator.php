<?php
/**
 * Bowling Average Calculator page (/bowling-average-calculator/): bowling average, economy rate
 * and bowling strike rate from overs, runs conceded and wickets, plus dot ball percentage.
 * Owns "bowling average calculator" and "economy rate calculator cricket".
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlscalc_slug = 'bowling-average-calculator';
$dlscalc_in   = dlscalc_nrr_inputs( $dlscalc_slug );
$dlscalc_r    = dlscalc_nrr_bowling( $dlscalc_in );

get_header();
dlscalc_tool_page_open(
	'Bowling Average & Economy Rate Calculator',
	'Enter the overs bowled, runs conceded and wickets to get the bowling average, economy rate and bowling strike rate. Works for one spell, one match or a whole career.'
);
?>
<div class="tool">
	<form class="panel tool__form" method="get" action="<?php echo esc_url( dlscalc_page_url( $dlscalc_slug ) . '#result' ); ?>" data-form="bowling" novalidate>
		<div class="fields">
			<?php dlscalc_stats_field( $dlscalc_r, $dlscalc_in, 'o', 'Overs bowled', 'type="text" inputmode="decimal" placeholder="e.g. 38.4"' ); ?>
			<?php dlscalc_stats_field( $dlscalc_r, $dlscalc_in, 'r', 'Runs conceded' ); ?>
		</div>
		<div class="fields">
			<?php dlscalc_stats_field( $dlscalc_r, $dlscalc_in, 'k', 'Wickets' ); ?>
			<?php dlscalc_stats_field( $dlscalc_r, $dlscalc_in, 'd', 'Dot balls (optional)' ); ?>
		</div>
		<p class="hint">Write overs as 38.4 for 38 overs and 4 balls. Runs conceded include wides and no-balls; byes and leg byes do not count against the bowler.</p>
		<div data-submit><button type="submit" class="btn btn--green">Calculate</button></div>
	</form>
	<?php dlscalc_stats_result( $dlscalc_r, $dlscalc_slug, $dlscalc_in, 'Bowling average', 'Bowling average calculator' ); ?>
</div>

<div class="entry-content tool-prose">
	<?php get_template_part( 'template-parts/content/bowling-average-calculator' ); ?>
</div>
<?php
dlscalc_tool_page_close( array( 'strike-rate-calculator', 'run-rate-calculator', 'net-run-rate-calculator', 'dls-calculator' ) );
get_footer();
