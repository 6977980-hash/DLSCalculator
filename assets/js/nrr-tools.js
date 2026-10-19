/*!
 * DLSCalc NRR tools: live results as you type on the net run rate, run rate and stats pages,
 * match and team rows, the points table saved on this device, result permalinks and sharing.
 * The page is already rendered by PHP from the same engine; this only keeps it in step.
 */
(function () {
	'use strict';
	var NRR = window.NRR;
	if (!NRR) {
		return;
	}
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
	var stepsHtml = function (r) {
		return (r.err ? ['Fix the inputs above to see the working.'] : r.steps).map(function (s) { return '<li>' + esc(s) + '</li>'; }).join('');
	};
	var query = function (p) {
		return Object.keys(p).filter(function (k) { return p[k] !== ''; }).map(function (k) {
			return k + '=' + encodeURIComponent(p[k]);
		}).join('&');
	};
	var link = function (p) {
		var q = query(p);
		return location.origin + location.pathname + (q ? '?' + q : '');
	};
	var store = {
		get: function (key) {
			try {
				return window.localStorage.getItem(key);
			} catch (e) {
				return null;
			}
		},
		set: function (key, value) {
			try {
				window.localStorage.setItem(key, value);
			} catch (e) { /* private mode: the permalink still works */ }
		}
	};

	/**
	 * A result link opened with no signal gets the page the service worker saved without inputs
	 * (data-plain), so the form is filled from the address first, the way the server reads the link
	 * online (audit F46). Returns a lookup of the address values (null when not in the link), or
	 * null when there is nothing to fill.
	 */
	function linked(form, keys) {
		if (!form.hasAttribute('data-plain') || !window.URLSearchParams) {
			return null;
		}
		var q = new URLSearchParams(location.search);
		if (!keys.some(function (k) { return q.has(k); })) {
			return null;
		}
		return function (k) {
			return q.has(k) ? q.get(k).trim().slice(0, 2000) : null;
		};
	}

	/** Puts a link value in a text field, select or radio group (unknown radio values pick the first, as the server does). */
	function setField(form, name, value) {
		var el = form.elements[name];
		if (!el || value === null) {
			return;
		}
		if (!el.tagName) {
			var radios = Array.prototype.slice.call(el);
			(radios.filter(function (r) { return r.value === value; })[0] || radios[0]).checked = true;
		} else if (el.tagName !== 'SELECT' || $$('option', el).some(function (o) { return o.value === value; })) {
			el.value = value;
		}
	}

	/** Replaces the filled rows before the spare one with one row per item, filled by fill(row, item). */
	function fillRows(box, items, fill) {
		var spare = $('.mrow--spare, .trow--spare', box);
		$$(':scope > *', box).forEach(function (row) {
			if (row !== spare) {
				box.removeChild(row);
			}
		});
		items.forEach(function (item) {
			var row = spare.cloneNode(true);
			row.classList.remove('mrow--spare', 'trow--spare');
			fill(row, item);
			box.insertBefore(row, spare);
		});
	}

	var put = function (row, f, value) {
		var el = $('[data-f="' + f + '"]', row);
		if (el.type === 'checkbox') {
			el.checked = !!value;
		} else {
			el.value = value;
		}
	};

	/**
	 * Rows that can be added and removed: drop the spare no-JS row, keep its markup for new rows.
	 * onChange runs after any add or remove.
	 */
	function rows(box, max, onChange) {
		var spare = $('.mrow--spare, .trow--spare', box);
		var blank = spare.cloneNode(true);
		blank.classList.remove('mrow--spare', 'trow--spare');
		box.removeChild(spare);
		var add = box.closest('form').querySelector('[data-add]');
		var renumber = function () {
			$$('.mrow__n', box).forEach(function (n, i) {
				n.textContent = String(i + 1);
			});
			add.hidden = box.children.length >= max;
		};
		var wire = function (row) {
			$('[data-remove]', row).addEventListener('click', function () {
				box.removeChild(row);
				renumber();
				onChange();
			});
		};
		$$(':scope > *', box).forEach(wire);
		add.addEventListener('click', function () {
			var row = blank.cloneNode(true);
			box.appendChild(row);
			wire(row);
			renumber();
			onChange();
			$('input', row).focus();
		});
		renumber();
	}

	var field = function (row, f) {
		var el = $('[data-f="' + f + '"]', row);
		return el.type === 'checkbox' ? el.checked : el.value.trim();
	};

	function shareWire(params) {
		$$('[data-copy]').forEach(function (btn) {
			if (!navigator.clipboard) {
				btn.hidden = true;
				return;
			}
			btn.addEventListener('click', function () {
				navigator.clipboard.writeText(link(params())).then(function () {
					btn.textContent = 'Link copied';
					setTimeout(function () { btn.textContent = 'Copy link'; }, 2000);
				});
			});
		});
		$$('[data-print]').forEach(function (btn) {
			btn.addEventListener('click', function () { window.print(); });
		});
	}

	/* Net run rate page --------------------------------------------------------------------- */

	function nrrPage(calcForm, tableForm) {
		var KEY = 'dlscalc-nrr';
		// A return visit with no inputs in the address opens the table saved on this device.
		if (!location.search) {
			var saved = store.get(KEY);
			if (saved) {
				location.replace(location.pathname + '?' + saved + location.hash);
				return;
			}
		}
		var matchBox = $('[data-rows="x"]', calcForm), teamBox = $('[data-rows="t"]', tableForm);
		var tab = $$('[data-tab]').filter(function (b) { return b.getAttribute('aria-pressed') === 'true'; })[0].getAttribute('data-tab');

		var matches = function () {
			return $$('.mrow', matchBox).map(function (row) {
				// "-" and "_" separate fields and matches, so a typed minus travels as "−" and the engine can say "cannot be negative".
				var v = [field(row, 'rf'), field(row, 'of') + (field(row, 'fa') ? 'a' : ''), field(row, 'ra'), field(row, 'oa') + (field(row, 'aa') ? 'a' : '')].map(function (t) {
					return t.replace(/-/g, '−').replace(/_/g, '');
				});
				if ((v[0] + v[1] + v[2] + v[3]).replace(/a/g, '') === '') {
					return '';
				}
				var cut = field(row, 'mo').replace(/[-_]/g, '');
				return v.join('-') + (cut ? '-' + cut : '') + (field(row, 'nr') ? '-n' : '');
			}).filter(Boolean).join('_');
		};
		var teams = function () {
			return $$('.trow', teamBox).map(function (row) {
				var v = ['n', 'p', 'w', 'pts', 'rf', 'of', 'ra', 'oa'].map(function (f) {
					return f === 'n' ? NRR.cleanName(field(row, f)) : field(row, f).replace(/[~_]/g, '');
				});
				return v.join('') === '' ? '' : v.join('~');
			}).filter(Boolean).join('_');
		};
		var el = function (name) { return tableForm.elements[name]; };
		var params = function () {
			var k = $('[name="k"]:checked', tableForm);
			return {
				u: tab === 't' ? 't' : '', q: calcForm.elements.q.value.trim(), x: matches(), t: teams(),
				y: el('y').value, v: el('v').value, o: el('o').value, k: k ? k.value : 'f',
				e: el('e').value.trim(), h: el('h').value.trim(), d: el('d').value.trim(), z: el('z').value
			};
		};

		// Team pickers follow the names in the points table, keeping each choice by position.
		var syncPickers = function (list) {
			$$('[data-teams]', tableForm).forEach(function (sel) {
				var cur = sel.value, none = sel.getAttribute('data-teams');
				var opts = (none ? '<option value="0">' + esc(none) + '</option>' : '') + list.map(function (t, i) {
					return '<option value="' + (i + 1) + '">' + esc(t.name) + '</option>';
				}).join('');
				if (sel.innerHTML !== opts) {
					sel.innerHTML = opts;
					sel.value = cur;
					if (sel.selectedIndex < 0) {
						sel.selectedIndex = 0;
					}
				}
			});
		};

		var got = linked(calcForm, ['u', 'q', 'x', 't', 'y', 'v', 'o', 'k', 'e', 'h', 'd', 'z']);
		if (got) {
			setField(calcForm, 'q', got('q'));
			if (got('x') !== null) {
				fillRows(matchBox, got('x').split('_').filter(Boolean), function (row, part) {
					var x = part.split('-'), extra = x.slice(4), of = x[1] || '', oa = x[3] || '';
					put(row, 'rf', x[0] || '');
					put(row, 'of', of.replace(/a$/, ''));
					put(row, 'fa', /a$/.test(of));
					put(row, 'ra', x[2] || '');
					put(row, 'oa', oa.replace(/a$/, ''));
					put(row, 'aa', /a$/.test(oa));
					put(row, 'mo', extra.filter(function (e) { return e !== 'n' && e !== ''; })[0] || '');
					put(row, 'nr', extra.indexOf('n') > -1);
				});
			}
			if (got('t') !== null) {
				fillRows(teamBox, NRR.parseTeams(got('t')), function (row, t) {
					put(row, 'n', t.name);
					Object.keys(t.raw).forEach(function (f) { put(row, f, t.raw[f]); });
				});
			}
			syncPickers(NRR.parseTeams(teams()));
			['y', 'v', 'o', 'k', 'e', 'h', 'd', 'z'].forEach(function (k) { setField(tableForm, k, got(k)); });
			tab = got('u') === 't' ? 't' : 'c';
			$$('[data-tab]').forEach(function (b) { b.setAttribute('aria-pressed', b.getAttribute('data-tab') === tab ? 'true' : 'false'); });
			$$('[data-pane]').forEach(function (pane) {
				if (pane.getAttribute('data-pane') === tab) {
					pane.removeAttribute('data-off');
				} else {
					pane.setAttribute('data-off', '');
				}
			});
		}

		var first = true;
		function update() {
			var p = params();

			var c = NRR.calc({ q: p.q, x: p.x });
			text('cerr', c.err || '');
			show(out('cerr'), !!c.err);
			text('cbig', c.err ? '–' : c.nrr);
			text('cfor', c.err ? '–' : c.forRate);
			text('cagainst', c.err ? '–' : c.agRate);
			text('cline', c.err ? 'Check the inputs.' : c.rf + ' runs in ' + NRR.overs(c.bf) + ' overs against ' + c.ra + ' in ' + NRR.overs(c.ba) + ' overs, from ' + c.counted + (c.counted === 1 ? ' match.' : ' matches.'));
			out('csteps').innerHTML = stepsHtml(c);

			var list = NRR.parseTeams(p.t);
			syncPickers(list);
			p = params();
			var s = NRR.solve({ t: p.t, y: p.y, v: p.v, o: p.o, k: p.k, e: p.e, q: p.h, d: p.d, z: p.z });
			var nrrCells = $$('[data-nrr]', teamBox), used = 0;
			$$('.trow', teamBox).forEach(function (row, i) {
				var blank = ['n', 'p', 'w', 'pts', 'rf', 'of', 'ra', 'oa'].every(function (f) { return field(row, f) === ''; });
				nrrCells[i].textContent = blank ? '–' : s.teams[used++].nrr;
			});
			text('scap', p.z === 'w' ? 'Standings by points, then wins, then net run rate' : 'Standings by points, then net run rate');
			out('standings').innerHTML = s.order.map(function (i, pos) {
				var t = s.teams[i], v = function (x) { return x === null ? '–' : x; };
				return '<tr><td>' + (pos + 1) + '</td><th scope="row">' + esc(t.name) + '</th><td>' + v(t.p) + '</td><td>' + v(t.w) + '</td><td>' + v(t.pts) + '</td><td>' + t.nrr + '</td></tr>';
			}).join('');
			text('serr', s.err || '');
			show(out('serr'), !!s.err);
			text('elabel', p.k === 'c' ? 'Opponent\'s score to chase' : 'Your expected score');
			text('shead', s.err ? 'Qualification scenario' : (s.bat === 'f' ? 'Batting first with ' + parseInt(p.e, 10) + ', ' + s.me + ' needs' : 'Chasing ' + (parseInt(p.e, 10) + 1) + ', ' + s.me + ' needs'));
			text('sbig', s.err ? '–' : s.big);
			text('sline', s.err ? 'Check the points table and the next match.' : s.line);
			text('snote', s.err ? '' : s.note);
			out('ssteps').innerHTML = stepsHtml(s);

			// Result permalink and the copy kept on this device.
			var q = query(p);
			if (!first) {
				history.replaceState(null, '', location.pathname + (q ? '?' + q : '') + location.hash);
				store.set(KEY, q);
			}
			var wa = $$('[data-out="wa"]');
			wa[0].href = 'https://wa.me/?text=' + encodeURIComponent((c.err ? 'Net run rate calculator:' : 'Net run rate: ' + c.nrr + ' (' + c.rf + ' runs in ' + NRR.overs(c.bf) + ' overs, ' + c.ra + ' conceded in ' + NRR.overs(c.ba) + '). Full working:') + ' ' + link(p));
			wa[1].href = 'https://wa.me/?text=' + encodeURIComponent((s.err ? 'NRR qualification calculator:' : s.me + ' vs ' + s.rv + ': ' + s.line + ' Full working:') + ' ' + link(p));
		}

		$$('[data-tab]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				tab = btn.getAttribute('data-tab');
				$$('[data-tab]').forEach(function (b) { b.setAttribute('aria-pressed', b === btn ? 'true' : 'false'); });
				$$('[data-pane]').forEach(function (pane) {
					if (pane.getAttribute('data-pane') === tab) {
						pane.removeAttribute('data-off');
					} else {
						pane.setAttribute('data-off', '');
					}
				});
				update();
			});
		});
		rows(matchBox, NRR.MATCHES_MAX, update);
		rows(teamBox, NRR.TEAMS_MAX, update);
		[calcForm, tableForm].forEach(function (form) {
			form.addEventListener('input', update);
			form.addEventListener('change', update);
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				update();
			});
		});
		var reset = $('[data-reset]');
		if (reset) {
			reset.addEventListener('click', function () {
				store.set(KEY, '');
				location.href = location.pathname + '?u=t#scenario';
			});
		}
		shareWire(params);
		update();
		first = false;
	}

	/* Run rate page ------------------------------------------------------------------------ */

	function runRatePage(form, conv) {
		var got = linked(form, ['u', 'n', 't', 'r', 'o']);
		if (got) {
			['u', 'n', 't', 'r', 'o'].forEach(function (k) { setField(form, k, got(k)); });
		}
		var params = function () {
			var u = $('[name="u"]:checked', form);
			return {
				u: u ? u.value : 'c', n: form.elements.n.value.trim(), t: form.elements.t.value.trim(),
				r: form.elements.r.value.trim(), o: form.elements.o.value.trim()
			};
		};
		var first = true;
		function update() {
			var p = params(), r = NRR.runRate(p), chase = p.u !== 'f';
			show($('[data-target]', form), chase);
			text('err', r.err || '');
			show(out('err'), !!r.err);
			text('label', r.err ? (chase ? 'Required run rate' : 'Projected score at the current rate') : r.label);
			text('big', r.err ? '–' : r.big);
			text('line', r.err ? 'Check the inputs.' : r.line);
			out('lines').innerHTML = (r.err ? [] : r.lines).map(function (l) {
				return '<tr><th scope="row">' + esc(l[0]) + '</th><td>' + esc(l[1]) + '</td></tr>';
			}).join('');
			if (!first) {
				var q = query(p);
				history.replaceState(null, '', location.pathname + (q ? '?' + q : '') + '#result');
			}
			var msg = r.err ? 'Run rate calculator:' : (r.mode === 'c' && r.state === 'on' ? 'Need ' + r.need + ' off ' + r.left + ' balls: required rate ' + r.rrr + ', current ' + r.crr + '.' : r.label + ': ' + r.big + '. ' + r.line + '.');
			out('wa').href = 'https://wa.me/?text=' + encodeURIComponent(msg + ' ' + link(p));
		}
		form.addEventListener('input', update);
		form.addEventListener('change', update);
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			update();
		});
		shareWire(params);
		update();
		first = false;

		if (conv) {
			var convert = function () {
				var c = NRR.convert({ v: conv.elements.v.value, j: conv.elements.j.value });
				text('ov', c.ov ? '= ' + c.ov : c.ovErr || '= ?');
				text('bl', c.bl ? '= ' + c.bl : c.blErr || '= ?');
				out('ov').classList.toggle('fld__err', !!c.ovErr);
				out('bl').classList.toggle('fld__err', !!c.blErr);
			};
			conv.addEventListener('input', convert);
			conv.addEventListener('submit', function (e) {
				e.preventDefault();
				convert();
			});
		}
	}

	/* Strike rate and bowling average pages ------------------------------------------------ */

	function statsPage(form, tool) {
		var names = tool === 'batting' ? ['r', 'b', 'i', 'o', 'f', 'x', 'g'] : ['o', 'r', 'k', 'd'];
		var title = tool === 'batting' ? 'Strike rate' : 'Bowling average';
		// Fields left out of a link are blank, as on the server.
		var got = linked(form, names);
		if (got) {
			names.forEach(function (n) { setField(form, n, got(n) === null ? '' : got(n)); });
		}
		var params = function () {
			var p = {};
			names.forEach(function (n) { p[n] = form.elements[n].value.trim(); });
			return p;
		};
		// Error message under the input it belongs to, as on the DLS calculator.
		var markField = function (r) {
			$$('[aria-invalid]', form).forEach(function (el) {
				el.removeAttribute('aria-invalid');
				el.removeAttribute('aria-describedby');
			});
			$$('.fld__err', form).forEach(function (el) { el.parentNode.removeChild(el); });
			var el = r.err && r.field ? form.elements[r.field] : null;
			if (!el) {
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
		var first = true;
		function update() {
			var p = params(), r = NRR[tool](p);
			text('err', r.err || '');
			show(out('err'), !!r.err);
			markField(r);
			text('label', r.err ? title : r.label);
			text('big', r.err ? '–' : r.big);
			text('line', r.err ? 'Check the inputs.' : r.line);
			out('lines').innerHTML = (r.err ? [] : r.lines).map(function (l) {
				return '<tr><th scope="row">' + esc(l[0]) + '</th><td>' + esc(l[1]) + '</td></tr>';
			}).join('');
			if (!first) {
				var q = query(p);
				history.replaceState(null, '', location.pathname + (q ? '?' + q : '') + '#result');
			}
			var msg = r.err ? title + ' calculator:' : r.label + ': ' + r.big + '. ' + r.line + '.';
			out('wa').href = 'https://wa.me/?text=' + encodeURIComponent(msg + ' ' + link(p));
		}
		form.addEventListener('input', update);
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			update();
		});
		shareWire(params);
		update();
		first = false;
	}

	var calcForm = $('form[data-form="calc"]'), tableForm = $('form[data-form="table"]'), rrForm = $('form[data-form="rr"]');
	var statsForm = $('form[data-form="batting"], form[data-form="bowling"]');
	if (calcForm && tableForm) {
		nrrPage(calcForm, tableForm);
	} else if (rrForm) {
		runRatePage(rrForm, $('form[data-form="conv"]'));
	} else if (statsForm) {
		statsPage(statsForm, statsForm.getAttribute('data-form'));
	}
}());
