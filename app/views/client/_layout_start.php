<?php Auth::requireRole(['client']); ?>
<?php require __DIR__ . '/../partials/head.php'; ?>

<?php
$currentRoute = $_GET['route'] ?? '';
$active = function($needle) use ($currentRoute) {
  return strpos($currentRoute, $needle) === 0 ? 'is-active' : '';
};
?>

<div class="app-shell app-shell--client" data-app-shell>
  <header class="app-topbar" role="banner">
    <div class="app-topbar__inner">
      <div class="app-topbar__brand">
        <span class="app-topbar__dot"></span>
        <span class="app-topbar__title">Client</span>
      </div>
      <button class="app-topbar__menu" type="button" data-sidebar-open aria-label="Open menu">☰</button>
    </div>
  </header>

  <main class="app-main" role="main">
    <div class="app-container">
      <div class="app-content content">
