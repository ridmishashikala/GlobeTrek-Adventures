<?php
/**
 * db_connection.php
 * Database Connection Utility using PDO
 * Preconfigured for WAMP server (host: localhost, user: root, password: "")
 */

$host = 'localhost';
$db   = 'globetrek_db';
$user = 'root';
$pass = ''; // Default password in WampServer phpMyAdmin is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Enable exceptions for queries
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Disable emulation for security
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Academic requirements: clear, readable error message without exposing credentials
    die("Database Connection Failed: " . htmlspecialchars($e->getMessage()));
}
?>
