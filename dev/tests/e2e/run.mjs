// Live-site browser tests: every tool driven like a visitor would, on a phone and a desktop screen.
// Run after each deploy:  node dev/tests/e2e/run.mjs [https://dlscalculator.com] [--only=name] [--headed]
// Needs Playwright with Chromium (see dev/tests/e2e/README.md). Expected answers come from the
// engines in this checkout, so run it from the commit that is live.
//
// A test tagged `known: 'F46'` checks behaviour an audit found wrong and the fix is not live yet:
// it is reported as KNOWN and does not fail the run. When it starts passing the run says so, and
// the tag should be removed so the fix stays guarded.
import { loadPlaywright, Fail } from './lib.mjs';
import pages from './pages.mjs';
import dls from './dls.mjs';
import nrr from './nrr.mjs';
import offline from './offline.mjs';

// Service worker requests only go through context.route() with this switch (offline tests).
process.env.PW_EXPERIMENTAL_SERVICE_WORKER_NETWORK_EVENTS = '1';
const { chromium, devices } = loadPlaywright();

const args = process.argv.slice(2);
const base = (args.find((a) => /^https?:\/\//.test(a)) || process.env.BASE_URL || 'https://dlscalculator.com').replace(/\/$/, '');
const only = (args.find((a) => a.startsWith('--only=')) || '').slice(7);
const headed = args.includes('--headed');

const phone = { ...devices['Pixel 5'] };
const desktop = { viewport: { width: 1280, height: 800 } };
const tests = [...pages, ...dls, ...nrr, ...offline].filter((t) => !only || t.name.includes(only));

const browser = await chromium.launch({ headless: !headed });
const tally = { pass: 0, fail: 0, known: 0, fixed: 0 };
const started = Date.now();
for (const t of tests) {
	const ctx = await browser.newContext({ ...(t.desktop ? desktop : phone), ...(t.context || {}) });
	const t0 = Date.now();
	let status, note = '';
	try {
		await t.run({ ctx, base, browser, devices });
		status = t.known ? 'FIXED' : 'PASS';
	} catch (e) {
		note = e instanceof Fail ? e.message : (e.stack || String(e)).split('\n').slice(0, 3).join(' ');
		status = t.known ? 'KNOWN' : 'FAIL';
	}
	await ctx.close();
	tally[{ PASS: 'pass', FAIL: 'fail', KNOWN: 'known', FIXED: 'fixed' }[status]]++;
	const tag = t.known ? ` [${t.known}]` : '';
	console.log(`${status.padEnd(5)} ${t.name}${tag} (${((Date.now() - t0) / 1000).toFixed(1)}s)${note ? '\n      ' + note : ''}`);
	if (status === 'FIXED') {
		console.log(`      ${t.known} now passes: remove its known tag in dev/tests/e2e so it stays guarded.`);
	}
}
await browser.close();
console.log(`\n${base}: ${tally.pass} passed, ${tally.fail} failed, ${tally.known} known issues still open, ${tally.fixed} known issues now fixed (${((Date.now() - started) / 1000).toFixed(0)}s)`);
process.exit(tally.fail ? 1 : 0);
