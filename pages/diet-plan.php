<?php
// pages/diet-plan.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../classes/GenerateDietPlan.php';

requireRole(['User', 'Elderly']);
$user = getCurrentUser();

$generator = new GenerateDietPlan($pdo);
$msg = '';

// Cancel Plan Action
// Cancel Plan Action
if (isset($_POST['cancel_plan'])) {
    $planID = $_POST['plan_id'];
    
    // Security Check: Ensure plan belongs to user
    $check = $pdo->prepare("SELECT elderlyID FROM diet_plans WHERE dietPlanID = ?");
    $check->execute([$planID]);
    $owner = $check->fetchColumn();
    
    if ($owner === $user->userID) {
        $stmt = $pdo->prepare("DELETE FROM diet_plans WHERE dietPlanID = ?");
        $stmt->execute([$planID]);
        $msg = "<div class='alert alert-warning'>Plan has been deleted. You can now generate a new one.</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Error: You cannot delete this plan.</div>";
    }
}

// Generate Action
if (isset($_POST['generate'])) {
    // Double Check Rule (Backend Enforcement)
    $stmt = $pdo->prepare("
        SELECT count(*) FROM diet_plans dp 
        JOIN diet_plan_approvals dpa ON dp.dietPlanID = dpa.dietPlanID
        WHERE dp.elderlyID = ? AND dpa.status IN ('Pending', 'Approved', 'Revise')
    ");
    $stmt->execute([$user->userID]);
    if ($stmt->fetchColumn() > 0) {
        $msg = "<div class='alert alert-danger'>You already have an active plan. Please cancel it first.</div>";
    } else {
        $cuisines = $_POST['cuisines'] ?? [];
        $duration = $_POST['duration'] ?? 1;
        $goal = $_POST['goal'] ?? 'maintain';
        $result = $generator->generate($user->userID, $cuisines, $duration, $goal);
        
        if (is_string($result) && strpos($result, 'Error:') === 0) {
            $msg = "<div class='alert alert-danger'><strong>Generation Failed</strong>: " . htmlspecialchars(substr($result, 7)) . "</div>";
        } else {
            $msg = "<div class='alert alert-success'>New Diet Plan Generated! Status: Pending Approval</div>";
        }
    }
}

// Fetch ALL plans
$stmt = $pdo->prepare("SELECT * FROM diet_plans WHERE elderlyID = ? ORDER BY createdAt DESC");
$stmt->execute([$user->userID]);
$allPlansData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$plans = [];
$activePlanID = null;
$activeStatus = '';

foreach ($allPlansData as $data) {
    $p = new DietPlan($pdo, $data);
    $plans[] = $p;
    
    // Check for Active Plan
    $status = $p->approval->getStatus();
    if (in_array($status, ['Pending', 'Approved', 'Revise']) && $activePlanID === null) {
        $activePlanID = $p->dietPlanID;
        $activeStatus = $status;
    }
}

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>My Diet Plans</h2>
            
            <?php if ($activePlanID): ?>
                <!-- Locked State -->
                <button class="btn btn-secondary" disabled>
                    <i class="bi bi-lock-fill"></i> New Plan Locked
                </button>
            <?php else: ?>
                <!-- Active button -->
                <button class="btn btn-success" type="button" data-bs-toggle="collapse" data-bs-target="#generateForm" aria-expanded="false">
                    Create New Plan
                </button>
            <?php endif; ?>
        </div>

        <?php if ($activePlanID): ?>
            <!-- Active Plan Warning & Cancel Option -->
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <div>
                    <strong>Active Plan Detected:</strong> You have a plan with status 
                    <span class="badge bg-primary"><?= $activeStatus ?></span>. 
                    You must complete or delete it to generate a new one.
                </div>
                <form method="post" onsubmit="return confirm('Are you sure you want to PERMANENTLY DELETE this plan? This cannot be undone.');">
                    <input type="hidden" name="plan_id" value="<?= $activePlanID ?>">
                    <button type="submit" name="cancel_plan" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i> Delete Active Plan
                    </button>
                </form>
            </div>
        <?php else: ?>
            <!-- Generation Form -->
            <div class="collapse mb-4" id="generateForm">
                <div class="card card-body bg-light">
                    <form method="post"
                        onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='Generating Plan... (Please wait ~1 min)';">
                        <input type="hidden" name="generate" value="true">
    
                        <div class="mb-3">
                        <label class="form-label">Duration</label>
                        <select name="duration" class="form-select">
                            <option value="1">1 Day</option>
                            <option value="7" selected>1 Week</option>
                            <option value="30">1 Month</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Diet Goal</label>
                        <select name="goal" class="form-select">
                            <option value="maintain" selected>Maintain Health</option>
                            <option value="lose">Lose Weight (-500 kcal)</option>
                            <option value="gain">Gain Weight (+500 kcal)</option>
                        </select>
                    </div>
                    <h5 class="mb-3">Select Preferred Cuisines (Optional)</h5>
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="cuisines[]"
                                        value="Chinese" id="c_chinese"><label class="form-check-label"
                                        for="c_chinese">Chinese</label></div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="cuisines[]"
                                        value="Malay" id="c_malay"><label class="form-check-label"
                                        for="c_malay">Malay</label></div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="cuisines[]"
                                        value="Indian" id="c_indian"><label class="form-check-label"
                                        for="c_indian">Indian</label></div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="cuisines[]"
                                        value="Western" id="c_western"><label class="form-check-label"
                                        for="c_western">Western</label></div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="cuisines[]"
                                        value="Japanese" id="c_japanese"><label class="form-check-label"
                                        for="c_japanese">Japanese</label></div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="cuisines[]"
                                        value="Mediterranean" id="c_med"><label class="form-check-label"
                                        for="c_med">Mediterranean</label></div>
                            </div>
                        </div>
                        <button type="submit" name="generate" class="btn btn-primary w-100">Generate New Plan</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?= $msg ?>
    </div>
</div>

<?php if (empty($plans)): ?>
    <div class="card text-center p-5">
        <h3>No Diet Plans Found</h3>
        <p>You haven't generated a diet plan yet. Click the button above to create one.</p>
    </div>
<?php else: ?>

    <div class="accordion" id="dietPlansAccordion">
        <?php foreach ($plans as $index => $plan):
            $collapseId = "collapse" . $plan->dietPlanID;
            $headerId = "heading" . $plan->dietPlanID;
            $isFirst = ($index === 0);
            ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="<?= $headerId ?>">
                    <button class="accordion-button <?= $isFirst ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse"
                        data-bs-target="#<?= $collapseId ?>" aria-expanded="<?= $isFirst ? 'true' : 'false' ?>"
                        aria-controls="<?= $collapseId ?>">
                        <strong><?= date('M d, Y', strtotime($plan->createdAt)) ?></strong>
                        &nbsp;-&nbsp;
                        <span class="badge bg-<?= $plan->approval->getStatus() == 'Approved' ? 'success' : 'warning' ?>">
                            <?= $plan->approval->getStatus() ?>
                        </span>
                    </button>
                </h2>
                <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $isFirst ? 'show' : '' ?>"
                    aria-labelledby="<?= $headerId ?>" data-bs-parent="#dietPlansAccordion">
                    <div class="accordion-body">

                        <div class="row">
                            <div class="col-md-12">
                                <?php if ($plan->approval->getStatus() == 'Pending'): ?>
                                    <div class="alert alert-info small mb-3">Waiting for Dietitian review.</div>
                                <?php endif; ?>

                                <h5 class="mb-3">Recommended Meals</h5>
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
                                    ksort($dailyMeals); // Ensure days are in order
                                    ?>

                                    <!-- Added Tabs for Days -->
                                    <ul class="nav nav-tabs mb-3" id="plan-<?= $plan->dietPlanID ?>-tabs" role="tablist">
                                        <?php 
                                        $isFirstDay = true;
                                        foreach (array_keys($dailyMeals) as $dayNum):
                                            $tabId = "tab-p{$plan->dietPlanID}-d{$dayNum}";
                                            $paneId = "pane-p{$plan->dietPlanID}-d{$dayNum}";
                                        ?>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link <?= $isFirstDay ? 'active' : '' ?>" 
                                                        id="<?= $tabId ?>" 
                                                        data-bs-toggle="tab" 
                                                        data-bs-target="#<?= $paneId ?>" 
                                                        type="button" 
                                                        role="tab" 
                                                        aria-controls="<?= $paneId ?>" 
                                                        aria-selected="<?= $isFirstDay ? 'true' : 'false' ?>">
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

                                            // Calculate Daily Totals
                                            $dayCal = 0; $dayProt = 0; $dayCarbs = 0; $daySod = 0;
                                            foreach($dMeals as $m) {
                                                $dayCal += $m['totalCalories'];
                                                $dayProt += $m['totalProtein'];
                                                $dayCarbs += $m['totalCarbs'];
                                                $daySod += $m['totalSodium'];
                                            }
                                        ?>
                                            <div class="tab-pane fade <?= $isFirstDay ? 'show active' : '' ?>" 
                                                 id="<?= $paneId ?>" 
                                                 role="tabpanel" 
                                                 aria-labelledby="<?= $tabId ?>">
                                                
                                                <div class="day-group mb-4 p-3 border rounded bg-white">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <h5 class="text-primary m-0">Day <?= $day ?> Summary</h5>
                                                        <div class="small text-muted">
                                                            <strong>Total:</strong> <?= $dayCal ?> kcal | P: <?= $dayProt ?>g | C: <?= $dayCarbs ?>g | Na: <?= $daySod ?>mg
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
                                                                    $fstmt = $pdo->prepare("SELECT foodName, recipeID FROM foods WHERE mealID = ?");
                                                                    $fstmt->execute([$meal['mealID']]);
                                                                    $foodRows = $fstmt->fetchAll();
                                                                    
                                                                    $foodNames = array_column($foodRows, 'foodName');
                                                                    echo implode(', ', $foodNames);
                                                                    
                                                                    // Get First Valid RecipeID
                                                                    $recipeID = null;
                                                                    foreach($foodRows as $fr) {
                                                                        if (!empty($fr['recipeID'])) {
                                                                            $recipeID = $fr['recipeID'];
                                                                            break;
                                                                        }
                                                                    }
                                                                    ?>
                                                                </p>
                                                                <?php if($recipeID): ?>
                                                                    <div class="mb-2">
                                                                        <a href="recipe.php?id=<?= $recipeID ?>" 
                                                                           class="btn btn-sm btn-outline-info"
                                                                           onclick="this.innerHTML='<span class=\'spinner-border spinner-border-sm\'></span> Preparing Recipe...'; this.classList.add('disabled');">
                                                                            <i class="bi bi-book"></i> View Recipe & Shop
                                                                        </a>
                                                                    </div>
                                                                <?php endif; ?>
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
                                    <?php
                                endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php endif; ?>

<!-- Script moved to accessibility.js included in footer -->

<?php include '../includes/footer.php'; ?>