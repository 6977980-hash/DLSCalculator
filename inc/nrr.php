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
 * Runs an over, from runs and balls, 3 decimals.
 *
 * @param int $runs Runs.
 * @param int $b    Balls.
 * @return string
 */
function dlscalc_nrr_rate3( $runs, $b ) {
	return dlscalc_fxq( $runs * 6, $b, 3 );
}

/**
 * Net run rate with a sign, 3 decimals: "+0.350", "-1.025", "0.000". See nrrText() in assets/js/nrr-engine.js.
 *
 * @param int[] $t Runs for, balls faced, runs against, balls bowled.
 * @return string
 */
function dlscalc_nrr_text( $t ) {
	$s = dlscalc_fxq( 6 * ( $t[0] * $t[3] - $t[2] * $t[1] ), $t[1] * $t[3], 3 );
	return '-' === $s[0] || '0.000' === $s ? $s : '+' . $s;
}

/**
 * Compares two net run rates exactly with whole-number cross products (audit F25). See cmpNrr()
 * in assets/js/nrr-engine.js.
 *
 * @param int[] $a Runs for, balls faced, runs against, balls bowled.
 * @param int[] $b The same for the other team.
 * @return int 1 when $a is higher, -1 when lower, 0 when equal.
 */
function dlscalc_nrr_cmp( $a, $b ) {
	$x = ( $a[0] * $a[3] - $a[2] * $a[1] ) * ( $b[1] * $b[3] ) - ( $b[0] * $b[3] - $b[2] * $b[1] ) * ( $a[1] * $a[3] );
	return $x <=> 0;
}

/**
 * Error for an overs figure with more than 5 balls after the dot ("20.7"), naming the field and the
 * 0-to-5 rule (audit F39); any other bad figure gets the fallback. See oversErr() in nrr-engine.js.
 *
 * @param string $label    Field name.
 * @param mixed  $s        Overs as typed.
 * @param string $fallback Message for any other bad figure.
 * @return string
 */
function dlscalc_nrr_overs_err( $label, $s, $fallback ) {
	if ( preg_match( '/^(\d{1,5})\.(\d)a?$/', dlscalc_dls_overs_in( $s ), $m ) && (int) $m[2] > 5 ) {
		return $label . ': ' . $m[1] . '.' . $m[2] . ' is not valid. Balls after the dot go from 0 to 5, so after ' . $m[1] . '.5 comes ' . ( (int) $m[1] + 1 ) . '.';
	}
	return $fallback;
}

/**
 * Overs as entered in a match row: "18.3" or "18.3a" (bowled out, so the full quota for the match counts).
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
		$extra = array_slice( $x, 4 );
		$nr    = in_array( 'n', $extra, true );
		$mq    = $quota;
		$mo    = array_values(
			array_filter(
				$extra,
				function ( $t ) {
					return 'n' !== $t && '' !== $t;
				}
			)
		);
		if ( $mo ) {
			$mq = dlscalc_dls_balls( $mo[0] );
			if ( is_nan( (float) $mq ) || $mq < 1 || $mq > $quota ) {
				return array( 'err' => dlscalc_nrr_overs_err( $name . ', overs per side in this match', $mo[0], $name . ': overs per side in this match must be between 0.1 and ' . $q . '.' ) );
			}
		}
		$cut = $mq < $quota;
		$mov = dlscalc_dls_overs( $mq );
		$a   = dlscalc_nrr_side( $x[0] ?? '', $x[1] ?? '', $mq );
		$d   = dlscalc_nrr_side( $x[2] ?? '', $x[3] ?? '', $mq );
		if ( dlscalc_dls_neg( $x[0] ?? '' ) || dlscalc_dls_neg( $x[2] ?? '' ) ) {
			return array( 'err' => $name . ': runs cannot be negative.' );
		}
		if ( is_nan( (float) $a['runs'] ) || is_nan( (float) $d['runs'] ) || $a['runs'] > 999 || $d['runs'] > 999 ) {
			return array( 'err' => $name . ': enter runs scored and runs conceded.' );
		}
		if ( is_nan( (float) $a['at'] ) || is_nan( (float) $d['at'] ) ) {
			$bad = is_nan( (float) $a['at'] );
			return array( 'err' => dlscalc_nrr_overs_err( $name . ( $bad ? ', overs faced' : ', overs bowled' ), $bad ? ( $x[1] ?? '' ) : ( $x[3] ?? '' ), $name . ': enter overs faced and overs bowled, like 19.4 for 19 overs and 4 balls.' ) );
		}
		if ( $a['at'] > $mq || $d['at'] > $mq || ( ! $a['allOut'] && 0 === $a['at'] ) || ( ! $d['allOut'] && 0 === $d['at'] ) ) {
			return array( 'err' => $name . ': overs must be more than 0 and no more than ' . $mov . ' per side' . ( $cut ? ' in this match.' : '.' ) );
		}
		$rows[] = array(
			'n'  => $i + 1,
			'rf' => $a['runs'],
			'bf' => $a['b'],
			'ra' => $d['runs'],
			'ba' => $d['b'],
			'nr' => $nr,
			'mq' => $mq,
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
		$steps[] = $name . ( $cut ? ' (cut to ' . $mov . ' overs a side)' : '' ) . ': scored ' . $a['runs'] . ( $a['allOut'] ? ', all out in ' . dlscalc_dls_overs( $a['at'] ) . ' overs, counted as ' . $mov : ' in ' . dlscalc_dls_overs( $a['b'] ) ) .
			' overs; conceded ' . $d['runs'] . ( $d['allOut'] ? ', bowling them out in ' . dlscalc_dls_overs( $d['at'] ) . ' overs, counted as ' . $mov : ' in ' . dlscalc_dls_overs( $d['b'] ) ) . ' overs.';
	}
	if ( ! $counted ) {
		return array( 'err' => 'Enter at least one match with a result.' );
	}
	$nrr     = dlscalc_nrr_text( array( $rf, $bf, $ra, $ba ) );
	$steps[] = 'Runs scored: ' . $rf . ' in ' . dlscalc_dls_overs( $bf ) . ' overs = ' . dlscalc_nrr_rate3( $rf, $bf ) . ' an over.';
	$steps[] = 'Runs conceded: ' . $ra . ' in ' . dlscalc_dls_overs( $ba ) . ' overs = ' . dlscalc_nrr_rate3( $ra, $ba ) . ' an over.';
	$steps[] = 'Net run rate = ' . dlscalc_nrr_rate3( $rf, $bf ) . ' − ' . dlscalc_nrr_rate3( $ra, $ba ) . ' = ' . $nrr . '.';
	return array(
		'Q'       => $q,
		'rows'    => $rows,
		'counted' => $counted,
		'rf'      => $rf,
		'bf'      => $bf,
		'ra'      => $ra,
		'ba'      => $ba,
		'nrr'     => $nrr,
		'forRate' => dlscalc_nrr_rate3( $rf, $bf ),
		'agRate'  => dlscalc_nrr_rate3( $ra, $ba ),
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
		$t['tot'] = $t['ok'] ? array( $t['rf'], $t['bf'], $t['ra'], $t['ba'] ) : null;
		$t['nrr'] = $t['ok'] ? dlscalc_nrr_text( $t['tot'] ) : '–';
		$out[]    = $t;
	}
	return $out;
}

/**
 * Teams in table order: points, then wins when $z is "w" (ICC events), then net run rate.
 * Teams without numbers go last.
 *
 * @param array[] $teams Teams.
 * @param string  $z     "w" for wins before net run rate.
 * @return int[] Indexes into $teams.
 */
function dlscalc_nrr_standings( $teams, $z = '' ) {
	$order = array_keys( $teams );
	usort(
		$order,
		function ( $i, $j ) use ( $teams, $z ) {
			$a  = $teams[ $i ];
			$b  = $teams[ $j ];
			$pa = null === $a['pts'] ? -1 : $a['pts'];
			$pb = null === $b['pts'] ? -1 : $b['pts'];
			if ( $pa !== $pb ) {
				return $pb - $pa;
			}
			$wa = null === $a['w'] ? -1 : $a['w'];
			$wb = null === $b['w'] ? -1 : $b['w'];
			if ( 'w' === $z && $wa !== $wb ) {
				return $wb - $wa;
			}
			if ( $a['ok'] !== $b['ok'] ) {
				return $a['ok'] ? -1 : 1;
			}
			$c = $a['ok'] ? dlscalc_nrr_cmp( $b['tot'], $a['tot'] ) : 0;
			return $c ? $c : $i - $j;
		}
	);
	return $order;
}

/**
 * Qualification solver. See solve() in assets/js/nrr-engine.js.
 *
 * @param array $p Inputs: t teams, y, v, o, k, e, q, d, z.
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
	$z     = 'w' === ( $p['z'] ?? '' ) ? 'w' : '';
	$res   = array(
		'teams' => $teams,
		'order' => dlscalc_nrr_standings( $teams, $z ),
		'z'     => $z,
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
		$res['err'] = dlscalc_dls_neg( $p['e'] ?? '' ) ? 'The score cannot be negative.' : ( 'f' === $bat ? 'Enter the score you expect to make.' : 'Enter the opponent\'s score you will chase.' );
		return $res;
	}
	if ( is_nan( (float) $d ) || $d > 10 ) {
		$res['err'] = 'Points for a win must be between 0 and 10.';
		return $res;
	}
	$vs    = $o === $v;
	$n     = $q * 6;
	$ov    = 'dlscalc_dls_overs';
	$s3    = 'dlscalc_nrr_text';
	$steps = array(
		$me['name'] . ' now: ' . $me['rf'] . ' runs in ' . $ov( $me['bf'] ) . ' overs, ' . $me['ra'] . ' conceded in ' . $ov( $me['ba'] ) . ' overs, NRR ' . $me['nrr'] . '.',
		$rv['name'] . ' now: ' . $rv['rf'] . ' runs in ' . $ov( $rv['bf'] ) . ' overs, ' . $rv['ra'] . ' conceded in ' . $ov( $rv['ba'] ) . ' overs, NRR ' . $rv['nrr'] . '.',
	);
	$res['me']   = $me['name'];
	$res['rv']   = $rv['name'];
	$res['vs']   = $vs;
	$res['bat']  = $bat;
	$res['note'] = ( $z ? 'Level on points: most wins, then net run rate. ' : 'Level on points: net run rate decides. ' )
		. ( $vs ? 'Your opponent is ' . $rv['name'] . ', so its net run rate changes too.' : 'Assumes ' . $rv['name'] . ' does not play again before your match.' );

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
		if ( $z ) {
			if ( null === $me['w'] || null === $rv['w'] ) {
				$res['err'] = 'Fill in wins for ' . ( null === $me['w'] ? $me['name'] : $rv['name'] ) . ' in the points table: teams level on points are split by wins first.';
				return $res;
			}
			$won     = $me['w'] + 1;
			$steps[] = 'Level on points after a win, so wins come next: ' . $me['name'] . ' would have ' . $won . ', ' . $rv['name'] . ' has ' . $rv['w'] . '.';
			if ( $won !== $rv['w'] ) {
				$res['kind']  = $won > $rv['w'] ? 'points' : 'short';
				$res['big']   = $won > $rv['w'] ? 'Any win' : 'Not on wins';
				$res['line']  = $won > $rv['w']
					? 'A win puts ' . $me['name'] . ' level with ' . $rv['name'] . ' on ' . $after . ' points but ahead on wins, ' . $won . ' to ' . $rv['w'] . ', so the margin does not matter.'
					: 'A win puts ' . $me['name'] . ' level with ' . $rv['name'] . ' on ' . $after . ' points, but behind on wins, ' . $won . ' to ' . $rv['w'] . '. Net run rate only counts when wins are level too.';
				$steps[]      = $won > $rv['w'] ? 'More wins than ' . $rv['name'] . ', so net run rate is not needed.' : 'Fewer wins than ' . $rv['name'] . ', so net run rate cannot help.';
				$res['steps'] = $steps;
				return $res;
			}
			$steps[] = 'Level on wins too, so net run rate decides.';
		} else {
			$steps[] = 'Level on points after a win, so net run rate decides.';
		}
	}

	// Your runs and balls for and against after the match, and the rival's, as [rf, bf, ra, ba].
	$mine   = function ( $runs, $b, $opp ) use ( $me, $n ) {
		return array( $me['rf'] + $runs, $me['bf'] + $b, $me['ra'] + $opp, $me['ba'] + $n );
	};
	$theirs = function ( $runs, $b, $opp ) use ( $rv, $n, $vs ) {
		return $vs ? array( $rv['rf'] + $opp, $rv['bf'] + $n, $rv['ra'] + $runs, $rv['ba'] + $b ) : $rv['tot'];
	};
	$above  = function ( $runs, $b, $opp ) use ( $mine, $theirs ) {
		return dlscalc_nrr_cmp( $mine( $runs, $b, $opp ), $theirs( $runs, $b, $opp ) ) > 0;
	};

	if ( 'f' === $bat ) {
		$ymax = -1;
		for ( $yy = $e - 1; $yy >= 0; $yy-- ) {
			if ( $above( $e, $n, $yy ) ) {
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
			if ( $above( $t, $b, $e ) ) {
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
		return array( 'err' => dlscalc_dls_neg( $p['t'] ?? '' ) ? 'The target cannot be negative.' : 'Enter the target, the runs needed to win.' );
	}
	if ( is_nan( (float) $r ) || $r > 999 ) {
		return array( 'err' => dlscalc_dls_neg( $p['r'] ?? '' ) ? 'Runs scored cannot be negative.' : 'Enter the runs scored so far.' );
	}
	if ( is_nan( (float) $b ) ) {
		return array( 'err' => dlscalc_nrr_overs_err( 'Overs bowled so far', $p['o'] ?? '', 'Enter the overs bowled so far, like 12.4 for 12 overs and 4 balls.' ) );
	}
	if ( $b > $n ) {
		return array( 'err' => 'Overs bowled cannot be more than ' . $nn . ' overs per side.' );
	}
	$left    = $n - $b;
	$crr     = $b > 0 ? $r * 6 / $b : 0;
	$lines   = array();
	$lines[] = array( 'Current run rate', $b > 0 ? dlscalc_fxq( $r * 6, $b, 2 ) : '–' );
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
		$rrr     = dlscalc_fxq( $need * 6, $left, 2 );
		$lines[] = array( 'Runs needed', (string) $need );
		$lines[] = array( 'Balls left', $left . ' (' . dlscalc_dls_overs( $left ) . ' overs)' );
		$lines[] = array( 'Runs needed per ball', dlscalc_fxq( $need, $left, 2 ) );
		if ( $b > 0 ) {
			$lines[] = array( 'Required minus current', ( $need * $b >= $r * $left ? '+' : '' ) . dlscalc_fxq( 6 * ( $need * $b - $r * $left ), $left * $b, 2 ) . ' an over' );
		}
		return array(
			'mode'  => 'c',
			'state' => 'on',
			'label' => 'Required run rate',
			'big'   => $rrr,
			'rrr'   => $rrr,
			'crr'   => $b > 0 ? dlscalc_fxq( $r * 6, $b, 2 ) : '0.00',
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
		$lines[] = array( 'At ' . dlscalc_fxq( $r * 6 + $d * $b, $b, 2 ) . ' an over (+' . $d . ')', $proj( $crr + $d ) );
	}
	foreach ( array( 6, 8, 10, 12 ) as $rate ) {
		$lines[] = array( 'At ' . $rate . ' an over', $proj( $rate ) );
	}
	return array(
		'mode'  => 'f',
		'state' => 'on',
		'label' => 'Projected score at the current rate',
		'big'   => $proj( $crr ),
		'crr'   => dlscalc_fxq( $r * 6, $b, 2 ),
		'left'  => $left,
		'line'  => $left ? dlscalc_dls_overs( $left ) . ' overs left at ' . dlscalc_fxq( $r * 6, $b, 2 ) . ' an over' : 'Innings complete',
		'lines' => $lines,
	);
}

/**
 * A career total such as runs or balls: a whole number up to 999999.
 *
 * @param mixed $s Value as typed.
 * @return int|float NAN when invalid.
 */
function dlscalc_nrr_count( $s ) {
	$s = trim( (string) $s );
	return preg_match( '/^\d{1,6}$/', $s ) ? (int) $s : NAN;
}

/**
 * Career overs such as "1043.2", up to 99999 overs, to balls.
 *
 * @param mixed $s Overs as typed.
 * @return int|float NAN when invalid.
 */
function dlscalc_nrr_career_balls( $s ) {
	$s = dlscalc_dls_overs_in( $s );
	if ( ! preg_match( '/^\d{1,5}(\.\d)?$/', $s ) ) {
		return NAN;
	}
	$p = explode( '.', $s );
	$b = isset( $p[1] ) ? (int) $p[1] : 0;
	return $b > 5 ? NAN : (int) $p[0] * 6 + $b;
}

/**
 * Strike rate and batting average.
 *
 * @param array $p Inputs: r runs, b balls faced, i innings, o not outs, f fours, x sixes, g target strike rate.
 * @return array
 */
function dlscalc_nrr_batting( $p ) {
	$r  = dlscalc_nrr_count( $p['r'] ?? '' );
	$b  = dlscalc_nrr_count( $p['b'] ?? '' );
	$i  = dlscalc_dls_blank( $p['i'] ?? '' ) ? null : dlscalc_nrr_count( $p['i'] );
	$o  = dlscalc_dls_blank( $p['o'] ?? '' ) ? 0 : dlscalc_nrr_count( $p['o'] );
	$f  = dlscalc_dls_blank( $p['f'] ?? '' ) ? 0 : dlscalc_nrr_count( $p['f'] );
	$x  = dlscalc_dls_blank( $p['x'] ?? '' ) ? 0 : dlscalc_nrr_count( $p['x'] );
	$g  = dlscalc_dls_blank( $p['g'] ?? '' ) ? null : dlscalc_nrr_count( $p['g'] );
	$na = function ( $v ) {
		return is_float( $v ) && is_nan( $v );
	};
	if ( $na( $r ) ) {
		return array(
			'err'   => dlscalc_dls_neg( $p['r'] ?? '' ) ? 'Runs cannot be negative.' : 'Enter the runs scored.',
			'field' => 'r',
		);
	}
	if ( $na( $b ) || $b < 1 ) {
		return array(
			'err'   => 'Enter the balls faced, at least 1.',
			'field' => 'b',
		);
	}
	if ( null !== $i && $na( $i ) ) {
		return array(
			'err'   => 'Innings must be a whole number.',
			'field' => 'i',
		);
	}
	// Runs off balls faced need at least one innings; 0 would read as "never out" (audit F39).
	if ( 0 === $i ) {
		return array(
			'err'   => 'Innings must be at least 1, or leave it blank.',
			'field' => 'i',
		);
	}
	if ( $na( $o ) || ( null !== $i && $o > $i ) ) {
		return array(
			'err'   => 'Not outs must be a whole number, no more than the innings.',
			'field' => 'o',
		);
	}
	if ( $na( $f ) || $na( $x ) ) {
		return array(
			'err'   => 'Fours and sixes must be whole numbers.',
			'field' => $na( $f ) ? 'f' : 'x',
		);
	}
	if ( $f * 4 + $x * 6 > $r || $f + $x > $b ) {
		return array(
			'err'   => 'The fours and sixes add up to more runs or balls than the batter had.',
			'field' => 'f',
		);
	}
	if ( null !== $g && ( $na( $g ) || $g < 1 || $g > 600 ) ) {
		return array(
			'err'   => 'Target strike rate must be between 1 and 600.',
			'field' => 'g',
		);
	}
	$sr      = dlscalc_fxq( $r * 100, $b, 2 );
	$outs    = null === $i ? null : $i - $o;
	$avg     = $outs ? dlscalc_fxq( $r, $outs, 2 ) : null;
	$lines   = array();
	$lines[] = array( 'Batting average', null !== $avg ? $avg : ( null === $i ? 'Add innings and not outs' : 'None yet: never out' ) );
	$lines[] = array( 'Runs per ball', dlscalc_fxq( $r, $b, 2 ) );
	if ( $i ) {
		$lines[] = array( 'Runs per innings', dlscalc_fxq( $r, $i, 2 ) );
	}
	if ( $outs ) {
		$lines[] = array( 'Balls per dismissal', dlscalc_fxq( $b, $outs, 1 ) );
	}
	if ( $f + $x > 0 ) {
		$lines[] = array( 'Runs in fours and sixes', ( $f * 4 + $x * 6 ) . ' (' . dlscalc_fxq( ( $f * 4 + $x * 6 ) * 100, $r, 1 ) . '%)' );
		$lines[] = array( 'Balls per boundary', dlscalc_fxq( $b, $f + $x, 1 ) );
	}
	if ( null !== $g ) {
		// Whole-number sum, so the rounding up is exact in both engines.
		foreach ( array( 6, 12, 30 ) as $n ) {
			$need    = max( 0, (int) ceil( ( $g * ( $b + $n ) - 100 * $r ) / 100 ) );
			$lines[] = array( 'Strike rate ' . $g . ' after ' . $n . ' more balls', $need > 6 * $n ? 'Not possible: needs ' . $need . ' runs' : $need . ( 1 === $need ? ' run' : ' runs' ) . ' needed' );
		}
	}
	return array(
		'label' => 'Strike rate',
		'big'   => $sr,
		'sr'    => $sr,
		'avg'   => $avg,
		'outs'  => $outs,
		'line'  => $r . ( 1 === $r ? ' run' : ' runs' ) . ' off ' . $b . ( 1 === $b ? ' ball' : ' balls' ) . ( null !== $avg ? ', average ' . $avg . ' (' . $outs . ( 1 === $outs ? ' dismissal)' : ' dismissals)' ) : '' ),
		'lines' => $lines,
	);
}

/**
 * Bowling average, economy rate and bowling strike rate.
 *
 * @param array $p Inputs: o overs bowled, r runs conceded, k wickets, d dot balls.
 * @return array
 */
function dlscalc_nrr_bowling( $p ) {
	$b  = dlscalc_nrr_career_balls( $p['o'] ?? '' );
	$r  = dlscalc_nrr_count( $p['r'] ?? '' );
	$k  = dlscalc_dls_blank( $p['k'] ?? '' ) ? 0 : dlscalc_nrr_count( $p['k'] );
	$d  = dlscalc_dls_blank( $p['d'] ?? '' ) ? null : dlscalc_nrr_count( $p['d'] );
	$na = function ( $v ) {
		return is_float( $v ) && is_nan( $v );
	};
	if ( $na( $b ) || $b < 1 ) {
		return array(
			'err'   => dlscalc_nrr_overs_err( 'Overs bowled', $p['o'] ?? '', 'Enter the overs bowled, like 38.4 for 38 overs and 4 balls.' ),
			'field' => 'o',
		);
	}
	if ( $na( $r ) ) {
		return array(
			'err'   => dlscalc_dls_neg( $p['r'] ?? '' ) ? 'Runs cannot be negative.' : 'Enter the runs conceded.',
			'field' => 'r',
		);
	}
	if ( $na( $k ) || $k > $b ) {
		return array(
			'err'   => 'Wickets must be a whole number, no more than the balls bowled.',
			'field' => 'k',
		);
	}
	if ( null !== $d && ( $na( $d ) || $d > $b ) ) {
		return array(
			'err'   => 'Dot balls must be a whole number, no more than the balls bowled.',
			'field' => 'd',
		);
	}
	$econ    = dlscalc_fxq( $r * 6, $b, 2 );
	$avg     = $k ? dlscalc_fxq( $r, $k, 2 ) : null;
	$sr      = $k ? dlscalc_fxq( $b, $k, 1 ) : null;
	$lines   = array();
	$lines[] = array( 'Economy rate', $econ );
	$lines[] = array( 'Bowling strike rate', null !== $sr ? $sr : 'None yet: no wickets' );
	$lines[] = array( 'Balls bowled', $b . ' (' . dlscalc_dls_overs( $b ) . ' overs)' );
	$lines[] = array( 'Runs per ball', dlscalc_fxq( $r, $b, 2 ) );
	if ( null !== $d ) {
		$lines[] = array( 'Dot balls', $d . ' (' . dlscalc_fxq( $d * 100, $b, 1 ) . '%)' );
	}
	return array(
		'label' => $k ? 'Bowling average' : 'Economy rate',
		'big'   => $k ? $avg : $econ,
		'avg'   => $avg,
		'econ'  => $econ,
		'sr'    => $sr,
		'line'  => $k . ( 1 === $k ? ' wicket' : ' wickets' ) . ' for ' . $r . ( 1 === $r ? ' run' : ' runs' ) . ' in ' . dlscalc_dls_overs( $b ) . ' overs' . ( $k ? ', economy ' . $econ : '' ),
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
		'ov' => is_nan( (float) $b ) ? '' : $b . ( 1 === $b ? ' ball' : ' balls' ) . ' (' . dlscalc_fxq( $b, 6, 2 ) . ' overs in decimal)',
		'bl' => is_nan( (float) $n ) ? '' : dlscalc_dls_overs( $n ) . ' overs',
	);
}
