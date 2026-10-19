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

	// A result link opened with no signal gets the page the service worker saved without inputs
	// (data-plain), so fill the form from the address first, the way the server reads the link
	// online (audit F46): a link means only what it carries, so anything left out is blank (no
	// stoppages, no overs lost, a missing score is an error) and only G50 keeps its 245 (audit F69).
	if (form.hasAttribute('data-plain') && window.URLSearchParams) {
		var linkQ = new URLSearchParams(location.search);
		var linkKeys = ['n', 'r', 'g', 'c', 'l', 'o', 'f', 'k', 'a', 'b'];
		if (linkKeys.some(function (k) { return linkQ.has(k); })) {
			linkKeys.forEach(function (k) {
				var v = linkQ.has(k) ? linkQ.get(k).trim().slice(0, 120) : (k === 'g' ? null : (k === 'l' ? '0' : ''));
				var box = $('[data-stops="' + k + '"]', form), el = form.elements[k];
				if (v === null) {
					return;
				}
				if (box) {
					var spare = $('.stop--spare', box);
					$$('.stop:not(.stop--spare)', box).forEach(function (row) { box.removeChild(row); });
					v.split('_').filter(function (part) { return part.replace(/-/g, '').trim() !== ''; }).forEach(function (part) {
						var row = spare.cloneNode(true), x = part.split('-');
						row.classList.remove('stop--spare');
						['at', 'w', 'lost'].forEach(function (f, i) { $('[data-f="' + f + '"]', row).value = x[i] || ''; });
						box.insertBefore(row, spare);
					});
				} else if (el && el.tagName === 'SELECT' && k === 'f') {
					// Links without a format go by the overs, as on the server.
					var known = v !== '' && $$('option', el).some(function (o) { return o.value === v; });
					el.value = known ? v : ({ 50: 'odi', 20: 't20', 10: 't10' }[form.elements.n.value.trim()] || 'custom');
				} else if (el && el.tagName) {
					el.value = v;
				}
			});
			var gs = $('[data-gsel]', form);
			if (gs) {
				gs.value = ['245', '200'].indexOf(form.elements.g.value) > -1 ? form.elements.g.value : 'custom';
				show($('[data-gwrap]', form), gs.value === 'custom');
			}
		}
	}

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
			$('[data-remove]', row).addEventListener('click', function () {
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
		['n', 'r', 'g', 'c', 'l', 'o', 'f', 'k'].forEach(function (k) {
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

	// The input an error belongs to: "n", "c" and the like, or "b-2-at" for the second Team 2
	// stoppage that has anything typed in it (blank rows are skipped, as in the permalink).
	var fieldEl = function (field) {
		var x = field.split('-');
		if (x.length === 1) {
			var el = form.elements[field];
			return el && el.tagName ? el : null;
		}
		var rows = $$('.stop', $('[data-stops="' + x[0] + '"]') || form).filter(function (row) {
			return $$('input', row).some(function (i) { return i.value.trim() !== ''; });
		});
		return rows[x[1] - 1] ? $('[data-f="' + x[2] + '"]', rows[x[1] - 1]) : null;
	};

	// Error message under the input it belongs to (audit F19); the result panel shows it too.
	var markField = function (r) {
		$$('[aria-invalid]', form).forEach(function (el) {
			el.removeAttribute('aria-invalid');
			el.removeAttribute('aria-describedby');
		});
		$$('.fld__err', form).forEach(function (el) { el.parentNode.removeChild(el); });
		var el = r.err && r.field ? fieldEl(r.field) : null;
		if (!el || el.offsetParent === null) {
			return;
		}
		var msg = document.createElement('span');
		msg.className = 'fld__err';
		msg.id = 'fe-' + r.field;
		msg.textContent = r.err;
		el.parentNode.appendChild(msg);
		el.setAttribute('aria-invalid', 'true');
		el.setAttribute('aria-describedby', msg.id);
	};

	// Scenario buttons: mark the one whose inputs are on screen.
	var scenarios = $$('.scenarios .chip');
	var markScenario = function (p) {
		scenarios.forEach(function (chip) {
			var q = new URLSearchParams(chip.getAttribute('href').split('?')[1] || '');
			var on = ['n', 'r', 'g', 'c', 'a', 'b', 'k'].every(function (k) { return (q.get(k) || '') === (p[k] || ''); });
			chip.setAttribute('aria-current', on ? 'true' : 'false');
		});
	};

	// On a phone the result panel sits below the whole form, so typing changes nothing on screen. A
	// slim bar at the bottom repeats the answer while the panel is below the screen (audit F51).
	var panel = document.getElementById('result'), bar = null, below = false;
	var placeBar = function () {
		// Only when the panel is stacked under the form (phones), not beside it (wide screens).
		var stacked = panel.getBoundingClientRect().top >= form.getBoundingClientRect().bottom - 1;
		bar.hidden = !(below && stacked);
		document.documentElement.classList.toggle('has-stick', !bar.hidden);
	};
	if ((tool === 'calc' || tool === 't20') && panel && window.IntersectionObserver) {
		bar = document.createElement('a');
		bar.className = 'stick noprint';
		bar.href = '#result';
		bar.hidden = true;
		document.body.appendChild(bar);
		new IntersectionObserver(function (entries) {
			var e = entries[entries.length - 1];
			below = !e.isIntersecting && e.boundingClientRect.top > 0;
			placeBar();
		}).observe(panel);
		window.addEventListener('resize', placeBar);
	}
	var barText = function (r) {
		if (r.err) {
			return '<span>Check the inputs</span><span class="stick__more">See why ↓</span>';
		}
		return '<span>' + (r.ended ? 'Par score <b>' + r.par + '</b>' : 'Target <b>' + r.target + '</b> in ' + DLS.overs(r.allot) + ' overs') +
			'</span><span class="stick__more">See working ↓</span>';
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
			// Fewer overs per side lowers the slider's top end. A link with more overs lost than the
			// slider allows keeps its value on first load, so it shows the same error as the server (audit F39).
			var linked = first && window.URLSearchParams ? new URLSearchParams(location.search).get('l') : null;
			p.l = linked !== null ? linked : String(lost);
			text('lostlabel', lost + (lost === 1 ? ' over' : ' overs'));
			text('mlabel', no >= 5 && no <= 20 ? String(no - lost) : '–');
		}

		var r = engine[tool](p);
		var err = r.err || '';
		markField(r);
		markScenario(p);
		text('err', err);
		show(out('err'), !!err);
		text('warn', err ? '' : r.warn);
		show(out('warn'), !err && !!r.warn);

		if (tool === 'quick') {
			text('big', err ? '–' : String(r.target));
			text('line', err ? err : 'in ' + DLS.overs(r.allot) + ' overs (par ' + r.par + ')');
			return;
		}

		if (bar) {
			bar.innerHTML = barText(r);
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

	// Rain before the start: a shorter match typed in the overs per side carries to Team 2's overs
	// while they still equal the old value, as the format picker does (audit F48).
	var side = form.elements.c ? form.elements[tool === 'parTable' ? 'o' : 'n'] : null, lastSide = side ? side.value.trim() : '';
	var carry = function (to) {
		if (form.elements.c.value.trim() === lastSide) {
			form.elements.c.value = to;
		}
		form.elements.c.placeholder = tool === 'calc' ? to : form.elements.c.placeholder;
		lastSide = to;
	};
	if (side && tool !== 'quick') {
		side.addEventListener('input', function () { carry(side.value.trim()); });
	}

	// The format picker fills in its usual overs, unless overs a rain-shortened match started with
	// are already typed (a 40-over ODI keeps 40). The format itself sets the minimum overs.
	var preset = $('[data-preset]', form);
	if (preset) {
		preset.addEventListener('change', function () {
			var to = preset.options[preset.selectedIndex].getAttribute('data-overs'), now = form.elements.n.value.trim();
			if (to && ['', '50', '20', '10'].indexOf(now) > -1) {
				form.elements.n.value = to;
				form.elements.c.value = to;
				lastSide = to;
			}
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
			if (side) {
				carry(form.elements.o.value);
			}
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
	}

	var copy = $('[data-copy]');
	if (copy && !navigator.clipboard) {
		copy.hidden = true;
	} else if (copy) {
		copy.addEventListener('click', function () {
			var q = query(params());
			navigator.clipboard.writeText(location.origin + location.pathname + (q ? '?' + q : '')).then(function () {
				copy.textContent = 'Link copied';
				setTimeout(function () { copy.textContent = 'Copy link'; }, 2000);
			});
		});
	}
	$$('[data-print]').forEach(function (print) {
		print.addEventListener('click', function () { window.print(); });
	});
	$$('[data-copy-embed]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			navigator.clipboard.writeText($('[data-embed-code]').value).then(function () {
				btn.textContent = 'Code copied';
				setTimeout(function () { btn.textContent = 'Copy code'; }, 2000);
			});
		});
	});
	// The printed par sheet ends with the link that reopens this result.
	window.addEventListener('beforeprint', function () {
		$$('[data-sheet-url]').forEach(function (el) { el.textContent = location.href.replace(/#.*$/, ''); });
	});

	update();
	first = false;
}());
