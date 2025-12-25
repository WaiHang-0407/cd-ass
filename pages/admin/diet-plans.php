<?php
// pages/admin/diet-plans.php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

requireRole('Admin');
$user = getCurrentUser();
include '../../includes/header.php';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_plan_id'])) {
        $delID = $_POST['delete_plan_id'];
        try {
            $pdo->beginTransaction();
            // Delete Approval
            $stmt = $pdo->prepare("DELETE FROM diet_plan_approvals WHERE dietPlanID = ?");
            $stmt->execute([$delID]);

            // Delete Meals/Foods cascade handled by DB or explicit?
            // Assuming no cascade for now, but safe deletion requires cleanup.
            // For simplicity in this iteration, we delete dependent records first or rely on constraints.
            // Let's rely on DB constraints if set, otherwise simple delete might fail or orphan.
            // To be safe, we delete meals linked to this plan.
            $stmt = $pdo->prepare("DELETE FROM foods WHERE mealID IN (SELECT mealID FROM meals WHERE dietPlanID = ?)");
            $stmt->execute([$delID]);

            $stmt = $pdo->prepare("DELETE FROM meals WHERE dietPlanID = ?");
            $stmt->execute([$delID]);

            $stmt = $pdo->prepare("DELETE FROM diet_plans WHERE dietPlanID = ?");
            $stmt->execute([$delID]);

            $pdo->commit();
            $success = "Diet Plan deleted successfully.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Search & Sort Logic
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'createdAt';
$dir = $_GET['dir'] ?? 'DESC';

$allowedSorts = ['elderlyName', 'status', 'createdAt'];
if (!in_array($sort, $allowedSorts))
    $sort = 'createdAt';
if (!in_array(strtoupper($dir), ['ASC', 'DESC']))
    $dir = 'DESC';

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$query = "
    SELECT dp.dietPlanID, dp.createdAt, u.name as elderlyName, dpa.status
    FROM diet_plans dp
    JOIN users u ON dp.elderlyID = u.userID
    LEFT JOIN diet_plan_approvals dpa ON dp.dietPlanID = dpa.dietPlanID
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (u.name LIKE ?)";
    $params[] = "%$search%";
}

// Count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM (" . $query . ") as count_table");
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Final Fetch
$query .= " ORDER BY $sort $dir LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getSortLink($column, $currentSort, $currentDir, $search)
{
    $newDir = ($column == $currentSort && $currentDir == 'ASC') ? 'DESC' : 'ASC';
    return "?sort=$column&dir=$newDir&search=" . urlencode($search);
}

function getSortIcon($column, $currentSort, $currentDir)
{
    if ($column != $currentSort)
        return '<i class="bi bi-arrow-down-up text-muted small ms-1"></i>';
    return $currentDir == 'ASC' ? '<i class="bi bi-arrow-up-short ms-1"></i>' : '<i class="bi bi-arrow-down-short ms-1"></i>';
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary"><i class="bi bi-journal-medical"></i> Diet Plans Management</h2>
    </div>

    <!-- Feedback -->
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <!-- Search -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-center">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search by User Name..."
                        value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary">Search</button>
                    <a href="diet-plans.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th><a href="<?= getSortLink('elderlyName', $sort, $dir, $search) ?>"
                                    class="text-dark text-decoration-none">User
                                    <?= getSortIcon('elderlyName', $sort, $dir) ?></a></th>
                            <th><a href="<?= getSortLink('status', $sort, $dir, $search) ?>"
                                    class="text-dark text-decoration-none">Status
                                    <?= getSortIcon('status', $sort, $dir) ?></a></th>
                            <th><a href="<?= getSortLink('createdAt', $sort, $dir, $search) ?>"
                                    class="text-dark text-decoration-none">Created Date
                                    <?= getSortIcon('createdAt', $sort, $dir) ?></a></th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($plans)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">No diet plans found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($plans as $p): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($p['elderlyName']) ?></div>
                                        <small class="text-muted">ID: <?= $p['dietPlanID'] ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $badge = 'secondary';
                                        if ($p['status'] == 'Approved')
                                            $badge = 'success';
                                        if ($p['status'] == 'Pending')
                                            $badge = 'warning';
                                        if ($p['status'] == 'Revise')
                                            $badge = 'danger';
                                        ?>
                                        <span class="badge bg-<?= $badge ?>"><?= $p['status'] ?? 'Unknown' ?></span>
                                    </td>
                                    <td><?= date('M d, Y H:i', strtotime($p['createdAt'])) ?></td>
                                    <td class="text-end">
                                        <a href="../../pages/review-plans.php?id=<?= $p['dietPlanID'] ?>"
                                            class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form method="post" class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this plan? This cannot be undone.');">
                                            <input type="hidden" name="delete_plan_id" value="<?= $p['dietPlanID'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="card-footer bg-white d-flex justify-content-end">
            <nav>
                <ul class="pagination mb-0">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link"
                                href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&dir=<?= $dir ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>