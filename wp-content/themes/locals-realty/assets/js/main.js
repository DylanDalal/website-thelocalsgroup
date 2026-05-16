(function () {
  'use strict';

  console.info('[locals] main.js loaded', new Date().toISOString());

  const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ---------- Visitor preference cookies ----------
  // Set when the visitor expresses interest in a region. Used by
  // locals_lofty_tailored_listing() on the next page render.
  function setPref(name, value) {
    if (!value) return;
    const d = new Date();
    d.setTime(d.getTime() + 30 * 24 * 60 * 60 * 1000);
    document.cookie = name + '=' + encodeURIComponent(value) + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
  }

  function stateAbbr(name) {
    const map = {
      'florida': 'FL', 'north carolina': 'NC', 'south carolina': 'SC', 'tennessee': 'TN',
      'maine': 'ME', 'pennsylvania': 'PA', 'texas': 'TX', 'georgia': 'GA',
    };
    return map[name.toLowerCase()] || name.toUpperCase().slice(0, 2);
  }

  document.querySelectorAll('.states__item a').forEach(function (a) {
    a.addEventListener('click', function () {
      const label = a.querySelector('.states__label');
      if (label) setPref('locals_pref_state', stateAbbr(label.textContent.trim()));
    });
  });

  document.querySelectorAll('[data-listings-filters] button[data-filter]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      try {
        const f = JSON.parse(btn.getAttribute('data-filter') || '{}');
        if (f.city)  setPref('locals_pref_city',  f.city);
        if (f.state) setPref('locals_pref_state', f.state);
      } catch (_) {}
    });
  });

  // ---------- Lofty widget auto-resize ----------
  // Lofty (formerly Chime) iframes post 'updateBodyRect' messages with their
  // measured content height; apply it to whichever lofty-widget iframe sent it.
  window.addEventListener('message', function (e) {
    let data;
    try { data = JSON.parse(e.data); } catch (err) { return; }
    if (!data || data.from !== 'chimeSite' || data.event !== 'updateBodyRect') return;
    const frames = document.querySelectorAll('iframe.lofty-widget');
    for (let i = 0; i < frames.length; i++) {
      if (frames[i].contentWindow === e.source) {
        frames[i].style.height = data.data.height + 'px';
        return;
      }
    }
  });

  // ---------- Reveal-on-scroll ----------
  // Elements with [data-reveal] start hidden (CSS) and animate in once they
  // intersect 12% of the viewport. Stagger children by reading [data-reveal-stagger]
  // and setting a CSS var --reveal-delay per child.
  function bootReveals() {
    const targets = document.querySelectorAll('[data-reveal]');
    if (!targets.length) return;

    if (reduceMotion || !('IntersectionObserver' in window)) {
      targets.forEach((el) => el.classList.add('is-revealed'));
      return;
    }

    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          entry.target.classList.add('is-revealed');
          io.unobserve(entry.target);
        });
      },
      { rootMargin: '0px 0px -12% 0px', threshold: 0.08 }
    );

    targets.forEach((el) => {
      const stagger = parseFloat(el.getAttribute('data-reveal-stagger') || '0');
      if (stagger > 0) {
        const children = el.children;
        for (let i = 0; i < children.length; i++) {
          children[i].style.setProperty('--reveal-delay', (i * stagger).toFixed(3) + 's');
          children[i].classList.add('reveal-child');
        }
      }
      io.observe(el);
    });
  }

  // ---------- Hero scroll-morph ----------
  // Hero title fades and lifts as the hero scrolls out. The header is always
  // the solid/white variant, so no header-search crossfade or `is-scrolled`
  // toggle is needed.
  function bootHeroMorph() {
    const hero = document.querySelector('[data-hero]');
    if (!hero) return;
    const title = hero.querySelector('[data-hero-title]');

    let ticking = false;
    function update() {
      ticking = false;
      const rect = hero.getBoundingClientRect();
      const total = rect.height || 1;
      const progress = Math.min(1, Math.max(0, -rect.top / total));

      if (title) {
        const t = Math.min(1, progress * 1.4);
        title.style.setProperty('--hero-title-opacity', String(1 - t));
        title.style.setProperty('--hero-title-y', (-t * 60).toFixed(1) + 'px');
        title.style.setProperty('--hero-title-scale', (1 - t * 0.05).toFixed(3));
      }
    }

    function onScroll() {
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(update);
      }
    }

    if (reduceMotion) return;

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    update();
  }

  // ---------- Flipbook (scroll-scrubbed background frames) ----------
  // Maps scroll progress through-and-past the section to a 0..1 range:
  //   0 = top of section just entered the viewport bottom
  //   1 = bottom of section just left the viewport top
  function bootFlipbook() {
    const section = document.querySelector('[data-flipbook]');
    if (!section) return;
    const frames = Array.from(section.querySelectorAll('.hero-flipbook__frame'));
    if (frames.length < 2) return;

    if (reduceMotion) {
      frames.forEach((f, i) => { f.style.opacity = i === 0 ? '1' : '0'; });
      return;
    }

    const total = frames.length;
    let ticking = false;

    function update() {
      ticking = false;
      const rect = section.getBoundingClientRect();
      const vh = window.innerHeight;
      // Activate as soon as the section pokes above the fold (~85% of vh),
      // and complete the full 6-frame run inside ~55% of a viewport's worth
      // of scroll so the sequence feels brisk rather than dragged out.
      const startAt = vh * 0.6;
      const distance = vh * 0.55;
      const progress = Math.min(1, Math.max(0, (startAt - rect.top) / distance));

      const f = progress * (total - 1);
      const active = Math.min(total - 1, Math.floor(f));
      const t = f - active;
      // Hold each frame for the first/last 37.5% of its scroll range and
      // crunch the fade into the middle 25% so transitions feel snappy.
      const tFade = Math.min(1, Math.max(0, (t - 0.375) / 0.25));

      // Keep the active frame fully opaque underneath and fade the *next*
      // one in on top of it. DOM order = paint order, so later frames cover
      // earlier ones. Avoids the body bg flashing through during a crossfade.
      frames.forEach((frame, i) => {
        let opacity = 0;
        let scale = 1.03;
        if (i === active) {
          opacity = 1;
          scale = 1.03 + t * 0.03;
        } else if (i === active + 1) {
          opacity = tFade;
          scale = 1.01 + t * 0.02;
        }
        frame.style.opacity = opacity.toFixed(3);
        frame.style.transform = `scale(${scale.toFixed(3)})`;
      });
    }

    function onScroll() {
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(update);
      }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    update();
  }

  // ---------- Mobile nav drawer ----------
  function bootMobileNav() {
    const toggle = document.querySelector('[data-nav-toggle]');
    const drawer = document.querySelector('[data-nav-drawer]');
    if (!toggle || !drawer) return;

    function setOpen(open) {
      toggle.setAttribute('aria-expanded', String(open));
      drawer.classList.toggle('is-open', open);
      document.documentElement.classList.toggle('nav-open', open);
    }

    toggle.addEventListener('click', () => {
      const open = toggle.getAttribute('aria-expanded') === 'true';
      setOpen(!open);
    });

    drawer.addEventListener('click', (e) => {
      if (e.target.tagName === 'A') setOpen(false);
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') setOpen(false);
    });
  }

  // ---------- Highlights pill filter (AJAX) + carousel ----------
  function bootHighlightsFilter() {
    const WINDOW_SIZE = 3;

    function hydrateCarousel(carousel) {
      const grid = carousel.querySelector('[data-listings-grid] .listings-grid');
      const prev = carousel.querySelector('[data-carousel-prev]');
      const next = carousel.querySelector('[data-carousel-next]');
      if (!grid) {
        if (prev) prev.hidden = true;
        if (next) next.hidden = true;
        return;
      }

      const cards = Array.from(grid.querySelectorAll(':scope > .listing-card'));
      const total = cards.length;
      const hasOverflow = total > WINDOW_SIZE;

      if (prev) prev.hidden = !hasOverflow;
      if (next) next.hidden = !hasOverflow;

      let start = 0;

      function render(direction) {
        const end = Math.min(start + WINDOW_SIZE, total);
        const featuredIdx = start + Math.min(1, end - start - 1);
        cards.forEach((card, i) => {
          const visible = i >= start && i < end;
          card.classList.toggle('is-visible', visible);
          card.classList.toggle('is-featured', i === featuredIdx);
        });
        if (direction) {
          grid.classList.remove('is-paging-next', 'is-paging-prev');
          // force reflow so the animation restarts
          void grid.offsetWidth;
          grid.classList.add(direction === 'next' ? 'is-paging-next' : 'is-paging-prev');
        }
        if (prev) prev.disabled = start <= 0;
        if (next) next.disabled = start >= total - WINDOW_SIZE;
      }

      if (prev) {
        prev.onclick = () => {
          if (start <= 0) return;
          start = Math.max(0, start - 1);
          render('prev');
        };
      }
      if (next) {
        next.onclick = () => {
          if (start >= total - WINDOW_SIZE) return;
          start = Math.min(total - WINDOW_SIZE, start + 1);
          render('next');
        };
      }

      render();
    }

    document.querySelectorAll('[data-listings-filters]').forEach(function (group) {
      const section = group.closest('.highlights') || group.parentElement;
      const carousel = (section || document).querySelector('[data-carousel]');
      if (carousel) hydrateCarousel(carousel);
    });
    console.info('[locals] highlights filters hydrated:',
      document.querySelectorAll('[data-listings-filters]').length);

    // Document-level delegation so the handler attaches regardless of DOM
    // timing or where a [data-listings-filters] group lives in the page.
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('button[data-filter]');
      if (!btn) return;
      const group = btn.closest('[data-listings-filters]');
      if (!group) return;
      const section = group.closest('.highlights') || group.parentElement;
      const carousel = section ? section.querySelector('[data-carousel]') : null;
      const grid = section ? section.querySelector('[data-listings-grid]') : null;
      if (!grid || !carousel) {
        console.warn('[locals] pill click — missing carousel/grid', { section, carousel, grid });
        return;
      }

      e.preventDefault();
      if (btn.classList.contains('is-active')) {
        console.info('[locals] pill click — already active, ignoring');
        return;
      }

      let filter;
      try { filter = JSON.parse(btn.getAttribute('data-filter') || '{}'); }
      catch (err) { console.warn('[locals] bad data-filter:', err); return; }

      group.querySelectorAll('button[data-filter]').forEach((b) => b.classList.remove('is-active'));
      btn.classList.add('is-active');

      grid.setAttribute('aria-busy', 'true');
      grid.classList.add('is-loading');

      const qs = new URLSearchParams();
      Object.keys(filter).forEach((k) => {
        if (filter[k] !== undefined && filter[k] !== '') qs.append(k, filter[k]);
      });
      qs.append('_', Date.now());

      const url = '/wp-json/locals/v1/listings?' + qs.toString();
      console.info('[locals] pill click — fetching', url);

      fetch(url, {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
        cache: 'no-store',
      })
        .then((r) => {
          console.info('[locals] response status', r.status);
          if (!r.ok) throw new Error('HTTP ' + r.status);
          return r.json();
        })
        .then((data) => {
          console.info('[locals] response html bytes', (data && data.html || '').length);
          const html = (data && typeof data.html === 'string') ? data.html : '';
          grid.innerHTML = html || '<p class="listings__empty">No active listings here right now.</p>';
        })
        .catch((err) => {
          console.warn('[locals] listings fetch failed:', err);
          grid.innerHTML = '<p class="listings__empty">Could not load listings (' + (err && err.message || 'unknown') + '). Please retry.</p>';
        })
        .finally(() => {
          grid.setAttribute('aria-busy', 'false');
          grid.classList.remove('is-loading');
          hydrateCarousel(carousel);
        });
    });
  }

  // ---------- State page favorites switcher ----------
  function bootFavorites() {
    const list = document.querySelector('[data-favorites-list]');
    const detail = document.querySelector('[data-favorites-detail]');
    if (!list || !detail) return;
    list.addEventListener('click', (e) => {
      const li = e.target.closest('li[data-town-id]');
      if (!li) return;
      list.querySelectorAll('li').forEach((el) => el.classList.remove('is-active'));
      li.classList.add('is-active');
      // Future: fetch town detail via REST and swap into `detail`.
    });
  }

  // ---------- Boot ----------
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
  function boot() {
    bootReveals();
    bootHeroMorph();
    bootFlipbook();
    bootMobileNav();
    bootHighlightsFilter();
    bootFavorites();
  }
})();
