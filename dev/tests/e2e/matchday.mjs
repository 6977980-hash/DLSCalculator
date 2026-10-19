// Match-day bugs from the full site audit (F66 to F73, F91): scores pasted from a scorecard, saved
// matches and shared links, clearing the live tracker, two tabs, incomplete links and the phone layout.
import { DLS, NRR, fresh, ok, eq, outs, watch } from './lib.mjs';

async function open(ctx, url) {
	const page = await ctx.newPage();
	const errors = watch(page);
	await page.goto(fresh(url));
	page.noErrors = () => ok(!errors.length, errors.join('; '));
	return page;
}

/** Pastes text into a field the way a scorer copies "98/6" from a scorecard app. */
async function paste(page, sel, value) {
	await page.evaluate((v) => navigator.clipboard.writeText(v), value);
	await page.locator(sel).first().click();
	await page.keyboard.press('ControlOrMeta+a');
	await page.keyboard.press('ControlOrMeta+v');
}

/** What the server prints for a link, with JavaScript off. */
async function serverOuts(browser, url) {
	const ctx = await browser.newContext({ javaScriptEnabled: false });
	const page = await ctx.newPage();
	await page.goto(fresh(url));
	const o = await outs(page);
	await ctx.close();
	return o;
}

const WK = /enter runs only, without wickets \(98, not 98\/6\)/i;

async function addOver(page, r, w) {
	await page.getByRole('button', { name: /Add next over/ }).click();
	const row = page.locator('.lrow').last();
	await row.locator('[data-f="r"]').fill(r);
	await row.locator('[data-f="w"]').fill(w);
}

export default [
	{
		name: 'match day: score boxes: "98/6" from a scorecard is refused, never read as 986',
		context: { permissions: ['clipboard-read', 'clipboard-write'] },
		run: async ({ ctx, base }) => {
			const fields = [
				['/dls-calculator/', 'form[data-tool] [name=r]', 'err'],
				['/dls-target-calculator-t20/', 'form[data-tool] [name=r]', 'err'],
				['/dls-par-score-table/', 'form[data-tool] [name=r]', 'err'],
				['/live-dls-par-score/', 'form[data-tool] [name=r]', 'err'],
				['/run-rate-calculator/', 'form[data-form="rr"] [name=r]', 'err'],
				['/net-run-rate-calculator/', 'input[name="x_ra[0]"]', 'cerr']
			];
			for (const [path, sel, key] of fields) {
				const page = await open(ctx, base + path);
				await paste(page, sel, '98/6');
				ok(WK.test((await outs(page))[key]), path + ': error for "98/6": ' + JSON.stringify((await outs(page))[key]) + ', field now ' + await page.locator(sel).first().inputValue());
				await page.close();
			}
			// A score log row on the live tracker, typed with a dash.
			const page = await open(ctx, base + '/live-dls-par-score/');
			await addOver(page, '24-1', '1');
			ok(WK.test((await outs(page)).err), 'live tracker row "24-1": ' + (await outs(page)).err);
			page.noErrors();
		}
	},
	{
		name: 'match day: saved match: a link with fbclid or utm tags still opens it',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/live-dls-par-score/');
			await addOver(page, '9', '0');
			await addOver(page, '20', '1');
			await page.goto(base + '/live-dls-par-score/?fbclid=IwAR123');
			await page.waitForLoadState();
			eq(await page.locator('.lrow').count(), 2, 'live tracker rows after a Facebook link');
			const nrr = await open(ctx, base + '/net-run-rate-calculator/');
			await nrr.fill('input[name="x_rf[0]"]', '201');
			await nrr.goto(base + '/net-run-rate-calculator/?utm_source=whatsapp');
			await nrr.waitForLoadState();
			eq(await nrr.locator('input[name="x_rf[0]"]').inputValue(), '201', 'NRR match runs after a WhatsApp link');
			page.noErrors();
			nrr.noErrors();
		}
	},
	{
		name: 'match day: live tracker: "Start a new match" and × can be undone, and the button sits apart',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/live-dls-par-score/');
			await addOver(page, '9', '0');
			await addOver(page, '20', '1');
			const addBox = await page.getByRole('button', { name: /Add next over/ }).boundingBox();
			const resetBox = await page.getByRole('button', { name: 'Start a new match' }).boundingBox();
			ok(resetBox.y - (addBox.y + addBox.height) >= 24, 'gap between "Add next over" and "Start a new match": ' + (resetBox.y - (addBox.y + addBox.height)) + 'px');
			await page.getByRole('button', { name: 'Start a new match' }).click();
			eq(await page.locator('.lrow').count(), 0, 'rows after a new match');
			await page.getByRole('button', { name: 'Undo' }).click();
			eq(await page.locator('.lrow').count(), 2, 'rows after Undo');
			ok(/x=1-9-0_2-20-1/.test(page.url()), 'address after Undo: ' + page.url());
			await page.locator('.lrow').last().getByRole('button', { name: 'Remove score entry' }).click();
			eq(await page.locator('.lrow').count(), 1, 'rows after ×');
			await page.getByRole('button', { name: 'Undo' }).click();
			eq(await page.locator('.lrow').count(), 2, 'rows after undoing ×');
			page.noErrors();
		}
	},
	{
		name: 'match day: incomplete links: what is left out is blank, not a sample figure',
		run: async ({ ctx, base, browser }) => {
			const checks = [
				['/dls-target-calculator-t20/?n=20&r=180', 'big', String(DLS.t20({ n: '20', r: '180' }).target)],
				['/run-rate-calculator/?u=c&n=20&r=92&o=12.4', 'err', 'Enter the target, the runs needed to win.'],
				['/dls-calculator/?n=50&g=245', 'err', 'Enter Team 1\'s final score.']
			];
			for (const [url, key, want] of checks) {
				const page = await open(ctx, base + url);
				eq((await outs(page))[key], want, url + ' with JavaScript');
				eq((await serverOuts(browser, base + url))[key], want, url + ' without JavaScript');
				page.noErrors();
				await page.close();
			}
		}
	},
	{
		name: 'match day: links with an empty stoppage part give the target, with and without JavaScript',
		run: async ({ ctx, base, browser }) => {
			for (const b of ['12-1-10_', '_12-1-10']) {
				const url = base + '/dls-calculator/?n=50&r=250&g=245&c=50&b=' + b;
				const page = await open(ctx, url);
				eq((await outs(page)).big, '218', b + ' with JavaScript');
				eq((await serverOuts(browser, url)).big, '218', b + ' without JavaScript');
				await page.close();
			}
		}
	},
	{
		name: 'match day: widget: picking ODI sets 50 overs',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/embed/dls/');
			await page.fill('[name=c]', '20');
			await page.selectOption('[name=f]', 'odi');
			eq(await page.inputValue('[name=n]'), '50', 'overs per side after picking ODI');
			eq(await page.inputValue('[name=c]'), '50', 'Team 2 overs follow');
			await page.fill('[name=c]', '40');
			eq((await page.locator('[data-o="target"]').innerText()).trim(), DLS.calc({ n: '50', r: '160', g: '245', c: '40', f: 'odi' }).target, 'ODI target with 40 overs for Team 2');
			ok(/[?&]n=50/.test(await page.locator('[data-o="full"]').getAttribute('href')), 'full calculator link carries 50 overs');
			page.noErrors();
		}
	},
	{
		name: 'match day: live tracker: two tabs on the same match stay in step',
		run: async ({ ctx, base }) => {
			const a = await open(ctx, base + '/live-dls-par-score/');
			await addOver(a, '9', '0');
			const b = await ctx.newPage();
			await b.goto(base + '/live-dls-par-score/');
			await b.waitForLoadState();
			await addOver(a, '20', '1');
			await b.waitForTimeout(500);
			eq(await b.locator('.lrow').count(), 2, 'rows in the older tab after the other tab adds an over');
			await b.fill('form[data-tool] [name=c]', '19');
			const saved = await b.evaluate(() => localStorage.getItem('dlscalc-live'));
			ok(/x=1-9-0_2-20-1/.test(saved), 'saved match keeps both overs: ' + saved);
			a.noErrors();
		}
	},
	{
		name: 'match day: nrr calculator: on a phone the answer shows at the bottom while the result is below',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/net-run-rate-calculator/');
			await page.fill('input[name="x_rf[0]"]', '200');
			const bar = page.locator('.stick');
			const q = new URL(page.url()).searchParams;
			ok(await bar.isVisible(), 'answer bar visible while the result is below the screen');
			ok((await bar.innerText()).includes(NRR.calc({ q: q.get('q'), x: q.get('x') }).nrr), 'bar shows the NRR: ' + await bar.innerText());
			await page.locator('#nrr-result').scrollIntoViewIfNeeded();
			await page.waitForTimeout(300);
			ok(!(await bar.isVisible()), 'bar hides once the result is on screen');
			page.noErrors();
		}
	},
	{
		name: 'match day: theme files: no test PDFs are served',
		run: async ({ ctx, base }) => {
			for (const f of ['q0.pdf', 'z.pdf']) {
				const res = await ctx.request.get(fresh(base + '/wp-content/themes/dlscalc/' + f));
				ok(res.status() === 404 || res.status() === 403, f + ' answers ' + res.status());
			}
		}
	}
];
