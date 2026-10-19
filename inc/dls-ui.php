<?php
/**
 * DLS tools on the page: reading inputs from the URL (result permalinks), rendering the result
 * panel, working and par tables in PHP, and loading the tool script and styles only where needed.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The DLS tool pages: slug => tool name used by the script (data-tool) and default inputs.
 * Defaults are what a first visit shows: the core match inputs only. Optional rows (stoppages, the
 * live score log) start empty, so a visitor who types just a score never keeps a sample stoppage
 * (audit F22); the worked examples come from the scenario buttons.
 *
 * @return array<string, array{tool: string, defaults: array<string, string>}>
 */
function dlscalc_dls_tools() {
	return array(
		'dls-calculator'            => array(
			'tool'     => 'calc',
			'defaults' => array(
				'n' => '50',
				'r' => '250',
				'g' => '245',
				'c' => '50',
				'a' => '',
				'b' => '',
				'f' => '',
				'k' => '',
			),
		),
		'dls-target-calculator-t20' => array(
			'tool'     => 't20',
			'defaults' => array(
				'n' => '20',
				'r' => '180',
				'l' => '5',
				'b' => '',
			),
		),
		'dls-par-score-table'       => array(
			'tool'     => 'parTable',
			'defaults' => array(
				'o' => '20',
				'r' => '160',
				'c' => '',
			),
		),
		'live-dls-par-score'        => array(
			'tool'     => 'live',
			'defaults' => array(
				'n' => '20',
				'r' => '160',
				'g' => '245',
				'c' => '',
				'f' => '',
				'x' => '',
			),
		),
	);
}

/**
 * Sample scenarios on the DLS Calculator (audit F21): each opens one of the ICC worked examples,
 * whose results are in the test suite and the page's "ICC examples" table.
 *
 * @return array<string, array<string, string>> Button label => inputs.
 */
function dlscalc_dls_scenarios() {
	return array(
		'Rain before chase'  => array( 'n' => '45', 'r' => '212', 'g' => '245', 'c' => '35', 'f' => 'odi' ),
		'Rain during chase'  => array( 'n' => '50', 'r' => '250', 'g' => '245', 'c' => '50', 'b' => '12-1-10', 'f' => 'odi' ),
		'Team 1 interrupted' => array( 'n' => '50', 'r' => '180', 'g' => '245', 'c' => '40', 'a' => '20-3-10', 'f' => 'odi' ),
		'Two stoppages'      => array( 'n' => '50', 'r' => '226', 'g' => '245', 'c' => '33', 'a' => '47.1-8-2.5', 'b' => '25-2-5', 'f' => 'odi' ),
	);
}

/**
 * Scenario buttons above the DLS Calculator. They are links to result permalinks, so they work
 * without JavaScript; the one matching the inputs on screen is marked current.
 *
 * @param array $in Current inputs.
 */
function dlscalc_dls_scenarios_html( $in ) {
	$keys = array( 'n', 'r', 'g', 'c', 'a', 'b', 'k' );
	?>
	<nav class="scenarios noprint" aria-labelledby="scenarios-title">
		<p class="hint" id="scenarios-title">Try an ICC worked example:</p>
		<div class="chips">
			<?php
			foreach ( dlscalc_dls_scenarios() as $label => $p ) {
				$on = true;
				foreach ( $keys as $key ) {
					$on = $on && (string) ( $p[ $key ] ?? '' ) === (string) ( $in[ $key ] ?? '' );
				}
				printf(
					'<a class="chip" rel="nofollow" href="%s" aria-current="%s">%s</a>',
					esc_url( dlscalc_dls_permalink( 'dls-calculator', $p ) ),
					$on ? 'true' : 'false',
					esc_html( $label )
				);
			}
			?>
		</div>
	</nav>
	<?php
}

/**
 * Whether the current request is a DLS tool page or the home page (which has the quick box).
 *
 * @return bool
 */
function dlscalc_is_dls_page() {
	return is_front_page() || isset( dlscalc_dls_tools()[ dlscalc_current_slug() ] );
}

/**
 * Stoppages sent by a form without JavaScript: a_at[], a_w[], a_lost[] become "12-1-10_22-3-2".
 * The live tracker's score log comes the same way, as x_at[], x_r[], x_w[]. Fully blank rows are skipped.
 *
 * @param string   $key    "a" (Team 1), "b" (Team 2) or "x" (live score log).
 * @param string[] $fields Field names after the key, in permalink order.
 * @return string|null Null when the form sent no rows.
 */
function dlscalc_dls_stops_from_rows( $key, $fields = array( 'at', 'w', 'lost' ) ) {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only calculator inputs.
	if ( ! isset( $_GET[ $key . '_at' ] ) || ! is_array( $_GET[ $key . '_at' ] ) ) {
		return null;
	}
	$at    = array_map( 'sanitize_text_field', wp_unslash( $_GET[ $key . '_at' ] ) );
	$cols  = array();
	foreach ( array_slice( $fields, 1 ) as $field ) {
		$cols[] = isset( $_GET[ $key . '_' . $field ] ) && is_array( $_GET[ $key . '_' . $field ] ) ? array_map( 'sanitize_text_field', wp_unslash( $_GET[ $key . '_' . $field ] ) ) : array();
	}
	// phpcs:enable
	$parts = array();
	foreach ( array_slice( $at, 0, 'x' === $key ? DLSCALC_LIVE_MAX : DLSCALC_STOPS_MAX ) as $i => $value ) {
		$row = array( trim( $value ), trim( $cols[0][ $i ] ?? '' ), trim( $cols[1][ $i ] ?? '' ) );
		if ( 'x' === $key ) {
			// "-" and "_" separate fields and entries, so a typed "24-1" travels as "24−1" (as live-par.js does).
			$row = str_replace( array( '-', '_' ), array( '−', '' ), $row );
		}
		if ( '' === implode( '', $row ) ) {
			continue;
		}
		$parts[] = implode( '-', $row );
	}
	return implode( '_', $parts );
}

/**
 * Inputs for a tool: defaults overlaid with whatever the URL carries. Parameter names avoid
 * WordPress's own query variables (s, m, w and others), which would turn the page into a search.
 *
 * @param string $slug Tool page slug.
 * @return array<string, string>
 */
function dlscalc_dls_inputs( $slug ) {
	$tools = dlscalc_dls_tools();
	$in    = $tools[ $slug ]['defaults'];
	if ( ! dlscalc_dls_has_params() ) {
		return $in;
	}
	foreach ( array_keys( $in ) as $key ) {
		$rows = in_array( $key, array( 'a', 'b' ), true ) ? dlscalc_dls_stops_from_rows( $key ) : null;
		$rows = 'x' === $key ? dlscalc_dls_stops_from_rows( 'x', array( 'at', 'r', 'w' ) ) : $rows;
		if ( null !== $rows ) {
			$in[ $key ] = $rows;
		} elseif ( isset( $_GET[ $key ] ) && is_string( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$in[ $key ] = substr( sanitize_text_field( wp_unslash( $_GET[ $key ] ) ), 0, 120 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} elseif ( 'g' !== $key ) {
			// A link means only what it carries (audit F69): without stoppages, score entries, overs
			// lost or penalty runs there were none, and a missing score or overs per side is an error
			// rather than the sample figure. Only G50 keeps its usual 245.
			$in[ $key ] = '';
		}
	}
	return $in;
}

/**
 * Whether the URL carries calculator inputs (a result permalink).
 *
 * @return bool
 */
function dlscalc_dls_has_params() {
	foreach ( array( 'n', 'r', 'g', 'c', 'a', 'b', 'l', 'o', 'x', 'f', 'k', 'a_at', 'b_at', 'x_at' ) as $key ) {
		if ( isset( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}
	}
	return false;
}

/**
 * Run a tool's engine on its inputs.
 *
 * @param string $slug Tool page slug.
 * @param array  $in   Inputs.
 * @return array
 */
function dlscalc_dls_run( $slug, $in ) {
	switch ( dlscalc_dls_tools()[ $slug ]['tool'] ) {
		case 't20':
			return dlscalc_dls_t20( $in );
		case 'parTable':
			return dlscalc_dls_par_table( $in );
		case 'live':
			return dlscalc_dls_live( $in );
		default:
			return dlscalc_dls_calc( $in );
	}
}

/**
 * Result permalink for a tool: the clean page URL plus the inputs.
 *
 * @param string $slug Tool page slug.
 * @param array  $in   Inputs.
 * @return string
 */
function dlscalc_dls_permalink( $slug, $in ) {
	return add_query_arg( array_map( 'rawurlencode', array_filter( $in, 'strlen' ) ), dlscalc_page_url( $slug ) );
}

/*
 * Parameter URLs (result permalinks) stay indexable: WordPress prints a canonical to the clean
 * page URL, which passes their signals there. A noindex on top would conflict with it (audit F8).
 */

/**
 * Engine and tool script, deferred, only on the DLS pages and home. The live par tracker has its
 * own script on top of the same engine.
 */
function dlscalc_dls_scripts() {
	if ( ! dlscalc_is_dls_page() ) {
		return;
	}
	$args   = array(
		'strategy'  => 'defer',
		'in_footer' => true,
	);
	$script = 'live-dls-par-score' === dlscalc_current_slug() ? 'live-par' : 'dls-tools';
	wp_enqueue_script( 'dlscalc-dls-engine', DLSCALC_URI . '/assets/js/dls-engine.js', array(), dlscalc_asset_version( 'assets/js/dls-engine.js' ), $args );
	wp_enqueue_script( 'dlscalc-' . $script, DLSCALC_URI . '/assets/js/' . $script . '.js', array( 'dlscalc-dls-engine' ), dlscalc_asset_version( 'assets/js/' . $script . '.js' ), $args );

	// Templates on these pages use no blocks, so the block stylesheets are dead weight.
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'dlscalc_dls_scripts', 100 );

/**
 * Tool styles, inlined after the main stylesheet (article styles first), only on pages that have a tool (DLS, NRR and home). The one-line
 * script marks the page as scripted before it paints, so buttons that need JavaScript take their
 * place at once and the no-JS extras never show: nothing shifts when the tool script runs.
 */
function dlscalc_dls_styles() {
	if ( dlscalc_is_dls_page() || dlscalc_is_nrr_page() ) {
		echo "<script>document.documentElement.classList.add('js')</script>\n";
		if ( ! is_front_page() ) {
			dlscalc_print_inline_css( 'assets/css/prose.css', 'dlscalc-prose-css' );
		}
		dlscalc_print_inline_css( 'assets/css/tools.css', 'dlscalc-tools-css' );
		if ( dlscalc_is_nrr_page() || 'live-dls-par-score' === dlscalc_current_slug() ) {
			dlscalc_print_inline_css( 'assets/css/nrr.css', 'dlscalc-nrr-css' );
		}
	}
}
add_action( 'wp_head', 'dlscalc_dls_styles', 6 );

/**
 * Overs as typed, for input values: balls back to "32.4".
 *
 * @param string $stops Stoppages string, e.g. "12-1-10_22-3-2".
 * @return array<int, array{at: string, w: string, lost: string}>
 */
function dlscalc_dls_stop_fields( $stops ) {
	$out = array();
	foreach ( explode( '_', $stops ) as $part ) {
		if ( '' === trim( str_replace( '-', '', $part ) ) ) {
			continue;
		}
		$x     = explode( '-', $part );
		$out[] = array(
			'at'   => $x[0] ?? '',
			'w'    => $x[1] ?? '',
			'lost' => $x[2] ?? '',
		);
	}
	return $out;
}

/**
 * Stoppage rows for one innings, plus one spare blank row for visitors without JavaScript
 * (the script removes it and adds rows on demand instead).
 *
 * @param string $key   "a" or "b".
 * @param string $value Stoppages string.
 * @param string $team  "Team 1", "Team 2" or "" for the T20 chase.
 * @param array  $r     Engine result, to mark the field an error belongs to.
 */
function dlscalc_dls_stop_rows( $key, $value, $team, $r = array() ) {
	$rows   = dlscalc_dls_stop_fields( $value );
	$rows[] = array(
		'at'    => '',
		'w'     => '',
		'lost'  => '',
		'spare' => true,
	);
	echo '<div class="stops" data-stops="' . esc_attr( $key ) . '" data-team="' . esc_attr( $team ) . '">';
	foreach ( $rows as $i => $row ) {
		dlscalc_dls_stop_row( $key, $i + 1, $row, $team, $r );
	}
	echo '</div>';
}

/**
 * Attributes that mark an input as the one an error belongs to (audit F19). dls-tools.js keeps
 * them in step as the visitor types.
 *
 * @param array  $r     Engine result.
 * @param string $field Field name: "n", "c", "k" or a stoppage field like "b-2-at".
 * @return string
 */
function dlscalc_dls_invalid( $r, $field ) {
	return ( $r['field'] ?? '' ) === $field ? ' aria-invalid="true" aria-describedby="fe-' . esc_attr( $field ) . '"' : '';
}

/**
 * The error message under the input it belongs to.
 *
 * @param array  $r     Engine result.
 * @param string $field Field name.
 */
function dlscalc_dls_field_err( $r, $field ) {
	if ( ( $r['field'] ?? '' ) === $field ) {
		echo '<span class="fld__err" id="fe-' . esc_attr( $field ) . '">' . esc_html( $r['err'] ) . '</span>';
	}
}

/**
 * One stoppage row. The script clones the first row's markup for new rows, so keep it generic.
 *
 * @param string $key  "a" or "b".
 * @param int    $n    Row number.
 * @param array  $row  Values.
 * @param string $team Team label for the remove button.
 * @param array  $r    Engine result.
 */
function dlscalc_dls_stop_row( $key, $n, $row, $team, $r = array() ) {
	$label = trim( $team . ' stoppage' );
	$f     = $key . '-' . $n . '-';
	?>
	<div class="stop<?php echo empty( $row['spare'] ) ? '' : ' stop--spare'; ?>">
		<label class="fld"><span>Overs bowled at stoppage <span class="stop__n"><?php echo (int) $n; ?></span></span>
			<input type="text" inputmode="decimal" name="<?php echo esc_attr( $key ); ?>_at[]" value="<?php echo esc_attr( $row['at'] ); ?>" placeholder="e.g. 32.4" data-f="at" autocomplete="off"<?php echo dlscalc_dls_invalid( $r, $f . 'at' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>>
			<?php dlscalc_dls_field_err( $r, $f . 'at' ); ?>
		</label>
		<label class="fld">Wickets down
			<input type="number" inputmode="numeric" min="0" max="9" name="<?php echo esc_attr( $key ); ?>_w[]" value="<?php echo esc_attr( $row['w'] ); ?>" data-f="w" autocomplete="off"<?php echo dlscalc_dls_invalid( $r, $f . 'w' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>>
			<?php dlscalc_dls_field_err( $r, $f . 'w' ); ?>
		</label>
		<label class="fld">Overs lost
			<input type="text" inputmode="decimal" name="<?php echo esc_attr( $key ); ?>_lost[]" value="<?php echo esc_attr( $row['lost'] ); ?>" placeholder="e.g. 10" data-f="lost" autocomplete="off"<?php echo dlscalc_dls_invalid( $r, $f . 'lost' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>>
			<?php dlscalc_dls_field_err( $r, $f . 'lost' ); ?>
		</label>
		<button type="button" class="xbtn" aria-label="Remove <?php echo esc_attr( $label ); ?>" data-remove>×</button>
	</div>
	<?php
}

/**
 * Result panel: headline target or par, key numbers, warnings and share buttons.
 *
 * @param array  $r     Engine result.
 * @param string $slug  Tool page slug.
 * @param array  $in    Inputs.
 * @param array  $stats Stat boxes: key => label. Keys: tie, t2overs, r1, r2, rr.
 */
function dlscalc_dls_result_panel( $r, $slug, $in, $stats ) {
	$err = $r['err'] ?? '';
	?>
	<aside class="result" id="result" aria-live="polite" aria-label="Result">
		<p class="alert" data-out="err"<?php echo $err ? '' : ' hidden'; ?>><?php echo esc_html( $err ); ?></p>
		<p class="alert alert--soft" data-out="warn"<?php echo empty( $r['warn'] ) ? ' hidden' : ''; ?>><?php echo esc_html( $r['warn'] ?? '' ); ?></p>
		<div>
			<div class="result__label" data-out="label"><?php echo esc_html( dlscalc_dls_label( $r ) ); ?></div>
			<div class="result__big" data-out="big"><?php echo esc_html( $err ? '–' : ( $r['ended'] ? $r['par'] : $r['target'] ) ); ?></div>
			<div class="result__line" data-out="line"><?php echo esc_html( dlscalc_dls_line( $r ) ); ?></div>
		</div>
		<div class="stats">
			<?php foreach ( $stats as $key => $label ) : ?>
				<div class="stat"><span><?php echo esc_html( $label ); ?></span><b data-out="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( dlscalc_dls_stat( $r, $key ) ); ?></b></div>
			<?php endforeach; ?>
		</div>
		<div class="share noprint">
			<a class="btn btn--gold" data-out="wa" href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( dlscalc_dls_share_text( $r, $slug ) . ' ' . dlscalc_dls_permalink( $slug, $in ) ) ); ?>" target="_blank" rel="noopener">
				<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20l1.3-3.9A8 8 0 1 1 8 19z"/></svg>
				Share on WhatsApp
			</a>
			<button type="button" class="btn btn--ghost js-only" data-copy>Copy link</button>
			<button type="button" class="btn btn--ghost js-only" data-print>Print par sheet</button>
		</div>
		<p class="result__note">DLSCalc is an independent tool, not ICC software. It uses the DLS Standard Edition, the ICC's published method. International matches use the Professional Edition software, so official targets can differ: usually by 3 runs or fewer in T20s, more in high-scoring ODIs. <a href="<?php echo esc_url( dlscalc_page_url( 'contact' ) ); ?>">Found a wrong answer? Tell us.</a></p>
	</aside>
	<?php
}

/**
 * Headline label.
 *
 * @param array $r Engine result.
 * @return string
 */
function dlscalc_dls_label( $r ) {
	return empty( $r['err'] ) && $r['ended'] ? 'Match ended: Team 2 par score' : 'Team 2 revised target';
}

/**
 * Line under the headline number.
 *
 * @param array $r Engine result.
 * @return string
 */
function dlscalc_dls_line( $r ) {
	if ( ! empty( $r['err'] ) ) {
		return 'Check the inputs.';
	}
	if ( $r['ended'] ) {
		return 'Team 2 wins with more than ' . $r['par'] . ' and ties on ' . $r['par'] . '.';
	}
	return 'to win in ' . dlscalc_dls_overs( $r['allot'] ) . ' overs';
}

/**
 * One stat box value.
 *
 * @param array  $r   Engine result.
 * @param string $key Stat key.
 * @return string
 */
function dlscalc_dls_stat( $r, $key ) {
	if ( ! empty( $r['err'] ) ) {
		return '–';
	}
	switch ( $key ) {
		case 'tie':
			return (string) $r['par'];
		case 't2overs':
			return dlscalc_dls_overs( $r['allot'] );
		case 'r1':
			return dlscalc_dls_f1( $r['R1'] ) . '%';
		case 'r2':
			return dlscalc_dls_f1( $r['R2'] ) . '%';
		case 'rr':
			return '' === ( $r['rr'] ?? '' ) ? '–' : $r['rr'];
	}
	return '';
}

/**
 * Share message before the link.
 *
 * @param array  $r    Engine result.
 * @param string $slug Tool page slug.
 * @return string
 */
function dlscalc_dls_share_text( $r, $slug ) {
	if ( ! empty( $r['err'] ) ) {
		return 'DLS calculator:';
	}
	$what = 'dls-target-calculator-t20' === $slug ? 'T20 DLS' : 'DLS';
	return $r['ended']
		? $what . ' par score: ' . $r['par'] . '. Full working:'
		: $what . ' target: ' . $r['target'] . ' in ' . dlscalc_dls_overs( $r['allot'] ) . ' overs (par ' . $r['par'] . '). Full working:';
}

/**
 * Numbered working.
 *
 * @param array $r Engine result.
 */
function dlscalc_dls_steps( $r ) {
	echo '<ol class="steps" data-out="steps">';
	$steps = empty( $r['err'] ) ? $r['steps'] : array( 'Fix the inputs above to see the working.' );
	foreach ( $steps as $step ) {
		echo '<li>' . esc_html( $step ) . '</li>';
	}
	echo '</ol>';
}

/**
 * Par score table: completed overs down, wickets across.
 *
 * @param array  $r       Engine result.
 * @param string $caption Visually hidden caption.
 */
function dlscalc_dls_par_table_html( $r, $caption ) {
	?>
	<div class="par-wrap" tabindex="0" role="region" aria-label="<?php echo esc_attr( $caption ); ?>">
		<table class="par">
			<caption class="screen-reader-text"><?php echo esc_html( $caption ); ?></caption>
			<thead><tr><th scope="col">Overs</th><?php for ( $w = 0; $w < 10; $w++ ) : ?><th scope="col"><?php echo (int) $w; ?> wkt</th><?php endfor; ?></tr></thead>
			<tbody data-out="rows">
				<?php
				foreach ( empty( $r['err'] ) ? $r['rows'] : array() as $row ) {
					echo '<tr><th scope="row">' . (int) $row['over'] . '</th>';
					foreach ( $row['cells'] as $cell ) {
						echo '<td>' . (int) $cell . '</td>';
					}
					echo '</tr>';
				}
				?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * FAQ list as native disclosure widgets. The same data feeds the FAQPage schema.
 *
 * @param string $slug Page slug.
 */
function dlscalc_faq_html( $slug ) {
	$faqs = dlscalc_faqs( $slug );
	if ( ! $faqs ) {
		return;
	}
	echo '<div class="faq">';
	foreach ( $faqs as $faq ) {
		echo '<details><summary>' . esc_html( $faq[0] ) . '</summary><p>' . wp_kses( $faq[1], array( 'a' => array( 'href' => true ) ) ) . '</p></details>';
	}
	echo '</div>';
}

/**
 * Who is ahead at one score entry of the live tracker: text, a CSS modifier and a short label
 * for the narrow score table.
 *
 * @param array $e Score entry from dlscalc_dls_live().
 * @return array{0: string, 1: string, 2: string}
 */
function dlscalc_live_status( $e ) {
	$n    = abs( $e['diff'] );
	$runs = $n . ( 1 === $n ? ' run' : ' runs' );
	switch ( $e['state'] ) {
		case 'won':
			return array( 'Team 2 won', 't2', 'Team 2 won' );
		case 'tied':
			return array( 'Match tied', 'level', 'Tied' );
		case 'lost':
			return array( 'Team 1 won by ' . $runs, 't1', 'Team 1 won' );
	}
	if ( 0 === $e['diff'] ) {
		return array( 'Level: tie', 'level', 'Level' );
	}
	return $e['diff'] > 0 ? array( 'Team 2 ahead by ' . $runs, 't2', 'Team 2 +' . $n ) : array( 'Team 1 ahead by ' . $runs, 't1', 'Team 1 +' . $n );
}

/**
 * Headline of the live tracker: label, big value, line, and whether the big value is words.
 *
 * @param array $r Live result.
 * @return array{label: string, big: string, line: string, word: bool}
 */
function dlscalc_live_headline( $r ) {
	if ( ! empty( $r['err'] ) ) {
		return array(
			'label' => 'Live DLS par',
			'big'   => '–',
			'line'  => 'Check the inputs.',
			'word'  => false,
		);
	}
	$e = $r['log'] ? end( $r['log'] ) : null;
	if ( ! $e ) {
		return array(
			'label' => 'Team 2 target',
			'big'   => (string) $r['target'],
			'line'  => 'in ' . dlscalc_dls_overs( $r['allot'] ) . ' overs. Add Team 2\'s score after each over to see who is ahead on DLS.',
			'word'  => false,
		);
	}
	$score = $e['runs'] . '/' . $e['w'];
	$at    = dlscalc_dls_overs( $e['at'] ) . ( 6 === (int) $e['at'] ? ' over' : ' overs' );
	if ( 'on' !== $e['state'] ) {
		$lines = array(
			'won'  => 'Team 2 reached the target of ' . $r['target'] . ' with ' . $score . ' in ' . $at . '.',
			'tied' => 'Team 2 finished on ' . $score . ', level with the par score of ' . $e['par'] . '.',
			'lost' => 'Team 2 finished on ' . $score . ' in ' . $at . ', short of the target of ' . $r['target'] . '.',
		);
		return array(
			'label' => 'Result',
			'big'   => dlscalc_live_status( $e )[0],
			'line'  => $lines[ $e['state'] ],
			'word'  => true,
		);
	}
	$runs = abs( $e['diff'] ) . ( 1 === abs( $e['diff'] ) ? ' run' : ' runs' );
	if ( 0 === $e['diff'] ) {
		$line = 'Team 2 has ' . $score . ', exactly on par. If play stopped now, the match would be tied.';
	} else {
		// A match decided on DLS during the chase is won by runs either way (ICC DLS section 7, audit F23).
		$line = 'Team 2 has ' . $score . ', ' . $runs . ( $e['diff'] > 0 ? ' above' : ' below' ) . ' par. If play stopped now, Team ' . ( $e['diff'] > 0 ? '2' : '1' ) . ' would win by ' . $runs . ' (DLS method).';
	}
	return array(
		'label' => 'DLS par at ' . $at . ', ' . $e['w'] . ' down',
		'big'   => (string) $e['par'],
		'line'  => $line,
		'word'  => false,
	);
}

/**
 * Printable par sheet, top: lines for the scorers to fill in. Hidden on screen; the print styles
 * show it above the result, the working and the par table.
 */
function dlscalc_par_sheet_head() {
	?>
	<div class="sheet sheet--head" aria-hidden="true">
		<p class="sheet__title">DLS par sheet</p>
		<div class="sheet__lines">
			<span>Match</span><span>Venue</span><span>Date</span>
			<span>Team 1</span><span>Team 2</span><span>Overs per side</span>
		</div>
	</div>
	<?php
}

/**
 * Printable par sheet, bottom: signatures and the link that reopens the same result.
 * dls-tools.js and live-par.js keep the link current before printing.
 */
function dlscalc_par_sheet_foot() {
	$url = home_url( add_query_arg( array() ) );
	?>
	<div class="sheet sheet--foot" aria-hidden="true">
		<div class="sheet__lines sheet__lines--sign">
			<span>Umpire</span><span>Umpire</span><span>Scorer</span>
		</div>
		<p>DLS Standard Edition (ICC). Par = score to tie if play ends after that over; one more run wins. Worked out at <span data-sheet-url><?php echo esc_html( $url ); ?></span></p>
	</div>
	<?php
}

/**
 * Resource lookup on /dls-method/ (audit F20): overs left (ball by ball) and wickets lost give the
 * percentage from the ICC table. Inputs v and d come from the address, so it works without
 * JavaScript; resource-lookup.js gives the same answer as the visitor types.
 *
 * @param string $v Overs left, e.g. "32.4".
 * @param string $d Wickets lost, 0 to 9.
 * @return array{err: string, pct: string, text: string}
 */
function dlscalc_dls_lookup( $v, $d ) {
	$b = dlscalc_dls_balls( $v );
	$w = dlscalc_dls_int( $d );
	if ( is_nan( (float) $b ) ) {
		return array(
			'err'  => dlscalc_dls_blank( $v ) ? 'Enter the overs left, for example 32.4.' : dlscalc_dls_overs_help( 'Overs left', $v ),
			'pct'  => '',
			'text' => '',
		);
	}
	if ( $b < 1 || $b > 300 || is_nan( (float) $w ) || $w > 9 ) {
		return array(
			'err'  => 'Overs left must be between 0.1 and 50, and wickets lost 0 to 9.',
			'pct'  => '',
			'text' => '',
		);
	}
	return array(
		'err'  => '',
		'pct'  => dlscalc_dls_f1( dlscalc_dls_res( $b, $w ) ) . '%',
		'text' => 'of resources left with ' . dlscalc_dls_overs( $b ) . ( 6 === $b ? ' over' : ' overs' ) . ' left and ' . $w . ( 1 === $w ? ' wicket' : ' wickets' ) . ' lost.',
	);
}

/**
 * The resource lookup form and its answer.
 */
function dlscalc_resource_lookup_html() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only lookup inputs.
	$v = isset( $_GET['v'] ) && is_string( $_GET['v'] ) ? substr( sanitize_text_field( wp_unslash( $_GET['v'] ) ), 0, 8 ) : '20';
	$d = isset( $_GET['d'] ) && is_string( $_GET['d'] ) ? substr( sanitize_text_field( wp_unslash( $_GET['d'] ) ), 0, 2 ) : '0';
	// phpcs:enable
	$r = dlscalc_dls_lookup( $v, $d );
	?>
	<form class="lookup noprint" method="get" action="<?php echo esc_url( dlscalc_page_url( 'dls-method' ) . '#resource-lookup' ); ?>" data-lookup novalidate>
		<label>Overs left
			<input type="text" inputmode="decimal" name="v" value="<?php echo esc_attr( $v ); ?>" placeholder="e.g. 32.4" autocomplete="off"<?php echo $r['err'] ? ' aria-invalid="true"' : ''; ?>>
		</label>
		<label>Wickets lost
			<select name="d">
				<?php for ( $w = 0; $w < 10; $w++ ) : ?>
					<option value="<?php echo (int) $w; ?>"<?php selected( $d, (string) $w ); ?>><?php echo (int) $w; ?></option>
				<?php endfor; ?>
			</select>
		</label>
		<button type="submit" class="btn btn--gold">Look up</button>
		<p class="lookup__out" aria-live="polite"><span class="lookup__err" data-lk="err"<?php echo $r['err'] ? '' : ' hidden'; ?>><?php echo esc_html( $r['err'] ); ?></span><b data-lk="pct"><?php echo esc_html( $r['pct'] ); ?></b> <span data-lk="text"><?php echo esc_html( $r['text'] ); ?></span></p>
	</form>
	<?php
	// The engine loads only when the visitor first uses the form: loading it with the page made the
	// fonts arrive after first paint on this long guide (a layout shift). Until then the form submits.
	$srcs = array(
		DLSCALC_URI . '/assets/js/dls-engine.js?ver=' . dlscalc_asset_version( 'assets/js/dls-engine.js' ),
		DLSCALC_URI . '/assets/js/resource-lookup.js?ver=' . dlscalc_asset_version( 'assets/js/resource-lookup.js' ),
	);
	?>
	<script>(function(f,s){var go=function(){f.removeEventListener('focusin',go);(function next(i){if(i<s.length){var e=document.createElement('script');e.src=s[i];e.onload=function(){next(i+1)};document.head.appendChild(e)}})(0)};f.addEventListener('focusin',go)})(document.querySelector('form[data-lookup]'),<?php echo wp_json_encode( array_map( 'esc_url_raw', $srcs ) ); ?>)</script>
	<?php
}
