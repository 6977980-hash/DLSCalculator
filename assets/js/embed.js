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
		var p = { n: form.n.value, r: form.r.value, c: form.c.value, g: '245' };
		form.c.placeholder = p.n;
		var r = window.DLS.calc(p);
		out('err').hidden = !r.err;
		out('res').hidden = !!r.err;
		if (r.err) {
			out('err').textContent = r.err;
			return;
		}
		out('target').textContent = r.target;
		out('line').textContent = 'in ' + window.DLS.overs(r.allot) + ' overs · par to tie ' + r.par;
		var q = ['n', 'r', 'g', 'c'].filter(function (k) { return String(p[k]).trim() !== ''; }).map(function (k) {
			return k + '=' + encodeURIComponent(String(p[k]).trim());
		}).join('&');
		out('full').href = full + '?' + q;
	}
	form.addEventListener('input', update);
	form.addEventListener('submit', function (e) { e.preventDefault(); update(); });
}());
