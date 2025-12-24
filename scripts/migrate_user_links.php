<?php
// scripts/migrate_user_links.php
require_once '../includes/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS user_links (
        linkID INTEGER PRIMARY KEY AUTOINCREMENT,
        caretakerID VARCHAR(50) NOT NULL,
        patientID VARCHAR(50) NOT NULL,
        createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (caretakerID) REFERENCES users(userID),
        FOREIGN KEY (patientID) REFERENCES users(userID)
    )";
    $pdo->exec($sql);
    echo "Table 'user_links' created successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>