<?php

$pdo = new PDO(
    "mysql:host=localhost;dbname=web_agency_app;charset=utf8mb4",
    "root",
    ""
);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
