<?php
// pages/settings.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole(['User', 'Elderly', 'Caretaker', 'Dietitian', 'Admin']);

$user = getCurrentUser();
$msg = '';

// Handle Post Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);

        if ($name && $email && $phone) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phoneNo = ? WHERE userID = ?");
            if ($stmt->execute([$name, $email, $phone, $user->userID])) {
                $msg = "<div class='alert alert-success'>Profile updated.</div>";
                // Refresh user object
                $user = getCurrentUser();
            } else {
                $msg = "<div class='alert alert-danger'>Update failed.</div>";
            }
        } else {
            $msg = "<div class='alert alert-danger'>All fields required.</div>";
        }

    } elseif ($action === 'change_password') {
        $old = $_POST['old_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        if ($new !== $confirm) {
            $msg = "<div class='alert alert-danger'>New passwords do not match.</div>";
        } else {
            // Check old pass
            $stmt = $pdo->prepare("SELECT password FROM users WHERE userID = ?");
            $stmt->execute([$user->userID]);
            $currentHash = $stmt->fetchColumn();

            if (password_verify($old, $currentHash)) {
                $newHash = password_hash($new, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE users SET password = ? WHERE userID = ?");
                $upd->execute([$newHash, $user->userID]);
                $msg = "<div class='alert alert-success'>Password changed successfully.</div>";
            } else {
                $msg = "<div class='alert alert-danger'>Incorrect current password.</div>";
            }
        }

    } elseif ($action === 'unlink') {
        $targetID = $_POST['target_id'];

        // Unlink logic.
        // If I am Caretaker, I am unlinking from Patient.
        // If I am Elderly, I am unlinking from Caretaker.
        // Simple DELETE where match found.
        $stmt = $pdo->prepare("DELETE FROM user_links WHERE (caretakerID = ? AND patientID = ?) OR (patientID = ? AND caretakerID = ?)");
        if ($stmt->execute([$user->userID, $targetID, $user->userID, $targetID])) { // Try both directions
            $msg = "<div class='alert alert-success'>Account unlinked.</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Failed to unlink.</div>";
        }
    }
}

include '../includes/header.php';

// Fetch Links
$links = [];
// Assuming same logic as dashboard or using SubUser methods if available (we used Caretaker::getLinkedPatients before).
// But for Elderly viewing, we don't have getLinkedCaretakers yet.
// Let's do a raw query for simplicity and coverage.
if ($user->role == 'Caretaker') {
    $stmt = $pdo->prepare("SELECT u.* FROM user_links ul JOIN users u ON ul.patientID = u.userID WHERE ul.caretakerID = ?");
    $stmt->execute([$user->userID]);
    $links = $stmt->fetchAll(PDO::FETCH_OBJ);
} elseif ($user->role == 'Elderly' || $user->role == 'User') { // Basic users can be caretakers OR patients depending on link direction?
    // Let's look for both directions just in case rules are lax. Or assume Role dictates direction.
    // If I am Elderly, I want to see My Caretakers.
    $stmt = $pdo->prepare("SELECT u.* FROM user_links ul JOIN users u ON ul.caretakerID = u.userID WHERE ul.patientID = ?");
    $stmt->execute([$user->userID]);
    $links = $stmt->fetchAll(PDO::FETCH_OBJ);
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2 class="mb-4">Account Settings</h2>
        <?= $msg ?>

        <!-- Profile Details -->
        <div class="card mb-4">
            <div class="card-header">Personal Information</div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control"
                            value="<?= htmlspecialchars($user->getName()) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control"
                            value="<?= htmlspecialchars($user->email) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control"
                            value="<?= htmlspecialchars($user->phoneNo) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card mb-4">
            <div class="card-header">Change Password</div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="change_password">
                    <div class="mb-3">
                        <label>Current Password</label>
                        <input type="password" name="old_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning">Change Password</button>
                </form>
            </div>
        </div>

        <!-- Linked Accounts (Only for Caretaker/Elderly) -->
        <?php if (!empty($links)): ?>
            <div class="card mb-4">
                <div class="card-header">Linked Accounts</div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php foreach ($links as $link): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($link->name) ?></strong>
                                    (<?= htmlspecialchars($link->email) ?>)
                                    <span class="badge bg-secondary"><?= $link->role ?></span>
                                </div>
                                <form method="post" onsubmit="return confirm('Are you sure you want to unlink?');">
                                    <input type="hidden" name="action" value="unlink">
                                    <input type="hidden" name="target_id" value="<?= $link->userID ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Unlink</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include '../includes/footer.php'; ?>