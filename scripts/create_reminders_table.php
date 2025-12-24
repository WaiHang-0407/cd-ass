<?php
// scripts/create_reminders_table.php
require_once __DIR__ . '/../includes/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS reminders (
        reminderID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        senderID VARCHAR(50) NOT NULL,
        receiverID VARCHAR(50) NOT NULL,
        message TEXT NOT NULL,
        isRead TINYINT(1) DEFAULT 0,
        createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (senderID),
        INDEX (receiverID),
        FOREIGN KEY (senderID) REFERENCES users(userID) ON DELETE CASCADE,
        FOREIGN KEY (receiverID) REFERENCES users(userID) ON DELETE CASCADE
    ) ENGINE=InnoDB;";

    $pdo->exec($sql);
    echo "Table 'reminders' created successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>