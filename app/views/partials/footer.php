<script>
  // root curat, fără ?route=
  window.APP_ROOT = "<?= rtrim(ASSET_URL, '/') . '/' ?>";
</script>

<script src="<?= ASSET_URL ?>assets/js/app.js?v=<?= time() ?>" defer></script>
</body>
</html>

