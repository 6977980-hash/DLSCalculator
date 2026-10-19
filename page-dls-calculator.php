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
// Format sets the minimum overs for a result (ODI 20, T20 5). Links without one go by the overs.
$dlscalc_fmts = array(
	'odi'    => array( '50', 'ODI (50 overs)' ),
	't20'    => array( '20', 'T20 (20 overs)' ),
	't10'    => array( '10', 'T10 (10 overs)' ),
	'custom' => array( '', 'Other format' ),
);
$dlscalc_f    = isset( $dlscalc_fmts[ $dlscalc_in['f'] ] ) ? $dlscalc_in['f'] : ( array( '50' => 'odi', '20' => 't20', '10' => 't10' )[ $dlscalc_n ] ?? 'custom' );

get_header();
dlscalc_tool_page_open(
	'DLS Calculator: Revised Target for Rain-Hit Matches',
	'Work out the Duckworth-Lewis-Stern target for any rain-affected match, with as many stoppages as you need in either innings. Every result shows its full working, using the ICC Standard Edition table.'
);
dlscalc_par_sheet_head();
dlscalc_dls_scenarios_html( $dlscalc_in );
?>
<div class="tool">
	<form class="panel tool__form" method="get" action="<?php echo esc_url( dlscalc_page_url( $dlscalc_slug ) . '#result' ); ?>" data-tool="calc" novalidate<?php echo dlscalc_dls_has_params() ? '' : ' data-plain'; // Page without link inputs: the copy saved for offline use (audit F46). ?>>
		<div class="tool__group" role="group" aria-labelledby="g-match">
			<h2 id="g-match">Match</h2>
			<div class="fields">
				<label class="fld">Format
					<select name="f" data-preset>
						<?php foreach ( $dlscalc_fmts as $dlscalc_v => $dlscalc_t ) : ?>
							<option value="<?php echo esc_attr( $dlscalc_v ); ?>" data-overs="<?php echo esc_attr( $dlscalc_t[0] ); ?>"<?php selected( $dlscalc_f, $dlscalc_v ); ?>><?php echo esc_html( $dlscalc_t[1] ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="fld">Overs per side at start
					<input type="text" inputmode="decimal" name="n" value="<?php echo esc_attr( $dlscalc_n ); ?>" autocomplete="off"<?php echo dlscalc_dls_invalid( $dlscalc_r, 'n' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>>
					<?php dlscalc_dls_field_err( $dlscalc_r, 'n' ); ?>
				</label>
				<label class="fld">Team 1 final score
					<input type="number" inputmode="numeric" min="0" max="999" name="r" value="<?php echo esc_attr( $dlscalc_in['r'] ); ?>" autocomplete="off"<?php echo dlscalc_dls_invalid( $dlscalc_r, 'r' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>>
					<?php dlscalc_dls_field_err( $dlscalc_r, 'r' ); ?>
				</label>
			</div>
			<p class="hint">Match shortened before it began? Keep the format and type the overs it started with, such as 40 for an ODI. The format sets the minimum overs for a result: 20 in an ODI, 5 in a T20.</p>
			<div class="fields">
				<label class="fld fld--wide">G50 (used when Team 2 has more resources)
					<select data-gsel>
						<option value="245"<?php selected( $dlscalc_gsel, '245' ); ?>>245: full members and first-class</option>
						<option value="200"<?php selected( $dlscalc_gsel, '200' ); ?>>200: women's, associate, U19 and U15</option>
						<option value="custom"<?php selected( $dlscalc_gsel, 'custom' ); ?>>Custom value</option>
					</select>
				</label>
				<label class="fld fld--narrow" data-gwrap<?php echo 'custom' === $dlscalc_gsel ? '' : ' hidden'; ?>>Custom G50
					<input type="text" inputmode="decimal" name="g" value="<?php echo esc_attr( $dlscalc_g ); ?>" autocomplete="off"<?php echo dlscalc_dls_invalid( $dlscalc_r, 'g' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>>
					<?php dlscalc_dls_field_err( $dlscalc_r, 'g' ); ?>
				</label>
			</div>
			<?php // Which G50 applies depends on the competition (audit F59). ?>
			<p class="hint">G50 is set by the playing conditions of your competition. The ICC uses 245 for matches involving full members and between first-class teams, and 200 for women's, associate, Under-19 and Under-15 internationals; the ECB's 2026 methodology uses 245 for Under-19 internationals. Local leagues may set their own value.</p>
		</div>

		<div class="tool__group" role="group" aria-labelledby="g-t1">
			<h2 id="g-t1">Team 1 stoppages</h2>
			<p class="hint">Add one row per stoppage. If rain ended the innings, enter all the overs that were left as overs lost. Write overs as 32.4 for 32 overs and 4 balls.</p>
			<?php dlscalc_dls_stop_rows( 'a', $dlscalc_in['a'], 'Team 1', $dlscalc_r ); ?>
			<div><button type="button" class="btn btn--line" data-add>+ Add Team 1 stoppage</button></div>
		</div>

		<div class="tool__group" role="group" aria-labelledby="g-t2">
			<h2 id="g-t2">Team 2 innings</h2>
			<label class="fld fld--narrow">Overs for Team 2 at start of innings
				<input type="text" inputmode="decimal" name="c" value="<?php echo esc_attr( $dlscalc_in['c'] ); ?>" placeholder="<?php echo esc_attr( $dlscalc_n ); ?>" autocomplete="off"<?php echo dlscalc_dls_invalid( $dlscalc_r, 'c' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>>
				<?php dlscalc_dls_field_err( $dlscalc_r, 'c' ); ?>
			</label>
			<?php dlscalc_dls_stop_rows( 'b', $dlscalc_in['b'], 'Team 2', $dlscalc_r ); ?>
			<div><button type="button" class="btn btn--line" data-add>+ Add Team 2 stoppage</button></div>
			<label class="fld fld--narrow">Penalty runs to Team 1 during the chase (optional)
				<input type="number" inputmode="numeric" min="0" max="99" name="k" value="<?php echo esc_attr( $dlscalc_in['k'] ); ?>" placeholder="0" autocomplete="off"<?php echo dlscalc_dls_invalid( $dlscalc_r, 'k' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>>
				<?php dlscalc_dls_field_err( $dlscalc_r, 'k' ); ?>
			</label>
			<p class="hint">Penalty runs given to the fielding side while Team 2 bats raise the target and every par score by the same number (ICC DLS rules, section 6). Penalty runs Team 1 earned while batting are part of its score, and penalty runs to Team 2 go on Team 2's score without changing the target.</p>
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
			<h2>Par score for the rest of the chase</h2>
			<?php $dlscalc_on = 'Par after each completed over from here, for 0 to 9 wickets down. If play ends for good, Team 2 wins when it is above par.'; ?>
			<p class="hint" data-out="tablenote" data-on="<?php echo esc_attr( $dlscalc_on ); ?>" data-off="No overs left in the chase, so there is no par table."><?php echo esc_html( empty( $dlscalc_r['err'] ) && $dlscalc_r['rows'] ? $dlscalc_on : 'No overs left in the chase, so there is no par table.' ); ?></p>
		</div>
	</div>
	<?php dlscalc_dls_par_table_html( $dlscalc_r, 'DLS par score for Team 2 by completed overs and wickets down' ); ?>
</section>
<?php dlscalc_par_sheet_foot(); ?>
<?php dlscalc_embed_box(); ?>

<div class="entry-content tool-prose">
	<?php get_template_part( 'template-parts/content/dls-calculator' ); ?>
</div>
<?php
dlscalc_tool_page_close( array( 'dls-target-calculator-t20', 'dls-par-score-table', 'dls-method', 'net-run-rate-calculator', 'famous-dls-matches' ) );
get_footer();
