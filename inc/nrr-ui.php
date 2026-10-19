<?php
/**
 * NRR and run rate tools on the page: reading inputs from the URL (result permalinks) and from
 * forms sent without JavaScript, rendering match rows, the points table and results in PHP, and
 * loading the tool script only where needed.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The NRR tool pages: slug => tool name used by the script (data-tool) and default inputs.
 * Parameter names avoid WordPress's own query variables (s, m, p, w and others).
 *
 * NRR page: u tab ("t" opens the qualification scenario), q overs per side and x matches for the
 * NRR calculator; t points table, y your team, v team to pass, o next opponent (0 = not in the
 * table), k bat first ("f") or chase ("c"), e score, h overs per side, d points for a win and
 * z tie-break ("w": wins before net run rate, as at ICC events; empty: net run rate) for the scenario. Run rate page: u "c" chasing or "f" batting first, n overs per side, t target,
 * r runs so far, o overs bowled. Strike rate page: r runs, b balls faced, i innings, o not outs,
 * f fours, x sixes, g target strike rate. Bowling page: o overs, r runs conceded, k wickets,
 * d dot balls. The stats pages have optional fields, so "blank" makes a link leave out what it
 * does not carry instead of filling in the sample figures.
 *
 * @return array<string, array{tool: string, blank?: bool, defaults: array<string, string>}>
 */
function dlscalc_nrr_tools() {
	return array(
		'net-run-rate-calculator'    => array(
			'tool'     => 'nrr',
			'defaults' => array(
				'u' => '',
				'q' => '20',
				'x' => '168-20-151-20_142-18.3a-145-20_155-20-139-17.2a',
				't' => 'Team A~4~3~6~640~78.2~600~80_Team B~4~2~4~610~80~590~79.3_Team C~4~2~4~580~80~575~80_Team D~4~1~2~560~80~625~79',
				'y' => '3',
				'v' => '1',
				'o' => '1',
				'k' => 'f',
				'e' => '180',
				'h' => '20',
				'd' => '2',
				'z' => '',
			),
		),
		'run-rate-calculator'        => array(
			'tool'     => 'runRate',
			'defaults' => array(
				'u' => 'c',
				'n' => '20',
				't' => '181',
				'r' => '92',
				'o' => '12.4',
			),
		),
		'strike-rate-calculator'     => array(
			'tool'     => 'batting',
			'blank'    => true,
			'defaults' => array(
				'r' => '182',
				'b' => '140',
				'i' => '',
				'o' => '',
				'f' => '',
				'x' => '',
				'g' => '',
			),
		),
		'bowling-average-calculator' => array(
			'tool'     => 'bowling',
			'blank'    => true,
			'defaults' => array(
				'o' => '38.4',
				'r' => '212',
				'k' => '9',
				'd' => '',
			),
		),
	);
}

/**
 * Whether the current page is one of the NRR tool pages (NRR, run rate and the stats calculators).
 *
 * @return bool
 */
function dlscalc_is_nrr_page() {
	return isset( dlscalc_nrr_tools()[ dlscalc_current_slug() ] );
}

/**
 * One value from the query string, or null.
 *
 * @param string $key Parameter.
 * @return string|null
 */
function dlscalc_nrr_get( $key ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only calculator inputs.
	return isset( $_GET[ $key ] ) && is_string( $_GET[ $key ] ) ? substr( sanitize_text_field( wp_unslash( $_GET[ $key ] ) ), 0, 2000 ) : null;
}

/**
 * Array fields from a form sent without JavaScript, e.g. x_rf[]. Missing keys come back empty.
 *
 * @param string $key Parameter.
 * @return string[]
 */
function dlscalc_nrr_get_rows( $key ) {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only calculator inputs.
	if ( ! isset( $_GET[ $key ] ) || ! is_array( $_GET[ $key ] ) ) {
		return array();
	}
	return array_map( 'sanitize_text_field', wp_unslash( $_GET[ $key ] ) );
	// phpcs:enable
}

/**
 * Matches sent by the no-JavaScript form, as a permalink value.
 *
 * @return string|null Null when the form sent no rows.
 */
function dlscalc_nrr_matches_from_rows() {
	$rf = dlscalc_nrr_get_rows( 'x_rf' );
	if ( ! $rf ) {
		return null;
	}
	$of    = dlscalc_nrr_get_rows( 'x_of' );
	$ra    = dlscalc_nrr_get_rows( 'x_ra' );
	$oa    = dlscalc_nrr_get_rows( 'x_oa' );
	$fa    = dlscalc_nrr_get_rows( 'x_fa' );
	$aa    = dlscalc_nrr_get_rows( 'x_aa' );
	$mo    = dlscalc_nrr_get_rows( 'x_mo' );
	$nr    = dlscalc_nrr_get_rows( 'x_nr' );
	$parts = array();
	foreach ( array_slice( $rf, 0, DLSCALC_MATCHES_MAX, true ) as $i => $runs ) {
		// "-" and "_" separate fields and matches, so a typed minus travels as "−" (see nrr-tools.js).
		$row = str_replace( array( '-', '_' ), array( '−', '' ), array( trim( $runs ), trim( $of[ $i ] ?? '' ), trim( $ra[ $i ] ?? '' ), trim( $oa[ $i ] ?? '' ) ) );
		if ( '' === implode( '', $row ) ) {
			continue;
		}
		$row[1] .= isset( $fa[ $i ] ) ? 'a' : '';
		$row[3] .= isset( $aa[ $i ] ) ? 'a' : '';
		$cut     = str_replace( array( '-', '_' ), '', trim( $mo[ $i ] ?? '' ) );
		$parts[] = implode( '-', $row ) . ( '' === $cut ? '' : '-' . $cut ) . ( isset( $nr[ $i ] ) ? '-n' : '' );
	}
	return implode( '_', $parts );
}

/**
 * Points table sent by the no-JavaScript form, as a permalink value.
 *
 * @return string|null Null when the form sent no rows.
 */
function dlscalc_nrr_teams_from_rows() {
	$names = dlscalc_nrr_get_rows( 't_n' );
	if ( ! $names ) {
		return null;
	}
	$cols  = array( 't_p', 't_w', 't_pts', 't_rf', 't_of', 't_ra', 't_oa' );
	$data  = array_map( 'dlscalc_nrr_get_rows', $cols );
	$parts = array();
	foreach ( array_slice( $names, 0, DLSCALC_TEAMS_MAX, true ) as $i => $name ) {
		$row = array( dlscalc_nrr_clean_name( $name ) );
		foreach ( $data as $col ) {
			$row[] = str_replace( array( '~', '_' ), '', trim( $col[ $i ] ?? '' ) );
		}
		if ( '' === implode( '', $row ) ) {
			continue;
		}
		$parts[] = implode( '~', $row );
	}
	return implode( '_', $parts );
}

/**
 * Inputs for a tool: defaults overlaid with whatever the URL carries.
 *
 * @param string $slug Tool page slug.
 * @return array<string, string>
 */
function dlscalc_nrr_inputs( $slug ) {
	$tool = dlscalc_nrr_tools()[ $slug ];
	$in   = $tool['defaults'];
	if ( ! dlscalc_nrr_has_params() ) {
		return $in;
	}
	if ( ! empty( $tool['blank'] ) ) {
		$in = array_fill_keys( array_keys( $in ), '' );
	}
	$rows = array(
		'x' => 'net-run-rate-calculator' === $slug ? dlscalc_nrr_matches_from_rows() : null,
		't' => 'net-run-rate-calculator' === $slug ? dlscalc_nrr_teams_from_rows() : null,
	);
	foreach ( array_keys( $in ) as $key ) {
		$value = $rows[ $key ] ?? dlscalc_nrr_get( $key );
		if ( null !== $value ) {
			$in[ $key ] = $value;
		}
	}
	return $in;
}

/**
 * Whether the URL carries calculator inputs (a result permalink).
 *
 * @return bool
 */
function dlscalc_nrr_has_params() {
	foreach ( array( 'u', 'q', 'x', 't', 'y', 'v', 'o', 'k', 'e', 'h', 'd', 'z', 'n', 'r', 'j', 'b', 'i', 'f', 'g', 'x_rf', 't_n' ) as $key ) {
		if ( isset( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}
	}
	return false;
}

/**
 * Solver inputs as the engine takes them: the scenario's overs per side travel as "h".
 *
 * @param array $in Page inputs.
 * @return array
 */
function dlscalc_nrr_solve_input( $in ) {
	return array(
		't' => $in['t'],
		'y' => $in['y'],
		'v' => $in['v'],
		'o' => $in['o'],
		'k' => $in['k'],
		'e' => $in['e'],
		'q' => $in['h'],
		'd' => $in['d'],
		'z' => $in['z'],
	);
}

/**
 * Result permalink: the clean page URL plus the inputs.
 *
 * @param string $slug Tool page slug.
 * @param array  $in   Inputs.
 * @return string
 */
function dlscalc_nrr_permalink( $slug, $in ) {
	return add_query_arg( array_map( 'rawurlencode', array_filter( $in, 'strlen' ) ), dlscalc_page_url( $slug ) );
}

/*
 * Parameter URLs stay indexable with WordPress's canonical to the clean page; see inc/dls-ui.php.
 */

/**
 * NRR engine and tool script, deferred, only on the NRR, run rate and stats pages.
 */
function dlscalc_nrr_scripts() {
	if ( ! dlscalc_is_nrr_page() ) {
		return;
	}
	$args = array(
		'strategy'  => 'defer',
		'in_footer' => true,
	);
	wp_enqueue_script( 'dlscalc-nrr-engine', DLSCALC_URI . '/assets/js/nrr-engine.js', array(), dlscalc_asset_version( 'assets/js/nrr-engine.js' ), $args );
	wp_enqueue_script( 'dlscalc-nrr-tools', DLSCALC_URI . '/assets/js/nrr-tools.js', array( 'dlscalc-nrr-engine' ), dlscalc_asset_version( 'assets/js/nrr-tools.js' ), $args );
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'dlscalc_nrr_scripts', 100 );

/**
 * Fields of one match row, for the inputs: overs without the all-out mark, the overs per side
 * when rain cut that match, plus the flags.
 *
 * @param string $part One match from the permalink value.
 * @return array{rf: string, of: string, fa: bool, ra: string, oa: string, aa: bool, mo: string, nr: bool}
 */
function dlscalc_nrr_match_fields( $part ) {
	$x     = explode( '-', $part );
	$of    = $x[1] ?? '';
	$oa    = $x[3] ?? '';
	$extra = array_slice( $x, 4 );
	$mo    = array_values( array_diff( $extra, array( 'n', '' ) ) );
	return array(
		'rf' => $x[0] ?? '',
		'of' => rtrim( $of, 'a' ),
		'fa' => 'a' === substr( $of, -1 ),
		'ra' => $x[2] ?? '',
		'oa' => rtrim( $oa, 'a' ),
		'aa' => 'a' === substr( $oa, -1 ),
		'mo' => $mo[0] ?? '',
		'nr' => in_array( 'n', $extra, true ),
	);
}

/**
 * Match rows, plus one spare blank row for visitors without JavaScript (the script removes it and
 * adds rows on demand instead).
 *
 * @param string $value Matches permalink value.
 */
function dlscalc_nrr_match_rows( $value ) {
	$rows = array();
	foreach ( '' === $value ? array() : explode( '_', $value ) as $part ) {
		$rows[] = dlscalc_nrr_match_fields( $part );
	}
	$rows[] = dlscalc_nrr_match_fields( '' ) + array( 'spare' => true );
	echo '<div class="rows" data-rows="x">';
	foreach ( $rows as $i => $row ) {
		dlscalc_nrr_match_row( $i, $row );
	}
	echo '</div>';
}

/**
 * One match row. The script clones the spare row's markup for new rows, so keep it generic.
 *
 * @param int   $i   Row index.
 * @param array $row Values.
 */
function dlscalc_nrr_match_row( $i, $row ) {
	$check = function ( $name, $on, $label ) use ( $i ) {
		printf(
			'<label class="tick"><input type="checkbox" name="%s[%d]" value="1" data-f="%s"%s> %s</label>',
			esc_attr( 'x_' . $name ),
			(int) $i,
			esc_attr( $name ),
			$on ? ' checked' : '',
			esc_html( $label )
		); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed string.
	};
	?>
	<fieldset class="mrow<?php echo empty( $row['spare'] ) ? '' : ' mrow--spare'; ?>">
		<legend>Match <span class="mrow__n"><?php echo (int) $i + 1; ?></span></legend>
		<div class="mrow__side">
			<label class="fld">Runs scored
				<input type="number" inputmode="numeric" min="0" max="999" name="x_rf[<?php echo (int) $i; ?>]" value="<?php echo esc_attr( $row['rf'] ); ?>" data-f="rf" autocomplete="off">
			</label>
			<label class="fld">Overs faced
				<input type="text" inputmode="decimal" name="x_of[<?php echo (int) $i; ?>]" value="<?php echo esc_attr( $row['of'] ); ?>" placeholder="e.g. 19.4" data-f="of" autocomplete="off">
			</label>
		</div>
		<div class="mrow__side">
			<label class="fld">Runs conceded
				<input type="number" inputmode="numeric" min="0" max="999" name="x_ra[<?php echo (int) $i; ?>]" value="<?php echo esc_attr( $row['ra'] ); ?>" data-f="ra" autocomplete="off">
			</label>
			<label class="fld">Overs bowled
				<input type="text" inputmode="decimal" name="x_oa[<?php echo (int) $i; ?>]" value="<?php echo esc_attr( $row['oa'] ); ?>" placeholder="e.g. 20" data-f="oa" autocomplete="off">
			</label>
		</div>
		<label class="fld mrow__cut">Overs per side in this match, if rain cut it
			<input type="text" inputmode="decimal" name="x_mo[<?php echo (int) $i; ?>]" value="<?php echo esc_attr( $row['mo'] ); ?>" placeholder="Full overs" data-f="mo" autocomplete="off">
		</label>
		<div class="mrow__end">
			<?php $check( 'fa', $row['fa'], 'We were all out' ); ?>
			<?php $check( 'aa', $row['aa'], 'They were all out' ); ?>
			<?php $check( 'nr', $row['nr'], 'No result' ); ?>
			<button type="button" class="xbtn" aria-label="Remove match" data-remove>×</button>
		</div>
	</fieldset>
	<?php
}

/**
 * Points table rows, with the NRR column worked out, plus a spare blank row without JavaScript.
 *
 * @param array[] $teams Parsed teams.
 */
function dlscalc_nrr_team_rows( $teams ) {
	$cols   = array(
		'p'   => array( 'Played', 'numeric' ),
		'w'   => array( 'Won', 'numeric' ),
		'pts' => array( 'Points', 'numeric' ),
		'rf'  => array( 'Runs for', 'numeric' ),
		'of'  => array( 'Overs faced', 'decimal' ),
		'ra'  => array( 'Runs against', 'numeric' ),
		'oa'  => array( 'Overs bowled', 'decimal' ),
	);
	$rows   = $teams;
	$rows[] = array(
		'name'  => '',
		'raw'   => array_fill_keys( array_keys( $cols ), '' ),
		'nrr'   => '–',
		'spare' => true,
	);
	?>
	<div class="table-wrap" tabindex="0" role="region" aria-label="Points table">
		<table class="data ptable">
			<thead><tr><th scope="col">Team</th><?php foreach ( $cols as $col ) : ?><th scope="col"><?php echo esc_html( $col[0] ); ?></th><?php endforeach; ?><th scope="col">NRR</th><th scope="col"><span class="screen-reader-text">Remove</span></th></tr></thead>
			<tbody data-rows="t">
				<?php foreach ( $rows as $team ) : ?>
					<tr class="trow<?php echo empty( $team['spare'] ) ? '' : ' trow--spare'; ?>">
						<td class="ptable__name"><input type="text" name="t_n[]" value="<?php echo esc_attr( $team['name'] ); ?>" aria-label="Team name" data-f="n" maxlength="30" autocomplete="off"></td>
						<?php foreach ( $cols as $key => $col ) : ?>
							<td><input type="<?php echo 'numeric' === $col[1] ? 'number' : 'text'; ?>" inputmode="<?php echo esc_attr( $col[1] ); ?>" name="t_<?php echo esc_attr( $key ); ?>[]" value="<?php echo esc_attr( $team['raw'][ $key ] ); ?>" aria-label="<?php echo esc_attr( $col[0] ); ?>" data-f="<?php echo esc_attr( $key ); ?>" autocomplete="off"></td>
						<?php endforeach; ?>
						<td class="ptable__nrr" data-nrr><?php echo esc_html( $team['nrr'] ); ?></td>
						<td><button type="button" class="xbtn" aria-label="Remove team" data-remove>×</button></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Caption of the standings table, naming the tie-break in use.
 *
 * @param string $z "w" for wins before net run rate.
 * @return string
 */
function dlscalc_nrr_standings_caption( $z ) {
	return 'w' === $z ? 'Standings by points, then wins, then net run rate' : 'Standings by points, then net run rate';
}

/**
 * Standings: teams sorted by points, then (if chosen) wins, then net run rate.
 *
 * @param array $s Solver result (teams and order).
 */
function dlscalc_nrr_standings_html( $s ) {
	?>
	<div class="table-wrap" tabindex="0" role="region" aria-label="Standings">
		<table class="data data--num standings">
			<caption data-out="scap"><?php echo esc_html( dlscalc_nrr_standings_caption( $s['z'] ?? '' ) ); ?></caption>
			<thead><tr><th scope="col">Pos</th><th scope="col">Team</th><th scope="col">Played</th><th scope="col">Won</th><th scope="col">Points</th><th scope="col">NRR</th></tr></thead>
			<tbody data-out="standings">
				<?php
				foreach ( $s['order'] as $pos => $i ) {
					$t = $s['teams'][ $i ];
					printf(
						'<tr><td>%d</td><th scope="row">%s</th><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
						(int) $pos + 1,
						esc_html( $t['name'] ),
						esc_html( (string) ( $t['p'] ?? '–' ) ),
						esc_html( (string) ( $t['w'] ?? '–' ) ),
						esc_html( (string) ( $t['pts'] ?? '–' ) ),
						esc_html( $t['nrr'] )
					);
				}
				?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Options for a team picker.
 *
 * @param array[] $teams    Teams.
 * @param string  $selected Selected value (1-based index).
 * @param string  $none     Label of a "0" option, or '' for none.
 */
function dlscalc_nrr_team_options( $teams, $selected, $none = '' ) {
	if ( $none ) {
		printf( '<option value="0"%s>%s</option>', selected( $selected, '0', false ), esc_html( $none ) );
	}
	foreach ( $teams as $i => $team ) {
		printf( '<option value="%d"%s>%s</option>', (int) $i + 1, selected( $selected, (string) ( $i + 1 ), false ), esc_html( $team['name'] ) );
	}
}

/**
 * Share buttons for a result panel.
 *
 * @param string $text Share text before the link.
 * @param string $url  Permalink.
 */
function dlscalc_share_buttons( $text, $url ) {
	?>
	<div class="share noprint">
		<a class="btn btn--gold" data-out="wa" href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( $text . ' ' . $url ) ); ?>" target="_blank" rel="noopener">
			<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20l1.3-3.9A8 8 0 1 1 8 19z"/></svg>
			Share on WhatsApp
		</a>
		<button type="button" class="btn btn--ghost js-only" data-copy>Copy link</button>
		<button type="button" class="btn btn--ghost js-only" data-print>Print</button>
	</div>
	<?php
}

/**
 * Numbered working from a result.
 *
 * @param array  $r   Engine result.
 * @param string $key data-out key.
 */
function dlscalc_nrr_steps( $r, $key ) {
	echo '<ol class="steps" data-out="' . esc_attr( $key ) . '">';
	foreach ( empty( $r['err'] ) ? $r['steps'] : array( 'Fix the inputs above to see the working.' ) as $step ) {
		echo '<li>' . esc_html( $step ) . '</li>';
	}
	echo '</ol>';
}

/**
 * Share text for the NRR calculator.
 *
 * @param array $r Engine result.
 * @return string
 */
function dlscalc_nrr_calc_share( $r ) {
	return empty( $r['err'] ) ? 'Net run rate: ' . $r['nrr'] . ' (' . $r['rf'] . ' runs in ' . dlscalc_dls_overs( $r['bf'] ) . ' overs, ' . $r['ra'] . ' conceded in ' . dlscalc_dls_overs( $r['ba'] ) . '). Full working:' : 'Net run rate calculator:';
}

/**
 * Share text for the qualification scenario.
 *
 * @param array $s Solver result.
 * @return string
 */
function dlscalc_nrr_solve_share( $s ) {
	return empty( $s['err'] ) ? $s['me'] . ' vs ' . $s['rv'] . ': ' . $s['line'] . ' Full working:' : 'NRR qualification calculator:';
}

/**
 * Headline over the scenario result.
 *
 * @param array $s Solver result.
 * @param array $in Inputs.
 * @return string
 */
function dlscalc_nrr_solve_head( $s, $in ) {
	if ( ! empty( $s['err'] ) ) {
		return 'Qualification scenario';
	}
	return 'f' === $s['bat'] ? 'Batting first with ' . (int) $in['e'] . ', ' . $s['me'] . ' needs' : 'Chasing ' . ( (int) $in['e'] + 1 ) . ', ' . $s['me'] . ' needs';
}

/**
 * One number input of a stats calculator, with its error message when the engine flags it.
 *
 * @param array  $r     Engine result.
 * @param array  $in    Inputs.
 * @param string $name  Parameter.
 * @param string $label Field label.
 * @param string $extra Extra input attributes (type, inputmode, min, placeholder).
 */
function dlscalc_stats_field( $r, $in, $name, $label, $extra = 'type="number" inputmode="numeric" min="0"' ) {
	?>
	<label class="fld"><?php echo esc_html( $label ); ?>
		<input <?php echo $extra; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed attribute strings from the templates. ?> name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $in[ $name ] ); ?>" autocomplete="off"<?php echo dlscalc_dls_invalid( $r, $name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>>
		<?php dlscalc_dls_field_err( $r, $name ); ?>
	</label>
	<?php
}

/**
 * Share text for a stats result, the same in stats-page JS.
 *
 * @param array  $r    Engine result.
 * @param string $name Calculator name for an error.
 * @return string
 */
function dlscalc_stats_share( $r, $name ) {
	return empty( $r['err'] ) ? $r['label'] . ': ' . $r['big'] . '. ' . $r['line'] . '.' : $name . ':';
}

/**
 * Result panel of a stats calculator: headline number, one line and the table of other figures.
 *
 * @param array  $r     Engine result.
 * @param string $slug  Page slug.
 * @param array  $in    Inputs.
 * @param string $label Headline label while the inputs have an error.
 * @param string $name  Calculator name for the share text.
 */
function dlscalc_stats_result( $r, $slug, $in, $label, $name ) {
	$err = $r['err'] ?? '';
	?>
	<aside class="result" id="result" aria-live="polite" aria-label="Result">
		<p class="alert" data-out="err"<?php echo $err ? '' : ' hidden'; ?>><?php echo esc_html( $err ); ?></p>
		<div>
			<div class="result__label" data-out="label"><?php echo esc_html( $err ? $label : $r['label'] ); ?></div>
			<div class="result__big" data-out="big"><?php echo esc_html( $err ? '–' : $r['big'] ); ?></div>
			<div class="result__line" data-out="line"><?php echo esc_html( $err ? 'Check the inputs.' : $r['line'] ); ?></div>
		</div>
		<table class="result__table">
			<tbody data-out="lines">
				<?php foreach ( $err ? array() : $r['lines'] as $line ) : ?>
					<tr><th scope="row"><?php echo esc_html( $line[0] ); ?></th><td><?php echo esc_html( $line[1] ); ?></td></tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php dlscalc_share_buttons( dlscalc_stats_share( $r, $name ), dlscalc_nrr_permalink( $slug, $in ) ); ?>
	</aside>
	<?php
}
