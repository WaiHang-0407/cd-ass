<?php
// Include the PDO database connection file using a robust path
require_once __DIR__ . '/../db.php'; // resolves to cd-ass/db.php

// Start the session
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $phoneOrEmail = $_POST['phoneOrEmail'];
    $password = $_POST['password'];

    // Validate the input fields
    if (empty($phoneOrEmail) || empty($password)) {
        echo "Please enter both email/phone number and password.";
        exit();
    }

    try {
        // Prepare the SQL query to check if the email/phone exists
        $sql = "SELECT * FROM users WHERE email = :phoneOrEmail OR phoneNo = :phoneOrEmail";
        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':phoneOrEmail', $phoneOrEmail, PDO::PARAM_STR);
        $stmt->execute();

        // Check if a user was found
        if ($stmt->rowCount() == 0) {
            echo "No user found with that email or phone number.";
            exit();
        }

        // Fetch the user record
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Password is correct, start the session and store user information
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role']; // Store the user's role in session

            // Redirect based on user role
            if ($user['role'] === 'elderly') {
                // Redirect to elderly_main.php
                header('Location: elderly_main.php');
            } elseif ($user['role'] === 'caretaker') {
                // Redirect to caretaker_main.php
                header('Location: caretaker_main.php');
            } elseif ($user['role'] === 'admin') {
                // Redirect to admin_main.php
                header('Location: admin_main.php');
            } elseif ($user['role'] === 'dietitian') {
                // Redirect to dietitian_main.php
                header('Location: dietitian_main.php');
            } else {
                // Default fallback if the role is not recognized
                echo "Invalid role!";
            }
            exit();
        } else {
            echo "Incorrect password!";
            exit();
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — FATTY DIET Planner</title>
    <!-- Use the same site stylesheet so the color theme matches index.php -->
    <link rel="stylesheet" href="../css/app.css">
    <script src="../js/app.js" defer></script>
    <style>
        /* Small page-specific adjustments that layer on top of app.css */
        .auth-card {
            background: #fff;
            padding: 36px;
            border-radius: 12px;
            box-shadow: 0 12px 20px rgba(0,0,0,0.06);
            max-width: 520px;
            margin: 120px auto 60px;
        }

        .hero-sub, .subtitle { color: #7f8c8d; }

       /* add gap after heading + subtitle */
       .auth-card h2 { margin-top: 0; margin-bottom: 12px; }
       .subtitle { display: block; margin-bottom: 20px; }

        /* Floating label inputs (keep names from original markup) */
        .input-container { position: relative; margin-bottom: 22px; }
        .input-container input { width: 100%; padding: 16px 18px; border: 1px solid #e6e6e6; border-radius: 8px; }
        .input-container label { position: absolute; left: 14px; top: 10px; background: transparent; color: #9aa4a6; padding: 0 6px; font-size: 0.95rem; }

        .password-wrapper #togglePassword { position: absolute; right: 12px; top: 12px; cursor: pointer; }
    </style>
</head>
<body>

    <!-- Reuse the same site header so theme colors, nav and branding match -->
    <header class="site-header">
        <div class="container nav-row">
            <div class="brand">FATTY DIET Planner</div>
            <nav class="main-nav">
                <a href="../index.php">Home</a>
                <a href="../index.php#about">About Us</a>
                <a href="login.php">Log In / Register</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="auth-card" role="main">
            <h2 style="margin-top:0;">Welcome Back</h2>
            <p class="subtitle">Please log in to continue</p>
            <linebreak></linebreak>

            <!-- Login Form -->
            <form method="POST" action="login.php">
                <div class="input-container">
                    <input type="text" id="phoneOrEmail" name="phoneOrEmail" required placeholder=" ">
                    <label for="phoneOrEmail">Phone Number / Email</label>
                </div>

                <!-- Password Input -->
                <div class="input-container password-wrapper">
                    <input type="password" id="password" name="password" required placeholder=" ">
                    <label for="password">Password</label>
                    <span id="togglePassword" class="toggle">👁</span>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">Log In</button>

                <div style="margin-top:12px; text-align:center;">
                    <a href="forgot_password.php" class="forgot">Forgot Password?</a>
                    <span style="display:inline-block; width:12px;"></span>
                    <a href="register.php" class="create">Create an Account</a>
                </div>
            </form>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">© 2025 FATTY DIET Planner</div>
    </footer>

    

</body>
</html>
