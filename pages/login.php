<?php
// pages/index.php (Login)
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../classes/SubUsers.php';
require_once __DIR__ . '/../classes/Profile.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['phoneOrEmail'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = User::findByUsername($pdo, $identifier);

    if ($user && $user->login($password)) {
        if (!$user->getIsActive()) {
            $error = "Account is deactivated. Please contact support.";
        } else {
            $_SESSION['user_id'] = $user->getUserID();
            $_SESSION['role'] = $user->getRole();
            $_SESSION['name'] = $user->getName();

            // Redirect Logic based on Testing File Flow
            if ($user->getRole() == 'User' || $user->getRole() == 'Elderly') {
                // Check if profile is set up
                $profile = new Profile($pdo, $user->getUserID());
                // Assuming empty height/weight means not set up
                if (empty($profile->height) || empty($profile->weight)) {
                    header("Location: profile.php?setup=1");
                } else {
                    header("Location: dashboard.php");
                }
            } elseif ($user->getRole() == 'Admin') {
                header("Location: admin/dashboard.php");
            } else {
                // Dietitian, Caretaker -> Dashboard
                header("Location: dashboard.php");
            }
            exit();
        }
    } else {
        $error = "Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Keep Bootstrap for Layout consistency but add custom styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Body Styles matching testing file */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            color: #333;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }

        .container-custom {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
            box-sizing: border-box;
        }

        h2 {
            font-size: 2.2rem;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .subtitle {
            font-size: 1.2rem;
            margin-bottom: 25px;
            color: #7f8c8d;
        }

        /* Floating Labels */
        .input-container {
            position: relative;
            margin-bottom: 30px;
            text-align: left;
        }

        .input-container input {
            width: 100%;
            padding: 18px 20px;
            padding-top: 24px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 1.2rem;
            outline: none;
            transition: 0.3s ease-in-out;
        }

        .input-container input:focus {
            border-color: #2980b9;
            box-shadow: 0 0 5px rgba(41, 128, 185, 0.3);
        }

        .input-container label {
            position: absolute;
            top: 20px;
            left: 20px;
            color: #7f8c8d;
            font-size: 1.1rem;
            pointer-events: none;
            background-color: transparent;
            transition: 0.3s ease all;
        }

        /* Floating effect */
        .input-container input:focus+label,
        .input-container input:not(:placeholder-shown)+label {
            top: 5px;
            left: 20px;
            font-size: 0.8rem;
            color: #2980b9;
        }

        #togglePassword {
            position: absolute;
            right: 15px;
            top: 18px;
            cursor: pointer;
            font-size: 1.5rem;
            color: #2980b9;
            z-index: 10;
        }

        .login-btn {
            background-color: #2980b9;
            color: #fff;
            padding: 16px;
            width: 100%;
            border: none;
            border-radius: 8px;
            font-size: 1.2rem;
            cursor: pointer;
            margin-top: 20px;
        }

        .login-btn:hover {
            background-color: #1c5e87;
        }

        a {
            color: #2980b9;
            text-decoration: none;
            font-size: 1.1rem;
        }

        a:hover {
            text-decoration: underline;
        }

        .forgot {
            color: #7f8c8d;
            margin-top: 10px;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="container-custom">
        <div style="text-align: left; margin-bottom: 20px;">
            <a href="index.php" style="font-size: 0.9rem; text-decoration: none;">&larr; Back to Home</a>
        </div>
        <h2>Welcome Back</h2>
        <p class="subtitle">Please log in to continue</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-container">
                <input type="text" id="phoneOrEmail" name="phoneOrEmail" required placeholder=" ">
                <label for="phoneOrEmail">Phone Number / Email / Username</label>
            </div>

            <div class="input-container">
                <input type="password" id="password" name="password" required placeholder=" ">
                <label for="password">Password</label>
                <span id="togglePassword">👁</span>
            </div>

            <button type="submit" class="login-btn">Log In</button>

            <!-- <a href="#" class="forgot">Forgot Password?</a><br> -->
            <br>
            <a href="register.php" class="create mt-3 d-inline-block">Create an Account</a>
        </form>
    </div>

    <script>
        const togglePassword = document.querySelector("#togglePassword");
        const passwordField = document.querySelector("#password");

        togglePassword.addEventListener("click", function () {
            const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
            passwordField.setAttribute("type", type);
            // Toggle icon
            this.textContent = type === "password" ? "👁" : "❌";
        });
    </script>
</body>

</html>