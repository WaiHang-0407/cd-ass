<?php
include('../php/_head.php');
include('../db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $role = strtolower($_POST['role']);

    // Validate input fields
    if (empty($username) || empty($phone) || empty($email) || empty($password) || empty($confirmPassword) || empty($role)) {
        echo "All fields are required!";
        exit();
    }

    if ($password !== $confirmPassword) {
        echo "Passwords do not match!";
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Start transaction
        $_db->beginTransaction();

        // Check if username or email already exists
        $sql = "SELECT * FROM users WHERE username = :username OR email = :email";
        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "Email or username already exists.";
            exit();
        }

        // Insert new user (without userID first)
        $sql = "INSERT INTO users (username, phoneNo, email, password, role) 
                VALUES (:username, :phone, :email, :password, :role)";
        $stmt = $_db->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindValue(':role', $role, PDO::PARAM_STR);
        $stmt->execute();

        // Get the auto-increment ID
        $autoIncrementID = $_db->lastInsertId();

        // Format the userID
        $formattedUserID = "U" . str_pad($autoIncrementID, 5, '0', STR_PAD_LEFT);

        // Update the userID field with formatted value
        $updateSql = "UPDATE users SET userID = :formattedUserID WHERE id = :autoIncrementID";
        $updateStmt = $_db->prepare($updateSql);
        $updateStmt->bindValue(':formattedUserID', $formattedUserID, PDO::PARAM_STR);
        $updateStmt->bindValue(':autoIncrementID', $autoIncrementID, PDO::PARAM_INT);
        $updateStmt->execute();

        // Insert into role-specific tables
        if ($role == 'elderly') {
            $elderlyID = "E" . str_pad($autoIncrementID, 5, '0', STR_PAD_LEFT);
            
            // CRITICAL: Use formatted userID since userID column is VARCHAR(10)
            $elderlySQL = "INSERT INTO Elderly (elderlyID, userID, profileID, dietPlanID, caretakerID) 
                           VALUES (:elderlyID, :userID, NULL, NULL, NULL)";
            $elderlyStmt = $_db->prepare($elderlySQL);
            $elderlyStmt->bindValue(':elderlyID', $elderlyID, PDO::PARAM_STR);
            $elderlyStmt->bindValue(':userID', $formattedUserID, PDO::PARAM_STR); // Use formatted userID
            $elderlyStmt->execute();
            
            // Verify insertion
            $verifySQL = "SELECT * FROM Elderly WHERE userID = :userID";
            $verifyStmt = $_db->prepare($verifySQL);
            $verifyStmt->bindValue(':userID', $formattedUserID, PDO::PARAM_STR);
            $verifyStmt->execute();
            
            if ($verifyStmt->rowCount() == 0) {
                throw new Exception("Failed to insert elderly record for userID: $formattedUserID");
            }
            
        } elseif ($role == 'caretaker') {
            $caretakerID = "C" . str_pad($autoIncrementID, 5, '0', STR_PAD_LEFT);
            
            $caretakerSQL = "INSERT INTO Caretaker (caretakerID, userID) 
                             VALUES (:caretakerID, :userID)";
            $caretakerStmt = $_db->prepare($caretakerSQL);
            $caretakerStmt->bindValue(':caretakerID', $caretakerID, PDO::PARAM_STR);
            $caretakerStmt->bindValue(':userID', $formattedUserID, PDO::PARAM_STR); // Use formatted userID
            $caretakerStmt->execute();
        }

        // Commit transaction
        $_db->commit();

        // Redirect to login
        header('Location: login.php');
        exit();

    } catch (PDOException $e) {
        // Rollback on error
        $_db->rollBack();
        echo "Database Error: " . $e->getMessage();
        error_log("Registration error: " . $e->getMessage());
        exit();
    } catch (Exception $e) {
        $_db->rollBack();
        echo "Error: " . $e->getMessage();
        error_log("Registration error: " . $e->getMessage());
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
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            color: #333;
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex-direction: column;
            padding-top: 80px;
            overflow-y: auto;
            min-height: 100%;
        }

        .form-container {
            margin-top: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

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

        .input-container {
            position: relative;
            margin-bottom: 30px;
        }

        .input-container input {
            width: 100%;
            padding: 18px 20px;
            padding-top: 24px;
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
            z-index: 1;
        }

        .input-container input:focus + label,
        .input-container input:not(:placeholder-shown) + label {
            top: -10px;
            left: 20px;
            color: #2980b9;
            font-size: 1rem;
        }

        .input-container input::-webkit-outer-spin-button,
        .input-container input::-webkit-inner-spin-button,
        .input-container input[type="password"] {
            appearance: none;
            -webkit-appearance: none;
        }

        .password-wrapper {
            position: relative;
        }

        #togglePassword, #toggleConfirmPassword {
            position: absolute;
            right: 15px;
            top: 13px;
            cursor: pointer;
            font-size: 1.5rem;
            color: #2980b9;
            display: block;
        }

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
            display: flex;
            justify-content: space-between;
            gap: 50px;
            align-items: center;
        }

        .role-option {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
            color: #7f8c8d;
            cursor: pointer;
        }

        .role-option input {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #2980b9;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .role-option input:checked {
            background-color: #2980b9;
            border-color: #2980b9;
        }

        .role-option input:checked + .role-label-text {
            color: #2980b9;
        }

        .role-option:hover input {
            background-color: #f0f0f0;
        }

        .role-label-text {
            font-size: 1.1rem;
            color: #7f8c8d;
            transition: color 0.3s ease;
        }

        .role-option:hover .role-label-text {
            color: #2980b9;
        }

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

        @media (max-width: 600px) {
            .container {
                padding: 30px 20px;
            }

            h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
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
                    <label for="role" class="role-label">Role :</label>
                    <div class="role-options">
                        <div class="role-option">
                            <input type="radio" id="role-elderly" name="role" value="elderly" required>
                            <span class="role-label-text">Elderly</span>
                        </div>

                        <div class="role-option">
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
        const togglePassword = document.querySelector("#togglePassword");
        const passwordField = document.querySelector("#password");
        const toggleConfirmPassword = document.querySelector("#toggleConfirmPassword");
        const confirmPasswordField = document.querySelector("#confirmPassword");

        togglePassword.addEventListener("click", function() {
            const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
            passwordField.setAttribute("type", type);
            this.textContent = type === "password" ? "👁" : "❌";
        });

        toggleConfirmPassword.addEventListener("click", function() {
            const type = confirmPasswordField.getAttribute("type") === "password" ? "text" : "password";
            confirmPasswordField.setAttribute("type", type);
            this.textContent = type === "password" ? "👁" : "❌";
        });
    </script>

    <?php include('_foot.php'); ?>
</body>
</html>