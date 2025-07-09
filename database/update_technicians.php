<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/update_technicians_table.sql');
    $pdo->exec($sql);

    echo "Technicians table updated successfully!";
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?> 