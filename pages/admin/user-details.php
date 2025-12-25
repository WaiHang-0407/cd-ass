<?php
// pages/admin/user-details.php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../classes/Profile.php';
require_once '../../classes/SubUsers.php';

requireRole('Admin');
include '../../includes/header.php';

if (!isset($_GET['id'])) {
    echo "<div class='container py-4'><div class='alert alert-danger'>No user ID specified.</div></div>";
    include '../../includes/footer.php';
    exit();
}

$viewUserID = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE userID = ?");
$stmt->execute([$viewUserID]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    echo "<div class='container py-4'><div class='alert alert-danger'>User not found.</div></div>";
    include '../../includes/footer.php';
    exit();
}

// Instantiate specific user type for easier data access if needed, or just use raw data
// Mapping Role to Class
$role = $userData['role'];
$userObj = null;
// Use User Factory or manual
switch ($role) {
    case 'Elderly':
        $userObj = new Elderly($pdo, $userData);
        break;
    case 'Caretaker':
        $userObj = new Caretaker($pdo, $userData);
        break;
    case 'Dietitian':
        $userObj = new Dietitian($pdo, $userData);
        break;
    default:
        // Treat generic 'User' as 'Elderly' (Patient) to support logic
        $userObj = new Elderly($pdo, $userData);
        break;
}

// Fetch Profile (Health Data) - Mainly for Elderly/User
$profile = new Profile($pdo, $viewUserID);


// Handle Dietitian Assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_dietitian'])) {
    $newDietitianID = $_POST['dietitian_id']; // Can be empty string for unassign
    if (empty($newDietitianID))
        $newDietitianID = null;

    // Update elderly table (Upsert to ensure row exists even for 'User' role)
    $upd = $pdo->prepare("INSERT INTO elderly (elderlyID, assignedDietitianID) VALUES (?, ?) ON DUPLICATE KEY UPDATE assignedDietitianID = VALUES(assignedDietitianID)");
    if ($upd->execute([$viewUserID, $newDietitianID])) {
        // Refresh check
        $userObj->assignedDietitianID = $newDietitianID;
        echo "<div class='container pt-3'>
    <div class='alert alert-success'>Dietitian assignment updated.</div>
</div>";
    } else {
        echo "<div class='container pt-3'>
    <div class='alert alert-danger'>Failed to update assignment.</div>
</div>";
    }
}

// Fetch All Dietitians for Dropdown
$dListStmt = $pdo->query("SELECT userID, name FROM users WHERE role = 'Dietitian' ORDER BY name ASC");
$allDietitians = $dListStmt->fetchAll();

?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary"><i class="bi bi-person-circle"></i> User Details</h2>
        <div>
            <a href="user-form.php?id=<?= $viewUserID ?>" class="btn btn-primary me-2"><i class="bi bi-pencil"></i> Edit
                User</a>
            <a href="users.php" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Basic Info Card -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="m-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="display-1 text-secondary"><i class="bi bi-person-fill"></i></div>
                        <h4><?= htmlspecialchars($userData['name']) ?></h4>
                        <span
                            class="badge bg-<?= $role == 'Admin' ? 'danger' : ($role == 'Dietitian' ? 'info' : ($role == 'Caretaker' ? 'success' : 'primary')) ?>">
                            <?= $role ?>
                        </span>
                        <?php if ($userData['isActive']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Username:</strong> <span><?= htmlspecialchars($userData['username']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Email:</strong> <span><?= htmlspecialchars($userData['email']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Phone:</strong> <span><?= htmlspecialchars($userData['phoneNo']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Age:</strong> <span><?= $userData['age'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Gender:</strong> <span><?= htmlspecialchars($userData['gender']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Joined:</strong>
                            <span><?= date('M d, Y', strtotime($userData['createdAt'] ?? 'now')) ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Role Specific Info -->
        <div class="col-md-8">

            <!-- Health Profile (For Elderly) -->
            <?php if ($role == 'Elderly' || $role == 'User'): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="m-0"><i class="bi bi-heart-pulse"></i> Health Profile</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-4 text-center border-end">
                                <h6 class="text-muted">Height</h6>
                                <p class="h4"><?= $profile->height ? $profile->height . ' cm' : '-' ?></p>
                            </div>
                            <div class="col-sm-4 text-center border-end">
                                <h6 class="text-muted">Weight</h6>
                                <p class="h4"><?= $profile->weight ? $profile->weight . ' kg' : '-' ?></p>
                            </div>
                            <div class="col-sm-4 text-center">
                                <h6 class="text-muted">BMI</h6>
                                <?php
                                $bmi = '-';
                                if ($profile->height && $profile->weight) {
                                    $hM = $profile->height / 100;
                                    $bmi = number_format($profile->weight / ($hM * $hM), 1);
                                }
                                ?>
                                <p class="h4"><?= $bmi ?></p>
                            </div>
                        </div>
                        <hr>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6><strong>Allergies:</strong></h6>
                                <p><?= !empty($profile->allergies) ? htmlspecialchars(implode(', ', $profile->allergies)) : 'None' ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6><strong>Medical Conditions:</strong></h6>
                                <p><?= !empty($profile->healthCondition) ? htmlspecialchars(implode(', ', $profile->healthCondition)) : 'None' ?>
                                </p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6><strong>Medications:</strong></h6>
                                <?php if (empty($profile->getMedications())): ?>
                                    <p class="text-muted">No medications recorded.</p>
                                <?php else: ?>
                                    <ul class="list-inline">
                                        <?php foreach ($profile->getMedications() as $med): ?>
                                            <li class="list-inline-item"><span
                                                    class="badge bg-warning text-dark"><?= htmlspecialchars($med['name']) ?>
                                                    (<?= htmlspecialchars($med['dosage']) ?>)</span></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Linked Professionals & Diet Plan -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="m-0"><i class="bi bi-people"></i> Linked Users</h5>
                            </div>
                            <div class="card-body">
                                <h6><strong>Assigned Dietitian:</strong></h6>
                                <?php if ($role == 'Elderly' || $role == 'User'): ?>
                                    <form method="post" class="d-flex gap-2">
                                        <select name="dietitian_id" class="form-select form-select-sm">
                                            <option value="">-- Unassigned --</option>
                                            <?php foreach ($allDietitians as $d): ?>
                                                <option value="<?= $d['userID'] ?>" <?= ($userObj->assignedDietitianID == $d['userID']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($d['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" name="assign_dietitian"
                                            class="btn btn-sm btn-primary">Update</button>
                                    </form>

                                <?php else: ?>
                                    <span class="text-muted">Not applicable for '<?= $role ?>' role</span>
                                <?php endif; ?>
                                <hr>
                                <h6><strong>Linked Caretaker(s):</strong></h6>
                                <?php
                                // Reverse lookup: Find caretakers linked to this Patient
                                $stmt = $pdo->prepare("SELECT u.name FROM user_links ul JOIN users u ON ul.caretakerID = u.userID WHERE ul.patientID = ?");
                                $stmt->execute([$viewUserID]);
                                $caretakers = $stmt->fetchAll(PDO::FETCH_COLUMN);

                                if ($caretakers) {
                                    echo '<ul>';
                                    foreach ($caretakers as $cn)
                                        echo '<li>' . htmlspecialchars($cn) . '</li>';
                                    echo '</ul>';
                                } else {
                                    echo '<span class="text-muted">None Linked</span>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-success text-white">
                                <h5 class="m-0"><i class="bi bi-calendar-check"></i> Current Diet Plan</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                // Fetch active diet plan description or status
                                // Fetch active diet plan description or status
                                $stmt = $pdo->prepare("
                                    SELECT dpa.*, dp.createdAt FROM diet_plan_approvals dpa 
                                    JOIN diet_plans dp ON dpa.dietPlanID = dp.dietPlanID
                                    WHERE dp.elderlyID = ? AND dpa.status = 'Approved' 
                                    ORDER BY dp.createdAt DESC LIMIT 1
                                ");
                                $stmt->execute([$viewUserID]);
                                $plan = $stmt->fetch(PDO::FETCH_ASSOC);

                                if ($plan) {
                                    echo '<h6><strong>Plan Status:</strong> <span class="text-success">Active</span></h6>';
                                    echo '<p>Created on: ' . date('M d, Y', strtotime($plan['createdAt'])) . '</p>';
                                    echo '<a href="#" class="btn btn-sm btn-outline-success">View Full Plan</a>';
                                } else {
                                    echo '<p class="text-muted">No active approved diet plan found.</p>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($role == 'Dietitian'): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="m-0">Professional Info</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        if ($userObj instanceof Dietitian) {
                            echo '<p><strong>License No:</strong> ' . ($userObj->licenseNo ?? 'N/A') . '</p>';
                            $quals = isset($userObj->qualification) ? (is_array($userObj->qualification) ? implode(', ', $userObj->qualification) : $userObj->qualification) : 'None';
                            echo '<p><strong>Qualifications:</strong> ' . htmlspecialchars($quals) . '</p>';

                            echo '<hr><h6>Assigned Patients:</h6>';
                            // Fetch Assigned Patients
                            $apStmt = $pdo->prepare("
                                SELECT u.userID, u.name 
                                FROM elderly e
                                JOIN users u ON e.elderlyID = u.userID
                                WHERE e.assignedDietitianID = ?
                            ");
                            $apStmt->execute([$viewUserID]);
                            $assignedPatients = $apStmt->fetchAll(PDO::FETCH_ASSOC);

                            if ($assignedPatients) {
                                echo '<div class="list-group list-group-flush">';
                                foreach ($assignedPatients as $p) {
                                    $initial = strtoupper(substr($p['name'], 0, 1));
                                    echo '<div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-light text-primary me-3 d-flex justify-content-center align-items-center rounded-circle fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                    ' . $initial . '
                                                </div>
                                                <div>
                                                    <a href="user-details.php?id=' . $p['userID'] . '" class="text-decoration-none fw-semibold text-dark">' . htmlspecialchars($p['name']) . '</a>
                                                    <div class="small text-muted">ID: ' . $p['userID'] . '</div>
                                                </div>
                                            </div>
                                            <a href="user-details.php?id=' . $p['userID'] . '" class="btn btn-sm btn-outline-primary rounded-pill px-3">View</a>
                                          </div>';
                                }
                                echo '</div>';
                            } else {
                                echo '<div class="text-muted text-center py-3 bg-light rounded"><i class="bi bi-people mb-2 d-block fs-4"></i>No patients assigned.</div>';
                            }
                        }
                        ?>
                    </div>
                </div>

            <?php elseif ($role == 'Caretaker'): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="m-0">Caretaker Details</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        if ($userObj instanceof Caretaker) {
                            echo '<p><strong>Relationship:</strong> ' . ($userObj->relationship ?? 'N/A') . '</p>';
                            echo '<p><strong>Emergency Contact:</strong> ' . ($userObj->emergencyContact ?? 'N/A') . '</p>';

                            echo '<hr><h6>Linked Patients:</h6>';
                            $patients = $userObj->getLinkedPatients();
                            if ($patients) {
                                echo '<ul>';
                                foreach ($patients as $p)
                                    echo '<li>' . htmlspecialchars($p->name) . '</li>';
                                echo '</ul>';
                            } else {
                                echo '<span class="text-muted">No patients linked yet.</span>';
                            }
                        }
                        ?>
                        </div>
                    </div>
            <?php endif; ?>

        </div>
    </div>
</div><?php include '../../includes/footer.php'; ?>