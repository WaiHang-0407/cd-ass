<?php
// pages/dashboard.php
require_once '../includes/auth.php';
require_once '../includes/db.php'; // Fix Undefined Variable $pdo
requireLogin();
include '../includes/header.php';

$user = getCurrentUser();
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Welcome, <?= htmlspecialchars($user->name) ?>! <span
                class="badge bg-secondary fs-6"><?= $user->role ?></span></h2>
    </div>
</div>

<div class="row">
    <?php if ($user->role == 'User' || $user->role == 'Elderly'): ?>
        <?php
        // Fetch Daily Progress
        require_once '../classes/Progress.php';
        require_once '../classes/Profile.php';
        require_once '../classes/DietPlan.php';

        $progress = new Progress($pdo, $user->userID);
        $profile = new Profile($pdo, $user->userID);

        $calLimit = $profile->caloriesLimit ?: 2000;
        $calPerc = min(100, ($progress->caloriesTaken / $calLimit) * 100);

        $protTarget = 60; // Approximate default if not set
        $protPerc = min(100, ($progress->proteinTaken / $protTarget) * 100);

        $waterTarget = 2.0;
        $waterPerc = min(100, ($progress->waterIntake / $waterTarget) * 100);

        // Fetch Today's Meal Plan
        $todayMeals = [];
        $planMsg = "No active diet plan.";

        // Get Latest Approved Plan
        $stmt = $pdo->prepare("
            SELECT dp.* 
            FROM diet_plans dp 
            JOIN diet_plan_approvals dpa ON dp.dietPlanID = dpa.dietPlanID
            WHERE dp.elderlyID = ? AND dpa.status = 'Approved'
            ORDER BY dp.createdAt DESC LIMIT 1
        ");
        $stmt->execute([$user->userID]);
        $planData = $stmt->fetch();

        if ($planData) {
            $plan = new DietPlan($pdo, $planData);

            // Calculate Day Number (e.g., Day 1, Day 2...)
            // Simple Logic: Diff in days % Duration.
            // If duration is 7 days, and it's day 8, show day 1.
            $start = new DateTime($plan->createdAt);
            $now = new DateTime();
            $diff = $now->diff($start)->days;
            $duration = $plan->duration ?? 7;

            $dayNum = ($diff % $duration) + 1;

            // Fetch meals for this Day
            $mstmt = $pdo->prepare("SELECT * FROM meals WHERE dietPlanID = ? AND day = ? ORDER BY mealID ASC");
            $mstmt->execute([$plan->dietPlanID, $dayNum]);
            $todayMeals = $mstmt->fetchAll();
            $planMsg = "Day $dayNum of " . $duration . " (Active)";
        }

        // Fetch Shopping List
        $slStmt = $pdo->prepare("SELECT * FROM shopping_items WHERE userID = ? ORDER BY createdAt DESC");
        $slStmt->execute([$user->userID]);
        $shoppingList = $slStmt->fetchAll();

        // --- Weekly Progress Logic ---
        $weekOffset = isset($_GET['week_offset']) ? (int) $_GET['week_offset'] : 0;

        // Calculate Date Range
        // Start of THIS week (Mon)
        $startThisWeek = new DateTime('monday this week');

        // Find Earliest Log Date to Determine Range
        $minDateStmt = $pdo->prepare("SELECT MIN(date) FROM progress WHERE elderlyID = ?");
        $minDateStmt->execute([$user->userID]);
        $minDateStr = $minDateStmt->fetchColumn();

        $totalWeeks = 0;
        if ($minDateStr) {
            $minDate = new DateTime($minDateStr);
            // Ensure min date starts on a Monday for cleaner calculation
            $minDate->modify('monday this week');
            $diff = $startThisWeek->diff($minDate);
            $totalWeeks = floor($diff->days / 7);
        }

        // Offset Logic
        $startSelectedWeek = clone $startThisWeek;
        $startSelectedWeek->modify("-$weekOffset week");
        $endSelectedWeek = clone $startSelectedWeek;
        $endSelectedWeek->modify('+6 days');

        $startDateStr = $startSelectedWeek->format('Y-m-d');
        $endDateStr = $endSelectedWeek->format('Y-m-d');

        // Fetch Data for Range
        $chartLabels = [];
        $chartCals = [];
        $chartWater = [];
        $chartLimit = []; // Constant array for line
    
        $currentDate = clone $startSelectedWeek;
        // Pre-fill valid array loops
        for ($i = 0; $i < 7; $i++) {
            $d = $currentDate->format('Y-m-d');
            $dayName = $currentDate->format('D'); // Mon, Tue...
            $chartLabels[] = $dayName;

            // Query
            $pStmt = $pdo->prepare("SELECT caloriesTaken, waterIntake FROM progress WHERE elderlyID = ? AND date = ?");
            $pStmt->execute([$user->userID, $d]);
            $pRes = $pStmt->fetch();

            $chartCals[] = $pRes ? (int) $pRes['caloriesTaken'] : 0;
            $chartWater[] = $pRes ? (float) $pRes['waterIntake'] : 0;
            $chartLimit[] = $calLimit;

            $currentDate->modify('+1 day');
        }
        ?>

        <!-- Weekly Progress Widget -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="m-0 text-primary"><i class="bi bi-bar-chart"></i> Weekly Analysis</h5>
                    <form method="get" class="d-flex align-items-center">
                        <select name="week_offset" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="0" <?= $weekOffset == 0 ? 'selected' : '' ?>>This Week
                                (<?= $startThisWeek->format('M d') ?>)</option>
                            <?php for ($w = 1; $w <= $totalWeeks; $w++):
                                $optDate = clone $startThisWeek;
                                $optDate->modify("-$w week");
                                ?>
                                <option value="<?= $w ?>" <?= $weekOffset == $w ? 'selected' : '' ?>>
                                    Week of <?= $optDate->format('M d') ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </form>
                </div>
                <div class="card-body">
                    <canvas id="weeklyChart" height="100"></canvas>
                </div>
            </div>

            <script>
                const ctx = document.getElementById('weeklyChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode($chartLabels) ?>,
                        datasets: [
                            {
                                label: 'Calories',
                                data: <?= json_encode($chartCals) ?>,
                                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                                yAxisID: 'y',
                                order: 2
                            },
                            {
                                label: 'Limit',
                                data: <?= json_encode($chartLimit) ?>,
                                type: 'line',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 2,
                                pointRadius: 0,
                                fill: false,
                                yAxisID: 'y',
                                order: 0
                            },
                            {
                                label: 'Water (L)',
                                data: <?= json_encode($chartWater) ?>,
                                type: 'bar',
                                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                                yAxisID: 'y1',
                                order: 0
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: { display: true, text: 'Calories' }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: { display: true, text: 'Water (L)' },
                                grid: { drawOnChartArea: false }
                            }
                        }
                    }
                });
            </script>
        </div>

        <!-- Daily Progress Section -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="m-0 text-primary"><i class="bi bi-activity"></i> Daily Progress (<?= date('M d') ?>)</h5>
                    <span class="badge bg-warning text-dark"><i class="bi bi-fire"></i> <?= $progress->getStreak() ?> Day
                        Streak</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <h6 class="text-muted">Calories</h6>
                            <h3 class="fw-bold"><?= $progress->caloriesTaken ?> <span class="fs-6 text-muted">/
                                    <?= $calLimit ?></span></h3>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $calPerc ?>%">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <h6 class="text-muted">Protein</h6>
                            <h3 class="fw-bold"><?= $progress->proteinTaken ?>g</h3>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: <?= $protPerc ?>%"></div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <h6 class="text-muted">Water</h6>
                            <h3 class="fw-bold"><?= $progress->waterIntake ?>L</h3>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $waterPerc ?>%">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-2">
                        <a href="food-log.php" class="btn btn-sm btn-outline-success">Log Intake</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Menu Section -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="m-0"><i class="bi bi-calendar-check"></i> Today's Menu</h5>
                    <span class="badge bg-light text-primary"><?= $planMsg ?></span>
                </div>
                <div class="card-body">
                    <?php if (empty($todayMeals)): ?>
                        <div class="text-muted text-center py-4">
                            <i class="bi bi-egg-fried fs-1 d-block mb-3"></i>
                            <p>No meals scheduled for today. <a href="diet-plan.php">Generate a plan?</a></p>
                        </div>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($todayMeals as $meal):
                                // Fetch Foods
                                $fstmt = $pdo->prepare("SELECT foodName FROM foods WHERE mealID = ?");
                                $fstmt->execute([$meal['mealID']]);
                                $foods = $fstmt->fetchAll(PDO::FETCH_COLUMN);
                                ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= htmlspecialchars($meal['mealType']) ?></strong>
                                        <span class="badge bg-secondary"><?= $meal['totalCalories'] ?> kcal</span>
                                    </div>
                                    <p class="mb-0 text-muted small"><?= implode(', ', $foods) ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light"> Quick Actions </div>
                <div class="card-body d-grid gap-2">
                    <a href="messages.php" class="btn btn-outline-primary text-start"><i class="bi bi-chat-dots me-2"></i>
                        Message Dietitian</a>
                    <a href="diet-plan.php" class="btn btn-outline-primary text-start"><i class="bi bi-file-text me-2"></i>
                        View Full Plan</a>
                    <a href="food-log.php" class="btn btn-outline-success text-start"><i class="bi bi-plus-circle me-2"></i>
                        Log Food / View History</a>
                    <a href="profile.php" class="btn btn-outline-info text-start"><i class="bi bi-person-gear me-2"></i>
                        Update Profile</a>
                </div>
            </div>
        </div>

        <!-- Shopping List Widget -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100 border-success">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-basket"></i> Shopping List</span>
                    <span class="badge bg-white text-success"><?= count($shoppingList) ?></span>
                </div>
                <ul class="list-group list-group-flush small" id="dashboardShopList">
                    <?php if (empty($shoppingList)): ?>
                        <li class="list-group-item text-muted text-center py-3">Your list is empty.</li>
                    <?php else: ?>
                        <?php foreach ($shoppingList as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center"
                                id="shop-item-<?= $item['itemID'] ?>">
                                <?= htmlspecialchars($item['item']) ?>
                                <button class="btn btn-sm text-danger p-0" onclick="removeShopItem('<?= $item['itemID'] ?>')">
                                    <i class="bi bi-x-circle-fill"></i>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <?php if (!empty($shoppingList)): ?>
                    <div class="card-footer bg-white border-top-0">
                        <button class="btn btn-outline-success w-100" onclick="clearShoppingList()">
                            <i class="bi bi-check-all"></i> Complete Shopping
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($user->role == 'Dietitian'): ?>
        <?php
        // Fetch stats
        // 1. My Patients Count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM elderly WHERE assignedDietitianID = ?");
        $stmt->execute([$user->userID]);
        $myPatientsCount = $stmt->fetchColumn();

        // 2. Pending Reviews Count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM diet_plan_approvals dpa 
            JOIN diet_plans dp ON dpa.dietPlanID = dp.dietPlanID
            JOIN elderly e ON dp.elderlyID = e.elderlyID
            WHERE e.assignedDietitianID = ? AND dpa.status = 'Pending'
        ");
        $stmt->execute([$user->userID]);
        $pendingReviewsCount = $stmt->fetchColumn();

        // 3. Unread Messages (Placeholder for now until Messages DB is ready/populated)
        $unreadMessages = 0;
        // Logic will be added when messaging is implemented
        ?>

        <!-- Dietitian Dashboard -->
        <h3 class="mb-4">Dietitian Dashboard</h3>

        <div class="row">
            <!-- My Patients Widget -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100 border-info">
                    <div class="card-body text-center">
                        <i class="bi bi-people fs-1 text-info mb-3"></i>
                        <h5 class="card-title">My Patients</h5>
                        <h2 class="display-4 fw-bold"><?= $myPatientsCount ?></h2>
                        <a href="my-patients.php" class="btn btn-outline-info stretched-link">View Patients</a>
                    </div>
                </div>
            </div>

            <!-- Pending Reviews Widget -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100 border-warning">
                    <div class="card-body text-center">
                        <i class="bi bi-clipboard-check fs-1 text-warning mb-3"></i>
                        <h5 class="card-title">Pending Reviews</h5>
                        <h2 class="display-4 fw-bold"><?= $pendingReviewsCount ?></h2>
                        <a href="review-plans.php?filter=Pending" class="btn btn-outline-warning stretched-link">Review
                            Plans</a>
                    </div>
                </div>
            </div>

            <!-- Messages Widget -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100 border-primary">
                    <div class="card-body text-center">
                        <i class="bi bi-chat-dots fs-1 text-primary mb-3"></i>
                        <h5 class="card-title">Messages</h5>
                        <h2 class="display-4 fw-bold"><?= $unreadMessages ?></h2>
                        <p class="text-muted small">Unread Messages</p>
                        <a href="messages.php" class="btn btn-outline-primary stretched-link">Go to Chat</a>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($user->role == 'Admin'): ?>
        <!-- Admin Dashboard -->
        <div class="col-md-4">
            <div class="card h-100 border-dark">
                <div class="card-body text-center">
                    <h5 class="card-title">Manage Users</h5>
                    <a href="#" class="btn btn-dark">Manage</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

<script>
    function removeShopItem(id) {
        if (!confirm('Remove this item?')) return;

        fetch('../ajax/shopping_list.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'remove_id', itemID: id })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('shop-item-' + id).remove();
                    // Optional: Reload to update count or just decrement visual count
                } else {
                    alert('Failed to remove item.');
                }
            });
    }
    function clearShoppingList() {
        if (!confirm('Mark all items as purchased and clear the list?')) return;

        fetch('../ajax/shopping_list.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'clear_all' })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to clear list.');
                }
            });
    }
</script>