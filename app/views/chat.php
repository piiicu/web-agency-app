<?php
// app/views/chat.php
require __DIR__ . '/admin/_layout_start.php';

$lastId = 0;
if (!empty($messages)) {
  foreach ($messages as $m) {
    $lastId = max($lastId, (int)($m['id'] ?? 0));
  }
}
?>

<div class="card ichat-card">
  <div class="ichat-headerbar">
    <div>
      <h2>Chat intern</h2>
      <div class="ichat-subtitle">Chat intern între administratori (fără clienți).</div>
    </div>
    <a href="<?= BASE_URL ?>admin/dashboard" class="btn">⬅ Dashboard</a>
  </div>

  <hr>

  <!-- Search -->
  <div class="ichat-search">
    <input id="chatSearch" type="text" placeholder="Caută în conversație...">
    <button id="chatPrev" type="button" title="Rezultat anterior">↑</button>
    <button id="chatNext" type="button" title="Rezultat următor">↓</button>
    <button id="chatClear" type="button" title="Golește">✕</button>
  </div>

  <!-- Messages -->
  <div id="chatBox" class="ichat-box">
    <?php if (empty($messages)): ?>
      <div class="ichat-empty">Nu există mesaje încă.</div>
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

        <div class="ichat-msg <?= $isMe ? 'ichat-me' : 'ichat-other' ?>" data-id="<?= $mid ?>">
          <div class="ichat-meta">
            <?php if (!$isMe): ?>
              <span class="ichat-name"><?= $name ?></span>
            <?php endif; ?>
            <span class="ichat-time"><?= $time ?></span>
          </div>

          <div class="ichat-bubble">
            <?php if (trim($text) !== ''): ?>
              <div class="ichat-text"><?= nl2br(htmlspecialchars($text)) ?></div>
            <?php endif; ?>

            <?php if (!empty($atts) && is_array($atts)): ?>
              <div class="ichat-atts">
                <?php foreach ($atts as $a): ?>
                  <?php
                    $aName = htmlspecialchars($a['name'] ?? 'file');
                    $aUrl  = $a['url'] ?? null;
                    $aDl   = $a['download_url'] ?? ($aUrl ?? null);
                    $aMime = strtolower((string)($a['mime'] ?? ''));
                    $isImg = ($aMime && str_starts_with($aMime, 'image/'));
                  ?>

                  <?php if ($aUrl): ?>
                    <?php if ($isImg): ?>
                      <div class="ichat-img">
                        <a href="<?= htmlspecialchars($aUrl) ?>" target="_blank" style="text-decoration:none;">
                          <img src="<?= htmlspecialchars($aUrl) ?>" alt="<?= $aName ?>">
                        </a>
                        <?php if ($aDl): ?>
                          <div style="margin-top:6px;">
                            <a href="<?= htmlspecialchars($aDl) ?>" download>Descarcă</a>
                          </div>
                        <?php endif; ?>
                      </div>
                    <?php else: ?>
                      <div class="ichat-file">
                        <strong><?= $aName ?></strong>
                        <div>
                          <a href="<?= htmlspecialchars($aUrl) ?>" target="_blank">Deschide</a>
                          <?php if ($aDl): ?>
                            <a href="<?= htmlspecialchars($aDl) ?>" download>Descarcă</a>
                          <?php endif; ?>
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

  <!-- Composer -->
  <form id="chatForm" class="ichat-form" enctype="multipart/form-data">
    <div class="ichat-inputwrap">
      <label for="chatMessage">Mesaj</label>
      <textarea id="chatMessage" name="message" rows="3" placeholder="Scrie un mesaj..."></textarea>

      <div class="ichat-files">
        <input id="chatFiles" type="file" name="files[]" multiple>
        <span id="chatStatus" class="ichat-status"></span>
      </div>
    </div>

    <button id="chatSend" type="submit" class="btn">Trimite</button>
  </form>
</div>

<script>
  const BASE_URL = "<?= BASE_URL ?>";
  let sinceId = <?= (int)$lastId ?>;

  const CHAT_POLL_ROUTE = BASE_URL + "chat-poll";
  const CHAT_SEND_ROUTE = BASE_URL + "chat";

  const box = document.getElementById("chatBox");
  const form = document.getElementById("chatForm");
  const input = document.getElementById("chatMessage");
  const files = document.getElementById("chatFiles");
  const status = document.getElementById("chatStatus");

  // search
  const sInput = document.getElementById("chatSearch");
  const sPrev = document.getElementById("chatPrev");
  const sNext = document.getElementById("chatNext");
  const sClear = document.getElementById("chatClear");
  let hits = [];
  let hitIndex = -1;

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

  function clearHighlights() {
    box.querySelectorAll("mark[data-chat-hit='1']").forEach(m => {
      const t = document.createTextNode(m.textContent);
      m.replaceWith(t);
    });
  }

  function applySearch(query) {
    clearHighlights();
    hits = [];
    hitIndex = -1;

    const q = (query || "").trim();
    if (!q) return;

    box.querySelectorAll(".ichat-text").forEach(n => {
      const raw = n.textContent;
      const idx = raw.toLowerCase().indexOf(q.toLowerCase());
      if (idx === -1) return;

      const before = escapeHtml(raw.slice(0, idx));
      const match  = escapeHtml(raw.slice(idx, idx + q.length));
      const after  = escapeHtml(raw.slice(idx + q.length));

      n.innerHTML = `${before}<mark data-chat-hit="1">${match}</mark>${after}`;
      const mark = n.querySelector("mark[data-chat-hit='1']");
      if (mark) hits.push(mark);
    });

    if (hits.length) {
      hitIndex = 0;
      focusHit();
    }
  }

  function focusHit() {
    hits.forEach((m, i) => m.style.outline = (i === hitIndex ? "2px solid #333" : "none"));
    const m = hits[hitIndex];
    if (!m) return;
    m.scrollIntoView({ block: "center", behavior: "smooth" });
  }

  sInput?.addEventListener("input", () => applySearch(sInput.value));
  sPrev?.addEventListener("click", () => {
    if (!hits.length) return;
    hitIndex = (hitIndex - 1 + hits.length) % hits.length;
    focusHit();
  });
  sNext?.addEventListener("click", () => {
    if (!hits.length) return;
    hitIndex = (hitIndex + 1) % hits.length;
    focusHit();
  });
  sClear?.addEventListener("click", () => {
    sInput.value = "";
    clearHighlights();
    hits = [];
    hitIndex = -1;
  });

  function renderAttachment(a) {
    const name = escapeHtml(a?.name || "file");
    const url = a?.url || "";
    const dl = a?.download_url || url;
    const mime = (a?.mime || "").toLowerCase();
    const isImg = mime.startsWith("image/");

    if (!url) return "";

    if (isImg) {
      return `
        <div class="ichat-img">
          <a href="${escapeHtml(url)}" target="_blank" style="text-decoration:none;">
            <img src="${escapeHtml(url)}" alt="${name}">
          </a>
          <div style="margin-top:6px;">
            <a href="${escapeHtml(dl)}" download>Descarcă</a>
          </div>
        </div>
      `;
    }

    return `
      <div class="ichat-file">
        <strong>${name}</strong>
        <div>
          <a href="${escapeHtml(url)}" target="_blank">Deschide</a>
          <a href="${escapeHtml(dl)}" download>Descarcă</a>
        </div>
      </div>
    `;
  }

  function appendMessage(m) {
    const mid = Number(m?.id || 0);
    const isMe = !!m?.is_me;

    const name = escapeHtml(m?.name || "User");
    const time = escapeHtml(m?.created_at || "");
    const text = escapeHtml(m?.message || "");
    const atts = Array.isArray(m?.attachments) ? m.attachments : [];

    const wrap = document.createElement("div");
    wrap.className = "ichat-msg " + (isMe ? "ichat-me" : "ichat-other");
    wrap.dataset.id = String(mid);

    const attHtml = atts.length ? `<div class="ichat-atts">${atts.map(renderAttachment).join("")}</div>` : "";

    wrap.innerHTML = `
      <div class="ichat-meta">
        ${isMe ? "" : `<span class="ichat-name">${name}</span>`}
        <span class="ichat-time">${time}</span>
      </div>
      <div class="ichat-bubble">
        ${text ? `<div class="ichat-text">${text}</div>` : ""}
        ${attHtml}
      </div>
    `;

    box.appendChild(wrap);
  }

  async function poll() {
    try {
      const stickToBottom = isNearBottom();

      const res = await fetch(CHAT_POLL_ROUTE + "&since=" + encodeURIComponent(String(sinceId)), {
        headers: { "Accept": "application/json" },
        credentials: "same-origin"
      });
      if (!res.ok) return;

      const data = await res.json();
      if (!data || !Array.isArray(data.messages) || data.messages.length === 0) return;

      data.messages.forEach(msg => {
        appendMessage(msg);
        sinceId = Math.max(sinceId, Number(msg?.id || 0));
      });

      if (stickToBottom) scrollToBottom();
      if (sInput.value.trim()) applySearch(sInput.value);
    } catch (e) {}
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const message = input.value.trim();
    const hasFiles = files.files && files.files.length > 0;
    if (!message && !hasFiles) return;

    const fd = new FormData();
    fd.append("message", message);
    if (hasFiles) for (const f of files.files) fd.append("files[]", f);

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

  scrollToBottom();
  setInterval(poll, 2000);
</script>

<?php require __DIR__ . '/admin/_layout_end.php'; ?>
