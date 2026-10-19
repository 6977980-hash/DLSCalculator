/*!
 * DLSCalc service worker, served at /sw.js by inc/pwa.php (which fills in VERSION, PAGES and LINKS).
 * Keeps the calculators working with no signal at the ground:
 * - on install it saves the calculator pages and the theme files they use;
 * - pages: network first, saved copy when offline (any ?inputs open the saved page); pages sent
 *   as private or no-store (a logged-in user's) are never saved; a page that was never saved gets a
 *   short "No signal" page listing the saved ones instead (audit F47);
 * - theme files with ?ver=: saved copy first; without one (fonts, icons, images): the saved copy at
 *   once, refreshed in the background, so a changed image shows on the next visit;
 * - one saved copy per file: a new ?ver= replaces the old one;
 * - VERSION changes whenever any theme file changes, so every deploy starts a fresh cache and
 *   activate deletes the old ones (audit F41);
 * - other sites (ads) and WordPress admin are left alone.
 */
'use strict';

var VERSION = '__VERSION__';
var PAGES = __PAGES__;
var LINKS = __LINKS__;
var CACHE = 'dlscalc-' + VERSION;
var THEME = '/wp-content/themes/dlscalc/';

/** Whether a response may be saved: a full, public answer from this site. */
function saveable(res) {
	return res.ok && res.type === 'basic' && !/no-store|private/i.test(res.headers.get('Cache-Control') || '');
}

/** Save a theme file, dropping the copies of the same file under other ?ver= values. */
function keep(cache, req, res) {
	return cache.delete(new URL(req.url).pathname, { ignoreSearch: true }).then(function () {
		return cache.put(req, res);
	});
}

/** "No signal" page for a page that is not saved, listing the saved pages that work offline (audit F47). */
function offlinePage() {
	var esc = function (s) {
		return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; });
	};
	return Promise.all(Object.keys(LINKS).map(function (url) {
		return caches.match(url).then(function (hit) {
			return hit ? '<li><a href="' + esc(url) + '">' + esc(LINKS[url]) + '</a></li>' : '';
		});
	})).then(function (items) {
		var html = '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' +
			'<meta name="robots" content="noindex"><title>No signal | DLS Calculator</title><style>' +
			'body{margin:0;font:17px/1.6 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:#f5f3ec;color:#10231c}' +
			'main{max-width:34rem;margin:0 auto;padding:32px 16px}h1{font-size:1.6rem;line-height:1.25;margin:0 0 12px}' +
			'ul{padding:0;list-style:none}li{margin:0 0 8px}a{display:block;padding:12px 14px;background:#fff;border:1px solid #dad6ca;border-radius:10px;color:#13452f;font-weight:600;text-decoration:none}' +
			'@media (prefers-color-scheme:dark){body{background:#101814;color:#e4ece6}a{background:#18231d;border-color:#2b3b31;color:#7fd3a0}}' +
			'</style></head><body><main><h1>No signal: this page is not saved on this phone yet</h1>' +
			'<p>It opens again once you are back online. These pages work offline and keep calculating with no connection:</p>' +
			'<ul>' + items.join('') + '</ul><p><a href="">Try again</a></p></main></body></html>';
		return new Response(html, { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8', 'Cache-Control': 'no-store' } });
	});
}

function assetsIn(html) {
	var out = [], re = /(?:src|href)=["']([^"']*\/wp-content\/themes\/dlscalc\/[^"']+)["']/g, m;
	while ((m = re.exec(html))) {
		out.push(m[1].replace(/&#0?38;|&amp;/g, '&'));
	}
	return out;
}

self.addEventListener('install', function (event) {
	event.waitUntil(caches.open(CACHE).then(function (cache) {
		return Promise.all(PAGES.map(function (url) {
			return fetch(url, { credentials: 'omit' }).then(function (res) {
				if (!saveable(res)) {
					return null;
				}
				return res.clone().text().then(function (html) {
					return cache.put(url, res).then(function () {
						return Promise.all(assetsIn(html).map(function (a) {
							return cache.add(a).catch(function () {});
						}));
					});
				});
			}).catch(function () {});
		}));
	}).then(function () {
		return self.skipWaiting();
	}));
});

self.addEventListener('activate', function (event) {
	event.waitUntil(caches.keys().then(function (keys) {
		return Promise.all(keys.filter(function (k) {
			return k.indexOf('dlscalc-') === 0 && k !== CACHE;
		}).map(function (k) {
			return caches.delete(k);
		}));
	}).then(function () {
		return self.clients.claim();
	}));
});

self.addEventListener('fetch', function (event) {
	var req = event.request, url = new URL(req.url);
	if (req.method !== 'GET' || url.origin !== location.origin || /^\/(wp-admin|wp-login|wp-json)|preview=/.test(url.pathname + url.search)) {
		return;
	}
	if (req.mode === 'navigate') {
		event.respondWith(fetch(req).then(function (res) {
			if (!url.search && saveable(res)) {
				var copy = res.clone();
				caches.open(CACHE).then(function (cache) { cache.put(url.pathname, copy); });
			}
			return res;
		}).catch(function () {
			return caches.match(url.pathname, { ignoreSearch: true }).then(function (hit) {
				return hit || offlinePage();
			});
		}));
		return;
	}
	if (url.pathname.indexOf(THEME) === 0) {
		event.respondWith(caches.match(req).then(function (hit) {
			var fresh = function () {
				return fetch(req).then(function (res) {
					if (saveable(res)) {
						var copy = res.clone();
						caches.open(CACHE).then(function (cache) { return keep(cache, req, copy); });
					}
					return res;
				});
			};
			if (!hit) {
				return fresh();
			}
			if (!url.search) {
				event.waitUntil(fresh().catch(function () {}));
			}
			return hit;
		}));
	}
});
