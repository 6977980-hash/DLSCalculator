// Test inputs shared by the JS and PHP test runners: the six ICC worked examples, NRR and run rate
// worked examples, plus a seeded spread of random matches, points tables and live score logs. Prints JSON: [{ fn, p, expect? }].
'use strict';

const icc = [
	// ICC playing conditions, DLS appendix, Standard Edition examples (G50 = 245).
	{ fn: 'calc', p: { n: '50', r: '180', g: '245', c: '40', a: '20-3-10' }, expect: { R1: 87.5, R2: 89.3, target: 185 } },
	{ fn: 'calc', p: { n: '45', r: '212', g: '245', c: '35' }, expect: { R1: 95, R2: 82.7, target: 185 } },
	{ fn: 'calc', p: { n: '50', r: '250', g: '245', c: '50', b: '12-1-10' }, expect: { R2: 86.8, target: 218, allot: 240 } },
	{ fn: 'calc', p: { n: '50', r: '250', g: '245', c: '50', b: '12-1-10_22-3-2' }, expect: { R2: 83.2, target: 209 } },
	{ fn: 'calc', p: { n: '50', r: '250', g: '245', c: '50', b: '12-1-10_22-3-2_30.2-6-7.4' }, expect: { R2: 63.8, par: 159, ended: true } },
	{ fn: 'calc', p: { n: '50', r: '226', g: '245', c: '33', a: '47.1-8-2.5' }, expect: { R1: 93.1, R2: 79.8, target: 194 } },
	{ fn: 'calc', p: { n: '50', r: '226', g: '245', c: '33', a: '47.1-8-2.5', b: '25-2-5' }, expect: { R2: 64.7, target: 158 } },
];

// Penalty runs to Team 1 during the chase (ICC DLS 6.2) raise the target and every par; bad inputs
// name their field, and overs like 12.7 get the 0-to-5 balls rule (audit F15, F19).
icc.push(
	{ fn: 'calc', p: { n: '50', r: '250', g: '245', c: '50', b: '12-1-10', k: '5' }, expect: { R2: 86.8, par: 222, target: 223 } },
	{ fn: 'calc', p: { n: '50', r: '250', g: '245', c: '50', b: '12-1-10_22-3-2_30.2-6-7.4', k: '5' }, expect: { par: 164, ended: true } },
	{ fn: 'calc', p: { n: '50', r: '250', g: '245', c: '50', k: '100' }, expect: { field: 'k' } },
	{ fn: 'calc', p: { n: '12.7', r: '250', g: '245' }, expect: { field: 'n', err: 'Overs per side: 12.7 is not valid. Balls after the dot go from 0 to 5, so after 12.5 comes 13.' } },
	{ fn: 'calc', p: { n: '50', r: '', g: '245' }, expect: { field: 'r' } },
	{ fn: 'calc', p: { n: '50', r: '250', g: '245', c: '40.6' }, expect: { field: 'c' } },
	{ fn: 'calc', p: { n: '50', r: '250', g: '245', b: '12-1-10_22.9-3-2' }, expect: { field: 'b-2-at' } },
	{ fn: 'calc', p: { n: '50', r: '250', g: '245', a: '20-3-1:5' }, expect: { field: 'a-1-lost', err: 'Team 1 stoppage 1, overs lost: write overs as 32.4 for 32 overs and 4 balls.' } },
	{ fn: 'calc', p: { n: '50', r: '250', g: '245', b: '30-2-5_20-3-5' }, expect: { field: 'b-2-at' } },
	{ fn: 'parTable', p: { o: '20', r: '160', c: '15.6' }, expect: { field: 'c' } },
	{ fn: 'live', p: { n: '20', r: '160', g: '245', x: '5.8-40-1' }, expect: { err: 'Score entry 1: 5.8 is not valid. Balls after the dot go from 0 to 5, so after 5.5 comes 6.' } }
);

let seed = 20261019;
const rnd = (n) => {
	seed = (seed * 1103515245 + 12345) % 2147483648;
	return seed % n;
};
const ov = (b) => (b % 6 ? Math.floor(b / 6) + '.' + (b % 6) : String(b / 6));

const cases = icc.slice();
for (let i = 0; i < 400; i++) {
	const N = [300, 120, 60, 270, 240][rnd(5)];
	const stops = (allot) => {
		const out = [];
		let at = 0, w = 0;
		for (let k = rnd(3); k > 0 && allot - at > 6; k--) {
			at += 1 + rnd(allot - at - 1);
			w = Math.min(9, w + rnd(4));
			const left = allot - at;
			const lost = rnd(4) ? 1 + rnd(left) : left;
			out.push(ov(at) + '-' + w + '-' + ov(lost));
			allot -= lost;
			if (allot <= at) break;
		}
		return out.join('_');
	};
	const M = 6 + rnd(N - 5);
	cases.push({ fn: 'calc', p: { n: ov(N), r: String(80 + rnd(300)), g: ['245', '200', '222.5'][rnd(3)], c: ov(M), a: stops(N), b: stops(M), f: ['', 'odi', 't20', 't10', 'custom', 'zz'][rnd(6)], k: ['', '', '', '5', '1', 'x'][rnd(6)] } });
	const tn = 5 + rnd(16);
	cases.push({ fn: 't20', p: { n: String(tn), r: String(60 + rnd(200)), l: String(rnd(tn - 4)), b: rnd(2) ? stops(60) : '' } });
	cases.push({ fn: 'parTable', p: { o: String(5 + rnd(46)), r: String(60 + rnd(300)) } });
}
// Live par tracker: random score logs, judged at every entry.
for (let i = 0; i < 150; i++) {
	const N = [300, 120, 60, 240][rnd(4)], M = rnd(3) ? N : 6 + rnd(N - 5);
	const log = [];
	let at = 0, runs = 0, w = 0;
	for (let k = 1 + rnd(12); k > 0 && at < M; k--) {
		at = Math.min(M, at + 1 + rnd(24));
		runs += rnd(40);
		w = Math.min(10, w + (rnd(3) ? 0 : 1 + rnd(3)));
		log.push(ov(at) + '-' + runs + '-' + w);
		if (w === 10) break;
	}
	cases.push({ fn: 'live', p: { n: ov(N), r: String(60 + rnd(300)), g: '245', c: ov(M), x: log.join('_') } });
}

// NRR engine: worked examples, then random matches, points tables, solver runs and run rates.
cases.push({ fn: 'nrr.calc', p: { q: '20', x: '160-20-150-20_160-20-150-20' }, expect: { nrr: '+0.500' } });
cases.push({ fn: 'nrr.calc', p: { q: '20', x: '168-20-151-20_142-18.3a-145-20' }, expect: { nrr: '+0.350', bf: 240 } });
cases.push({ fn: 'nrr.calc', p: { q: '20', x: '120-15a-121-14.2_150-20-140-20-n' }, expect: { nrr: '-2.442', counted: 1 } });
// Minimum overs go by format, not by the overs the match started with (audit F12): a 40-over ODI
// still needs 20 overs for Team 2, a 15-over T20 still needs 5.
const NO_RESULT = 'Team 2 can face fewer than 20 overs, so under ICC rules there is no result unless the match was already decided.';
cases.push({ fn: 'calc', p: { n: '40', r: '220', g: '245', c: '40', b: '10-2-25', f: 'odi' }, expect: { allot: 90, warn: NO_RESULT } });
cases.push({ fn: 'calc', p: { n: '40', r: '220', g: '245', c: '40', b: '10-2-25', f: 'custom' }, expect: { allot: 90, warn: '' } });
cases.push({ fn: 'calc', p: { n: '40', r: '220', g: '245', c: '40', b: '10-2-15', f: 'odi' }, expect: { allot: 150, warn: '' } });
cases.push({ fn: 'calc', p: { n: '50', r: '220', g: '245', c: '50', b: '10-2-35' }, expect: { warn: NO_RESULT } });
cases.push({ fn: 't20', p: { n: '15', r: '130', l: '0', b: '3-1-11' }, expect: { allot: 24, warn: 'Team 2 can face fewer than 5 overs, so under ICC rules there is no result unless the match was already decided.' } });
cases.push({ fn: 'live', p: { n: '40', r: '220', g: '245', c: '30', f: 'odi', x: '5-30-1' }, expect: { target: 186, warn: '' } });

// Rain-reduced match: an all-out side counts the overs it was entitled to in that match (audit F2).
cases.push({ fn: 'nrr.calc', p: { q: '20', x: '143-15-120-12a-15' }, expect: { nrr: '+1.533', ba: 90 } });
cases.push({ fn: 'nrr.calc', p: { q: '20', x: '143-15-120-12a' }, expect: { nrr: '+3.533', ba: 120 } });
cases.push({ fn: 'nrr.calc', p: { q: '20', x: '110-9.4a-111-8.1-12_160-20-150-20-n_150-20-140-20' }, expect: { nrr: '-0.786', bf: 192, ba: 169, counted: 2 } });
cases.push({ fn: 'nrr.runRate', p: { n: '20', t: '181', r: '92', o: '12.4' }, expect: { big: '12.14', crr: '7.26', need: 89, left: 44 } });
cases.push({ fn: 'nrr.convert', p: { v: '12.5', j: '77' }, expect: { ov: '77 balls (12.83 overs in decimal)', bl: '12.5 overs' } });
// Stats calculators (audit F16): strike rate, batting average, bowling average, economy.
cases.push({ fn: 'nrr.batting', p: { r: '182', b: '140', i: '5', o: '1', f: '18', x: '6', g: '150' }, expect: { big: '130.00', avg: '45.50', outs: 4 } });
cases.push({ fn: 'nrr.batting', p: { r: '57', b: '38', i: '2', o: '2' }, expect: { sr: '150.00', avg: null, outs: 0 } });
cases.push({ fn: 'nrr.batting', p: { r: '18426', b: '21367', i: '452', o: '41' }, expect: { sr: '86.24', avg: '44.83' } });
cases.push({ fn: 'nrr.bowling', p: { o: '38.4', r: '212', k: '9', d: '130' }, expect: { big: '23.56', econ: '5.48', sr: '25.8' } });
cases.push({ fn: 'nrr.bowling', p: { o: '4', r: '31', k: '0' }, expect: { label: 'Economy rate', big: '7.75', avg: null } });
cases.push({ fn: 'nrr.bowling', p: { o: '1250.3', r: '5320', k: '211' }, expect: { avg: '25.21', econ: '4.25', sr: '35.6' } });
const T4 = 'Team A~4~3~6~640~78.2~600~80_Team B~4~2~4~610~80~590~79.3_Team C~4~2~4~580~80~575~80_Team D~4~1~2~560~80~625~79';
cases.push({ fn: 'nrr.solve', p: { t: T4, y: '3', v: '1', o: '0', k: 'f', e: '180', q: '20' }, expect: { kind: 'runs', margin: 63 } });
cases.push({ fn: 'nrr.solve', p: { t: T4, y: '3', v: '1', o: '0', k: 'c', e: '150', q: '20' }, expect: { kind: 'overs', within: '12.1' } });
cases.push({ fn: 'nrr.solve', p: { t: T4, y: '3', v: '2', o: '0', k: 'f', e: '170', q: '20' }, expect: { kind: 'points' } });
// Rounding from whole numbers, half away from zero, the same in JS and PHP (audit F38).
cases.push({ fn: 't20', p: { n: '16', r: '162', l: '2', b: '9.3-4-0.4' }, expect: { rr: '10.43' } });
cases.push({ fn: 'nrr.calc', p: { q: '20', x: '378-13.3a-58-10.4' }, expect: { nrr: '+13.463' } });
cases.push({ fn: 'nrr.calc', p: { q: '50', x: '279-37.4a-120-10.4-48' }, expect: { nrr: '-5.438' } });
// Equal net run rates are not "above" (audit F25): both sides end on exactly -1.050 at 79.
const TIE = 'Me~1~0~~20~20~63~20_Riv~1~0~~169~20~190~20_X~1~1~~1~1~1~1';
cases.push({ fn: 'nrr.solve', p: { t: TIE, y: '1', v: '2', o: '3', k: 'f', e: '80', q: '20' }, expect: { kind: 'runs', margin: 2, limit: 78 } });
// Tie-break (audit F45): with z=w, teams level on points are split by wins before net run rate.
const WINS = 'A~5~2~6~600~80~620~80_B~5~2~8~640~80~600~80_C~5~4~8~640~80~600~80_D~5~3~6~500~80~520~80';
cases.push({ fn: 'nrr.solve', p: { t: WINS, y: '1', v: '2', o: '4', k: 'f', e: '160', q: '20', z: 'w' }, expect: { kind: 'points', big: 'Any win' } });
cases.push({ fn: 'nrr.solve', p: { t: WINS, y: '1', v: '2', o: '4', k: 'f', e: '160', q: '20' }, expect: { kind: 'runs' } });
cases.push({ fn: 'nrr.solve', p: { t: WINS, y: '1', v: '3', o: '4', k: 'f', e: '160', q: '20', z: 'w' }, expect: { kind: 'short', big: 'Not on wins' } });
cases.push({ fn: 'nrr.solve', p: { t: WINS, y: '4', v: '2', o: '1', k: 'f', e: '160', q: '20', z: 'w' }, expect: { big: 'Any win' } });
cases.push({ fn: 'nrr.solve', p: { t: 'A~5~~6~600~80~620~80_B~5~2~8~640~80~600~80', y: '1', v: '2', o: '0', k: 'f', e: '160', q: '20', z: 'w' }, expect: { err: 'Fill in wins for A in the points table: teams level on points are split by wins first.' } });
const oval = (max) => ov(1 + rnd(max)) + (rnd(6) ? '' : 'a');
for (let i = 0; i < 200; i++) {
	const q = [20, 50, 10, 8][rnd(4)], ms = [];
	for (let k = 1 + rnd(6); k > 0; k--) {
		const mq = rnd(4) ? q * 6 : 6 + rnd(q * 6 - 5);
		ms.push([rnd(400), oval(mq), rnd(400), oval(mq)].join('-') + (mq < q * 6 || !rnd(20) ? '-' + ov(mq) : '') + (rnd(97) < 12 ? '-n' : ''));
	}
	cases.push({ fn: 'nrr.calc', p: { q: String(q), x: ms.join('_') } });
	const teams = [];
	for (let k = 2 + rnd(6); k > 0; k--) {
		const pl = rnd(10), won = rnd(pl + 1);
		teams.push(['Team ' + k, pl, won, rnd(2) ? 4 : rnd(3) ? '' : won * 2, 100 + rnd(2000), ov(60 + rnd(900)), 100 + rnd(2000), ov(60 + rnd(900))].join('~'));
	}
	const y = 1 + rnd(teams.length), v = 1 + ((y + rnd(teams.length - 1)) % teams.length), o = rnd(3) ? v : rnd(teams.length + 1);
	cases.push({ fn: 'nrr.solve', p: { t: teams.join('_'), y: String(y), v: String(v), o: String(o), k: rnd(2) ? 'f' : 'c', e: String(60 + rnd(300)), q: String(q), d: rnd(3) ? '' : String(rnd(3)), z: rnd(2) ? 'w' : '' } });
	const nn = [20, 50, 10][rnd(3)];
	cases.push({ fn: 'nrr.runRate', p: { u: rnd(3) ? 'c' : 'f', n: String(nn), t: String(50 + rnd(350)), r: String(rnd(300)), o: ov(rnd(nn * 6 + 1)) } });
	const bb = 1 + rnd(3000), bi = rnd(60);
	cases.push({ fn: 'nrr.batting', p: { r: String(rnd(bb * 2)), b: String(bb), i: rnd(4) ? String(bi) : '', o: rnd(2) ? String(rnd(bi + 1)) : '', f: rnd(2) ? String(rnd(bb / 8)) : '', x: rnd(2) ? String(rnd(bb / 20)) : '', g: rnd(2) ? String(50 + rnd(200)) : '' } });
	cases.push({ fn: 'nrr.bowling', p: { o: ov(1 + rnd(6000)), r: String(rnd(3000)), k: rnd(5) ? String(rnd(120)) : '', d: rnd(2) ? String(rnd(1000)) : '' } });
}

// Clear messages for common slips (audit F39), the same in both engines.
const BALLS = (label, o) => label + ': ' + o + ' is not valid. Balls after the dot go from 0 to 5, so after ' + o.split('.')[0] + '.5 comes ' + (parseInt(o, 10) + 1) + '.';
cases.push({ fn: 't20', p: { n: '20', r: '160', l: '18' }, expect: { field: 'l', err: 'Overs lost before the chase must be a whole number from 0 to 15: Team 2 needs at least 5 overs for a result in a T20.' } });
cases.push({ fn: 't20', p: { n: '10', r: '90', l: '5' }, expect: { allot: 30 } });
cases.push({ fn: 't20', p: { n: '20', r: '160', l: '' }, expect: { allot: 120 } });
cases.push({ fn: 'calc', p: { n: '50', r: '250', g: '245', b: '0-0-10' }, expect: { field: 'b-1-at', err: 'Team 2 stoppage 1: overs bowled must be more than 0. Overs lost before an innings starts are not a stoppage: enter the shorter innings as its overs instead.' } });
cases.push({ fn: 'calc', p: { n: '50', r: '-5', g: '245' }, expect: { field: 'r', err: 'Team 1\'s score cannot be negative.' } });
cases.push({ fn: 'calc', p: { n: '50', r: '250', g: '245', a: '30-3-10' }, expect: { warn: 'Team 2 starts with 50 overs, more than the 40 Team 1 had after its stoppages. Both sides usually get the same overs, so check Team 2\'s overs at the start of the chase.' } });
cases.push({ fn: 'calc', p: { n: '50', r: '250', g: '245', a: '30-3-10', c: '40' }, expect: { warn: '' } });
cases.push({ fn: 'nrr.calc', p: { q: '20', x: '160-20.7-150-20' }, expect: { err: BALLS('Match 1, overs faced', '20.7') } });
cases.push({ fn: 'nrr.calc', p: { q: '20', x: '160-20-150-19.6a' }, expect: { err: BALLS('Match 1, overs bowled', '19.6') } });
cases.push({ fn: 'nrr.calc', p: { q: '20', x: '−5-20-150-20' }, expect: { err: 'Match 1: runs cannot be negative.' } });
cases.push({ fn: 'nrr.runRate', p: { u: 'c', n: '20', t: '181', r: '92', o: '12.6' }, expect: { err: BALLS('Overs bowled so far', '12.6') } });
cases.push({ fn: 'nrr.runRate', p: { u: 'c', n: '20', t: '-181', r: '92', o: '12' }, expect: { err: 'The target cannot be negative.' } });
cases.push({ fn: 'nrr.batting', p: { r: '50', b: '40', i: '0' }, expect: { field: 'i', err: 'Innings must be at least 1, or leave it blank.' } });
cases.push({ fn: 'nrr.batting', p: { r: '-50', b: '40' }, expect: { field: 'r', err: 'Runs cannot be negative.' } });
cases.push({ fn: 'nrr.bowling', p: { o: '10.8', r: '50' }, expect: { field: 'o', err: BALLS('Overs bowled', '10.8') } });
cases.push({ fn: 'nrr.solve', p: { t: T4, y: '3', v: '1', o: '0', k: 'f', e: '-180', q: '20' }, expect: { err: 'The score cannot be negative.' } });

// Bad input must give the same error text in both.
cases.push({ fn: 'calc', p: { n: '50', r: '', g: '245' } });
cases.push({ fn: 'calc', p: { n: '50', r: '200', g: '245', b: '12.7-1-10' } });
cases.push({ fn: 'calc', p: { n: '50', r: '200', g: '245', b: '20-1-10_12-1-5' } });
cases.push({ fn: 't20', p: { n: '25', r: '150' } });
cases.push({ fn: 'parTable', p: { o: '20', r: '150', c: '25' } });
cases.push({ fn: 'live', p: { n: '20', r: '160', g: '245', x: '10-80-2_8-90-2' } });
cases.push({ fn: 'live', p: { n: '20', r: '160', g: '245', x: '10-170-2_12-180-2' } });
cases.push({ fn: 'nrr.calc', p: { q: '20', x: '160-21-150-20' } });
cases.push({ fn: 'nrr.calc', p: { q: '20', x: '160-16-150-12a-15' } });
cases.push({ fn: 'nrr.calc', p: { q: '20', x: '160-15-150-12a-21' } });
cases.push({ fn: 'nrr.calc', p: { q: '20', x: '160-15-150-12a-x-n' } });
cases.push({ fn: 'nrr.solve', p: { t: T4, y: '3', v: '3', k: 'f', e: '180', q: '20' } });
cases.push({ fn: 'nrr.runRate', p: { u: 'f', n: '20', r: '0', o: '0' } });
cases.push({ fn: 'nrr.batting', p: { r: '50', b: '0' } });
cases.push({ fn: 'nrr.batting', p: { r: '50', b: '40', i: '2', o: '3' } });
cases.push({ fn: 'nrr.batting', p: { r: '20', b: '40', f: '4', x: '1' } });
cases.push({ fn: 'nrr.batting', p: { r: '20', b: '40', g: '700' } });
cases.push({ fn: 'nrr.bowling', p: { o: '12.7', r: '50' } });
cases.push({ fn: 'nrr.bowling', p: { o: '1', r: '5', k: '7' } });
cases.push({ fn: 'nrr.bowling', p: { o: '10', r: '50', d: 'x' } });

// Overs typed with a comma (Android number pads) or a trailing dot read as the same overs (audit F55).
const NRRJS = require('../../assets/js/nrr-engine.js');
cases.push({ fn: 'calc', p: { n: '50.', r: '250', g: '245', c: '50', b: '12,0-1-10.' }, expect: { R2: 86.8, target: 218, allot: 240 } });
cases.push({ fn: 'calc', p: { n: '50', r: '250', g: '245', c: '40,' }, expect: { allot: 240 } });
cases.push({ fn: 'calc', p: { n: '50', r: '250', g: '245', c: '40,7' }, expect: { field: 'c', err: 'Overs for Team 2 at the start of the chase: 40.7 is not valid. Balls after the dot go from 0 to 5, so after 40.5 comes 41.' } });
cases.push({ fn: 'live', p: { n: '20', r: '160', g: '245', x: '5,3-40-1_10.-80-2' } });
cases.push({ fn: 'nrr.calc', p: { q: '20', x: '160-19,4-150-20._142-18.a-145-20' }, expect: { nrr: NRRJS.calc({ q: '20', x: '160-19.4-150-20_142-18a-145-20' }).nrr } });
cases.push({ fn: 'nrr.calc', p: { q: '20', x: '160-20,7-150-20' }, expect: { err: BALLS('Match 1, overs faced', '20.7') } });
cases.push({ fn: 'nrr.runRate', p: { u: 'c', n: '20', t: '181', r: '92', o: '12,4' }, expect: { big: NRRJS.runRate({ u: 'c', n: '20', t: '181', r: '92', o: '12.4' }).big } });
cases.push({ fn: 'nrr.bowling', p: { o: '38,4', r: '212', k: '9' }, expect: { big: NRRJS.bowling({ o: '38.4', r: '212', k: '9' }).big } });

process.stdout.write(JSON.stringify(cases));
