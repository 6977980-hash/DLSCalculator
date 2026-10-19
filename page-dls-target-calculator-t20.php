<?php
/**
 * DLS Target Calculator T20 page (/dls-target-calculator-t20/): rain slider, chase stoppages,
 * target chart, par table and a static common-targets table.
 * Owns "dls target calculator t20" and "dls calculator t20".
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlscalc_slug = 'dls-target-calculator-t20';
$dlscalc_in   = dlscalc_dls_inputs( $dlscalc_slug );
$dlscalc_r    = dlscalc_dls_run( $dlscalc_slug, $dlscalc_in );
$dlscalc_ok   = empty( $dlscalc_r['err'] );
$dlscalc_no   = (int) $dlscalc_in['n'];
$dlscalc_no   = $dlscalc_no >= 5 && $dlscalc_no <= 20 ? $dlscalc_no : 20;
$dlscalc_l    = min( max( 0, (int) $dlscalc_in['l'] ), $dlscalc_no - 5 );

get_header();
dlscalc_tool_page_open(
	'DLS Target Calculator T20',
	'Rain in a T20? Slide the overs lost and watch the revised target change. Add any stoppage during the chase and get the par score for every over and wicket.'
);
dlscalc_par_sheet_head();
?>
<div class="tool">
	<form class="panel tool__form" method="get" action="<?php echo esc_url( dlscalc_page_url( $dlscalc_slug ) . '#result' ); ?>" data-tool="t20" novalidate>
		<div class="fields">
			<label class="fld">Overs per side at start
				<input type="number" inputmode="numeric" min="5" max="20" name="n" value="<?php echo esc_attr( $dlscalc_in['n'] ); ?>" autocomplete="off">
			</label>
			<label class="fld">Team 1 final score
				<input type="number" inputmode="numeric" min="0" max="999" name="r" value="<?php echo esc_attr( $dlscalc_in['r'] ); ?>" autocomplete="off">
			</label>
		</div>
		<div class="slider">
			<label for="rain-slider" class="slider__label"><span>Overs lost before the chase</span><span data-out="lostlabel"><?php echo esc_html( $dlscalc_l . ( 1 === $dlscalc_l ? ' over' : ' overs' ) ); ?></span></label>
			<input id="rain-slider" type="range" name="l" min="0" max="<?php echo (int) ( $dlscalc_no - 5 ); ?>" step="1" value="<?php echo (int) $dlscalc_l; ?>">
			<p class="hint">Team 2 gets <b data-out="mlabel"><?php echo (int) ( $dlscalc_no - $dlscalc_l ); ?></b> overs. At least 5 overs are needed for a result.</p>
		</div>
		<div class="tool__group" role="group" aria-labelledby="g-chase">
			<h2 id="g-chase">Stoppages during the chase</h2>
			<p class="hint">Add a row if rain stops the chase. If the match is abandoned, enter all the overs that were left as overs lost.</p>
			<?php dlscalc_dls_stop_rows( 'b', $dlscalc_in['b'], '' ); ?>
			<div><button type="button" class="btn btn--line" data-add>+ Add stoppage in the chase</button></div>
		</div>
		<div data-submit><button type="submit" class="btn btn--green">Calculate</button></div>
	</form>
	<?php
	dlscalc_dls_result_panel(
		$dlscalc_r,
		$dlscalc_slug,
		$dlscalc_in,
		array(
			'tie' => 'Par to tie',
			'rr'  => 'Required rate',
		)
	);
	?>
</div>

<section class="panel block noprint">
	<h2>Target for every chase length</h2>
	<p class="hint">If no more overs are lost once the chase starts. The gold bar is the current setting.</p>
	<div class="chart" role="img" aria-label="<?php echo esc_attr( 'Bar chart of the Team 2 target for chases of 5 to ' . $dlscalc_no . ' overs' ); ?>">
		<div class="chart__bars" data-out="bars">
			<?php
			if ( $dlscalc_ok ) {
				$dlscalc_cur = '' === $dlscalc_in['b'] ? $dlscalc_no - $dlscalc_l : 0;
				foreach ( $dlscalc_r['bars'] as $dlscalc_bar ) {
					printf(
						'<div class="bar%s"><span class="bar__v">%d</span><i style="height:%dpx"></i><span class="bar__l">%d</span></div>',
						$dlscalc_bar['overs'] === $dlscalc_cur ? ' bar--on' : '',
						(int) $dlscalc_bar['target'],
						(int) max( 8, round( 150 * $dlscalc_bar['target'] / ( $dlscalc_r['S'] + 2 ) ) ),
						(int) $dlscalc_bar['overs']
					); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed class string and integers.
				}
			}
			?>
		</div>
		<div class="chart__axis">Overs for Team 2</div>
	</div>
</section>

<section class="panel block">
	<h2>How this target was worked out</h2>
	<?php dlscalc_dls_steps( $dlscalc_r ); ?>
</section>

<section class="block">
	<div class="block__head">
		<div>
			<h2>Par score for the rest of the chase</h2>
			<?php $dlscalc_on = 'Par after each completed over of the chase, for 0 to 9 wickets down. If play ends for good, Team 2 wins when it is above par.'; ?>
			<p class="hint" data-out="tablenote" data-on="<?php echo esc_attr( $dlscalc_on ); ?>" data-off="No overs left in the chase, so there is no par table."><?php echo esc_html( $dlscalc_ok && $dlscalc_r['rows'] ? $dlscalc_on : 'No overs left in the chase, so there is no par table.' ); ?></p>
		</div>
	</div>
	<?php dlscalc_dls_par_table_html( $dlscalc_r, 'T20 DLS par score by completed overs and wickets down' ); ?>
</section>
<?php dlscalc_par_sheet_foot(); ?>

<div class="entry-content tool-prose">
	<?php get_template_part( 'template-parts/content/dls-target-calculator-t20' ); ?>
</div>
<?php
dlscalc_tool_page_close( array( 'dls-calculator', 'dls-par-score-table', 'run-rate-calculator', 'net-run-rate-calculator', 'dls-method' ) );
get_footer();
