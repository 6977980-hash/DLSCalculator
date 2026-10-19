/*!
 * DLSCalc DLS tools: live results as you type, stoppage rows, result permalinks and sharing.
 * The page is already rendered by PHP from the same engine; this only keeps it in step.
 */
(function () {
	'use strict';
	var DLS = window.DLS;
	var form = document.querySelector('form[data-tool]');
	if (!DLS || !form) {
		return;
	}
	var tool = form.getAttribute('data-tool');
	var $ = function (sel, root) { return (root || document).querySelector(sel); };
	var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
	var out = function (key) { return $('[data-out="' + key + '"]'); };
	var esc = function (s) {
		return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; });
	};
	var text = function (key, value) {
		var el = out(key);
		if (el) {
			el.textContent = value;
		}
	};
	var show = function (el, on) {
		if (el) {
			el.hidden = !on;
		}
	};

	// Stoppage rows: drop the spare no-JS row, then add and remove rows on demand.
	$$('[data-stops]').forEach(function (box) {
		var spare = $('.stop--spare', box);
		var blank = spare.cloneNode(true);
		blank.classList.remove('stop--spare');
		box.removeChild(spare);
		var renumber = function () {
			$$('.stop', box).forEach(function (row, i) {
				$('.stop__n', row).textContent = String(i + 1);
			});
			var add = box.parentNode.querySelector('[data-add]');
			if (add) {
				add.hidden = $$('.stop', box).length >= DLS.STOPS_MAX;
			}
		};
		var wire = function (row) {
			var x = $('[data-remove]', row);
			x.hidden = false;
			x.addEventListener('click', function () {
				box.removeChild(row);
				renumber();
				update();
			});
		};
		$$('.stop', box).forEach(wire);
		var add = box.parentNode.querySelector('[data-add]');
		if (add) {
			add.addEventListener('click', function () {
				var row = blank.cloneNode(true);
				box.appendChild(row);
				wire(row);
				renumber();
				$('input', row).focus();
			});
		}
		renumber();
	});

	var stopsOf = function (key) {
		var box = $('[data-stops="' + key + '"]');
		if (!box) {
			return '';
		}
		return $$('.stop', box).map(function (row) {
			return ['at', 'w', 'lost'].map(function (f) { return $('[data-f="' + f + '"]', row).value.trim(); });
		}).filter(function (v) { return v.join('') !== ''; }).map(function (v) { return v.join('-'); }).join('_');
	};

	// Inputs as permalink parameters, in a fixed order so links read the same every time.
	var params = function () {
		var p = {};
		['n', 'r', 'g', 'c', 'l', 'o'].forEach(function (k) {
			var el = form.elements[k];
			if (el) {
				p[k] = String(el.value).trim();
			}
		});
		if ($('[data-stops="a"]')) {
			p.a = stopsOf('a');
		}
		if ($('[data-stops="b"]')) {
			p.b = stopsOf('b');
		}
		return p;
	};

	var query = function (p) {
		return Object.keys(p).filter(function (k) { return p[k] !== ''; }).map(function (k) {
			return k + '=' + encodeURIComponent(p[k]);
		}).join('&');
	};

	var engine = { calc: DLS.calc, quick: DLS.calc, t20: DLS.t20, parTable: DLS.parTable };

	var rowsHtml = function (rows) {
		return rows.map(function (row) {
			return '<tr><th scope="row">' + row.over + '</th>' + row.cells.map(function (c) { return '<td>' + c + '</td>'; }).join('') + '</tr>';
		}).join('');
	};

	var barsHtml = function (r, cur) {
		var max = r.S + 2;
		return r.bars.map(function (b) {
			return '<div class="bar' + (b.overs === cur ? ' bar--on' : '') + '"><span class="bar__v">' + b.target + '</span><i style="height:' +
				Math.max(8, Math.round(150 * b.target / max)) + 'px"></i><span class="bar__l">' + b.overs + '</span></div>';
		}).join('');
	};

	var shareText = function (r) {
		var what = tool === 't20' ? 'T20 DLS' : 'DLS';
		return r.ended ? what + ' par score: ' + r.par + '. Full working:' :
			what + ' target: ' + r.target + ' in ' + DLS.overs(r.allot) + ' overs (par ' + r.par + '). Full working:';
	};

	var first = true;
	function update() {
		var p = params();

		if (tool === 't20') {
			var no = parseInt(p.n, 10), slider = form.elements.l;
			if (no >= 5 && no <= 20) {
				slider.max = String(no - 5);
			}
			var lost = Math.min(parseInt(slider.value, 10) || 0, parseInt(slider.max, 10));
			text('lostlabel', lost + (lost === 1 ? ' over' : ' overs'));
			text('mlabel', no >= 5 && no <= 20 ? String(no - lost) : '–');
		}

		var r = engine[tool](p);
		var err = r.err || '';
		text('err', err);
		show(out('err'), !!err);
		text('warn', err ? '' : r.warn);
		show(out('warn'), !err && !!r.warn);

		if (tool === 'quick') {
			text('big', err ? '–' : String(r.target));
			text('line', err ? err : 'in ' + DLS.overs(r.allot) + ' overs (par ' + r.par + ')');
			return;
		}

		text('label', !err && r.ended ? 'Match ended: Team 2 par score' : 'Team 2 revised target');
		text('big', err ? '–' : String(r.ended ? r.par : r.target));
		text('line', err ? 'Check the inputs.' : (r.ended ? 'Team 2 wins with more than ' + r.par + ' and ties on ' + r.par + '.' : 'to win in ' + DLS.overs(r.allot) + ' overs'));
		text('tie', err ? '–' : String(r.par));
		text('t2overs', err ? '–' : DLS.overs(r.allot));
		text('r1', err ? '–' : r.R1.toFixed(1) + '%');
		text('r2', err ? '–' : r.R2.toFixed(1) + '%');
		text('rr', err || !r.rr ? '–' : r.rr);

		var steps = out('steps');
		if (steps) {
			steps.innerHTML = (err ? ['Fix the inputs above to see the working.'] : r.steps).map(function (s) { return '<li>' + esc(s) + '</li>'; }).join('');
		}
		var rows = out('rows');
		if (rows) {
			rows.innerHTML = err ? '' : rowsHtml(r.rows);
		}
		var note = out('tablenote');
		if (note) {
			note.textContent = !err && r.rows.length ? note.getAttribute('data-on') : note.getAttribute('data-off');
		}
		var bars = out('bars');
		if (bars && !err) {
			bars.innerHTML = barsHtml(r, r.allot / 6 === Math.floor(r.allot / 6) && !p.b ? r.allot / 6 : 0);
		}
		$$('[data-o]').forEach(function (chip) {
			chip.setAttribute('aria-current', chip.getAttribute('data-o') === p.o ? 'true' : 'false');
		});

		// Result permalink: the address bar always reproduces what is on screen.
		if (!first) {
			var q = query(p);
			history.replaceState(null, '', location.pathname + (q ? '?' + q : '') + '#result');
		}
		var wa = out('wa');
		if (wa) {
			wa.href = 'https://wa.me/?text=' + encodeURIComponent((err ? 'DLS calculator:' : shareText(r)) + ' ' + location.origin + location.pathname + '?' + query(p));
		}
	}

	// Format and G50 pickers fill the plain inputs that carry the values.
	var preset = $('[data-preset]', form);
	if (preset) {
		preset.addEventListener('change', function () {
			if (preset.value !== 'custom') {
				form.elements.n.value = preset.value;
				form.elements.c.value = preset.value;
			}
		});
		form.elements.n.addEventListener('input', function () {
			preset.value = ['50', '20', '10'].indexOf(form.elements.n.value.trim()) > -1 ? form.elements.n.value.trim() : 'custom';
		});
	}
	var gsel = $('[data-gsel]', form);
	if (gsel) {
		gsel.addEventListener('change', function () {
			show($('[data-gwrap]', form), gsel.value === 'custom');
			if (gsel.value !== 'custom') {
				form.elements.g.value = gsel.value;
			} else {
				form.elements.g.focus();
			}
		});
	}
	var quickN = tool === 'quick' && form.elements.n;
	if (quickN) {
		quickN.addEventListener('change', function () {
			form.elements.c.value = quickN.value === '50' ? '40' : '15';
		});
	}
	$$('[data-o]').forEach(function (chip) {
		chip.addEventListener('click', function (e) {
			e.preventDefault();
			form.elements.o.value = chip.getAttribute('data-o');
			update();
		});
	});

	form.addEventListener('input', update);
	form.addEventListener('change', update);
	if (tool !== 'quick') {
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			update();
		});
		show($('[data-submit]', form), false);
	}

	var copy = $('[data-copy]');
	if (copy && navigator.clipboard) {
		copy.hidden = false;
		copy.addEventListener('click', function () {
			var q = query(params());
			navigator.clipboard.writeText(location.origin + location.pathname + (q ? '?' + q : '')).then(function () {
				copy.textContent = 'Link copied';
				setTimeout(function () { copy.textContent = 'Copy link'; }, 2000);
			});
		});
	}
	$$('[data-print]').forEach(function (print) {
		print.hidden = false;
		print.addEventListener('click', function () { window.print(); });
	});

	update();
	first = false;
}());
