<?php
// pages/patient-details.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../classes/Profile.php';
require_once '../classes/Progress.php';
require_once '../classes/DietPlan.php';

requireRole('Dietitian');
$curUser = getCurrentUser();

$patientID = $_GET['id'] ?? '';

// Check if patient is assigned to this dietitian
$stmt = $pdo->prepare("SELECT elderlyID FROM elderly WHERE elderlyID = ? AND assignedDietitianID = ?");
$stmt->execute([$patientID, $curUser->userID]);
if (!$stmt->fetch()) {
    die("Access Denied: This patient is not assigned to you.");
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE userID = ?");
$stmt->execute([$patientID]);
$patient = $stmt->fetchObject();

$profile = new Profile($pdo, $patientID);
$progress = new Progress($pdo, $patientID);

include '../includes/header.php';
?>

<div class="row mb-3">
    <div class="col-12">
        <a href="my-patients.php" class="btn btn-outline-secondary mb-3">&larr; Back to List</a>
        <h2>Patient: <?= htmlspecialchars($patient->name) ?></h2>
    </div>
</div>

<div class="row">
    <!-- Profile Card -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-info text-white">Profile Summary</div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><strong>Age:</strong> <?= $patient->age ?></li>
                    <li><strong>Gender:</strong> <?= $patient->gender ?></li>
                    <li><strong>Height:</strong> <?= $profile->height ?> cm</li>
                    <li><strong>Weight:</strong> <?= $profile->weight ?> kg</li>
                    <li><strong>BMI:</strong> <?= number_format($profile->bmi, 1) ?></li>
                    <li class="mt-2"><strong>Conditions:</strong></li>
                    <li><?= implode(', ', $profile->healthCondition ?? []) ?></li>
                    <li class="mt-2"><strong>Allergies:</strong></li>
                    <li><?= implode(', ', $profile->allergies ?? []) ?></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Stats Card -->
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-success text-white">Current Progress (Today)</div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-sm-4">
                        <h5>Calories</h5>
                        <h3 class="fw-bold text-success"><?= $progress->caloriesTaken ?></h3>
                        <p class="text-muted">/ <?= $profile->caloriesLimit ?> kcal</p>
                    </div>
                    <div class="col-sm-4">
                        <h5>Protein</h5>
                        <h3 class="fw-bold text-info"><?= $progress->proteinTaken ?>g</h3>
                    </div>
                    <div class="col-sm-4">
                        <h5>Water</h5>
                        <h3 class="fw-bold text-primary"><?= $progress->waterIntake ?>L</h3>
                    </div>
                </div>
                <hr>
                <h5>Latest Diet Plan</h5>
                <?php
                $stmt = $pdo->prepare("
                    SELECT dp.*, dpa.status 
                    FROM diet_plans dp 
                    JOIN diet_plan_approvals dpa ON dp.dietPlanID = dpa.dietPlanID
                    WHERE dp.elderlyID = ? ORDER BY dp.createdAt DESC LIMIT 1");
                $stmt->execute([$patientID]);
                $planData = $stmt->fetch();
                if ($planData):
                    $badgeClass = ($planData['status'] == 'Approved' ? 'bg-success' : 'bg-warning text-dark');
                    ?>
                    <p>
                        <strong>Status:</strong> <span
                            class="badge <?= $badgeClass ?>"><?= $planData['status'] ?></span><br>
                        <strong>Created:</strong> <?= date('M d, Y', strtotime($planData['createdAt'])) ?>
                    </p>
                    <a href="review-plans.php?filter=<?= $planData['status'] ?>" class="btn btn-sm btn-outline-dark">Go to
                        Review</a>
                <?php else: ?>
                    <p class="text-muted">No diet plan found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- History or Logs (Simplistic list) -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">Recent Food Logs</div>
            <div class="card-body">
                <?php
                $stmt = $pdo->prepare("SELECT * FROM food_logs WHERE elderlyID = ? ORDER BY loggedAt DESC LIMIT 10");
                $stmt->execute([$patientID]);
                $logs = $stmt->fetchAll();
                ?>
                <?php if ($logs): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Food</th>
                                    <th>Cals</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($log['foodName']) ?></td>
                                        <td><?= $log['calories'] ?></td>
                                        <td><?= date('M d H:i', strtotime($log['loggedAt'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No logs found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>