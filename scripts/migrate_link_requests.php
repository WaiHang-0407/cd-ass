<?php
// scripts/migrate_link_requests.php
require_once __DIR__ . '/../includes/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS link_requests (
        requestID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        initiatorID VARCHAR(50) NOT NULL,
        targetID VARCHAR(50) NOT NULL,
        createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (initiatorID),
        INDEX (targetID),
        FOREIGN KEY (initiatorID) REFERENCES users(userID) ON DELETE CASCADE,
        FOREIGN KEY (targetID) REFERENCES users(userID) ON DELETE CASCADE,
        UNIQUE KEY unique_request (initiatorID, targetID)
    ) ENGINE=InnoDB;";

    $pdo->exec($sql);
    echo "Table 'link_requests' created successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>