(function () {
  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function qsa(sel, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(sel));
  }

  function openModal(modal) {
    if (!modal) return;
    // suportă ambele variante: is-hidden + aria-hidden
    modal.classList.remove("is-hidden");
    modal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  }

  function closeModal(modal) {
    if (!modal) return;
    modal.classList.add("is-hidden");
    modal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  }

  function getOpenModal() {
    // considerăm "deschis" orice modal fără is-hidden sau aria-hidden=false
    var m = qs(".attachments-modal:not(.is-hidden)");
    if (m) return m;
    return qs('.media-modal[aria-hidden="false"]');
  }

  function applyFilter(panel, filter) {
    // Dacă nu ai grid/tipuri, nu face nimic
    var grid = qs(".media-grid", panel);
    if (!grid) return;

    qsa(".media-item", grid).forEach(function (item) {
      var type = item.getAttribute("data-type") || "other";
      if (filter === "all") item.style.display = "";
      else if (filter === "images") item.style.display = (type === "images") ? "" : "none";
      else if (filter === "pdf") item.style.display = (type === "pdf") ? "" : "none";
      else item.style.display = "";
    });
  }

  document.addEventListener("click", function (e) {
    // OPEN: buton (vechi) .attachments-toggle
    var openBtnOld = e.target.closest ? e.target.closest(".attachments-toggle") : null;
    if (openBtnOld) {
      var selOld = openBtnOld.getAttribute("data-modal");
      if (!selOld) return;
      openModal(qs(selOld));
      return;
    }

    // OPEN: buton (nou) .js-open-media
    var openBtn = e.target.closest ? e.target.closest(".js-open-media") : null;
    if (openBtn) {
      var sel = openBtn.getAttribute("data-target") || openBtn.getAttribute("data-modal");
      if (!sel) return;
      openModal(qs(sel));
      return;
    }

    // CLOSE: element cu [data-close] (X, buton, etc.)
    var closeEl = e.target.closest ? e.target.closest("[data-close]") : null;
    if (closeEl) {
      var modal = e.target.closest(".attachments-modal") || e.target.closest(".media-modal");
      closeModal(modal);
      return;
    }

    // CLOSE: click pe backdrop (dacă ai .media-modal__backdrop sau .attachments-backdrop)
    if (e.target.classList && (e.target.classList.contains("media-modal__backdrop") || e.target.classList.contains("attachments-backdrop"))) {
      var modalB = e.target.closest(".media-modal") || e.target.closest(".attachments-modal");
      closeModal(modalB);
      return;
    }

    // TABS FILTER: .media-tab (All/Media/Docs)
    var tab = e.target.closest ? e.target.closest(".media-tab") : null;
    if (tab) {
      var panel = tab.closest(".media-modal__panel") || tab.closest(".attachments-modal") || document;
      var filter = tab.getAttribute("data-filter") || "all";

      // active class
      var tabsWrap = tab.parentElement;
      if (tabsWrap) {
        qsa(".media-tab", tabsWrap).forEach(function (t) { t.classList.remove("is-active"); });
      }
      tab.classList.add("is-active");

      applyFilter(panel, filter);
      return;
    }
  });

  document.addEventListener("keydown", function (e) {
    if (e.key !== "Escape") return;
    var modal = getOpenModal();
    if (!modal) return;
    closeModal(modal);
  });
})();
