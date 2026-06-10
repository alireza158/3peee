(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var swiperEl = document.querySelector('.works-swiper');

    if (swiperEl && window.Swiper) {
      var slideCount = swiperEl.querySelectorAll('.swiper-slide').length;
      new Swiper(swiperEl, {
        slidesPerView: 1,
        spaceBetween: 18,
        loop: false,
        grabCursor: true,
        watchOverflow: true,
        centeredSlides: false,
        speed: prefersReducedMotion ? 0 : 520,
        threshold: 8,
        touchAngle: 45,
        preventClicks: true,
        preventClicksPropagation: true,
        slideToClickedSlide: false,
        navigation: {
          nextEl: '.works-next',
          prevEl: '.works-prev'
        },
        pagination: {
          el: '.works-swiper .swiper-pagination',
          clickable: true
        },
        observer: true,
        observeParents: true,
        breakpoints: {
          576: { slidesPerView: 1.2, spaceBetween: 18 },
          768: { slidesPerView: 2, spaceBetween: 20 },
          992: { slidesPerView: 3, spaceBetween: 22 },
          1200: { slidesPerView: Math.min(slideCount || 1, 3.4), spaceBetween: 24 }
        }
      });
    }

    initWorkModal(prefersReducedMotion);
  });

  function initWorkModal(prefersReducedMotion) {
    var modalEl = document.getElementById('workModal');
    var frame = document.getElementById('workModalFrame');
    var img = document.getElementById('workModalImg');
    var title = document.getElementById('workModalTitle');
    var sub = document.getElementById('workModalSub');
    var download = document.getElementById('workDownload');

    if (!modalEl || !frame || !img || !window.bootstrap) return;

    var modal = new bootstrap.Modal(modalEl);
    var pointerState = { id: null, x: 0, y: 0, moved: false };

    document.querySelectorAll('.work-card').forEach(function (card) {
      card.addEventListener('pointerdown', function (event) {
        pointerState.id = event.pointerId;
        pointerState.x = event.clientX;
        pointerState.y = event.clientY;
        pointerState.moved = false;
      }, { passive: true });

      card.addEventListener('pointermove', function (event) {
        if (pointerState.id !== event.pointerId) return;
        var dx = Math.abs(event.clientX - pointerState.x);
        var dy = Math.abs(event.clientY - pointerState.y);
        if (dx > 8 || dy > 8) pointerState.moved = true;
      }, { passive: true });

      card.addEventListener('click', function (event) {
        if (pointerState.moved) {
          event.preventDefault();
          event.stopPropagation();
          pointerState.moved = false;
          return;
        }
        openWork(card);
      });
    });

    document.addEventListener('keydown', function (event) {
      var card = event.target && event.target.closest ? event.target.closest('.work-card') : null;
      if (!card || (event.key !== 'Enter' && event.key !== ' ')) return;
      event.preventDefault();
      openWork(card);
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
      img.removeAttribute('src');
      img.alt = 'نمایش نمونه کار';
      frame.scrollTo({ top: 0, left: 0, behavior: 'auto' });
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('overflow');
      document.body.style.removeProperty('padding-right');
      document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
        backdrop.remove();
      });
    });

    function openWork(card) {
      var src = card.getAttribute('data-img') || '';
      var cardTitle = card.getAttribute('data-title') || 'نمایش نمونه‌کار';
      var cardSub = card.getAttribute('data-sub') || '';

      img.src = src;
      img.alt = cardTitle;
      if (download) download.href = src;
      if (title) title.textContent = cardTitle;
      if (sub) sub.textContent = cardSub;
      frame.scrollTo({ top: 0, left: 0, behavior: prefersReducedMotion ? 'auto' : 'smooth' });
      modal.show();
    }
  }
})();
