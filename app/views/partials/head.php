<?php if (!defined('ASSET_URL')) { die('ASSET_URL not defined'); } ?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Web Agency App</title>

  <!-- Shared CSS -->
  <link rel="stylesheet" href="<?= ASSET_URL ?>assets/css/base.css">
  <link rel="stylesheet" href="<?= ASSET_URL ?>assets/css/components.css">
  <link rel="stylesheet" href="<?= ASSET_URL ?>assets/css/layout.css">

  <!-- Role-specific -->
  <?php if (class_exists('Auth') && Auth::check() && Auth::role() === 'client'): ?>
    <link rel="stylesheet" href="<?= ASSET_URL ?>assets/css/client.css">
  <?php endif; ?>

  <?php if (class_exists('Auth') && Auth::check() && Auth::role() !== 'client'): ?>
    <link rel="stylesheet" href="<?= ASSET_URL ?>assets/css/admin.css">
  <?php endif; ?>
</head>
<body>
