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
    // In a real app we might reload from DB or just use session data
    // For now returning session data as a quick object
    return (object) [
        'userID' => $_SESSION['user_id'] ?? null,
        'role' => $_SESSION['role'] ?? null,
        'name' => $_SESSION['name'] ?? 'User'
    ];
}
?>