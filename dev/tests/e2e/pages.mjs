// Every page in the sitemap: loads, no script errors, no broken images, no sideways scroll on small
// phones, working internal links; framing rules for the widget.
import { fresh, ok, eq, watch } from './lib.mjs';

async function sitemap(base) {
	const xml = await (await fetch(fresh(base + '/wp-sitemap-posts-page-1.xml'))).text();
	const urls = (xml.match(/<loc>[^<]+/g) || []).map((s) => s.slice(5));
	ok(urls.length >= 25, `sitemap lists ${urls.length} pages, expected at least 25`);
	return urls.concat(base + '/embed/dls/');
}

async function crawl(ctx, base, width) {
	const page = await ctx.newPage();
	const errors = watch(page), problems = [];
	for (const url of await sitemap(base)) {
		errors.length = 0;
		const res = await page.goto(fresh(url), { waitUntil: 'networkidle' });
		const where = url.replace(base, '') || '/';
		if (res.status() !== 200) {
			problems.push(`${where}: HTTP ${res.status()}`);
		}
		const r = await page.evaluate(() => ({
			sw: document.documentElement.scrollWidth,
			w: document.documentElement.clientWidth,
			broken: [...document.images].filter((i) => i.complete && i.naturalWidth === 0).map((i) => i.src),
			h1: document.querySelectorAll('h1').length
		}));
		if (r.sw > r.w) {
			problems.push(`${where}: page scrolls sideways at ${width}px (${r.sw}px wide)`);
		}
		if (r.broken.length) {
			problems.push(`${where}: broken images ${r.broken.join(', ')}`);
		}
		if (r.h1 !== 1 && !where.startsWith('/embed/')) {
			problems.push(`${where}: ${r.h1} h1 headings`);
		}
		errors.forEach((e) => problems.push(`${where}: ${e}`));
	}
	ok(!problems.length, problems.join('\n      '));
}

export default [
	{ name: 'pages: all sitemap pages on a 320px phone', context: { viewport: { width: 320, height: 640 }, isMobile: true, hasTouch: true }, run: ({ ctx, base }) => crawl(ctx, base, 320) },
	{ name: 'pages: all sitemap pages on a 1280px desktop', desktop: true, run: ({ ctx, base }) => crawl(ctx, base, 1280) },
	{
		name: 'pages: internal links answer 200',
		run: async ({ ctx, base }) => {
			const page = await ctx.newPage(), links = new Set();
			for (const url of await sitemap(base)) {
				await page.goto(fresh(url));
				(await page.$$eval('a[href]', (as) => as.map((a) => a.href.split('#')[0]))).forEach((l) => l.startsWith(base) && links.add(l));
			}
			const bad = [];
			for (const l of links) {
				const r = await fetch(l, { redirect: 'manual' });
				if (r.status !== 200) {
					bad.push(`${r.status} ${l}`);
				}
			}
			ok(!bad.length, bad.join(', '));
		}
	},
	{
		name: 'pages: unknown address gives the 404 page',
		run: async ({ ctx, base }) => {
			const page = await ctx.newPage();
			const res = await page.goto(base + '/no-such-page-' + Date.now() + '/');
			eq(res.status(), 404, 'status');
			ok(await page.locator('a[href$="/dls-calculator/"]').count(), '404 page links to the DLS calculator');
		}
	},
	{
		name: 'pages: widget can be framed by another site, tool pages cannot',
		run: async ({ base }) => {
			const embed = await fetch(fresh(base + '/embed/dls/'));
			ok(!embed.headers.get('x-frame-options'), '/embed/dls/ must not send X-Frame-Options');
			ok(!/frame-ancestors/.test(embed.headers.get('content-security-policy') || ''), '/embed/dls/ must not limit frame-ancestors');
			const tool = await fetch(fresh(base + '/dls-calculator/'));
			ok(/sameorigin/i.test(tool.headers.get('x-frame-options') || ''), '/dls-calculator/ sends X-Frame-Options SAMEORIGIN');
		}
	}
];
