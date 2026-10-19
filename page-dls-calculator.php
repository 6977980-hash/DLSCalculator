<?php
/**
 * DLS Calculator page (/dls-calculator/): the full calculator with stoppages in both innings.
 * Owns the keywords "dls calculator", "duckworth lewis calculator", "dls target calculator"
 * and "dls calculator odi". The method itself is explained on /dls-method/.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlscalc_slug = 'dls-calculator';
$dlscalc_in   = dlscalc_dls_inputs( $dlscalc_slug );
$dlscalc_r    = dlscalc_dls_run( $dlscalc_slug, $dlscalc_in );
$dlscalc_n    = $dlscalc_in['n'];
$dlscalc_g    = $dlscalc_in['g'];
$dlscalc_gsel = in_array( $dlscalc_g, array( '245', '200' ), true ) ? $dlscalc_g : 'custom';

get_header();
dlscalc_tool_page_open(
	'DLS Calculator: Revised Target for Rain-Hit Matches',
	'Work out the Duckworth-Lewis-Stern target for any rain-affected match, with as many stoppages as you need in either innings. Every result shows its full working, using the ICC Standard Edition table.'
);
dlscalc_par_sheet_head();
?>
<div class="tool">
	<form class="panel tool__form" method="get" action="<?php echo esc_url( dlscalc_page_url( $dlscalc_slug ) . '#result' ); ?>" data-tool="calc" novalidate>
		<div class="tool__group" role="group" aria-labelledby="g-match">
			<h2 id="g-match">Match</h2>
			<div class="fields">
				<label class="fld">Format
					<select data-preset>
						<?php foreach ( array( '50' => 'ODI (50 overs)', '20' => 'T20 (20 overs)', '10' => 'T10 (10 overs)', 'custom' => 'Custom overs' ) as $dlscalc_v => $dlscalc_t ) : ?>
							<option value="<?php echo esc_attr( $dlscalc_v ); ?>"<?php selected( in_array( $dlscalc_n, array( '50', '20', '10' ), true ) ? $dlscalc_n : 'custom', $dlscalc_v ); ?>><?php echo esc_html( $dlscalc_t ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="fld">Overs per side at start
					<input type="text" inputmode="decimal" name="n" value="<?php echo esc_attr( $dlscalc_n ); ?>" autocomplete="off">
				</label>
				<label class="fld">Team 1 final score
					<input type="number" inputmode="numeric" min="0" max="999" name="r" value="<?php echo esc_attr( $dlscalc_in['r'] ); ?>" autocomplete="off">
				</label>
			</div>
			<div class="fields">
				<label class="fld fld--wide">G50 (used when Team 2 has more resources)
					<select data-gsel>
						<option value="245"<?php selected( $dlscalc_gsel, '245' ); ?>>245: men's internationals and first-class</option>
						<option value="200"<?php selected( $dlscalc_gsel, '200' ); ?>>200: women's, associate and U19 (ICC)</option>
						<option value="custom"<?php selected( $dlscalc_gsel, 'custom' ); ?>>Custom value</option>
					</select>
				</label>
				<label class="fld fld--narrow" data-gwrap<?php echo 'custom' === $dlscalc_gsel ? '' : ' hidden'; ?>>Custom G50
					<input type="text" inputmode="decimal" name="g" value="<?php echo esc_attr( $dlscalc_g ); ?>" autocomplete="off">
				</label>
			</div>
		</div>

		<div class="tool__group" role="group" aria-labelledby="g-t1">
			<h2 id="g-t1">Team 1 stoppages</h2>
			<p class="hint">Add one row per stoppage. If rain ended the innings, enter all the overs that were left as overs lost. Write overs as 32.4 for 32 overs and 4 balls.</p>
			<?php dlscalc_dls_stop_rows( 'a', $dlscalc_in['a'], 'Team 1' ); ?>
			<div><button type="button" class="btn btn--line" data-add>+ Add Team 1 stoppage</button></div>
		</div>

		<div class="tool__group" role="group" aria-labelledby="g-t2">
			<h2 id="g-t2">Team 2 innings</h2>
			<label class="fld fld--narrow">Overs for Team 2 at start of innings
				<input type="text" inputmode="decimal" name="c" value="<?php echo esc_attr( $dlscalc_in['c'] ); ?>" placeholder="<?php echo esc_attr( $dlscalc_n ); ?>" autocomplete="off">
			</label>
			<?php dlscalc_dls_stop_rows( 'b', $dlscalc_in['b'], 'Team 2' ); ?>
			<div><button type="button" class="btn btn--line" data-add>+ Add Team 2 stoppage</button></div>
		</div>
		<div data-submit><button type="submit" class="btn btn--green">Calculate</button></div>
	</form>
	<?php
	dlscalc_dls_result_panel(
		$dlscalc_r,
		$dlscalc_slug,
		$dlscalc_in,
		array(
			'tie'     => 'Par to tie',
			't2overs' => 'Overs for Team 2',
			'r1'      => 'Team 1 resources (R1)',
			'r2'      => 'Team 2 resources (R2)',
		)
	);
	?>
</div>

<section class="panel block">
	<h2>How this target was worked out</h2>
	<p class="hint">Every number comes from the ICC Standard Edition resource table, ball by ball.</p>
	<?php dlscalc_dls_steps( $dlscalc_r ); ?>
</section>

<section class="block">
	<div class="block__head">
		<div>
			<h2>Par score table for the rest of the chase</h2>
			<?php $dlscalc_on = 'Par after each completed over from here, for 0 to 9 wickets down. If play ends for good, Team 2 wins when it is above par.'; ?>
			<p class="hint" data-out="tablenote" data-on="<?php echo esc_attr( $dlscalc_on ); ?>" data-off="No overs left in the chase, so there is no par table."><?php echo esc_html( empty( $dlscalc_r['err'] ) && $dlscalc_r['rows'] ? $dlscalc_on : 'No overs left in the chase, so there is no par table.' ); ?></p>
		</div>
	</div>
	<?php dlscalc_dls_par_table_html( $dlscalc_r, 'DLS par score for Team 2 by completed overs and wickets down' ); ?>
</section>
<?php dlscalc_par_sheet_foot(); ?>

<div class="entry-content tool-prose">
	<?php get_template_part( 'template-parts/content/dls-calculator' ); ?>
</div>
<?php
dlscalc_tool_page_close( array( 'dls-target-calculator-t20', 'dls-par-score-table', 'dls-method', 'net-run-rate-calculator', 'famous-dls-matches' ) );
get_footer();
