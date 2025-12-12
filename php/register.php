<?php
// Include the PDO database connection file using a robust path
require_once __DIR__ . '/../db.php'; // resolves to cd-ass/db.php

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $role = $_POST['role']; // Get the role from the form

    // Convert the role to lowercase to ensure consistency
    $role = strtolower($role);

    // Validate the input fields
    if (empty($username) || empty($phone) || empty($email) || empty($password) || empty($confirmPassword) || empty($role)) {
        echo "All fields are required!";
        exit();
    }

    if ($password !== $confirmPassword) {
        echo "Passwords do not match!";
        exit();
    }

    // Hash the password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if the username or email already exists
    try {
        $sql = "SELECT * FROM users WHERE username = :username OR email = :email";
        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "Email already exists.";
            exit();
        }

        // Insert the new user into the database (without the userID)
        $sql = "INSERT INTO users (username, phoneNo, email, password, role) VALUES (:username, :phone, :email, :password, :role)";
        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindValue(':role', $role, PDO::PARAM_STR);

        // Execute the query
        if ($stmt->execute()) {
            // Get the last inserted id (auto-increment value)
            $userID = $_db->lastInsertId();

            // Format the userID to match the "U00001", "U00002", format
            $formattedUserID = "U" . str_pad($userID, 5, '0', STR_PAD_LEFT);

            // Update the userID field in the users table with the formatted userID
            $updateSql = "UPDATE users SET userID = :formattedUserID WHERE userID = :userID";
            $updateStmt = $_db->prepare($updateSql);
            $updateStmt->bindValue(':formattedUserID', $formattedUserID, PDO::PARAM_STR);
            $updateStmt->bindValue(':userID', $userID, PDO::PARAM_INT);

            // Execute the update query (only once)
            if ($updateStmt->execute()) {
                // Redirect to the login page after successful registration
                header('Location: login.php');
                exit();
            } else {
                echo "Error: Could not update the userID.";
            }
        } else {
            echo "Error: Could not register the user.";
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
    <title>Register — FATTY DIET Planner</title>
    <!-- Use the same site stylesheet so the color theme matches index.php/login.php -->
    <link rel="stylesheet" href="../css/app.css">
    <script src="../js/app.js" defer></script>
    <style>
        /* Small page-specific adjustments that layer on top of app.css */
        .auth-card { background: #fff; padding: 36px; border-radius: 12px; box-shadow: 0 12px 20px rgba(0,0,0,0.06); max-width: 620px; margin: 120px auto 60px; }
        .hero-sub, .subtitle { color: #7f8c8d; }
        .auth-card h2 { margin-top: 0; margin-bottom: 12px; }
        .subtitle { display:block; margin-bottom:18px; }

        /* Keep floating label compatibility with global styles */
        .input-container { position: relative; margin-bottom: 18px; }
        .input-container input { width:100%; padding:16px 18px; border-radius:8px; border:1px solid #e6e6e6; }
        .input-container label { position:absolute; left:14px; top:12px; padding:0 6px; color:var(--muted); transition:all .18s ease; pointer-events:none; }
        .password-wrapper #togglePassword, .password-wrapper #toggleConfirmPassword { position:absolute; right:12px; top:12px; cursor:pointer; }

        /* Role options layout - simpler and matching theme */
        .role-container { display:flex; gap:18px; align-items:center; margin-bottom:18px; }
        .role-label { color:var(--muted); margin-right:8px; }
        .role-options { display:flex; gap:18px; }
        .role-options label { display:flex; gap:8px; align-items:center; color:var(--muted); cursor:pointer; }
        .role-options input { width:16px; height:16px; }

        .register-btn { width:100%; }
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
            <h2>Create an Account</h2>
            <p class="subtitle">Register to get started</p>

            <form action="register.php" method="POST">
                <div class="input-container">
                    <input type="text" id="username" name="username" required placeholder=" ">
                    <label for="username">Username</label>
                </div>

                <div class="input-container">
                    <input type="text" id="phone" name="phone" required placeholder=" ">
                    <label for="phone">Phone Number</label>
                </div>

                <div class="input-container">
                    <input type="email" id="email" name="email" required placeholder=" ">
                    <label for="email">Email</label>
                </div>

                <div class="input-container password-wrapper">
                    <input type="password" id="password" name="password" required placeholder=" ">
                    <label for="password">Password</label>
                    <span id="togglePassword" class="toggle">👁</span>
                </div>

                <div class="input-container password-wrapper">
                    <input type="password" id="confirmPassword" name="confirmPassword" required placeholder=" ">
                    <label for="confirmPassword">Confirm Password</label>
                    <span id="toggleConfirmPassword" class="toggle">👁</span>
                </div>

                <div class="role-container">
                    <div class="role-label">Role:</div>
                    <div class="role-options">
                        <label><input type="radio" id="role-elderly" name="role" value="elderly" required> Elderly</label>
                        <label><input type="radio" id="role-caretaker" name="role" value="caretaker"> Caretaker</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary register-btn">Register</button>

                <div style="margin-top:12px; text-align:center;">
                    <a href="login.php" class="forgot">Already have an account? Log in</a>
                </div>
            </form>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">© 2025 FATTY DIET Planner</div>
    </footer>

    

</body>
</html>
