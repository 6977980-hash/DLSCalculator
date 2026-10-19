// Draws the SVG charts used in the DLS page articles from the engine itself, so the pictures
// always match the calculator. Run from the theme folder: node dev/make-charts.mjs
import { createRequire } from 'node:module';
import { writeFileSync } from 'node:fs';
import { resolve } from 'node:path';

const root = resolve(import.meta.dirname, '..');
const DLS = createRequire(import.meta.url)(`${root}/assets/js/dls-engine.js`);
const W = 760, H = 440, L = 64, R = 130, T = 56, B = 56;
const colors = ['#13452F', '#2E7D57', '#C99A2E', '#C8372D', '#6B4E9B'];
const font = 'font-family="Segoe UI, Helvetica, Arial, sans-serif"';

function chart({ title, xLabel, yLabel, xMax, yMax, yStep, xStep, series, reverseX }) {
  const px = (x) => L + ((reverseX ? xMax - x : x) / xMax) * (W - L - R);
  const py = (y) => H - B - (y / yMax) * (H - T - B);
  let s = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H}" width="${W}" height="${H}" ${font}>`;
  s += `<rect width="${W}" height="${H}" fill="#fff"/>`;
  s += `<text x="${L}" y="30" font-size="20" font-weight="700" fill="#10231C">${title}</text>`;
  for (let y = 0; y <= yMax; y += yStep) {
    s += `<line x1="${L}" x2="${W - R}" y1="${py(y)}" y2="${py(y)}" stroke="#E6E2D6"/>`;
    s += `<text x="${L - 8}" y="${py(y) + 5}" font-size="14" text-anchor="end" fill="#55605A">${y}</text>`;
  }
  for (let x = 0; x <= xMax; x += xStep) {
    s += `<text x="${px(x)}" y="${H - B + 22}" font-size="14" text-anchor="middle" fill="#55605A">${x}</text>`;
  }
  s += `<text x="${(L + W - R) / 2}" y="${H - 10}" font-size="15" text-anchor="middle" fill="#33403A">${xLabel}</text>`;
  s += `<text transform="translate(18 ${(T + H - B) / 2}) rotate(-90)" font-size="15" text-anchor="middle" fill="#33403A">${yLabel}</text>`;
  series.forEach((ser, i) => {
    const d = ser.points.map(([x, y], k) => `${k ? 'L' : 'M'}${px(x).toFixed(1)} ${py(y).toFixed(1)}`).join('');
    s += `<path d="${d}" fill="none" stroke="${colors[i]}" stroke-width="3" stroke-linejoin="round"/>`;
    const [lx, ly] = ser.points[ser.points.length - 1];
    s += `<text x="${px(lx) + 8}" y="${py(ly) + 5 + (ser.dy || 0)}" font-size="14" font-weight="700" fill="${colors[i]}">${ser.name}</text>`;
  });
  return s + '</svg>\n';
}

// Resources remaining by overs left, for a few wicket counts.
writeFileSync(`${root}/assets/img/dls/resource-curves.svg`, chart({
  title: 'DLS resources remaining (ICC Standard Edition)',
  xLabel: 'Overs left', yLabel: 'Resources remaining (%)',
  xMax: 50, yMax: 100, yStep: 20, xStep: 10,
  series: [0, 2, 4, 6, 8].map((w) => ({
    name: `${w} wkts lost`,
    points: Array.from({ length: 51 }, (_, o) => [o, DLS.res(o * 6, w)]),
  })),
}));

// Par through a 20-over chase of 160.
const par = DLS.parTable({ o: '20', s: '160' });
writeFileSync(`${root}/assets/img/dls/t20-par-160.svg`, chart({
  title: 'DLS par score, 20-over chase of 160',
  xLabel: 'Overs completed', yLabel: 'Par score',
  xMax: 20, yMax: 160, yStep: 40, xStep: 5,
  series: [0, 3, 6].map((w) => ({
    name: `${w} down`,
    dy: w === 0 ? 10 : (w === 3 ? 0 : -6),
    points: [[0, 0]].concat(par.rows.map((r) => [r.over, r.cells[w]])),
  })),
}));
console.log('Charts written to assets/img/dls/');
