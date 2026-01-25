<?php require __DIR__ . '/../_layout_start.php'; ?>

<h2>Admin → Users</h2>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <p style="color:red;"><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_success'])): ?>
  <p style="color:green;"><?= htmlspecialchars($_SESSION['flash_success']) ?></p>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($inviteLink)): ?>
  <div style="border:1px solid #ddd; padding:10px; margin:10px 0;">
    <b>Invite link (copy & send to user):</b><br><br>

    <input
      id="inviteLinkInput"
      style="width:70%;"
      value="<?= htmlspecialchars($inviteLink) ?>"
      readonly>

    <button type="button" onclick="copyInviteLink()">
      Copiază
    </button>

    <span id="copyStatus" style="margin-left:10px;color:green;display:none;">
      ✔ Copiat
    </span>
  </div>
<?php endif; ?>

<h3>Create user</h3>
<form method="POST" action="<?= BASE_URL ?>admin/users-create">
  <input name="name" placeholder="User name" required>
  <input name="email" placeholder="email@example.com" required>

  <select name="role" required>
    <option value="client">Client</option>
    <option value="admin">Admin</option>
  </select>

  <button type="submit">Create + Generate Invite</button>
</form>

<hr>

<h3>Users</h3>
<table border="1" cellpadding="8" cellspacing="0">
  <tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Role</th>
    <th>Actions</th>
  </tr>

  <?php foreach ($users as $u): ?>
    <tr>
      <td><?= (int)$u['id'] ?></td>
      <td><?= htmlspecialchars($u['name']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><?= htmlspecialchars($u['role']) ?></td>
      <td>
        <?php if ((int)$u['id'] !== (int)Auth::id()): ?>
          <form method="POST" action="<?= BASE_URL ?>admin/users-invite" style="display:inline;">
            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
            <button type="submit">Generate Invite</button>
          </form>
        <?php else: ?>
          —
        <?php endif; ?>
      </td>

    </tr>
  <?php endforeach; ?>
</table>

<script>
  function copyInviteLink() {
    const input = document.getElementById('inviteLinkInput');
    input.select();
    input.setSelectionRange(0, 99999); // mobile safe

    navigator.clipboard.writeText(input.value).then(() => {
      const status = document.getElementById('copyStatus');
      status.style.display = 'inline';
      setTimeout(() => status.style.display = 'none', 2000);
    });
  }
</script>

<?php require __DIR__ . '/../_layout_end.php'; ?>