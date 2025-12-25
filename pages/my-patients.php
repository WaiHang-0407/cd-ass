<?php
// pages/my-patients.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../classes/DietPlan.php';
require_once '../classes/Profile.php';

requireRole('Dietitian');
$user = getCurrentUser();

// Fetch Assigned Patients with Plan Status
$stmt = $pdo->prepare("
    SELECT u.userID, u.name, u.email, u.phoneNo, u.age, u.gender, e.elderlyID,
           (SELECT status FROM diet_plan_approvals dpa 
            JOIN diet_plans dp ON dpa.dietPlanID = dp.dietPlanID 
            WHERE dp.elderlyID = u.userID 
            ORDER BY dp.createdAt DESC LIMIT 1) as planStatus,
            (SELECT createdAt FROM diet_plans dp 
            WHERE dp.elderlyID = u.userID 
            ORDER BY createdAt DESC LIMIT 1) as planDate
    FROM elderly e
    JOIN users u ON e.elderlyID = u.userID
    WHERE e.assignedDietitianID = ?
    ORDER BY u.name ASC
");
$stmt->execute([$user->userID]);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary"><i class="bi bi-people-fill"></i> My Patients</h2>
            <p class="text-muted">Manage the diet plans and health profiles of your assigned patients.</p>
        </div>
        <div>
            <span class="badge bg-white text-primary border border-primary fs-6 px-3 py-2 shadow-sm">
                Total Assigned: <?= count($patients) ?>
            </span>
        </div>
    </div>

    <?php if (empty($patients)): ?>
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <div class="mb-3">
                    <i class="bi bi-person-slash display-1 text-muted opacity-25"></i>
                </div>
                <h4 class="text-muted">No Patients Assigned Yet</h4>
                <p>Contact an administrator to have patients assigned to your profile.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($patients as $p):
                // Determine Status Badge
                $statusBadge = '<span class="badge bg-secondary">No Plan</span>';
                if ($p['planStatus'] == 'Approved') {
                    $statusBadge = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Active Plan</span>';
                } elseif ($p['planStatus'] == 'Pending') {
                    $statusBadge = '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Review Needed</span>';
                } elseif ($p['planStatus'] == 'Revise') {
                    $statusBadge = '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle"></i> Revision Requested</span>';
                }

                $initial = strtoupper(substr($p['name'], 0, 1));
                $bgColors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
                $randColor = $bgColors[array_rand($bgColors)];
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-circle bg-<?= $randColor ?> text-white fs-4 d-flex justify-content-center align-items-center rounded-circle shadow-sm me-3"
                                    style="width: 50px; height: 50px;">
                                    <?= $initial ?>
                                </div>
                                <div>
                                    <h5 class="card-title fw-bold mb-0 text-dark"><?= htmlspecialchars($p['name']) ?></h5>
                                    <small class="text-muted"><?= $p['age'] ?> yrs • <?= $p['gender'] ?></small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center small mb-1">
                                    <span class="text-muted">Diet Plan Status:</span>
                                    <?= $statusBadge ?>
                                </div>
                                <?php if ($p['planDate']): ?>
                                    <div class="d-flex justify-content-between align-items-center small text-muted">
                                        <span>Last Update:</span>
                                        <span><?= date('M d, Y', strtotime($p['planDate'])) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <hr class="opacity-10 my-3">

                            <div class="d-grid gap-2">
                                <a href="profile.php?id=<?= $p['userID'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-person-lines-fill"></i> Health Profile
                                </a>
                                <?php if ($p['planStatus'] == 'Pending' || $p['planStatus'] == 'Revise'): ?>
                                    <a href="review-plans.php?filter=<?= $p['planStatus'] ?>&id=<?= $p['planID'] ?? '' ?>"
                                        class="btn btn-warning btn-sm text-dark fw-bold">
                                        <i class="bi bi-pencil-square"></i> Review Plan
                                    </a>
                                <?php else: ?>
                                    <a href="diet-plan.php?id=<?= $p['userID'] ?>" class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-journal-text"></i> Manage Diet Plan
                                    </a>
                                <?php endif; ?>
                                <a href="food-log.php?view_user=<?= $p['userID'] ?>" class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-egg-fried"></i> View Food Log
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .hover-shadow:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
    }

    .transition-all {
        transition: all 0.3s ease;
    }
</style>

<?php include '../includes/footer.php'; ?>