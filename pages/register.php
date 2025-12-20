<?php
// pages/register.php
session_start();
require_once '../includes/db.php';
require_once '../classes/SubUsers.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    // Optional fields for profile setup stub, keeping them simple
    $name = $_POST['name'] ?? $username;
    $age = $_POST['age'] ?? 0;
    $gender = $_POST['gender'] ?? 'Other';
    $role = $_POST['role'] ?? '';

    // Validation
    if (empty($username) || empty($phone) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    } else {
        // Check duplicates
        $check = $pdo->prepare("SELECT userID FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);
        if ($check->rowCount() > 0) {
            $error = "Username or Email already exists.";
        } else {
            $data = [
                'name' => $name,
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'email' => $email,
                'phoneNo' => $phone,
                'age' => $age,
                'gender' => $gender,
                'role' => $role
            ];

            // Factory logic
            $newUser = null;
            switch ($role) {
                case 'User': // Map 'Elderly' radio to 'User' role internally if needed, or stick to 'User'
                case 'Elderly':
                    $data['role'] = 'User'; // Force internal role name
                    // Load Balancing
                    $sql = "SELECT d.dietitianID, COUNT(e.elderlyID) as patientCount 
                            FROM dietitians d 
                            LEFT JOIN elderly e ON d.dietitianID = e.assignedDietitianID 
                            GROUP BY d.dietitianID 
                            ORDER BY patientCount ASC, RAND() 
                            LIMIT 1";
                    $stmt = $pdo->query($sql);
                    $assignedDietitian = $stmt->fetch();
                    if ($assignedDietitian) {
                        $data['assignedDietitianID'] = $assignedDietitian['dietitianID'];
                    }
                    $newUser = new Elderly($pdo, $data);
                    break;
                case 'Dietitian':
                    $data['role'] = 'Dietitian';
                    $newUser = new Dietitian($pdo, $data);
                    $newUser->licenseNo = $_POST['licenseNo'] ?? 'PENDING';
                    break;
                case 'Caretaker':
                    $data['role'] = 'Caretaker';
                    $newUser = new Caretaker($pdo, $data);
                    $newUser->relationship = $_POST['relationship'] ?? 'Family';
                    $newUser->emergencyContact = $_POST['emergencyContact'] ?? '000';
                    break;
                case 'Admin':
                    $data['role'] = 'Admin';
                    $newUser = new Admin($pdo, $data);
                    break;
            }

            if ($newUser && $newUser->save()) {
                header("Location: index.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
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
        /* Shared Styles matching Login */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            color: #333;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            /* Align top for long form */
            padding-top: 40px;
            padding-bottom: 40px;
            min-height: 100vh;
            box-sizing: border-box;
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

        .input-container {
            position: relative;
            margin-bottom: 25px;
            text-align: left;
        }

        .input-container input,
        .input-container select {
            width: 100%;
            padding: 16px 20px;
            padding-top: 22px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 1.1rem;
            outline: none;
            transition: 0.3s ease-in-out;
            box-sizing: border-box;
            background: #fff;
        }

        .input-container input:focus,
        .input-container select:focus {
            border-color: #2980b9;
            box-shadow: 0 0 5px rgba(41, 128, 185, 0.3);
        }

        .input-container label {
            position: absolute;
            top: 18px;
            left: 20px;
            color: #7f8c8d;
            font-size: 1.1rem;
            pointer-events: none;
            transition: 0.3s ease all;
            z-index: 10;
        }

        .input-container input:focus+label,
        .input-container input:not(:placeholder-shown)+label,
        .input-container select+label {
            top: 4px;
            left: 20px;
            font-size: 0.8rem;
            color: #2980b9;
        }

        /* Password Toggle */
        .toggle {
            position: absolute;
            right: 15px;
            top: 18px;
            cursor: pointer;
            font-size: 1.5rem;
            color: #2980b9;
        }

        /* Role Radio Buttons */
        .role-container {
            margin-bottom: 30px;
            text-align: left;
        }

        .role-label {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin-bottom: 10px;
            display: block;
        }

        .role-options {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: flex-start;
        }

        .role-option {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
            color: #7f8c8d;
            cursor: pointer;
        }

        .role-option input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #2980b9;
        }

        .role-option:hover span {
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
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        .register-btn:hover {
            background-color: #1c5e87;
        }

        a {
            color: #2980b9;
            text-decoration: none;
            font-size: 1.1rem;
            display: inline-block;
            margin-top: 15px;
        }

        a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: left;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .extra-fields {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #eee;
        }
    </style>
</head>

<body>
    <div class="container-custom">
        <div style="text-align: left; margin-bottom: 20px;">
            <a href="index.php" style="font-size: 0.9rem; text-decoration: none;">&larr; Back to Home</a>
        </div>
        <h2>Create an Account</h2>
        <p class="subtitle">Register to get started</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <!-- Full Name (Added to match logic) -->
            <div class="input-container">
                <input type="text" id="name" name="name" required placeholder=" ">
                <label for="name">Full Name</label>
            </div>

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

            <div class="input-container">
                <input type="number" id="age" name="age" required placeholder=" ">
                <label for="age">Age</label>
            </div>

            <div class="input-container">
                <!-- Simple Gender Select -->
                <select name="gender" id="gender">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
                <label for="gender">Gender</label>
            </div>

            <div class="input-container">
                <input type="password" id="password" name="password" required placeholder=" ">
                <label for="password">Password</label>
                <span id="togglePassword" class="toggle">👁</span>
            </div>

            <div class="input-container">
                <input type="password" id="confirmPassword" name="confirmPassword" required placeholder=" ">
                <label for="confirmPassword">Confirm Password</label>
                <span id="toggleConfirmPassword" class="toggle">👁</span>
            </div>

            <div class="role-container">
                <label class="role-label">I am a:</label>
                <div class="role-options">
                    <label class="role-option">
                        <input type="radio" name="role" value="Elderly" required onclick="toggleFields()">
                        <span>Elderly User</span>
                    </label>
                    <label class="role-option">
                        <input type="radio" name="role" value="Caretaker" onclick="toggleFields()">
                        <span>Caretaker</span>
                    </label>
                    <label class="role-option">
                        <input type="radio" name="role" value="Dietitian" onclick="toggleFields()">
                        <span>Dietitian</span>
                    </label>
                    <label class="role-option">
                        <input type="radio" name="role" value="Admin" onclick="toggleFields()">
                        <span>Admin</span>
                    </label>
                </div>
            </div>

            <!-- Dietitian Fields -->
            <div id="dietitianFields" class="extra-fields" style="display:none;">
                <div class="input-container" style="margin-bottom:0;">
                    <input type="text" name="licenseNo" placeholder=" ">
                    <label>License Number</label>
                </div>
            </div>

            <!-- Caretaker Fields -->
            <div id="caretakerFields" class="extra-fields" style="display:none;">
                <div class="input-container">
                    <input type="text" name="relationship" placeholder=" ">
                    <label>Relationship</label>
                </div>
                <div class="input-container" style="margin-bottom:0;">
                    <input type="text" name="emergencyContact" placeholder=" ">
                    <label>Emergency Contact</label>
                </div>
            </div>

            <button type="submit" class="register-btn">Register</button>

            <a href="login.php" class="forgot">Already have an account? Log in</a>
        </form>
    </div>

    <script>
        // Password Toggle
        function setupToggle(id, fieldId) {
            document.getElementById(id).addEventListener('click', function  () {
                const field = document.getElementById(fieldId);
                const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
                field.setAttribute('type', type);
                this.innerText = type === 'password' ? '👁' : '❌';
            });
        }
        setupToggle('togglePassword', 'password');
        setupToggle('toggleConfirmPassword', 'confirmPassword');

        // Field Toggling
        function toggleFields() {
            const roles = document.getElementsByName('role');
            let selected = '';
            fo r (const r of roles) {  if (r.checked) selected = r.value; }

            document.getElementById('dietitianFields').style.display = selected === 'Dietitian' ? 'block' : 'none';
            document.getElementById('caretakerFields').style.display = selected === 'Caretaker' ? 'block' : 'none';
        }
    </script>
</body>

</html>