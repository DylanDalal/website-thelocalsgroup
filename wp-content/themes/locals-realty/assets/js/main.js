(function () {
  'use strict';

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
