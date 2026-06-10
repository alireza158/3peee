(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    initWorksGallery();
  });

  function initWorksGallery() {
    var track = document.querySelector('.works-track');
    var lightbox = document.getElementById('workLightbox');
    var lightboxImg = document.getElementById('workLightboxImg');
    var closeBtn = lightbox ? lightbox.querySelector('.work-lightbox-close') : null;
    var previousFocus = null;

    if (track) {
      document.querySelectorAll('.works-prev, .works-next').forEach(function (button) {
        button.addEventListener('click', function () {
          var amount = getScrollAmount(track);
          var isPrev = button.classList.contains('works-prev');
          var direction = document.documentElement.dir === 'rtl' ? -1 : 1;
          var left = (isPrev ? -amount : amount) * direction;
          track.scrollBy({ left: left, behavior: 'smooth' });
        });
      });
    }

    if (!lightbox || !lightboxImg) return;

    document.querySelectorAll('.work-thumb').forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        var item = thumb.closest('.work-item');
        var image = thumb.querySelector('img');
        if (!item || !image) return;
        openLightbox(item.getAttribute('data-img') || image.currentSrc || image.src, item.getAttribute('data-title') || image.alt || 'نمایش نمونه‌کار');
      });
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', closeLightbox);
    }

    lightbox.addEventListener('click', function (event) {
      if (event.target === lightbox || event.target.classList.contains('work-lightbox-inner')) {
        closeLightbox();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && lightbox.classList.contains('is-open')) {
        closeLightbox();
      }
    });

    function openLightbox(src, alt) {
      if (!src) return;
      previousFocus = document.activeElement;
      lightboxImg.src = src;
      lightboxImg.alt = alt;
      lightbox.classList.add('is-open');
      lightbox.setAttribute('aria-hidden', 'false');
      document.body.classList.add('work-lightbox-open');
      if (closeBtn) closeBtn.focus({ preventScroll: true });
    }

    function closeLightbox() {
      lightbox.classList.remove('is-open');
      lightbox.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('work-lightbox-open');
      lightboxImg.removeAttribute('src');
      lightboxImg.alt = 'نمایش نمونه‌کار';
      if (previousFocus && typeof previousFocus.focus === 'function') {
        previousFocus.focus({ preventScroll: true });
      }
    }
  }

  function getScrollAmount(track) {
    var firstItem = track.querySelector('.work-item');
    if (!firstItem) return Math.max(280, Math.round(track.clientWidth * 0.85));
    var styles = window.getComputedStyle(track);
    var gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;
    return Math.round(firstItem.getBoundingClientRect().width + gap);
  }
})();
