// NRR calculator, qualification scenario, run rate, strike rate and bowling average driven like a visitor.
import { NRR, fresh, ok, eq, outs, watch } from './lib.mjs';

async function open(ctx, url) {
	const page = await ctx.newPage();
	const errors = watch(page);
	await page.goto(fresh(url));
	page.noErrors = () => ok(!errors.length, errors.join('; '));
	return page;
}

const xOf = (url) => new URL(url).searchParams;

export default [
	{
		name: 'nrr calculator: edit, add a match, all out, rain-cut and no result',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/net-run-rate-calculator/');
			const check = async (what) => {
				const q = xOf(page.url());
				eq((await outs(page)).cbig, NRR.calc({ q: q.get('q'), x: q.get('x') }).nrr, what);
			};
			await page.fill('input[name="x_rf[0]"]', '200');
			await check('after editing runs');
			await page.check('input[name="x_aa[0]"]');
			await check('after ticking "They were all out"');
			await page.fill('input[name="x_mo[2]"]', '15');
			ok(/no more than 15 per side/.test((await outs(page)).cerr), 'rain-cut match with 20 overs typed is an error');
			await page.fill('input[name="x_of[2]"]', '15');
			await page.fill('input[name="x_oa[2]"]', '14.2');
			await check('rain-cut match');
			await page.getByRole('button', { name: '+ Add match' }).click();
			await page.fill('input[name="x_rf[3]"]', '120');
			await page.fill('input[name="x_of[3]"]', '20');
			await page.fill('input[name="x_ra[3]"]', '121');
			await page.fill('input[name="x_oa[3]"]', '19.1');
			await check('fourth match');
			const before = (await outs(page)).cbig;
			await page.check('input[name="x_nr[3]"]');
			await check('no result');
			ok((await outs(page)).cbig !== before, 'no result leaves the match out');
			// Reopening the link shows the same NRR.
			const link = page.url().replace(/#.*$/, '');
			await page.goto(fresh(link));
			await check('reopened link');
			page.noErrors();
		}
	},
	{
		name: 'nrr calculator: overs like 19.6 are refused with the 0-to-5 balls rule',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/net-run-rate-calculator/');
			await page.fill('input[name="x_of[0]"]', '19.6');
			ok(/after 19\.5 comes 20/.test((await outs(page)).cerr), 'overs error');
		}
	},
	{
		name: 'qualification scenario: same answer with and without JavaScript',
		run: async ({ ctx, base, browser }) => {
			const urls = ['/net-run-rate-calculator/?u=t', '/net-run-rate-calculator/?u=t&y=4', '/net-run-rate-calculator/?u=t&k=c&e=150', '/net-run-rate-calculator/?u=t&y=3&v=4&o=4', '/net-run-rate-calculator/?u=t&z=w'];
			const off = await browser.newContext({ javaScriptEnabled: false });
			const noJs = await off.newPage();
			const page = await open(ctx, base + '/');
			for (const u of urls) {
				await page.goto(fresh(base + u));
				await noJs.goto(fresh(base + u));
				const a = await outs(page), b = await outs(noJs);
				eq(a.sbig, b.sbig, u + ' headline');
				eq(a.sline, b.sline, u + ' line');
			}
			await off.close();
			page.noErrors();
		}
	},
	{
		name: 'qualification scenario: a team name is shown as text, never run as HTML',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/net-run-rate-calculator/?u=t');
			await page.locator('input[name="t_n[]"]').first().fill('<img src=x onerror=alert(1)>');
			ok(!(await page.locator('[data-out="standings"] img').count()), 'no image element from the name');
			page.noErrors();
		}
	},
	{
		name: 'qualification scenario: note matches the answer when points decide',
		known: 'F52',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/net-run-rate-calculator/?u=t&y=3&v=4&o=4');
			const o = await outs(page);
			ok(!/net run rate decides/.test(o.snote) || !/margin does not matter/.test(o.sline), `"${o.sline}" next to "${o.snote}"`);
		}
	},
	{
		name: 'run rate: chasing, won, lost, batting first and the converter',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/run-rate-calculator/');
			for (const [t, r, o] of [['181', '180', '19.5'], ['181', '181', '19'], ['181', '100', '20'], ['181', '50', '0']]) {
				await page.fill('input[name=t]', t);
				await page.fill('input[name=r]', r);
				await page.fill('input[name=o]', o);
				const want = NRR.runRate({ u: 'c', n: '20', t, r, o });
				const got = await outs(page);
				eq(got.big, want.big, `${r} in ${o} chasing ${t}`);
			}
			await page.locator('label:has-text("Batting first")').click();
			await page.fill('input[name=r]', '92');
			await page.fill('input[name=o]', '12.4');
			eq((await outs(page)).big, NRR.runRate({ u: 'f', n: '20', r: '92', o: '12.4' }).big, 'projected score');
			await page.fill('#conv input[name=v]', '12.4');
			ok(/76 balls/.test(await page.locator('#conv output').first().innerText()), 'overs to balls');
			page.noErrors();
		}
	},
	{
		name: 'strike rate and bowling average match the engine',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/strike-rate-calculator/');
			for (const [r, b, i, o] of [['50', '30', '3', '1'], ['0', '10', '', ''], ['50', '0', '', '']]) {
				await page.fill('input[name=r]', r);
				await page.fill('input[name=b]', b);
				await page.fill('input[name=i]', i);
				await page.fill('input[name=o]', o);
				const want = NRR.batting({ r, b, i, o });
				eq((await outs(page)).big, want.err ? '–' : want.big, `strike rate ${r}/${b}`);
			}
			await page.goto(fresh(base + '/bowling-average-calculator/'));
			for (const [o, r, k] of [['10', '50', '0'], ['38.4', '212', '9'], ['4', '20', '5']]) {
				await page.fill('input[name=o]', o);
				await page.fill('input[name=r]', r);
				await page.fill('input[name=k]', k);
				const want = NRR.bowling({ o, r, k });
				eq((await outs(page)).big, want.err ? '–' : want.big, `bowling ${o}-${r}-${k}`);
			}
			page.noErrors();
		}
	}
];
