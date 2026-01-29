<?php
// expects: $users (array), optional $inviteLink (string|null)
// optional: $redirectTarget ('settings' to redirect back to settings users tab)
$redirectTarget = $redirectTarget ?? '';
?>

<?php if (!empty($inviteLink)): ?>
  <div class="card" style="margin-bottom:12px;">
    <div style="font-weight:600; margin-bottom:10px;">Invite link (copy &amp; send to user):</div>

    <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
      <input
        id="inviteLinkInput"
        class="input"
        style="min-width:260px; flex: 1;"
        value="<?= htmlspecialchars($inviteLink) ?>"
        readonly>

      <button type="button" class="btn" onclick="copyInviteLink()">Copiază</button>

      <span id="copyStatus" style="margin-left:6px;color:green;display:none;">✔ Copiat</span>
    </div>
  </div>
<?php endif; ?>

<div class="card" style="margin-bottom:12px;">
  <h3 class="section-title" style="margin-top:0;">Crează utilizatori</h3>
  <form method="POST" action="<?= BASE_URL ?>admin/users-create">
    <?php if ($redirectTarget): ?>
      <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectTarget) ?>">
    <?php endif; ?>

    <div class="form-row">
      <label class="label">Nume utilizator</label>
      <input class="input" name="name" placeholder="Nume" required>
    </div>

    <div class="form-row">
      <label class="label">Email</label>
      <input class="input" name="email" type="email" placeholder="email@example.com" required>
    </div>

    <div class="form-row">
      <label class="label">Rolul utilizatorului</label>
      <select class="input" name="role" required>
        <option value="client">Client</option>
        <option value="admin">Admin</option>
      </select>
    </div>

    <button class="btn" type="submit">Crează + Generează invitație</button>
  </form>
</div>

<div class="card">
  <h3 class="section-title" style="margin-top:0;">Users</h3>

  <div style="overflow:auto;">
    <table class="table" style="min-width:680px;">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nume</th>
          <th>Email</th>
          <th>Rolul</th>
          <th style="text-align:center;">Acțiuni</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= (int)$u['id'] ?></td>
            <td><?= htmlspecialchars($u['name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['role']) ?></td>
            <td style="text-align:center;">
              <?php if ((int)$u['id'] !== (int)Auth::id()): ?>
                <form method="POST" action="<?= BASE_URL ?>admin/users-invite" style="display:inline;">
                  <?php if ($redirectTarget): ?>
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectTarget) ?>">
                  <?php endif; ?>
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                  <button class="btn" type="submit">Generează invitație</button>
                </form>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  function copyInviteLink() {
    const input = document.getElementById('inviteLinkInput');
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);

    navigator.clipboard.writeText(input.value).then(() => {
      const status = document.getElementById('copyStatus');
      if (!status) return;
      status.style.display = 'inline';
      setTimeout(() => status.style.display = 'none', 2000);
    });
  }
</script>
