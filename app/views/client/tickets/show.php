<?php Auth::requireRole(['client']); ?>
<?php require __DIR__ . '/../../partials/head.php'; ?>
<div class="container client-ticket">
    <p><a href="<?= BASE_URL ?>client/tickets">⬅ Înapoi la Ticket-ele mele</a></p>

    <style>
        .wrap {
            max-width: 900px;
        }

        .head {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 14px;
            background: #fff;
            margin-bottom: 14px;
        }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            font-weight: 700;
        }

        .open {
            background: #dcfce7;
            color: #166534;
            border-color: #bbf7d0;
        }

        .closed {
            background: #fee2e2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .timeline {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
            overflow: hidden;
        }

        .msg {
            padding: 14px;
            border-top: 1px solid #f1f5f9;
        }

        .msg:first-child {
            border-top: none;
        }

        .meta {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .bubble {
            white-space: pre-wrap;
        }

        .attachments {
            margin-top: 10px;
        }

        .attachments a {
            display: inline-block;
            margin-right: 10px;
            margin-top: 6px;
        }

        .reply {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 14px;
            background: #fff;
            margin-top: 14px;
        }

        textarea {
            width: 100%;
            min-height: 120px;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
        }

        .btn {
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid #111;
            background: #111;
            color: #fff;
            cursor: pointer;
            font-weight: 700;
        }
    </style>

    <div class="wrap">

        <div class="head">
            <h2 style="margin:0 0 6px;">#<?= (int)$ticket['id'] ?> — <?= htmlspecialchars($ticket['subject']) ?></h2>

            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <span class="badge <?= ($ticket['status'] === 'open') ? 'open' : 'closed' ?>">
                    <?= ($ticket['status'] === 'open') ? '🟢 Deschis' : '🔴 Închis' ?>
                </span>

                <span style="color:#6b7280;">
                    Creat: <?= htmlspecialchars($ticket['created_at']) ?>
                </span>
            </div>

            <?php if (!empty($attachments)): ?>
                <div class="attachments">
                    <b>Atașamente:</b><br>
                    <?php foreach ($attachments as $a): ?>
                        <a href="<?= BASE_URL ?>ticket-attachment&id=<?= (int)$a['id'] ?>" target="_blank">
                            📎 <?= htmlspecialchars($a['original_name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="timeline">
            <?php if (empty($messages)): ?>
                <div class="msg">Nu există mesaje încă.</div>
            <?php endif; ?>

            <?php foreach ($messages as $m): ?>
                <div class="msg">
                    <div class="meta">
                        <b><?= htmlspecialchars($m['name'] ?? 'User') ?></b>
                        • <?= htmlspecialchars($m['created_at'] ?? '') ?>
                    </div>
                    <div class="bubble">
                        <?php if (!empty(trim($m['body'] ?? ''))): ?>
                            <?= nl2br(htmlspecialchars($m['body'])) ?>
                        <?php else: ?>
                            <span style="color:#9ca3af;">(Mesaj fără text)</span>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($ticket['status'] !== 'open'): ?>
            <div class="reply" style="background:#fff7ed; border-color:#fed7aa;">
                <b>Ticket închis.</b> Nu mai poți trimite mesaje pe acest ticket.
            </div>
        <?php else: ?>
            <div class="reply">
                <div class="ticket-compose-header">
                    <h3 style="margin-top:0;">Răspuns nou</h3>

                    <?php if (!empty($attachments)): ?>
  <?php $modalId = 'mediaModal'; require __DIR__ . '/../../partials/attachments_modal.php'; ?>
<?php endif; ?>
                </div>


                <form method="POST" action="<?= BASE_URL ?>client/ticket-message" enctype="multipart/form-data">
                    <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">

                    <div style="margin-bottom:10px;">
                        <textarea name="message" placeholder="Scrie mesajul..." required></textarea>
                    </div>

                    <div style="margin-bottom:12px;">
                        <label><b>Atașamente</b> (jpg/png/webp/pdf, max 8MB)</label><br>
                        <input type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.webp,.pdf">
                    </div>

                    <button class="btn" type="submit">Trimite</button>
                </form>
            </div>
        <?php endif; ?>

    </div>

</div>
<?php require __DIR__ . '/../../partials/footer.php'; ?>