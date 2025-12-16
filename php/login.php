<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_head.php';// Start the session
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
                if (empty($user['profileID'])) {
                    header('Location: health_profile_setup.php');
                } else {
                    header('Location: elderly_main.php');
                }
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
    <title>Login</title>
    <style>
        /* Body Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6; /* Soft light background */
            color: #333; /* Dark text for readability */
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center; /* Center the form vertically */
            height: 100vh; /* Make sure it takes the full viewport height */
            flex-direction: column;
            padding-top: 80px;
        }

        /* Container Styling */
        .container {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
            box-sizing: border-box;
            margin-top: 200px;
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

        /* Input Fields with Floating Labels */
        .input-container {
            position: relative;
            margin-bottom: 30px;
        }

        .input-container input {
            width: 100%;
            padding: 18px 20px;
            padding-top: 24px; /* Add space for the label */
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 1.2rem;
            outline: none;
            transition: 0.3s ease-in-out;
            box-sizing: border-box;
        }

        .input-container input:focus {
            border-color: #2980b9;
            box-shadow: 0 0 5px rgba(41,128,185,0.3);
        }

        .input-container label {
            position: absolute;
            top: 20px;
            left: 20px;
            color: #7f8c8d;
            font-size: 1.1rem;
            pointer-events: none;
            background-color: #ffffff;
            padding: 0 5px;
            transition: 0.3s ease all;
        }

        .input-container input:focus + label,
        .input-container input:not(:placeholder-shown) + label {
            top: -10px;
            left: 20px;
            color: #2980b9;
            font-size: 1rem;
        }

        /* Password Visibility Toggle */
        .password-wrapper {
            position: relative;
        }

        #togglePassword {
            position: absolute;
            right: 15px;
            top: 13px;
            cursor: pointer;
            font-size: 1.5rem;
            color: #2980b9;
            display: block;
        }

        /* Button Styling */
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
            transition: background-color 0.3s ease;
        }

        .login-btn:hover {
            background-color: #1c5e87;
        }

        /* Links Styling */
        a {
            display: inline-block;
            margin-top: 15px;
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
        }

        .create {
            font-size: 1.1rem;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome Back</h2>
        <p class="subtitle">Please log in to continue</p>

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

            <button type="submit" class="login-btn">Log In</button>

            <a href="forgot_password.php" class="forgot">Forgot Password?</a><br>
            <a href="../php/register.php" class="create">Create an Account</a>
        </form>
    </div>

    <script>
        // Password visibility toggle
        const togglePassword = document.querySelector("#togglePassword");
        const passwordField = document.querySelector("#password");

        // Toggle password visibility
        togglePassword.addEventListener("click", function() {
            const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
            passwordField.setAttribute("type", type);

            // Toggle the eye icon between open and closed
            if (type === "password") {
                this.textContent = "👁"; // Eye icon for password hidden
            } else {
                this.textContent = "❌"; // Crossed eye icon for password visible
            }
        });
    </script>

    <?php include('_foot.php'); ?>
</body>
</html>
