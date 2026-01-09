<?php
/**
 * Attachments modal + viewer
 * Expects:
 *  - $attachments: array of attachments rows (id, original_name, mime_type, ...)
 *  - $modalId: string unique id (default: 'mediaModal')
 */
$modalId = $modalId ?? 'mediaModal';
$attachments = $attachments ?? [];
?>
<button type="button"
        class="btn btn--secondary js-open-media"
        data-target="#<?= htmlspecialchars($modalId) ?>"
        aria-haspopup="dialog">
  📎 Media, links and docs (<?= count($attachments) ?>)
</button>

<div id="<?= htmlspecialchars($modalId) ?>" class="media-modal is-hidden" role="dialog" aria-modal="true" aria-label="Attachments">
  <div class="media-modal__backdrop js-close-media" data-close="1"></div>

  <div class="media-modal__panel" role="document">
    <div class="media-modal__header">
      <div class="media-modal__title">Media, links and docs</div>
      <button type="button" class="media-modal__close js-close-media" data-close="1" aria-label="Close">✕</button>
    </div>

    <div class="media-modal__tabs" role="tablist">
      <button type="button" class="media-tab is-active" data-filter="all">All</button>
      <button type="button" class="media-tab" data-filter="media">Media</button>
      <button type="button" class="media-tab" data-filter="docs">Docs</button>
    </div>

    <?php if (empty($attachments)): ?>
      <div class="media-modal__empty">No attachments yet.</div>
    <?php else: ?>
      <div class="media-grid" data-grid>
        <?php foreach ($attachments as $a):
          $mime = (string)($a['mime_type'] ?? '');
          $isImg = str_starts_with($mime, 'image/');
          $isPdf = ($mime === 'application/pdf');
          $inlineUrl = BASE_URL . 'ticket-attachment?id=' . (int)$a['id'] . '&inline=1';
          $downloadUrl = BASE_URL . 'ticket-attachment?id=' . (int)$a['id'] . '&download=1';
          $filter = $isImg ? 'media' : 'docs';
        ?>
          <div class="media-item" data-kind="<?= $filter ?>">
            <button type="button"
                    class="media-item__thumb js-attachment-open"
                    data-type="<?= $isImg ? 'image' : ($isPdf ? 'pdf' : 'file') ?>"
                    data-src="<?= htmlspecialchars($inlineUrl) ?>"
                    title="Open">
              <?php if ($isImg): ?>
                <img src="<?= htmlspecialchars($inlineUrl) ?>" alt="<?= htmlspecialchars($a['original_name'] ?? 'image') ?>">
              <?php else: ?>
                <div class="media-item__icon"><?= $isPdf ? '📄' : '📎' ?></div>
              <?php endif; ?>
            </button>

            <div class="media-item__meta">
              <div class="media-item__name" title="<?= htmlspecialchars($a['original_name'] ?? '') ?>">
                <?= htmlspecialchars($a['original_name'] ?? '') ?>
              </div>
              <a class="media-item__download" href="<?= htmlspecialchars($downloadUrl) ?>">Download</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- viewer (image/pdf) -->
    <div class="media-viewer is-hidden" data-viewer>
      <div class="media-viewer__backdrop js-viewer-close" data-close="1"></div>
      <div class="media-viewer__panel">
        <button type="button" class="media-viewer__close js-viewer-close" data-close="1" aria-label="Close">✕</button>
        <div class="media-viewer__content" data-viewer-content></div>
      </div>
    </div>

  </div>
</div>
