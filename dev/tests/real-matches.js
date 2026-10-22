// Real rain-hit matches (2015-2025) with the official DLS target or par from the scorecards.
// Official figures come from the Professional Edition; this engine is the Standard Edition, so they
// can differ. The test fails when our Standard result for a match changes (a regression guard) and
// prints the gap to the official figure. Date and Cricinfo match id (ci) per match are also printed on /famous-dls-matches/#real-matches
// Run: node dev/tests/real-matches.js
'use strict';
const DLS = require('../../assets/js/dls-engine.js');
const matches = require('../../inc/real-matches.json');

let changed = 0;
const gaps = { t20: [], odi: [] };
for (const m of matches) {
	const r = DLS.calc(m.p);
	const key = 'target' in m.official ? 'target' : 'par';
	const ours = key === 'target' ? r.target : r.par;
	if (r.err || ours !== m.standard[key] || (key === 'par' && !r.ended)) {
		changed++;
		console.log(`CHANGED ${m.id}: ${key} ${r.err || ours}, recorded ${m.standard[key]}`);
	}
	gaps[m.p.f === 't20' ? 't20' : 'odi'].push(Math.abs(ours - m.official[key]));
}
const sum = (a) => `${a.length} matches, ${a.filter((g) => g === 0).length} exact, ${a.filter((g) => g <= 3).length} within 3 runs, largest gap ${Math.max(...a)}`;
console.log(`Real matches vs official (Professional Edition): T20 ${sum(gaps.t20)}; ODI ${sum(gaps.odi)}`);
if (changed) {
	console.log(`${changed} real-match results changed: check the engine change, then update "standard" in inc/real-matches.json`);
	process.exit(1);
}
