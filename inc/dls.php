<?php
/**
 * DLS engine in PHP: a line-for-line copy of assets/js/dls-engine.js, so tool pages render a real
 * result, its working and the par tables in the HTML. Search engines and AI answer engines see
 * the numbers without running JavaScript, and the calculators work with JavaScript switched off.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DLSCALC_STOPS_MAX', 10 );
define( 'DLSCALC_LIVE_MAX', 100 );

/**
 * ICC DLS Standard Edition resource table, ball by ball, delta-encoded exactly as in the JS engine.
 *
 * @return int[][] Ten rows (wickets lost 0 to 9) of 301 values (balls left 0 to 300), in tenths of a percent.
 */
function dlscalc_dls_table() {
	static $table = null;
	if ( null !== $table ) {
		return $table;
	}
	$rows  = array(
		'666666666666566566566565656556556555565555555555555455554554554545454545454454445444445444444444444434444434434443434434343434334343343343334333334333333333333333333323333323332332332332332323232323232323223232232223223222232222322222222322222222222222122222222122222122221222122122212212122122121212',
		'666666666656665665656565655655565555555555555554555455454545454544544544454444444444444444434443444343443434343434334334333433334333333333333333333233333233323323323233232323232323223223223223222322223222222232222222222222222122222221222212221222122122122122121221212121212121212121121211212112112112',
		'666666666566656565656565556555555555555554554554545454545444544445444444444434444344434344343434343343343333433333334333233333333233323332332323323232323223232232232223222232222222322222222221222222221222212221222122122121221212212121212121121212112112112112112111211121111211111121111111112111111111',
		'666666665665665655655655555555555545545545454544544445444444444434443443443434343433433343333333433323333333233323323323232323232232322232223222232222222222222222122222212221222122121221212212121212112121121211211211121112111121111121111111111211111111111101111111111101111110111101111011101110110111',
		'666666656656556556555555555455454545454445444444444344434434343434333433333433332333333233233232323232322322322232222222222222222222122221221221221212212121211212112112112112111121111121111111111111111111111111110111111011110111101101110110110110110110101101010110101010101010101010101010010101001010',
		'666665665655655555554554545454444444444443443434334333343333323333233233232322322322222322222222122222122122122121212121211211211211112111111211111111111111011111101111101110110111011011010110101101010101010101010100101010010100101001001001001001000100100010001001000010001000010000100001000010000010',
		'666656565555555455445444444434434334333333333323323232322232222222222212221212212121121121121111121111111111110111111011101101110101101011010101010101001010010100100100100100100010001000100010000100001000010000001000000100000001000000001000000000010000000000000100000000000000000100000000000000000000',
		'666565555454544444343433333333232322232222122212212121121112111111111101111011011011010101010101001010010010010001000100001000010000010000001000000000100000000000010000000000000000000100000000000000000000000000000000000000000000000001000000000000000000000000000000000000000000000000000000000000000000',
		'665555444434333323232221221212111121011110110110101001010010001000010000010000000100000000000000010000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000',
		'654433322221111110101010001000000000100000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000',
	);
	$table = array();
	foreach ( $rows as $row ) {
		$sum = 0;
		$out = array( 0 );
		$len = strlen( $row );
		for ( $i = 0; $i < $len; $i++ ) {
			$sum  += ord( $row[ $i ] ) - 48;
			$out[] = $sum;
		}
		$table[] = $out;
	}
	return $table;
}

/**
 * Resources left (percent) with this many balls left and wickets lost.
 *
 * @param int $balls_left Balls left.
 * @param int $wkts       Wickets lost.
 * @return float
 */
function dlscalc_dls_res( $balls_left, $wkts ) {
	if ( $wkts >= 10 || $balls_left <= 0 ) {
		return 0.0;
	}
	return dlscalc_dls_table()[ $wkts ][ min( 300, $balls_left ) ] / 10;
}

/**
 * Overs as typed, tidied: a comma (some Android number pads have no dot) reads as the dot and a
 * trailing dot ("40.") is dropped (audit F55). Same as oversIn() in the JS engines.
 *
 * @param mixed $s Overs as typed.
 * @return string
 */
function dlscalc_dls_overs_in( $s ) {
	return (string) preg_replace( '/\.(a?)$/', '$1', preg_replace( '/,/', '.', trim( (string) $s ), 1 ) );
}

/**
 * "32.4" (32 overs and 4 balls) to 196 balls. NAN if it is not a valid overs figure.
 *
 * @param mixed $s Overs as typed.
 * @return float|int
 */
function dlscalc_dls_balls( $s ) {
	$s = dlscalc_dls_overs_in( $s );
	if ( ! preg_match( '/^\d{1,3}(\.\d)?$/', $s ) ) {
		return NAN;
	}
	$p = explode( '.', $s );
	$b = isset( $p[1] ) ? (int) $p[1] : 0;
	return $b > 5 ? NAN : (int) $p[0] * 6 + $b;
}

/**
 * 196 balls to "32.4".
 *
 * @param int $b Balls.
 * @return string
 */
function dlscalc_dls_overs( $b ) {
	$o = intdiv( (int) $b, 6 );
	$r = (int) $b % 6;
	return $r ? $o . '.' . $r : (string) $o;
}

/**
 * Error for an overs figure that dlscalc_dls_balls() could not read. See oversHelp() in the JS engine.
 *
 * @param string $label Field name for the message.
 * @param mixed  $s     Overs as typed.
 * @return string
 */
function dlscalc_dls_overs_help( $label, $s ) {
	if ( preg_match( '/^(\d{1,3})\.(\d)$/', dlscalc_dls_overs_in( $s ), $m ) ) {
		return $label . ': ' . $m[0] . ' is not valid. Balls after the dot go from 0 to 5, so after ' . $m[1] . '.5 comes ' . ( (int) $m[1] + 1 ) . '.';
	}
	return $label . ': write overs as 32.4 for 32 overs and 4 balls.';
}

/**
 * An error result, with the input it belongs to. See fail() in the JS engine.
 *
 * @param string $err   Message.
 * @param string $field Form field name, or "b-2-at" for a stoppage field.
 * @return array{err: string, field: string}
 */
function dlscalc_dls_fail( $err, $field ) {
	return array(
		'err'   => $err,
		'field' => $field,
	);
}

/**
 * Whole number from a form value, NAN if it is not one.
 *
 * @param mixed $s Value.
 * @return float|int
 */
function dlscalc_dls_int( $s ) {
	$s = trim( (string) $s );
	return preg_match( '/^\d{1,4}$/', $s ) ? (int) $s : NAN;
}

/**
 * Round to one decimal.
 *
 * @param float $x Value.
 * @return float
 */
function dlscalc_dls_r1( $x ) {
	return round( $x * 10 ) / 10;
}

/**
 * Format with one decimal.
 *
 * @param float $x Value.
 * @return string
 */
function dlscalc_dls_f1( $x ) {
	return number_format( dlscalc_dls_r1( $x ), 1, '.', '' );
}

/**
 * Round to two decimals the way the JS engine does (PHP's round() pre-rounds and can differ).
 *
 * @param float $x Value.
 * @return float
 */
function dlscalc_dls_c2( $x ) {
	return floor( $x * 100 + 0.5 ) / 100;
}

/**
 * Format with two decimals.
 *
 * @param float $x Value.
 * @return string
 */
function dlscalc_dls_f2( $x ) {
	return number_format( dlscalc_dls_c2( $x ), 2, '.', '' );
}

/**
 * $num ÷ $den rounded half away from zero to $d decimals, as text. Works on whole numbers, so PHP
 * and the browser always agree: as a float, 10.425 sits just under the half (audit F38). Copy of
 * fxq() in assets/js/dls-engine.js and nrr-engine.js; inc/nrr.php uses this one.
 *
 * @param int $num Numerator.
 * @param int $den Denominator, not 0.
 * @param int $d   Decimals.
 * @return string
 */
function dlscalc_fxq( $num, $den, $d ) {
	if ( $den < 0 ) {
		$num = -$num;
		$den = -$den;
	}
	$v = intdiv( abs( $num ) * 10 ** $d * 2 + $den, 2 * $den );
	$s = str_pad( (string) $v, $d + 1, '0', STR_PAD_LEFT );
	return ( $num < 0 && $v ? '-' : '' ) . ( $d ? substr( $s, 0, -$d ) . '.' . substr( $s, -$d ) : $s );
}

/**
 * Raw par: Team 1's score scaled to the resources Team 2 used.
 *
 * @param int   $s    Team 1 score.
 * @param float $used Team 2 resources used.
 * @param float $r1   Team 1 resources.
 * @param float $g    G50.
 * @return float
 */
function dlscalc_dls_scale( $s, $used, $r1, $g ) {
	return $used <= $r1 ? $s * $used / $r1 : $s + $g * ( $used - $r1 ) / 100;
}

/**
 * Par from a raw value, guarding against float noise just under a whole number.
 *
 * @param float $raw Raw par.
 * @return int
 */
function dlscalc_dls_floor( $raw ) {
	return (int) floor( $raw + 1e-9 );
}

/**
 * Stoppages from a permalink value: "12-1-10_22-3-2" (overs bowled, wickets down, overs lost).
 * Empty parts are skipped; see parseStops() in the JS engine.
 *
 * @param mixed $str Value.
 * @return array<int, array{at: float|int, w: float|int, lost: float|int}>
 */
function dlscalc_dls_parse_stops( $str ) {
	if ( ! is_string( $str ) || '' === $str ) {
		return array();
	}
	$out = array();
	// Empty parts ("12-1-10_", "__") are skipped, as the form skips blank rows (audit F70).
	$parts = array_filter(
		explode( '_', $str ),
		function ( $part ) {
			return '' !== trim( str_replace( '-', '', $part ) );
		}
	);
	foreach ( array_slice( $parts, 0, DLSCALC_STOPS_MAX ) as $part ) {
		$x     = explode( '-', $part );
		$out[] = array(
			'at'    => dlscalc_dls_balls( $x[0] ?? '' ),
			'w'     => dlscalc_dls_int( $x[1] ?? '' ),
			'lost'  => dlscalc_dls_balls( $x[2] ?? '' ),
			'atS'   => $x[0] ?? '',
			'lostS' => $x[2] ?? '',
		);
	}
	return $out;
}

/**
 * Resources for one innings, with stoppages in order.
 *
 * @param int    $start_b Balls at the start of the innings.
 * @param array  $stops   Stoppages.
 * @param string $label   "Team 1" or "Team 2".
 * @param string $key     "a" or "b", for the field an error belongs to.
 * @return array
 */
function dlscalc_dls_innings( $start_b, $stops, $label, $key ) {
	$allot = $start_b;
	$r     = dlscalc_dls_res( $start_b, 0 );
	$steps = array( $label . ' starts with ' . dlscalc_dls_overs( $start_b ) . ' overs and 10 wickets: ' . dlscalc_dls_f1( $r ) . '% of resources.' );
	$count = count( $stops );
	for ( $i = 0; $i < $count; $i++ ) {
		$st   = $stops[ $i ];
		$name = $label . ' stoppage ' . ( $i + 1 );
		$f    = $key . '-' . ( $i + 1 ) . '-';
		if ( is_nan( (float) $st['at'] ) && ! dlscalc_dls_blank( $st['atS'] ?? '' ) ) {
			return dlscalc_dls_fail( dlscalc_dls_overs_help( $name . ', overs bowled', $st['atS'] ), $f . 'at' );
		}
		if ( is_nan( (float) $st['lost'] ) && ! dlscalc_dls_blank( $st['lostS'] ?? '' ) ) {
			return dlscalc_dls_fail( dlscalc_dls_overs_help( $name . ', overs lost', $st['lostS'] ), $f . 'lost' );
		}
		if ( is_nan( (float) $st['at'] ) || is_nan( (float) $st['w'] ) || is_nan( (float) $st['lost'] ) ) {
			return dlscalc_dls_fail( $name . ': fill in overs bowled, wickets down and overs lost (overs like 32.4).', $f . ( is_nan( (float) $st['at'] ) ? 'at' : ( is_nan( (float) $st['w'] ) ? 'w' : 'lost' ) ) );
		}
		if ( $i > 0 && ( $st['at'] < $stops[ $i - 1 ]['at'] || $st['w'] < $stops[ $i - 1 ]['w'] ) ) {
			return dlscalc_dls_fail( $label . ' stoppages must be entered in the order they happened.', $f . ( $st['at'] < $stops[ $i - 1 ]['at'] ? 'at' : 'w' ) );
		}
		$left = $allot - $st['at'];
		if ( 0 === $st['at'] ) {
			return dlscalc_dls_fail( $name . ': overs bowled must be more than 0. Overs lost before an innings starts are not a stoppage: enter the shorter innings as its overs instead.', $f . 'at' );
		}
		if ( $left <= 0 ) {
			return dlscalc_dls_fail( $name . ' must come before the end of the innings (' . dlscalc_dls_overs( $allot ) . ' overs).', $f . 'at' );
		}
		if ( $st['w'] > 9 ) {
			return dlscalc_dls_fail( $name . ': wickets down must be 0 to 9.', $f . 'w' );
		}
		if ( $st['lost'] <= 0 || $st['lost'] > $left ) {
			return dlscalc_dls_fail( $name . ': overs lost must be more than 0 and no more than ' . dlscalc_dls_overs( $left ) . '.', $f . 'lost' );
		}
		$before = dlscalc_dls_res( $left, $st['w'] );
		$after  = dlscalc_dls_res( $left - $st['lost'], $st['w'] );
		$r     -= $before - $after;
		$allot -= $st['lost'];
		if ( $st['lost'] === $left ) {
			$steps[] = $label . ' innings ended at ' . dlscalc_dls_overs( $st['at'] ) . ' overs, ' . $st['w'] . ' down, with ' . dlscalc_dls_overs( $left ) . ' overs left = ' . dlscalc_dls_f1( $before ) . '%. All of it is lost.';
		} else {
			$steps[] = $label . ' stoppage at ' . dlscalc_dls_overs( $st['at'] ) . ' overs, ' . $st['w'] . ' down: ' . dlscalc_dls_overs( $left ) . ' overs left = ' . dlscalc_dls_f1( $before ) . '%. After losing ' . dlscalc_dls_overs( $st['lost'] ) . ' overs, ' . dlscalc_dls_overs( $left - $st['lost'] ) . ' left = ' . dlscalc_dls_f1( $after ) . '%. Resources lost: ' . dlscalc_dls_f1( $before - $after ) . '%.';
		}
	}
	$last = $count ? $stops[ $count - 1 ] : null;
	return array(
		'R'      => dlscalc_dls_r1( $r ),
		'steps'  => $steps,
		'allot'  => $allot,
		'lastAt' => $last ? $last['at'] : 0,
		'ended'  => $last && $last['at'] >= $allot,
	);
}

/**
 * Fewest balls Team 2 must face for a result, by format. See minBalls() in the JS engine.
 *
 * @param int    $n Balls per side at the start.
 * @param string $f Format: odi, t20, t20ko (T20 knockout, 10 overs), t10, custom, or '' to go by the overs.
 * @return int
 */
function dlscalc_dls_min_balls( $n, $f = '' ) {
	$min = array(
		'odi'   => 120,
		't20'   => 30,
		't20ko' => 60,
	);
	if ( isset( $min[ $f ] ) ) {
		return $min[ $f ];
	}
	if ( 't10' === $f || 'custom' === $f ) {
		return 0;
	}
	return 300 === $n ? 120 : ( 120 === $n ? 30 : 0 );
}

/**
 * A whole match. Same input and output as match() in the JS engine.
 *
 * @param array $o Keys N, S, G, t1, M, t2 and optional note, F and P (penalty runs to Team 1 during the chase).
 * @return array
 */
function dlscalc_dls_match( $o ) {
	$a = dlscalc_dls_innings( $o['N'], $o['t1'], 'Team 1', 'a' );
	if ( isset( $a['err'] ) ) {
		return $a;
	}
	$b = dlscalc_dls_innings( $o['M'], $o['t2'], 'Team 2', 'b' );
	if ( isset( $b['err'] ) ) {
		return $b;
	}
	$pen      = (int) ( $o['P'] ?? 0 );
	$raw      = dlscalc_dls_scale( $o['S'], $b['R'], $a['R'], $o['G'] );
	$base     = dlscalc_dls_floor( $raw );
	$par      = $base + $pen;
	$formula  = $b['R'] <= $a['R']
		? 'R2 is not more than R1, so par = ' . $o['S'] . ' × ' . dlscalc_dls_f1( $b['R'] ) . ' ÷ ' . dlscalc_dls_f1( $a['R'] ) . ' = ' . dlscalc_dls_f2( $raw )
		: 'R2 is more than R1, so par = ' . $o['S'] . ' + ' . dlscalc_dls_num( $o['G'] ) . ' × (' . dlscalc_dls_f1( $b['R'] ) . ' − ' . dlscalc_dls_f1( $a['R'] ) . ') ÷ 100 = ' . dlscalc_dls_f2( $raw );
	$result   = $b['ended']
		? 'Team 2 wins if they scored more than ' . $par . ' and the match is tied on ' . $par . '.'
		: 'Target = ' . $par . ' + 1 = ' . ( $par + 1 ) . '.';
	$formula .= ', rounded down to ' . $base . '.' . ( $pen ? '' : ' ' . $result );
	$steps    = array_merge( $a['steps'], array( 'Team 1 resources, R1 = ' . dlscalc_dls_f1( $a['R'] ) . '%.' ) );
	if ( ! empty( $o['note'] ) ) {
		$steps[] = $o['note'];
	}
	$steps = array_merge( $steps, $b['steps'], array( 'Team 2 resources, R2 = ' . dlscalc_dls_f1( $b['R'] ) . '%.', $formula ) );
	if ( $pen ) {
		$steps[] = 'Team 1 was awarded ' . $pen . ' penalty ' . ( 1 === $pen ? 'run' : 'runs' ) . ' during the chase, so the par score and every par in the table go up by ' . $pen . ' (ICC DLS rules, section 6.2): par = ' . $base . ' + ' . $pen . ' = ' . $par . '. ' . $result;
	}

	$min_b = dlscalc_dls_min_balls( $o['N'], $o['F'] ?? '' );
	$warn  = '';
	if ( $min_b && ( $b['ended'] ? $b['lastAt'] : $b['allot'] ) < $min_b ) {
		$warn = 'Team 2 ' . ( $b['ended'] ? 'faced' : 'can face' ) . ' fewer than ' . dlscalc_dls_overs( $min_b ) . ' overs, so under ICC rules there is no result unless the match was already decided.';
	}
	// A common slip: Team 1's innings was cut but Team 2 keeps the full overs (audit F39).
	if ( $o['t1'] && $o['M'] > $a['allot'] ) {
		$warn .= ( $warn ? ' ' : '' ) . 'Team 2 starts with ' . dlscalc_dls_overs( $o['M'] ) . ' overs, more than the ' . dlscalc_dls_overs( $a['allot'] ) . ' Team 1 had after its stoppages. Both sides usually get the same overs, so check Team 2\'s overs at the start of the chase.';
	}
	return array(
		'N'      => $o['N'],
		'F'      => $o['F'] ?? '',
		'S'      => $o['S'],
		'G'      => $o['G'],
		'M'      => $o['M'],
		'R1'     => $a['R'],
		'R2'     => $b['R'],
		'raw'    => dlscalc_dls_c2( $raw ),
		'par'    => $par,
		'target' => $par + 1,
		'allot'  => $b['allot'],
		'lastAt' => $b['lastAt'],
		'ended'  => $b['ended'],
		'steps'  => $steps,
		'warn'   => $warn,
		'rows'   => dlscalc_dls_par_rows( $o['S'], $a['R'], $o['G'], $b, $pen ),
	);
}

/**
 * A number as JavaScript prints it: no trailing ".0" on whole numbers.
 *
 * @param float|int $x Value.
 * @return string
 */
function dlscalc_dls_num( $x ) {
	return floor( $x ) == $x ? (string) (int) $x : (string) $x; // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual -- int and float compare by value.
}

/**
 * Par after each completed over left in the chase, for 0 to 9 wickets down.
 *
 * @param int   $s  Team 1 score.
 * @param float $r1 Team 1 resources.
 * @param float $g  G50.
 * @param array $b  Team 2 innings.
 * @param int   $p  Penalty runs to Team 1 during the chase, added to every par.
 * @return array<int, array{over: int, cells: int[]}>
 */
function dlscalc_dls_par_rows( $s, $r1, $g, $b, $p = 0 ) {
	$rows = array();
	if ( $b['ended'] ) {
		return $rows;
	}
	for ( $ov = max( 1, (int) ceil( $b['lastAt'] / 6 ) ); $ov * 6 < $b['allot']; $ov++ ) {
		$cells = array();
		for ( $w = 0; $w < 10; $w++ ) {
			$cells[] = dlscalc_dls_floor( dlscalc_dls_scale( $s, dlscalc_dls_r1( $b['R'] - dlscalc_dls_res( $b['allot'] - $ov * 6, $w ) ), $r1, $g ) ) + $p;
		}
		$rows[] = array(
			'over'  => $ov,
			'cells' => $cells,
		);
	}
	return $rows;
}

/**
 * Shared score and G50 checks.
 *
 * @param float|int $s Score.
 * @param float     $g G50.
 * @return array|null Error result, or null when fine.
 */
function dlscalc_dls_check( $s, $g, $raw = '' ) {
	if ( is_nan( (float) $s ) || $s > 999 ) {
		return dlscalc_dls_fail( dlscalc_dls_runs_err( $raw, 'Team 1\'s score', 'Enter Team 1\'s final score.' ), 'r' );
	}
	if ( ! ( $g > 0 && $g <= 500 ) ) {
		return dlscalc_dls_fail( 'Enter a G50 value between 1 and 500, for example 245.', 'g' );
	}
	return null;
}

/**
 * Whether a form value is empty or missing.
 *
 * @param mixed $v Value.
 * @return bool
 */
function dlscalc_dls_blank( $v ) {
	return null === $v || '' === trim( (string) $v );
}

/**
 * Whether a value was typed as a negative number ("-5"), so the error can say so (audit F39).
 *
 * @param mixed $v Value.
 * @return bool
 */
function dlscalc_dls_neg( $v ) {
	return (bool) preg_match( '/^\s*[-−]\s*\d/u', (string) $v );
}

/**
 * Whether a runs box holds a scorecard score with wickets ("98/6", "98-6"), which a number box
 * used to turn into 986 (audit F66). As score() in the JS engines; also used by inc/nrr.php.
 *
 * @param mixed $v Value.
 * @return bool
 */
function dlscalc_dls_score( $v ) {
	return (bool) preg_match( '/^\s*\d{1,3}\s*[\/\-−]\s*\d{1,2}\s*$/u', (string) $v );
}

/**
 * Error for a runs box that is not a whole number from 0 to 999, naming the cause (audit F49).
 * As runsErr() in the JS engines; also used by inc/nrr.php.
 *
 * @param mixed  $raw   Value as typed.
 * @param string $who   Field name for the message, like "Team 1's score".
 * @param string $empty Message for a blank box.
 * @return string
 */
function dlscalc_dls_runs_err( $raw, $who, $empty ) {
	$s = trim( (string) $raw );
	if ( '' === $s || ( preg_match( '/^\d+$/', $s ) && (int) $s <= 999 ) ) {
		return $empty;
	}
	if ( dlscalc_dls_score( $s ) ) {
		return $who . ': enter runs only, without wickets (98, not 98/6).';
	}
	if ( dlscalc_dls_neg( $s ) ) {
		return $who . ' cannot be negative.';
	}
	return preg_match( '/^\d+$/', $s ) ? $who . ' can be no more than 999.' : $who . ' must be a whole number, like 245.';
}

/**
 * DLS Calculator page.
 *
 * @param array $p Keys n, r, g, c, a, b, f, k as strings (see calc() in the JS engine).
 * @return array
 */
function dlscalc_dls_calc( $p ) {
	$n   = dlscalc_dls_balls( $p['n'] ?? '' );
	$s   = dlscalc_dls_int( $p['r'] ?? '' );
	$pen = dlscalc_dls_blank( $p['k'] ?? null ) ? 0 : dlscalc_dls_int( $p['k'] );
	$g   = preg_match( '/^\d{1,3}(\.\d{1,2})?$/', trim( (string) ( $p['g'] ?? '' ) ) ) ? (float) $p['g'] : NAN;
	$m   = dlscalc_dls_blank( $p['c'] ?? null ) ? $n : dlscalc_dls_balls( $p['c'] );
	if ( is_nan( (float) $n ) && ! dlscalc_dls_blank( $p['n'] ?? null ) ) {
		return dlscalc_dls_fail( dlscalc_dls_overs_help( 'Overs per side', $p['n'] ), 'n' );
	}
	if ( is_nan( (float) $n ) || $n < 6 || $n > 300 ) {
		return dlscalc_dls_fail( 'Overs per side must be between 1 and 50.', 'n' );
	}
	$e = dlscalc_dls_check( $s, $g, $p['r'] ?? '' );
	if ( $e ) {
		return $e;
	}
	if ( is_nan( (float) $m ) ) {
		return dlscalc_dls_fail( dlscalc_dls_overs_help( 'Overs for Team 2 at the start of the chase', $p['c'] ), 'c' );
	}
	if ( $m < 1 || $m > $n ) {
		return dlscalc_dls_fail( 'Overs for Team 2 at the start of the chase must be between 0.1 and ' . dlscalc_dls_overs( $n ) . '.', 'c' );
	}
	if ( is_nan( (float) $pen ) || $pen > 99 ) {
		return dlscalc_dls_fail( 'Penalty runs must be a whole number from 0 to 99.', 'k' );
	}
	return dlscalc_dls_match(
		array(
			'N'  => $n,
			'S'  => $s,
			'G'  => $g,
			't1' => dlscalc_dls_parse_stops( $p['a'] ?? '' ),
			'M'  => $m,
			't2' => dlscalc_dls_parse_stops( $p['b'] ?? '' ),
			'F'  => in_array( $p['f'] ?? '', array( 'odi', 't20', 't20ko', 't10', 'custom' ), true ) ? $p['f'] : '',
			'P'  => $pen,
		)
	);
}

/**
 * T20 page.
 *
 * @param array $p Keys n, r, l, b as strings.
 * @return array
 */
function dlscalc_dls_t20( $p ) {
	$no = dlscalc_dls_int( $p['n'] ?? '' );
	$s  = dlscalc_dls_int( $p['r'] ?? '' );
	$l  = dlscalc_dls_int( $p['l'] ?? '' );
	if ( is_nan( (float) $no ) || $no < 5 || $no > 20 ) {
		return dlscalc_dls_fail( 'Overs per side must be between 5 and 20.', 'n' );
	}
	$e = dlscalc_dls_check( $s, 245, $p['r'] ?? '' );
	if ( $e ) {
		return $e;
	}
	// More overs lost than leave Team 2 its 5 is an error, not a silent cap (audit F39).
	if ( dlscalc_dls_blank( $p['l'] ?? null ) ) {
		$l = 0;
	} elseif ( is_nan( (float) $l ) || $l > $no - 5 ) {
		return dlscalc_dls_fail( 'Overs lost before the chase must be a whole number from 0 to ' . ( $no - 5 ) . ': Team 2 needs at least 5 overs for a result in a T20.', 'l' );
	}
	$r = dlscalc_dls_match(
		array(
			'N'    => $no * 6,
			'S'    => $s,
			'G'    => 245,
			't1'   => array(),
			'M'    => ( $no - $l ) * 6,
			't2'   => dlscalc_dls_parse_stops( $p['b'] ?? '' ),
			'F'    => 't20',
			'note' => $l ? 'Rain cut ' . $l . ( 1 === $l ? ' over' : ' overs' ) . ' before the chase, so Team 2 starts with ' . ( $no - $l ) . ' overs.' : '',
		)
	);
	if ( isset( $r['err'] ) ) {
		return $r;
	}
	$r['No']   = $no;
	$r['L']    = $l;
	$r['rr']   = $r['ended'] ? '' : dlscalc_fxq( $r['target'] * 6, $r['allot'], 2 );
	$r['bars'] = array();
	for ( $mo = 5; $mo <= $no; $mo++ ) {
		$r['bars'][] = array(
			'overs'  => $mo,
			'target' => dlscalc_dls_floor( dlscalc_dls_scale( $s, dlscalc_dls_res( $mo * 6, 0 ), $r['R1'], 245 ) ) + 1,
		);
	}
	return $r;
}

/**
 * Par score table page.
 *
 * @param array $p Keys o, r and optional c as strings.
 * @return array
 */
function dlscalc_dls_par_table( $p ) {
	$no = dlscalc_dls_int( $p['o'] ?? '' );
	$s  = dlscalc_dls_int( $p['r'] ?? '' );
	if ( is_nan( (float) $no ) || $no < 5 || $no > 50 ) {
		return dlscalc_dls_fail( 'Overs per side must be between 5 and 50.', 'o' );
	}
	$e = dlscalc_dls_check( $s, 245, $p['r'] ?? '' );
	if ( $e ) {
		return $e;
	}
	$m = dlscalc_dls_blank( $p['c'] ?? null ) ? $no * 6 : dlscalc_dls_balls( $p['c'] );
	if ( is_nan( (float) $m ) ) {
		return dlscalc_dls_fail( dlscalc_dls_overs_help( 'Overs for Team 2', $p['c'] ), 'c' );
	}
	if ( $m < 6 || $m > $no * 6 ) {
		return dlscalc_dls_fail( 'Overs for Team 2 must be between 1 and ' . $no . '.', 'c' );
	}
	$r       = dlscalc_dls_match(
		array(
			'N'  => $no * 6,
			'S'  => $s,
			'G'  => 245,
			't1' => array(),
			'M'  => $m,
			't2' => array(),
		)
	);
	$r['No'] = $no;
	return $r;
}

/**
 * Live par tracker. See live() in assets/js/dls-engine.js.
 *
 * @param array $p Inputs: n, r, g, c as in dlscalc_dls_calc(), x score log.
 * @return array
 */
function dlscalc_dls_live( $p ) {
	$base = dlscalc_dls_calc(
		array(
			'n' => $p['n'] ?? '',
			'r' => $p['r'] ?? '',
			'g' => $p['g'] ?? '',
			'c' => $p['c'] ?? '',
			'f' => $p['f'] ?? '',
		)
	);
	if ( isset( $base['err'] ) ) {
		return $base;
	}
	$log       = array();
	$last      = null;
	$last_name = '';
	$now       = null;
	$parts = dlscalc_dls_blank( $p['x'] ?? '' ) ? array() : array_slice( explode( '_', (string) $p['x'] ), 0, DLSCALC_LIVE_MAX );
	foreach ( $parts as $i => $part ) {
		$x    = explode( '-', $part );
		$at   = dlscalc_dls_balls( $x[0] ?? '' );
		$runs = dlscalc_dls_int( $x[1] ?? '' );
		$w    = dlscalc_dls_int( $x[2] ?? '' );
		$name = 'Score entry ' . ( $i + 1 );
		if ( '' === implode( '', $x ) ) {
			continue;
		}
		if ( is_nan( (float) $at ) && ! dlscalc_dls_blank( $x[0] ?? '' ) ) {
			return array( 'err' => dlscalc_dls_overs_help( $name, $x[0] ) );
		}
		if ( dlscalc_dls_score( $x[1] ?? '' ) ) {
			return array( 'err' => $name . ': enter runs only, without wickets (98, not 98/6).' );
		}
		if ( is_nan( (float) $at ) || is_nan( (float) $runs ) || is_nan( (float) $w ) ) {
			return array( 'err' => $name . ': fill in overs, runs and wickets (overs like 12.3).' );
		}
		if ( $last && 'on' !== $last['state'] ) {
			return array( 'err' => 'The chase was already over before ' . strtolower( $name ) . '. Remove the entries after it.' );
		}
		if ( $at < 1 || $at > $base['allot'] ) {
			return array( 'err' => $name . ': overs must be between 0.1 and ' . dlscalc_dls_overs( $base['allot'] ) . '.' );
		}
		if ( $w > 10 || $runs > 999 ) {
			return array( 'err' => $name . ': wickets must be 0 to 10 and runs no more than 999.' );
		}
		if ( $last && $at <= $last['at'] ) {
			return array( 'err' => 'Score entries must be in order, with the overs going up.' );
		}
		if ( $last && ( $runs < $last['runs'] || $w < $last['w'] ) ) {
			$down = $runs < $last['runs'];
			return array( 'err' => ( $down ? 'Runs' : 'Wickets' ) . ' cannot go down: ' . strtolower( $name ) . ' has ' . ( $down ? $runs : $w ) . ', ' . strtolower( $last_name ) . ' has ' . ( $down ? $last['runs'] : $last['w'] ) . '.' );
		}
		$e   = array(
			'at'   => $at,
			'runs' => $runs,
			'w'    => $w,
		);
		$now = null;
		if ( $runs >= $base['target'] ) {
			$e['state'] = 'won';
			$e['par']   = $base['par'];
		} elseif ( 10 === $w || $at === $base['allot'] ) {
			$e['state'] = $runs === $base['par'] ? 'tied' : 'lost';
			$e['par']   = $base['par'];
		} else {
			$now        = dlscalc_dls_match(
				array(
					'N'  => $base['N'],
					'S'  => $base['S'],
					'G'  => $base['G'],
					't1' => array(),
					'M'  => $base['M'],
					't2' => array(
						array(
							'at'   => $at,
							'w'    => $w,
							'lost' => $base['allot'] - $at,
						),
					),
					'F'  => $base['F'],
				)
			);
			$e['state'] = 'on';
			$e['par']   = $now['par'];
			$e['warn']  = $now['warn'];
		}
		$e['diff'] = $runs - $e['par'];
		$log[]     = $e;
		$last      = $e;
		$last_name = $name;
	}
	$base['log']      = $log;
	$base['nowSteps'] = $now ? array_slice( $now['steps'], -3 ) : array();
	return $base;
}
