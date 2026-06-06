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

  function boot() {
    bootHeaderSearch();
    bootScrolledHeader();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
