<?php
// ajax/shopping_list.php
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$user = getCurrentUser(); // Returns User object or null
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$item = trim($input['item'] ?? '');

if (empty($item) && !in_array($action, ['get', 'clear_all', 'remove_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid Item']);
    exit;
}

try {
    if ($action === 'toggle') {
        // Check if exists
        $stmt = $pdo->prepare("SELECT itemID FROM shopping_items WHERE userID = ? AND item = ?");
        $stmt->execute([$user->userID, $item]);
        $exists = $stmt->fetch();

        if ($exists) {
            // Remove
            $del = $pdo->prepare("DELETE FROM shopping_items WHERE itemID = ?");
            $del->execute([$exists['itemID']]);
            echo json_encode(['success' => true, 'inList' => false]);
        } else {
            // Add
            $newItemID = uniqid('S_');
            $ins = $pdo->prepare("INSERT INTO shopping_items (itemID, userID, item) VALUES (?, ?, ?)");
            $ins->execute([$newItemID, $user->userID, $item]);
            echo json_encode(['success' => true, 'inList' => true]);
        }
    } elseif ($action === 'remove_id') {
        $itemID = $input['itemID'] ?? '';
        $del = $pdo->prepare("DELETE FROM shopping_items WHERE itemID = ? AND userID = ?");
        $del->execute([$itemID, $user->userID]);
        echo json_encode(['success' => true]);
    } elseif ($action === 'clear_all') {
        $del = $pdo->prepare("DELETE FROM shopping_items WHERE userID = ?");
        $del->execute([$user->userID]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid Action']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>