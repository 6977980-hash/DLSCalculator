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

	/** "32.4" (32 overs and 4 balls) to 196 balls. NaN if it is not a valid overs figure. */
	function balls(s) {
		s = String(s == null ? '' : s).trim();
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

	function int(s) {
		s = String(s == null ? '' : s).trim();
		return /^\d{1,4}$/.test(s) ? parseInt(s, 10) : NaN;
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

	/** Raw par: Team 1's score scaled to the resources Team 2 used. */
	function scale(S, used, R1, G) {
		return used <= R1 ? S * used / R1 : S + G * (used - R1) / 100;
	}

	function floorPar(raw) {
		return Math.floor(raw + 1e-9);
	}

	/** Stoppages from a permalink value: "12-1-10_22-3-2" (overs bowled, wickets down, overs lost). */
	function parseStops(str) {
		if (!str) {
			return [];
		}
		return String(str).split('_').slice(0, STOPS_MAX).map(function (part) {
			var x = part.split('-');
			return { at: balls(x[0]), w: int(x[1]), lost: balls(x[2]) };
		});
	}

	/**
	 * Resources for one innings, with stoppages in order. Each stoppage takes away the resources
	 * between the overs left before and after it, at the wickets down when it happened.
	 */
	function innings(startB, stops, label) {
		var allot = startB, R = res(startB, 0);
		var steps = [label + ' starts with ' + overs(startB) + ' overs and 10 wickets: ' + f1(R) + '% of resources.'];
		for (var i = 0; i < stops.length; i++) {
			var st = stops[i], name = label + ' stoppage ' + (i + 1);
			if (isNaN(st.at) || isNaN(st.w) || isNaN(st.lost)) {
				return { err: name + ': fill in overs bowled, wickets down and overs lost (overs like 32.4).' };
			}
			if (i > 0 && (st.at < stops[i - 1].at || st.w < stops[i - 1].w)) {
				return { err: label + ' stoppages must be entered in the order they happened.' };
			}
			var left = allot - st.at;
			if (st.at <= 0 || left <= 0) {
				return { err: name + ' must come before the end of the innings (' + overs(allot) + ' overs).' };
			}
			if (st.w > 9) {
				return { err: name + ': wickets down must be 0 to 9.' };
			}
			if (st.lost <= 0 || st.lost > left) {
				return { err: name + ': overs lost must be more than 0 and no more than ' + overs(left) + '.' };
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

	/** Fewest overs (in balls) Team 2 must face for a result: 20 in a 50-over match, 5 in a 20-over match. */
	function minBalls(N) {
		return N === 300 ? 120 : (N === 120 ? 30 : 0);
	}

	/**
	 * A whole match. o: { N: balls per side at start, S: Team 1 score, G: G50, t1: Team 1 stoppages,
	 * M: Team 2 balls at start of its innings, t2: Team 2 stoppages, note: optional extra step }.
	 */
	function match(o) {
		var a = innings(o.N, o.t1, 'Team 1');
		if (a.err) {
			return a;
		}
		var b = innings(o.M, o.t2, 'Team 2');
		if (b.err) {
			return b;
		}
		var raw = scale(o.S, b.R, a.R, o.G), par = floorPar(raw);
		var formula = b.R <= a.R ?
			'R2 is not more than R1, so par = ' + o.S + ' × ' + f1(b.R) + ' ÷ ' + f1(a.R) + ' = ' + f2(raw) :
			'R2 is more than R1, so par = ' + o.S + ' + ' + o.G + ' × (' + f1(b.R) + ' − ' + f1(a.R) + ') ÷ 100 = ' + f2(raw);
		formula += b.ended ?
			', rounded down to ' + par + '. Team 2 wins if they scored more than ' + par + ' and the match is tied on ' + par + '.' :
			', rounded down to ' + par + '. Target = ' + par + ' + 1 = ' + (par + 1) + '.';
		var steps = a.steps.concat(['Team 1 resources, R1 = ' + f1(a.R) + '%.']);
		if (o.note) {
			steps.push(o.note);
		}
		steps = steps.concat(b.steps, ['Team 2 resources, R2 = ' + f1(b.R) + '%.', formula]);

		var minB = minBalls(o.N), warn = '';
		if (minB && (b.ended ? b.lastAt : b.allot) < minB) {
			warn = 'Team 2 ' + (b.ended ? 'faced' : 'can face') + ' fewer than ' + overs(minB) + ' overs, so under ICC rules there is no result unless the match was already decided.';
		}
		return {
			N: o.N, S: o.S, G: o.G, M: o.M, R1: a.R, R2: b.R, raw: c2(raw), par: par, target: par + 1,
			allot: b.allot, lastAt: b.lastAt, ended: b.ended, steps: steps, warn: warn, rows: parRows(o.S, a.R, o.G, b)
		};
	}

	/** Par after each completed over left in the chase, for 0 to 9 wickets down. */
	function parRows(S, R1, G, b) {
		var rows = [];
		if (b.ended) {
			return rows;
		}
		for (var ov = Math.max(1, Math.ceil(b.lastAt / 6)); ov * 6 < b.allot; ov++) {
			var cells = [];
			for (var w = 0; w < 10; w++) {
				cells.push(floorPar(scale(S, r1(b.R - res(b.allot - ov * 6, w)), R1, G)));
			}
			rows.push({ over: ov, cells: cells });
		}
		return rows;
	}

	function check(S, G) {
		if (isNaN(S)) {
			return 'Enter Team 1\'s final score.';
		}
		if (S > 999) {
			return 'Team 1\'s score looks too high. Enter runs only.';
		}
		if (!(G > 0 && G <= 500)) {
			return 'Enter a G50 value between 1 and 500, for example 245.';
		}
		return '';
	}

	/**
	 * DLS Calculator page. p: { n: overs per side, r: Team 1 runs, g: G50, c: Team 2 overs at the start
	 * of the chase, a: Team 1 stops, b: Team 2 stops }. Short names that WordPress does not use itself
	 * (it treats s, m and w as its own query variables).
	 */
	function calc(p) {
		var N = balls(p.n), S = int(p.r);
		var G = /^\d{1,3}(\.\d{1,2})?$/.test(String(p.g == null ? '' : p.g).trim()) ? parseFloat(p.g) : NaN;
		var M = (p.c == null || String(p.c).trim() === '') ? N : balls(p.c);
		if (isNaN(N) || N < 6 || N > 300) {
			return { err: 'Overs per side must be between 1 and 50.' };
		}
		var e = check(S, G);
		if (e) {
			return { err: e };
		}
		if (isNaN(M) || M < 1 || M > N) {
			return { err: 'Overs for Team 2 at the start of the chase must be between 0.1 and ' + overs(N) + '.' };
		}
		return match({ N: N, S: S, G: G, t1: parseStops(p.a), M: M, t2: parseStops(p.b) });
	}

	/** T20 page. p: { n: overs per side (5 to 20), r: Team 1 runs, l: overs lost before the chase, b: chase stops }. */
	function t20(p) {
		var No = int(p.n), S = int(p.r), L = int(p.l);
		if (isNaN(No) || No < 5 || No > 20) {
			return { err: 'Overs per side must be between 5 and 20.' };
		}
		var e = check(S, 245);
		if (e) {
			return { err: e };
		}
		L = isNaN(L) ? 0 : Math.min(L, No - 5);
		var r = match({
			N: No * 6, S: S, G: 245, t1: [], M: (No - L) * 6, t2: parseStops(p.b),
			note: L ? 'Rain cut ' + L + (L === 1 ? ' over' : ' overs') + ' before the chase, so Team 2 starts with ' + (No - L) + ' overs.' : ''
		});
		if (r.err) {
			return r;
		}
		r.No = No;
		r.L = L;
		r.rr = r.ended ? '' : (r.target / (r.allot / 6)).toFixed(2);
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
			return { err: 'Overs per side must be between 5 and 50.' };
		}
		var e = check(S, 245);
		if (e) {
			return { err: e };
		}
		var M = (p.c == null || String(p.c).trim() === '') ? No * 6 : balls(p.c);
		if (isNaN(M) || M < 6 || M > No * 6) {
			return { err: 'Overs for Team 2 must be between 1 and ' + No + '.' };
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
		var base = calc({ n: p.n, r: p.r, g: p.g, c: p.c });
		if (base.err) {
			return base;
		}
		var log = [], last = null, now = null;
		var parts = (p.x == null || String(p.x).trim() === '') ? [] : String(p.x).split('_').slice(0, LIVE_MAX);
		for (var i = 0; i < parts.length; i++) {
			var x = parts[i].split('-'), at = balls(x[0]), runs = int(x[1]), w = int(x[2]), name = 'Score entry ' + (i + 1);
			if (x.join('') === '') {
				continue;
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
			if (last && (at <= last.at || runs < last.runs || w < last.w)) {
				return { err: 'Score entries must be in order, with the overs going up.' };
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
				now = match({ N: base.N, S: base.S, G: base.G, t1: [], M: base.M, t2: [{ at: at, w: w, lost: base.allot - at }] });
				e.state = 'on';
				e.par = now.par;
				e.warn = now.warn;
			}
			e.diff = runs - e.par;
			log.push(e);
			last = e;
		}
		base.log = log;
		base.nowSteps = now ? now.steps.slice(-3) : [];
		return base;
	}

	return {
		res: res, balls: balls, overs: overs, scale: scale, innings: innings, match: match,
		parRows: parRows, parseStops: parseStops, calc: calc, t20: t20, parTable: parTable, live: live, STOPS_MAX: STOPS_MAX, LIVE_MAX: LIVE_MAX
	};
}));
