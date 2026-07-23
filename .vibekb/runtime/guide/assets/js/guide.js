/* VibeKB guide — light progressive enhancement, no framework, no CDN.
   Core navigation and reading remain fully usable without JavaScript:
   - Without JS the sidebar flows in document order on small screens.
   - With JS it becomes a drawer opened by the Menu button.
   - Search and filtering are enhancements; the guide is readable without them.

   The same file powers the dynamic PHP guide and the static /docs snapshot. */
(function (window, document) {
  'use strict';

  document.documentElement.classList.remove('no-js');
  document.body.classList.add('js-ready');

  function ready(fn) {
    if (document.readyState !== 'loading') {
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  }

  function escapeHtml(value) {
    var div = document.createElement('div');
    div.textContent = value == null ? '' : String(value);
    return div.innerHTML;
  }

  ready(function () {
    initMobileNav();
    initFunctionalityFilters();
    initSearch();
    initDiagrams();
    initFunctionalityMap();
  });

  // ---- Mobile navigation drawer -------------------------------------------
  function initMobileNav() {
    var toggle = document.querySelector('.nav-toggle');
    var sidebar = document.getElementById('guide-sidebar');
    var backdrop = document.getElementById('nav-backdrop');
    if (!toggle || !sidebar) {
      return;
    }
    var mq = window.matchMedia('(max-width: 900px)');

    function setOpen(open) {
      sidebar.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (backdrop) {
        backdrop.hidden = !open;
        backdrop.classList.toggle('is-visible', open);
      }
      document.body.classList.toggle('nav-open', open);
    }

    toggle.hidden = false;
    toggle.addEventListener('click', function () {
      if (!mq.matches) {
        return;
      }
      setOpen(!sidebar.classList.contains('is-open'));
    });
    if (backdrop) {
      backdrop.addEventListener('click', function () { setOpen(false); });
    }
    sidebar.addEventListener('click', function (e) {
      if (mq.matches && e.target.closest('a')) {
        setOpen(false);
      }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && sidebar.classList.contains('is-open')) {
        setOpen(false);
        toggle.focus();
      }
    });
    var onChange = function (e) { if (!e.matches) { setOpen(false); } };
    if (typeof mq.addEventListener === 'function') {
      mq.addEventListener('change', onChange);
    } else if (typeof mq.addListener === 'function') {
      mq.addListener(onChange);
    }
  }

  // ---- Functionality filters ----------------------------------------------
  // Works in both modes: dynamic (server-side GET) and static (client-side).
  // In static output there is no server, so filtering is applied on the page.
  function initFunctionalityFilters() {
    var form = document.querySelector('.filters');
    if (!form) {
      return;
    }
    var selects = form.querySelectorAll('select');
    if (!selects.length) {
      return;
    }
    var cards = document.querySelectorAll('.record-card[data-status]');
    var isStatic = document.body.getAttribute('data-mode') === 'static';

    if (!isStatic && !cards.length) {
      // Dynamic mode without client cards: keep the original auto-submit.
      form.addEventListener('change', function (e) {
        if (e.target.matches('select')) {
          form.submit();
        }
      });
      return;
    }

    function currentValues() {
      var v = {};
      selects.forEach(function (s) { v[s.name] = s.value; });
      return v;
    }

    function apply() {
      var v = currentValues();
      var shown = 0;
      cards.forEach(function (card) {
        var match =
          (!v.status || card.getAttribute('data-status') === v.status) &&
          (!v.area || card.getAttribute('data-area') === v.area) &&
          (!v.verification || card.getAttribute('data-verification') === v.verification) &&
          (!v.facing || card.getAttribute('data-facing') === v.facing);
        card.hidden = !match;
        if (match) { shown += 1; }
      });
      document.querySelectorAll('.group-block').forEach(function (group) {
        var visible = group.querySelector('.record-card:not([hidden])');
        group.hidden = !visible;
      });
      var empty = document.getElementById('filter-empty');
      if (empty) { empty.hidden = shown > 0; }
    }

    // Intercept submit so static pages never navigate to a dead ?query.
    form.addEventListener('submit', function (e) { e.preventDefault(); apply(); });
    selects.forEach(function (s) { s.addEventListener('change', apply); });
    var clear = document.getElementById('clear-filters');
    if (clear) {
      clear.addEventListener('click', function (e) {
        e.preventDefault();
        selects.forEach(function (s) { s.value = ''; });
        apply();
      });
    }
    // Honour ?status=... deep links in static mode.
    if (isStatic && window.location.search) {
      var params = new URLSearchParams(window.location.search);
      selects.forEach(function (s) {
        if (params.get(s.name)) { s.value = params.get(s.name); }
      });
      apply();
    }
  }

  // ---- Explainable diagrams ------------------------------------------------
  // Progressive enhancement only. Without JS, the SVG markers are ordinary
  // in-page links to the explanation sections and every explanation is fully
  // readable. With JS, selecting a node or edge (in the diagram or in the list)
  // highlights it, dims the rest, and focuses its explanation; Escape clears.
  function initDiagrams() {
    var sections = document.querySelectorAll('.diagram-section--explainable');
    if (!sections.length) {
      return;
    }

    function clearSelection(section) {
      section.classList.remove('dx-has-selection');
      section.querySelectorAll('.is-selected').forEach(function (el) { el.classList.remove('is-selected'); });
      section.querySelectorAll('.dx-card.is-active').forEach(function (el) { el.classList.remove('is-active'); });
    }

    function select(section, type, id, opts) {
      opts = opts || {};
      clearSelection(section);
      var svg = section.querySelector('.diagram-figure svg');
      var marker = svg ? svg.querySelector('[data-vibekb-' + type + '="' + id + '"]') : null;
      var card = document.getElementById(type + '-' + id);
      if (marker) {
        marker.classList.add('is-selected');
        section.classList.add('dx-has-selection');
      }
      if (card) {
        card.classList.add('is-active');
        if (opts.scroll !== false) {
          card.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        if (opts.focus !== false) {
          try { card.focus({ preventScroll: true }); } catch (e) { card.focus(); }
        }
      }
    }

    sections.forEach(function (section) {
      var svg = section.querySelector('.diagram-figure svg');
      if (svg) {
        svg.addEventListener('click', function (e) {
          var a = e.target.closest('[data-vibekb-node],[data-vibekb-edge]');
          if (!a) { return; }
          e.preventDefault();
          if (a.hasAttribute('data-vibekb-node')) {
            select(section, 'node', a.getAttribute('data-vibekb-node'));
          } else {
            select(section, 'edge', a.getAttribute('data-vibekb-edge'));
          }
        });
      }

      // Selecting a card syncs the diagram (without stealing focus/scroll).
      section.querySelectorAll('.dx-card').forEach(function (card) {
        card.addEventListener('click', function (e) {
          if (e.target.closest('a')) { return; }
          var type = card.hasAttribute('data-node') ? 'node' : 'edge';
          select(section, type, card.getAttribute('data-' + type), { scroll: false, focus: false });
        });
      });

      // An edge's endpoint link selects the referenced node rather than jumping.
      section.querySelectorAll('.dx-edge__endpoint').forEach(function (a) {
        a.addEventListener('click', function (e) {
          var href = a.getAttribute('href') || '';
          if (href.indexOf('#node-') !== 0) { return; }
          e.preventDefault();
          select(section, 'node', href.slice('#node-'.length));
        });
      });
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        sections.forEach(clearSelection);
      }
    });

    // Honour a deep link (#node-x / #edge-x) on load.
    var hash = window.location.hash || '';
    var m = /^#(node|edge)-(.+)$/.exec(hash);
    if (m) {
      var el = document.getElementById(m[1] + '-' + m[2]);
      var section = el && el.closest('.diagram-section--explainable');
      if (section) {
        select(section, m[1], m[2], { scroll: true, focus: true });
      }
    }
  }

  // ---- Interactive Functionality Map ---------------------------------------
  // The guide's first screen. A progressive enhancement layered on top of the
  // accessible fallback (nested area cards that link into the docs): on capable
  // screens it becomes a zoomable, pannable map of the application's
  // functionality — Level 1 areas, expanding to Level 2 capabilities, opening
  // into Level 3 documentation. Without JS, on small screens, or in list mode,
  // the fallback is shown instead. The two are the same data, so they can never
  // disagree. All coordinates live in a fixed virtual space; pan/zoom is a
  // single CSS transform on the canvas.
  function initFunctionalityMap() {
    var root = document.querySelector('[data-fmap]');
    if (!root) { return; }
    var stage = root.querySelector('[data-fmap-stage]');
    var fallback = root.querySelector('[data-fmap-fallback]');
    var hint = root.querySelector('[data-fmap-hint]');
    var modelEl = root.querySelector('[data-fmap-model]');
    if (!stage || !fallback || !modelEl) { return; }

    var model;
    try { model = JSON.parse(modelEl.textContent || '{}'); } catch (e) { return; }
    if (!model.areas || !model.areas.length) { return; }

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var canvasMedia = window.matchMedia('(min-width: 760px)');

    // Virtual coordinate space. Nodes are positioned by their centre; the canvas
    // is transformed as a whole for pan/zoom.
    var CENTER = { x: 1400, y: 900 };
    var R1 = 430;          // radius of the ring of areas
    var R2 = 250;          // area -> children radius
    var FIT_RADIUS = R1 + R2 + 150;

    var expanded = {};     // areaId -> true (lazy: children exist only when open)
    var selected = null;   // currently selected node id
    var view = { x: 0, y: 0, scale: 1 };
    var listMode = false;
    var built = false;
    var canvas, edges, tooltip, nodeLayer;

    // ---- geometry ----------------------------------------------------------
    function areaAngle(i, n) { return (-Math.PI / 2) + (i * (2 * Math.PI / n)); }

    function positions() {
      var pos = {};
      pos['__app'] = { x: CENTER.x, y: CENTER.y };
      var n = model.areas.length;
      model.areas.forEach(function (area, i) {
        var a = areaAngle(i, n);
        var ax = CENTER.x + R1 * Math.cos(a);
        var ay = CENTER.y + R1 * Math.sin(a);
        pos[area.id] = { x: ax, y: ay, angle: a };
        if (expanded[area.id] && area.children) {
          var c = area.children.length;
          var reach = R2 + Math.max(0, c - 3) * 26;
          var span = Math.min(Math.PI * 0.9, c * 0.42);
          area.children.forEach(function (child, j) {
            var offset = c === 1 ? 0 : (j - (c - 1) / 2) * (span / (c - 1));
            var ca = a + offset;
            pos[child.id] = {
              x: ax + reach * Math.cos(ca),
              y: ay + reach * Math.sin(ca)
            };
          });
        }
      });
      return pos;
    }

    // ---- rendering ---------------------------------------------------------
    function buildShell() {
      stage.innerHTML = '';
      stage.hidden = false;
      stage.removeAttribute('aria-hidden');
      stage.setAttribute('role', 'application');
      stage.setAttribute('aria-label', 'Interactive functionality map. Use the list view for a fully keyboard-navigable version.');

      canvas = el('div', 'fmap-stage__canvas');
      edges = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      edges.setAttribute('class', 'fmap-edges');
      edges.setAttribute('width', String(CENTER.x * 2));
      edges.setAttribute('height', String(CENTER.y * 2));
      edges.setAttribute('viewBox', '0 0 ' + (CENTER.x * 2) + ' ' + (CENTER.y * 2));
      nodeLayer = el('div', 'fmap-nodes');
      canvas.appendChild(edges);
      canvas.appendChild(nodeLayer);
      stage.appendChild(canvas);

      stage.appendChild(buildControls());

      tooltip = el('div', 'fmap-tooltip');
      tooltip.hidden = true;
      stage.appendChild(tooltip);

      bindPanZoom();
    }

    function buildControls() {
      var wrap = el('div', 'fmap-stage__controls');
      wrap.appendChild(ctrlButton('＋', 'Zoom in', function () { zoomBy(1.2); }));
      wrap.appendChild(ctrlButton('−', 'Zoom out', function () { zoomBy(1 / 1.2); }));
      wrap.appendChild(ctrlButton('⤢', 'Fit map', function () { fit(true); }));
      var list = ctrlButton('List', 'Switch to list view', function () { setListMode(true); });
      list.classList.add('fmap-ctrl--wide');
      wrap.appendChild(list);
      return wrap;
    }

    function ctrlButton(label, title, fn) {
      var b = el('button', 'fmap-ctrl');
      b.type = 'button';
      b.textContent = label;
      b.title = title;
      b.setAttribute('aria-label', title);
      b.addEventListener('click', function (e) { e.preventDefault(); fn(); });
      return b;
    }

    function render() {
      var pos = positions();
      nodeLayer.innerHTML = '';
      nodeLayer.classList.toggle('has-context', !!(model.context && model.context.active));
      while (edges.firstChild) { edges.removeChild(edges.firstChild); }

      // Edges first so nodes sit above them.
      model.areas.forEach(function (area) {
        drawEdge(pos['__app'], pos[area.id], area.current);
        if (expanded[area.id] && area.children) {
          area.children.forEach(function (child) {
            drawEdge(pos[area.id], pos[child.id], child.current);
          });
        }
      });

      nodeLayer.appendChild(appNode(pos['__app']));
      model.areas.forEach(function (area) {
        nodeLayer.appendChild(areaNode(area, pos[area.id]));
        if (expanded[area.id] && area.children) {
          area.children.forEach(function (child) {
            nodeLayer.appendChild(leafNode(child, pos[child.id]));
          });
        }
      });
    }

    function drawEdge(from, to, current) {
      if (!from || !to) { return; }
      var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      var mx = (from.x + to.x) / 2;
      var my = (from.y + to.y) / 2;
      // Gentle curve: pull the control point slightly perpendicular.
      var dx = to.x - from.x, dy = to.y - from.y;
      var cx = mx - dy * 0.08, cy = my + dx * 0.08;
      path.setAttribute('d', 'M ' + from.x + ' ' + from.y + ' Q ' + cx + ' ' + cy + ' ' + to.x + ' ' + to.y);
      path.setAttribute('class', 'fmap-edge' + (current ? ' is-current' : ''));
      edges.appendChild(path);
    }

    function baseNode(kind, x, y) {
      var b = el('button', 'fmap-cnode fmap-cnode--' + kind);
      b.type = 'button';
      b.style.left = x + 'px';
      b.style.top = y + 'px';
      if (!reduceMotion) { b.classList.add('is-entering'); }
      return b;
    }

    function appNode(p) {
      var b = baseNode('app', p.x, p.y);
      b.innerHTML =
        '<span class="fmap-cnode__eyebrow">Application</span>' +
        '<span class="fmap-cnode__title">' + escapeHtml(model.app.name) + '</span>' +
        (model.app.outcome ? '<span class="fmap-cnode__sub">' + escapeHtml(clip(model.app.outcome, 80)) + '</span>' : '');
      b.addEventListener('click', function () { fit(true); });
      return b;
    }

    function areaNode(area, p) {
      var b = baseNode('area', p.x, p.y);
      b.setAttribute('data-status', area.status);
      b.setAttribute('aria-expanded', expanded[area.id] ? 'true' : 'false');
      if (area.current) { b.classList.add('is-current'); }
      if (selected === area.id) { b.classList.add('is-selected'); }
      b.innerHTML =
        '<span class="fmap-cnode__row">' +
          '<span class="fmap-dot" data-status="' + escapeHtml(area.status) + '"></span>' +
          '<span class="fmap-cnode__title">' + escapeHtml(area.title) + '</span>' +
        '</span>' +
        (area.description ? '<span class="fmap-cnode__sub">' + escapeHtml(clip(area.description, 84)) + '</span>' : '') +
        '<span class="fmap-cnode__badge">' + area.count + (expanded[area.id] ? ' ▾' : ' ▸') + '</span>';
      wireNode(b, area, {
        single: function () { toggleArea(area.id); },
        open: function () { openUrl(area.url); },
        tip: area.description || area.title,
        tipMeta: area.count + ' capabilities'
      });
      return b;
    }

    function leafNode(child, p) {
      var b = baseNode('leaf', p.x, p.y);
      b.setAttribute('data-status', child.status);
      if (child.current) { b.classList.add('is-current'); }
      if (selected === child.id) { b.classList.add('is-selected'); }
      b.innerHTML =
        '<span class="fmap-cnode__row">' +
          '<span class="fmap-dot" data-status="' + escapeHtml(child.status) + '"></span>' +
          '<span class="fmap-cnode__title">' + escapeHtml(child.title) + '</span>' +
        '</span>' +
        '<span class="fmap-cnode__badge fmap-cnode__badge--soft">' + escapeHtml(child.statusLabel) + '</span>';
      wireNode(b, child, {
        single: function () { setSelected(child.id); },
        open: function () { openUrl(child.url); },
        tip: child.summary || child.title,
        tipMeta: child.statusLabel + (child.fileCount ? ' · ' + child.fileCount + ' file' + (child.fileCount === 1 ? '' : 's') : '')
      });
      return b;
    }

    // click = single action, double-click = open docs, resolved with a short
    // delay so the two never fight. Keyboard: Enter/Space = single action.
    function wireNode(b, data, handlers) {
      var timer = null;
      b.addEventListener('click', function (e) {
        if (suppressClick) { return; }
        e.preventDefault();
        if (timer) { return; }
        timer = window.setTimeout(function () { timer = null; handlers.single(); }, 220);
      });
      b.addEventListener('dblclick', function (e) {
        e.preventDefault();
        if (timer) { window.clearTimeout(timer); timer = null; }
        handlers.open();
      });
      b.addEventListener('mouseenter', function () { showTip(b, data, handlers); });
      b.addEventListener('mouseleave', hideTip);
      b.addEventListener('focus', function () { showTip(b, data, handlers); });
      b.addEventListener('blur', hideTip);
      b.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); handlers.single(); }
        else if (e.key === 'o' || e.key === 'O') { e.preventDefault(); handlers.open(); }
      });
    }

    function showTip(b, data, handlers) {
      if (!tooltip) { return; }
      tooltip.innerHTML =
        '<strong>' + escapeHtml(data.title) + '</strong>' +
        (handlers.tip ? '<span>' + escapeHtml(clip(handlers.tip, 140)) + '</span>' : '') +
        (handlers.tipMeta ? '<span class="fmap-tooltip__meta">' + escapeHtml(handlers.tipMeta) + '</span>' : '') +
        '<span class="fmap-tooltip__hint">Double-click to open documentation</span>';
      tooltip.hidden = false;
      var sr = stage.getBoundingClientRect();
      var br = b.getBoundingClientRect();
      var left = br.left - sr.left + br.width / 2;
      var top = br.top - sr.top - 12;
      tooltip.style.left = Math.max(12, Math.min(sr.width - 12, left)) + 'px';
      tooltip.style.top = top + 'px';
    }
    function hideTip() { if (tooltip) { tooltip.hidden = true; } }

    // ---- state transitions -------------------------------------------------
    function toggleArea(id) {
      expanded[id] = !expanded[id];
      selected = expanded[id] ? id : selected;
      render();
    }
    function setSelected(id) {
      selected = (selected === id) ? null : id;
      render();
    }
    function openUrl(url) { if (url) { window.location.href = url; } }

    // ---- pan / zoom --------------------------------------------------------
    var suppressClick = false;
    function applyView() {
      canvas.style.transform = 'translate(' + view.x + 'px,' + view.y + 'px) scale(' + view.scale + ')';
    }
    function clampScale(s) { return Math.max(0.35, Math.min(2.4, s)); }

    function zoomAround(factor, px, py) {
      var ns = clampScale(view.scale * factor);
      var k = ns / view.scale;
      // keep the point (px,py) in stage space stationary
      view.x = px - (px - view.x) * k;
      view.y = py - (py - view.y) * k;
      view.scale = ns;
      applyView();
    }
    function zoomBy(factor) {
      var r = stage.getBoundingClientRect();
      zoomAround(factor, r.width / 2, r.height / 2);
    }

    function fit(animate) {
      var r = stage.getBoundingClientRect();
      var s = clampScale(Math.min(r.width, r.height) / (FIT_RADIUS * 2) * 0.95);
      view.scale = s;
      view.x = r.width / 2 - CENTER.x * s;
      view.y = r.height / 2 - CENTER.y * s;
      if (animate && !reduceMotion) {
        canvas.style.transition = 'transform 420ms cubic-bezier(.22,.61,.36,1)';
        window.setTimeout(function () { canvas.style.transition = ''; }, 460);
      }
      applyView();
    }

    function bindPanZoom() {
      var pointers = {};
      var last = null;
      var startDist = 0, startScale = 1, moved = 0, captured = false;

      stage.addEventListener('wheel', function (e) {
        e.preventDefault();
        var r = stage.getBoundingClientRect();
        zoomAround(e.deltaY < 0 ? 1.12 : 1 / 1.12, e.clientX - r.left, e.clientY - r.top);
      }, { passive: false });

      stage.addEventListener('pointerdown', function (e) {
        if (e.target.closest('.fmap-ctrl')) { return; }
        pointers[e.pointerId] = { x: e.clientX, y: e.clientY };
        var ids = Object.keys(pointers);
        if (ids.length === 1) {
          last = { x: e.clientX, y: e.clientY };
          moved = 0;
          captured = false;
          suppressClick = false;
          // Do NOT capture the pointer yet: capturing on pointerdown would
          // redirect the subsequent click away from the node under the cursor,
          // breaking single-click expand. Capture only once a real drag starts.
        } else if (ids.length === 2) {
          startDist = pointerDist(pointers);
          startScale = view.scale;
        }
      });

      stage.addEventListener('pointermove', function (e) {
        if (!pointers[e.pointerId]) { return; }
        pointers[e.pointerId] = { x: e.clientX, y: e.clientY };
        var ids = Object.keys(pointers);
        if (ids.length >= 2) {
          var d = pointerDist(pointers);
          if (startDist > 0) {
            var r = stage.getBoundingClientRect();
            var c = pointerCenter(pointers, r);
            var target = clampScale(startScale * (d / startDist));
            zoomAround(target / view.scale, c.x, c.y);
          }
          return;
        }
        if (last) {
          var dx = e.clientX - last.x, dy = e.clientY - last.y;
          moved += Math.abs(dx) + Math.abs(dy);
          if (moved > 6) {
            suppressClick = true;
            hideTip();
            if (!captured) {
              captured = true;
              stage.classList.add('is-grabbing');
              try { stage.setPointerCapture(e.pointerId); } catch (err) {}
            }
          }
          view.x += dx; view.y += dy;
          last = { x: e.clientX, y: e.clientY };
          applyView();
        }
      });

      function endPointer(e) {
        delete pointers[e.pointerId];
        if (Object.keys(pointers).length === 0) {
          last = null;
          stage.classList.remove('is-grabbing');
          // release suppression after the click event has passed
          window.setTimeout(function () { suppressClick = false; }, 0);
        }
      }
      stage.addEventListener('pointerup', endPointer);
      stage.addEventListener('pointercancel', endPointer);
      stage.addEventListener('pointerleave', function (e) {
        if (e.pointerType === 'mouse') { endPointer(e); }
      });
    }

    function pointerDist(p) {
      var ids = Object.keys(p);
      var a = p[ids[0]], b = p[ids[1]];
      return Math.hypot(a.x - b.x, a.y - b.y);
    }
    function pointerCenter(p, r) {
      var ids = Object.keys(p);
      var a = p[ids[0]], b = p[ids[1]];
      return { x: (a.x + b.x) / 2 - r.left, y: (a.y + b.y) / 2 - r.top };
    }

    // ---- list <-> map mode -------------------------------------------------
    function setListMode(on) {
      listMode = on;
      root.classList.toggle('fmap--list', on);
      fallback.hidden = false; // always available; visibility controlled by CSS
      if (on) {
        stage.hidden = true;
        if (hint) { hint.hidden = true; }
      } else {
        stage.hidden = false;
        if (hint) { hint.hidden = false; }
        fit(false);
      }
    }

    // A "Back to map" affordance for the fallback when it is being used as the
    // desktop list view.
    function addMapToggle() {
      if (fallback.querySelector('.fmap-fallback__toggle')) { return; }
      var bar = el('div', 'fmap-fallback__toggle');
      var b = el('button', 'fmap-ctrl fmap-ctrl--wide');
      b.type = 'button';
      b.textContent = '◱ Map view';
      b.addEventListener('click', function () { setListMode(false); });
      bar.appendChild(b);
      fallback.insertBefore(bar, fallback.firstChild);
    }

    // ---- boot --------------------------------------------------------------
    function enableCanvas() {
      if (!built) {
        buildShell();
        render();
        addMapToggle();
        built = true;
      }
      root.classList.add('fmap--canvas');
      if (!listMode) {
        stage.hidden = false;
        if (hint) { hint.hidden = false; }
        fit(false);
      }
    }
    function disableCanvas() {
      root.classList.remove('fmap--canvas');
      if (stage) { stage.hidden = true; }
      if (hint) { hint.hidden = true; }
    }

    function evaluateMode() {
      if (canvasMedia.matches) { enableCanvas(); }
      else { disableCanvas(); }
    }

    evaluateMode();
    var onMedia = function () { evaluateMode(); };
    if (typeof canvasMedia.addEventListener === 'function') {
      canvasMedia.addEventListener('change', onMedia);
    } else if (typeof canvasMedia.addListener === 'function') {
      canvasMedia.addListener(onMedia);
    }
    var rt = null;
    window.addEventListener('resize', function () {
      if (!built || listMode || !canvasMedia.matches) { return; }
      window.clearTimeout(rt);
      rt = window.setTimeout(function () { fit(false); }, 150);
    });
  }

  function el(tag, className) {
    var node = document.createElement(tag);
    if (className) { node.className = className; }
    return node;
  }
  function clip(s, n) {
    s = String(s || '');
    return s.length > n ? s.slice(0, n - 1).replace(/\s+\S*$/, '') + '…' : s;
  }

  // ---- Client-side search --------------------------------------------------
  function initSearch() {
    var input = document.getElementById('search-query');
    var results = document.getElementById('search-results');
    if (!input || !results) {
      // Header search box (present on every page): route to the search page.
      var header = document.getElementById('site-search-input');
      if (header) {
        var f = header.closest('form');
        if (f) {
          f.addEventListener('submit', function (e) {
            e.preventDefault();
            var action = f.getAttribute('action') || 'search/index.html';
            var q = header.value;
            window.location.href = action + (q ? '?q=' + encodeURIComponent(q) : '');
          });
        }
      }
      return;
    }
    var empty = document.getElementById('search-empty');
    var indexUrl = results.getAttribute('data-search-index') || 'assets/data/search.json';
    var index = null;

    function render(query) {
      var q = (query || '').toLowerCase().trim();
      if (!index) { return; }
      if (!q) {
        results.innerHTML = '';
        if (empty) { empty.hidden = true; }
        return;
      }
      var matches = index.filter(function (item) {
        var hay = (item.title + ' ' + item.summary + ' ' + item.type + ' ' + (item.body || '')).toLowerCase();
        return hay.indexOf(q) !== -1;
      });
      if (!matches.length) {
        results.innerHTML = '';
        if (empty) { empty.hidden = false; }
        return;
      }
      if (empty) { empty.hidden = true; }
      var html = '<ul class="record-list">';
      matches.slice(0, 60).forEach(function (item) {
        html +=
          '<li class="record-card"><div class="record-card__row">' +
          '<h3 class="record-card__title"><a class="record-card__link" href="' +
          escapeHtml(item.url) + '">' + escapeHtml(item.title) + '</a></h3>' +
          '<span class="badge badge--info">' + escapeHtml(item.type) + '</span></div>' +
          '<p class="record-card__summary">' + escapeHtml(item.summary) + '</p></li>';
      });
      html += '</ul>';
      results.innerHTML = html;
    }

    var xhr = new XMLHttpRequest();
    xhr.open('GET', indexUrl, true);
    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) { return; }
      if (xhr.status >= 200 && xhr.status < 300) {
        try { index = JSON.parse(xhr.responseText); } catch (e) { index = []; }
        var params = new URLSearchParams(window.location.search);
        var initial = params.get('q') || '';
        input.value = initial;
        render(initial);
      }
    };
    xhr.send();
    input.addEventListener('input', function () { render(input.value); });
  }
})(window, document);
