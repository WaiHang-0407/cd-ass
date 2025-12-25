<?php
// pages/profile.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../classes/Profile.php';

requireRole(['User', 'Elderly', 'Dietitian']);

$currentUser = getCurrentUser();
$targetUserID = $currentUser->userID;
$isDietitian = ($currentUser->role === 'Dietitian');

// Dietitian Mode: Check for ID parameter
if ($isDietitian) {
    if (!isset($_GET['id'])) {
        header("Location: my-patients.php");
        exit;
    }
    $targetUserID = $_GET['id'];

    // Validate Assignment
    $check = $pdo->prepare("SELECT count(*) FROM elderly WHERE elderlyID = ? AND assignedDietitianID = ?");
    $check->execute([$targetUserID, $currentUser->userID]);
    if ($check->fetchColumn() == 0) {
        die("Unauthorized access to this patient profile.");
    }
}

// Load Target Profile
$profile = new Profile($pdo, $targetUserID);
$msg = '';

// Fetch User Data for Target
$stmtUser = $pdo->prepare("SELECT age, gender, name FROM users WHERE userID = ?");
$stmtUser->execute([$targetUserID]);
$userData = $stmtUser->fetch();
$targetName = $userData['name'] ?? 'User';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profile->setHeight((float) ($_POST['height'] ?? 0));
    $profile->setWeight((float) ($_POST['weight'] ?? 0));

    // Limits
    $profile->setCaloriesLimit((float) ($_POST['caloriesLimit'] ?? 0));
    $profile->setCarbsLimit((float) ($_POST['carbsLimit'] ?? 0));
    $profile->setSugarLimit((float) ($_POST['sugarLimit'] ?? 0));
    $profile->setSodiumLimit((float) ($_POST['sodiumLimit'] ?? 0));
    $profile->setFibreRequirement((float) ($_POST['fibreRequirement'] ?? 0));
    $profile->setSoftFoodRequirement(isset($_POST['softFoodRequirement']));

    // Arrays
    $profile->allergies = $_POST['allergies'] ?? [];
    $profile->healthCondition = $_POST['healthCondition'] ?? [];
    // $profile->medicationList = array_map('trim', explode(',', $_POST['medicationList'] ?? '')); // Old logic removed

    // Handle Detailed Medications
    $profile->clearMedications();
    if (isset($_POST['med_name']) && is_array($_POST['med_name'])) {
        $names = $_POST['med_name'];
        $dosages = $_POST['med_dosage'];
        $freqs = $_POST['med_freq'];

        for ($i = 0; $i < count($names); $i++) {
            if (!empty(trim($names[$i]))) {
                $d = trim($dosages[$i] ?? ''); // Allow string
                $f = (int) ($freqs[$i] ?? 1);
                if (!empty($d)) {
                    $profile->addMedicationDetailed(trim($names[$i]), $d, $f);
                }
            }
        }
    }

    // AUTO-CALCULATION LOGIC
    // 1. Fetch Age and Gender from Users table (already done above)

    if ($userData) {
        // 2. Calculate Limits
        $profile->calculateDietaryLimits($userData['age'], $userData['gender']);

        // 3. Override any manual input for these (since user asked for automatic)
        // If you wanted to respect manual overrides, you'd check if $_POST values are empty.
        // But "automatically calculated" usually implies the system decides.
        // We will prioritize the calculation, but maybe display them as set values.

        // Note: The setters above set values from POST. 
        // Calling calculateDietaryLimits NOW will overwrite them with calculated values.
        // effectively ignoring the manual input for limits. This matches the request.
    }

    if ($profile->save()) {
        $msg = "<div class='alert alert-success'>Profile updated successfully! BMI: " . number_format($profile->bmi, 1) . "</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Failed to save profile.</div>";
    }
}

// DEFINED LISTS
$availableAllergies = [
    'Peanuts',
    'Tree Nuts',
    'Milk (Lactose Intolerance)',
    'Eggs',
    'Fish',
    'Shellfish',
    'Soy',
    'Wheat (Gluten)'
];

$availableConditions = [
    'Diabetes',
    'Hypertension (High Blood Pressure)',
    'High Cholesterol',
    'Heart Disease',
    'Kidney Disease'
];

include '../includes/header.php';
?>
<script>
    // Pass PHP vars to JS
    const userAge = <?= $userData['age'] ?? 70 ?>;
    const userGender = "<?= $userData['gender'] ?? 'Female' ?>";

    function calculateLimits() {
        const height = parseFloat(document.querySelector('input[name="height"]').value) || 0;
        const weight = parseFloat(document.querySelector('input[name="weight"]').value) || 0;

        // Get selected conditions
        const conditionCheckboxes = document.querySelectorAll('input[name="healthCondition[]"]:checked');
        const conditions = Array.from(conditionCheckboxes).map(cb => cb.value.toLowerCase());

        if (height > 0 && weight > 0) {
            // BMI
            const h_m = height / 100;
            const bmi = weight / (h_m * h_m);
            // Update BMI alert if exists
            // (Optional: update DOM)

            // BMR
            let bmr = (10 * weight) + (6.25 * height) - (5 * userAge);
            if (userGender.toLowerCase() === 'male') bmr += 5; else bmr -= 161;

            let tdee = bmr * 1.2;
            let calories = Math.round(tdee);
            let carbs = Math.round((tdee * 0.50) / 4);
            let sugar = 30;
            let sodium = 2300;
            let fibre = 25;

            // Adjustments
            const hasDiabetes = conditions.includes('diabetes');
            const hasHypertension = conditions.some(c => c.includes('hypertension') || c.includes('high blood pressure'));
            const hasHeartDisease = conditions.includes('heart disease');
            const hasKidneyDisease = conditions.includes('kidney disease');
            const hasHighCholesterol = conditions.includes('high cholesterol');

            // -- Diabetes --
            if (hasDiabetes) {
                sugar = 20;
                carbs = Math.round((tdee * 0.45) / 4);
                fibre = 30;
            }

            // -- Hypertension / Heart Disease / Kidney Disease --
            if (hasHypertension || hasHeartDisease || hasKidneyDisease) {
                sodium = 1500;
            }

            // -- High Cholesterol --
            if (hasHighCholesterol) {
                fibre = 30; // High fibre matches diabetes reco too
                calories = Math.round(tdee * 0.95);
            }

            // -- Combined: Diabetes AND High Cholesterol --
            if (hasDiabetes && hasHighCholesterol) {
                fibre = 35;
            }

            // Set Values
            document.querySelector('input[name="caloriesLimit"]').value = calories;
            document.querySelector('input[name="carbsLimit"]').value = carbs;
            document.querySelector('input[name="sugarLimit"]').value = sugar;
            document.querySelector('input[name="sodiumLimit"]').value = sodium;
            document.querySelector('input[name="fibreRequirement"]').value = fibre;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const inputs = document.querySelectorAll('input[name="height"], input[name="weight"]');
        const checkboxes = document.querySelectorAll('input[name="healthCondition[]"]');

        inputs.forEach(input => input.addEventListener('input', calculateLimits));
        checkboxes.forEach(cb => cb.addEventListener('change', calculateLimits));

        // Also run on load to populate if values are already present
        calculateLimits();
    });
</script>

<div class="row">
    <div class="col-md-12">
        <?php if ($isDietitian): ?>
            <a href="my-patients.php" class="btn btn-outline-secondary mb-3">&larr; Back to Patient List</a>
            <h2>Health Profile: <span class="text-primary"><?= htmlspecialchars($targetName) ?></span></h2>
            <p class="text-muted">Viewing and editing patient health metrics.</p>
        <?php else: ?>
            <h2>Health Profile</h2>
            <p class="text-muted">Update your health metrics to get better diet recommendations.</p>
        <?php endif; ?>
        <?= $msg ?>
    </div>
</div>

<form method="post">
    <div class="row">
        <!-- Basic Metrics -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">Body Metrics</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label>Height (cm)</label>
                        <input type="number" step="0.1" name="height" class="form-control"
                            value="<?= $profile->height ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Weight (kg)</label>
                        <input type="number" step="0.1" name="weight" class="form-control"
                            value="<?= $profile->weight ?>" required>
                    </div>
                    <?php if ($profile->bmi): ?>
                        <div class="alert alert-secondary">Current BMI:
                            <strong><?= number_format($profile->bmi, 1) ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Medical Details -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-warning text-dark">Medical Details</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Allergies</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($availableAllergies as $allergy): ?>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="checkbox" name="allergies[]"
                                        value="<?= htmlspecialchars($allergy) ?>" id="allergy_<?= md5($allergy) ?>"
                                        <?= in_array($allergy, $profile->allergies) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="allergy_<?= md5($allergy) ?>">
                                        <?= htmlspecialchars($allergy) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Other Option -->
                        <!-- <div class="mt-2">
                            <input type="text" name="allergies_other" class="form-control form-control-sm" placeholder="Other allergies...">
                         </div> -->
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Health Conditions</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($availableConditions as $cond): ?>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="checkbox" name="healthCondition[]"
                                        value="<?= htmlspecialchars($cond) ?>" id="cond_<?= md5($cond) ?>"
                                        <?= in_array($cond, $profile->healthCondition) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="cond_<?= md5($cond) ?>">
                                        <?= htmlspecialchars($cond) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Medications</label>
                        <table class="table table-sm table-bordered" id="medTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Dosage (Pills/ml)</th>
                                    <th>Freq/Day</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="medTableBody">
                                <?php
                                $meds = $profile->getMedications();
                                if (empty($meds)) {
                                    // Add one empty row for UI guidance
                                    // $meds[] = ['name' => '', 'dosage' => 1, 'frequency' => 1];
                                }
                                foreach ($meds as $med):
                                    ?>
                                    <tr class="med-row">
                                        <td><input type="text" name="med_name[]" class="form-control form-control-sm"
                                                value="<?= htmlspecialchars($med['name']) ?>" placeholder="Med Name"
                                                required></td>
                                        <td><input type="text" name="med_dosage[]" class="form-control form-control-sm"
                                                value="<?= htmlspecialchars($med['dosage']) ?>" placeholder="1 or 5ml"></td>
                                        <td>
                                            <select name="med_freq[]" class="form-select form-select-sm">
                                                <?php for ($k = 1; $k <= 4; $k++): ?>
                                                    <option value="<?= $k ?>" <?= $med['frequency'] == $k ? 'selected' : '' ?>>
                                                        <?= $k ?>x
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </td>
                                        <td><button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="removeMedRow(this)">X</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addMedRow()">+ Add
                            Medication</button>

                        <script>
                            function addMedRow() {
                                const tbody = document.getElementById('medTableBody');
                                const tr = document.createElement('tr');
                                tr.className = 'med-row';
                                tr.innerHTML = `
                                    <td><input type="text" name="med_name[]" class="form-control form-control-sm" placeholder="Med Name" required></td>
                                    <td><input type="text" name="med_dosage[]" class="form-control form-control-sm" placeholder="1 or 5ml"></td>
                                    <td>
                                        <select name="med_freq[]" class="form-select form-select-sm">
                                            <option value="1">1x</option>
                                            <option value="2">2x</option>
                                            <option value="3">3x</option>
                                            <option value="4">4x</option>
                                        </select>
                                    </td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMedRow(this)">X</button></td>
                                `;
                                tbody.appendChild(tr);
                            }

                            function removeMedRow(btn) {
                                btn.closest('tr').remove();
                            }
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Dietary Limits -->
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header bg-success text-white">Dietary Requirements</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Daily Calories Limit (kcal)</label>
                            <input type="number" name="caloriesLimit" class="form-control"
                                value="<?= $profile->caloriesLimit ?>" readonly style="background-color: #e9ecef;">
                            <small class="text-muted">Auto-calculated</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Carbs Limit (g)</label>
                            <input type="number" name="carbsLimit" class="form-control"
                                value="<?= $profile->carbsLimit ?>" readonly style="background-color: #e9ecef;">
                            <small class="text-muted">Auto-calculated</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Sugar Limit (g)</label>
                            <input type="number" name="sugarLimit" class="form-control"
                                value="<?= $profile->sugarLimit ?>" readonly style="background-color: #e9ecef;">
                            <small class="text-muted">Auto-calculated</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Sodium Limit (mg)</label>
                            <input type="number" name="sodiumLimit" class="form-control"
                                value="<?= $profile->sodiumLimit ?>" readonly style="background-color: #e9ecef;">
                            <small class="text-muted">Auto-calculated</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Fibre Requirement (g)</label>
                            <input type="number" name="fibreRequirement" class="form-control"
                                value="<?= $profile->fibreRequirement ?>" readonly style="background-color: #e9ecef;">
                            <small class="text-muted">Auto-calculated</small>
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="softFoodRequirement" id="softFood"
                                    <?= $profile->softFoodRequirement ? 'checked' : '' ?>>
                                <label class="form-check-label" for="softFood">
                                    Require Soft Food?
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg mb-4">Save Profile</button>
</form>

<?php include '../includes/footer.php'; ?>