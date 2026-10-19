// Regenerates the theme's icons and Open Graph images.
// Run from the theme folder: node dev/make-images.mjs   (needs Playwright + Chromium, and python3 with Pillow for favicon.ico)
import { createRequire } from 'node:module';
import { readFileSync, writeFileSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { resolve } from 'node:path';

const root = resolve(import.meta.dirname, '..');
const { chromium } = createRequire(process.env.NODE_PATH ? `${process.env.NODE_PATH}/` : import.meta.url)('playwright');
const img = (p) => `${root}/assets/img/${p}`;
const font = (f) => `data:font/woff2;base64,${readFileSync(`${root}/assets/fonts/${f}`).toString('base64')}`;
const fonts = `@font-face{font-family:BC;font-weight:700;src:url(${font('barlow-condensed-700.woff2')})}
@font-face{font-family:SS;font-weight:400 700;src:url(${font('source-sans-3-var.woff2')})}`;

const ball = (size, stroke = 2) => `<svg width="${size}" height="${size}" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg"><circle cx="15" cy="15" r="13" fill="#C8372D" stroke="#F5F3EC" stroke-width="${stroke}"/><path d="M9 5c3 6 3 14 0 20M21 5c-3 6-3 14 0 20" fill="none" stroke="#F5F3EC" stroke-width="1.5" stroke-dasharray="2 2"/></svg>`;

// Square icon: ball on green. pad = share of the canvas left around the ball.
const icon = (size, pad) => `<div style="width:${size}px;height:${size}px;background:#13452F;display:flex;align-items:center;justify-content:center">${ball(Math.round(size * (1 - pad * 2)), 1.6)}</div>`;

// Pages that get their own share image: slug => [kicker, title, line].
const pages = {
  default: ['Free cricket calculators', 'Rain stopped play? Get the DLS target in seconds.', 'DLS targets, par scores and net run rate'],
  'dls-calculator': ['DLS Calculator', 'DLS Calculator: revised target and par score', 'ICC Standard Edition · multiple interruptions · full working'],
  'dls-target-calculator-t20': ['T20', 'DLS Target Calculator for T20 matches', 'Rain-reduced targets and an over-by-over par table'],
  'dls-par-score-table': ['DLS Par Score', 'DLS par score table and calculator', 'Par for every over and wicket, 5 to 50 overs'],
  'net-run-rate-calculator': ['Net Run Rate', 'Net Run Rate Calculator (NRR)', 'Your NRR and the margin you need to qualify'],
  'run-rate-calculator': ['Run Rate', 'Run Rate Calculator: current and required', 'CRR, RRR, runs per ball and projected score'],
  'dls-method': ['Guide', 'The DLS method explained', 'Resource table, formula and ICC worked examples'],
  'how-to-calculate-nrr': ['Guide', 'How to calculate net run rate', 'The NRR formula with a step-by-step example'],
  about: ['About', 'About DLSCalc and Ch Alee', 'Cricketer and tournament organiser'],
};

const og = ([kicker, title, line]) => `<div style="width:1200px;height:630px;box-sizing:border-box;background:#13452F;color:#F5F3EC;padding:64px 72px;display:flex;flex-direction:column;justify-content:space-between;font-family:SS;position:relative;overflow:hidden">
<div style="position:absolute;right:-120px;bottom:-120px;opacity:.12">${ball(560, 1)}</div>
<div style="display:flex;align-items:center;gap:16px">${ball(56)}<span style="font-family:BC;font-size:48px">DLS<span style="color:#F2B33D">Calc</span></span></div>
<div style="position:relative">
<div style="color:#F2B33D;font-weight:700;letter-spacing:2px;text-transform:uppercase;font-size:26px;margin-bottom:14px">${kicker}</div>
<div style="font-family:BC;font-size:84px;line-height:1.02;max-width:960px">${title}</div>
<div style="font-size:32px;color:#D9E3DC;margin-top:20px">${line}</div>
</div>
<div style="font-size:26px;color:#C9D4CD;position:relative">dlscalculator.com</div>
</div>`;

const browser = await chromium.launch({ executablePath: process.env.CHROMIUM || undefined });
const page = await browser.newPage();
async function shot(html, w, h, out) {
  await page.setViewportSize({ width: w, height: h });
  await page.setContent(`<!doctype html><style>${fonts}body{margin:0}</style>${html}`);
  await page.evaluate(() => document.fonts.ready);
  await page.screenshot({ path: out, clip: { x: 0, y: 0, width: w, height: h } });
}

await shot(icon(180, 0.14), 180, 180, img('apple-touch-icon.png'));
await shot(icon(192, 0.1), 192, 192, img('icon-192.png'));
await shot(icon(512, 0.1), 512, 512, img('icon-512.png'));
await shot(icon(512, 0.2), 512, 512, img('icon-maskable-512.png'));
await shot(`<div style="width:64px;height:64px;display:flex;align-items:center;justify-content:center">${ball(64, 1.4)}</div>`, 64, 64, '/tmp/dlscalc-favicon-64.png');
for (const [slug, text] of Object.entries(pages)) {
  await shot(og(text), 1200, 630, img(`og/${slug}.png`));
}
await browser.close();

writeFileSync(img('favicon.svg'), ball(30, 1.4).replace(' width="30" height="30"', '') + '\n');
writeFileSync(img('logo.svg'), `<svg xmlns="http://www.w3.org/2000/svg" width="160" height="40" viewBox="0 0 160 40" role="img" aria-label="DLSCalc"><g transform="translate(2 5)">${ball(30).replace(/^<svg[^>]*>|<\/svg>$/g, '')}</g><text x="42" y="30" font-family="Barlow Condensed, Arial Narrow, sans-serif" font-weight="700" font-size="30" fill="#13452F">DLS<tspan fill="#C8372D">Calc</tspan></text></svg>\n`);

execFileSync('python3', ['-I', '-c', `from PIL import Image; im=Image.open(${JSON.stringify('/tmp/dlscalc-favicon-64.png')}).convert('RGBA'); im.save(${JSON.stringify(img('favicon.ico'))}, sizes=[(16,16),(32,32),(48,48)])`]);
console.log('done');
