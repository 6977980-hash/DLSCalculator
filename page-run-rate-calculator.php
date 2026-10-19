<?php
/**
 * Run Rate Calculator page (/run-rate-calculator/): current and required run rate, runs needed per
 * ball, projected scores and an overs to balls converter.
 * Owns "run rate calculator cricket", "required run rate calculator" and "current run rate calculator".
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlscalc_slug  = 'run-rate-calculator';
$dlscalc_in    = dlscalc_nrr_inputs( $dlscalc_slug );
$dlscalc_r     = dlscalc_nrr_run_rate( $dlscalc_in );
$dlscalc_err   = $dlscalc_r['err'] ?? '';
$dlscalc_chase = 'f' !== $dlscalc_in['u'];
$dlscalc_conv  = array(
	'v' => dlscalc_nrr_get( 'v' ) ?? '12.4',
	'j' => dlscalc_nrr_get( 'j' ) ?? '77',
);
$dlscalc_cv    = dlscalc_nrr_convert( $dlscalc_conv );
if ( $dlscalc_err ) {
	$dlscalc_share = 'Run rate calculator:';
} elseif ( 'c' === $dlscalc_r['mode'] && 'on' === $dlscalc_r['state'] ) {
	$dlscalc_share = 'Need ' . $dlscalc_r['need'] . ' off ' . $dlscalc_r['left'] . ' balls: required rate ' . $dlscalc_r['rrr'] . ', current ' . $dlscalc_r['crr'] . '.';
} else {
	$dlscalc_share = $dlscalc_r['label'] . ': ' . $dlscalc_r['big'] . '. ' . $dlscalc_r['line'] . '.';
}

get_header();
dlscalc_tool_page_open(
	'Run Rate Calculator: Current and Required Run Rate',
	'Get the current run rate, the required run rate and how many runs are needed off how many balls. Batting first? See the projected score at different scoring rates.'
);
?>
<div class="tool">
	<form class="panel tool__form" method="get" action="<?php echo esc_url( dlscalc_page_url( $dlscalc_slug ) . '#result' ); ?>" data-form="rr" novalidate<?php echo dlscalc_nrr_has_params() ? '' : ' data-plain'; // Page without link inputs: the copy saved for offline use (audit F46). ?>>
		<fieldset class="seg">
			<legend class="screen-reader-text">Innings</legend>
			<label><input type="radio" name="u" value="c"<?php checked( $dlscalc_chase ); ?>><span>Chasing a target</span></label>
			<label><input type="radio" name="u" value="f"<?php checked( ! $dlscalc_chase ); ?>><span>Batting first</span></label>
		</fieldset>
		<div class="fields">
			<label class="fld">Overs per side
				<input type="number" inputmode="numeric" min="1" max="50" name="n" value="<?php echo esc_attr( $dlscalc_in['n'] ); ?>" autocomplete="off">
			</label>
			<label class="fld" data-target<?php echo $dlscalc_chase ? '' : ' hidden'; ?>>Target
				<input type="number" inputmode="numeric" min="1" max="999" name="t" value="<?php echo esc_attr( $dlscalc_in['t'] ); ?>" autocomplete="off">
			</label>
		</div>
		<div class="fields">
			<label class="fld">Runs scored so far
				<input type="number" inputmode="numeric" min="0" max="999" name="r" value="<?php echo esc_attr( $dlscalc_in['r'] ); ?>" autocomplete="off">
			</label>
			<label class="fld">Overs bowled so far
				<input type="text" inputmode="decimal" name="o" value="<?php echo esc_attr( $dlscalc_in['o'] ); ?>" placeholder="e.g. 12.4" autocomplete="off">
			</label>
		</div>
		<p class="hint">Write overs as 12.4 for 12 overs and 4 balls. The target is the runs needed to win: one more than the other side's score, or the DLS target after rain.</p>
		<div data-submit><button type="submit" class="btn btn--green">Calculate</button></div>
	</form>
	<aside class="result" id="result" aria-live="polite" aria-label="Result">
		<p class="alert" data-out="err"<?php echo $dlscalc_err ? '' : ' hidden'; ?>><?php echo esc_html( $dlscalc_err ); ?></p>
		<div>
			<div class="result__label" data-out="label"><?php echo esc_html( $dlscalc_err ? ( $dlscalc_chase ? 'Required run rate' : 'Projected score at the current rate' ) : $dlscalc_r['label'] ); ?></div>
			<div class="result__big" data-out="big"><?php echo esc_html( $dlscalc_err ? '–' : $dlscalc_r['big'] ); ?></div>
			<div class="result__line" data-out="line"><?php echo esc_html( $dlscalc_err ? 'Check the inputs.' : $dlscalc_r['line'] ); ?></div>
		</div>
		<table class="result__table">
			<tbody data-out="lines">
				<?php foreach ( $dlscalc_err ? array() : $dlscalc_r['lines'] as $dlscalc_line ) : ?>
					<tr><th scope="row"><?php echo esc_html( $dlscalc_line[0] ); ?></th><td><?php echo esc_html( $dlscalc_line[1] ); ?></td></tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php dlscalc_share_buttons( $dlscalc_share, dlscalc_nrr_permalink( $dlscalc_slug, $dlscalc_in ) ); ?>
	</aside>
</div>

<section class="panel block">
	<h2>Overs and balls converter</h2>
	<p class="hint">The number after the point counts balls, not tenths of an over.</p>
	<form class="conv" method="get" action="#conv" id="conv" data-form="conv" novalidate>
		<div class="conv__pair">
			<label class="fld">Overs
				<input type="text" inputmode="decimal" name="v" value="<?php echo esc_attr( $dlscalc_conv['v'] ); ?>" autocomplete="off">
			</label>
			<output class="conv__out<?php echo $dlscalc_cv['ovErr'] ? ' fld__err' : ''; ?>" data-out="ov"><?php echo esc_html( $dlscalc_cv['ov'] ? '= ' . $dlscalc_cv['ov'] : ( $dlscalc_cv['ovErr'] ? $dlscalc_cv['ovErr'] : '= ?' ) ); ?></output>
		</div>
		<div class="conv__pair">
			<label class="fld">Balls
				<input type="number" inputmode="numeric" min="0" name="j" value="<?php echo esc_attr( $dlscalc_conv['j'] ); ?>" autocomplete="off">
			</label>
			<output class="conv__out<?php echo $dlscalc_cv['blErr'] ? ' fld__err' : ''; ?>" data-out="bl"><?php echo esc_html( $dlscalc_cv['bl'] ? '= ' . $dlscalc_cv['bl'] : ( $dlscalc_cv['blErr'] ? $dlscalc_cv['blErr'] : '= ?' ) ); ?></output>
		</div>
	</form>
</section>

<div class="entry-content tool-prose">
	<?php get_template_part( 'template-parts/content/run-rate-calculator' ); ?>
</div>
<?php
dlscalc_tool_page_close( array( 'net-run-rate-calculator', 'strike-rate-calculator', 'dls-target-calculator-t20', 'live-dls-par-score', 'dls-calculator' ) );
get_footer();
