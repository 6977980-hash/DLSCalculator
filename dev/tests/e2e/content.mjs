// Wording, titles and SEO tags from the full site audit's content phase (F79-F90).
import { fresh, ok, eq } from './lib.mjs';

const text = async (url) => (await fetch(fresh(url))).text();
const meta = (html, attr, name) => (html.match(new RegExp(`<meta ${attr}="${name}" content="([^"]*)"`)) || [])[1];
const plain = (s) => s.replace(/&#0?39;|&#8217;/g, "'").replace(/&amp;/g, '&');

export default [
	{
		name: 'content: one accuracy sentence everywhere, no old ODI wording (audit F79, F90)',
		run: async ({ base }) => {
			const line = 'every T20 target and par came within 3 runs of this calculator, while ODIs differed by up to 23 runs';
			for (const path of ['/', '/dls-calculator/', '/dls-target-calculator-t20/', '/live-dls-par-score/', '/about/', '/famous-dls-matches/ipl-2023-final-csk-gt/', '/llms.txt']) {
				const html = await text(base + path);
				ok(html.includes(line), `${path} has the accuracy sentence`);
				ok(!/10 runs or more|3 runs or fewer|more in high-scoring ODIs/.test(html), `${path} still has the old ODI gap wording`);
			}
			ok(!/Accurate DLS/.test(await text(base + '/')), 'home hero no longer says "Accurate DLS"');
		}
	},
	{
		name: 'content: small text fixes on the VJD, 1992 and England v Ireland pages (audit F85, F86)',
		run: async ({ base }) => {
			ok(!/BCCI used it/.test(plain(await text(base + '/vjd-method/'))), '/vjd-method/ says the BCCI uses VJD');
			ok(!/a few runs off the last ball/.test(await text(base + '/famous-dls-matches/1992-world-cup-semi-final-england-south-africa/')), '1992 page drops "a few runs"');
			ok(!/11 for 0/.test(await text(base + '/famous-dls-matches/t20-world-cup-2022-england-ireland/')), 'England v Ireland drops the unsourced "11 for 0" break');
		}
	},
	{
		name: 'content: the widget says it is independent, not ICC software (audit F84)',
		run: async ({ base }) => {
			ok(/not ICC software/.test(await text(base + '/embed/dls/')), '/embed/dls/ text');
		}
	},
	{
		name: 'content: titles up to 60 characters and descriptions up to 160 (audit F82)',
		run: async ({ base }) => {
			const xml = await text(base + '/wp-sitemap-posts-page-1.xml');
			const long = [];
			for (const url of (xml.match(/<loc>[^<]+/g) || []).map((s) => s.slice(5))) {
				const html = await text(url);
				const title = plain((html.match(/<title>([^<]*)<\/title>/) || [])[1] || '');
				const desc = plain(meta(html, 'name', 'description') || '');
				// The DLS calculator title stays at 61 by the owner's choice (audit 2, F26).
				if (title.length > 60 && !url.endsWith('/dls-calculator/')) {
					long.push(`${url.replace(base, '')} title ${title.length}`);
				}
				if (desc.length > 160) {
					long.push(`${url.replace(base, '')} description ${desc.length}`);
				}
			}
			ok(!long.length, long.join(', '));
		}
	},
	{
		name: 'content: sitemap lastmod and dateModified follow the theme text date (audit F83)',
		run: async ({ base }) => {
			const xml = await text(base + '/wp-sitemap-posts-page-1.xml');
			const lastmod = (xml.match(new RegExp(`<loc>${base}/dls-calculator/</loc><lastmod>([^<]+)`)) || [])[1];
			const html = await text(base + '/dls-calculator/');
			const modified = meta(html, 'property', 'article:modified_time');
			ok(lastmod, 'sitemap has a lastmod for /dls-calculator/');
			eq(Date.parse(lastmod), Date.parse(modified), 'sitemap lastmod vs article:modified_time');
			ok(Date.parse(modified) >= Date.parse('2026-10-20T08:30:00Z'), 'dateModified is at least this content phase: ' + modified);
		}
	},
	{
		name: 'content: print sheet link keeps only the tool\'s own params (audit F87)',
		run: async ({ ctx, base }) => {
			const url = fresh(base + '/dls-par-score-table/?o=20&r=160&fbclid=abc&utm_source=wa');
			const html = await text(url);
			const noJs = plain((html.match(/<span data-sheet-url>([^<]*)/) || [])[1] || '');
			eq(noJs, base + '/dls-par-score-table/?o=20&r=160', 'printed link without JavaScript');
			const page = await ctx.newPage();
			await page.goto(url);
			await page.evaluate(() => window.dispatchEvent(new Event('beforeprint')));
			const js = await page.locator('[data-sheet-url]').first().textContent();
			ok(!/fbclid|utm_|nc=/.test(js) && js.includes('r=160'), 'printed link with JavaScript: ' + js);
		}
	},
	{
		name: 'content: contact and privacy pages have their own share image (audit F88)',
		run: async ({ base }) => {
			for (const path of ['/contact/', '/privacy-policy/']) {
				const img = meta(await text(base + path), 'property', 'og:image') || '';
				ok(img && !img.endsWith('/default.png'), `${path} og:image ${img}`);
				eq((await fetch(img)).status, 200, `${path} share image`);
			}
		}
	},
	{
		name: 'content: /page/2/ goes home instead of copying it (audit F89)',
		run: async ({ base }) => {
			for (const path of ['/page/2/', '/page/7/', '/dls-calculator/2/']) {
				const r = await fetch(base + path, { redirect: 'manual' });
				eq(r.status, 301, path + ' status');
				eq(r.headers.get('location'), base + (path.startsWith('/page/') ? '/' : '/dls-calculator/'), path + ' goes to');
			}
		}
	}
];
