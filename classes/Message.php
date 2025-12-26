<?php
// classes/Message.php

class Message
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function send($senderID, $receiverID, $message)
    {
        if (empty($message))
            return false;

        $msgID = uniqid('MSG_');
        $stmt = $this->pdo->prepare("
            INSERT INTO messages (messageID, senderID, receiverID, message, sentAt) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$msgID, $senderID, $receiverID, $message]);
    }

    public function getConversation($user1, $user2)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM messages 
            WHERE (senderID = ? AND receiverID = ?) 
               OR (senderID = ? AND receiverID = ?)
            ORDER BY sentAt ASC
        ");
        $stmt->execute([$user1, $user2, $user2, $user1]);
        return $stmt->fetchAll();
    }

    public function getConversationsForUser($userID)
    {
        return [];
    }

    public function markRead($senderID, $receiverID)
    {
        $stmt = $this->pdo->prepare("UPDATE messages SET isRead = 1 WHERE senderID = ? AND receiverID = ?");
        $stmt->execute([$senderID, $receiverID]);
    }
}
?>