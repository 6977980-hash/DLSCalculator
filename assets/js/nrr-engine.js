/*!
 * DLSCalc NRR engine: net run rate across matches, points tables, the qualification solver and
 * the run rate calculator. Runs in the browser (window.NRR) and in Node (module.exports) for tests.
 * inc/nrr.php is a line-for-line PHP copy, so pages render real results without JavaScript.
 */
(function (root, factory) {
	var api = factory();
	if (typeof module === 'object' && module.exports) {
		module.exports = api;
	} else {
		root.NRR = api;
	}
}(typeof self !== 'undefined' ? self : this, function () {
	'use strict';

	var MATCHES_MAX = 30, TEAMS_MAX = 16;

	/** "19.4" (19 overs and 4 balls) to 118 balls. NaN if it is not a valid overs figure. */
	function balls(s) {
		s = String(s == null ? '' : s).trim();
		if (!/^\d{1,3}(\.\d)?$/.test(s)) {
			return NaN;
		}
		var p = s.split('.'), b = p[1] ? parseInt(p[1], 10) : 0;
		return b > 5 ? NaN : parseInt(p[0], 10) * 6 + b;
	}

	/** 118 balls to "19.4". */
	function overs(b) {
		var o = Math.floor(b / 6), r = b % 6;
		return r ? o + '.' + r : String(o);
	}

	function int(s) {
		s = String(s == null ? '' : s).trim();
		return /^\d{1,4}$/.test(s) ? parseInt(s, 10) : NaN;
	}

	/** A number, or null for a missing one (so results stay valid JSON in both engines). */
	function num(x) {
		return isNaN(x) ? null : x;
	}

	function blank(s) {
		return s == null || String(s).trim() === '';
	}

	// Floor of x + 0.5 rather than Math.round, because PHP's round() pre-rounds and can differ.
	function fx(x, d) {
		var k = Math.pow(10, d), v = Math.floor(x * k + 0.5) / k;
		return (v === 0 ? 0 : v).toFixed(d);
	}

	/** Net run rate with a sign, 3 decimals: "+0.350", "-1.025", "0.000". */
	function sign3(x) {
		var s = fx(x, 3);
		return s.charAt(0) === '-' || s === '0.000' ? s : '+' + s;
	}

	/** Runs an over, from runs and balls. */
	function rate(runs, b) {
		return runs * 6 / b;
	}

	/** Net run rate, or null when either side has no overs yet. */
	function nrrOf(rf, bf, ra, ba) {
		return bf > 0 && ba > 0 ? rate(rf, bf) - rate(ra, ba) : null;
	}

	/** Overs as entered in a match row: "18.3" or "18.3a" (bowled out, so the full quota counts). */
	function side(runs, ov, quota) {
		var allOut = /a$/.test(String(ov == null ? '' : ov).trim());
		var b = balls(allOut ? String(ov).trim().slice(0, -1) : ov);
		return { runs: int(runs), at: b, allOut: allOut, b: allOut ? quota : b };
	}

	/**
	 * NRR across matches. p: { q: overs per side, x: matches "168-20-151-20_142-18.3a-145-20" }.
	 * Each match is runs scored - overs faced - runs conceded - overs bowled, with "a" after the overs
	 * when that side was bowled out, and "-n" on the end for a no result (left out of NRR).
	 */
	function calc(p) {
		var Q = int(p.q);
		if (isNaN(Q) || Q < 1 || Q > 50) {
			return { err: 'Overs per side must be between 1 and 50.' };
		}
		var quota = Q * 6, rows = [], steps = [], rf = 0, bf = 0, ra = 0, ba = 0, counted = 0;
		var parts = blank(p.x) ? [] : String(p.x).split('_').slice(0, MATCHES_MAX);
		for (var i = 0; i < parts.length; i++) {
			var x = parts[i].split('-'), name = 'Match ' + (i + 1);
			if (x.slice(0, 4).join('') === '') {
				continue;
			}
			var a = side(x[0], x[1], quota), d = side(x[2], x[3], quota), nr = x[4] === 'n';
			if (isNaN(a.runs) || isNaN(d.runs) || a.runs > 999 || d.runs > 999) {
				return { err: name + ': enter runs scored and runs conceded.' };
			}
			if (isNaN(a.at) || isNaN(d.at)) {
				return { err: name + ': enter overs faced and overs bowled, like 19.4 for 19 overs and 4 balls.' };
			}
			if (a.at > quota || d.at > quota || (!a.allOut && a.at === 0) || (!d.allOut && d.at === 0)) {
				return { err: name + ': overs must be more than 0 and no more than ' + Q + ' per side.' };
			}
			rows.push({ n: i + 1, rf: a.runs, bf: a.b, ra: d.runs, ba: d.b, nr: nr });
			if (nr) {
				steps.push(name + ': no result, so it is left out of net run rate.');
				continue;
			}
			counted++;
			rf += a.runs;
			bf += a.b;
			ra += d.runs;
			ba += d.b;
			steps.push(name + ': scored ' + a.runs + (a.allOut ? ', all out in ' + overs(a.at) + ' overs, counted as ' + Q : ' in ' + overs(a.b)) +
				' overs; conceded ' + d.runs + (d.allOut ? ', bowling them out in ' + overs(d.at) + ' overs, counted as ' + Q : ' in ' + overs(d.b)) + ' overs.');
		}
		if (!counted) {
			return { err: 'Enter at least one match with a result.' };
		}
		var nrr = nrrOf(rf, bf, ra, ba);
		steps.push('Runs scored: ' + rf + ' in ' + overs(bf) + ' overs = ' + fx(rate(rf, bf), 3) + ' an over.');
		steps.push('Runs conceded: ' + ra + ' in ' + overs(ba) + ' overs = ' + fx(rate(ra, ba), 3) + ' an over.');
		steps.push('Net run rate = ' + fx(rate(rf, bf), 3) + ' − ' + fx(rate(ra, ba), 3) + ' = ' + sign3(nrr) + '.');
		return {
			Q: Q, rows: rows, counted: counted, rf: rf, bf: bf, ra: ra, ba: ba, nrr: sign3(nrr),
			forRate: fx(rate(rf, bf), 3), agRate: fx(rate(ra, ba), 3), steps: steps
		};
	}

	/** Team name as typed, without the characters the permalink uses as separators. */
	function cleanName(s) {
		return String(s == null ? '' : s).replace(/[~_]/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 30);
	}

	/**
	 * Points table from a permalink value: teams joined by "_", fields by "~":
	 * name ~ played ~ won ~ points ~ runs for ~ overs faced ~ runs against ~ overs bowled.
	 */
	function parseTeams(str) {
		if (blank(str)) {
			return [];
		}
		return String(str).split('_').slice(0, TEAMS_MAX).map(function (part, i) {
			var x = part.split('~'), t = {
				name: cleanName(x[0]) || 'Team ' + (i + 1), p: num(int(x[1])), w: num(int(x[2])), pts: num(int(x[3])),
				rf: num(int(x[4])), bf: num(balls(x[5])), ra: num(int(x[6])), ba: num(balls(x[7]))
			};
			t.raw = { p: x[1] || '', w: x[2] || '', pts: x[3] || '', rf: x[4] || '', of: x[5] || '', ra: x[6] || '', oa: x[7] || '' };
			t.ok = t.rf !== null && t.ra !== null && t.bf > 0 && t.ba > 0;
			t.val = t.ok ? nrrOf(t.rf, t.bf, t.ra, t.ba) : null;
			t.nrr = t.ok ? sign3(t.val) : '–';
			return t;
		});
	}

	/** Teams in table order: points, then net run rate. Teams without numbers go last. */
	function standings(teams) {
		var order = teams.map(function (t, i) {
			return i;
		});
		order.sort(function (i, j) {
			var a = teams[i], b = teams[j];
			var pa = a.pts === null ? -1 : a.pts, pb = b.pts === null ? -1 : b.pts;
			if (pa !== pb) {
				return pb - pa;
			}
			var na = a.ok ? a.val : -1e9, nb = b.ok ? b.val : -1e9;
			return na !== nb ? (nb > na ? 1 : -1) : i - j;
		});
		return order;
	}

	/**
	 * Qualification solver. p: { t: teams, y: your team (1-based), v: team to pass, o: next opponent
	 * (1-based, or 0 for a team not in the table), k: "f" bat first or "c" chase, e: your score when
	 * batting first or the opponent's score when chasing, q: overs per side, d: points for a win }.
	 * Assumes the team to pass does not play again before your match, unless it is your opponent.
	 */
	function solve(p) {
		var teams = parseTeams(p.t), Y = int(p.y), V = int(p.v), O = blank(p.o) ? 0 : int(p.o);
		var Q = int(p.q), E = int(p.e), D = blank(p.d) ? 2 : int(p.d), bat = p.k === 'c' ? 'c' : 'f';
		var res = { teams: teams, order: standings(teams) };
		if (teams.length < 2) {
			res.err = 'Add at least two teams to the points table.';
			return res;
		}
		var me = teams[Y - 1], rv = teams[V - 1];
		if (!me || !rv) {
			res.err = 'Choose your team and the team to pass.';
			return res;
		}
		if (Y === V) {
			res.err = 'Your team and the team to pass must be different.';
			return res;
		}
		if (!me.ok || !rv.ok) {
			res.err = 'Fill in runs and overs for ' + (me.ok ? rv.name : me.name) + ' in the points table.';
			return res;
		}
		if (isNaN(O) || O === Y || O > teams.length) {
			res.err = 'Choose a different team as the next opponent.';
			return res;
		}
		if (isNaN(Q) || Q < 1 || Q > 50) {
			res.err = 'Overs per side must be between 1 and 50.';
			return res;
		}
		if (isNaN(E) || E > 999) {
			res.err = bat === 'f' ? 'Enter the score you expect to make.' : 'Enter the opponent\'s score you will chase.';
			return res;
		}
		if (isNaN(D) || D > 10) {
			res.err = 'Points for a win must be between 0 and 10.';
			return res;
		}
		var vs = O === V, N = Q * 6;
		var steps = [
			me.name + ' now: ' + me.rf + ' runs in ' + overs(me.bf) + ' overs, ' + me.ra + ' conceded in ' + overs(me.ba) + ' overs, NRR ' + me.nrr + '.',
			rv.name + ' now: ' + rv.rf + ' runs in ' + overs(rv.bf) + ' overs, ' + rv.ra + ' conceded in ' + overs(rv.ba) + ' overs, NRR ' + rv.nrr + '.'
		];
		res.me = me.name;
		res.rv = rv.name;
		res.vs = vs;
		res.bat = bat;
		res.note = vs ? 'Your opponent is ' + rv.name + ', so its net run rate changes too.' : 'Assumes ' + rv.name + ' does not play again before your match.';

		if (me.pts !== null && rv.pts !== null) {
			var after = me.pts + D;
			steps.push('Points: a win takes ' + me.name + ' from ' + me.pts + ' to ' + after + '; ' + rv.name + ' has ' + rv.pts + '.');
			if (after !== rv.pts) {
				res.kind = after > rv.pts ? 'points' : 'short';
				res.big = after > rv.pts ? 'Any win' : 'Not on points';
				res.line = after > rv.pts ?
					'A win takes ' + me.name + ' to ' + after + ' points, above ' + rv.name + ' on ' + rv.pts + ', so the margin does not matter.' :
					'Even with a win, ' + me.name + ' has ' + after + ' points, behind ' + rv.name + ' on ' + rv.pts + '. Net run rate only separates teams level on points.';
				steps.push(after > rv.pts ? 'More points than ' + rv.name + ', so net run rate is not needed.' : 'Fewer points than ' + rv.name + ', so net run rate cannot help yet.');
				res.steps = steps;
				return res;
			}
			steps.push('Level on points after a win, so net run rate decides.');
		}

		// Your new NRR and the rival's, for your runs and balls and the opponent's runs.
		var mine = function (runs, b, opp) {
			return nrrOf(me.rf + runs, me.bf + b, me.ra + opp, me.ba + N);
		};
		var theirs = function (runs, b, opp) {
			return vs ? nrrOf(rv.rf + opp, rv.bf + N, rv.ra + runs, rv.ba + b) : rv.val;
		};

		if (bat === 'f') {
			var ymax = -1;
			for (var y = E - 1; y >= 0; y--) {
				if (mine(E, N, y) > theirs(E, N, y)) {
					ymax = y;
					break;
				}
			}
			steps.push('Batting first, ' + me.name + ' scores ' + E + ' in ' + Q + ' overs. The opponent\'s innings counts as ' + Q + ' overs whether it is bowled out or not.');
			if (ymax < 0) {
				res.kind = 'none';
				res.big = 'Not enough';
				res.line = 'Even bowling the opponent out for 0 would not lift ' + me.name + ' above ' + rv.name + ' on net run rate. Aim for a bigger total.';
				steps.push('Opponent all out for 0: ' + me.name + ' NRR ' + sign3(mine(E, N, 0)) + ', ' + rv.name + ' ' + sign3(theirs(E, N, 0)) + '. Still not above.');
			} else {
				res.kind = 'runs';
				res.margin = E - ymax;
				res.limit = ymax;
				res.big = ymax === E - 1 ? 'Any win' : (E - ymax) + ' runs';
				res.line = 'Win by at least ' + (E - ymax) + (E - ymax === 1 ? ' run' : ' runs') + ': hold the opponent to ' + ymax + ' or fewer in ' + Q + ' overs.';
				steps.push('Opponent makes ' + ymax + ': ' + me.name + ' NRR ' + sign3(mine(E, N, ymax)) + ', ' + rv.name + ' ' + sign3(theirs(E, N, ymax)) + '. ' + me.name + ' is above.');
				if (ymax < E - 1) {
					steps.push('Opponent makes ' + (ymax + 1) + ': ' + me.name + ' NRR ' + sign3(mine(E, N, ymax + 1)) + ', ' + rv.name + ' ' + sign3(theirs(E, N, ymax + 1)) + '. Not above, so ' + ymax + ' is the most the opponent can make.');
				}
			}
		} else {
			var T = E + 1, bmax = 0;
			for (var b = N; b >= 1; b--) {
				if (mine(T, b, E) > theirs(T, b, E)) {
					bmax = b;
					break;
				}
			}
			steps.push('Chasing ' + E + ', ' + me.name + ' needs ' + T + ' to win. The opponent\'s innings counts as ' + Q + ' overs; your chase counts the overs you actually face.');
			if (!bmax) {
				res.kind = 'none';
				res.big = 'Not possible';
				res.line = 'No winning chase of ' + T + ' is fast enough to lift ' + me.name + ' above ' + rv.name + ' on net run rate.';
				steps.push('Even reaching ' + T + ' off the first ball gives ' + me.name + ' ' + sign3(mine(T, 1, E)) + ' against ' + rv.name + ' ' + sign3(theirs(T, 1, E)) + '.');
			} else {
				res.kind = 'overs';
				res.within = overs(bmax);
				res.big = bmax === N ? 'Any win' : overs(bmax) + ' overs';
				res.line = 'Reach ' + T + ' within ' + overs(bmax) + ' overs (' + bmax + ' balls) to move above ' + rv.name + '.';
				steps.push('Reaching ' + T + ' in ' + overs(bmax) + ' overs: ' + me.name + ' NRR ' + sign3(mine(T, bmax, E)) + ', ' + rv.name + ' ' + sign3(theirs(T, bmax, E)) + '. ' + me.name + ' is above.');
				if (bmax < N) {
					steps.push('One ball slower, ' + overs(bmax + 1) + ' overs: ' + me.name + ' NRR ' + sign3(mine(T, bmax + 1, E)) + ', ' + rv.name + ' ' + sign3(theirs(T, bmax + 1, E)) + '. Not above.');
				}
			}
		}
		steps.push('Assumes ' + me.name + ' makes exactly the runs shown. A bigger total or a quicker finish only helps.');
		res.steps = steps;
		return res;
	}

	/**
	 * Run rate calculator. p: { u: "c" chasing or "f" batting first, n: overs per side, t: target,
	 * r: runs so far, o: overs bowled so far }.
	 */
	function runRate(p) {
		var chase = p.u !== 'f', Nn = int(p.n), R = int(p.r), B = balls(p.o);
		if (isNaN(Nn) || Nn < 1 || Nn > 50) {
			return { err: 'Overs per side must be between 1 and 50.' };
		}
		var N = Nn * 6, T = int(p.t);
		if (chase && (isNaN(T) || T < 1 || T > 999)) {
			return { err: 'Enter the target, the runs needed to win.' };
		}
		if (isNaN(R) || R > 999) {
			return { err: 'Enter the runs scored so far.' };
		}
		if (isNaN(B)) {
			return { err: 'Enter the overs bowled so far, like 12.4 for 12 overs and 4 balls.' };
		}
		if (B > N) {
			return { err: 'Overs bowled cannot be more than ' + Nn + ' overs per side.' };
		}
		var left = N - B, crr = B > 0 ? rate(R, B) : 0, lines = [];
		lines.push(['Current run rate', B > 0 ? fx(crr, 2) : '–']);
		if (chase) {
			var need = T - R;
			if (need <= 0) {
				return { mode: 'c', state: 'won', label: 'Result', big: 'Won', line: 'The target of ' + T + ' has been reached' + (left ? ' with ' + left + (left === 1 ? ' ball' : ' balls') + ' to spare.' : '.'), lines: lines };
			}
			if (left === 0) {
				return { mode: 'c', state: 'over', label: 'Result', big: need === 1 ? 'Tied' : 'Lost', line: need === 1 ? 'Scores level with no balls left.' : 'No balls left: short by ' + (need - 1) + (need === 2 ? ' run.' : ' runs.'), lines: lines };
			}
			var rrr = rate(need, left);
			lines.push(['Runs needed', String(need)]);
			lines.push(['Balls left', left + ' (' + overs(left) + ' overs)']);
			lines.push(['Runs needed per ball', fx(need / left, 2)]);
			if (B > 0) {
				lines.push(['Required minus current', (rrr >= crr ? '+' : '') + fx(rrr - crr, 2) + ' an over']);
			}
			return {
				mode: 'c', state: 'on', label: 'Required run rate', big: fx(rrr, 2), rrr: fx(rrr, 2), crr: fx(crr, 2), need: need, left: left,
				line: 'Need ' + need + (need === 1 ? ' run' : ' runs') + ' off ' + left + (left === 1 ? ' ball' : ' balls') + ' (' + overs(left) + ' overs)', lines: lines
			};
		}
		if (B === 0) {
			return { err: 'Enter at least one ball bowled to project the score.' };
		}
		var proj = function (r) {
			return String(Math.floor(R + r * left / 6 + 0.5));
		};
		for (var d = 1; d <= 3; d++) {
			lines.push(['At ' + fx(crr + d, 2) + ' an over (+' + d + ')', proj(crr + d)]);
		}
		[6, 8, 10, 12].forEach(function (r) {
			lines.push(['At ' + r + ' an over', proj(r)]);
		});
		return {
			mode: 'f', state: 'on', label: 'Projected score at the current rate', big: proj(crr), crr: fx(crr, 2), left: left,
			line: left ? overs(left) + ' overs left at ' + fx(crr, 2) + ' an over' : 'Innings complete', lines: lines
		};
	}

	/** Overs and balls converter: { v: overs as typed, j: balls }. */
	function convert(p) {
		var b = balls(p.v), n = int(p.j);
		return {
			ov: isNaN(b) ? '' : b + (b === 1 ? ' ball' : ' balls') + ' (' + fx(b / 6, 2) + ' overs in decimal)',
			bl: isNaN(n) ? '' : overs(n) + ' overs'
		};
	}

	return {
		balls: balls, overs: overs, fx: fx, sign3: sign3, nrrOf: nrrOf, calc: calc, parseTeams: parseTeams, standings: standings,
		solve: solve, runRate: runRate, convert: convert, cleanName: cleanName, MATCHES_MAX: MATCHES_MAX, TEAMS_MAX: TEAMS_MAX
	};
}));
