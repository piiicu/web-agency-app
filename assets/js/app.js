// Global tiny JS helpers (kept intentionally small).
(function () {
  // Auto-scroll chat threads to bottom (WhatsApp style)
  document.addEventListener('DOMContentLoaded', function () {
    var threads = document.querySelectorAll('[data-chat-thread]');
    if (!threads || threads.length === 0) return;

    threads.forEach(function (el) {
      try {
        el.scrollTop = el.scrollHeight;
      } catch (e) {}
    });
  });
})();

(function () {
  function getInt(v, fallback = 0) {
    const n = parseInt(v, 10);
    return Number.isFinite(n) ? n : fallback;
  }

  function scrollToBottom(thread) {
    thread.scrollTop = thread.scrollHeight;
  }

  function scrollMessageIntoView(thread, el) {
    // scroll in container, not full page
    const top = el.offsetTop - 12;
    thread.scrollTop = top < 0 ? 0 : top;
  }

  function initChatAutoScroll() {
    const thread = document.querySelector('[data-chat-thread]');
    if (!thread) return;

    const ticketId = getInt(thread.getAttribute('data-ticket-id'));
    const role = thread.getAttribute('data-role') || 'user';

    if (!ticketId) {
      // fallback: scroll to bottom
      scrollToBottom(thread);
      return;
    }

    const key = `chat_last_seen_${role}_${ticketId}`;

    // toate mesajele (trebuie să aibă data-message-id)
    const msgEls = Array.from(thread.querySelectorAll('[data-message-id]'));
    if (msgEls.length === 0) {
      scrollToBottom(thread);
      return;
    }

    const lastSeen = getInt(localStorage.getItem(key), 0);

    // ultimul mesaj
    const lastMsgEl = msgEls[msgEls.length - 1];
    const lastMsgId = getInt(lastMsgEl.getAttribute('data-message-id'), 0);

    // găsește primul mesaj "nou" (id > lastSeen)
    const firstUnread = msgEls.find(el => getInt(el.getAttribute('data-message-id'), 0) > lastSeen);

    // Așteaptă un frame ca să fie calculate dimensiunile (img loading etc.)
    requestAnimationFrame(() => {
      if (firstUnread) {
        // ca WhatsApp: sare la primul mesaj nou
        scrollMessageIntoView(thread, firstUnread);

        // opțional: highlight discret
        firstUnread.classList.add('chat-unread-jump');
        setTimeout(() => firstUnread.classList.remove('chat-unread-jump'), 1500);
      } else {
        // altfel la final
        scrollToBottom(thread);
      }

      // marchează ca văzut ultimul mesaj (după ce ai făcut jump)
      // (practic: ai "vizitat" discuția)
      if (lastMsgId > 0) localStorage.setItem(key, String(lastMsgId));
    });

    // Dacă user-ul stă în jos (aproape de bottom) și apar imagini încărcate târziu, păstrează-l jos
    // (nu îl tragem în jos dacă a dat scroll în sus manual)
    let userScrolledUp = false;

    thread.addEventListener('scroll', () => {
      const nearBottom = (thread.scrollHeight - thread.scrollTop - thread.clientHeight) < 80;
      userScrolledUp = !nearBottom;
    });

    // Observă modificări (ex: se încarcă imagini / se adaugă noduri)
    const mo = new MutationObserver(() => {
      if (!userScrolledUp) scrollToBottom(thread);
    });

    mo.observe(thread, { childList: true, subtree: true });
  }

  // run
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initChatAutoScroll);
  } else {
    initChatAutoScroll();
  }
})();


(function () {
  function escapeRegExp(str) {
    return str.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  }

  function clearHighlights(root) {
    const marks = root.querySelectorAll("mark.chat-mark");
    marks.forEach(m => {
      const text = document.createTextNode(m.textContent || "");
      m.replaceWith(text);
    });
    root.normalize();
  }

  function highlightTermInElement(el, term) {
    const text = el.textContent || "";
    if (!term || !text) return 0;

    const re = new RegExp(escapeRegExp(term), "gi");
    let count = 0;

    // work on HTML safely by splitting text nodes
    const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, null);
    const nodes = [];
    while (walker.nextNode()) nodes.push(walker.currentNode);

    nodes.forEach(node => {
      const v = node.nodeValue;
      if (!v) return;

      const matches = [...v.matchAll(re)];
      if (matches.length === 0) return;

      const frag = document.createDocumentFragment();
      let lastIndex = 0;

      matches.forEach(match => {
        const start = match.index;
        const end = start + match[0].length;

        frag.appendChild(document.createTextNode(v.slice(lastIndex, start)));

        const mark = document.createElement("mark");
        mark.className = "chat-mark";
        mark.textContent = v.slice(start, end);
        frag.appendChild(mark);

        lastIndex = end;
        count++;
      });

      frag.appendChild(document.createTextNode(v.slice(lastIndex)));
      node.parentNode.replaceChild(frag, node);
    });

    return count;
  }

  function scrollMarkIntoView(container, mark) {
    // scroll inside chat thread
    const top = mark.getBoundingClientRect().top - container.getBoundingClientRect().top;
    container.scrollTop += top - 80;
  }

  function initChatSearchForContainer(container) {
    const input = container.parentElement.querySelector("[data-chat-search]");
    const meta = container.parentElement.querySelector("[data-chat-search-meta]");
    const btnPrev = container.parentElement.querySelector("[data-chat-search-prev]");
    const btnNext = container.parentElement.querySelector("[data-chat-search-next]");
    const btnClear = container.parentElement.querySelector("[data-chat-search-clear]");

    if (!input || !meta || !btnPrev || !btnNext || !btnClear) return;

    const getMarks = () => Array.from(container.querySelectorAll("mark.chat-mark"));
    let currentIndex = -1;

    function updateMeta() {
      const marks = getMarks();
      if (marks.length === 0) {
        meta.textContent = input.value.trim() ? "0 rezultate" : "";
        return;
      }
      meta.textContent = `${currentIndex + 1}/${marks.length}`;
    }

    function setActive(index) {
      const marks = getMarks();
      marks.forEach(m => m.classList.remove("chat-mark--active"));

      if (marks.length === 0) {
        currentIndex = -1;
        updateMeta();
        return;
      }

      currentIndex = (index + marks.length) % marks.length;
      const active = marks[currentIndex];
      active.classList.add("chat-mark--active");
      scrollMarkIntoView(container, active);
      updateMeta();
    }

    function runSearch() {
      const term = input.value.trim();
      clearHighlights(container);

      currentIndex = -1;

      if (!term) {
        meta.textContent = "";
        return;
      }

      // Highlight only inside message text blocks
      const textBlocks = container.querySelectorAll(".chat-text");
      let total = 0;
      textBlocks.forEach(el => {
        total += highlightTermInElement(el, term);
      });

      if (total === 0) {
        meta.textContent = "0 rezultate";
        return;
      }

      // move to first result
      setActive(0);
    }

    input.addEventListener("input", () => {
      // debounce light
      window.clearTimeout(input.__t);
      input.__t = window.setTimeout(runSearch, 120);
    });

    input.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        setActive(currentIndex + 1);
      }
      if (e.key === "Escape") {
        e.preventDefault();
        input.value = "";
        clearHighlights(container);
        meta.textContent = "";
      }
    });

    btnNext.addEventListener("click", () => setActive(currentIndex + 1));
    btnPrev.addEventListener("click", () => setActive(currentIndex - 1));
    btnClear.addEventListener("click", () => {
      input.value = "";
      clearHighlights(container);
      meta.textContent = "";
    });
  }

  function initAllChatSearch() {
    const threads = document.querySelectorAll("[data-chat-container]");
    threads.forEach(initChatSearchForContainer);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initAllChatSearch);
  } else {
    initAllChatSearch();
  }
})();

// Mobile sidebar drawer (safe, no dependencies)
(function () {
  function initDrawer() {
    const shell = document.querySelector('[data-app-shell]');
    if (!shell) return;

    const sidebar = shell.querySelector('[data-sidebar]');
    const overlay = shell.querySelector('[data-sidebar-overlay]');
    if (!sidebar || !overlay) return;

    const openBtns = shell.querySelectorAll('[data-sidebar-open]');
    const closeBtns = shell.querySelectorAll('[data-sidebar-close]');

    function open() {
      sidebar.classList.add('is-open');
      overlay.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }

    function close() {
      sidebar.classList.remove('is-open');
      overlay.classList.remove('is-open');
      document.body.style.overflow = '';
    }

    openBtns.forEach(btn => btn.addEventListener('click', open));
    closeBtns.forEach(btn => btn.addEventListener('click', close));
    overlay.addEventListener('click', close);

    // ESC to close
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') close();
    });

    // Close when resizing to desktop
    window.addEventListener('resize', () => {
      if (window.innerWidth > 980) close();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDrawer);
  } else {
    initDrawer();
  }
})();
