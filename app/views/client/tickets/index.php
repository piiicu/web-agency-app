<h2>Ticket-ele mele</h2>
<p><a href="<?= BASE_URL ?>client/dashboard">⬅ Înapoi</a></p>

<h3>Crează un nou ticket</h3>
<form method="POST" action="<?= BASE_URL ?>client/tickets-create">
  <input type="text" name="subject" placeholder="Subiect" style="width: 60%;" required>
  <br><br>
  <textarea name="message" placeholder="Descrie cererea dvs..." style="width: 60%; height: 120px;" required></textarea>
  <br><br>
  <button type="submit">Trimite</button>
</form>

<hr>

<h3>Ticket-ele tale</h3>
<ul>
  <?php foreach ($tickets as $t): ?>
    <li>
      <a href="<?= BASE_URL ?>client/ticket&id=<?= (int)$t['id'] ?>">
        #<?= (int)$t['id'] ?> — <?= htmlspecialchars($t['subject']) ?>
      </a>
      (<?= htmlspecialchars($t['status']) ?>)
    </li>
  <?php endforeach; ?>
</ul>
