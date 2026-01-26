<?php require __DIR__ . '/../_layout_start.php'; ?>
<div class="client-tickets">
    <h2>Ticket-ele mele</h2>
    <p><a href="<?= BASE_URL ?>client/dashboard">⬅ Înapoi</a></p>

    <h3>Crează un nou ticket</h3>
    <form method="POST" action="<?= BASE_URL ?>client/tickets-create" enctype="multipart/form-data">
        <input type="text" name="subject" placeholder="Subiect" style="width: 60%;" required>
        <br><br>
        <textarea name="message" placeholder="Descrie cererea dvs..." style="width: 60%; height: 120px;" required></textarea>
        <br><br>
        <input type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.webp,.pdf">
        <br><br>
        <button type="submit">Trimite</button>
    </form>

    <hr>

    <h3>Ticket-ele tale</h3>

    <style>
        table {
            border-collapse: collapse;
        }

        th {
            background: #f5f5f5;
        }

        tr:hover {
            background: #fafafa;
        }
    </style>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
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
                        <?php if ($t['status'] === 'open'): ?>
                            🟢 Deschis
                        <?php else: ?>
                            🔴 Închis
                        <?php endif; ?>
                    </td>

                    <td>
                        <?= date('d.m.Y H:i', strtotime($t['created_at'])) ?>
                    </td>
                </tr>
            <?php endforeach; ?>

        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../_layout_end.php'; ?>