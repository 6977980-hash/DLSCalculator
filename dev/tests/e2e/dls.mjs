// DLS tools driven like a visitor: typing, stoppage rows, the T20 slider, the par table, the live
// tracker, the widget, the home box, sharing, printing and the no-JavaScript forms.
import { createRequire } from 'module';
import { DLS, fresh, ok, eq, outs, watch } from './lib.mjs';

const realMatches = createRequire(import.meta.url)('../real-matches.json');

const form = 'form[data-tool]';

async function open(ctx, url) {
	const page = await ctx.newPage();
	const errors = watch(page);
	await page.goto(fresh(url));
	page.noErrors = () => ok(!errors.length, errors.join('; '));
	return page;
}

async function addStop(page, team, at, w, lost) {
	await page.getByRole('button', { name: new RegExp('Add Team ' + team + ' stoppage') }).click();
	const row = page.locator(`[data-stops="${team === 1 ? 'a' : 'b'}"] .stop`).last();
	await row.locator('[data-f="at"]').fill(at);
	await row.locator('[data-f="w"]').fill(w);
	await row.locator('[data-f="lost"]').fill(lost);
}

/** The target the server prints for a link, with JavaScript off. */
async function serverBig(browser, url) {
	const ctx = await browser.newContext({ javaScriptEnabled: false });
	const page = await ctx.newPage();
	await page.goto(fresh(url));
	const o = await outs(page);
	await ctx.close();
	return o;
}

export default [
	{
		name: 'dls calculator: typed score and a Team 2 stoppage give the engine target',
		run: async ({ ctx, base, browser }) => {
			const page = await open(ctx, base + '/dls-calculator/');
			await page.fill(`${form} [name=r]`, '231');
			await addStop(page, 2, '30', '4', '10');
			const want = DLS.calc({ n: '50', r: '231', g: '245', c: '50', f: 'odi', b: '30-4-10' });
			let o = await outs(page);
			eq(o.big, want.target, 'target');
			eq(o.line, 'to win in ' + DLS.overs(want.allot) + ' overs', 'overs line');
			const link = page.url();
			ok(/[?&]b=30-4-10/.test(link) && /[?&]r=231/.test(link), 'address bar carries the inputs: ' + link);
			// Opening the link again shows the same result, with and without JavaScript.
			await page.goto(fresh(link.replace(/#.*$/, '')));
			o = await outs(page);
			eq(o.big, want.target, 'target after reopening the link');
			eq((await serverBig(browser, link.replace(/#.*$/, ''))).big, want.target, 'server target for the link');
			page.noErrors();
		}
	},
	{
		name: 'dls calculator: Team 1 stoppage (ICC example 1)',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/dls-calculator/');
			await page.fill(`${form} [name=r]`, '180');
			await addStop(page, 1, '20', '3', '10');
			await page.fill(`${form} [name=c]`, '40');
			eq((await outs(page)).big, 185, 'ICC example 1 target');
		}
	},
	{
		name: 'dls calculator: bad inputs show the error under the right field',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/dls-calculator/');
			const cases = [
				['n', '12.7', /12\.7 is not valid/],
				['r', '-5', /cannot be negative/],
				['c', '60', /between 0\.1 and 50/]
			];
			for (const [f, v, msg] of cases) {
				await page.goto(fresh(base + '/dls-calculator/'));
				await page.fill(`${form} [name=${f}]`, v);
				const o = await outs(page);
				eq(o.big, '–', `big number for ${f}=${v}`);
				ok(msg.test(o.err), `error for ${f}=${v}: ${o.err}`);
				const invalid = await page.$eval(`${form} [name=${f === 'c' ? 'c' : f}]`, (el) => el.getAttribute('aria-invalid'));
				eq(invalid, 'true', `aria-invalid on ${f}`);
			}
			await page.goto(fresh(base + '/dls-calculator/'));
			await addStop(page, 2, '30', '10', '5');
			ok(/wickets down must be 0 to 9/.test((await outs(page)).err), 'wickets 10 in a stoppage');
		}
	},
	{
		name: 'dls calculator: typing a shorter match (40 overs) carries to Team 2 overs',
		known: 'F48',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/dls-calculator/');
			await page.fill(`${form} [name=n]`, '40');
			const o = await outs(page);
			eq(o.err, '', 'error after typing 40 overs per side');
			eq(o.big, DLS.calc({ n: '40', r: '250', g: '245', c: '40', f: 'odi' }).target, 'target');
		}
	},
	{
		name: 'dls calculator: clear messages for a decimal or huge score',
		known: 'F49',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/dls-calculator/');
			for (const v of ['250.5', '99999']) {
				await page.fill(`${form} [name=r]`, v);
				const err = (await outs(page)).err;
				ok(!/^Enter Team 1's final score/.test(err), `r=${v} says "${err}", which reads as if the field were empty`);
			}
		}
	},
	{
		name: 'dls calculator: format picker sets T20 overs and the 5-over warning',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/dls-calculator/');
			await page.selectOption(`${form} [name=f]`, 't20');
			eq(await page.inputValue(`${form} [name=n]`), '20', 'overs per side');
			eq(await page.inputValue(`${form} [name=c]`), '20', 'Team 2 overs');
			await page.fill(`${form} [name=r]`, '160');
			await page.fill(`${form} [name=c]`, '4');
			const o = await outs(page);
			eq(o.big, DLS.calc({ n: '20', r: '160', g: '245', c: '4', f: 't20' }).target, 'target');
			ok(/fewer than 5 overs/.test(o.warn), 'minimum overs warning: ' + o.warn);
		}
	},
	{
		name: 'home: quick box result and its link to the calculator agree',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/');
			await page.fill('.quick [name=r]', '150');
			await page.fill('.quick [name=c]', '12');
			const want = DLS.calc({ n: '20', r: '150', g: '245', c: '12' }).target;
			eq((await outs(page)).big, want, 'home target');
			await page.click('.quick button[type=submit]');
			await page.waitForLoadState();
			ok(page.url().includes('/dls-calculator/'), 'lands on the DLS calculator');
			eq((await outs(page)).big, want, 'calculator target');
			eq(await page.inputValue(`${form} [name=f]`), 't20', 'format picked from 20 overs');
			page.noErrors();
		}
	},
	{
		name: 't20 calculator: keyboard on the rain slider',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/dls-target-calculator-t20/');
			await page.fill(`${form} [name=r]`, '172');
			const slider = page.locator('#rain-slider');
			await slider.focus();
			await slider.press('ArrowRight');
			await slider.press('ArrowRight');
			eq(await slider.inputValue(), '7', 'slider value');
			eq((await outs(page)).big, DLS.t20({ n: '20', r: '172', l: '7' }).target, 'target');
			await page.fill(`${form} [name=n]`, '8');
			eq(await slider.getAttribute('max'), '3', 'slider top end for an 8-over match');
			page.noErrors();
		}
	},
	{
		name: 'par table: rows match the engine',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/dls-par-score-table/');
			await page.fill(`${form} [name=r]`, '145');
			await page.fill(`${form} [name=c]`, '14');
			const want = DLS.parTable({ o: '20', r: '145', c: '14' });
			eq((await outs(page)).big, want.target, 'target');
			const rows = await page.$$eval('[data-out="rows"] tr', (trs) => trs.map((t) => t.innerText.trim().split(/\s+/).join(' ')));
			eq(rows.length, want.rows.length, 'row count');
			want.rows.forEach((r, i) => eq(rows[i], [r.over].concat(r.cells).join(' '), 'row ' + r.over));
		}
	},
	{
		name: 'live tracker: over by over pars, saved on the device, new match',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/live-dls-par-score/');
			const add = page.getByRole('button', { name: /Add next over/ });
			const entries = [['1', '9', '0'], ['6', '52', '1'], ['10', '70', '3'], ['12.3', '85', '3']];
			for (const [at, r, w] of entries) {
				await add.click();
				const row = page.locator('.lrow').last();
				await row.locator('[data-f="at"]').fill(at);
				await row.locator('[data-f="r"]').fill(r);
				await row.locator('[data-f="w"]').fill(w);
			}
			const want = DLS.live({ n: '20', r: '160', g: '245', c: '', f: 't20', x: entries.map((e) => e.join('-')).join('_') });
			const pars = await page.$$eval('.lrow [data-par]', (els) => els.map((e) => e.textContent));
			eq(pars.join(','), want.log.map((e) => e.par).join(','), 'par per row');
			const o = await outs(page);
			eq(o.big, want.log[3].par, 'headline par');
			ok(/Team 1 would win by 8 runs/.test(o.line), 'margin in runs: ' + o.line);
			// A return visit without the link reopens the saved match.
			await page.goto(base + '/live-dls-par-score/');
			await page.waitForLoadState();
			eq(await page.locator('.lrow').count(), 4, 'rows after a return visit');
			await page.getByRole('button', { name: 'Start a new match' }).click();
			eq(await page.locator('.lrow').count(), 0, 'rows after a new match');
			page.noErrors();
		}
	},
	{
		name: 'live tracker: entries out of order are refused',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/live-dls-par-score/?n=20&r=160&g=245&f=t20&x=10-70-3_6-52-1');
			ok(/in order/.test((await outs(page)).err), 'error for overs going down');
		}
	},
	{
		name: 'live tracker: runs or wickets going down get their own message',
		known: 'F50',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/live-dls-par-score/?n=20&r=160&g=245&f=t20&x=6-52-1_10-40-3');
			const err = (await outs(page)).err;
			ok(err && !/overs going up/.test(err), 'runs dropping from 52 to 40 says: ' + err);
		}
	},
	{
		name: 'widget: result, warning and links inside a page on another site',
		run: async ({ ctx, base }) => {
			const page = await ctx.newPage();
			await page.route('https://club.example/', (r) => r.fulfill({ contentType: 'text/html', body: `<h1>Club</h1><iframe src="${base}/embed/dls/" width="100%" height="500" style="border:0;max-width:480px" title="DLS target calculator"></iframe>` }));
			await page.goto('https://club.example/');
			const frame = page.frameLocator('iframe');
			await frame.locator('[name=r]').fill('140');
			await frame.locator('[name=c]').fill('4');
			eq((await frame.locator('[data-o="target"]').innerText()).trim(), DLS.calc({ n: '20', r: '140', g: '245', c: '4', f: 't20' }).target, 'widget target');
			ok(/Fewer than 5 overs/.test(await frame.locator('body').innerText()), 'widget shows the minimum overs warning');
			const targets = await frame.locator('a').evaluateAll((as) => as.map((a) => a.target));
			ok(targets.every((t) => t === '_blank'), 'widget links open a new tab');
		}
	},
	{
		name: 'sharing: WhatsApp text and Copy link carry the result link',
		context: { permissions: ['clipboard-read', 'clipboard-write'] },
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/dls-calculator/');
			await page.fill(`${form} [name=r]`, '231');
			await addStop(page, 2, '30', '4', '10');
			const wa = decodeURIComponent((await page.locator('[data-out="wa"]').getAttribute('href')).split('text=')[1]);
			ok(wa.startsWith('DLS target: 194 in 40 overs (par 193).'), 'WhatsApp text: ' + wa);
			ok(wa.includes('/dls-calculator/?n=50&r=231&g=245&c=50&f=odi&b=30-4-10'), 'WhatsApp link: ' + wa);
			await page.getByRole('button', { name: 'Copy link' }).first().click();
			await page.waitForTimeout(300);
			const copied = await page.evaluate(() => navigator.clipboard.readText());
			ok(copied.endsWith('/dls-calculator/?n=50&r=231&g=245&c=50&f=odi&b=30-4-10'), 'copied: ' + copied);
		}
	},
	{
		name: 'print: par sheet hides the form and ends with the result link',
		desktop: true,
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/dls-calculator/?n=50&r=231&g=245&c=50&f=odi&b=30-4-10');
			await page.evaluate(() => window.dispatchEvent(new Event('beforeprint')));
			await page.emulateMedia({ media: 'print' });
			const v = await page.evaluate(() => ({
				form: getComputedStyle(document.querySelector('form[data-tool]')).display,
				head: getComputedStyle(document.querySelector('.site-head')).display,
				share: getComputedStyle(document.querySelector('.share')).display,
				url: [...document.querySelectorAll('[data-sheet-url]')].map((e) => e.textContent).join(' ')
			}));
			eq(v.form, 'none', 'form in print');
			eq(v.head, 'none', 'site header in print');
			eq(v.share, 'none', 'share buttons in print');
			ok(v.url.includes('b=30-4-10'), 'sheet ends with the link: ' + v.url);
		}
	},
	{
		name: 'no JavaScript: forms still give the answer',
		context: { javaScriptEnabled: false },
		run: async ({ ctx, base }) => {
			const page = await ctx.newPage();
			await page.goto(fresh(base + '/dls-calculator/'));
			await page.fill(`${form} [name=r]`, '231');
			await page.fill(`${form} [name=c]`, '40');
			await page.click(`${form} button[type=submit]`);
			await page.waitForLoadState();
			eq((await outs(page)).big, DLS.calc({ n: '50', r: '231', g: '245', c: '40', f: 'odi' }).target, 'DLS target');
			await page.goto(fresh(base + '/dls-target-calculator-t20/'));
			await page.fill(`${form} [name=r]`, '150');
			await page.click(`${form} button[type=submit]`);
			await page.waitForLoadState();
			eq((await outs(page)).big, DLS.t20({ n: '20', r: '150', l: '5' }).target, 'T20 target');
		}
	},
	{
		name: 'consistency: the same chase gives one target on every DLS page',
		run: async ({ base }) => {
			let seed = 20261019;
			const rnd = (k) => (seed = (seed * 1103515245 + 12345) % 2147483648) % k;
			const big = async (url, re) => ((await (await fetch(fresh(base + url))).text()).match(re) || [])[1];
			const bad = [];
			for (let i = 0; i < 12; i++) {
				const t20 = i % 2 === 0, n = t20 ? 20 : 50, f = t20 ? 't20' : 'odi';
				const r = t20 ? 100 + rnd(120) : 150 + rnd(200), c = t20 ? 5 + rnd(15) : 20 + rnd(30);
				const want = String(DLS.calc({ n: String(n), r: String(r), g: '245', c: String(c), f }).target);
				const urls = [
					`/dls-calculator/?n=${n}&r=${r}&g=245&c=${c}&f=${f}`,
					`/dls-par-score-table/?o=${n}&r=${r}&c=${c}`,
					`/live-dls-par-score/?n=${n}&r=${r}&g=245&c=${c}&f=${f}`
				].concat(t20 ? [`/dls-target-calculator-t20/?n=20&r=${r}&l=${20 - c}`] : []);
				for (const u of urls) {
					const got = await big(u, /data-out="big"[^>]*>\s*([^<\s]+)/);
					if (got !== want) {
						bad.push(`${u}: ${got} (engine ${want})`);
					}
				}
				const w = await big(`/embed/dls/?f=${f}&n=${n}&r=${r}&c=${c}`, /data-o="target"[^>]*>\s*([^<\s]+)/);
				if (w !== want) {
					bad.push(`embed ${n}/${r}/${c}: ${w} (engine ${want})`);
				}
			}
			ok(!bad.length, bad.join('; '));
		}
	},
	{
		name: 'real matches: the live calculator gives our Standard result for 34 rain-hit matches',
		run: async ({ base }) => {
			const bad = [];
			for (const m of realMatches) {
				const html = await (await fetch(fresh(base + '/dls-calculator/?' + new URLSearchParams(m.p)))).text();
				const got = (html.match(/data-out="big"[^>]*>\s*([^<\s]+)/) || [])[1];
				const want = 'target' in m.standard ? m.standard.target : m.standard.par;
				if (String(got) !== String(want)) {
					bad.push(`${m.id}: ${got} (expected ${want})`);
				}
			}
			ok(!bad.length, bad.join('; '));
		}
	}
];
