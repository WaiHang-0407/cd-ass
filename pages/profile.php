<?php
// pages/profile.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../classes/Profile.php';

requireRole(['User', 'Elderly']); // Only elderly users can edit their own profile for now

$user = getCurrentUser();
$profile = new Profile($pdo, $user->userID);
$msg = '';

// Fetch User Data global for both POST and GET logic
$stmtUser = $pdo->prepare("SELECT age, gender FROM users WHERE userID = ?");
$stmtUser->execute([$user->userID]);
$userData = $stmtUser->fetch();


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

    // Arrays (Simple text area parsing for prototype)
    $profile->allergies = array_map('trim', explode(',', $_POST['allergies'] ?? ''));
    $profile->healthCondition = array_map('trim', explode(',', $_POST['healthCondition'] ?? ''));
    $profile->medicationList = array_map('trim', explode(',', $_POST['medicationList'] ?? ''));

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

include '../includes/header.php';
?>
<script>
    // Pass PHP vars to JS
    const userAge = <?= $userData['age'] ?? 70 ?>;
    const userGender = "<?= $userData['gender'] ?? 'Female' ?>";

    function calculateLimits() {
        const height = parseFloat(document.querySelector('input[name="height"]').value) || 0;
        const weight = parseFloat(document.querySelector('input[name="weight"]').value) || 0;
        const conditions = document.querySelector('textarea[name="healthCondition"]').value.toLowerCase();

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
            if (conditions.includes('diabetes')) {
                sugar = 20;
                carbs = Math.round((tdee * 0.45) / 4);
            }
            if (conditions.includes('hypertension') || conditions.includes('high blood pressure')) {
                sodium = 1500;
            }
            if (conditions.includes('high cholesterol')) {
                calories = Math.round(tdee * 0.95);
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
        const inputs = document.querySelectorAll('input[name="height"], input[name="weight"], textarea[name="healthCondition"]');
        inputs.forEach(input => input.addEventListener('input', calculateLimits));
        // Also run on load to populate if values are already present
        calculateLimits();
    });
</script>

<div class="row">
    <div class="col-md-12">
        <h2>Health Profile</h2>
        <p class="text-muted">Update your health metrics to get better diet recommendations.</p>
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
                        <label>Allergies (comma separated)</label>
                        <textarea name="allergies"
                            class="form-control"><?= implode(', ', $profile->allergies) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Health Conditions (comma separated)</label>
                        <textarea name="healthCondition"
                            class="form-control"><?= implode(', ', $profile->healthCondition) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Medications (comma separated)</label>
                        <textarea name="medicationList"
                            class="form-control"><?= implode(', ', $profile->medicationList) ?></textarea>
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