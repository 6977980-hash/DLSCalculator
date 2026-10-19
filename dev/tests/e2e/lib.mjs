// Shared helpers for the live-site browser tests (dev/tests/e2e/run.mjs).
import { createRequire } from 'module';
import path from 'path';
import { fileURLToPath } from 'url';

const require = createRequire(import.meta.url);
const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../../..');

// The engines the pages run, straight from this checkout: the expected answers come from them.
export const DLS = require(path.join(root, 'assets/js/dls-engine.js'));
export const NRR = require(path.join(root, 'assets/js/nrr-engine.js'));

/** Playwright from this folder, the PLAYWRIGHT_PATH folder or a global install. */
export function loadPlaywright() {
	const tries = [process.env.PLAYWRIGHT_PATH, 'playwright', '/opt/node-tools/node_modules/playwright'].filter(Boolean);
	for (const t of tries) {
		try {
			return require(t);
		} catch (e) { /* next */ }
	}
	console.error('Playwright not found. Install it once: npm i --no-save playwright && npx playwright install chromium');
	process.exit(2);
}

/** Adds a cache-busting parameter so LiteSpeed and the CDN never answer with an old copy. */
export function fresh(url) {
	return url + (url.includes('?') ? '&' : '?') + 'nc=' + Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
}

export class Fail extends Error {}

export function eq(got, want, what) {
	if (String(got) !== String(want)) {
		throw new Fail(`${what}: got ${JSON.stringify(got)}, expected ${JSON.stringify(want)}`);
	}
}

export function ok(cond, what) {
	if (!cond) {
		throw new Fail(what);
	}
}

/** Text of every visible [data-out] result element on the page, by key. */
export async function outs(page) {
	return page.evaluate(() => {
		const o = {};
		document.querySelectorAll('[data-out],[data-o]').forEach((el) => {
			const k = el.getAttribute('data-out') || el.getAttribute('data-o');
			if (!(k in o)) {
				o[k] = el.hidden || el.closest('[hidden]') ? '' : el.textContent.trim().replace(/\s+/g, ' ');
			}
		});
		return o;
	});
}

/** Collects uncaught errors and console errors from a page. */
export function watch(page) {
	const errors = [];
	page.on('pageerror', (e) => errors.push('pageerror: ' + e.message));
	page.on('console', (m) => {
		if (m.type() === 'error' && !/status of 404/.test(m.text())) {
			errors.push('console: ' + m.text());
		}
	});
	page.on('dialog', (d) => {
		errors.push('dialog: ' + d.message());
		d.dismiss();
	});
	return errors;
}
