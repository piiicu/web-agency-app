<?php

/**
 * Routing in this project uses `?route=...`.
 *
 * BASE_URL  -> for internal app links (must include `?route=`)
 * ASSET_URL -> for static assets (css/js/images) (must NOT include `?route=`)
 *
 * We generate these dynamically so the project works no matter what folder
 * name you use in htdocs (e.g. /web-agency-app/ or /web-agency-app-main/).
 */

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$scheme  = $isHttps ? 'https' : 'http';
$host    = $_SERVER['HTTP_HOST'] ?? 'localhost';

// script path to /index.php -> we want the folder path
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$basePath = $basePath === '' ? '/' : ($basePath . '/');

// Example:
//  BASE_URL  = http://localhost/web-agency-app/?route=
//  ASSET_URL = http://localhost/web-agency-app/
define('BASE_URL',  $scheme . '://' . $host . $basePath . '?route=');
define('ASSET_URL', $scheme . '://' . $host . $basePath);
