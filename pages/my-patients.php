<?php
// pages/my-patients.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../classes/Profile.php';

requireRole('Dietitian');
$user = getCurrentUser();

$stmt = $pdo->prepare("
    SELECT u.* 
    FROM users u 
    JOIN elderly e ON u.userID = e.elderlyID 
    WHERE e.assignedDietitianID = ?
");
$stmt->execute([$user->userID]);
$patients = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row mb-3">
    <div class="col-12">
        <a href="dashboard.php" class="btn btn-outline-secondary mb-3">&larr; Back to Dashboard</a>
        <h2>My Patients</h2>
    </div>
</div>

<div class="row">
    <?php if (empty($patients)): ?>
        <div class="col-12">
            <div class="alert alert-info">You have no assigned patients yet.</div>
        </div>
    <?php else: ?>
        <?php foreach ($patients as $p):
            $profile = new Profile($pdo, $p['userID']);
            $conditions = implode(', ', $profile->healthCondition ?? []);
            ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>
                        <p class="card-text text-muted mb-1">
                            <i class="bi bi-person"></i> <?= $p['age'] ?> yrs | <?= $p['gender'] ?>
                        </p>
                        <p class="card-text mb-2">
                            <small class="text-secondary">Condition: <?= $conditions ?: 'None' ?></small>
                        </p>
                        <div class="d-grid mt-3">
                            <a href="patient-details.php?id=<?= $p['userID'] ?>" class="btn btn-primary btn-sm">View Details</a>
                            <a href="messages.php?chat_with=<?= $p['userID'] ?>"
                                class="btn btn-outline-primary btn-sm mt-1">Message</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>