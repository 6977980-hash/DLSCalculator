<?php
/**
 * Net Run Rate Calculator page (/net-run-rate-calculator/): NRR across matches with the all-out
 * rule, a hand-entered points table and the qualification scenario solver.
 * Owns "net run rate calculator", "nrr calculator", "nrr calculator cricket" and the qualification
 * terms. The formula itself is taught on /how-to-calculate-nrr/.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlscalc_slug = 'net-run-rate-calculator';
$dlscalc_in   = dlscalc_nrr_inputs( $dlscalc_slug );
$dlscalc_c    = dlscalc_nrr_calc(
	array(
		'q' => $dlscalc_in['q'],
		'x' => $dlscalc_in['x'],
	)
);
$dlscalc_s    = dlscalc_nrr_solve( dlscalc_nrr_solve_input( $dlscalc_in ) );
$dlscalc_tab  = 't' === $dlscalc_in['u'] ? 't' : 'c';
$dlscalc_link = dlscalc_nrr_permalink( $dlscalc_slug, $dlscalc_in );
$dlscalc_cerr = $dlscalc_c['err'] ?? '';
$dlscalc_serr = $dlscalc_s['err'] ?? '';

get_header();
dlscalc_tool_page_open(
	'Net Run Rate Calculator (NRR Calculator Cricket)',
	'Work out net run rate across any number of matches, with the all-out rule built in. Then fill in your points table to see exactly how big a win your team needs to move above a rival.'
);
?>
<div class="tabs js-only noprint" role="group" aria-label="Calculator mode">
	<button type="button" class="tab" data-tab="c" aria-pressed="<?php echo 'c' === $dlscalc_tab ? 'true' : 'false'; ?>">Calculate NRR</button>
	<button type="button" class="tab" data-tab="t" aria-pressed="<?php echo 't' === $dlscalc_tab ? 'true' : 'false'; ?>">Qualification scenario</button>
</div>

<section class="pane" data-pane="c"<?php echo 'c' === $dlscalc_tab ? '' : ' data-off'; ?> aria-labelledby="h-calc">
	<h2 class="pane__title" id="h-calc">Calculate net run rate</h2>
	<div class="tool">
		<form class="panel tool__form" method="get" action="<?php echo esc_url( dlscalc_page_url( $dlscalc_slug ) . '#nrr-result' ); ?>" data-form="calc" novalidate>
			<div class="fields">
				<label class="fld fld--narrow">Overs per side
					<input type="number" inputmode="numeric" min="1" max="50" name="q" value="<?php echo esc_attr( $dlscalc_in['q'] ); ?>" autocomplete="off">
				</label>
			</div>
			<p class="hint">Enter every match your team has played. Write overs as 19.4 for 19 overs and 4 balls. If a side was bowled out, tick "all out" and its full quota of overs is counted.</p>
			<?php dlscalc_nrr_match_rows( $dlscalc_in['x'] ); ?>
			<div><button type="button" class="btn btn--line" data-add>+ Add match</button></div>
			<div data-submit><button type="submit" class="btn btn--green">Calculate NRR</button></div>
		</form>
		<section class="result" id="nrr-result" aria-live="polite" aria-label="Net run rate result">
			<p class="alert" data-out="cerr"<?php echo $dlscalc_cerr ? '' : ' hidden'; ?>><?php echo esc_html( $dlscalc_cerr ); ?></p>
			<div>
				<div class="result__label">Net run rate</div>
				<div class="result__big" data-out="cbig"><?php echo esc_html( $dlscalc_cerr ? '–' : $dlscalc_c['nrr'] ); ?></div>
			</div>
			<div class="stats">
				<div class="stat"><span>Scored, runs an over</span><b data-out="cfor"><?php echo esc_html( $dlscalc_cerr ? '–' : $dlscalc_c['forRate'] ); ?></b></div>
				<div class="stat"><span>Conceded, runs an over</span><b data-out="cagainst"><?php echo esc_html( $dlscalc_cerr ? '–' : $dlscalc_c['agRate'] ); ?></b></div>
			</div>
			<p class="result__line" data-out="cline"><?php echo esc_html( $dlscalc_cerr ? 'Check the inputs.' : $dlscalc_c['rf'] . ' runs in ' . dlscalc_dls_overs( $dlscalc_c['bf'] ) . ' overs against ' . $dlscalc_c['ra'] . ' in ' . dlscalc_dls_overs( $dlscalc_c['ba'] ) . ' overs, from ' . $dlscalc_c['counted'] . ( 1 === $dlscalc_c['counted'] ? ' match.' : ' matches.' ) ); ?></p>
			<?php dlscalc_share_buttons( dlscalc_nrr_calc_share( $dlscalc_c ), $dlscalc_link ); ?>
			<p class="result__note">ICC method: overs count in balls, a side bowled out counts its full quota, and no-result matches are left out.</p>
		</section>
	</div>
	<section class="panel block">
		<h2>How this net run rate was worked out</h2>
		<?php dlscalc_nrr_steps( $dlscalc_c, 'csteps' ); ?>
	</section>
</section>

<section class="pane" id="scenario" data-pane="t"<?php echo 't' === $dlscalc_tab ? '' : ' data-off'; ?> aria-labelledby="h-scenario">
	<h2 class="pane__title" id="h-scenario">Qualification scenario: the margin you need</h2>
	<form method="get" action="<?php echo esc_url( dlscalc_page_url( $dlscalc_slug ) . '#scenario' ); ?>" data-form="table" novalidate>
		<input type="hidden" name="u" value="t">
		<div class="panel tool__group">
			<h3 class="panel__title">Points table</h3>
			<p class="hint">Example data below. Replace it with your tournament's table: runs and overs for and against are on the full points table of most leagues. Overs as 78.2 for 78 overs and 2 balls. This page remembers your table on this device.</p>
			<?php dlscalc_nrr_team_rows( $dlscalc_s['teams'] ); ?>
			<div class="btn-row">
				<button type="button" class="btn btn--line" data-add>+ Add team</button>
				<button type="button" class="btn btn--line js-only" data-reset>Clear saved table</button>
			</div>
		</div>
		<div class="tool block">
			<div class="panel tool__form">
				<h3 class="panel__title">Next match</h3>
				<div class="fields">
					<label class="fld">Your team
						<select name="y" data-teams><?php dlscalc_nrr_team_options( $dlscalc_s['teams'], $dlscalc_in['y'] ); ?></select>
					</label>
					<label class="fld">Team to pass
						<select name="v" data-teams><?php dlscalc_nrr_team_options( $dlscalc_s['teams'], $dlscalc_in['v'] ); ?></select>
					</label>
					<label class="fld">Next opponent
						<select name="o" data-teams="Another team"><?php dlscalc_nrr_team_options( $dlscalc_s['teams'], $dlscalc_in['o'], 'Another team' ); ?></select>
					</label>
				</div>
				<fieldset class="radios">
					<legend>Your team will</legend>
					<label class="tick"><input type="radio" name="k" value="f"<?php checked( 'c' !== $dlscalc_in['k'] ); ?>> Bat first</label>
					<label class="tick"><input type="radio" name="k" value="c"<?php checked( 'c' === $dlscalc_in['k'] ); ?>> Chase</label>
				</fieldset>
				<div class="fields">
					<label class="fld"><span data-out="elabel"><?php echo 'c' === $dlscalc_in['k'] ? 'Opponent\'s score to chase' : 'Your expected score'; ?></span>
						<input type="number" inputmode="numeric" min="0" max="999" name="e" value="<?php echo esc_attr( $dlscalc_in['e'] ); ?>" autocomplete="off">
					</label>
					<label class="fld">Overs per side
						<input type="number" inputmode="numeric" min="1" max="50" name="h" value="<?php echo esc_attr( $dlscalc_in['h'] ); ?>" autocomplete="off">
					</label>
					<label class="fld">Points for a win
						<input type="number" inputmode="numeric" min="0" max="10" name="d" value="<?php echo esc_attr( $dlscalc_in['d'] ); ?>" autocomplete="off">
					</label>
					<label class="fld fld--wide">Level on points
						<select name="z">
							<option value=""<?php selected( 'w' !== $dlscalc_in['z'] ); ?>>Net run rate (most leagues)</option>
							<option value="w"<?php selected( 'w' === $dlscalc_in['z'] ); ?>>Most wins first (ICC events)</option>
						</select>
					</label>
				</div>
				<div data-submit><button type="submit" class="btn btn--green">Work out the scenario</button></div>
			</div>
			<section class="result" id="scenario-result" aria-live="polite" aria-label="Scenario result">
				<p class="alert" data-out="serr"<?php echo $dlscalc_serr ? '' : ' hidden'; ?>><?php echo esc_html( $dlscalc_serr ); ?></p>
				<div>
					<div class="result__label" data-out="shead"><?php echo esc_html( dlscalc_nrr_solve_head( $dlscalc_s, $dlscalc_in ) ); ?></div>
					<div class="result__big result__big--word" data-out="sbig"><?php echo esc_html( $dlscalc_serr ? '–' : $dlscalc_s['big'] ); ?></div>
					<p class="result__line" data-out="sline"><?php echo esc_html( $dlscalc_serr ? 'Check the points table and the next match.' : $dlscalc_s['line'] ); ?></p>
				</div>
				<?php dlscalc_share_buttons( dlscalc_nrr_solve_share( $dlscalc_s ), $dlscalc_link ); ?>
				<p class="result__note" data-out="snote"><?php echo esc_html( $dlscalc_serr ? '' : $dlscalc_s['note'] ); ?></p>
			</section>
		</div>
	</form>
	<section class="block">
		<h2>Standings</h2>
		<?php dlscalc_nrr_standings_html( $dlscalc_s ); ?>
	</section>
	<section class="panel block">
		<h2>How the scenario was worked out</h2>
		<?php dlscalc_nrr_steps( $dlscalc_s, 'ssteps' ); ?>
	</section>
</section>

<div class="entry-content tool-prose">
	<?php get_template_part( 'template-parts/content/net-run-rate-calculator' ); ?>
</div>
<?php
dlscalc_tool_page_close( array( 'run-rate-calculator', 'dls-calculator', 'dls-target-calculator-t20', 'live-dls-par-score' ) );
get_footer();
