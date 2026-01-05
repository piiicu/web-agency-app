<?php
// app/views/admin/dashboard.php
Auth::requireRole(['admin']);
require __DIR__ . '/_nav.php';
?>

<h2>Panou de control administrator</h2>

<p>Alege o secțiune din meniul de sus.</p>

<div style="margin-top: 12px; border:1px solid #ddd; padding: 12px;">
  <h3>Scurtături</h3>
  <ul>
    <li><a href="<?= BASE_URL ?>admin/tickets">Tickets (Inbox)</a></li>
    <li><a href="<?= BASE_URL ?>admin/internal-tasks">Internal Tasks</a></li>
    <li><a href="<?= BASE_URL ?>admin/clients">Clients</a></li>
    <li><a href="<?= BASE_URL ?>admin/settings">Settings</a></li>
  </ul>
</div>
