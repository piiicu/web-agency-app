<?php require __DIR__ . '/../_layout_start.php'; ?>

<div class="ticket-page">
  <div class="page-header">
    <div>
      <h1 class="page-header__title">Tichet #<?= (int)$ticket['id'] ?> — <?= htmlspecialchars($ticket['subject']) ?></h1>
      <p class="page-header__subtitle">Client: <b><?= htmlspecialchars($ticket['client_name']) ?></b></p>
    </div>

    <div class="page-header__actions">
      <a class="btn btn-ghost" href="<?= BASE_URL ?>admin/tickets">⬅ Înapoi la inbox</a>

      <form method="POST" action="<?= BASE_URL ?>admin/ticket-status" class="ticket-status-form">
  <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
  <label class="label" for="ticket_status">Status</label>
  <select id="ticket_status" name="status" class="select" style="width:auto; min-width: 160px;">
    <?php foreach (['open','resolved'] as $s): ?>
      <option value="<?= $s ?>" <?= $ticket['status'] === $s ? 'selected' : '' ?>>
        <?= $s ?>
      </option>
    <?php endforeach; ?>
  </select>
  <button class="btn" type="submit">Actualizează</button>
      </form>
    </div>
  </div>

<?php
  // Build a map of attachments by message_id
  $attByMsg = [];
  $orphan = [];
  foreach (($attachments ?? []) as $a) {
    $mid = (int)($a['message_id'] ?? 0);
    if ($mid > 0) {
      $attByMsg[$mid][] = $a;
    } else {
      $orphan[] = $a;
    }
  }

  function isImg(?string $mime): bool {
    return is_string($mime) && str_starts_with($mime, 'image/');
  }
  function isPdf(?string $mime): bool {
    return $mime === 'application/pdf';
  }
?>

<!-- Serach bar conversatie-->
<div class="chat-search">
  <input type="search" class="chat-search__input" placeholder="Caută în conversație..." data-chat-search>
  <div class="chat-search__meta" data-chat-search-meta></div>
  <div class="chat-search__actions">
    <button type="button" class="chat-search__btn" data-chat-search-prev title="Previous">↑</button>
    <button type="button" class="chat-search__btn" data-chat-search-next title="Next">↓</button>
    <button type="button" class="chat-search__btn" data-chat-search-clear title="Clear">✕</button>
  </div>
</div>


<div class="ticket-chat">

<!-- MOD 1: data-ticket-id + data-role -->
<div class="chat-thread" data-chat-thread data-chat-container data-ticket-id="<?= (int)$ticket['id'] ?>" data-role="admin">
  <?php if (empty($messages)): ?>
    <div class="chat-empty">Nu există mesaje încă.</div>
  <?php endif; ?>

  <?php foreach ($messages as $m): ?>
    <?php
      $mine = (Auth::id() && (int)$m['sender_id'] === (int)Auth::id());
      $internal = ((int)($m['is_internal'] ?? 0) === 1);
      $mid = (int)$m['id'];
      $mAtt = $attByMsg[$mid] ?? [];
    ?>

    <!-- MOD 2: data-message-id -->
    <div class="chat-row <?= $mine ? 'is-mine' : '' ?> <?= $internal ? 'is-internal' : '' ?>" data-message-id="<?= (int)$m['id'] ?>">
      <div class="chat-bubble">
        <div class="chat-meta">
          <span class="chat-name"><?= htmlspecialchars($m['name'] ?? 'User') ?></span>
          <?php if ($internal): ?>
            <span class="chat-pill">INTERNAL</span>
          <?php endif; ?>
          <span class="chat-time"><?= htmlspecialchars($m['created_at'] ?? '') ?></span>
        </div>

        <?php if (!empty(trim((string)($m['body'] ?? '')))): ?>
          <div class="chat-text"><?= nl2br(htmlspecialchars((string)$m['body'])) ?></div>
        <?php else: ?>
          <div class="chat-text chat-text--muted">(Mesaj fără text)</div>
        <?php endif; ?>

        <?php if (!empty($mAtt)): ?>
          <div class="chat-attachments">
            <?php foreach ($mAtt as $a): ?>
              <?php
                $id = (int)$a['id'];
                $name = (string)($a['original_name'] ?? 'file');
                $mime = (string)($a['mime_type'] ?? 'application/octet-stream');
                $openUrl = BASE_URL . 'ticket-attachment&id=' . $id . '&inline=1';
                $downloadUrl = BASE_URL . 'ticket-attachment&id=' . $id . '&download=1';
              ?>

              <?php if (isImg($mime)): ?>
                <a class="chat-attachment chat-attachment--image" href="<?= htmlspecialchars($openUrl) ?>" target="_blank" rel="noopener">
                  <img src="<?= htmlspecialchars($openUrl) ?>" alt="<?= htmlspecialchars($name) ?>" loading="lazy">
                </a>
                <a class="chat-attachment-link" href="<?= htmlspecialchars($downloadUrl) ?>">Descarcă</a>
              <?php elseif (isPdf($mime)): ?>
                <div class="chat-attachment chat-attachment--file">
                  <div class="chat-file">
                    <div class="chat-file__icon">PDF</div>
                    <div class="chat-file__meta">
                      <div class="chat-file__name"><?= htmlspecialchars($name) ?></div>
                      <div class="chat-file__actions">
                        <a href="<?= htmlspecialchars($openUrl) ?>" target="_blank" rel="noopener">Deschide</a>
                        <a href="<?= htmlspecialchars($downloadUrl) ?>">Descarcă</a>
                      </div>
                    </div>
                  </div>
                </div>
              <?php else: ?>
                <div class="chat-attachment chat-attachment--file">
                  <div class="chat-file">
                    <div class="chat-file__icon">FILE</div>
                    <div class="chat-file__meta">
                      <div class="chat-file__name"><?= htmlspecialchars($name) ?></div>
                      <div class="chat-file__actions">
                        <a href="<?= htmlspecialchars($openUrl) ?>" target="_blank" rel="noopener">Deschide</a>
                        <a href="<?= htmlspecialchars($downloadUrl) ?>">Descarcă</a>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if (!empty($orphan)): ?>
    <div class="chat-row">
      <div class="chat-bubble">
        <div class="chat-meta">
          <span class="chat-name">Atașamente (mai vechi)</span>
          <span class="chat-time">(fără asociere cu un mesaj)</span>
        </div>
        <div class="chat-attachments">
          <?php foreach ($orphan as $a): ?>
            <?php
              $id = (int)$a['id'];
              $name = (string)($a['original_name'] ?? 'file');
              $mime = (string)($a['mime_type'] ?? 'application/octet-stream');
              $openUrl = BASE_URL . 'ticket-attachment&id=' . $id . '&inline=1';
              $downloadUrl = BASE_URL . 'ticket-attachment&id=' . $id . '&download=1';
            ?>
            <?php if (isImg($mime)): ?>
              <a class="chat-attachment chat-attachment--image" href="<?= htmlspecialchars($openUrl) ?>" target="_blank" rel="noopener">
                <img src="<?= htmlspecialchars($openUrl) ?>" alt="<?= htmlspecialchars($name) ?>" loading="lazy">
              </a>
              <a class="chat-attachment-link" href="<?= htmlspecialchars($downloadUrl) ?>">Descarcă</a>
            <?php else: ?>
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
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

<!-- Composer -->
</div>

<div class="card chat-compose" style="margin-top:16px;">
  <h3 class="h3">Scrie mesajul</h3>

  <form method="POST" action="<?= BASE_URL ?>admin/ticket-message" enctype="multipart/form-data">
    <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">

    <textarea class="textarea" name="body" placeholder="Scrie mesaj / notă internă…" required></textarea>

    <div class="form-inline" style="margin: 10px 0 12px;">
      <label class="form-inline">
        <input type="checkbox" name="is_internal" value="1">
        <span>Mesaje interne (clientul nu vede mesajul)</span>
      </label>
    </div>

    <div class="form-actions">
      <input type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.webp,.pdf">
      <button class="btn" type="submit">Trimite</button>
    </div>
  </form>
</div>

</div>

<?php require __DIR__ . '/../_layout_end.php'; ?>
