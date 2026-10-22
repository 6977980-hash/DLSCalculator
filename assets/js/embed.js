/*!
 * DLSCalc embeddable widget (/embed/dls/): live target as the user types, using the DLS engine.
 */
(function () {
	'use strict';
	var form = document.querySelector('[data-embed]');
	if (!form || !window.DLS) {
		return;
	}
	var full = document.currentScript.getAttribute('data-full');
	var out = function (k) { return document.querySelector('[data-o="' + k + '"]'); };

	function update() {
		var p = { f: form.f.value, n: form.n.value, r: form.r.value, c: form.c.value, g: '245' };
		form.c.placeholder = p.n;
		var r = window.DLS.calc(p);
		out('err').hidden = !r.err;
		out('res').hidden = !!r.err;
		out('warn').hidden = !!r.err || !r.warn;
		// The minimum-overs warning in a few words, as dlscalc_embed_warn() does, so the widget fits its iframe.
		out('warn').textContent = r.err ? '' : r.warn.replace(/^Team 2 (?:can face|faced) fewer than (\S+) overs.*$/, 'Fewer than $1 overs for Team 2: no result under ICC rules.');
		if (r.err) {
			out('err').textContent = r.err;
			return;
		}
		out('target').textContent = r.target;
		out('line').textContent = 'to win in ' + window.DLS.overs(r.allot) + ' overs; ' + r.par + ' ties';
		var q = ['f', 'n', 'r', 'g', 'c'].filter(function (k) { return String(p[k]).trim() !== ''; }).map(function (k) {
			return k + '=' + encodeURIComponent(String(p[k]).trim());
		}).join('&');
		out('full').href = full + '?' + q;
	}
	// The format fills in its usual overs, unless a rain-shortened start is already typed (a 40-over
	// ODI keeps 40), and Team 2's overs follow the overs per side while they still match, as on the
	// full calculator; otherwise ODI with 20 overs gave a 20-over target (audit F71).
	var usual = { odi: '50', t20: '20', t20ko: '20', t10: '10' }, lastN = form.n.value.trim();
	var carry = function (to) {
		var c = form.c.value.trim();
		if (c === '' || c === lastN) {
			form.c.value = c === '' ? '' : to;
		}
		lastN = to;
	};
	form.f.addEventListener('change', function () {
		var to = usual[form.f.value], now = form.n.value.trim();
		if (to && ['', '50', '20', '10'].indexOf(now) > -1) {
			form.n.value = to;
			carry(to);
		}
	});
	form.n.addEventListener('input', function () { carry(form.n.value.trim()); });
	form.addEventListener('input', update);
	form.addEventListener('change', update);
	form.addEventListener('submit', function (e) { e.preventDefault(); update(); });
}());
