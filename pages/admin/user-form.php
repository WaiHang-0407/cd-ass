<?php
// pages/admin/user-form.php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../classes/SubUsers.php';

requireRole('Admin');
include '../../includes/header.php';

$userToEdit = null;
$editing = false;
$error = '';
$success = '';

// Fetch for Edit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE userID = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($data) {
        $editing = true;
        // Use factory or manual instantiation
        // We just need data for the form mostly, but object is good for consistency
        // Let's just use raw data for form filling to avoid class switch complexity if role changes
        $userToEdit = $data;
    } else {
        $error = "User not found.";
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    // Use part of email or name for username if empty, or require it
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'] ?? 'User';
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($name) || empty($username) || empty($email)) {
        $error = "Name, Username, and Email are required.";
    } elseif (!$editing && empty($password)) {
        $error = "Password is required for new users.";
    } else {
        // Instantiate correct class
        $uObj = null;
        switch ($role) {
            case 'Admin':
                $uObj = new Admin($pdo);
                break;
            case 'Dietitian':
                $uObj = new Dietitian($pdo);
                break;
            case 'Elderly':
                $uObj = new Elderly($pdo);
                break;
            case 'Caretaker':
                $uObj = new Caretaker($pdo);
                break;
            default:
                $uObj = new User($pdo);
                break;
        }

        if ($editing) {
            $uObj->userID = $userToEdit['userID'];
            // If editing, we might need to load existing hash if password empty
            $uObj->setPassword($userToEdit['password']); // Temporarily set old hash? No, setPassword hashes it.
            // Wait, setPassword hashes the input.
            // If password field is empty, we keep old password.
            if (!empty($password)) {
                $uObj->setPassword($password);
            } else {
                // Manually set protected property? No, extend User or use Reflection?
                // Actually, User class doesn't have setPasswordHash.
                // But we can just set $uObj->password directly if we change visibility or access it via constructor data.
                // Constructor takes $data. Let's re-instantiate with old data merged.
                $mergedData = $userToEdit;
                $mergedData['name'] = $name;
                $mergedData['username'] = $username;
                $mergedData['email'] = $email;
                $mergedData['phoneNo'] = $phone;
                $mergedData['role'] = $role; // If role changed, this might need care (old table cleanup?)
                // For now assuming role change is fine, old table entry remains orphan or triggers update

                // Re-create object with merged data (preserves old password hash)
                switch ($role) {
                    case 'Admin':
                        $uObj = new Admin($pdo, $mergedData);
                        break;
                    case 'Dietitian':
                        $uObj = new Dietitian($pdo, $mergedData);
                        break;
                    case 'Elderly':
                        $uObj = new Elderly($pdo, $mergedData);
                        break;
                    case 'Caretaker':
                        $uObj = new Caretaker($pdo, $mergedData);
                        break;
                    default:
                        $uObj = new User($pdo, $mergedData);
                        break;
                }
            }
        } else {
            // New User
            $uObj->setName($name);
            $uObj->setUsername($username);
            $uObj->setEmail($email);
            $uObj->setPhoneNo($phone);
            $uObj->setRole($role);
            $uObj->setPassword($password);
            $uObj->setAge(60); // Default
            $uObj->setGender('Not Specified');
        }

        // If Editing and Password was provided (and we used constructor above which uses old hash),
        // we need to set the NEW password.
        if ($editing && !empty($password)) {
            $uObj->setPassword($password);
        }

        // Save
        if ($uObj->save()) {
            echo "<script>window.location.href='users.php?msg=saved';</script>";
            exit;
        } else {
            $error = "Failed to save user.";
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?= $editing ? 'Edit User' : 'Add New User' ?></h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" required
                                    value="<?= $editing ? htmlspecialchars($userToEdit['name']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required
                                    value="<?= $editing ? htmlspecialchars($userToEdit['username']) : '' ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required
                                value="<?= $editing ? htmlspecialchars($userToEdit['email']) : '' ?>">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control"
                                    value="<?= $editing ? htmlspecialchars($userToEdit['phoneNo']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select">
                                    <?php
                                    $roles = [
                                        'User' => 'User (Elderly)',
                                        'Dietitian' => 'Dietitian',
                                        'Caretaker' => 'Caretaker',
                                        'Admin' => 'Admin'
                                    ];
                                    foreach ($roles as $val => $label):
                                        $sel = ($editing && $userToEdit['role'] == $val) ? 'selected' : '';
                                        ?>
                                        <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Password
                                <?= $editing ? '<small class="text-muted">(Leave blank to keep unchanged)</small>' : '' ?></label>
                            <input type="password" name="password" class="form-control" <?= $editing ? '' : 'required' ?>>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="users.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit"
                                class="btn btn-primary"><?= $editing ? 'Update User' : 'Create User' ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>