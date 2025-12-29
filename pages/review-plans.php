<?php
// pages/review-plans.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../classes/DietPlan.php';
require_once '../classes/Profile.php'; // Fix Fatal Error

requireRole(['Dietitian', 'Admin']);
$user = getCurrentUser();

// Handle Actions
if (isset($_POST['action'])) {
    
    if ($_POST['action'] == 'update_plan_details') {
        $mealsData = $_POST['meals']; // array [mealID => [calories, protein, ..., food_text]]
        foreach ($mealsData as $mID => $mData) {
             // 1. Update Meal Macros
             $upd = $pdo->prepare("UPDATE meals SET totalCalories=?, totalProtein=?, totalCarbs=?, totalSodium=? WHERE mealID=?");
             $upd->execute([$mData['calories'], $mData['protein'], $mData['carbs'], $mData['sodium'], $mID]);

             // 2. Update Foods (Simplification: Delete all, add one text entry)
             // Preserve any existing recipe association when the review form doesn't include it.
             $rstmt = $pdo->prepare("SELECT recipeID FROM foods WHERE mealID = ? LIMIT 1");
             $rstmt->execute([$mID]);
             $existingRecipeID = $rstmt->fetchColumn();

             $pdo->prepare("DELETE FROM foods WHERE mealID=?")->execute([$mID]);

            // Use recipeID from submitted data if present, otherwise fall back to existing one
            $recipeToUse = $mData['recipeID'] ?? $existingRecipeID;
            if (!empty($recipeToUse)) {
                $ins = $pdo->prepare("INSERT INTO foods (foodID, mealID, recipeID, foodName) VALUES (?, ?, ?, ?)");
                $ins->execute([uniqid('F_'), $mID, $recipeToUse, $mData['food_text']]);
            } else {
                $ins = $pdo->prepare("INSERT INTO foods (foodID, mealID, foodName) VALUES (?, ?, ?)");
                $ins->execute([uniqid('F_'), $mID, $mData['food_text']]);
            }
        }
        $successMsg = "Plan details updated successfully.";
    } else {
        $planID = $_POST['dietPlanID'];
        // We instantiate DietPlanApproval directly for the action
        $approval = new DietPlanApproval($pdo, $planID, 'Pending'); // State will update
    
        if ($_POST['action'] == 'approve') {
            $approval->approve($user->userID);
        } elseif ($_POST['action'] == 'revise') {
            $approval->revise($user->userID);
        }
    }
}

$filter = $_GET['filter'] ?? 'All';
$reqID = $_GET['id'] ?? null;

$sql = "
    SELECT dp.*, u.name as elderlyName, dpa.status
    FROM diet_plans dp 
    JOIN diet_plan_approvals dpa ON dp.dietPlanID = dpa.dietPlanID 
    JOIN users u ON dp.elderlyID = u.userID
    JOIN elderly e ON u.userID = e.elderlyID
    WHERE 1=1
";

$params = [];

// Admin sees all (or specific ID), Dietitian sees assigned
if ($user->role !== 'Admin') {
    $sql .= " AND e.assignedDietitianID = ?";
    $params[] = $user->userID;
}

if ($reqID) {
    $sql .= " AND dp.dietPlanID = ?";
    $params[] = $reqID;
}

if ($filter !== 'All') {
    $sql .= " AND dpa.status = ?";
    $params[] = $filter;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$plans = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
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
                    $statusIcon = 'bi-circle';
                    if ($row['status'] == 'Approved') {
                        $badgeClass = 'bg-success';
                        $statusIcon = 'bi-check-circle-fill';
                    }
                    if ($row['status'] == 'Pending') {
                        $badgeClass = 'bg-warning text-dark';
                        $statusIcon = 'bi-hourglass-split';
                    }
                    if ($row['status'] == 'Revise') {
                        $badgeClass = 'bg-danger';
                        $statusIcon = 'bi-exclamation-circle-fill';
                    }
                    ?>
                    <div class="accordion-item shadow-sm mb-3 border-0 rounded overflow-hidden">
                        <h2 class="accordion-header" id="<?= $headerId ?>">
                            <button class="accordion-button <?= $isFirst ? '' : 'collapsed' ?> bg-white" type="button" data-bs-toggle="collapse"
                                data-bs-target="#<?= $collapseId ?>" aria-expanded="<?= $isFirst ? 'true' : 'false' ?>"
                                aria-controls="<?= $collapseId ?>">
                                <div class="d-flex w-100 justify-content-between align-items-center me-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-circle bg-primary text-white d-flex justify-content-center align-items-center fw-bold" style="width: 40px; height: 40px; border-radius: 50%;">
                                            <?= substr($row['elderlyName'], 0, 1) ?>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 text-dark"><?= htmlspecialchars($row['elderlyName']) ?></h5>
                                            <small class="text-muted"><i class="bi bi-calendar"></i> <?= date('M d, Y', strtotime($plan->createdAt)) ?></small>
                                        </div>
                                    </div>
                                    <span class="badge <?= $badgeClass ?> rounded-pill px-3 py-2">
                                        <i class="bi <?= $statusIcon ?>"></i> <?= $row['status'] ?>
                                    </span>
                                </div>
                            </button>
                        </h2>
                        <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $isFirst ? 'show' : '' ?>"
                            aria-labelledby="<?= $headerId ?>" data-bs-parent="#reviewPlansAccordion">
                            <div class="accordion-body bg-light">

                                <div class="row">
                                    <!-- Left Col: Patient Stats -->
                            <div class="col-lg-4 mb-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white border-bottom-0 pt-3">
                                        <h6 class="fw-bold text-uppercase text-secondary small mb-0"><i class="bi bi-person-vcard"></i> Patient Profile</h6>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        $profile = new Profile($pdo, $row['elderlyID']);
                                        $conditions = implode(', ', $profile->healthCondition);
                                        $allergies = implode(', ', $profile->allergies);
                                        
                                        // Fetch Caretaker
                                        $ctStmt = $pdo->prepare("SELECT caretakerID FROM user_links WHERE patientID = ? LIMIT 1");
                                        $ctStmt->execute([$row['elderlyID']]);
                                        $caretakerID = $ctStmt->fetchColumn();
                                        ?>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Age/Gender:</span>
                                            <span class="fw-bold"><?= $profile->age ?>y / <?= $profile->gender ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>BMI:</span>
                                            <span class="fw-bold <?= ($profile->bmi > 25) ? 'text-warning' : 'text-success' ?>"><?= number_format($profile->bmi, 1) ?></span>
                                        </div>
                                        <div class="mb-3">
                                            <span class="d-block small text-muted">Conditions:</span>
                                            <div class="fw-bold text-dark"><?= $conditions ?: 'None' ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <span class="d-block small text-muted">Allergies:</span>
                                            <div class="fw-bold text-danger"><?= $allergies ?: 'None' ?></div>
                                        </div>
                                        <hr class="my-2">
                                        <h6 class="small fw-bold text-secondary">Dietary Limits</h6>
                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="badge bg-light text-dark border">Cal: <?= $profile->caloriesLimit ?></span>
                                            <span class="badge bg-light text-dark border">Na: <?= $profile->sodiumLimit ?></span>
                                            <span class="badge bg-light text-dark border">Carb: <?= $profile->carbsLimit ?></span>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-0">
                                         <!-- Actions -->
                                        <div class="d-grid gap-2">
                                            <?php if ($caretakerID): ?>
                                                <a href="messages.php?chat_with=<?= $caretakerID ?>" class="btn btn-outline-primary shadow-sm">
                                                    <i class="bi bi-chat-text"></i> Message Caretaker
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($row['status'] != 'Approved'): ?>
                                                <form method="post">
                                                    <input type="hidden" name="dietPlanID" value="<?= $plan->dietPlanID ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-success w-100 shadow-sm">
                                                        <i class="bi bi-check-lg"></i> Approve Plan
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="post">
                                                <input type="hidden" name="dietPlanID" value="<?= $plan->dietPlanID ?>">
                                                <input type="hidden" name="action" value="revise">
                                                <button type="submit" class="btn btn-outline-danger w-100">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Request Revision
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                                    <!-- Right Col: Plan Details -->
                            <div class="col-lg-8">
                                <form method="post" class="h-100">
                                    <input type="hidden" name="action" value="update_plan_details">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white border-bottom-0 d-flex justify-content-between pt-3">
                                        <h6 class="fw-bold text-uppercase text-secondary small mb-0"><i class="bi bi-calendar-week"></i> Weekly Plan (Editable)</h6>
                                        <button type="submit" class="btn btn-primary btn-sm px-3"><i class="bi bi-save"></i> Save Changes</button>
                                    </div>
                                    <div class="card-body p-0">
                                        <?php
                                        $meals = $plan->getMeals();
                                        if (empty($meals)):
                                            echo "<div class='p-3 text-center text-muted'>No meals generated.</div>";
                                        else:
                                            $dailyMeals = [];
                                            foreach ($meals as $meal) {
                                                $day = $meal['day'] ?? 1;
                                                $dailyMeals[$day][] = $meal;
                                            }
                                            ksort($dailyMeals);
                                            ?>

                                            <ul class="nav nav-tabs nav-fill px-3" role="tablist">
                                                <?php
                                                $isFirstDay = true;
                                                foreach (array_keys($dailyMeals) as $dayNum):
                                                    $tabId = "tab-p{$plan->dietPlanID}-d{$dayNum}";
                                                    $paneId = "pane-p{$plan->dietPlanID}-d{$dayNum}";
                                                    ?>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link <?= $isFirstDay ? 'active fw-bold' : '' ?>" id="<?= $tabId ?>"
                                                            data-bs-toggle="tab" data-bs-target="#<?= $paneId ?>" type="button" role="tab">
                                                            Day <?= $dayNum ?>
                                                        </button>
                                                    </li>
                                                    <?php
                                                    $isFirstDay = false;
                                                endforeach;
                                                ?>
                                            </ul>

                                            <div class="tab-content p-3 bg-white rounded-bottom">
                                                <?php
                                                $isFirstDay = true;
                                                foreach ($dailyMeals as $day => $dMeals):
                                                    $paneId = "pane-p{$plan->dietPlanID}-d{$day}";
                                                    $tabId = "tab-p{$plan->dietPlanID}-d{$day}";
                                                    ?>
                                                    <div class="tab-pane fade <?= $isFirstDay ? 'show active' : '' ?>" id="<?= $paneId ?>" role="tabpanel">
                                                        
                                                        <div class="list-group list-group-flush">
                                                            <?php foreach ($dMeals as $meal): 
                                                                $icon = 'bi-cup-hot'; 
                                                                if (stripos($meal['mealType'], 'Lunch') !== false) $icon = 'bi-sun';
                                                                if (stripos($meal['mealType'], 'Dinner') !== false) $icon = 'bi-moon-stars';
                                                                if (stripos($meal['mealType'], 'Snack') !== false) $icon = 'bi-cup-straw';
                                                                
                                                                $fstmt = $pdo->prepare("SELECT foodName FROM foods WHERE mealID = ?");
                                                                $fstmt->execute([$meal['mealID']]);
                                                                $foods = $fstmt->fetchAll(PDO::FETCH_COLUMN);
                                                                $foodText = implode(', ', $foods);
                                                            ?>
                                                                <div class="list-group-item px-0 border-bottom-0 pb-3">
                                                                    <div class="d-flex align-items-start">
                                                                        <div class="me-3 mt-1 text-center" style="width: 50px;">
                                                                            <span class="badge bg-light text-dark border p-2 rounded-circle mb-1">
                                                                                <i class="bi <?= $icon ?> fs-5"></i>
                                                                            </span>
                                                                            <small class="d-block fw-bold" style="font-size: 0.7rem;"><?= htmlspecialchars($meal['mealType']) ?></small>
                                                                        </div>
                                                                        <div class="flex-grow-1">
                                                                            <!-- Macros Inputs Row -->
                                                                            <div class="row g-2 mb-2">
                                                                                 <div class="col-md-3">
                                                                                     <label class="small text-muted" style="font-size: 0.7rem;">Calories</label>
                                                                                     <input type="number" name="meals[<?= $meal['mealID'] ?>][calories]" value="<?= $meal['totalCalories'] ?>" class="form-control form-control-sm">
                                                                                 </div>
                                                                                 <div class="col-md-3">
                                                                                     <label class="small text-muted" style="font-size: 0.7rem;">Protein (g)</label>
                                                                                     <input type="number" name="meals[<?= $meal['mealID'] ?>][protein]" value="<?= $meal['totalProtein'] ?>" class="form-control form-control-sm">
                                                                                 </div>
                                                                                 <div class="col-md-3">
                                                                                     <label class="small text-muted" style="font-size: 0.7rem;">Carbs (g)</label>
                                                                                     <input type="number" name="meals[<?= $meal['mealID'] ?>][carbs]" value="<?= $meal['totalCarbs'] ?>" class="form-control form-control-sm">
                                                                                 </div>
                                                                                 <div class="col-md-3">
                                                                                     <label class="small text-muted" style="font-size: 0.7rem;">Sodium (mg)</label>
                                                                                     <input type="number" name="meals[<?= $meal['mealID'] ?>][sodium]" value="<?= $meal['totalSodium'] ?>" class="form-control form-control-sm">
                                                                                 </div>
                                                                            </div>

                                                                            <!-- Food Description Input -->
                                                                            <label class="small text-muted" style="font-size: 0.7rem;">Foods</label>
                                                                            <textarea name="meals[<?= $meal['mealID'] ?>][food_text]" class="form-control form-control-sm" rows="2" placeholder="e.g. Apple, Bread"><?= htmlspecialchars($foodText) ?></textarea>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                    <?php $isFirstDay = false; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                </form>
                            </div>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>