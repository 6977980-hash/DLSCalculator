// Test inputs shared by the JS and PHP test runners: the six ICC worked examples plus a seeded
// spread of random matches. Prints JSON: [{ fn, p, expect? }].
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
	cases.push({ fn: 'calc', p: { n: ov(N), r: String(80 + rnd(300)), g: ['245', '200', '222.5'][rnd(3)], c: ov(M), a: stops(N), b: stops(M) } });
	cases.push({ fn: 't20', p: { n: String(5 + rnd(16)), r: String(60 + rnd(200)), l: String(rnd(16)), b: rnd(2) ? stops(60) : '' } });
	cases.push({ fn: 'parTable', p: { o: String(5 + rnd(46)), r: String(60 + rnd(300)) } });
}
// Bad input must give the same error text in both.
cases.push({ fn: 'calc', p: { n: '50', r: '', g: '245' } });
cases.push({ fn: 'calc', p: { n: '50', r: '200', g: '245', b: '12.7-1-10' } });
cases.push({ fn: 'calc', p: { n: '50', r: '200', g: '245', b: '20-1-10_12-1-5' } });
cases.push({ fn: 't20', p: { n: '25', r: '150' } });
cases.push({ fn: 'parTable', p: { o: '20', r: '150', c: '25' } });

process.stdout.write(JSON.stringify(cases));
