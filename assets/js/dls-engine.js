/*!
 * DLSCalc DLS engine: ICC DLS Standard Edition targets, par scores and par tables.
 * Runs in the browser (window.DLS) and in Node (module.exports) for tests.
 * inc/dls.php is a line-for-line PHP copy, so pages render real results without JavaScript.
 */
(function (root, factory) {
	var api = factory();
	if (typeof module === 'object' && module.exports) {
		module.exports = api;
	} else {
		root.DLS = api;
	}
}(typeof self !== 'undefined' ? self : this, function () {
	'use strict';

	// ICC DLS Standard Edition resource table, ball by ball: one row per wickets lost (0 to 9),
	// 301 values per row (0 to 300 balls left) in tenths of a percent. Each row is stored as the
	// step from one value to the next, one digit per step. inc/dls.php holds the same rows and
	// dev/tests/run.sh checks that both copies match the ICC table.
	var ROWS = [
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
	];
	var TABLE = ROWS.map(function (row) {
		var sum = 0, out = [0];
		for (var i = 0; i < row.length; i++) {
			sum += row.charCodeAt(i) - 48;
			out.push(sum);
		}
		return out;
	});

	var STOPS_MAX = 10, LIVE_MAX = 100;

	/** Resources left (percent) with this many balls left and wickets lost. */
	function res(ballsLeft, wkts) {
		if (wkts >= 10 || ballsLeft <= 0) {
			return 0;
		}
		return TABLE[wkts][Math.min(300, ballsLeft)] / 10;
	}

	/**
	 * Overs as typed, tidied: a comma (some Android number pads have no dot) reads as the dot and a
	 * trailing dot ("40.") is dropped (audit F55). Same in nrr-engine.js and dlscalc_dls_overs_in() in inc/dls.php.
	 */
	function oversIn(s) {
		return String(s == null ? '' : s).trim().replace(',', '.').replace(/\.(a?)$/, '$1');
	}

	/** "32.4" (32 overs and 4 balls) to 196 balls. NaN if it is not a valid overs figure. */
	function balls(s) {
		s = oversIn(s);
		if (!/^\d{1,3}(\.\d)?$/.test(s)) {
			return NaN;
		}
		var p = s.split('.'), b = p[1] ? parseInt(p[1], 10) : 0;
		return b > 5 ? NaN : parseInt(p[0], 10) * 6 + b;
	}

	/** 196 balls to "32.4". */
	function overs(b) {
		var o = Math.floor(b / 6), r = b % 6;
		return r ? o + '.' + r : String(o);
	}

	/**
	 * Error for an overs figure that balls() could not read, naming the field: "12.7" gets the
	 * rule that balls after the dot go from 0 to 5 (audit F19).
	 */
	function oversHelp(label, s) {
		var m = /^(\d{1,3})\.(\d)$/.exec(oversIn(s));
		if (m) {
			return label + ': ' + m[0] + ' is not valid. Balls after the dot go from 0 to 5, so after ' + m[1] + '.5 comes ' + (parseInt(m[1], 10) + 1) + '.';
		}
		return label + ': write overs as 32.4 for 32 overs and 4 balls.';
	}

	/** An error result, with the input it belongs to (form field name, or "b-2-at" for a stoppage). */
	function fail(err, field) {
		return { err: err, field: field };
	}

	function blank(v) {
		return v == null || String(v).trim() === '';
	}

	/** Whether a value was typed as a negative number ("-5"), so the error can say so (audit F39). */
	function neg(v) {
		return /^\s*[-−]\s*\d/.test(String(v == null ? '' : v));
	}

	function int(s) {
		s = String(s == null ? '' : s).trim();
		return /^\d{1,4}$/.test(s) ? parseInt(s, 10) : NaN;
	}

	/**
	 * Whether a runs box holds a scorecard score with wickets ("98/6", "98-6"), which a number box
	 * used to turn into 986 (audit F66). Same in nrr-engine.js; PHP: dlscalc_dls_score().
	 */
	function score(v) {
		return /^\s*\d{1,3}\s*[\/\-−]\s*\d{1,2}\s*$/.test(String(v == null ? '' : v));
	}

	/**
	 * Error for a runs box that is not a whole number from 0 to 999, naming the cause (audit F49):
	 * negative, too big or not whole; `empty` for a blank box. Same in nrr-engine.js; PHP: dlscalc_dls_runs_err().
	 */
	function runsErr(raw, who, empty) {
		var s = String(raw == null ? '' : raw).trim();
		if (s === '' || (/^\d+$/.test(s) && parseInt(s, 10) <= 999)) {
			return empty;
		}
		if (score(s)) {
			return who + ': enter runs only, without wickets (98, not 98/6).';
		}
		if (neg(s)) {
			return who + ' cannot be negative.';
		}
		return /^\d+$/.test(s) ? who + ' can be no more than 999.' : who + ' must be a whole number, like 245.';
	}

	function r1(x) {
		return Math.round(x * 10) / 10;
	}

	function f1(x) {
		return r1(x).toFixed(1);
	}

	// Floor of x + 0.5 rather than Math.round, because PHP's round() pre-rounds and can differ.
	function c2(x) {
		return Math.floor(x * 100 + 0.5) / 100;
	}

	function f2(x) {
		return c2(x).toFixed(2);
	}

	/**
	 * num ÷ den rounded half away from zero to d decimals, as text. Works on whole numbers, so the
	 * browser and PHP always agree: as a float, 10.425 sits just under the half (audit F38). The same
	 * helper is in nrr-engine.js and inc/dls.php (dlscalc_fxq).
	 */
	function fxq(num, den, d) {
		if (den < 0) {
			num = -num;
			den = -den;
		}
		var a = Math.abs(num) * Math.pow(10, d) * 2 + den, v = (a - a % (2 * den)) / (2 * den), s = String(v);
		while (s.length <= d) {
			s = '0' + s;
		}
		return (num < 0 && v ? '-' : '') + (d ? s.slice(0, -d) + '.' + s.slice(-d) : s);
	}

	/** Raw par: Team 1's score scaled to the resources Team 2 used. */
	function scale(S, used, R1, G) {
		return used <= R1 ? S * used / R1 : S + G * (used - R1) / 100;
	}

	function floorPar(raw) {
		return Math.floor(raw + 1e-9);
	}

	/**
	 * Stoppages from a permalink value: "12-1-10_22-3-2" (overs bowled, wickets down, overs lost).
	 * Empty parts ("12-1-10_", "__") are skipped, as the form skips blank rows (audit F70).
	 */
	function parseStops(str) {
		if (!str) {
			return [];
		}
		return String(str).split('_').filter(function (part) {
			return part.replace(/-/g, '').trim() !== '';
		}).slice(0, STOPS_MAX).map(function (part) {
			var x = part.split('-');
			return { at: balls(x[0]), w: int(x[1]), lost: balls(x[2]), atS: x[0] || '', lostS: x[2] || '' };
		});
	}

	/**
	 * Resources for one innings, with stoppages in order. Each stoppage takes away the resources
	 * between the overs left before and after it, at the wickets down when it happened.
	 */
	function innings(startB, stops, label, key) {
		var allot = startB, R = res(startB, 0);
		var steps = [label + ' starts with ' + overs(startB) + ' overs and 10 wickets: ' + f1(R) + '% of resources.'];
		for (var i = 0; i < stops.length; i++) {
			var st = stops[i], name = label + ' stoppage ' + (i + 1), f = key + '-' + (i + 1) + '-';
			if (isNaN(st.at) && !blank(st.atS)) {
				return fail(oversHelp(name + ', overs bowled', st.atS), f + 'at');
			}
			if (isNaN(st.lost) && !blank(st.lostS)) {
				return fail(oversHelp(name + ', overs lost', st.lostS), f + 'lost');
			}
			if (isNaN(st.at) || isNaN(st.w) || isNaN(st.lost)) {
				return fail(name + ': fill in overs bowled, wickets down and overs lost (overs like 32.4).', f + (isNaN(st.at) ? 'at' : (isNaN(st.w) ? 'w' : 'lost')));
			}
			if (i > 0 && (st.at < stops[i - 1].at || st.w < stops[i - 1].w)) {
				return fail(label + ' stoppages must be entered in the order they happened.', f + (st.at < stops[i - 1].at ? 'at' : 'w'));
			}
			var left = allot - st.at;
			if (st.at === 0) {
				return fail(name + ': overs bowled must be more than 0. Overs lost before an innings starts are not a stoppage: enter the shorter innings as its overs instead.', f + 'at');
			}
			if (left <= 0) {
				return fail(name + ' must come before the end of the innings (' + overs(allot) + ' overs).', f + 'at');
			}
			if (st.w > 9) {
				return fail(name + ': wickets down must be 0 to 9.', f + 'w');
			}
			if (st.lost <= 0 || st.lost > left) {
				return fail(name + ': overs lost must be more than 0 and no more than ' + overs(left) + '.', f + 'lost');
			}
			var before = res(left, st.w), after = res(left - st.lost, st.w);
			R -= before - after;
			allot -= st.lost;
			if (st.lost === left) {
				steps.push(label + ' innings ended at ' + overs(st.at) + ' overs, ' + st.w + ' down, with ' + overs(left) + ' overs left = ' + f1(before) + '%. All of it is lost.');
			} else {
				steps.push(label + ' stoppage at ' + overs(st.at) + ' overs, ' + st.w + ' down: ' + overs(left) + ' overs left = ' + f1(before) + '%. After losing ' + overs(st.lost) + ' overs, ' + overs(left - st.lost) + ' left = ' + f1(after) + '%. Resources lost: ' + f1(before - after) + '%.');
			}
		}
		var last = stops.length ? stops[stops.length - 1] : null;
		return { R: r1(R), steps: steps, allot: allot, lastAt: last ? last.at : 0, ended: !!last && last.at >= allot };
	}

	/**
	 * Fewest overs (in balls) Team 2 must face for a result, by format: 20 in an ODI, 5 in a T20 and
	 * 10 in a T20 knockout such as the ICC Men's T20 World Cup semi-finals and final (audit F64),
	 * even when the match started with fewer overs. F: "odi", "t20", "t20ko", "t10" or "custom" (no ICC
	 * minimum); without one, a 50-over match counts as an ODI and a 20-over match as a T20.
	 */
	function minBalls(N, F) {
		if (F === 'odi' || F === 't20' || F === 't20ko') {
			return { odi: 120, t20: 30, t20ko: 60 }[F];
		}
		if (F === 't10' || F === 'custom') {
			return 0;
		}
		return N === 300 ? 120 : (N === 120 ? 30 : 0);
	}

	/**
	 * A whole match. o: { N: balls per side at start, S: Team 1 score, G: G50, t1: Team 1 stoppages,
	 * M: Team 2 balls at start of its innings, t2: Team 2 stoppages, note: optional extra step,
	 * F: optional format for the minimum overs, see minBalls(), P: optional penalty runs awarded to
	 * Team 1 during the chase, which raise the target and every par score (ICC DLS section 6.2) }.
	 */
	function match(o) {
		var a = innings(o.N, o.t1, 'Team 1', 'a');
		if (a.err) {
			return a;
		}
		var b = innings(o.M, o.t2, 'Team 2', 'b');
		if (b.err) {
			return b;
		}
		var P = o.P || 0, raw = scale(o.S, b.R, a.R, o.G), base = floorPar(raw), par = base + P;
		var formula = b.R <= a.R ?
			'R2 is not more than R1, so par = ' + o.S + ' × ' + f1(b.R) + ' ÷ ' + f1(a.R) + ' = ' + f2(raw) :
			'R2 is more than R1, so par = ' + o.S + ' + ' + o.G + ' × (' + f1(b.R) + ' − ' + f1(a.R) + ') ÷ 100 = ' + f2(raw);
		var result = b.ended ?
			'Team 2 wins if they scored more than ' + par + ' and the match is tied on ' + par + '.' :
			'Target = ' + par + ' + 1 = ' + (par + 1) + '.';
		var penalty = P ? 'Team 1 was awarded ' + P + ' penalty ' + (P === 1 ? 'run' : 'runs') + ' during the chase, so the par score and every par in the table go up by ' + P + ' (ICC DLS rules, section 6.2): par = ' + base + ' + ' + P + ' = ' + par + '. ' + result : '';
		formula += ', rounded down to ' + base + '.' + (P ? '' : ' ' + result);
		var steps = a.steps.concat(['Team 1 resources, R1 = ' + f1(a.R) + '%.']);
		if (o.note) {
			steps.push(o.note);
		}
		steps = steps.concat(b.steps, ['Team 2 resources, R2 = ' + f1(b.R) + '%.', formula]);
		if (P) {
			steps.push(penalty);
		}

		var minB = minBalls(o.N, o.F), warn = '';
		if (minB && (b.ended ? b.lastAt : b.allot) < minB) {
			warn = 'Team 2 ' + (b.ended ? 'faced' : 'can face') + ' fewer than ' + overs(minB) + ' overs, so under ICC rules there is no result unless the match was already decided.';
		}
		// A common slip: Team 1's innings was cut but Team 2 keeps the full overs (audit F39).
		if (o.t1.length && o.M > a.allot) {
			warn += (warn ? ' ' : '') + 'Team 2 starts with ' + overs(o.M) + ' overs, more than the ' + overs(a.allot) + ' Team 1 had after its stoppages. Both sides usually get the same overs, so check Team 2\'s overs at the start of the chase.';
		}
		return {
			N: o.N, F: o.F || '', S: o.S, G: o.G, M: o.M, R1: a.R, R2: b.R, raw: c2(raw), par: par, target: par + 1,
			allot: b.allot, lastAt: b.lastAt, ended: b.ended, steps: steps, warn: warn, rows: parRows(o.S, a.R, o.G, b, P)
		};
	}

	/** Par after each completed over left in the chase, for 0 to 9 wickets down, plus penalty runs P. */
	function parRows(S, R1, G, b, P) {
		var rows = [];
		if (b.ended) {
			return rows;
		}
		for (var ov = Math.max(1, Math.ceil(b.lastAt / 6)); ov * 6 < b.allot; ov++) {
			var cells = [];
			for (var w = 0; w < 10; w++) {
				cells.push(floorPar(scale(S, r1(b.R - res(b.allot - ov * 6, w)), R1, G)) + (P || 0));
			}
			rows.push({ over: ov, cells: cells });
		}
		return rows;
	}

	function check(S, G, raw) {
		if (isNaN(S) || S > 999) {
			return fail(runsErr(raw, 'Team 1\'s score', 'Enter Team 1\'s final score.'), 'r');
		}
		if (!(G > 0 && G <= 500)) {
			return fail('Enter a G50 value between 1 and 500, for example 245.', 'g');
		}
		return null;
	}

	/**
	 * DLS Calculator page. p: { n: overs per side, r: Team 1 runs, g: G50, c: Team 2 overs at the start
	 * of the chase, a: Team 1 stops, b: Team 2 stops, f: format "odi", "t20", "t20ko", "t10" or "custom",
	 * k: penalty runs awarded to Team 1 during the chase }. Short names that WordPress does not use
	 * itself (it treats s, m, p and w as its own query variables).
	 */
	function calc(p) {
		var N = balls(p.n), S = int(p.r), P = blank(p.k) ? 0 : int(p.k);
		var G = /^\d{1,3}(\.\d{1,2})?$/.test(String(p.g == null ? '' : p.g).trim()) ? parseFloat(p.g) : NaN;
		var M = blank(p.c) ? N : balls(p.c);
		if (isNaN(N) && !blank(p.n)) {
			return fail(oversHelp('Overs per side', p.n), 'n');
		}
		if (isNaN(N) || N < 6 || N > 300) {
			return fail('Overs per side must be between 1 and 50.', 'n');
		}
		var e = check(S, G, p.r);
		if (e) {
			return e;
		}
		if (isNaN(M)) {
			return fail(oversHelp('Overs for Team 2 at the start of the chase', p.c), 'c');
		}
		if (M < 1 || M > N) {
			return fail('Overs for Team 2 at the start of the chase must be between 0.1 and ' + overs(N) + '.', 'c');
		}
		if (isNaN(P) || P > 99) {
			return fail('Penalty runs must be a whole number from 0 to 99.', 'k');
		}
		var F = ['odi', 't20', 't20ko', 't10', 'custom'].indexOf(p.f) > -1 ? p.f : '';
		return match({ N: N, S: S, G: G, t1: parseStops(p.a), M: M, t2: parseStops(p.b), F: F, P: P });
	}

	/** T20 page. p: { n: overs per side (5 to 20), r: Team 1 runs, l: overs lost before the chase, b: chase stops }. */
	function t20(p) {
		var No = int(p.n), S = int(p.r), L = int(p.l);
		if (isNaN(No) || No < 5 || No > 20) {
			return fail('Overs per side must be between 5 and 20.', 'n');
		}
		var e = check(S, 245, p.r);
		if (e) {
			return e;
		}
		// More overs lost than leave Team 2 its 5 is an error, not a silent cap (audit F39).
		if (blank(p.l)) {
			L = 0;
		} else if (isNaN(L) || L > No - 5) {
			return fail('Overs lost before the chase must be a whole number from 0 to ' + (No - 5) + ': Team 2 needs at least 5 overs for a result in a T20.', 'l');
		}
		var r = match({
			N: No * 6, S: S, G: 245, t1: [], M: (No - L) * 6, t2: parseStops(p.b), F: 't20',
			note: L ? 'Rain cut ' + L + (L === 1 ? ' over' : ' overs') + ' before the chase, so Team 2 starts with ' + (No - L) + ' overs.' : ''
		});
		if (r.err) {
			return r;
		}
		r.No = No;
		r.L = L;
		r.rr = r.ended ? '' : fxq(r.target * 6, r.allot, 2);
		r.bars = [];
		for (var mo = 5; mo <= No; mo++) {
			r.bars.push({ overs: mo, target: floorPar(scale(S, res(mo * 6, 0), r.R1, 245)) + 1 });
		}
		return r;
	}

	/** Par score table page. p: { o: overs per side (5 to 50), r: Team 1 runs, c: Team 2 overs, optional }. */
	function parTable(p) {
		var No = int(p.o), S = int(p.r);
		if (isNaN(No) || No < 5 || No > 50) {
			return fail('Overs per side must be between 5 and 50.', 'o');
		}
		var e = check(S, 245, p.r);
		if (e) {
			return e;
		}
		var M = blank(p.c) ? No * 6 : balls(p.c);
		if (isNaN(M)) {
			return fail(oversHelp('Overs for Team 2', p.c), 'c');
		}
		if (M < 6 || M > No * 6) {
			return fail('Overs for Team 2 must be between 1 and ' + No + '.', 'c');
		}
		var r = match({ N: No * 6, S: S, G: 245, t1: [], M: M, t2: [] });
		r.No = No;
		return r;
	}

	/**
	 * Live par tracker. p: { n, r, g, c as in calc(), x: Team 2's score log "5-38-1_10-71-2"
	 * (overs - runs - wickets) }. Each entry is judged as if rain ended the chase right there, so its
	 * par is the DLS par at that point: who is ahead if play stops now.
	 */
	function live(p) {
		var base = calc({ n: p.n, r: p.r, g: p.g, c: p.c, f: p.f });
		if (base.err) {
			return base;
		}
		var log = [], last = null, lastName = '', now = null;
		var parts = (p.x == null || String(p.x).trim() === '') ? [] : String(p.x).split('_').slice(0, LIVE_MAX);
		for (var i = 0; i < parts.length; i++) {
			var x = parts[i].split('-'), at = balls(x[0]), runs = int(x[1]), w = int(x[2]), name = 'Score entry ' + (i + 1);
			if (x.join('') === '') {
				continue;
			}
			if (isNaN(at) && !blank(x[0])) {
				return { err: oversHelp(name, x[0]) };
			}
			if (score(x[1])) {
				return { err: name + ': enter runs only, without wickets (98, not 98/6).' };
			}
			if (isNaN(at) || isNaN(runs) || isNaN(w)) {
				return { err: name + ': fill in overs, runs and wickets (overs like 12.3).' };
			}
			if (last && last.state !== 'on') {
				return { err: 'The chase was already over before ' + name.toLowerCase() + '. Remove the entries after it.' };
			}
			if (at < 1 || at > base.allot) {
				return { err: name + ': overs must be between 0.1 and ' + overs(base.allot) + '.' };
			}
			if (w > 10 || runs > 999) {
				return { err: name + ': wickets must be 0 to 10 and runs no more than 999.' };
			}
			if (last && at <= last.at) {
				return { err: 'Score entries must be in order, with the overs going up.' };
			}
			if (last && (runs < last.runs || w < last.w)) {
				return { err: (runs < last.runs ? 'Runs' : 'Wickets') + ' cannot go down: ' + name.toLowerCase() + ' has ' + (runs < last.runs ? runs : w) + ', ' + lastName.toLowerCase() + ' has ' + (runs < last.runs ? last.runs : last.w) + '.' };
			}
			var e = { at: at, runs: runs, w: w };
			now = null;
			if (runs >= base.target) {
				e.state = 'won';
				e.par = base.par;
			} else if (w === 10 || at === base.allot) {
				e.state = runs === base.par ? 'tied' : 'lost';
				e.par = base.par;
			} else {
				now = match({ N: base.N, S: base.S, G: base.G, t1: [], M: base.M, t2: [{ at: at, w: w, lost: base.allot - at }], F: base.F });
				e.state = 'on';
				e.par = now.par;
				e.warn = now.warn;
			}
			e.diff = runs - e.par;
			log.push(e);
			last = e;
			lastName = name;
		}
		base.log = log;
		base.nowSteps = now ? now.steps.slice(-3) : [];
		return base;
	}

	return {
		res: res, balls: balls, overs: overs, oversHelp: oversHelp, fxq: fxq, scale: scale, innings: innings, match: match,
		parRows: parRows, parseStops: parseStops, calc: calc, t20: t20, parTable: parTable, live: live, STOPS_MAX: STOPS_MAX, LIVE_MAX: LIVE_MAX
	};
}));
