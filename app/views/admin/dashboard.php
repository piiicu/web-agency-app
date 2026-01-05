<?php Auth::requireRole(['admin', 'employee', 'staff']); ?>
<h2>Admin Dashboard</h2>

<ul>
  <li><a href="<?= BASE_URL ?>tasks">Taskuri (temporar)</a></li>
  <li><a href="<?= BASE_URL ?>chat">Chat (temporar)</a></li>
  <li><a href="<?= BASE_URL ?>logout">Logout</a></li>
</ul>

<p style="color:#666;">
  Următorul pas: Inbox tichete + taskuri interne + chat pe ticket.
</p>
