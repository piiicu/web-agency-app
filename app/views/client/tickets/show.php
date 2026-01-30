<?php require __DIR__ . '/../_layout_start.php'; ?>

<div class="client-ticket">

  <div class="page-header">
    <div class="page-header__left">
      <h1 class="page-header__title">Ticket #<?= (int)$ticket['id'] ?></h1>
      <p class="page-header__subtitle"><?= htmlspecialchars($ticket['subject']) ?></p>
    </div>
    <div class="page-header__actions">
      <a class="btn btn-ghost" href="<?= BASE_URL ?>client/tickets">⬅ Înapoi la Ticket-ele mele</a>
    </div>
  </div>

  <?php
  // Map attachments by message_id
  $attByMsg = [];
  $orphan = [];

  foreach (($attachments ?? []) as $a) {
    $mid = (int)($a['message_id'] ?? 0);
    if ($mid > 0) $attByMsg[$mid][] = $a;
    else $orphan[] = $a;
  }

  function isImg(?string $mime): bool
  {
    return is_string($mime) && str_starts_with($mime, 'image/');
  }
  function isPdf(?string $mime): bool
  {
    return $mime === 'application/pdf';
  }

  // helper render attachment
  function renderAttachment(array $a): void
  {
    $id = (int)$a['id'];
    $name = (string)($a['original_name'] ?? 'file');
    $mime = (string)($a['mime_type'] ?? 'application/octet-stream');

    $openUrl = BASE_URL . 'ticket-attachment&id=' . $id . '&inline=1';
    $downloadUrl = BASE_URL . 'ticket-attachment&id=' . $id . '&download=1';

    if (isImg($mime)) {
  ?>
      <div class="chat-attachment-item">
        <a class="chat-attachment chat-attachment--image" href="<?= htmlspecialchars($openUrl) ?>" target="_blank" rel="noopener">
          <img src="<?= htmlspecialchars($openUrl) ?>" alt="<?= htmlspecialchars($name) ?>" loading="lazy">
        </a>
        <a class="chat-attachment-link" href="<?= htmlspecialchars($downloadUrl) ?>">Descarcă</a>
      </div>
    <?php
      return;
    }

    ?>
    <div class="chat-attachment chat-attachment--file">
      <div class="chat-file">
        <div class="chat-file__icon"><?= isPdf($mime) ? 'PDF' : 'FILE' ?></div>
        <div class="chat-file__meta">
          <div class="chat-file__name"><?= htmlspecialchars($name) ?></div>
          <div class="chat-file__actions">
            <a href="<?= htmlspecialchars($openUrl) ?>" target="_blank" rel="noopener">Deschide</a>
            <a href="<?= htmlspecialchars($downloadUrl) ?>">Descarcă</a>
          </div>
        </div>
      </div>
    </div>
  <?php
  }
  ?>

  <div class="client-ticket__wrap">

    <div class="client-ticket__head">
      <div class="form-inline">
        <?php $st = (string)($ticket['status'] ?? ''); ?>
        <span class="badge <?= ($st === 'open') ? 'open' : 'closed' ?>">
          <?= ($st === 'open') ? '🟢 ' . htmlspecialchars(ticketStatusLabel('open')) : '🔴 ' . htmlspecialchars(ticketStatusLabel('resolved')) ?>
        </span>

        <span class="muted">
          Creat: <?= htmlspecialchars($ticket['created_at']) ?>
        </span>
      </div>
    </div>

    <div class="ticket-chat">
    <!-- CHAT -->
    <!-- Search bar chat -->
    <div class="chat-search">
      <input type="search" class="chat-search__input" placeholder="Caută în conversație..." data-chat-search>
      <div class="chat-search__meta" data-chat-search-meta></div>
      <div class="chat-search__actions">
        <button type="button" class="chat-search__btn" data-chat-search-prev title="Previous">↑</button>
        <button type="button" class="chat-search__btn" data-chat-search-next title="Next">↓</button>
        <button type="button" class="chat-search__btn" data-chat-search-clear title="Clear">✕</button>
      </div>
    </div>


    <!-- MOD 1: data-ticket-id + data-role -->
    <div class="chat-thread chat-thread--adminlike" data-chat-thread data-chat-container data-ticket-id="<?= (int)$ticket['id'] ?>" data-role="client">

      <?php if (empty($messages) && empty($orphan)): ?>
        <div class="chat-empty">Nu există mesaje încă.</div>
      <?php endif; ?>

      <?php foreach ($messages as $m): ?>
        <?php
        $mine = (Auth::id() && (int)$m['sender_id'] === (int)Auth::id());
        $mid  = (int)$m['id'];
        $mAtt = $attByMsg[$mid] ?? [];
        ?>

        <!-- MOD 2: data-message-id -->
        <div class="chat-row <?= $mine ? 'is-mine' : '' ?>" data-message-id="<?= (int)$m['id'] ?>">
          <div class="chat-bubble">

            <div class="chat-meta">
              <span class="chat-name"><?= htmlspecialchars($m['name'] ?? 'User') ?></span>
              <span class="chat-time"><?= htmlspecialchars($m['created_at'] ?? '') ?></span>
            </div>

            <?php if (!empty(trim((string)($m['body'] ?? '')))): ?>
              <div class="chat-text"><?= nl2br(htmlspecialchars((string)$m['body'])) ?></div>
            <?php else: ?>
              <div class="chat-text chat-text--muted">(Mesaj fără text)</div>
            <?php endif; ?>

            <?php if (!empty($mAtt)): ?>
              <div class="chat-attachments">
                <?php foreach ($mAtt as $a) renderAttachment($a); ?>
              </div>
            <?php endif; ?>

          </div>
        </div>
      <?php endforeach; ?>

      <?php if (!empty($orphan)): ?>
        <div class="chat-row">
          <div class="chat-bubble chat-bubble--system">
            <div class="chat-meta">
              <span class="chat-name">Atașamente (mai vechi)</span>
              <span class="chat-time">(fără asociere cu un mesaj)</span>
            </div>

            <div class="chat-text chat-text--muted" style="margin-bottom:8px;">
              Aceste fișiere au fost încărcate înainte de actualizarea sistemului și nu sunt legate de un mesaj.
            </div>

            <div class="chat-attachments">
              <?php foreach ($orphan as $a) renderAttachment($a); ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

    </div>

    <?php if ($ticket['status'] !== 'open'): ?>
      <div class="client-ticket__reply client-ticket__reply--closed chat-compose">
        <b>Ticket închis.</b> Nu mai poți trimite mesaje pe acest ticket.
      </div>
    <?php else: ?>
      <div class="client-ticket__reply chat-compose">
        <h3 class="h3" style="margin-top:0;">Răspuns nou</h3>

        <form method="POST" action="<?= BASE_URL ?>client/ticket-message" enctype="multipart/form-data">
          <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">

          <div class="form-row">
            <label class="label">Mesaj</label>
            <textarea class="textarea" name="message" placeholder="Scrie mesajul..." required></textarea>
          </div>

          <div class="form-row">
            <label class="label">Atașamente <span class="help">(jpg/png/webp/pdf, max 8MB)</span></label>
            <input type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.webp,.pdf">
          </div>

          <div class="form-actions">
            <button class="btn" type="submit">Trimite</button>
          </div>
        </form>
      </div>
    <?php endif; ?>

    </div><!-- /.ticket-chat -->

  </div>

</div>

<?php require __DIR__ . '/../_layout_end.php'; ?>