<?php
// pages/review-plans.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../classes/DietPlan.php';
require_once '../classes/Profile.php'; // Fix Fatal Error

requireRole('Dietitian');
$user = getCurrentUser();

// Handle Actions
if (isset($_POST['action'])) {
    $planID = $_POST['dietPlanID'];
    // We instantiate DietPlanApproval directly for the action
    $approval = new DietPlanApproval($pdo, $planID, 'Pending'); // State will update

    if ($_POST['action'] == 'approve') {
        $approval->approve($user->userID);
    } elseif ($_POST['action'] == 'revise') {
        $approval->revise($user->userID);
    }
}

$filter = $_GET['filter'] ?? 'All';
$sql = "
    SELECT dp.*, u.name as elderlyName, dpa.status
    FROM diet_plans dp 
    JOIN diet_plan_approvals dpa ON dp.dietPlanID = dpa.dietPlanID 
    JOIN users u ON dp.elderlyID = u.userID
    JOIN elderly e ON u.userID = e.elderlyID
    WHERE e.assignedDietitianID = ?
";

$params = [$user->userID];
if ($filter !== 'All') {
    $sql .= " AND dpa.status = ?";
    $params[] = $filter;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$plans = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="col-12 d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Dietitian Dashboard - Review Plans</h2>
        <p>Review and approve diet plans for users.</p>
    </div>
    <form method="get" class="d-flex align-items-center">
        <label class="me-2 fw-bold">Filter:</label>
        <select name="filter" class="form-select me-2" onchange="this.form.submit()">
            <option value="All" <?= $filter == 'All' ? 'selected' : '' ?>>All Plans</option>
            <option value="Pending" <?= $filter == 'Pending' ? 'selected' : '' ?>>Pending Only</option>
            <option value="Approved" <?= $filter == 'Approved' ? 'selected' : '' ?>>Approved</option>
            <option value="Revise" <?= $filter == 'Revise' ? 'selected' : '' ?>>Revise Requested</option>
        </select>
    </form>
</div>
</div>

<?php if (empty($plans)): ?>
    <div class="alert alert-info">No plans found for filter: <strong><?= htmlspecialchars($filter) ?></strong></div>
<?php else: ?>
    <div class="accordion" id="reviewPlansAccordion">
        <?php foreach ($plans as $index => $row):
            // Instantiate DietPlan to reuse logic
            $plan = new DietPlan($pdo, $row);
            $collapseId = "collapse" . $plan->dietPlanID;
            $headerId = "heading" . $plan->dietPlanID;
            $isFirst = ($index === 0);

            $badgeClass = 'bg-secondary';
            if ($row['status'] == 'Approved')
                $badgeClass = 'bg-success';
            if ($row['status'] == 'Pending')
                $badgeClass = 'bg-warning text-dark';
            if ($row['status'] == 'Revise')
                $badgeClass = 'bg-danger';
            ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="<?= $headerId ?>">
                    <button class="accordion-button <?= $isFirst ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse"
                        data-bs-target="#<?= $collapseId ?>" aria-expanded="<?= $isFirst ? 'true' : 'false' ?>"
                        aria-controls="<?= $collapseId ?>">
                        <span class="badge <?= $badgeClass ?> me-2"><?= $row['status'] ?></span>
                        <strong>Patient: <?= htmlspecialchars($row['elderlyName']) ?></strong>
                        &nbsp;<span class="text-muted small"> (<?= date('M d, Y', strtotime($plan->createdAt)) ?>)</span>
                    </button>
                </h2>
                <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $isFirst ? 'show' : '' ?>"
                    aria-labelledby="<?= $headerId ?>" data-bs-parent="#reviewPlansAccordion">
                    <div class="accordion-body">

                        <!-- Action Buttons Top (for quick access) -->
                        <div class="d-flex gap-2 mb-3 border-bottom pb-3">
                            <form method="post" class="flex-grow-1">
                                <input type="hidden" name="dietPlanID" value="<?= $plan->dietPlanID ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-success w-100">Approve Plan</button>
                            </form>
                            <form method="post" class="flex-grow-1">
                                <input type="hidden" name="dietPlanID" value="<?= $plan->dietPlanID ?>">
                                <input type="hidden" name="action" value="revise">
                                <button type="submit" class="btn btn-danger w-100">Request Revision</button>
                            </form>
                        </div>

                        <!-- Patient Health Profile -->
                        <div class="card bg-light mb-4 border-info">
                            <div class="card-header bg-info text-dark fw-bold">Patient Health Profile</div>
                            <div class="card-body">
                                <?php
                                $profile = new Profile($pdo, $row['elderlyID']);
                                $conditions = implode(', ', $profile->healthCondition);
                                $allergies = implode(', ', $profile->allergies);
                                ?>
                                <div class="row">
                                    <div class="col-md-3">
                                        <p class="mb-1"><strong>Age:</strong> <?= $profile->age ?? 'N/A' ?> years</p>
                                        <p class="mb-1"><strong>Gender:</strong> <?= $profile->gender ?? 'N/A' ?></p>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="mb-1"><strong>Height:</strong> <?= $profile->height ?> cm</p>
                                        <p class="mb-1"><strong>Weight:</strong> <?= $profile->weight ?> kg</p>
                                        <p class="mb-1"><strong>BMI:</strong> <?= number_format($profile->bmi, 1) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Health Conditions:</strong> <?= $conditions ?: 'None' ?></p>
                                        <p class="mb-1"><strong>Allergies:</strong> <?= $allergies ?: 'None' ?></p>
                                        <p class="mb-1"><strong>Bio Limits:</strong> Cal: <?= $profile->caloriesLimit ?>, Na:
                                            <?= $profile->sodiumLimit ?>, Carb: <?= $profile->carbsLimit ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-3">Plan Details</h5>
                        <?php
                        $meals = $plan->getMeals();
                        if (empty($meals)):
                            echo "<p>No meals generated for this plan.</p>";
                        else:
                            // Group meals by Day
                            $dailyMeals = [];
                            foreach ($meals as $meal) {
                                $day = $meal['day'] ?? 1;
                                $dailyMeals[$day][] = $meal;
                            }
                            ksort($dailyMeals);
                            ?>

                            <!-- Tabs for Days -->
                            <ul class="nav nav-tabs mb-3" id="plan-<?= $plan->dietPlanID ?>-tabs" role="tablist">
                                <?php
                                $isFirstDay = true;
                                foreach (array_keys($dailyMeals) as $dayNum):
                                    $tabId = "tab-p{$plan->dietPlanID}-d{$dayNum}";
                                    $paneId = "pane-p{$plan->dietPlanID}-d{$dayNum}";
                                    ?>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link <?= $isFirstDay ? 'active' : '' ?>" id="<?= $tabId ?>"
                                            data-bs-toggle="tab" data-bs-target="#<?= $paneId ?>" type="button" role="tab"
                                            aria-controls="<?= $paneId ?>" aria-selected="<?= $isFirstDay ? 'true' : 'false' ?>">
                                            Day <?= $dayNum ?>
                                        </button>
                                    </li>
                                    <?php
                                    $isFirstDay = false;
                                endforeach;
                                ?>
                            </ul>

                            <div class="tab-content" id="plan-<?= $plan->dietPlanID ?>-content">
                                <?php
                                $isFirstDay = true;
                                foreach ($dailyMeals as $day => $dMeals):
                                    $paneId = "pane-p{$plan->dietPlanID}-d{$day}";
                                    $tabId = "tab-p{$plan->dietPlanID}-d{$day}";

                                    // Calculate Totals
                                    $dayCal = 0;
                                    $dayProt = 0;
                                    $dayCarbs = 0;
                                    $daySod = 0;
                                    foreach ($dMeals as $m) {
                                        $dayCal += $m['totalCalories'];
                                        $dayProt += $m['totalProtein'];
                                        $dayCarbs += $m['totalCarbs'];
                                        $daySod += $m['totalSodium'];
                                    }
                                    ?>
                                    <div class="tab-pane fade <?= $isFirstDay ? 'show active' : '' ?>" id="<?= $paneId ?>"
                                        role="tabpanel" aria-labelledby="<?= $tabId ?>">

                                        <div class="day-group mb-4 p-3 border rounded bg-white">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="text-primary m-0">Day <?= $day ?> Summary</h5>
                                                <div class="small text-muted">
                                                    <strong>Total:</strong> <?= $dayCal ?> kcal | P: <?= $dayProt ?>g | C:
                                                    <?= $dayCarbs ?>g | Na: <?= $daySod ?>mg
                                                </div>
                                            </div>

                                            <?php foreach ($dMeals as $meal): ?>
                                                <div class="card mb-2">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex justify-content-between">
                                                            <h6 class="card-title"><?= htmlspecialchars($meal['mealType']) ?></h6>
                                                            <span class="badge bg-secondary"><?= $meal['totalCalories'] ?> kcal</span>
                                                        </div>
                                                        <p class="card-text small mb-2">
                                                            <?php
                                                            $fstmt = $pdo->prepare("SELECT foodName FROM foods WHERE mealID = ?");
                                                            $fstmt->execute([$meal['mealID']]);
                                                            $foods = $fstmt->fetchAll(PDO::FETCH_COLUMN);
                                                            echo implode(', ', $foods);
                                                            ?>
                                                        </p>
                                                        <div class="mb-1">
                                                            <?php if ($meal['totalSodium'] > 400): ?>
                                                                <span class="badge bg-danger">High Sodium</span>
                                                            <?php endif; ?>
                                                            <?php if ($meal['totalCarbs'] > 60): ?>
                                                                <span class="badge bg-warning text-dark">High Carbs</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php
                                    $isFirstDay = false;
                                endforeach;
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>