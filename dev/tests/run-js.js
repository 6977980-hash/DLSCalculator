// Runs every case through assets/js/dls-engine.js or, for "nrr." cases, assets/js/nrr-engine.js. Usage: node run-js.js cases.json > out.json
'use strict';
const DLS = require('../../assets/js/dls-engine.js');
const NRR = require('../../assets/js/nrr-engine.js');
const cases = JSON.parse(require('fs').readFileSync(process.argv[2], 'utf8'));
const extra = { fmt: (p) => ({ f: DLS.fmtOf(p.n) }) };
process.stdout.write(JSON.stringify(cases.map((c) => (extra[c.fn] || (c.fn.startsWith('nrr.') ? NRR[c.fn.slice(4)] : DLS[c.fn]))(c.p))));
