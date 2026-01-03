<?php

class DB {
    public static function connect() {
        return new PDO(
            "mysql:host=localhost;dbname=web_agency_app;charset=utf8",
            "root",
            "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
}
