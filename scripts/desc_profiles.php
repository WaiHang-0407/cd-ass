<?php
// scripts/desc_profiles.php
require_once __DIR__ . '/../includes/db.php';

try {
    $stmt = $pdo->query("DESCRIBE profiles");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in profiles:\n";
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>