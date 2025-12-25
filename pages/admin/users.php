<?php
// pages/admin/users.php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

requireRole('Admin');

$user = getCurrentUser();
include '../../includes/header.php';

// Handle Actions (Toggle Status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_status_id'])) {
        $toggleID = $_POST['toggle_status_id'];
        if ($toggleID == $user->userID) {
            $error = "You cannot deactivate yourself.";
        } else {
            // Get current status
            $stmt = $pdo->prepare("SELECT isActive FROM users WHERE userID = ?");
            $stmt->execute([$toggleID]);
            $curr = $stmt->fetchColumn();

            // Toggle
            $newStatus = $curr ? 0 : 1;
            $stmt = $pdo->prepare("UPDATE users SET isActive = ? WHERE userID = ?");
            if ($stmt->execute([$newStatus, $toggleID])) {
                $success = "User status updated.";
            } else {
                $error = "Failed to update status.";
            }
        }
    }
}

// Sorting Logic
$sort = $_GET['sort'] ?? 'createdAt';
$dir = $_GET['dir'] ?? 'DESC';
$allowedSorts = ['name', 'role', 'isActive', 'createdAt', 'email', 'username'];

if (!in_array($sort, $allowedSorts)) {
    $sort = 'createdAt';
}
if (!in_array(strtoupper($dir), ['ASC', 'DESC'])) {
    $dir = 'DESC';
}

// Helper to generate sort link
function getSortLink($column, $currentSort, $currentDir, $search)
{
    $newDir = ($column == $currentSort && $currentDir == 'ASC') ? 'DESC' : 'ASC';
    return "?sort=$column&dir=$newDir&search=" . urlencode($search);
}

// Helper for sort icon
function getSortIcon($column, $currentSort, $currentDir)
{
    if ($column != $currentSort)
        return '<i class="bi bi-arrow-down-up text-muted small ms-1"></i>';
    return $currentDir == 'ASC' ? '<i class="bi bi-arrow-up-short ms-1"></i>' : '<i class="bi bi-arrow-down-short ms-1"></i>';
}

// Search Logic
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Prefix sort columns to avoid ambiguity
$sortKey = $sort;
if (in_array($sort, ['name', 'role', 'isActive', 'createdAt', 'email', 'username'])) {
    $sortKey = "u.$sort";
}

$query = "
    SELECT u.*, d.name as dietitianName 
    FROM users u
    LEFT JOIN elderly e ON u.userID = e.elderlyID
    LEFT JOIN users d ON e.assignedDietitianID = d.userID
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Get Total for Pagination
$countQuery = "SELECT COUNT(*) FROM users u WHERE 1=1";
if (!empty($search)) {
    $countQuery .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)";
}
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Final Fetch
$query .= " ORDER BY $sortKey $dir LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary"><i class="bi bi-people"></i> User Management</h2>
        <a href="user-form.php" class="btn btn-primary"><i class="bi bi-person-plus"></i> Add New User</a>
    </div>

    <!-- Feedback -->
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <!-- Search & Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-center">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control"
                        placeholder="Search by name, email, or username..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary">Search</button>
                    <a href="users.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th><a href="<?= getSortLink('name', $sort, $dir, $search) ?>"
                                    class="text-dark text-decoration-none">User
                                    <?= getSortIcon('name', $sort, $dir) ?></a></th>
                            <th><a href="<?= getSortLink('role', $sort, $dir, $search) ?>"
                                    class="text-dark text-decoration-none">Role
                                    <?= getSortIcon('role', $sort, $dir) ?></a></th>
                            <th>Linked Dietitian</th>
                            <th><a href="<?= getSortLink('isActive', $sort, $dir, $search) ?>"
                                    class="text-dark text-decoration-none">Status
                                    <?= getSortIcon('isActive', $sort, $dir) ?></a></th>
                            <th>Contact</th>
                            <th><a href="<?= getSortLink('createdAt', $sort, $dir, $search) ?>"
                                    class="text-dark text-decoration-none">Registered
                                    <?= getSortIcon('createdAt', $sort, $dir) ?></a></th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">No users found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($u['name']) ?></div>
                                        <small class="text-muted">@<?= htmlspecialchars($u['username']) ?></small>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-<?= $u['role'] == 'Admin' ? 'danger' : ($u['role'] == 'Dietitian' ? 'info' : ($u['role'] == 'Caretaker' ? 'success' : 'primary')) ?>">
                                            <?= $u['role'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($u['role'] == 'Elderly' && !empty($u['dietitianName'])): ?>
                                            <span class="text-info"><i class="bi bi-person-badge"></i>
                                                <?= htmlspecialchars($u['dietitianName']) ?></span>
                                        <?php elseif ($u['role'] == 'Elderly'): ?>
                                            <span class="text-muted small">Unassigned</span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($u['isActive']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small"><?= htmlspecialchars($u['email']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($u['phoneNo']) ?></div>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($u['createdAt'] ?? 'now')) ?></td>
                                    <td class="text-end">
                                        <a href="user-details.php?id=<?= $u['userID'] ?>"
                                            class="btn btn-sm btn-outline-info me-1">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <form method="post" class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to change this user\'s status?');">
                                            <input type="hidden" name="toggle_status_id" value="<?= $u['userID'] ?>">
                                            <?php if ($u['isActive']): ?>
                                                <button type="submit" class="btn btn-sm btn-outline-warning me-1"
                                                    <?= $u['userID'] == $user->userID ? 'disabled' : '' ?>>
                                                    <i class="bi bi-pause-circle"></i> Deactivate
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-outline-success me-1">
                                                    <i class="bi bi-play-circle"></i> Activate
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end">
            <!-- Pagination -->
            <nav>
                <ul class="pagination mb-0">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Delete Modal -->


<?php include '../../includes/footer.php'; ?>