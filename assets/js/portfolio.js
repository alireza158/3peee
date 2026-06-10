(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    initWorksGallery();
  });

  function initWorksGallery() {
    var track = document.querySelector('.works-track');
    var lightbox = document.getElementById('workLightbox');
    var stage = document.getElementById('workLightboxStage');
    var lightboxImg = document.getElementById('workLightboxImg');
    var closeBtn = lightbox ? lightbox.querySelector('.work-lightbox-close') : null;
    var backdrop = lightbox ? lightbox.querySelector('.work-lightbox-backdrop') : null;
    var previousFocus = null;

    var scale = 1;
    var minScale = 1;
    var maxScale = 5;
    var translateX = 0;
    var translateY = 0;
    var isDragging = false;
    var startX = 0;
    var startY = 0;
    var lastX = 0;
    var lastY = 0;
    var initialPinchDistance = 0;
    var startScale = 1;
    var didDrag = false;

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

    if (!lightbox || !stage || !lightboxImg) return;

    document.querySelectorAll('.work-thumb').forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        var item = thumb.closest('.work-item');
        var image = thumb.querySelector('img');
        if (!item || !image) return;
        var fullSrc = image.dataset.fullSrc || item.getAttribute('data-full-src') || image.currentSrc || image.src;
        openLightbox(fullSrc, item.getAttribute('data-title') || image.alt || 'نمایش نمونه‌کار');
      });
    });

    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
    if (backdrop) backdrop.addEventListener('click', closeLightbox);

    stage.addEventListener('click', function (event) {
      if (event.target === stage && !didDrag) closeLightbox();
      didDrag = false;
    });

    stage.addEventListener('wheel', function (event) {
      if (!lightbox.classList.contains('is-open')) return;
      event.preventDefault();

      var oldScale = scale;
      var zoomFactor = event.deltaY < 0 ? 1.12 : 0.88;
      var nextScale = clamp(scale * zoomFactor, minScale, maxScale);
      if (nextScale === scale) return;

      var rect = stage.getBoundingClientRect();
      var pointerX = event.clientX - rect.left - rect.width / 2;
      var pointerY = event.clientY - rect.top - rect.height / 2;
      var ratio = nextScale / oldScale;

      translateX = pointerX - (pointerX - translateX) * ratio;
      translateY = pointerY - (pointerY - translateY) * ratio;
      scale = nextScale;
      normalizeTransform();
      updateTransform();
    }, { passive: false });

    stage.addEventListener('mousedown', function (event) {
      if (scale <= minScale || event.button !== 0) return;
      isDragging = true;
      didDrag = false;
      startX = event.clientX;
      startY = event.clientY;
      lastX = event.clientX;
      lastY = event.clientY;
      lightboxImg.classList.add('is-dragging');
    });

    window.addEventListener('mousemove', function (event) {
      if (!isDragging || scale <= minScale) return;
      var dx = event.clientX - lastX;
      var dy = event.clientY - lastY;
      if (Math.abs(event.clientX - startX) > 2 || Math.abs(event.clientY - startY) > 2) didDrag = true;
      translateX += dx;
      translateY += dy;
      lastX = event.clientX;
      lastY = event.clientY;
      updateTransform();
    });

    window.addEventListener('mouseup', stopDragging);
    stage.addEventListener('mouseleave', stopDragging);
    lightboxImg.addEventListener('dragstart', function (event) {
      event.preventDefault();
    });

    stage.addEventListener('touchstart', function (event) {
      if (!lightbox.classList.contains('is-open')) return;

      if (event.touches.length === 2) {
        initialPinchDistance = getTouchDistance(event.touches[0], event.touches[1]);
        startScale = scale;
        isDragging = false;
        lightboxImg.classList.remove('is-dragging');
      } else if (event.touches.length === 1 && scale > minScale) {
        isDragging = true;
        didDrag = false;
        startX = event.touches[0].clientX;
        startY = event.touches[0].clientY;
        lastX = event.touches[0].clientX;
        lastY = event.touches[0].clientY;
        lightboxImg.classList.add('is-dragging');
      }
    }, { passive: true });

    stage.addEventListener('touchmove', function (event) {
      if (!lightbox.classList.contains('is-open')) return;

      if (event.touches.length === 2) {
        event.preventDefault();
        var newDistance = getTouchDistance(event.touches[0], event.touches[1]);
        if (!initialPinchDistance) initialPinchDistance = newDistance;
        scale = clamp(startScale * newDistance / initialPinchDistance, minScale, maxScale);
        normalizeTransform();
        updateTransform();
      } else if (event.touches.length === 1 && isDragging && scale > minScale) {
        event.preventDefault();
        var touch = event.touches[0];
        var dx = touch.clientX - lastX;
        var dy = touch.clientY - lastY;
        if (Math.abs(touch.clientX - startX) > 2 || Math.abs(touch.clientY - startY) > 2) didDrag = true;
        translateX += dx;
        translateY += dy;
        lastX = touch.clientX;
        lastY = touch.clientY;
        updateTransform();
      }
    }, { passive: false });

    stage.addEventListener('touchend', function (event) {
      if (event.touches.length === 0) {
        stopDragging();
        settleScale();
        initialPinchDistance = 0;
        return;
      }

      if (event.touches.length === 1 && scale > minScale) {
        isDragging = true;
        lastX = event.touches[0].clientX;
        lastY = event.touches[0].clientY;
        initialPinchDistance = 0;
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && lightbox.classList.contains('is-open')) closeLightbox();
    });

    function openLightbox(src, alt) {
      if (!src) return;
      previousFocus = document.activeElement;
      lightbox.classList.remove('is-open');
      lightbox.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('has-lightbox');
      document.body.classList.remove('work-lightbox-open');
      resetZoom();
      lightboxImg.removeAttribute('src');
      lightboxImg.alt = alt;
      lightboxImg.onload = revealLoadedImage;
      lightboxImg.onerror = revealLoadedImage;
      lightboxImg.src = src;
      if (lightboxImg.complete && lightboxImg.naturalWidth > 0) {
        revealLoadedImage();
      }
    }


    function revealLoadedImage() {
      lightboxImg.onload = null;
      lightboxImg.onerror = null;
      resetZoom();
      lightbox.classList.add('is-open');
      lightbox.setAttribute('aria-hidden', 'false');
      document.body.classList.add('has-lightbox');
      if (closeBtn) closeBtn.focus({ preventScroll: true });
    }

    function closeLightbox() {
      lightbox.classList.remove('is-open');
      lightbox.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('has-lightbox');
      document.body.classList.remove('work-lightbox-open');
      stopDragging();
      resetZoom();
      lightboxImg.onload = null;
      lightboxImg.onerror = null;
      lightboxImg.removeAttribute('src');
      lightboxImg.alt = 'نمایش نمونه‌کار';
      if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus({ preventScroll: true });
    }

    function updateTransform() {
      lightboxImg.style.transform = 'translate3d(' + translateX + 'px, ' + translateY + 'px, 0) scale(' + scale + ')';
    }

    function resetZoom() {
      scale = minScale;
      translateX = 0;
      translateY = 0;
      isDragging = false;
      didDrag = false;
      initialPinchDistance = 0;
      startScale = minScale;
      lightboxImg.classList.remove('is-dragging');
      updateTransform();
    }

    function normalizeTransform() {
      if (scale <= minScale) {
        scale = minScale;
        translateX = 0;
        translateY = 0;
      }
    }

    function settleScale() {
      if (scale <= 1.02) {
        scale = minScale;
        translateX = 0;
        translateY = 0;
        updateTransform();
      }
    }

    function stopDragging() {
      isDragging = false;
      lightboxImg.classList.remove('is-dragging');
    }
  }

  function getScrollAmount(track) {
    var firstItem = track.querySelector('.work-item');
    if (!firstItem) return Math.max(280, Math.round(track.clientWidth * 0.85));
    var styles = window.getComputedStyle(track);
    var gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;
    return Math.round(firstItem.getBoundingClientRect().width + gap);
  }

  function getTouchDistance(touchA, touchB) {
    var dx = touchA.clientX - touchB.clientX;
    var dy = touchA.clientY - touchB.clientY;
    return Math.sqrt(dx * dx + dy * dy);
  }

  function clamp(value, min, max) {
    return Math.min(max, Math.max(min, value));
  }
})();
