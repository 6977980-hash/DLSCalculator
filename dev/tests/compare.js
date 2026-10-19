// Checks the ICC examples and that the JS and PHP engines agree on every case, and that both
// tables match the ICC table. Usage: node compare.js cases.json js.json php.json
'use strict';
const fs = require('fs');
const [cases, js, php] = process.argv.slice(2).map((f) => JSON.parse(fs.readFileSync(f, 'utf8')));
let fails = 0;
const fail = (msg) => {
	fails++;
	console.log('FAIL ' + msg);
};

// Numbers compare by value (PHP may print 100.0 where JS prints 100).
const same = (a, b) => JSON.stringify(a, (k, v) => (typeof v === 'number' ? +v.toFixed(6) : v)) === JSON.stringify(b, (k, v) => (typeof v === 'number' ? +v.toFixed(6) : v));

cases.forEach((c, i) => {
	if (!same(js[i], php[i])) {
		fail(`#${i} ${c.fn} ${JSON.stringify(c.p)}\n  js:  ${JSON.stringify(js[i]).slice(0, 400)}\n  php: ${JSON.stringify(php[i]).slice(0, 400)}`);
	}
	if (c.expect) {
		for (const [k, v] of Object.entries(c.expect)) {
			for (const [name, out] of [['js', js[i]], ['php', php[i]]]) {
				if (out[k] !== v) fail(`ICC example #${i + 1} ${name}: ${k} is ${out[k]}, expected ${v}`);
			}
		}
	}
});

// Table check, every cell, both engines.
const icc = JSON.parse(fs.readFileSync(__dirname + '/icc-table.json', 'utf8'));
const DLS = require('../../assets/js/dls-engine.js');
let cells = 0;
icc.forEach((row, w) => row.forEach((v, b) => {
	cells++;
	if (b > 0 && DLS.res(b, w) !== v / 10) fail(`JS table w${w} b${b}`);
}));
const phpTable = JSON.parse(require('child_process').execFileSync('php', ['-r', "define('ABSPATH',1);require '" + __dirname + "/../../inc/dls.php';echo json_encode(dlscalc_dls_table());"]).toString());
if (JSON.stringify(phpTable) !== JSON.stringify(icc)) fail('PHP table differs from the ICC table');

const examples = cases.filter((c) => c.expect).length;
console.log(fails ? `${fails} failures` : `OK: ${examples} ICC examples pass in JS and PHP, ${cases.length} cases agree, ${cells} table cells match`);
process.exit(fails ? 1 : 0);
