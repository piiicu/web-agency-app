<?php Auth::requireRole(['client']); ?>
<h2>Client Dashboard</h2>

<ul>
  <li><a href="<?= BASE_URL ?>chat">Suport / Chat</a></li>
  <li><a href="<?= BASE_URL ?>logout">Logout</a></li>
</ul>

<p style="color:#666;">
  Următorul pas: “Tichetele mele” + status + atașamente.
</p>
