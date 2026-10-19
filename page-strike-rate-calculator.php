<?php
/**
 * Strike Rate Calculator page (/strike-rate-calculator/): batting strike rate and batting average
 * from runs, balls, innings and not outs, plus boundary figures and the runs needed to reach a
 * target strike rate. Owns "strike rate calculator cricket" and "batting average calculator cricket".
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlscalc_slug = 'strike-rate-calculator';
$dlscalc_in   = dlscalc_nrr_inputs( $dlscalc_slug );
$dlscalc_r    = dlscalc_nrr_batting( $dlscalc_in );

get_header();
dlscalc_tool_page_open(
	'Strike Rate & Batting Average Calculator',
	'Enter runs and balls faced for the strike rate. Add innings and not outs for the batting average, and fours and sixes for the boundary figures. Works for one innings or a whole season.'
);
?>
<div class="tool">
	<form class="panel tool__form" method="get" action="<?php echo esc_url( dlscalc_page_url( $dlscalc_slug ) . '#result' ); ?>" data-form="batting" novalidate<?php echo dlscalc_nrr_has_params() ? '' : ' data-plain'; // Page without link inputs: the copy saved for offline use (audit F46). ?>>
		<div class="fields">
			<?php dlscalc_stats_field( $dlscalc_r, $dlscalc_in, 'r', 'Runs scored', 'type="text" inputmode="numeric"' ); ?>
			<?php dlscalc_stats_field( $dlscalc_r, $dlscalc_in, 'b', 'Balls faced', 'type="number" inputmode="numeric" min="1"' ); ?>
		</div>
		<div class="fields">
			<?php dlscalc_stats_field( $dlscalc_r, $dlscalc_in, 'i', 'Innings', 'type="number" inputmode="numeric" min="0" placeholder="e.g. 5"' ); ?>
			<?php dlscalc_stats_field( $dlscalc_r, $dlscalc_in, 'o', 'Not outs', 'type="number" inputmode="numeric" min="0" placeholder="e.g. 1"' ); ?>
		</div>
		<div class="fields">
			<?php dlscalc_stats_field( $dlscalc_r, $dlscalc_in, 'f', 'Fours (optional)', 'type="number" inputmode="numeric" min="0" placeholder="e.g. 18"' ); ?>
			<?php dlscalc_stats_field( $dlscalc_r, $dlscalc_in, 'x', 'Sixes (optional)', 'type="number" inputmode="numeric" min="0" placeholder="e.g. 6"' ); ?>
		</div>
		<div class="fields">
			<?php dlscalc_stats_field( $dlscalc_r, $dlscalc_in, 'g', 'Target strike rate (optional)', 'type="number" inputmode="numeric" min="1" max="600" placeholder="e.g. 150"' ); ?>
		</div>
		<p class="hint">Balls faced include no-balls but not wides. For one innings, enter 1 innings and 1 not out if the batter finished not out.</p>
		<div data-submit><button type="submit" class="btn btn--green">Calculate</button></div>
	</form>
	<?php dlscalc_stats_result( $dlscalc_r, $dlscalc_slug, $dlscalc_in, 'Strike rate', 'Strike rate calculator' ); ?>
</div>

<div class="entry-content tool-prose">
	<?php get_template_part( 'template-parts/content/strike-rate-calculator' ); ?>
</div>
<?php
dlscalc_tool_page_close( array( 'bowling-average-calculator', 'run-rate-calculator', 'net-run-rate-calculator', 'dls-calculator' ) );
get_footer();
