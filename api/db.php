<?php
// api/db.php → Database connection

$host = '127.0.0.1';      // localhost
$db   = 'studyhub';       // your database name
$user = 'root';           // default XAMPP username
$pass = '';               // default XAMPP password (empty by default)
$charset = 'utf8mb4';     // supports all characters

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch as array
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
