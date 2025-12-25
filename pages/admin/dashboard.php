<?php
// pages/admin/dashboard.php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

// Ensure only Admin can access
requireRole('Admin');

$user = getCurrentUser();
include '../../includes/header.php';

// Fetch Quick Stats
// 1. Total Users (Elderly)
$stmt = $pdo->query("SELECT COUNT(*) FROM elderly");
$totalElderly = $stmt->fetchColumn();

// 2. Total Dietitians
$stmt = $pdo->query("SELECT COUNT(*) FROM dietitians");
$totalDietitians = $stmt->fetchColumn();

// 3. Total Caretakers
$stmt = $pdo->query("SELECT COUNT(*) FROM caretakers");
$totalCaretakers = $stmt->fetchColumn();

// 4. Pending Diet Plans
$stmt = $pdo->query("SELECT COUNT(*) FROM diet_plan_approvals WHERE status = 'Pending'");
$pendingPlans = $stmt->fetchColumn();

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary"><i class="bi bi-shield-lock"></i> Admin Dashboard</h2>
        <div>
            <span class="text-muted me-2">Welcome, <?= htmlspecialchars($user->name) ?></span>
            <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card shadow-sm border-left-primary h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Elderly Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalElderly ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-left-info h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Registered Dietitians
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalDietitians ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-activity fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-left-success h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Caretakers
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalCaretakers ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-heart-pulse fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-left-warning h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Plans</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pendingPlans ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clipboard-data fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Row -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="m-0"><i class="bi bi-gear"></i> System Management</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="users.php"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div><i class="bi bi-person-lines-fill me-2"></i> Manage Users</div>
                        <span class="badge bg-secondary rounded-pill">CRUD</span>
                    </a>
                    <a href="diet-plans.php"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div><i class="bi bi-journal-medical me-2"></i> Manage Diet Plans</div>
                        <span class="badge bg-secondary rounded-pill">List/Delete</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="m-0">Recent Registrations</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php
                        // Fetch 5 most recent users
                        $stmt = $pdo->query("SELECT name, role, email FROM users ORDER BY userID DESC LIMIT 5");
                        while ($u = $stmt->fetch()):
                            ?>
                            <li class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($u['name']) ?></h6>
                                    <small><?= $u['role'] ?></small>
                                </div>
                                <small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>