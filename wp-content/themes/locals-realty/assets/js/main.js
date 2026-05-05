(function () {
  'use strict';

  // ---------- Visitor preference cookies ----------
  // Set when the visitor expresses interest in a region. Used by
  // locals_lofty_tailored_listing() on the next page render.
  function setPref(name, value) {
    if (!value) return;
    var d = new Date();
    d.setTime(d.getTime() + 30 * 24 * 60 * 60 * 1000);
    document.cookie = name + '=' + encodeURIComponent(value) + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
  }

  // State card clicks → remember the state.
  document.querySelectorAll('.states__item a').forEach(function (a) {
    a.addEventListener('click', function () {
      var label = a.querySelector('.states__label');
      if (label) setPref('locals_pref_state', stateAbbr(label.textContent.trim()));
    });
  });

  // Highlighted-property pill clicks → remember city + state.
  document.querySelectorAll('[data-listings-filters] button[data-filter]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      try {
        var f = JSON.parse(btn.getAttribute('data-filter') || '{}');
        if (f.city)  setPref('locals_pref_city',  f.city);
        if (f.state) setPref('locals_pref_state', f.state);
      } catch (_) {}
    });
  });

  function stateAbbr(name) {
    var map = {
      'florida': 'FL', 'north carolina': 'NC', 'south carolina': 'SC', 'tennessee': 'TN',
      'maine': 'ME', 'pennsylvania': 'PA', 'texas': 'TX', 'georgia': 'GA',
    };
    return map[name.toLowerCase()] || name.toUpperCase().slice(0, 2);
  }


  // Lofty widget auto-resize. Lofty (formerly Chime) iframes post 'updateBodyRect'
  // messages to the parent with their measured content height; we apply it to
  // whichever <iframe class="lofty-widget"> sent the message.
  window.addEventListener('message', function (e) {
    var data;
    try { data = JSON.parse(e.data); } catch (err) { return; }
    if (!data || data.from !== 'chimeSite' || data.event !== 'updateBodyRect') return;
    var frames = document.querySelectorAll('iframe.lofty-widget');
    for (var i = 0; i < frames.length; i++) {
      if (frames[i].contentWindow === e.source) {
        frames[i].style.height = data.data.height + 'px';
        return;
      }
    }
  });


  // Hero parallax-on-scroll: shift the title upward as user scrolls so the
  // image cleanly takes over (per Figma note "moves up as user scrolls").
  const hero = document.querySelector('[data-hero]');
  if (hero) {
    const content = hero.querySelector('.hero__content');
    const onScroll = () => {
      const rect = hero.getBoundingClientRect();
      if (rect.bottom < 0 || rect.top > window.innerHeight) return;
      const progress = Math.min(1, Math.max(0, -rect.top / rect.height));
      if (content) content.style.transform = 'translateY(' + (-progress * 60) + 'px)';
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // Highlighted-properties pill filter — AJAX swap into the listings grid.
  document.querySelectorAll('[data-listings-filters]').forEach(function (group) {
    var grid = document.querySelector('[data-listings-grid]');
    if (!grid) return;

    group.addEventListener('click', function (e) {
      var btn = e.target.closest('button[data-filter]');
      if (!btn) return;
      e.preventDefault();
      if (btn.classList.contains('is-active')) return;

      var filter;
      try { filter = JSON.parse(btn.getAttribute('data-filter') || '{}'); } catch (_) { return; }

      group.querySelectorAll('button[data-filter]').forEach(function (b) { b.classList.remove('is-active'); });
      btn.classList.add('is-active');

      grid.setAttribute('aria-busy', 'true');
      grid.classList.add('is-loading');

      var qs = new URLSearchParams();
      Object.keys(filter).forEach(function (k) {
        if (filter[k] !== undefined && filter[k] !== '') qs.append(k, filter[k]);
      });

      fetch('/wp-json/locals/v1/listings?' + qs.toString(), {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
      })
        .then(function (r) { return r.ok ? r.json() : Promise.reject(r.status); })
        .then(function (data) {
          grid.innerHTML = data.html || '';
        })
        .catch(function () {
          grid.innerHTML = '<p class="listings__empty">Could not load listings. Please retry.</p>';
        })
        .finally(function () {
          grid.setAttribute('aria-busy', 'false');
          grid.classList.remove('is-loading');
        });
    });
  });

  // State page: town list switcher.
  const list = document.querySelector('[data-favorites-list]');
  const detail = document.querySelector('[data-favorites-detail]');
  if (list && detail) {
    list.addEventListener('click', (e) => {
      const li = e.target.closest('li[data-town-id]');
      if (!li) return;
      list.querySelectorAll('li').forEach((el) => el.classList.remove('is-active'));
      li.classList.add('is-active');
      // Future: fetch town detail via REST and swap into `detail`.
    });
  }
})();
