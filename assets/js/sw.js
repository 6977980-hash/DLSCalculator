/*!
 * DLSCalc service worker, served at /sw.js by inc/pwa.php (which fills in VERSION and PAGES).
 * Keeps the calculators working with no signal at the ground:
 * - on install it saves the calculator pages and the theme files they use;
 * - pages: network first, saved copy when offline (any ?inputs open the saved page); pages sent
 *   as private or no-store (a logged-in user's) are never saved;
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
				return hit || caches.match('/');
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
