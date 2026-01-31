<?php require __DIR__ . '/../partials/head.php'; ?>

<?php
$currentRoute = $_GET['route'] ?? '';
function navActive($needle, $current) {
  return strpos($current, $needle) === 0 ? 'is-active' : '';
}
?>

<div class="app-shell app-shell--admin" data-app-shell>
  <!-- Mobile top bar (shown only on small screens via CSS) -->
  <header class="app-topbar" role="banner">
    <div class="app-topbar__inner">
      <div class="app-topbar__brand">
        <span class="app-topbar__dot"></span>
        <span class="app-topbar__title">Web app</span>
      </div>
      <button class="app-topbar__menu" type="button" data-sidebar-open aria-label="Open menu">☰</button>
    </div>
  </header>

  <main class="app-main" role="main">
    <div class="app-container">
      <div class="app-content content">