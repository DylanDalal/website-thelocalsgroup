/* The Locals Group — dark homepage behaviour.
 * Loaded only on the front page (see inc/enqueue.php). Reveal-on-scroll and the
 * mobile drawer are handled by main.js; this adds the collapsing header search
 * and the scrolled-header background toggle. */
(function () {
  'use strict';

  // ---- Collapsing header search (icon -> expanding field) ----
  // On the dark home header the search pill is collapsed to its submit icon.
  // First click opens + focuses the field; once open, the button submits.
  function bootHeaderSearch() {
    var header = document.querySelector('[data-site-header]');
    var form = header && header.querySelector('[data-header-search]');
    if (!header || !form) return;

    var input = form.querySelector('input');
    var button = form.querySelector('button');
    if (button) button.setAttribute('data-header-search-toggle', '');

    function open() {
      header.classList.add('is-search-open');
      if (input) input.focus();
    }
    function close() {
      if (input && input.value.trim() !== '') return; // keep open if mid-query
      header.classList.remove('is-search-open');
    }

    if (button) {
      button.addEventListener('click', function (e) {
        if (!header.classList.contains('is-search-open')) {
          e.preventDefault();
          open();
        }
        // else: allow native submit.
      });
    }

    document.addEventListener('click', function (e) {
      if (!header.classList.contains('is-search-open')) return;
      if (!form.contains(e.target)) close();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { header.classList.remove('is-search-open'); }
    });
  }

  // ---- Scrolled header background ----
  function bootScrolledHeader() {
    var body = document.body;
    var ticking = false;
    function update() {
      ticking = false;
      body.classList.toggle('is-scrolled', window.scrollY > 24);
    }
    function onScroll() {
      if (!ticking) { ticking = true; requestAnimationFrame(update); }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    update();
  }

  // ---- Hero market names highlight their state on the map ----
  // Hovering/focusing a "[data-state]" name toggles .is-active on the matching
  // SVG path (#FL/#NC/#SC/#TN) so it lights up beyond its idle glow.
  function bootStateHighlight() {
    var map = document.querySelector('.tlg-hero__usmap');
    var names = document.querySelectorAll('.tlg-hero__state[data-state]');
    if (!map || !names.length) return;

    names.forEach(function (name) {
      var code = name.getAttribute('data-state');
      var shape = map.querySelector('#' + code);
      if (!shape) return;
      var on = function () { shape.classList.add('is-active'); };
      var off = function () { shape.classList.remove('is-active'); };
      name.addEventListener('mouseenter', on);
      name.addEventListener('mouseleave', off);
      name.addEventListener('focus', on);
      name.addEventListener('blur', off);
    });
  }

  // ---- Map parallax ----
  // Drift the US-map watermark slightly slower than the page as the saga
  // scrolls, for a touch of depth. We only set a CSS variable (--map-shift); the
  // map's own translateY(-50%) centring is preserved via calc() in the CSS.
  // Skipped when reduced motion is requested.
  function bootMapParallax() {
    var section = document.querySelector('.tlg-saga');
    var map = section && section.querySelector('.tlg-hero__usmap');
    if (!map) return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var ticking = false;
    function update() {
      ticking = false;
      // -rect.top grows as the section scrolls up past the viewport top; cap the
      // drift so it stays subtle.
      var top = section.getBoundingClientRect().top;
      var shift = Math.min(Math.max(-top * 0.1, 0), window.innerHeight * 0.18);
      map.style.setProperty('--map-shift', shift.toFixed(1) + 'px');
    }
    function onScroll() {
      if (!ticking) { ticking = true; requestAnimationFrame(update); }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    update();
  }

  // ---- Painted "Get Approved" scene (background1 flipbook → brush wipe) ----
  // Section 1 (.tlg-paint--scene) stacks four frames (background1-1…4) that wipe in
  // 1→4 as the section scrolls in (a quick flip). Right where that flip finishes, the
  // stacked brush frames (from /brushes) wipe in by continued scroll. Both use the
  // same top→bottom directional reveal (--reveal), set on each frame below.
  function bootPaintScene() {
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var scene = document.querySelector('[data-paint-scene]');
    if (!scene) return;

    var frames = Array.prototype.slice.call(scene.querySelectorAll('.tlg-paint__frame'));
    var brushWrap = scene.querySelector('[data-brush-frames]');
    var bFrames = brushWrap
      ? Array.prototype.slice.call(brushWrap.querySelectorAll('.tlg-paint__brush-frame'))
      : [];
    var phone = scene.querySelector('[data-phone]');

    if (reduce) return;   // CSS reveals the final background/brush frames + upright phone
    if (!frames.length && !bFrames.length && !phone) return;

    var ticking = false;
    function update() {
      ticking = false;
      var vh = window.innerHeight;
      var top = scene.getBoundingClientRect().top;

      // Phone entrance — pivots from bottom-left, scaling 0.6→1 and rotating -20°→0 as
      // the section scrolls in.
      if (phone) {
        var pp = Math.min(1, Math.max(0, (vh * 0.95 - top) / (vh * 0.5)));
        var pe = pp * pp * (3 - 2 * pp);                 // smoothstep
        var ps = 0.6 + 0.4 * pe;
        var pr = -20 * (1 - pe);
        phone.style.transform = 'rotate(' + pr.toFixed(2) + 'deg) scale(' + ps.toFixed(3) + ')';
      }

      // Background flip — keyed to the scene's top edge (px): the paint wipes in as the
      // scene scrolls up from `flipStart` (top near the viewport bottom) over `flipDist`.
      // A wider window than before so it reliably completes on tall/large screens.
      var flipStart = vh * 0.95, flipDist = vh * 0.42;
      if (frames.length) {
        var progress = Math.min(1, Math.max(0, (flipStart - top) / flipDist));
        var total = frames.length;
        var f = progress * (total - 1);
        var active = Math.min(total - 1, Math.floor(f));
        var t = f - active;
        var e = t * t * (3 - 2 * t);          // smoothstep ease
        for (var i = 0; i < total; i++) {
          var reveal = 0;
          if (i <= active) reveal = 116;        // already painted in
          else if (i === active + 1) reveal = e * 116;   // wiping in now
          frames[i].style.setProperty('--reveal', reveal.toFixed(1));
        }
      }

      // Brush wipe — five states (empty → 1 → 2 → 3 → 4). Begins where the flip ends
      // and starts empty; each frame wipes in top→bottom over the one before it.
      if (bFrames.length) {
        var bp = Math.min(1, Math.max(0, ((flipStart - flipDist) - top) / (vh * 0.4)));
        var bTotal = bFrames.length;
        var bf = bp * bTotal;                 // 0..total; floor() = the frame wiping in
        var bFloor = Math.floor(bf);
        var bt = bf - bFloor;
        var be = bt * bt * (3 - 2 * bt);      // smoothstep
        for (var k = 0; k < bTotal; k++) {
          var br = 0;
          if (k < bFloor) br = 116;                  // already painted
          else if (k === bFloor) br = be * 116;      // wiping in now
          bFrames[k].style.setProperty('--reveal', br.toFixed(1));
        }
      }
    }
    function onScroll() { if (!ticking) { ticking = true; requestAnimationFrame(update); } }
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    update();
  }

  // ---- Wave scrub (background2 foreground) ----
  // All unique frames are stacked (CSS, opacity 0) inside the sticky [data-wave-track].
  // Scrubbing only toggles opacity — no img.src swap — so there is no per-frame decode
  // stutter. Frames are loaded PROGRESSIVELY as the user scrolls toward the wave (plus a
  // slow idle drip), and we only ever reveal a decoded frame (nearest-loaded fallback).
  function bootWaveScrub() {
    var track = document.querySelector('[data-wave-track]');
    if (!track) return;
    var frames = Array.prototype.slice.call(track.querySelectorAll('.tlg-paint__wave-frame'));
    if (!frames.length) return;

    var slots;
    try { slots = JSON.parse(track.getAttribute('data-slots') || '[]'); }
    catch (e) { slots = []; }
    if (!slots.length) slots = frames.map(function (_, i) { return i; });

    var back  = track.querySelector('[data-wave-back]');
    var front = track.querySelector('[data-wave-front]');
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var total = frames.length;
    var SMASH = Math.max(1, Math.round(total * 0.62));   // focus smashes from back→front here
    var loaded = frames.map(function () { return false; });   // only true once DECODED
    var ticking = false, shown = 0, nextLoad = 0;

    function refresh() { if (!ticking) { ticking = true; requestAnimationFrame(update); } }

    // A frame is only eligible to show once it has finished DECODING (not just loading),
    // otherwise toggling opacity to it decodes-on-paint = the empty flash.
    function markReady(idx, el) {
      var done = function () { loaded[idx] = true; refresh(); };
      if (el.decode) { el.decode().then(done).catch(done); } else { done(); }
    }
    // Assign src to frames 0..n (in order) so they decode ahead of being shown.
    function loadUpTo(n) {
      while (nextLoad <= n && nextLoad < total) {
        var f = frames[nextLoad];
        if (!f.getAttribute('src') && f.getAttribute('data-src')) {
          (function (idx, el) {
            el.addEventListener('load', function () { markReady(idx, el); });
            el.src = el.getAttribute('data-src');
          })(nextLoad, f);
        } else if (f.complete && f.naturalWidth > 0) { markReady(nextLoad, f); }
        nextLoad++;
      }
    }

    function nearestLoaded(i) {
      if (loaded[i]) return i;
      for (var d = 1; d < total; d++) {
        if (i - d >= 0 && loaded[i - d]) return i - d;
        if (i + d < total && loaded[i + d]) return i + d;
      }
      return shown;            // nothing ready yet — keep whatever is on screen
    }
    function show(i) {
      if (i === shown) return;
      // visibility (not opacity) so only the ONE active frame is ever painted/composited
      // — the other 12 large frames cost nothing. This is what kills the scroll lag.
      frames[shown].style.visibility = 'hidden';
      frames[i].style.visibility = 'visible';
      shown = i;
    }

    function update() {
      ticking = false;
      var vh = window.innerHeight;
      var rect = track.getBoundingClientRect();

      // Progressive preload: how far are we (0→1) from page top to the wave appearing?
      var trackTopDoc = rect.top + window.pageYOffset;
      var loadFrac = Math.min(1, Math.max(0, window.pageYOffset / Math.max(1, trackTopDoc - vh)));
      loadUpTo(Math.ceil(loadFrac * (total - 1)));

      var u;
      if (reduce) {
        u = total - 1;                       // rest on the final frame
      } else {
        var pinned = Math.max(1, track.offsetHeight - vh);
        var raw = Math.min(1, Math.max(0, -rect.top / pinned));
        var START = 0.04, END = 0.6;         // small freeze, scrub, long last-frame tail
        var p = Math.min(1, Math.max(0, (raw - START) / (END - START)));
        u = slots[Math.round(p * (slots.length - 1))];
      }
      // Smash: hard-cut focus from background to foreground once the wave covers the
      // screen (no transition — it's a smash, synced to the scrub).
      var past = u >= SMASH;
      if (back)  back.style.opacity  = past ? '0' : '1';
      if (front) front.style.opacity = past ? '1' : '0';
      show(nearestLoaded(u));
    }

    window.addEventListener('scroll', refresh, { passive: true });
    window.addEventListener('resize', refresh, { passive: true });
    // Slow idle drip so even an idle user has every frame ready before they reach it.
    var drip = setInterval(function () {
      loadUpTo(nextLoad);
      if (nextLoad >= total) clearInterval(drip);
    }, 350);
    update();
  }

  function boot() {
    bootHeaderSearch();
    bootScrolledHeader();
    bootStateHighlight();
    bootMapParallax();
    bootPaintScene();
    bootWaveScrub();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
