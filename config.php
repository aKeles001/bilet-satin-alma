<?php
$dbPath = __DIR__ . '/db/database.sqlite';

try {
    $conn = new PDO("sqlite:" . $dbPath);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
