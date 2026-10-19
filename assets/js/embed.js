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
		out('line').textContent = 'in ' + window.DLS.overs(r.allot) + ' overs · par to tie ' + r.par;
		var q = ['f', 'n', 'r', 'g', 'c'].filter(function (k) { return String(p[k]).trim() !== ''; }).map(function (k) {
			return k + '=' + encodeURIComponent(String(p[k]).trim());
		}).join('&');
		out('full').href = full + '?' + q;
	}
	form.addEventListener('input', update);
	form.addEventListener('change', update);
	form.addEventListener('submit', function (e) { e.preventDefault(); update(); });
}());
