<?php if (!defined('ASSET_URL')) { die('ASSET_URL not defined'); } ?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Web Agency App</title>

  <!-- CSS: load global styles everywhere, and add client overrides for client role -->
  <link rel="stylesheet" href="<?= ASSET_URL ?>assets/css/app.css?v=<?= time() ?>">
  <?php if (class_exists('Auth') && Auth::check() && Auth::role() === 'client'): ?>
    <link rel="stylesheet" href="<?= ASSET_URL ?>assets/css/client.css?v=<?= time() ?>">
  <?php endif; ?>
</head>
<body>
