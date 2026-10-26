// Par tables, small input bugs, accessibility, security headers, legal pages and print from the full
// site audit's last phase (F74 to F78, F92 to F95, F97 to F103).
import { fresh, ok, eq, outs, watch } from './lib.mjs';

async function open(ctx, url) {
	const page = await ctx.newPage();
	const errors = watch(page);
	await page.goto(fresh(url));
	page.noErrors = () => ok(!errors.length, errors.join('; '));
	return page;
}

const head = async (url) => (await fetch(fresh(url), { redirect: 'manual' }));
const MIN = (n) => `Team 2 can face fewer than ${n} overs, so under ICC rules there is no result`;

/** Pages of a printed A4 sheet. */
async function printPages(page) {
	await page.evaluate(() => window.dispatchEvent(new Event('beforeprint')));
	const pdf = await page.pdf({ preferCSSPageSize: true });
	return (pdf.toString('latin1').match(/\/Type\s*\/Page[^s]/g) || []).length;
}

export default [
	{
		name: 'par tables: overs before the minimum are grey and a short chase is warned (audit F74, F75)',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/dls-par-score-table/?o=20&r=160');
			eq(await page.locator('.par tbody tr.par__nr').count(), 4, 'grey rows in a T20 table (overs 1 to 4)');
			ok(/Team 2 has faced 5 overs/.test((await outs(page)).nrnote), 'note under the T20 table: ' + (await outs(page)).nrnote);
			await page.locator('form[data-tool] [name=o]').fill('45');
			await page.locator('form[data-tool] [name=r]').fill('250');
			await page.locator('form[data-tool] [name=c]').fill('15');
			const o = await outs(page);
			eq(o.big, '119', 'target for a 15-over chase of 250');
			ok(o.warn.startsWith(MIN(20)), 'minimum-overs warning for a cut 45-over match: ' + o.warn);
			eq(await page.locator('.par tbody tr.par__nr').count(), 14, 'every row of a 15-over chase is before the 20-over minimum');
			const live = await open(ctx, base + '/live-dls-par-score/?n=50&r=250&g=245&f=odi');
			eq(await live.locator('.par tbody tr.par__nr').count(), 19, 'grey rows on the live tracker (overs 1 to 19)');
			page.noErrors();
		}
	},
	{
		name: 'par tables: grey rows come from the server too, without JavaScript (audit F74)',
		context: { javaScriptEnabled: false },
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/dls-calculator/?n=50&r=250&g=245&c=50&f=odi');
			eq(await page.locator('.par tbody tr.par__nr').count(), 19, 'grey ODI rows from the server');
			ok(/faced 20 overs/.test((await outs(page)).nrnote), 'note from the server');
		}
	},
	{
		name: 'dls calculator: "50.0" overs counts as an ODI, and G50 "245." is read as 245 (audit F76, F78)',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/dls-calculator/?n=50.0&r=250&c=12');
			ok((await outs(page)).warn.startsWith(MIN(20)), 'warning with JavaScript for n=50.0: ' + (await outs(page)).warn);
			eq(await page.locator('form[data-tool] [name=f]').inputValue(), 'odi', 'format picked for 50.0');
			const g = await open(ctx, base + '/dls-calculator/?n=50&r=250&g=245.&c=50&b=12-1-10');
			eq((await outs(g)).big, '218', 'target with G50 "245."');
			const half = await open(ctx, base + '/dls-calculator/?n=50&r=250&g=0.5&c=50');
			ok(/G50 value between 1 and 500/.test((await outs(half)).err), 'G50 0.5 refused: ' + (await outs(half)).err);
		}
	},
	{
		name: 't20 page: the rate is labelled as the rate for the whole chase (audit F77)',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/dls-target-calculator-t20/?n=20&r=180&l=0&b=10-2-5');
			ok(await page.getByText('Required rate, whole chase').count() > 0, 'stat label');
		}
	},
	{
		name: 'accessibility: dark green focus ring on light backgrounds, gold on green (audit F92)',
		desktop: true,
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/dls-calculator/');
			await page.locator('form[data-tool] [name=r]').focus();
			eq(await page.locator('form[data-tool] [name=r]').evaluate((el) => getComputedStyle(el).outlineColor), 'rgb(19, 69, 47)', 'field focus ring');
			await page.locator('.site-head a').first().focus();
			eq(await page.locator('.site-head a').first().evaluate((el) => getComputedStyle(el).outlineColor), 'rgb(242, 179, 61)', 'header link focus ring');
		}
	},
	{
		name: 'accessibility: sticky bar inside <main>, focus stays near a removed stoppage row (audit F99, F101)',
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/dls-calculator/?n=50&r=250&g=245&c=50&b=12-1-10_22-3-2');
			ok(await page.locator('main .stick').count() === 1, 'sticky bar is inside main');
			await page.locator('[data-stops="b"] .stop').nth(1).locator('[data-remove]').focus();
			await page.keyboard.press('Enter');
			const where = await page.evaluate(() => document.activeElement.matches('[data-stops="b"] .stop [data-remove], [data-add]'));
			ok(where, 'focus after removing a row: ' + await page.evaluate(() => document.activeElement.outerHTML.slice(0, 80)));
			const nrr = await open(ctx, base + '/net-run-rate-calculator/');
			await nrr.locator('[data-remove]').first().focus();
			await nrr.keyboard.press('Enter');
			ok(await nrr.evaluate(() => document.activeElement !== document.body), 'NRR: focus is not lost after removing a match');
			page.noErrors();
		}
	},
	{
		name: 'accessibility: at 200% zoom the sticky bar never covers the focused field (audit F100)',
		context: { viewport: { width: 640, height: 400 } },
		run: async ({ ctx, base }) => {
			const page = await open(ctx, base + '/dls-calculator/');
			await page.locator('form[data-tool] [name=r]').fill('251');
			// The field sits at the very bottom of the screen, where the bar is, when the keyboard reaches it.
			await page.evaluate(() => { const c = document.querySelector('form[data-tool] [name=c]'); scrollBy(0, c.getBoundingClientRect().bottom - innerHeight); });
			await page.waitForTimeout(200);
			await page.locator('form[data-tool] [name=c]').focus();
			await page.waitForTimeout(300);
			const r = await page.evaluate(() => {
				const f = document.activeElement.getBoundingClientRect(), bar = document.querySelector('.stick');
				return { bottom: f.bottom, bar: bar && !bar.hidden ? bar.getBoundingClientRect().top : innerHeight };
			});
			ok(r.bottom <= r.bar, `field ends at ${Math.round(r.bottom)} px, bar starts at ${Math.round(r.bar)} px`);
		}
	},
	{
		name: 'security: widget gets nosniff, referrer and permissions headers; pages get COOP (audit F97, F103)',
		run: async ({ base }) => {
			const embed = await head(base + '/embed/dls/');
			eq(embed.headers.get('x-content-type-options'), 'nosniff', 'widget nosniff');
			eq(embed.headers.get('referrer-policy'), 'strict-origin-when-cross-origin', 'widget referrer policy');
			ok(/camera=\(\)/.test(embed.headers.get('permissions-policy') || ''), 'widget permissions policy');
			ok(!embed.headers.get('x-frame-options') && !embed.headers.get('cross-origin-opener-policy'), 'widget can still be framed');
			const page = await head(base + '/dls-calculator/');
			eq(page.headers.get('cross-origin-opener-policy'), 'same-origin', 'page COOP');
			if (base.startsWith('https:')) {
				ok(/includeSubDomains/.test(page.headers.get('strict-transport-security') || ''), 'HSTS covers subdomains');
			}
			const login = await (await fetch(fresh(base + '/wp-login.php'))).text();
			// WordPress's own wp-admin files carry its version; libraries such as underscore keep theirs.
			ok(!/wp-admin\/[^"']+\?ver=\d+\.\d+/.test(login), 'login page assets do not show the WordPress version');
		}
	},
	{
		name: 'analytics: GA4 tag on pages, loaded after the page, never in the widget',
		run: async ({ base }) => {
			const html = await (await fetch(fresh(base + '/dls-calculator/'))).text();
			ok(/gtag\('config',"G-XT5JL42RV1"\)/.test(html), 'GA4 config on /dls-calculator/');
			ok(!/<script[^>]+src="https:\/\/www\.googletagmanager\.com/.test(html), 'gtag.js is not a blocking script tag');
			ok(/analytics_storage:'denied'/.test(html), 'EEA/UK consent default denied');
			const embed = await (await fetch(fresh(base + '/embed/dls/'))).text();
			ok(!/gtag|googletagmanager/.test(embed), 'widget has no analytics');
		}
	},
	{
		name: 'security: readme.txt and install.php refused, /favicon.ico answers (audit F95, F98)',
		run: async ({ base }) => {
			eq((await head(base + '/wp-content/themes/generatepress/readme.txt')).status, 403, 'parent theme readme.txt');
			eq((await head(base + '/wp-admin/install.php')).status, 403, 'install.php');
			const icon = await head(base + '/favicon.ico');
			eq(icon.status, 200, '/favicon.ico');
		}
	},
	{
		name: 'legal: privacy policy covers the offline copy, who runs the site and your rights (audit F93)',
		run: async ({ base }) => {
			const html = await (await fetch(fresh(base + '/privacy-policy/'))).text();
			for (const id of ['who', 'rights']) {
				ok(html.includes(`id="${id}"`), 'section #' + id);
			}
			ok(/service worker/.test(html) && /server logs/.test(html), 'offline copy and result links in logs');
			ok(!/nothing you type is sent to us/.test(await (await fetch(fresh(base + '/about/'))).text()), 'About no longer says nothing is sent');
		}
	},
	{
		name: 'legal: Terms and Disclaimer page with a footer link (audit F94, page made in WP admin)',
		run: async ({ base }) => {
			const res = await fetch(fresh(base + '/terms/'));
			eq(res.status, 200, '/terms/');
			const html = await res.text();
			ok(/<h1[^>]*>Terms and Disclaimer</.test(html), 'heading');
			ok(/The official figure is final/.test(html), 'official figure section');
			ok(/href="[^"]*\/terms\/"/.test(await (await fetch(fresh(base + '/'))).text()), 'footer link on the home page');
		}
	},
	{
		name: 'print: T20 live tracker sheet fits one A4 page, a 50-over par sheet two (audit F102)',
		desktop: true,
		run: async ({ ctx, base }) => {
			const live = await open(ctx, base + '/live-dls-par-score/?n=20&r=160&g=245&f=t20&x=1-8-0_2-15-0_3-24-1_4-30-1_5-41-1');
			eq(await printPages(live), 1, 'T20 live tracker sheet pages');
			const par = await open(ctx, base + '/dls-par-score-table/?o=50&r=250');
			eq(await printPages(par), 2, '50-over par sheet pages');
		}
	}
];
