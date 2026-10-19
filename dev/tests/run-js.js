// Runs every case through assets/js/dls-engine.js. Usage: node run-js.js cases.json > out.json
'use strict';
const DLS = require('../../assets/js/dls-engine.js');
const cases = JSON.parse(require('fs').readFileSync(process.argv[2], 'utf8'));
process.stdout.write(JSON.stringify(cases.map((c) => DLS[c.fn](c.p))));
