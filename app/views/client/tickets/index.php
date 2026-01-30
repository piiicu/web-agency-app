<?php require __DIR__ . '/../_layout_start.php'; ?>

<div class="client-tickets">
  <div class="page-header">
    <div class="page-header__left">
      <h1 class="page-header__title">Ticket-ele mele</h1>
      <p class="page-header__subtitle">Creează un ticket nou sau vezi istoricul conversațiilor.</p>
    </div>
    <div class="page-header__actions">
      <a class="btn btn-ghost" href="<?= BASE_URL ?>client/dashboard">⬅ Înapoi</a>
    </div>
  </div>

  <div class="card" style="margin-bottom:16px;">
    <h3 class="h3">Creează un nou ticket</h3>

    <form method="POST" action="<?= BASE_URL ?>client/tickets-create" enctype="multipart/form-data">
      <div class="form-stack">
        <input class="input" type="text" name="subject" placeholder="Subiect" required>
        <textarea class="textarea" name="message" placeholder="Descrie cererea dvs..." required></textarea>
        <div class="form-row">
          <label class="label">Atașamente <span class="help">(jpg/png/webp/pdf, max 8MB)</span></label>
          <input type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.webp,.pdf">
        </div>
        <div class="form-actions">
          <button class="btn" type="submit">Trimite</button>
        </div>
      </div>
    </form>
  </div>

  <h3 class="h3">Ticket-ele tale</h3>

  <div class="table-wrap rtable">
  <table class="table">
        <thead>
            <tr>
                <th align="left">#</th>
                <th align="left">Subiect</th>
                <th align="left">Status</th>
                <th align="left">Data</th>
            </tr>
        </thead>
        <tbody>

            <?php if (empty($tickets)): ?>
              <tr>
                <td colspan="4">Nu ai încă niciun ticket.</td>
              </tr>
            <?php endif; ?>

            <?php foreach ($tickets as $t): ?>
                <tr>
                    <td>#<?= (int)$t['id'] ?></td>

                    <td>
                        <a href="<?= BASE_URL ?>client/ticket&id=<?= (int)$t['id'] ?>">
                            <?= htmlspecialchars($t['subject']) ?>
                        </a>
                    </td>

                    <td>
                      <?php $st = (string)($t['status'] ?? ''); ?>
                      <span class="badge badge--<?= htmlspecialchars($st) ?>"><?= htmlspecialchars(ticketStatusLabel($st)) ?></span>
                    </td>

                    <td>
                        <?= date('d.m.Y H:i', strtotime($t['created_at'])) ?>
                    </td>
                </tr>
            <?php endforeach; ?>

        </tbody>
    </table>
  </div>

  <!-- Mobile cards -->
  <div class="rtable-cards" aria-label="Tickets list">
    <?php if (empty($tickets)): ?>
      <div class="data-card">
        <p style="margin:0;">Nu ai încă niciun ticket.</p>
      </div>
    <?php endif; ?>

    <?php foreach ($tickets as $t): ?>
      <?php $st = (string)($t['status'] ?? ''); ?>
      <div class="data-card">
        <div class="data-card__top">
          <div>
            <p class="data-card__title" style="margin:0;">#<?= (int)$t['id'] ?> — <?= htmlspecialchars($t['subject']) ?></p>
            <div class="data-card__meta">
              <div><b>Status:</b> <span class="badge badge--<?= htmlspecialchars($st) ?>"><?= htmlspecialchars(ticketStatusLabel($st)) ?></span></div>
              <div><b>Data:</b> <?= date('d.m.Y H:i', strtotime($t['created_at'])) ?></div>
            </div>
          </div>
        </div>
        <div class="data-card__actions">
          <a class="btn" href="<?= BASE_URL ?>client/ticket&id=<?= (int)$t['id'] ?>">Deschide</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require __DIR__ . '/../_layout_end.php'; ?>