# Web Agency App – Structure (cleaned)

## Routing
The app uses a single entry point: `index.php` and routes are passed via `?route=...`.

- `BASE_URL` includes `?route=`
- `ASSET_URL` is `BASE_URL` without `?route=` and is used for static files.

## Static assets (ONE place)
All static assets live in:

- `assets/css/app.css` – global styles (admin + client)
- `assets/css/client.css` – client-only overrides
- `assets/js/app.js` – global JS helpers (currently includes attachments modal open/close)

Pages include CSS/JS through:

- `app/views/partials/head.php`
- `app/views/partials/footer.php`

## Uploads
Uploads are stored outside `assets/`:

- `uploads/avatars/`
- `uploads/tickets/`

