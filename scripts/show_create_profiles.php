<?php
// scripts/show_create_profiles.php
require_once __DIR__ . '/../includes/db.php';

try {
    $stmt = $pdo->query("SHOW CREATE TABLE profiles");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $result['Create Table'];
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>