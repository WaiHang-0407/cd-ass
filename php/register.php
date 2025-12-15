<?php
// Include the PDO database connection file
include('../db.php'); // Ensure the path to db.php is correct

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
    <title>Register</title>
    <style>
        /* Body Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6; /* Soft light background */
            color: #333; /* Dark text for readability */
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Align content at the top */
            flex-direction: column;
            padding-top: 80px; /* Add space for fixed header */
            overflow-y: auto; /* Enable scrolling when content overflows */
            min-height: 100%; /* Ensure the body takes the full height and can scroll */
        }

        /* Header Styles - Fixed Navigation */
        header {
            position: fixed; /* Fix the header at the top */
            top: 0;
            left: 0;
            width: 100%;
            background-color: #ffffff;
            color: #2c3e50;
            padding: 20px;
            z-index: 1000; /* Ensure header stays on top of other content */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Add a small shadow for the header */
        }

        h1 {
            font-size: 2.5rem;
            letter-spacing: 1px;
            color: #2980b9; /* Blue color for the header */
            margin: 0; /* Remove default margin */
        }

        /* Adjust the space to leave for the header */
        .form-container {
            margin-top: 100px; /* This is the space for the fixed header */
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        /* Container Styling */
        .container {
            background-color: #ffffff;
            padding: 40px;
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
            font-size: 1.1rem;
            outline: none;
            transition: 0.3s ease-in-out;
            box-sizing: border-box;
        }

        .input-container input:focus {
            border-color: #2980b9;
            box-shadow: 0 0 5px rgba(41, 128, 185, 0.3);
        }

        /* Label Styling (Floating Box) */
        .input-container label {
            position: absolute;
            top: 20px; /* Position label inside the input initially */
            left: 20px;
            color: #7f8c8d;
            font-size: 1.1rem;
            pointer-events: none;
            background-color: #ffffff; /* Background color for the floating box */
            padding: 0 5px; /* Padding to create the box effect */
            transition: 0.3s ease all; /* Smooth transition */
            z-index: 1;
        }

        /* Move label above the input when focused or text is entered */
        .input-container input:focus + label,
        .input-container input:not(:placeholder-shown) + label {
            top: -10px;
            left: 20px;
            color: #2980b9;
            font-size: 1rem;
        }

        /* Hide the Default Eye Icon */
        .input-container input::-webkit-outer-spin-button,
        .input-container input::-webkit-inner-spin-button,
        .input-container input[type="password"] {
            appearance: none;
            -webkit-appearance: none; /* Hide the default eye icon in Webkit browsers (Chrome, Safari) */
        }

        /* Password Visibility Toggle */
        .password-wrapper {
            position: relative;
        }

        /* Custom Eye Icon for Password */
        #togglePassword, #toggleConfirmPassword {
            position: absolute;
            right: 15px;
            top: 13px;
            cursor: pointer;
            font-size: 1.5rem;
            color: #2980b9;
            display: block; /* Always visible */
        }

        /* General Styles for Radio Button Group */
        .role-container {
            position: relative;
            margin-bottom: 30px;
            text-align: left;
            display: flex;
        }

        .role-label {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin: 0 50px 0 30px;
            text-align: left;
        }

        .role-options {
            display: flex;                  /* Using flexbox to align items horizontally */
            justify-content: space-between; /* Distribute space between radio buttons */
            gap: 50px;                      /* Reduce the space between radio buttons */
            align-items: center;            /* Vertically align the radio buttons */
        }

        .role-option {
            display: flex;
            align-items: center;            /* Align the radio button with the label */
            gap: 10px;                       /* Space between radio button and label */
            font-size: 1.1rem;
            color: #7f8c8d;
            cursor: pointer;
        }

        .role-option input {
            width: 20px;
            height: 20px;
            border-radius: 50%;             /* Make the radio button round */
            border: 2px solid #2980b9;     /* Blue border */
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .role-option input:checked {
            background-color: #2980b9;     /* Blue when checked */
            border-color: #2980b9;
        }

        .role-option input:checked + .role-label-text {
            color: #2980b9;                /* Text color when the radio is checked */
        }

        .role-option:hover input {
            background-color: #f0f0f0;     /* Light gray background on hover */
        }

        .role-label-text {
            font-size: 1.1rem;
            color: #7f8c8d;
            transition: color 0.3s ease;
        }

        .role-option:hover .role-label-text {
            color: #2980b9;                /* Change text color on hover */
        }


        /* Button Styling */
        .register-btn {
            background-color: #2980b9;
            color: #fff;
            padding: 16px;
            width: 100%;
            border: none;
            border-radius: 8px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        .register-btn:hover {
            background-color: #1c5e87;
        }

        /* Links Styling */
        a {
            display: inline-block;
            font-size: 1.1rem;
            margin-top: 15px;
            color: #2980b9;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #1c5e87;
            text-decoration: underline;
        }

        .forgot {
            margin-top: 10px;
            font-size: 1.1rem;
            color: #7f8c8d;
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
    <header>
        <h1>Nutrition App</h1>
    </header>

    <div class="form-container">
        <div class="container">
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

                <!-- Password Input -->
                <div class="input-container password-wrapper">
                    <input type="password" id="password" name="password" required placeholder=" ">
                    <label for="password">Password</label>
                    <span id="togglePassword" class="toggle">👁</span>
                </div>

                <!-- Confirm Password Input -->
                <div class="input-container password-wrapper">
                    <input type="password" id="confirmPassword" name="confirmPassword" required placeholder=" ">
                    <label for="confirmPassword">Confirm Password</label>
                    <span id="toggleConfirmPassword" class="toggle">👁</span>
                </div>

                <!-- Role Selection Radio Buttons -->
                <div class="role-container">
                    <label for="role" class="role-label">Role :</label>
                    <div class="role-options">
                        <div id="role-option">
                            <input type="radio" id="role-elderly" name="role" value="elderly" required>
                            <span class="role-label-text">Elderly</span>
                        </div>

                        <div id="role-option">
                            <input type="radio" id="role-caretaker" name="role" value="caretaker" required>
                            <span class="role-label-text">Caretaker</span>
                        </div> 
                    </div>
                </div>

                <button type="submit" class="register-btn">Register</button>

                <a href="login.php" class="forgot">Already have an account? Log in</a>
            </form>

        </div>
    </div>

    <script>
        // Password visibility toggle
        const togglePassword = document.querySelector("#togglePassword");
        const passwordField = document.querySelector("#password");

        // Confirm Password visibility toggle
        const toggleConfirmPassword = document.querySelector("#toggleConfirmPassword");
        const confirmPasswordField = document.querySelector("#confirmPassword");

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

        // Toggle confirm password visibility
        toggleConfirmPassword.addEventListener("click", function() {
            const type = confirmPasswordField.getAttribute("type") === "password" ? "text" : "password";
            confirmPasswordField.setAttribute("type", type);

            // Toggle the eye icon between open and closed
            if (type === "password") {
                this.textContent = "👁"; // Eye icon for password hidden
            } else {
                this.textContent = "❌"; // Crossed eye icon for password visible
            }
        });
    </script>

</body>
</html>
