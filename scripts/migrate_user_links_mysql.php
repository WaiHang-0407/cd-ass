<?php
// scripts/migrate_user_links_mysql.php
require_once __DIR__ . '/../includes/db.php';

try {
    // MySQL Syntax
    $sql = "CREATE TABLE IF NOT EXISTS user_links (
linkID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
caretakerID VARCHAR(50) NOT NULL,
patientID VARCHAR(50) NOT NULL,
createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
INDEX (caretakerID),
INDEX (patientID),
FOREIGN KEY (caretakerID) REFERENCES users(userID) ON DELETE CASCADE,
FOREIGN KEY (patientID) REFERENCES users(userID) ON DELETE CASCADE
) ENGINE=InnoDB;";

    $pdo->exec($sql);
    echo "Table 'user_links' created successfully (MySQL).";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>