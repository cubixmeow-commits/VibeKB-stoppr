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
