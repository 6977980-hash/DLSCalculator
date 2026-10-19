<?php
/**
 * DLS Par Score Table page (/dls-par-score-table/): par for every over and wicket, 5 to 50 overs.
 * Owns "dls par score calculator", "dls par score", "dls par score table" and "dls par score chart".
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlscalc_slug = 'dls-par-score-table';
$dlscalc_in   = dlscalc_dls_inputs( $dlscalc_slug );
$dlscalc_r    = dlscalc_dls_run( $dlscalc_slug, $dlscalc_in );

get_header();
dlscalc_tool_page_open(
	'DLS Par Score Table and Calculator',
	'Choose the overs per side and Team 1\'s score to get the DLS par score after every over of the chase, for 0 to 9 wickets down. Print it before the match and the scorers are ready for rain.'
);
dlscalc_par_sheet_head();
?>
<div class="tool">
	<form class="panel tool__form" method="get" action="<?php echo esc_url( dlscalc_page_url( $dlscalc_slug ) . '#result' ); ?>" data-tool="parTable" novalidate<?php echo dlscalc_dls_has_params() ? '' : ' data-plain'; // Page without link inputs: the copy saved for offline use (audit F46). ?>>
		<div class="tool__group" role="group" aria-labelledby="g-overs">
			<h2 id="g-overs">Overs per side</h2>
			<div class="chips">
				<?php foreach ( array( 8, 10, 12, 15, 20, 50 ) as $dlscalc_o ) : ?>
					<a class="chip" rel="nofollow" data-o="<?php echo (int) $dlscalc_o; ?>" aria-current="<?php echo (string) $dlscalc_o === $dlscalc_in['o'] ? 'true' : 'false'; ?>" href="<?php echo esc_url( dlscalc_dls_permalink( $dlscalc_slug, array_merge( $dlscalc_in, array( 'o' => (string) $dlscalc_o ) ) ) ); ?>"><?php echo (int) $dlscalc_o; ?> overs</a>
				<?php endforeach; ?>
			</div>
			<div class="fields">
				<label class="fld">Overs per side (5 to 50)
					<input type="number" inputmode="numeric" min="5" max="50" name="o" value="<?php echo esc_attr( $dlscalc_in['o'] ); ?>" autocomplete="off">
				</label>
				<label class="fld">Team 1 final score
					<input type="number" inputmode="numeric" min="0" max="999" name="r" value="<?php echo esc_attr( $dlscalc_in['r'] ); ?>" autocomplete="off">
				</label>
				<label class="fld">Overs for Team 2 (if cut before the chase)
					<input type="text" inputmode="decimal" name="c" value="<?php echo esc_attr( $dlscalc_in['c'] ); ?>" placeholder="Same as Team 1" autocomplete="off">
				</label>
			</div>
		</div>
		<div data-submit><button type="submit" class="btn btn--green">Show par table</button></div>
	</form>
	<?php
	dlscalc_dls_result_panel(
		$dlscalc_r,
		$dlscalc_slug,
		$dlscalc_in,
		array(
			'tie'     => 'Par for the full chase',
			't2overs' => 'Overs for Team 2',
		)
	);
	?>
</div>

<section class="block">
	<div class="block__head">
		<div>
			<h2>DLS par score table</h2>
			<?php $dlscalc_on = 'Par after each completed over, for 0 to 9 wickets down. If play ends for good, Team 2 wins when it is above par and the match is tied on par.'; ?>
			<p class="hint" data-out="tablenote" data-on="<?php echo esc_attr( $dlscalc_on ); ?>" data-off="Fix the inputs above to see the par table."><?php echo esc_html( empty( $dlscalc_r['err'] ) ? $dlscalc_on : 'Fix the inputs above to see the par table.' ); ?></p>
		</div>
		<button type="button" class="btn btn--line noprint js-only" data-print>Print par sheet</button>
	</div>
	<?php dlscalc_dls_par_table_html( $dlscalc_r, 'DLS par score by completed overs and wickets down' ); ?>
</section>
<?php dlscalc_par_sheet_foot(); ?>

<section class="panel block">
	<h2>How the par scores were worked out</h2>
	<?php dlscalc_dls_steps( $dlscalc_r ); ?>
</section>

<div class="entry-content tool-prose">
	<?php get_template_part( 'template-parts/content/dls-par-score-table' ); ?>
</div>
<?php
dlscalc_tool_page_close( array( 'dls-calculator', 'dls-target-calculator-t20', 'dls-method', 'run-rate-calculator' ) );
get_footer();
