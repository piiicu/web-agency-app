<?php if (!defined('ASSET_URL')) { die('ASSET_URL not defined'); } ?>
<?php
// View helpers (safe globals)
if (!function_exists('ticketStatusLabel')) {
  function ticketStatusLabel(string $status): string
  {
    return match ($status) {
      'open' => 'Deschis',
      'resolved' => 'Rezolvat',
      'deleted' => 'Șters',
      default => $status,
    };
  }
}
?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Web Agency App</title>

  <!-- CSS: load global styles everywhere, and add client overrides for client role -->
  <link rel="stylesheet" href="<?= ASSET_URL ?>assets/css/app.css?v=<?= time() ?>">
  <?php // IMPORTANT: Auth::check() redirects; for styling checks we must NOT redirect.
  // Use the session-backed user() instead.
  if (class_exists('Auth') && Auth::user() && Auth::role() === 'client'): ?>
    <link rel="stylesheet" href="<?= ASSET_URL ?>assets/css/client.css?v=<?= time() ?>">
  <?php endif; ?>
</head>
<body>
