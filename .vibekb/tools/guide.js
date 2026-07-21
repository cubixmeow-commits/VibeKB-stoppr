(function (window, document, $) {
  'use strict';

  document.documentElement.classList.remove('no-js');
  document.body.classList.add('js-ready');

  if (!$ || !$.fn) {
    return;
  }

  $(function () {
    var $toggle = $('.nav-toggle');
    var $sidebar = $('#guide-sidebar');
    var $backdrop = $('#nav-backdrop');
    var mq = window.matchMedia('(max-width: 900px)');

    function setOpen(open) {
      $sidebar.toggleClass('is-open', open);
      $toggle.attr('aria-expanded', open ? 'true' : 'false');
      $backdrop.prop('hidden', !open).toggleClass('is-visible', open);
      document.body.classList.toggle('nav-open', open);
    }

    function closeNav() {
      setOpen(false);
    }

    if ($toggle.length && $sidebar.length) {
      $toggle.prop('hidden', false);
      $toggle.on('click', function () {
        if (!mq.matches) {
          return;
        }
        setOpen(!$sidebar.hasClass('is-open'));
      });
      $backdrop.on('click', closeNav);
      $sidebar.on('click', 'a', function () {
        if (mq.matches) {
          closeNav();
        }
      });
      $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $sidebar.hasClass('is-open')) {
          closeNav();
          $toggle.trigger('focus');
        }
      });
    }

    var $filters = $('#functionality-filters');
    if ($filters.length) {
      function applyFilters() {
        var status = $filters.find('[name=status]').val();
        var area = $filters.find('[name=area]').val();
        var verification = $filters.find('[name=verification]').val();
        var facing = $filters.find('[name=facing]').val();
        var shown = 0;
        $('.record-card').each(function () {
          var $card = $(this);
          var match =
            (!status || $card.data('status') === status) &&
            (!area || $card.data('area') === area) &&
            (!verification || $card.data('verification') === verification) &&
            (!facing || $card.data('facing') === facing);
          $card.toggle(match);
          if (match) {
            shown += 1;
          }
        });
        $('.group-block').each(function () {
          var $group = $(this);
          $group.toggle($group.find('.record-card:visible').length > 0);
        });
        $('#filter-empty').prop('hidden', shown > 0);
      }
      $filters.find('select').on('change', applyFilters);
      $('#clear-filters').on('click', function () {
        $filters.find('select').val('');
        applyFilters();
      });
    }

    var searchIndex = null;
    var searchBase = $('script[src*="guide.js"]').attr('src').replace(/js\/guide\.js.*$/, 'data/search.json');

    function renderSearchResults(query) {
      var $results = $('#search-results');
      var $empty = $('#search-empty');
      if (!$results.length || !searchIndex) {
        return;
      }
      var q = (query || '').toLowerCase().trim();
      if (!q) {
        $results.empty();
        $empty.prop('hidden', true);
        return;
      }
      var matches = searchIndex.filter(function (item) {
        var haystack = (
          item.title +
          ' ' +
          item.summary +
          ' ' +
          item.type +
          ' ' +
          (item.body || '')
        ).toLowerCase();
        return haystack.indexOf(q) !== -1;
      });
      if (!matches.length) {
        $results.empty();
        $empty.prop('hidden', false);
        return;
      }
      $empty.prop('hidden', true);
      var html = '<ul class="record-list">';
      matches.slice(0, 50).forEach(function (item) {
        html +=
          '<li class="record-card"><h3 class="record-card__title"><a class="record-card__link" href="../' +
          item.url +
          '">' +
          $('<div>').text(item.title).html() +
          '</a></h3><p class="record-card__summary">' +
          $('<div>').text(item.summary).html() +
          '</p><p class="muted">' +
          $('<div>').text(item.type).html() +
          '</p></li>';
      });
      html += '</ul>';
      $results.html(html);
    }

    if ($('#search-query').length) {
      $.getJSON(searchBase, function (data) {
        searchIndex = data;
        var params = new URLSearchParams(window.location.search);
        var initial = params.get('q') || '';
        $('#search-query').val(initial);
        renderSearchResults(initial);
      });
      $('#search-query').on('input', function () {
        renderSearchResults($(this).val());
      });
    }

    var $headerSearch = $('#site-search-input');
    if ($headerSearch.length) {
      $headerSearch.closest('form').on('submit', function (e) {
        e.preventDefault();
        var action = $(this).attr('action');
        var q = $headerSearch.val();
        window.location.href = action + (q ? '?q=' + encodeURIComponent(q) : '');
      });
    }
  });
})(window, document, window.jQuery);
