<?php
// app/views/chat.php (Admin internal chat)
require __DIR__ . '/admin/_layout_start.php';

$lastId = 0;
if (!empty($messages)) {
  foreach ($messages as $m) {
    $lastId = max($lastId, (int)($m['id'] ?? 0));
  }
}

function isImgMime(?string $mime): bool
{
  return is_string($mime) && str_starts_with(strtolower($mime), 'image/');
}
?>

<?php
  $cid = (int)($activeConversation['id'] ?? 0);
?>

<div class="page-header">
  <div class="page-header__left">
    <h1 class="page-header__title">Chat intern</h1>
    <p class="page-header__subtitle">Chat intern între administratori (fără clienți).</p>
  </div>

  <div class="page-header__actions">
    <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-ghost">⬅ Dashboard</a>

    <?php if (!empty($conversations) && is_array($conversations)): ?>
      <div class="chat-convpick">
        <label class="chat-convpick__label" for="chatConv">Conversație</label>
        <select id="chatConv" class="input" data-chat-conv data-cselect>
          <?php foreach ($conversations as $c): ?>
            <?php $id = (int)($c['id'] ?? 0); ?>
            <option value="<?= $id ?>" <?= $id === $cid ? 'selected' : '' ?>>
              <?= htmlspecialchars((string)($c['title'] ?? 'Conversație')) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>

    <!-- Create DM / Group -->
    <?php if (!empty($users) && is_array($users)): ?>
<button type="button" class="btn btn-ghost" data-chat-new-open>+ Nou</button>

<div class="chat-new" data-chat-new hidden>
  <div class="chat-new__backdrop" data-chat-new-close></div>
  <div class="chat-new__dialog" role="dialog" aria-modal="true" aria-label="Conversație nouă">
    <div class="chat-new__head">
      <div class="chat-new__title">Conversație nouă</div>
      <button type="button" class="chat-new__close" data-chat-new-close aria-label="Închide">✕</button>
    </div>

    <div class="chat-new__body">
      <div class="chat-new__section">
        <div class="chat-new__label">Creează conversație (grup sau privat)</div>
        <form method="POST" action="<?= BASE_URL ?>chat-group" class="chat-new__group">
          <input class="input" name="title" placeholder="Nume grup (opțional)">

          <div class="chat-new__users" role="group" aria-label="Participanți">
            <?php $meUid = (int)($_SESSION['user']['id'] ?? 0); ?>
            <?php foreach ($users as $u): ?>
              <?php if ((int)$u['id'] === $meUid) continue; ?>
              <label class="chat-new__user">
                <input type="checkbox" name="user_ids[]" value="<?= (int)$u['id'] ?>">
                <span><?= htmlspecialchars($u['name'] . ' (' . $u['role'] . ')') ?></span>
              </label>
            <?php endforeach; ?>
          </div>

          <button class="btn" type="submit">Creează</button>
          <div class="chat-new__hint" data-chat-new-hint style="display:none;"></div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php endif; ?>

    <!-- Mobile: WhatsApp-like actions (⋮ → Search) -->
    <div class="chat-topmenu" data-chat-topmenu>
      <button type="button" class="btn btn-ghost chat-topmenu__btn" data-chat-menu-btn aria-label="Mai multe" aria-expanded="false">⋮</button>
      <div class="chat-topmenu__pop" data-chat-menu-pop hidden>
        <button type="button" class="chat-topmenu__item" data-chat-open-search>🔎 Caută în conversație</button>
      </div>
    </div>
  </div>
</div>

<div class="ticket-chat">
  <!-- Search (shared component) -->
  <div class="chat-search" data-chat-search-wrap>
    <input type="search" class="chat-search__input" placeholder="Caută în conversație..." data-chat-search>
    <div class="chat-search__meta" data-chat-search-meta></div>
    <div class="chat-search__actions">
      <button type="button" class="chat-search__btn" data-chat-search-prev title="Rezultat anterior">↑</button>
      <button type="button" class="chat-search__btn" data-chat-search-next title="Rezultat următor">↓</button>
      <button type="button" class="chat-search__btn" data-chat-search-clear title="Golește">✕</button>
    </div>
  </div>

  <!-- Messages -->
  <div id="chatBox" class="chat-thread" data-chat-container>
    <?php if (empty($messages)): ?>
      <div class="chat-empty">Nu există mesaje încă.</div>
    <?php else: ?>
      <?php foreach ($messages as $m): ?>
        <?php
        $mid  = (int)($m['id'] ?? 0);
        $isMe = !empty($m['is_me']);
        $name = htmlspecialchars($m['name'] ?? 'User');
        $time = htmlspecialchars($m['created_at'] ?? '');
        $text = (string)($m['message'] ?? '');
        $atts = $m['attachments'] ?? [];
        ?>

        <div class="chat-row <?= $isMe ? 'is-mine' : '' ?>" data-message-id="<?= $mid ?>">
          <div class="chat-bubble">
            <div class="chat-meta">
              <?php if (!$isMe): ?>
                <span class="chat-name"><?= $name ?></span>
              <?php else: ?>
                <span class="chat-name">Tu</span>
              <?php endif; ?>
              <span class="chat-time"><?= $time ?></span>
              <?php if ($isMe): ?>
                <span class="chat-checks sent" aria-label="sent">✓</span>
              <?php endif; ?>
            </div>

            <?php if (trim($text) !== ''): ?>
              <div class="chat-text"><?= nl2br(htmlspecialchars($text)) ?></div>
            <?php else: ?>
              <div class="chat-text chat-text--muted">(Mesaj fără text)</div>
            <?php endif; ?>

            <?php if (!empty($atts) && is_array($atts)): ?>
              <div class="chat-attachments">
                <?php foreach ($atts as $a): ?>
                  <?php
                  $aName = htmlspecialchars($a['name'] ?? 'file');
                  $aUrl  = $a['url'] ?? null;
                  $aDl   = $a['download_url'] ?? ($aUrl ?? null);
                  $aMime = (string)($a['mime'] ?? '');
                  $isImg = isImgMime($aMime);
                  ?>

                  <?php if ($aUrl): ?>
                    <?php if ($isImg): ?>
                      <a class="chat-attachment chat-attachment--image" href="<?= htmlspecialchars($aUrl) ?>" target="_blank" rel="noopener">
                        <img src="<?= htmlspecialchars($aUrl) ?>" alt="<?= $aName ?>" loading="lazy">
                      </a>
                      <?php if ($aDl): ?>
                        <a class="chat-attachment-link" href="<?= htmlspecialchars($aDl) ?>" download>Descarcă</a>
                      <?php endif; ?>
                    <?php else: ?>
                      <div class="chat-file">
                        <div class="chat-file__icon">FILE</div>
                        <div class="chat-file__meta">
                          <div class="chat-file__name"><?= $aName ?></div>
                          <div class="chat-file__actions">
                            <a href="<?= htmlspecialchars($aUrl) ?>" target="_blank" rel="noopener">Deschide</a>
                            <?php if ($aDl): ?>
                              <a href="<?= htmlspecialchars($aDl) ?>" download>Descarcă</a>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    <?php endif; ?>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Composer (AJAX) -->
  <div class="card chat-compose" style="margin-top:16px;">
    <h3 class="h3" style="margin-top:0;">Scrie mesajul</h3>

    <form id="chatForm" enctype="multipart/form-data" class="form-standard">
      <div>
        <div class="chat-compose__inputrow">
          <textarea id="chatMessage" class="textarea" name="message" rows="3" placeholder="Scrie un mesaj..."></textarea>

          <!-- Mobile-only: paperclip + compact send button -->
          <label for="chatFiles" class="chat-clip" title="Atașează">📎</label>
          <button type="button" class="chat-send-mini" data-chat-send-mini aria-label="Trimite">➤</button>
        </div>
      </div>

      <div class="form-actions">
        <input id="chatFiles" type="file" name="files[]" multiple>
        <span id="chatStatus" class="help"></span>
        <button id="chatSend" type="submit" class="btn">Trimite</button>
      </div>
    </form>
  </div>
</div>

<script>
  // === WhatsApp-like textarea auto-grow ===
  const ta = document.getElementById('chatMessage');

  if (ta) {
    ta.addEventListener('input', () => {
      ta.style.height = '44px';
      ta.style.height = Math.min(ta.scrollHeight, 120) + 'px';
    });
  }

  const BASE_URL = "<?= BASE_URL ?>";
  const CID = <?= (int)$cid ?>;
  let sinceId = <?= (int)$lastId ?>;

  const CHAT_POLL_ROUTE = BASE_URL + "chat-poll&cid=" + encodeURIComponent(String(CID));
  const CHAT_MARK_READ_ROUTE = BASE_URL + "chat-mark-read&cid=" + encodeURIComponent(String(CID));
  const CHAT_SEND_ROUTE = BASE_URL + "chat&cid=" + encodeURIComponent(String(CID));


  // New conversation modal (DM / Group) - overlay, no layout shift
  (function () {
    const openBtn = document.querySelector('[data-chat-new-open]');
    const modal = document.querySelector('[data-chat-new]');
    if (!openBtn || !modal) return;

    // Safety: always start closed (prevents modal showing due to cached DOM state)
    modal.classList.remove('is-open');
    modal.hidden = true;

    const closeBtns = modal.querySelectorAll('[data-chat-new-close]');
    const groupForm = modal.querySelector('form.chat-new__group');
    const hint = modal.querySelector('[data-chat-new-hint]');

    function open() {
      modal.classList.add('is-open');
      modal.hidden = false;
      document.body.classList.add('has-modal');
      // focus first input if present
      const first = modal.querySelector('select, input, button');
      if (first) first.focus({ preventScroll: true });
    }

    function close() {
      modal.classList.remove('is-open');
      modal.hidden = true;
      document.body.classList.remove('has-modal');
      openBtn.focus({ preventScroll: true });
    }

    openBtn.addEventListener('click', open);

    // Robust close: works even if the close button/backdrop is re-rendered
    closeBtns.forEach(b => b.addEventListener('click', close));
    modal.addEventListener('click', (e) => {
      if (e.target && e.target.closest && e.target.closest('[data-chat-new-close]')) {
        close();
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !modal.hidden) close();
    });

    // No need to stop propagation: only elements marked with [data-chat-new-close]
    // will close the modal.

    // Validate group creation: require at least 1 other user checked
    if (groupForm) {
      groupForm.addEventListener('submit', (e) => {
        const checked = groupForm.querySelectorAll('input[type="checkbox"][name="user_ids[]"]:checked').length;
        if (checked < 1) {
          e.preventDefault();
          if (hint) {
            hint.style.display = 'block';
            hint.textContent = 'Selectează cel puțin o persoană.';
          }
          return;
        }
        if (hint) {
          hint.style.display = 'none';
          hint.textContent = '';
        }
      });
    }
  })();



  // Conversation switcher
  const convSelect = document.querySelector('[data-chat-conv]');
  if (convSelect) {
    convSelect.addEventListener('change', () => {
      const next = convSelect.value;
      if (!next) return;
      window.location.href = BASE_URL + 'chat&cid=' + encodeURIComponent(String(next));
    });
  }

  const box = document.getElementById("chatBox");
  const form = document.getElementById("chatForm");
  const input = document.getElementById("chatMessage");
  const files = document.getElementById("chatFiles");
  const status = document.getElementById("chatStatus");

  const searchInput = document.querySelector('[data-chat-search]');
  const searchWrap = document.querySelector('[data-chat-search-wrap]');

  // Mobile header menu (⋮)
  const menuBtn = document.querySelector('[data-chat-menu-btn]');
  const menuPop = document.querySelector('[data-chat-menu-pop]');
  const openSearchBtn = document.querySelector('[data-chat-open-search]');
  const clearSearchBtn = document.querySelector('[data-chat-search-clear]');

  // Mobile compact send button
  const sendMini = document.querySelector('[data-chat-send-mini]');

  function closeTopmenu() {
    if (!menuPop || !menuBtn) return;
    menuPop.hidden = true;
    menuBtn.setAttribute('aria-expanded', 'false');
  }

  function openSearch() {
    if (!searchWrap) return;
    searchWrap.classList.add('is-open');
    // keep menu closed
    closeTopmenu();
    // focus search input
    const si = searchWrap.querySelector('[data-chat-search]');
    if (si) {
      si.focus();
      si.select?.();
    }
  }

  if (menuBtn && menuPop) {
    menuBtn.addEventListener('click', () => {
      const next = !menuPop.hidden;
      menuPop.hidden = next;
      menuBtn.setAttribute('aria-expanded', String(!next));
    });

    document.addEventListener('click', (e) => {
      if (!menuPop.hidden) {
        const root = menuBtn.closest('[data-chat-topmenu]');
        if (root && !root.contains(e.target)) closeTopmenu();
      }
    });
  }

  if (openSearchBtn) {
    openSearchBtn.addEventListener('click', openSearch);
  }

  if (clearSearchBtn && searchWrap) {
    clearSearchBtn.addEventListener('click', () => {
      // also collapse the search UI on mobile
      searchWrap.classList.remove('is-open');
    });
  }

  if (sendMini) {
    sendMini.addEventListener('click', () => {
      // submit via the existing form handler (keeps logic untouched)
      if (typeof form.requestSubmit === 'function') form.requestSubmit();
      else form.submit();
    });
  }

  // Mobile: textarea auto-grow (prevents the resize "bar" on the right)
  function autoGrowTextarea(el) {
    if (!el) return;
    el.style.height = 'auto';
    const max = 120;
    const next = Math.min(el.scrollHeight, max);
    el.style.height = next + 'px';
  }

  input.addEventListener('input', () => {
    if (window.matchMedia && window.matchMedia('(max-width: 640px)').matches) {
      autoGrowTextarea(input);
    }
  });

  function escapeHtml(s) {
    return (s ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  function isNearBottom() {
    return (box.scrollHeight - box.scrollTop - box.clientHeight) < 80;
  }

  function scrollToBottom() {
    box.scrollTop = box.scrollHeight;
  }

  function renderAttachment(a) {
    const name = escapeHtml(a?.name || "file");
    const url = a?.url || "";
    const dl = a?.download_url || url;
    const mime = (a?.mime || "").toLowerCase();
    const isImg = mime.startsWith("image/");

    if (!url) return "";

    if (isImg) {
      return `
        <a class="chat-attachment chat-attachment--image" href="${escapeHtml(url)}" target="_blank" rel="noopener">
          <img src="${escapeHtml(url)}" alt="${name}" loading="lazy">
        </a>
        <a class="chat-attachment-link" href="${escapeHtml(dl)}" download>Descarcă</a>
      `;
    }

    return `
      <div class="chat-file">
        <div class="chat-file__icon">FILE</div>
        <div class="chat-file__meta">
          <div class="chat-file__name">${name}</div>
          <div class="chat-file__actions">
            <a href="${escapeHtml(url)}" target="_blank" rel="noopener">Deschide</a>
            <a href="${escapeHtml(dl)}" download>Descarcă</a>
          </div>
        </div>
      </div>
    `;
  }

  
  function checksHtml(m) {
    if (!m || !m.is_me) return "";
    // default until statuses come from server
    return `<span class="chat-checks sent" aria-label="sent">✓</span>`;
  }

  function applyStatus(id, delivered, read) {
    const row = box ? box.querySelector(`[data-message-id="${CSS.escape(String(id))}"]`) : null;
    if (!row) return;
    const el = row.querySelector(".chat-checks");
    if (!el) return;

    if (read) {
      el.className = "chat-checks read";
      el.textContent = "✓✓";
    } else if (delivered) {
      el.className = "chat-checks delivered";
      el.textContent = "✓✓";
    } else {
      el.className = "chat-checks sent";
      el.textContent = "✓";
    }
  }

function appendMessage(m) {
    const mid = Number(m?.id || 0);
    const isMe = !!m?.is_me;

    const name = escapeHtml(m?.name || "User");
    const time = escapeHtml(m?.created_at || "");
    const text = escapeHtml(m?.message || "");
    const atts = Array.isArray(m?.attachments) ? m.attachments : [];

    const row = document.createElement("div");
    row.className = "chat-row" + (isMe ? " is-mine" : "");
    row.dataset.messageId = String(mid);

    const attHtml = atts.length ? `<div class="chat-attachments">${atts.map(renderAttachment).join("")}</div>` : "";

    row.innerHTML = `
      <div class="chat-bubble">
        <div class="chat-meta">
          <span class="chat-name">${isMe ? "Tu" : name}</span>
          <span class="chat-time">${time}</span>
          ${checksHtml(m)}
        </div>
        ${text ? `<div class="chat-text">${text}</div>` : `<div class="chat-text chat-text--muted">(Mesaj fără text)</div>`}
        ${attHtml}
      </div>
    `;

    box.appendChild(row);
  }

  async function poll() {
    try {
      const stickToBottom = isNearBottom();

      const res = await fetch(CHAT_POLL_ROUTE + "&since=" + encodeURIComponent(String(sinceId)), {
        headers: {
          "Accept": "application/json"
        },
        credentials: "same-origin"
      });
      if (!res.ok) return;

      const data = await res.json();
      if (!data) return;
      if (data.statuses && Array.isArray(data.statuses)) {
        data.statuses.forEach(s => applyStatus(s.id, !!s.delivered, !!s.read));
      }
      if (!Array.isArray(data.messages) || data.messages.length === 0) return;

      data.messages.forEach(msg => {
        appendMessage(msg);
        sinceId = Math.max(sinceId, Number(msg?.id || 0));
      });

      if (stickToBottom) scrollToBottom();

      // If search is active, re-run highlight (app.js listens on input)
      if (searchInput && searchInput.value.trim()) {
        try {
          searchInput.dispatchEvent(new Event('input', {
            bubbles: true
          }));
        } catch (e) {}
      }
    } catch (e) {}
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const message = input.value.trim();
    const hasFiles = files.files && files.files.length > 0;
    if (!message && !hasFiles) return;

    const fd = new FormData();
    fd.append("message", message);
    if (hasFiles)
      for (const f of files.files) fd.append("files[]", f);

    status.textContent = "Se trimite...";
    try {
      const res = await fetch(CHAT_SEND_ROUTE, {
        method: "POST",
        body: fd,
        credentials: "same-origin"
      });

      if (!res.ok) {
        status.textContent = "Eroare la trimitere.";
        return;
      }

      input.value = "";
      files.value = "";
      status.textContent = "";

      await poll();
      scrollToBottom();
    } catch (e) {
      status.textContent = "Eroare la trimitere.";
    }
  });

  async function markRead() {
  // doar dacă user chiar vede pagina
  if (document.visibilityState !== 'visible') return;
  if (!document.hasFocus()) return;

  try {
    await fetch(CHAT_MARK_READ_ROUTE, {
      method: "POST",
      headers: { "Accept": "application/json" },
      credentials: "same-origin"
    });
  } catch (e) {}
}

// când revine tab-ul activ / focus
document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'visible') markRead();
});
window.addEventListener('focus', markRead);

// și imediat la încărcare (dacă e vizibil)
markRead();


  scrollToBottom();
  setInterval(poll, 2000);
</script>

<?php require __DIR__ . '/admin/_layout_end.php'; ?>
