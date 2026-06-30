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

  // ---- Painted "Get Approved" scene (background1 flipbook → brush cycle) ----
  // Section 1 (.tlg-paint--scene) stacks four frames (background1-1…4) which
  // crossfade 1→4 as the section scrolls in (a quick flip). Right where that flip
  // finishes, a brush <img> (frames from /brushes) is cycled by continued scroll.
  function bootPaintScene() {
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var scene = document.querySelector('[data-paint-scene]');
    if (!scene) return;

    var frames = Array.prototype.slice.call(scene.querySelectorAll('.tlg-paint__frame'));
    var brush = scene.querySelector('[data-brush-frames]');

    var bFrames = [];
    if (brush) {
      try { bFrames = JSON.parse(brush.getAttribute('data-frames') || '[]'); }
      catch (e) { bFrames = []; }
      bFrames = bFrames.map(function (s) { return encodeURI(s); });
    }

    if (reduce) {
      if (bFrames.length) brush.src = bFrames[bFrames.length - 1]; // final brush
      return;                                                      // CSS shows final bg frame
    }
    if (!frames.length && !bFrames.length) return;

    // Preload brush frames and track readiness so a fast scroll never blanks it.
    var bReady = {}, bShown = 0;
    bFrames.forEach(function (src) {
      if (bReady[src]) return;
      var im = new Image();
      im.decoding = 'async';
      im.onload = function () { bReady[src] = true; onScroll(); };
      im.src = src;
      if (im.complete && im.naturalWidth > 0) bReady[src] = true;
    });
    if (brush && brush.complete && brush.naturalWidth > 0 && bFrames[0]) bReady[bFrames[0]] = true;
    function nearestBrush(i) {
      if (bReady[bFrames[i]]) return i;
      for (var d = 1; d < bFrames.length; d++) {
        if (i - d >= 0 && bReady[bFrames[i - d]]) return i - d;
        if (i + d < bFrames.length && bReady[bFrames[i + d]]) return i + d;
      }
      return bShown;
    }

    var ticking = false;
    function update() {
      ticking = false;
      var vh = window.innerHeight;
      var top = scene.getBoundingClientRect().top;

      // Background flip — triggers as the section appears and finishes fast.
      var flipStart = vh * 0.92, flipDist = vh * 0.18;
      if (frames.length) {
        var progress = Math.min(1, Math.max(0, (flipStart - top) / flipDist));
        var total = frames.length;
        var f = progress * (total - 1);
        var active = Math.min(total - 1, Math.floor(f));
        var t = f - active;
        var fade = Math.min(1, Math.max(0, (t - 0.375) / 0.25));
        for (var i = 0; i < total; i++) {
          var opacity = 0;
          if (i === active) opacity = 1;
          else if (i === active + 1) opacity = fade;
          frames[i].style.opacity = opacity.toFixed(3);
        }
      }

      // Brush cycle — begins where the flip ends, runs quickly over the next ~0.28vh.
      if (brush && bFrames.length) {
        var bp = Math.min(1, Math.max(0, ((flipStart - flipDist) - top) / (vh * 0.28)));
        var bi = nearestBrush(Math.round(bp * (bFrames.length - 1)));
        if (bi !== bShown) { bShown = bi; brush.src = bFrames[bi]; }
      }
    }
    function onScroll() { if (!ticking) { ticking = true; requestAnimationFrame(update); } }
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    update();
  }

  // ---- Wave scrub (background2 foreground) ----
  // A WebP frame sequence (alpha) pinned via CSS position:sticky inside
  // [data-wave-track]. The flat frame list (held duplicates already expanded in PHP)
  // is mapped to scroll: as the 150vh track passes the viewport we swap the <img>
  // src across the sequence. Frames are preloaded, and we only ever show a frame
  // that has finished decoding — until the exact one is ready we hold the nearest
  // decoded frame, so a fast scroll never lands on a blank.
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
      // Pinned span: the track (150vh) minus the sticky child (100vh) = 50vh of scroll.
      var rect = track.getBoundingClientRect();
      var pinned = Math.max(1, track.offsetHeight - window.innerHeight);
      var progress = Math.min(1, Math.max(0, -rect.top / pinned));
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
