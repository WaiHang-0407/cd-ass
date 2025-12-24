<?php
// includes/auth.php
session_start();

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: ../pages/index.php");
        exit();
    }
}

function requireRole($allowedRoles)
{
    requireLogin();
    if (!in_array($_SESSION['role'], (array) $allowedRoles)) {
        echo "<div class='alert alert-danger'>Access Denied. You do not have permission to view this page.</div>";
        exit();
    }
}

function getCurrentUser()
{
    global $pdo;
    if (!isset($pdo)) {
        require_once __DIR__ . '/db.php';
    }

    // Include Class Definitions
    require_once __DIR__ . '/../classes/User.php';
    // SubUsers.php requires User.php, so order matters or use once.
    // User.php has interface.
    require_once __DIR__ . '/../classes/SubUsers.php';

    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE userID = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        switch ($data['role']) {
            case 'Caretaker':
                return new Caretaker($pdo, $data);
            case 'Elderly':
            case 'User': // Fallback for basic user
                // Check if actually Elderly logic applies? 
                // Using Elderly class for both for now based on previous work
                return new Elderly($pdo, $data);
            case 'Dietitian':
                return new Dietitian($pdo, $data);
            case 'Admin':
                return new Admin($pdo, $data);
            default:
                return new User($pdo, $data);
        }
    }

    return null;
}
?>