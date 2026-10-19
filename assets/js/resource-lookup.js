/*!
 * DLSCalc resource lookup on the DLS method guide: overs left and wickets lost to the resource
 * percentage, as the visitor types. Same text as dlscalc_dls_lookup() in inc/dls-ui.php.
 */
(function () {
	'use strict';
	var DLS = window.DLS;
	var form = document.querySelector('form[data-lookup]');
	if (!DLS || !form) {
		return;
	}
	var out = function (key) { return form.querySelector('[data-lk="' + key + '"]'); };

	function lookup(v, d) {
		var b = DLS.balls(v), w = /^\d$/.test(d) ? parseInt(d, 10) : NaN;
		if (isNaN(b)) {
			return { err: String(v).trim() === '' ? 'Enter the overs left, for example 32.4.' : DLS.oversHelp('Overs left', v) };
		}
		if (b < 1 || b > 300 || isNaN(w)) {
			return { err: 'Overs left must be between 0.1 and 50, and wickets lost 0 to 9.' };
		}
		return {
			err: '',
			pct: DLS.res(b, w).toFixed(1) + '%',
			text: 'of resources left with ' + DLS.overs(b) + (b === 6 ? ' over' : ' overs') + ' left and ' + w + (w === 1 ? ' wicket' : ' wickets') + ' lost.'
		};
	}

	function update() {
		var r = lookup(form.elements.v.value, form.elements.d.value);
		out('err').textContent = r.err;
		out('err').hidden = !r.err;
		out('pct').textContent = r.err ? '' : r.pct;
		out('text').textContent = r.err ? '' : r.text;
		if (r.err) {
			form.elements.v.setAttribute('aria-invalid', 'true');
		} else {
			form.elements.v.removeAttribute('aria-invalid');
		}
	}

	form.addEventListener('input', update);
	form.addEventListener('change', update);
	form.addEventListener('submit', function (e) {
		e.preventDefault();
		update();
	});
}());
