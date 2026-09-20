<?php
/**
 * Database connection (PDO).
 * XAMPP / WAMP default values. Change only if your setup is different.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'studyspot');
define('DB_USER', 'root');
define('DB_PASS', '');            // XAMPP = empty, MAMP = 'root'

define('SITE_NAME', 'StudySpot');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
