<?php require __DIR__ . '/../_layout_start.php'; ?>

<h2>Ticket #<?= (int)$ticket['id'] ?> — <?= htmlspecialchars($ticket['subject']) ?></h2>
<p><a href="<?= BASE_URL ?>admin/tickets">⬅ Back to inbox</a></p>

<p>Client: <b><?= htmlspecialchars($ticket['client_name']) ?></b></p>

<form method="POST" action="<?= BASE_URL ?>admin/ticket-status" style="margin: 10px 0;">
  <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
  <label>Status:</label>
  <select name="status">
    <?php foreach (['open','resolved'] as $s): ?>
      <option value="<?= $s ?>" <?= $ticket['status'] === $s ? 'selected' : '' ?>>
        <?= $s ?>
      </option>
    <?php endforeach; ?>
  </select>
  <button type="submit">Update</button>
</form>

<hr>

<div style="border:1px solid #ddd; padding:10px;">
  <?php foreach ($messages as $m): ?>
    <p>
      <b><?= htmlspecialchars($m['name']) ?>:</b>
      <?= nl2br(htmlspecialchars($m['body'])) ?>
      <?php if ((int)$m['is_internal'] === 1): ?>
        <span style="background:#ffe7a8; padding:2px 6px; border-radius:6px; font-size:12px;">INTERNAL</span>
      <?php endif; ?>
      <span style="color:#999; font-size:12px;">(<?= htmlspecialchars($m['created_at']) ?>)</span>
    </p>
  <?php endforeach; ?>
</div>

<hr>

<div class="ticket-compose-header">
  <h3>Scrie mesajul</h3>

  <?php if (!empty($attachments)): ?>
    <button type="button" class="btn btn--secondary js-open-media" data-target="#mediaModal">
      📎 Media, links and docs (<?= count($attachments) ?>)
    </button>
  <?php endif; ?>
</div>

<form method="POST" action="<?= BASE_URL ?>admin/ticket-message" enctype="multipart/form-data">
  <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
  <textarea name="body" placeholder="Write message / internal note..." style="width: 70%; height: 120px;" required></textarea>
  <br><br>
  <label>
    <input type="checkbox" name="is_internal" value="1">
    Internal note (client can't see)
  </label>
  <br><br>
  <input type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.webp,.pdf">
  <br><br>
  <button type="submit">Send</button>
</form>

<?php if (!empty($attachments)): ?>
  <hr>
  <h3>Attachments</h3>
  <ul>
    <?php foreach ($attachments as $a): ?>
      <li>
        <a href="<?= BASE_URL ?>ticket-attachment&id=<?= (int)$a['id'] ?>" target="_blank">
          <?= htmlspecialchars($a['original_name']) ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<!-- Popup atasamente -->
<?php if (!empty($attachments)): ?>
<div class="media-modal" id="mediaModal" aria-hidden="true">
  <div class="media-modal__backdrop js-close-media" data-target="#mediaModal"></div>

  <div class="media-modal__panel" role="dialog" aria-modal="true">
    <div class="media-modal__top">
      <div class="media-modal__title">Media, links and docs</div>
      <button type="button" class="media-modal__close js-close-media" data-target="#mediaModal">✕</button>
    </div>

    <div class="media-tabs">
      <button type="button" class="media-tab is-active" data-filter="all">All</button>
      <button type="button" class="media-tab" data-filter="images">Media</button>
      <button type="button" class="media-tab" data-filter="pdf">Docs</button>
    </div>

    <div class="media-grid">
      <?php foreach ($attachments as $a): ?>
        <?php
          $id = (int)$a['id'];
          $name = htmlspecialchars($a['original_name']);
          $mime = $a['mime_type'] ?? '';
          $isImg = is_string($mime) && str_starts_with($mime, 'image/');
          $isPdf = ($mime === 'application/pdf');
          $typeClass = $isImg ? 'images' : ($isPdf ? 'pdf' : 'other');
          $previewUrl = BASE_URL . "ticket-attachment&id={$id}&inline=1";
          $downloadUrl = BASE_URL . "ticket-attachment&id={$id}&download=1";
        ?>

        <div class="media-item" data-type="<?= $typeClass ?>">
          <a class="media-item__preview" href="<?= $previewUrl ?>" target="_blank" rel="noopener">
            <?php if ($isImg): ?>
              <img src="<?= $previewUrl ?>" alt="<?= $name ?>">
            <?php elseif ($isPdf): ?>
              <div class="media-doc">📄 PDF</div>
            <?php else: ?>
              <div class="media-doc">📎 FILE</div>
            <?php endif; ?>
          </a>

          <div class="media-item__meta" title="<?= $name ?>"><?= $name ?></div>

          <a class="media-item__download" href="<?= $downloadUrl ?>" title="Descarcă">⬇</a>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</div>
<?php endif; ?>


<?php require __DIR__ . '/../_layout_end.php'; ?>


