<?php

// IMPORTANT: BASE_URL must include ?route= because routing uses $_GET['route']
define('BASE_URL', 'http://localhost/web-agency-app/?route=');

// For static assets (css/js/images) we need URL without ?route=
define('ASSET_URL', str_replace('?route=', '', BASE_URL));


