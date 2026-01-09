(function () {
  function qs(sel, root) { return (root || document).querySelector(sel); }
  function qsa(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

  function openModal(modal) {
    if (!modal) return;
    modal.classList.remove('is-hidden');
    document.body.style.overflow = 'hidden';
  }
  function closeModal(modal) {
    if (!modal) return;
    modal.classList.add('is-hidden');
    document.body.style.overflow = '';

    // also close viewer if present
    var viewer = qs('[data-viewer]', modal);
    if (viewer) viewer.classList.add('is-hidden');
    var content = qs('[data-viewer-content]', modal);
    if (content) content.innerHTML = '';
  }

  function setActiveTab(modal, filter) {
    qsa('.media-tab', modal).forEach(function (btn) {
      btn.classList.toggle('is-active', btn.getAttribute('data-filter') === filter);
    });

    qsa('.media-item', modal).forEach(function (item) {
      var kind = item.getAttribute('data-kind') || 'all';
      var show = (filter === 'all') || (kind === filter);
      item.style.display = show ? '' : 'none';
    });
  }

  function openViewer(modal, type, src) {
    var viewer = qs('[data-viewer]', modal);
    var content = qs('[data-viewer-content]', modal);
    if (!viewer || !content) return;

    content.innerHTML = '';

    if (type === 'image') {
      var img = document.createElement('img');
      img.src = src;
      img.alt = 'attachment';
      img.loading = 'eager';
      content.appendChild(img);
    } else if (type === 'pdf') {
      var iframe = document.createElement('iframe');
      iframe.src = src;
      iframe.title = 'PDF preview';
      iframe.setAttribute('referrerpolicy', 'no-referrer');
      content.appendChild(iframe);
    } else {
      var a = document.createElement('a');
      a.href = src;
      a.textContent = 'Open file';
      a.target = '_blank';
      content.appendChild(a);
    }

    viewer.classList.remove('is-hidden');
  }

  document.addEventListener('click', function (e) {
    // Open modal (supports: .js-open-media with data-target OR .attachments-toggle with data-modal)
    var openBtn = e.target.closest && (e.target.closest('.js-open-media') || e.target.closest('.attachments-toggle'));
    if (openBtn) {
      var sel = openBtn.getAttribute('data-target') || openBtn.getAttribute('data-modal');
      if (!sel) return;
      var modal = qs(sel);
      if (!modal) return;
      openModal(modal);
      setActiveTab(modal, 'all');
      return;
    }

    // Close modal
    var closeBtn = e.target.closest && (e.target.closest('.js-close-media') || e.target.closest('.media-modal__backdrop'));
    if (closeBtn) {
      var modal2 = closeBtn.closest('.media-modal');
      if (modal2) {
        closeModal(modal2);
        return;
      }
    }

    // Tabs
    var tabBtn = e.target.closest && e.target.closest('.media-tab');
    if (tabBtn) {
      var modal3 = tabBtn.closest('.media-modal');
      if (!modal3) return;
      setActiveTab(modal3, tabBtn.getAttribute('data-filter') || 'all');
      return;
    }

    // Open attachment preview
    var itemBtn = e.target.closest && e.target.closest('.js-attachment-open');
    if (itemBtn) {
      var modal4 = itemBtn.closest('.media-modal');
      if (!modal4) return;
      openViewer(modal4, itemBtn.getAttribute('data-type') || 'file', itemBtn.getAttribute('data-src') || '#');
      return;
    }

    // Close viewer
    var viewerClose = e.target.closest && (e.target.closest('.js-viewer-close') || e.target.closest('.media-viewer__backdrop'));
    if (viewerClose) {
      var viewer = viewerClose.closest('.media-viewer');
      if (viewer) viewer.classList.add('is-hidden');
      var modal5 = viewerClose.closest('.media-modal');
      if (modal5) {
        var content = qs('[data-viewer-content]', modal5);
        if (content) content.innerHTML = '';
      }
      return;
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;

    // Close viewer first if open
    var openViewerEl = document.querySelector('.media-viewer:not(.is-hidden)');
    if (openViewerEl) {
      openViewerEl.classList.add('is-hidden');
      var modal = openViewerEl.closest('.media-modal');
      if (modal) {
        var content = qs('[data-viewer-content]', modal);
        if (content) content.innerHTML = '';
      }
      return;
    }

    // Otherwise close modal
    var modal2 = document.querySelector('.media-modal:not(.is-hidden)');
    if (modal2) closeModal(modal2);
  });
})();




