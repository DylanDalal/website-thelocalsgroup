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

      // Background flip — triggers as the section appears and finishes fast. Each new
      // frame is wiped in top→bottom via --reveal, eased with smoothstep so the paint
      // flows on rather than pops.
      var flipStart = vh * 0.92, flipDist = vh * 0.18;
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
        var bp = Math.min(1, Math.max(0, ((flipStart - flipDist) - top) / (vh * 0.28)));
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
  // A WebP frame sequence (alpha) pinned via CSS position:sticky inside
  // [data-wave-track] (which spans the whole 330vh section). The flat frame list
  // (held duplicates already expanded in PHP) is mapped to scroll over a sub-window
  // of the section, so the wave starts farther down and holds its last frame for a
  // long tail. Frames are preloaded, and we only ever show a frame that has finished
  // decoding — until the exact one is ready we hold the nearest decoded frame, so a
  // fast scroll never lands on a blank.
  function bootWaveScrub() {
    var track = document.querySelector('[data-wave-track]');
    var img = document.querySelector('[data-wave-frames]');
    if (!track || !img) return;

    var frames;
    try { frames = JSON.parse(img.getAttribute('data-frames') || '[]'); }
    catch (e) { frames = []; }
    if (!frames.length) return;
    frames = frames.map(function (src) { return encodeURI(src); });

    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var ready = {};            // url -> true once fully decoded
    var ticking = false, shown = 0;

    function refresh() { if (!ticking) { ticking = true; requestAnimationFrame(update); } }

    // Preload unique frames; mark each ready (and re-evaluate) as it lands.
    var loaders = {};
    frames.forEach(function (src) {
      if (loaders[src]) return;
      var im = new Image();
      loaders[src] = im;
      im.decoding = 'async';
      im.onload = function () { ready[src] = true; refresh(); };
      im.src = src;
      if (im.complete && im.naturalWidth > 0) ready[src] = true;
    });
    // The first frame is already in the DOM <img> from PHP.
    if (img.complete && img.naturalWidth > 0) ready[frames[0]] = true;

    // Nearest decoded frame to index i (prefer i, then fan outward).
    function nearestReady(i) {
      if (ready[frames[i]]) return i;
      for (var d = 1; d < frames.length; d++) {
        if (i - d >= 0 && ready[frames[i - d]]) return i - d;
        if (i + d < frames.length && ready[frames[i + d]]) return i + d;
      }
      return shown;            // nothing ready yet — keep whatever is on screen
    }

    function targetIndex() {
      if (reduce) return frames.length - 1;   // rest on the final frame
      // raw = 0..1 across the section's pinned span (its height minus one viewport).
      var rect = track.getBoundingClientRect();
      var pinned = Math.max(1, track.offsetHeight - window.innerHeight);
      var raw = Math.min(1, Math.max(0, -rect.top / pinned));
      // Begin the scrub almost immediately (little freeze on frame 0), run it to END,
      // then sit on the last frame for the rest — a long tail.
      var START = 0.04, END = 0.6;
      var progress = Math.min(1, Math.max(0, (raw - START) / (END - START)));
      return Math.round(progress * (frames.length - 1));
    }

    function update() {
      ticking = false;
      var i = nearestReady(targetIndex());
      if (i !== shown) { shown = i; img.src = frames[i]; }
    }

    if (!reduce) {
      window.addEventListener('scroll', refresh, { passive: true });
      window.addEventListener('resize', refresh, { passive: true });
    }
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
