<?php
/**
 * NRR engine in PHP: a line-for-line copy of assets/js/nrr-engine.js, so the NRR and run rate
 * pages print real results without JavaScript. dev/tests/run.sh checks that both engines agree.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DLSCALC_MATCHES_MAX', 30 );
define( 'DLSCALC_TEAMS_MAX', 16 );

/**
 * A number, or null for a missing one (so results stay valid JSON in both engines).
 *
 * @param float|int $x Value, possibly NAN.
 * @return int|null
 */
function dlscalc_nrr_num( $x ) {
	return is_nan( (float) $x ) ? null : $x;
}

/**
 * Round the way the JS engine does and format with $d decimals, never "-0.000".
 *
 * @param float $x Value.
 * @param int   $d Decimals.
 * @return string
 */
function dlscalc_nrr_fx( $x, $d ) {
	$k = 10 ** $d;
	$v = floor( $x * $k + 0.5 ) / $k;
	return number_format( 0.0 === (float) $v ? 0 : $v, $d, '.', '' );
}

/**
 * Net run rate with a sign, 3 decimals: "+0.350", "-1.025", "0.000".
 *
 * @param float $x Value.
 * @return string
 */
function dlscalc_nrr_sign3( $x ) {
	$s = dlscalc_nrr_fx( $x, 3 );
	return '-' === $s[0] || '0.000' === $s ? $s : '+' . $s;
}

/**
 * Runs an over, from runs and balls.
 *
 * @param int $runs Runs.
 * @param int $b    Balls.
 * @return float
 */
function dlscalc_nrr_rate( $runs, $b ) {
	return $runs * 6 / $b;
}

/**
 * Net run rate, or null when either side has no overs yet.
 *
 * @param int $rf Runs for.
 * @param int $bf Balls faced.
 * @param int $ra Runs against.
 * @param int $ba Balls bowled.
 * @return float|null
 */
function dlscalc_nrr_of( $rf, $bf, $ra, $ba ) {
	return $bf > 0 && $ba > 0 ? dlscalc_nrr_rate( $rf, $bf ) - dlscalc_nrr_rate( $ra, $ba ) : null;
}

/**
 * Overs as entered in a match row: "18.3" or "18.3a" (bowled out, so the full quota counts).
 *
 * @param mixed $runs  Runs as typed.
 * @param mixed $ov    Overs as typed.
 * @param int   $quota Balls per side.
 * @return array
 */
function dlscalc_nrr_side( $runs, $ov, $quota ) {
	$ov      = trim( (string) $ov );
	$all_out = (bool) preg_match( '/a$/', $ov );
	$b       = dlscalc_dls_balls( $all_out ? substr( $ov, 0, -1 ) : $ov );
	return array(
		'runs'   => dlscalc_dls_int( $runs ),
		'at'     => $b,
		'allOut' => $all_out,
		'b'      => $all_out ? $quota : $b,
	);
}

/**
 * NRR across matches. See calc() in assets/js/nrr-engine.js.
 *
 * @param array $p Inputs: q overs per side, x matches.
 * @return array
 */
function dlscalc_nrr_calc( $p ) {
	$q = dlscalc_dls_int( $p['q'] ?? '' );
	if ( is_nan( (float) $q ) || $q < 1 || $q > 50 ) {
		return array( 'err' => 'Overs per side must be between 1 and 50.' );
	}
	$quota   = $q * 6;
	$rows    = array();
	$steps   = array();
	$rf      = 0;
	$bf      = 0;
	$ra      = 0;
	$ba      = 0;
	$counted = 0;
	$parts   = dlscalc_dls_blank( $p['x'] ?? '' ) ? array() : array_slice( explode( '_', (string) $p['x'] ), 0, DLSCALC_MATCHES_MAX );
	foreach ( $parts as $i => $part ) {
		$x    = explode( '-', $part );
		$name = 'Match ' . ( $i + 1 );
		if ( '' === implode( '', array_slice( $x, 0, 4 ) ) ) {
			continue;
		}
		$a  = dlscalc_nrr_side( $x[0] ?? '', $x[1] ?? '', $quota );
		$d  = dlscalc_nrr_side( $x[2] ?? '', $x[3] ?? '', $quota );
		$nr = 'n' === ( $x[4] ?? '' );
		if ( is_nan( (float) $a['runs'] ) || is_nan( (float) $d['runs'] ) || $a['runs'] > 999 || $d['runs'] > 999 ) {
			return array( 'err' => $name . ': enter runs scored and runs conceded.' );
		}
		if ( is_nan( (float) $a['at'] ) || is_nan( (float) $d['at'] ) ) {
			return array( 'err' => $name . ': enter overs faced and overs bowled, like 19.4 for 19 overs and 4 balls.' );
		}
		if ( $a['at'] > $quota || $d['at'] > $quota || ( ! $a['allOut'] && 0 === $a['at'] ) || ( ! $d['allOut'] && 0 === $d['at'] ) ) {
			return array( 'err' => $name . ': overs must be more than 0 and no more than ' . $q . ' per side.' );
		}
		$rows[] = array(
			'n'  => $i + 1,
			'rf' => $a['runs'],
			'bf' => $a['b'],
			'ra' => $d['runs'],
			'ba' => $d['b'],
			'nr' => $nr,
		);
		if ( $nr ) {
			$steps[] = $name . ': no result, so it is left out of net run rate.';
			continue;
		}
		++$counted;
		$rf     += $a['runs'];
		$bf     += $a['b'];
		$ra     += $d['runs'];
		$ba     += $d['b'];
		$steps[] = $name . ': scored ' . $a['runs'] . ( $a['allOut'] ? ', all out in ' . dlscalc_dls_overs( $a['at'] ) . ' overs, counted as ' . $q : ' in ' . dlscalc_dls_overs( $a['b'] ) ) .
			' overs; conceded ' . $d['runs'] . ( $d['allOut'] ? ', bowling them out in ' . dlscalc_dls_overs( $d['at'] ) . ' overs, counted as ' . $q : ' in ' . dlscalc_dls_overs( $d['b'] ) ) . ' overs.';
	}
	if ( ! $counted ) {
		return array( 'err' => 'Enter at least one match with a result.' );
	}
	$nrr     = dlscalc_nrr_of( $rf, $bf, $ra, $ba );
	$steps[] = 'Runs scored: ' . $rf . ' in ' . dlscalc_dls_overs( $bf ) . ' overs = ' . dlscalc_nrr_fx( dlscalc_nrr_rate( $rf, $bf ), 3 ) . ' an over.';
	$steps[] = 'Runs conceded: ' . $ra . ' in ' . dlscalc_dls_overs( $ba ) . ' overs = ' . dlscalc_nrr_fx( dlscalc_nrr_rate( $ra, $ba ), 3 ) . ' an over.';
	$steps[] = 'Net run rate = ' . dlscalc_nrr_fx( dlscalc_nrr_rate( $rf, $bf ), 3 ) . ' − ' . dlscalc_nrr_fx( dlscalc_nrr_rate( $ra, $ba ), 3 ) . ' = ' . dlscalc_nrr_sign3( $nrr ) . '.';
	return array(
		'Q'       => $q,
		'rows'    => $rows,
		'counted' => $counted,
		'rf'      => $rf,
		'bf'      => $bf,
		'ra'      => $ra,
		'ba'      => $ba,
		'nrr'     => dlscalc_nrr_sign3( $nrr ),
		'forRate' => dlscalc_nrr_fx( dlscalc_nrr_rate( $rf, $bf ), 3 ),
		'agRate'  => dlscalc_nrr_fx( dlscalc_nrr_rate( $ra, $ba ), 3 ),
		'steps'   => $steps,
	);
}

/**
 * Team name as typed, without the characters the permalink uses as separators.
 *
 * @param mixed $s Name.
 * @return string
 */
function dlscalc_nrr_clean_name( $s ) {
	return mb_substr( trim( preg_replace( '/\s+/u', ' ', preg_replace( '/[~_]/', ' ', (string) $s ) ) ), 0, 30 );
}

/**
 * Points table from a permalink value. See parseTeams() in assets/js/nrr-engine.js.
 *
 * @param mixed $str Value.
 * @return array[]
 */
function dlscalc_nrr_parse_teams( $str ) {
	if ( dlscalc_dls_blank( $str ) ) {
		return array();
	}
	$out = array();
	foreach ( array_slice( explode( '_', (string) $str ), 0, DLSCALC_TEAMS_MAX ) as $i => $part ) {
		$x        = explode( '~', $part );
		$name     = dlscalc_nrr_clean_name( $x[0] ?? '' );
		$t        = array(
			'name' => '' !== $name ? $name : 'Team ' . ( $i + 1 ),
			'p'    => dlscalc_nrr_num( dlscalc_dls_int( $x[1] ?? '' ) ),
			'w'    => dlscalc_nrr_num( dlscalc_dls_int( $x[2] ?? '' ) ),
			'pts'  => dlscalc_nrr_num( dlscalc_dls_int( $x[3] ?? '' ) ),
			'rf'   => dlscalc_nrr_num( dlscalc_dls_int( $x[4] ?? '' ) ),
			'bf'   => dlscalc_nrr_num( dlscalc_dls_balls( $x[5] ?? '' ) ),
			'ra'   => dlscalc_nrr_num( dlscalc_dls_int( $x[6] ?? '' ) ),
			'ba'   => dlscalc_nrr_num( dlscalc_dls_balls( $x[7] ?? '' ) ),
		);
		$t['raw'] = array(
			'p'   => $x[1] ?? '',
			'w'   => $x[2] ?? '',
			'pts' => $x[3] ?? '',
			'rf'  => $x[4] ?? '',
			'of'  => $x[5] ?? '',
			'ra'  => $x[6] ?? '',
			'oa'  => $x[7] ?? '',
		);
		$t['ok']  = null !== $t['rf'] && null !== $t['ra'] && $t['bf'] > 0 && $t['ba'] > 0;
		$t['val'] = $t['ok'] ? dlscalc_nrr_of( $t['rf'], $t['bf'], $t['ra'], $t['ba'] ) : null;
		$t['nrr'] = $t['ok'] ? dlscalc_nrr_sign3( $t['val'] ) : '–';
		$out[]    = $t;
	}
	return $out;
}

/**
 * Teams in table order: points, then net run rate. Teams without numbers go last.
 *
 * @param array[] $teams Teams.
 * @return int[] Indexes into $teams.
 */
function dlscalc_nrr_standings( $teams ) {
	$order = array_keys( $teams );
	usort(
		$order,
		function ( $i, $j ) use ( $teams ) {
			$a  = $teams[ $i ];
			$b  = $teams[ $j ];
			$pa = null === $a['pts'] ? -1 : $a['pts'];
			$pb = null === $b['pts'] ? -1 : $b['pts'];
			if ( $pa !== $pb ) {
				return $pb - $pa;
			}
			$na = $a['ok'] ? $a['val'] : -1e9;
			$nb = $b['ok'] ? $b['val'] : -1e9;
			return $na !== $nb ? ( $nb > $na ? 1 : -1 ) : $i - $j;
		}
	);
	return $order;
}

/**
 * Qualification solver. See solve() in assets/js/nrr-engine.js.
 *
 * @param array $p Inputs: t teams, y, v, o, k, e, q, d.
 * @return array
 */
function dlscalc_nrr_solve( $p ) {
	$teams = dlscalc_nrr_parse_teams( $p['t'] ?? '' );
	$y     = dlscalc_dls_int( $p['y'] ?? '' );
	$v     = dlscalc_dls_int( $p['v'] ?? '' );
	$o     = dlscalc_dls_blank( $p['o'] ?? '' ) ? 0 : dlscalc_dls_int( $p['o'] );
	$q     = dlscalc_dls_int( $p['q'] ?? '' );
	$e     = dlscalc_dls_int( $p['e'] ?? '' );
	$d     = dlscalc_dls_blank( $p['d'] ?? '' ) ? 2 : dlscalc_dls_int( $p['d'] );
	$bat   = 'c' === ( $p['k'] ?? '' ) ? 'c' : 'f';
	$res   = array(
		'teams' => $teams,
		'order' => dlscalc_nrr_standings( $teams ),
	);
	if ( count( $teams ) < 2 ) {
		$res['err'] = 'Add at least two teams to the points table.';
		return $res;
	}
	$me = is_nan( (float) $y ) ? null : ( $teams[ $y - 1 ] ?? null );
	$rv = is_nan( (float) $v ) ? null : ( $teams[ $v - 1 ] ?? null );
	if ( ! $me || ! $rv ) {
		$res['err'] = 'Choose your team and the team to pass.';
		return $res;
	}
	if ( $y === $v ) {
		$res['err'] = 'Your team and the team to pass must be different.';
		return $res;
	}
	if ( ! $me['ok'] || ! $rv['ok'] ) {
		$res['err'] = 'Fill in runs and overs for ' . ( $me['ok'] ? $rv['name'] : $me['name'] ) . ' in the points table.';
		return $res;
	}
	if ( is_nan( (float) $o ) || $o === $y || $o > count( $teams ) ) {
		$res['err'] = 'Choose a different team as the next opponent.';
		return $res;
	}
	if ( is_nan( (float) $q ) || $q < 1 || $q > 50 ) {
		$res['err'] = 'Overs per side must be between 1 and 50.';
		return $res;
	}
	if ( is_nan( (float) $e ) || $e > 999 ) {
		$res['err'] = 'f' === $bat ? 'Enter the score you expect to make.' : 'Enter the opponent\'s score you will chase.';
		return $res;
	}
	if ( is_nan( (float) $d ) || $d > 10 ) {
		$res['err'] = 'Points for a win must be between 0 and 10.';
		return $res;
	}
	$vs    = $o === $v;
	$n     = $q * 6;
	$ov    = 'dlscalc_dls_overs';
	$s3    = 'dlscalc_nrr_sign3';
	$steps = array(
		$me['name'] . ' now: ' . $me['rf'] . ' runs in ' . $ov( $me['bf'] ) . ' overs, ' . $me['ra'] . ' conceded in ' . $ov( $me['ba'] ) . ' overs, NRR ' . $me['nrr'] . '.',
		$rv['name'] . ' now: ' . $rv['rf'] . ' runs in ' . $ov( $rv['bf'] ) . ' overs, ' . $rv['ra'] . ' conceded in ' . $ov( $rv['ba'] ) . ' overs, NRR ' . $rv['nrr'] . '.',
	);
	$res['me']   = $me['name'];
	$res['rv']   = $rv['name'];
	$res['vs']   = $vs;
	$res['bat']  = $bat;
	$res['note'] = $vs ? 'Your opponent is ' . $rv['name'] . ', so its net run rate changes too.' : 'Assumes ' . $rv['name'] . ' does not play again before your match.';

	if ( null !== $me['pts'] && null !== $rv['pts'] ) {
		$after   = $me['pts'] + $d;
		$steps[] = 'Points: a win takes ' . $me['name'] . ' from ' . $me['pts'] . ' to ' . $after . '; ' . $rv['name'] . ' has ' . $rv['pts'] . '.';
		if ( $after !== $rv['pts'] ) {
			$res['kind']  = $after > $rv['pts'] ? 'points' : 'short';
			$res['big']   = $after > $rv['pts'] ? 'Any win' : 'Not on points';
			$res['line']  = $after > $rv['pts']
				? 'A win takes ' . $me['name'] . ' to ' . $after . ' points, above ' . $rv['name'] . ' on ' . $rv['pts'] . ', so the margin does not matter.'
				: 'Even with a win, ' . $me['name'] . ' has ' . $after . ' points, behind ' . $rv['name'] . ' on ' . $rv['pts'] . '. Net run rate only separates teams level on points.';
			$steps[]      = $after > $rv['pts'] ? 'More points than ' . $rv['name'] . ', so net run rate is not needed.' : 'Fewer points than ' . $rv['name'] . ', so net run rate cannot help yet.';
			$res['steps'] = $steps;
			return $res;
		}
		$steps[] = 'Level on points after a win, so net run rate decides.';
	}

	// Your new NRR and the rival's, for your runs and balls and the opponent's runs.
	$mine   = function ( $runs, $b, $opp ) use ( $me, $n ) {
		return dlscalc_nrr_of( $me['rf'] + $runs, $me['bf'] + $b, $me['ra'] + $opp, $me['ba'] + $n );
	};
	$theirs = function ( $runs, $b, $opp ) use ( $rv, $n, $vs ) {
		return $vs ? dlscalc_nrr_of( $rv['rf'] + $opp, $rv['bf'] + $n, $rv['ra'] + $runs, $rv['ba'] + $b ) : $rv['val'];
	};

	if ( 'f' === $bat ) {
		$ymax = -1;
		for ( $yy = $e - 1; $yy >= 0; $yy-- ) {
			if ( $mine( $e, $n, $yy ) > $theirs( $e, $n, $yy ) ) {
				$ymax = $yy;
				break;
			}
		}
		$steps[] = 'Batting first, ' . $me['name'] . ' scores ' . $e . ' in ' . $q . ' overs. The opponent\'s innings counts as ' . $q . ' overs whether it is bowled out or not.';
		if ( $ymax < 0 ) {
			$res['kind'] = 'none';
			$res['big']  = 'Not enough';
			$res['line'] = 'Even bowling the opponent out for 0 would not lift ' . $me['name'] . ' above ' . $rv['name'] . ' on net run rate. Aim for a bigger total.';
			$steps[]     = 'Opponent all out for 0: ' . $me['name'] . ' NRR ' . $s3( $mine( $e, $n, 0 ) ) . ', ' . $rv['name'] . ' ' . $s3( $theirs( $e, $n, 0 ) ) . '. Still not above.';
		} else {
			$res['kind']   = 'runs';
			$res['margin'] = $e - $ymax;
			$res['limit']  = $ymax;
			$res['big']    = $ymax === $e - 1 ? 'Any win' : ( $e - $ymax ) . ' runs';
			$res['line']   = 'Win by at least ' . ( $e - $ymax ) . ( 1 === $e - $ymax ? ' run' : ' runs' ) . ': hold the opponent to ' . $ymax . ' or fewer in ' . $q . ' overs.';
			$steps[]       = 'Opponent makes ' . $ymax . ': ' . $me['name'] . ' NRR ' . $s3( $mine( $e, $n, $ymax ) ) . ', ' . $rv['name'] . ' ' . $s3( $theirs( $e, $n, $ymax ) ) . '. ' . $me['name'] . ' is above.';
			if ( $ymax < $e - 1 ) {
				$steps[] = 'Opponent makes ' . ( $ymax + 1 ) . ': ' . $me['name'] . ' NRR ' . $s3( $mine( $e, $n, $ymax + 1 ) ) . ', ' . $rv['name'] . ' ' . $s3( $theirs( $e, $n, $ymax + 1 ) ) . '. Not above, so ' . $ymax . ' is the most the opponent can make.';
			}
		}
	} else {
		$t    = $e + 1;
		$bmax = 0;
		for ( $b = $n; $b >= 1; $b-- ) {
			if ( $mine( $t, $b, $e ) > $theirs( $t, $b, $e ) ) {
				$bmax = $b;
				break;
			}
		}
		$steps[] = 'Chasing ' . $e . ', ' . $me['name'] . ' needs ' . $t . ' to win. The opponent\'s innings counts as ' . $q . ' overs; your chase counts the overs you actually face.';
		if ( ! $bmax ) {
			$res['kind'] = 'none';
			$res['big']  = 'Not possible';
			$res['line'] = 'No winning chase of ' . $t . ' is fast enough to lift ' . $me['name'] . ' above ' . $rv['name'] . ' on net run rate.';
			$steps[]     = 'Even reaching ' . $t . ' off the first ball gives ' . $me['name'] . ' ' . $s3( $mine( $t, 1, $e ) ) . ' against ' . $rv['name'] . ' ' . $s3( $theirs( $t, 1, $e ) ) . '.';
		} else {
			$res['kind']   = 'overs';
			$res['within'] = $ov( $bmax );
			$res['big']    = $bmax === $n ? 'Any win' : $ov( $bmax ) . ' overs';
			$res['line']   = 'Reach ' . $t . ' within ' . $ov( $bmax ) . ' overs (' . $bmax . ' balls) to move above ' . $rv['name'] . '.';
			$steps[]       = 'Reaching ' . $t . ' in ' . $ov( $bmax ) . ' overs: ' . $me['name'] . ' NRR ' . $s3( $mine( $t, $bmax, $e ) ) . ', ' . $rv['name'] . ' ' . $s3( $theirs( $t, $bmax, $e ) ) . '. ' . $me['name'] . ' is above.';
			if ( $bmax < $n ) {
				$steps[] = 'One ball slower, ' . $ov( $bmax + 1 ) . ' overs: ' . $me['name'] . ' NRR ' . $s3( $mine( $t, $bmax + 1, $e ) ) . ', ' . $rv['name'] . ' ' . $s3( $theirs( $t, $bmax + 1, $e ) ) . '. Not above.';
			}
		}
	}
	$steps[]      = 'Assumes ' . $me['name'] . ' makes exactly the runs shown. A bigger total or a quicker finish only helps.';
	$res['steps'] = $steps;
	return $res;
}

/**
 * Run rate calculator. See runRate() in assets/js/nrr-engine.js.
 *
 * @param array $p Inputs: u, n, t, r, o.
 * @return array
 */
function dlscalc_nrr_run_rate( $p ) {
	$chase = 'f' !== ( $p['u'] ?? '' );
	$nn    = dlscalc_dls_int( $p['n'] ?? '' );
	$r     = dlscalc_dls_int( $p['r'] ?? '' );
	$b     = dlscalc_dls_balls( $p['o'] ?? '' );
	if ( is_nan( (float) $nn ) || $nn < 1 || $nn > 50 ) {
		return array( 'err' => 'Overs per side must be between 1 and 50.' );
	}
	$n = $nn * 6;
	$t = dlscalc_dls_int( $p['t'] ?? '' );
	if ( $chase && ( is_nan( (float) $t ) || $t < 1 || $t > 999 ) ) {
		return array( 'err' => 'Enter the target, the runs needed to win.' );
	}
	if ( is_nan( (float) $r ) || $r > 999 ) {
		return array( 'err' => 'Enter the runs scored so far.' );
	}
	if ( is_nan( (float) $b ) ) {
		return array( 'err' => 'Enter the overs bowled so far, like 12.4 for 12 overs and 4 balls.' );
	}
	if ( $b > $n ) {
		return array( 'err' => 'Overs bowled cannot be more than ' . $nn . ' overs per side.' );
	}
	$left    = $n - $b;
	$crr     = $b > 0 ? dlscalc_nrr_rate( $r, $b ) : 0;
	$lines   = array();
	$lines[] = array( 'Current run rate', $b > 0 ? dlscalc_nrr_fx( $crr, 2 ) : '–' );
	if ( $chase ) {
		$need = $t - $r;
		if ( $need <= 0 ) {
			return array(
				'mode'  => 'c',
				'state' => 'won',
				'label' => 'Result',
				'big'   => 'Won',
				'line'  => 'The target of ' . $t . ' has been reached' . ( $left ? ' with ' . $left . ( 1 === $left ? ' ball' : ' balls' ) . ' to spare.' : '.' ),
				'lines' => $lines,
			);
		}
		if ( 0 === $left ) {
			return array(
				'mode'  => 'c',
				'state' => 'over',
				'label' => 'Result',
				'big'   => 1 === $need ? 'Tied' : 'Lost',
				'line'  => 1 === $need ? 'Scores level with no balls left.' : 'No balls left: short by ' . ( $need - 1 ) . ( 2 === $need ? ' run.' : ' runs.' ),
				'lines' => $lines,
			);
		}
		$rrr     = dlscalc_nrr_rate( $need, $left );
		$lines[] = array( 'Runs needed', (string) $need );
		$lines[] = array( 'Balls left', $left . ' (' . dlscalc_dls_overs( $left ) . ' overs)' );
		$lines[] = array( 'Runs needed per ball', dlscalc_nrr_fx( $need / $left, 2 ) );
		if ( $b > 0 ) {
			$lines[] = array( 'Required minus current', ( $rrr >= $crr ? '+' : '' ) . dlscalc_nrr_fx( $rrr - $crr, 2 ) . ' an over' );
		}
		return array(
			'mode'  => 'c',
			'state' => 'on',
			'label' => 'Required run rate',
			'big'   => dlscalc_nrr_fx( $rrr, 2 ),
			'rrr'   => dlscalc_nrr_fx( $rrr, 2 ),
			'crr'   => dlscalc_nrr_fx( $crr, 2 ),
			'need'  => $need,
			'left'  => $left,
			'line'  => 'Need ' . $need . ( 1 === $need ? ' run' : ' runs' ) . ' off ' . $left . ( 1 === $left ? ' ball' : ' balls' ) . ' (' . dlscalc_dls_overs( $left ) . ' overs)',
			'lines' => $lines,
		);
	}
	if ( 0 === $b ) {
		return array( 'err' => 'Enter at least one ball bowled to project the score.' );
	}
	$proj = function ( $rate ) use ( $r, $left ) {
		return (string) (int) floor( $r + $rate * $left / 6 + 0.5 );
	};
	for ( $d = 1; $d <= 3; $d++ ) {
		$lines[] = array( 'At ' . dlscalc_nrr_fx( $crr + $d, 2 ) . ' an over (+' . $d . ')', $proj( $crr + $d ) );
	}
	foreach ( array( 6, 8, 10, 12 ) as $rate ) {
		$lines[] = array( 'At ' . $rate . ' an over', $proj( $rate ) );
	}
	return array(
		'mode'  => 'f',
		'state' => 'on',
		'label' => 'Projected score at the current rate',
		'big'   => $proj( $crr ),
		'crr'   => dlscalc_nrr_fx( $crr, 2 ),
		'left'  => $left,
		'line'  => $left ? dlscalc_dls_overs( $left ) . ' overs left at ' . dlscalc_nrr_fx( $crr, 2 ) . ' an over' : 'Innings complete',
		'lines' => $lines,
	);
}

/**
 * Overs and balls converter.
 *
 * @param array $p Inputs: v overs, j balls.
 * @return array{ov: string, bl: string}
 */
function dlscalc_nrr_convert( $p ) {
	$b = dlscalc_dls_balls( $p['v'] ?? '' );
	$n = dlscalc_dls_int( $p['j'] ?? '' );
	return array(
		'ov' => is_nan( (float) $b ) ? '' : $b . ( 1 === $b ? ' ball' : ' balls' ) . ' (' . dlscalc_nrr_fx( $b / 6, 2 ) . ' overs in decimal)',
		'bl' => is_nan( (float) $n ) ? '' : dlscalc_dls_overs( $n ) . ' overs',
	);
}
