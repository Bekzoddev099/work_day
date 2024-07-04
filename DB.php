<?php

class DB {
    public static function connect(): PDO {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=work_off_tracker', 'username=beko', 'password=9999');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}
