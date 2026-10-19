// Offline mode (no signal at the ground) and a slow phone connection.
import { DLS, NRR, fresh, ok, eq, outs } from './lib.mjs';

/** Opens home, waits for the service worker to take over and save the calculators. */
async function installed(ctx, base) {
	const page = await ctx.newPage();
	await page.goto(base + '/');
	await page.waitForFunction(() => navigator.serviceWorker && navigator.serviceWorker.controller, null, { timeout: 30000 }).catch(() => {});
	await page.reload();
	await page.waitForFunction(async () => {
		const keys = await caches.keys();
		return keys.length && (await (await caches.open(keys[0])).keys()).length > 5;
	}, null, { timeout: 30000 });
	return page;
}

/** No network for the page or the service worker from here on. */
const offline = (ctx) => ctx.route('**/*', (r) => r.abort('internetdisconnected'));
const go = (page, url) => page.goto(url).catch(() => null);

export default [
	{
		name: 'offline: the calculators open with no signal',
		run: async ({ ctx, base }) => {
			const page = await installed(ctx, base);
			await offline(ctx);
			for (const path of ['/dls-calculator/', '/dls-target-calculator-t20/', '/net-run-rate-calculator/', '/live-dls-par-score/', '/run-rate-calculator/']) {
				await go(page, base + path);
				ok((await page.locator('form[data-tool], form.tool__form').count()) > 0, path + ' has its form offline');
				eq(new URL(page.url()).pathname, path, 'address');
			}
			await go(page, base + '/dls-calculator/');
			await page.fill('form[data-tool] [name=r]', '199');
			eq((await outs(page)).big, 200, 'typing offline still calculates');
		}
	},
	{
		name: 'offline: a result link opens with its own inputs',
		run: async ({ ctx, base }) => {
			const page = await installed(ctx, base);
			await offline(ctx);
			await go(page, base + '/dls-calculator/?n=50&r=231&g=245&c=50&f=odi&b=30-4-10');
			const want = DLS.calc({ n: '50', r: '231', g: '245', c: '50', f: 'odi', b: '30-4-10' }).target;
			eq((await outs(page)).big, want, 'target shown for the link offline');
		}
	},
	{
		name: 'offline: links to the other calculators open with their own inputs',
		run: async ({ ctx, base }) => {
			const page = await installed(ctx, base);
			await offline(ctx);
			const T = 'Lions~4~3~6~640~78.2~600~80_Tigers~4~2~4~610~80~590~79.3_Hawks~4~2~4~580~80~575~80_Kings~4~1~2~560~80~625~79_Stars~4~2~4~600~80~600~80';
			const solve = NRR.solve({ t: T, y: '5', v: '3', o: '1', k: 'c', e: '150', q: '20', d: '2', z: '' });
			const checks = [
				['/dls-target-calculator-t20/?n=20&r=150&l=4&b=10-2-2', 'big', DLS.t20({ n: '20', r: '150', l: '4', b: '10-2-2' }).target],
				['/net-run-rate-calculator/?q=20&x=150-20-140-20_120-18.4a-121-19,2', 'cbig', NRR.calc({ q: '20', x: '150-20-140-20_120-18.4a-121-19.2' }).nrr],
				['/net-run-rate-calculator/?u=t&t=' + encodeURIComponent(T) + '&y=5&v=3&o=1&k=c&e=150&h=20&d=2', 'sbig', solve.big],
				['/run-rate-calculator/?u=f&n=20&r=60&o=10', 'big', NRR.runRate({ u: 'f', n: '20', t: '', r: '60', o: '10' }).big],
				['/strike-rate-calculator/?r=50&b=40', 'big', NRR.batting({ r: '50', b: '40' }).big]
			];
			for (const [url, key, want] of checks) {
				await go(page, base + url);
				eq((await outs(page))[key], want, url + ' offline');
			}
		}
	},
	{
		name: 'offline: reloading the live tracker keeps the score log',
		run: async ({ ctx, base }) => {
			const page = await installed(ctx, base);
			await go(page, base + '/live-dls-par-score/');
			for (const runs of ['9', '20']) {
				await page.getByRole('button', { name: /Add next over/ }).click();
				await page.keyboard.type(runs);
			}
			await offline(ctx);
			await page.reload().catch(() => null);
			eq(await page.locator('.lrow').count(), 2, 'score rows after an offline reload');
		}
	},
	{
		name: 'offline: a page that was not saved says so instead of showing home',
		run: async ({ ctx, base }) => {
			const page = await installed(ctx, base);
			await offline(ctx);
			await go(page, base + '/famous-dls-matches/');
			const h1 = await page.locator('h1').first().innerText().catch(() => '');
			ok(!/Rain stopped play\? Get the DLS/.test(h1) && /offline|no connection|signal/i.test(await page.locator('body').innerText()), 'got the home page: ' + h1);
		}
	},
	{
		name: 'slow 3G phone: calculators usable within 3 seconds',
		run: async ({ ctx, base }) => {
			const page = await ctx.newPage();
			const cdp = await ctx.newCDPSession(page);
			await cdp.send('Network.emulateNetworkConditions', { offline: false, latency: 400, downloadThroughput: 50 * 1024, uploadThroughput: 50 * 1024 });
			await cdp.send('Emulation.setCPUThrottlingRate', { rate: 4 });
			for (const path of ['/', '/dls-calculator/', '/net-run-rate-calculator/', '/live-dls-par-score/']) {
				await page.goto(fresh(base + path), { waitUntil: 'load' });
				const t = await page.evaluate(() => {
					const n = performance.getEntriesByType('navigation')[0];
					const fcp = performance.getEntriesByName('first-contentful-paint')[0];
					return { fcp: fcp ? fcp.startTime : 0, load: n.loadEventEnd };
				});
				ok(t.fcp < 2000 && t.load < 3000, `${path}: first paint ${Math.round(t.fcp)} ms, load ${Math.round(t.load)} ms`);
			}
		}
	}
];
