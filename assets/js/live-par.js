/*!
 * DLSCalc live par tracker: the scorer adds Team 2's score after each over and the page shows the
 * DLS par at that point. The score is kept on this device so a reload during the match loses nothing.
 * The page is already rendered by PHP from the same engine; this only keeps it in step.
 */
(function () {
	'use strict';
	var DLS = window.DLS;
	var form = document.querySelector('form[data-tool="live"]');
	if (!DLS || !form) {
		return;
	}
	var KEY = 'dlscalc-live';
	var store = {
		get: function () {
			try {
				return window.localStorage.getItem(KEY);
			} catch (e) {
				return null;
			}
		},
		set: function (value) {
			try {
				window.localStorage.setItem(KEY, value);
			} catch (e) { /* private mode: the permalink still works */ }
		}
	};
	// A return visit with no inputs in the address picks up the match saved on this device.
	if (!location.search) {
		var saved = store.get();
		if (saved) {
			location.replace(location.pathname + '?' + saved + '#result');
			return;
		}
	}

	var $ = function (sel, root) { return (root || document).querySelector(sel); };
	var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
	var out = function (key) { return $('[data-out="' + key + '"]'); };
	var esc = function (s) {
		return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; });
	};
	var text = function (key, value) {
		out(key).textContent = value;
	};
	var show = function (el, on) {
		el.hidden = !on;
	};
	var plural = function (n, word) {
		return n + ' ' + word + (n === 1 ? '' : 's');
	};

	var box = $('[data-rows="x"]', form);
	var spare = $('.lrow--spare', box);

	// Reloading during a rain break with no signal gets the page the service worker saved without
	// inputs (data-plain), so fill the form and the score log from the address first, the way the
	// server reads the link online; otherwise the next entry would overwrite the saved log (audit F46).
	if (form.hasAttribute('data-plain') && window.URLSearchParams) {
		var linkQ = new URLSearchParams(location.search);
		if (['n', 'r', 'g', 'c', 'f', 'x'].some(function (k) { return linkQ.has(k); })) {
			var got = function (k, dflt) { return linkQ.has(k) ? linkQ.get(k).trim().slice(0, 120) : dflt; };
			form.elements.n.value = got('n', form.elements.n.value);
			form.elements.r.value = got('r', form.elements.r.value);
			form.elements.c.value = got('c', '');
			form.elements.g.value = got('g', '') === '200' ? '200' : '245';
			var fmt = got('f', '');
			form.elements.f.value = ['odi', 't20', 't10', 'custom'].indexOf(fmt) > -1 ? fmt : ({ 50: 'odi', 20: 't20', 10: 't10' }[form.elements.n.value] || 'custom');
			$$('.lrow:not(.lrow--spare)', box).forEach(function (row) { box.removeChild(row); });
			got('x', '').split('_').filter(Boolean).forEach(function (part) {
				var row = spare.cloneNode(true), v = part.split('-');
				row.classList.remove('lrow--spare');
				['at', 'r', 'w'].forEach(function (f, i) { $('[data-f="' + f + '"]', row).value = v[i] || ''; });
				box.insertBefore(row, spare);
			});
		}
	}
	var blank = spare.cloneNode(true);
	blank.classList.remove('lrow--spare');
	box.removeChild(spare);
	var add = $('[data-add]', form);
	var val = function (row, f) {
		return $('[data-f="' + f + '"]', row).value.trim();
	};

	var log = function () {
		return $$('.lrow', box).map(function (row) {
			var v = [val(row, 'at'), val(row, 'r'), val(row, 'w')];
			return v.join('') === '' ? '' : v.join('-');
		}).filter(Boolean).join('_');
	};
	var params = function () {
		return {
			n: form.elements.n.value.trim(), r: form.elements.r.value.trim(), g: form.elements.g.value,
			c: form.elements.c.value.trim(), f: form.elements.f.value, x: log()
		};
	};
	var query = function (p) {
		return Object.keys(p).filter(function (k) { return p[k] !== ''; }).map(function (k) {
			return k + '=' + encodeURIComponent(p[k]);
		}).join('&');
	};
	var link = function (p) {
		return location.origin + location.pathname + '?' + query(p);
	};

	/** Who is ahead at one entry: [text, modifier, short label]. Mirrors dlscalc_live_status(). */
	var status = function (e) {
		var n = Math.abs(e.diff), runs = plural(n, 'run');
		if (e.state === 'won') {
			return ['Team 2 won', 't2', 'Team 2 won'];
		}
		if (e.state === 'tied') {
			return ['Match tied', 'level', 'Tied'];
		}
		if (e.state === 'lost') {
			return ['Team 1 won by ' + runs, 't1', 'Team 1 won'];
		}
		if (e.diff === 0) {
			return ['Level: tie', 'level', 'Level'];
		}
		return e.diff > 0 ? ['Team 2 ahead by ' + runs, 't2', 'Team 2 +' + n] : ['Team 1 ahead by ' + runs, 't1', 'Team 1 +' + n];
	};

	/** Headline. Mirrors dlscalc_live_headline(). */
	var headline = function (r) {
		if (r.err) {
			return { label: 'Live DLS par', big: '–', line: 'Check the inputs.', word: false };
		}
		var e = r.log[r.log.length - 1];
		if (!e) {
			return { label: 'Team 2 target', big: String(r.target), line: 'in ' + DLS.overs(r.allot) + ' overs. Add Team 2\'s score after each over to see who is ahead on DLS.', word: false };
		}
		var score = e.runs + '/' + e.w, at = DLS.overs(e.at) + (e.at === 6 ? ' over' : ' overs');
		if (e.state !== 'on') {
			var lines = {
				won: 'Team 2 reached the target of ' + r.target + ' with ' + score + ' in ' + at + '.',
				tied: 'Team 2 finished on ' + score + ', level with the par score of ' + e.par + '.',
				lost: 'Team 2 finished on ' + score + ' in ' + at + ', short of the target of ' + r.target + '.'
			};
			return { label: 'Result', big: status(e)[0], line: lines[e.state], word: true };
		}
		var runs = plural(Math.abs(e.diff), 'run'), line;
		if (e.diff === 0) {
			line = 'Team 2 has ' + score + ', exactly on par. If play stopped now, the match would be tied.';
		} else {
			// A match decided on DLS during the chase is won by runs either way (ICC DLS section 7, audit F23).
			line = 'Team 2 has ' + score + ', ' + runs + (e.diff > 0 ? ' above' : ' below') + ' par. If play stopped now, Team ' +
				(e.diff > 0 ? '2' : '1') + ' would win by ' + runs + ' (DLS method).';
		}
		return { label: 'DLS par at ' + at + ', ' + e.w + ' down', big: String(e.par), line: line, word: false };
	};

	var first = true;
	function update() {
		var p = params(), r = DLS.live(p), err = r.err || '';
		text('err', err);
		show(out('err'), !!err);
		var h = headline(r), last = err ? null : r.log[r.log.length - 1];
		// The latest entry's warning, or before any entry the one for the whole chase (audit F24).
		var warn = err ? '' : (last ? last.warn || '' : r.warn);
		text('warn', warn);
		show(out('warn'), !!warn);
		text('label', h.label);
		text('big', h.big);
		out('big').classList.toggle('result__big--word', h.word);
		text('line', h.line);
		text('target', err ? '–' : String(r.target));
		text('t2overs', err ? '–' : DLS.overs(r.allot));

		// Par and status beside each filled row, in order.
		var used = 0;
		$$('.lrow', box).forEach(function (row) {
			var filled = (val(row, 'at') + val(row, 'r') + val(row, 'w')) !== '';
			var e = !err && filled ? r.log[used++] : null, st = e ? status(e) : ['', '', ''];
			$('[data-par]', row).textContent = e ? String(e.par) : '';
			var pill = $('[data-status]', row);
			pill.textContent = st[2];
			pill.title = st[0];
			pill.className = 'pill' + (st[1] ? ' pill--' + st[1] : '');
		});
		add.hidden = !!last && last.state !== 'on' || $$('.lrow', box).length >= DLS.LIVE_MAX;

		out('steps').innerHTML = (err ? ['Fix the inputs above to see the working.'] : r.steps.concat(r.nowSteps)).map(function (s) {
			return '<li>' + esc(s) + '</li>';
		}).join('');
		out('rows').innerHTML = err ? '' : r.rows.map(function (row) {
			return '<tr><th scope="row">' + row.over + '</th>' + row.cells.map(function (c) { return '<td>' + c + '</td>'; }).join('') + '</tr>';
		}).join('');
		var note = out('tablenote');
		note.textContent = note.getAttribute(err ? 'data-off' : 'data-on');

		var q = query(p);
		if (!first) {
			history.replaceState(null, '', location.pathname + '?' + q + '#result');
			store.set(q);
		}
		var msg = err ? 'Live DLS par score:' : (last && last.state === 'on' ?
			'DLS par after ' + DLS.overs(last.at) + (last.at === 6 ? ' over: ' : ' overs: ') + last.par + '. Team 2 ' + last.runs + '/' + last.w + ', ' + status(last)[0] + '. Live tracker:' :
			'DLS target: ' + r.target + ' in ' + DLS.overs(r.allot) + ' overs. Live tracker:');
		out('wa').href = 'https://wa.me/?text=' + encodeURIComponent(msg + ' ' + link(p));
	}

	var wire = function (row) {
		$('[data-remove]', row).addEventListener('click', function () {
			box.removeChild(row);
			update();
		});
	};
	$$('.lrow', box).forEach(wire);

	// The next row starts one over on from the last whole over, with the score carried over.
	add.addEventListener('click', function () {
		var rows = $$('.lrow', box), prev = rows[rows.length - 1], row = blank.cloneNode(true);
		if (prev) {
			var b = DLS.balls(val(prev, 'at'));
			$('[data-f="at"]', row).value = isNaN(b) ? '' : String(Math.floor(b / 6) + 1);
			$('[data-f="r"]', row).value = val(prev, 'r');
			$('[data-f="w"]', row).value = val(prev, 'w');
		} else {
			$('[data-f="at"]', row).value = '1';
			$('[data-f="w"]', row).value = '0';
		}
		box.appendChild(row);
		wire(row);
		update();
		var runs = $('[data-f="r"]', row);
		runs.focus();
		runs.select();
	});

	$('[data-reset]', form).addEventListener('click', function () {
		$$('.lrow', box).forEach(function (row) { box.removeChild(row); });
		update();
	});

	form.addEventListener('input', update);
	form.addEventListener('change', update);
	form.addEventListener('submit', function (e) {
		e.preventDefault();
		update();
	});

	var copy = $('[data-copy]');
	if (copy && !navigator.clipboard) {
		copy.hidden = true;
	} else if (copy) {
		copy.addEventListener('click', function () {
			navigator.clipboard.writeText(link(params())).then(function () {
				copy.textContent = 'Link copied';
				setTimeout(function () { copy.textContent = 'Copy link'; }, 2000);
			});
		});
	}
	$$('[data-print]').forEach(function (btn) {
		btn.addEventListener('click', function () { window.print(); });
	});
	// The printed par sheet ends with the link that reopens this result.
	window.addEventListener('beforeprint', function () {
		$$('[data-sheet-url]').forEach(function (el) { el.textContent = location.href.replace(/#.*$/, ''); });
	});

	update();
	first = false;
}());
