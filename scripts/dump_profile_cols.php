<?php
// scripts/dump_profile_cols.php
require_once __DIR__ . '/../includes/db.php';
try {
    $stmt = $pdo->query("SELECT * FROM profiles LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        print_r(array_keys($row));
    } else {
        echo "No rows found in profiles table.";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
?>