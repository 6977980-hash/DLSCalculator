<?php
/**
 * Live DLS Par Score page (/live-dls-par-score/): the scorer enters Team 2's score over by over
 * and the page shows the DLS par at each point, i.e. who is ahead if rain stops play now.
 * Owns "live dls par score", "live dls calculator" and "who is ahead on dls". Reuses the DLS engine.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlscalc_slug = 'live-dls-par-score';
$dlscalc_in   = dlscalc_dls_inputs( $dlscalc_slug );
$dlscalc_r    = dlscalc_dls_run( $dlscalc_slug, $dlscalc_in );
$dlscalc_err  = $dlscalc_r['err'] ?? '';
$dlscalc_head = dlscalc_live_headline( $dlscalc_r );
$dlscalc_log  = $dlscalc_err ? array() : $dlscalc_r['log'];
$dlscalc_last = $dlscalc_log ? end( $dlscalc_log ) : null;
// The latest entry's warning, or before any entry the one for the whole chase (audit F24).
$dlscalc_warn = $dlscalc_err ? '' : ( $dlscalc_last ? ( $dlscalc_last['warn'] ?? '' ) : $dlscalc_r['warn'] );
// Format sets the minimum overs for a result (ODI 20, T20 5). Links without one go by the overs.
$dlscalc_fmts = array(
	'odi'    => 'ODI',
	't20'    => 'T20',
	't10'    => 'T10',
	'custom' => 'Other format',
);
$dlscalc_fmt  = isset( $dlscalc_fmts[ $dlscalc_in['f'] ] ) ? $dlscalc_in['f'] : ( array( '50' => 'odi', '20' => 't20', '10' => 't10' )[ $dlscalc_in['n'] ] ?? 'custom' );
$dlscalc_rows = array();
// The score log uses the stoppage format (three fields per entry): overs, runs, wickets.
foreach ( dlscalc_dls_stop_fields( $dlscalc_in['x'] ) as $dlscalc_i => $dlscalc_f ) {
	$dlscalc_rows[] = array(
		'at' => $dlscalc_f['at'],
		'r'  => $dlscalc_f['w'],
		'w'  => $dlscalc_f['lost'],
		'e'  => $dlscalc_log[ $dlscalc_i ] ?? null,
	);
}
$dlscalc_rows[] = array(
	'at'    => '',
	'r'     => '',
	'w'     => '',
	'e'     => null,
	'spare' => true,
);
if ( $dlscalc_err ) {
	$dlscalc_share = 'Live DLS par score:';
} elseif ( $dlscalc_last && 'on' === $dlscalc_last['state'] ) {
	$dlscalc_share = 'DLS par after ' . dlscalc_dls_overs( $dlscalc_last['at'] ) . ' overs: ' . $dlscalc_last['par'] . '. Team 2 ' . $dlscalc_last['runs'] . '/' . $dlscalc_last['w'] . ', ' . dlscalc_live_status( $dlscalc_last )[0] . '. Live tracker:';
} else {
	$dlscalc_share = 'DLS target: ' . $dlscalc_r['target'] . ' in ' . dlscalc_dls_overs( $dlscalc_r['allot'] ) . ' overs. Live tracker:';
}

get_header();
dlscalc_tool_page_open(
	'Live DLS Par Score: Who Is Ahead If Rain Stops Play?',
	'Enter the chasing side\'s score after each over and see the DLS par score at that point, so you always know who is ahead if rain stops the match. Built for scorers and captains during a live game.'
);
dlscalc_par_sheet_head();
?>
<div class="tool">
	<form class="panel tool__form" method="get" action="<?php echo esc_url( dlscalc_page_url( $dlscalc_slug ) . '#result' ); ?>" data-tool="live" novalidate>
		<div class="tool__group" role="group" aria-labelledby="g-match">
			<h2 id="g-match">Match</h2>
			<div class="fields">
				<label class="fld">Format
					<select name="f">
						<?php foreach ( $dlscalc_fmts as $dlscalc_v => $dlscalc_t ) : ?>
							<option value="<?php echo esc_attr( $dlscalc_v ); ?>"<?php selected( $dlscalc_fmt, $dlscalc_v ); ?>><?php echo esc_html( $dlscalc_t ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="fld">Overs per side at start
					<input type="text" inputmode="decimal" name="n" value="<?php echo esc_attr( $dlscalc_in['n'] ); ?>" autocomplete="off">
				</label>
				<label class="fld">Team 1 final score
					<input type="number" inputmode="numeric" min="0" max="999" name="r" value="<?php echo esc_attr( $dlscalc_in['r'] ); ?>" autocomplete="off">
				</label>
				<label class="fld">Overs for Team 2 (if cut before the chase)
					<input type="text" inputmode="decimal" name="c" value="<?php echo esc_attr( $dlscalc_in['c'] ); ?>" placeholder="Same as Team 1" autocomplete="off">
				</label>
				<label class="fld">G50
					<select name="g">
						<option value="245"<?php selected( '200' !== $dlscalc_in['g'] ); ?>>245 (full members)</option>
						<option value="200"<?php selected( '200' === $dlscalc_in['g'] ); ?>>200 (women, U19)</option>
					</select>
				</label>
			</div>
		</div>

		<div class="tool__group" role="group" aria-labelledby="g-log">
			<h2 id="g-log">Team 2's score, over by over</h2>
			<p class="hint">After each over, add a row with the overs bowled, Team 2's total and wickets down. You can also add a row in the middle of an over, like 12.3. The page remembers the score on this device.</p>
			<div class="table-wrap">
				<table class="data live">
					<thead><tr><th scope="col">Overs</th><th scope="col">Runs</th><th scope="col">Wkts</th><th scope="col">Par</th><th scope="col">Ahead</th><th scope="col"><span class="screen-reader-text">Remove</span></th></tr></thead>
					<tbody data-rows="x">
						<?php foreach ( $dlscalc_rows as $dlscalc_row ) : ?>
							<?php $dlscalc_st = $dlscalc_row['e'] ? dlscalc_live_status( $dlscalc_row['e'] ) : array( '', '', '' ); ?>
							<tr class="lrow<?php echo empty( $dlscalc_row['spare'] ) ? '' : ' lrow--spare'; ?>">
								<td><input type="text" inputmode="decimal" name="x_at[]" value="<?php echo esc_attr( $dlscalc_row['at'] ); ?>" aria-label="Overs bowled" data-f="at" autocomplete="off"></td>
								<td><input type="number" inputmode="numeric" min="0" max="999" name="x_r[]" value="<?php echo esc_attr( $dlscalc_row['r'] ); ?>" aria-label="Team 2 runs" data-f="r" autocomplete="off"></td>
								<td><input type="number" inputmode="numeric" min="0" max="10" name="x_w[]" value="<?php echo esc_attr( $dlscalc_row['w'] ); ?>" aria-label="Wickets down" data-f="w" autocomplete="off"></td>
								<td class="live__par" data-par><?php echo $dlscalc_row['e'] ? (int) $dlscalc_row['e']['par'] : ''; ?></td>
								<td><span class="pill<?php echo $dlscalc_st[1] ? ' pill--' . esc_attr( $dlscalc_st[1] ) : ''; ?>" data-status title="<?php echo esc_attr( $dlscalc_st[0] ); ?>"><?php echo esc_html( $dlscalc_st[2] ); ?></span></td>
								<td><button type="button" class="xbtn" aria-label="Remove score entry" data-remove>×</button></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<div class="btn-row">
				<button type="button" class="btn btn--green js-only" data-add>+ Add next over</button>
				<button type="button" class="btn btn--line js-only" data-reset>Start a new match</button>
			</div>
		</div>
		<div data-submit><button type="submit" class="btn btn--green">Update par</button></div>
	</form>
	<aside class="result" id="result" aria-live="polite" aria-label="Result">
		<p class="alert" data-out="err"<?php echo $dlscalc_err ? '' : ' hidden'; ?>><?php echo esc_html( $dlscalc_err ); ?></p>
		<p class="alert alert--soft" data-out="warn"<?php echo $dlscalc_warn ? '' : ' hidden'; ?>><?php echo esc_html( $dlscalc_warn ); ?></p>
		<div>
			<div class="result__label" data-out="label"><?php echo esc_html( $dlscalc_head['label'] ); ?></div>
			<div class="result__big<?php echo $dlscalc_head['word'] ? ' result__big--word' : ''; ?>" data-out="big"><?php echo esc_html( $dlscalc_head['big'] ); ?></div>
			<div class="result__line" data-out="line"><?php echo esc_html( $dlscalc_head['line'] ); ?></div>
		</div>
		<div class="stats">
			<div class="stat"><span>Target</span><b data-out="target"><?php echo esc_html( $dlscalc_err ? '–' : (string) $dlscalc_r['target'] ); ?></b></div>
			<div class="stat"><span>Overs for Team 2</span><b data-out="t2overs"><?php echo esc_html( $dlscalc_err ? '–' : dlscalc_dls_overs( $dlscalc_r['allot'] ) ); ?></b></div>
		</div>
		<?php dlscalc_share_buttons( $dlscalc_share, dlscalc_dls_permalink( $dlscalc_slug, $dlscalc_in ) ); ?>
		<p class="result__note">DLS Standard Edition, the ICC's published method. Rain during the chase changes the target: enter the stoppage in the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-calculator' ) ); ?>">DLS calculator</a>.</p>
	</aside>
</div>

<section class="panel block">
	<h2>How the par was worked out</h2>
	<p class="hint">The target for the full chase, then the par if Team 2's innings ended at the latest score entry.</p>
	<ol class="steps" data-out="steps">
		<?php
		$dlscalc_steps = $dlscalc_err ? array( 'Fix the inputs above to see the working.' ) : array_merge( $dlscalc_r['steps'], $dlscalc_r['nowSteps'] );
		foreach ( $dlscalc_steps as $dlscalc_step ) {
			echo '<li>' . esc_html( $dlscalc_step ) . '</li>';
		}
		?>
	</ol>
</section>

<section class="block">
	<div class="block__head">
		<div>
			<h2>Par score for every over and wicket</h2>
			<?php $dlscalc_on = 'Par after each completed over of the chase, for 0 to 9 wickets down. Print it as a backup for the scorers.'; ?>
			<p class="hint" data-out="tablenote" data-on="<?php echo esc_attr( $dlscalc_on ); ?>" data-off="Fix the inputs above to see the par table."><?php echo esc_html( $dlscalc_err ? 'Fix the inputs above to see the par table.' : $dlscalc_on ); ?></p>
		</div>
		<button type="button" class="btn btn--line noprint js-only" data-print>Print par sheet</button>
	</div>
	<?php dlscalc_dls_par_table_html( $dlscalc_r, 'DLS par score by completed overs and wickets down' ); ?>
</section>
<?php dlscalc_par_sheet_foot(); ?>

<div class="entry-content tool-prose">
	<?php get_template_part( 'template-parts/content/live-dls-par-score' ); ?>
</div>
<?php
dlscalc_tool_page_close( array( 'dls-calculator', 'dls-par-score-table', 'dls-target-calculator-t20', 'run-rate-calculator', 'famous-dls-matches' ) );
get_footer();
