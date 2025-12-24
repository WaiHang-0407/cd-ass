<?php
// scripts/clear_links.php
require_once __DIR__ . '/../includes/db.php';

try {
    $pdo->exec("DELETE FROM user_links");
    $pdo->exec("DELETE FROM link_requests");
    echo "Links and requests cleared.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>