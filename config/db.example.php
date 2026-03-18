<?php
// config/db.php
// Copy this file to config/db.php and fill in your credentials.
// config/db.php is in .gitignore and will NOT be committed.

$host   = 'localhost';
$dbname = 'spinfit_db';       // your MySQL database name
$user   = 'root';             // your MySQL username
$pass   = '';                 // your MySQL password

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,   false);
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}
