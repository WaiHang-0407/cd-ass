<?php
// pages/food-log.php
require_once '../includes/auth.php';
require_once '../includes/config.php'; // Fix Undefined Constant AI_MODEL
require_once '../includes/db.php';
require_once '../classes/Progress.php';

requireRole(['User', 'Elderly']);
$user = getCurrentUser();
$progress = new Progress($pdo, $user->userID);
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_entry'])) {
    $foodName = trim($_POST['food_name'] ?? 'Logged Item');
    $cals = (float) $_POST['calories'];
    $protein = (float) $_POST['protein'];
    $water = (float) $_POST['water'];

    $fibre = (float) ($_POST['fibre'] ?? 0);
    $carbs = (float) ($_POST['carbs'] ?? 0);
    $sodium = (float) ($_POST['sodium'] ?? 0);
    $sugar = (float) ($_POST['sugar'] ?? 0);
    $sugar = (float) ($_POST['sugar'] ?? 0);

    $progress->addIntake($cals, $protein, $water, $fibre, $carbs, $sodium, $sugar, $foodName);
    $msg = "<div class='alert alert-success'>Entry Logged! Current Status: <strong>" . $progress->state->displayStatus() . "</strong></div>";
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-6">
        <h2>Daily Food Log</h2>
        <p>Date: <?= date('Y-m-d') ?></p>
        <?= $msg ?>

        <div class="card mb-4 border-<?= $progress->getStatusColor() ?>">
            <div class="card-header bg-<?= $progress->getStatusColor() ?> text-white">
                Daily Status: <?= $progress->state->displayStatus() ?>
            </div>
            <div class="card-body">
                <h5 class="card-title"><?= $progress->state->getAdvice() ?></h5>
                <hr>
                <div class="row text-center">
                    <div class="col-4">
                        <h3><?= $progress->caloriesTaken ?></h3>
                        <small>Kcal</small>
                    </div>
                    <div class="col-4">
                        <h3><?= $progress->proteinTaken ?></h3>
                        <small>Protein (g)</small>
                    </div>
                    <div class="col-4">
                        <h3><?= $progress->waterIntake ?></h3>
                        <small>Water (L)</small>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $autoCals = 0;
        $autoProt = 0;
        $autoCarbs = 0;
        $autoFibre = 0;
        $autoFat = 0;
        $autoSugar = 0;
        $autoSodium = 0;
        $autoMsg = '';

        // Handle Image Recognition
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['analyze'])) {
            $imageData = null;
            $mimeType = 'image/jpeg'; // Default for canvas export
        
            // 1. Check for Compressed Image (Client-Side)
            if (!empty($_POST['compressed_image'])) {
                // Remove header "data:image/jpeg;base64,"
                $parts = explode(',', $_POST['compressed_image']);
                $imageData = $parts[1] ?? $parts[0];
            }
            // 2. Fallback to direct upload (if JS failed or small file)
            elseif (isset($_FILES['food_image']) && $_FILES['food_image']['error'] === 0) {
                $imageData = base64_encode(file_get_contents($_FILES['food_image']['tmp_name']));
                $mimeType = $_FILES['food_image']['type'];
            }

            if ($imageData) {
                // Send to AI
                $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/" . AI_MODEL . ":generateContent?key=" . AI_API_KEY;

                set_time_limit(90); // Allow extra time for Vision AI
        
                $payload = [
                    "contents" => [
                        [
                            "parts" => [
                                ["text" => "Analyze this food image. Identify the items. Estimate total Calories (kcal), Protein (g), Carbs (g), Fibre (g), Sugar (g), and Sodium (mg). Return strictly JSON format: {\"description\": \"Food Name\", \"calories\": 500, \"protein\": 30, \"carbs\": 50, \"fibre\": 10, \"sugar\": 5, \"sodium\": 200}. Do not use Markdown."],
                                [
                                    "inlineData" => [
                                        "mimeType" => $mimeType,
                                        "data" => $imageData
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];

                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 80); // Prevent hang before PHP timeout
        
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                curl_close($ch);

                if ($curlErr) {
                    $autoMsg = "<div class='alert alert-danger'><strong>Connection Error:</strong> $curlErr</div>";
                } elseif ($httpCode !== 200) {
                    $autoMsg = "<div class='alert alert-danger'><strong>API Error ($httpCode):</strong> " . htmlspecialchars(substr($response, 0, 200)) . "</div>";
                } else {
                    $result = json_decode($response, true);
                    $aiText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

                    // Clean JSON
                    $aiText = preg_replace('/```json|```/', '', $aiText);
                    $data = json_decode($aiText, true);

                    if ($data) {
                        $autoCals = $data['calories'] ?? 0;
                        $autoProt = $data['protein'] ?? 0;
                        $autoCarbs = $data['carbs'] ?? 0;
                        $autoFibre = $data['fibre'] ?? $data['fiber'] ?? 0;
                        $autoFat = $data['fat'] ?? 0;
                        $autoSugar = $data['sugar'] ?? 0;
                        $autoSodium = $data['sodium'] ?? 0;

                        $desc = $data['description'] ?? 'Identified Food';
                        $autoMsg = "<div class='alert alert-info'><strong>AI Identified:</strong> $desc<br>Values auto-filled. Please verify before logging.</div>";
                    } else {
                        // Better error parsing if JSON decode failed but response was 200
                        $errorDetail = $result['error']['message'] ?? 'Invalid AI Response Format';
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $errorDetail .= " (JSON: " . json_last_error_msg() . ")";
                        }
                        $autoMsg = "<div class='alert alert-danger'><strong>AI Error:</strong> " . htmlspecialchars($errorDetail) . "</div>";
                    }
                }
            } else {
                // Error Handling
                if (isset($_FILES['food_image']['error']) && $_FILES['food_image']['error'] !== 0) {
                    $err = $_FILES['food_image']['error'];
                    $autoMsg = "<div class='alert alert-danger'><strong>Upload Failed:</strong> Error Code $err. (1=Size Limit Exceeded). Attempting compression next time...</div>";
                } elseif (empty($_POST['compressed_image'])) {
                    $autoMsg = "<div class='alert alert-warning'>No image data received. Please try again.</div>";
                }
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
            $maxSize = ini_get('post_max_size');
            $autoMsg = "<div class='alert alert-danger'><strong>Upload Failed:</strong> File too large! Server Limit is $maxSize. Try a smaller photo.</div>";
        }
        ?>

        <?= $autoMsg ?>

        <form method="post" enctype="multipart/form-data" class="card p-4 shadow-sm" id="foodLogForm">
            <h4 class="mb-3">Add Entry</h4>

            <!-- Snap & Log Section -->
            <div class="mb-3 border p-2 rounded bg-light">
                <label class="form-label fw-bold"><i class="bi bi-camera-fill"></i> Snap & Log (AI)</label>
                <div class="input-group">
                    <input type="file" name="food_image" id="food_image" class="form-control" accept="image/*"
                        capture="environment">
                    <!-- Hidden input to store compressed base64 image -->
                    <input type="hidden" name="compressed_image" id="compressed_image">
                    <button type="button" onclick="handleAnalyze()" class="btn btn-outline-primary">Analyze</button>
                    <!-- Actual submit button hidden, triggered by JS -->
                    <button type="submit" name="analyze" id="btn_analyze_submit" style="display:none;"
                        formnovalidate></button>
                </div>
                <small class="text-muted" id="compression_status">Upload a photo to auto-fill calories.</small>
            </div>
            <script>
                // Client-side Compression Script
                const fileInput = document.getElementById('food_image');
                const compressedInput = document.getElementById('compressed_image');
                const statusText = document.getElementById('compression_status');

                fileInput.addEventListener('change', function (e) {
                    if (fileInput.files.length === 0) return;

                    const file = fileInput.files[0];
                    statusText.innerText = "Compressing image...";

                    const reader = new FileReader();
                    reader.readAsDataURL(file);

                    reader.onload = function (event) {
                        const img = new Image();
                        img.src = event.target.result;

                        img.onload = function () {
                            const canvas = document.createElement('canvas');
                            const ctx = canvas.getContext('2d');

                            // Resize logic (Max 800px)
                            const MAX_WIDTH = 800;
                            const MAX_HEIGHT = 800;
                            let width = img.width;
                            let height = img.height;

                            if (width > height) {
                                if (width > MAX_WIDTH) {
                                    height *= MAX_WIDTH / width;
                                    width = MAX_WIDTH;
                                }
                            } else {
                                if (height > MAX_HEIGHT) {
                                    width *= MAX_HEIGHT / height;
                                    height = MAX_HEIGHT;
                                }
                            }

                            canvas.width = width;
                            canvas.height = height;
                            ctx.drawImage(img, 0, 0, width, height);

                            // Compress to JPEG 0.7 quality
                            const dataUrl = canvas.toDataURL('image/jpeg', 0.7);
                            compressedInput.value = dataUrl;

                            statusText.innerText = "Image Ready (" + Math.round(dataUrl.length / 1024) + "KB). Click Analyze.";
                            console.log("Image compressed");
                        }
                    }
                });

                function handleAnalyze() {
                    if (!compressedInput.value && (!fileInput.files || fileInput.files.length === 0)) {
                        alert("Please select an image first.");
                        return;
                    }

                    // IF we have a compressed image, we should strip the original file input 
                    // from the form submission to avoid 'post_max_size' error.
                    // However, we can't easily remove it without removing the element.
                    // Strategy: We rely on the compressed_image hidden field.
                    // If the file is huge, PHP post_max_size might still kill it.
                    // SO: We MUST clear the file input value if we have the compressed version.

                    if (compressedInput.value) {
                        fileInput.value = ''; // Clear the massive file
                    }

                    document.getElementById('btn_analyze_submit').click();
                }
            </script>
            <hr>

            <div class="row">
                <div class="col-12 mb-3">
                    <label>Food Name / Description</label>
                    <input type="text" name="food_name" class="form-control"
                        placeholder="e.g. Apple, Chicken Rice, etc." value="<?= htmlspecialchars($desc ?? '') ?>"
                        required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Calories (kcal)</label>
                    <input type="number" name="calories" class="form-control" value="<?= $autoCals ?: 0 ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Protein (g)</label>
                    <input type="number" name="protein" class="form-control" value="<?= $autoProt ?: 0 ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Carbs (g)</label>
                    <input type="number" name="carbs" class="form-control" value="<?= $autoCarbs ?: 0 ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Fibre (g)</label>
                    <input type="number" name="fibre" class="form-control" value="<?= $autoFibre ?: 0 ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Sugar (g)</label>
                    <input type="number" name="sugar" class="form-control" value="<?= $autoSugar ?: 0 ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Sodium (mg)</label>
                    <input type="number" name="sodium" class="form-control" value="<?= $autoSodium ?: 0 ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="fw-bold text-primary">Water Intake (L)</label>
                <input type="number" step="0.1" name="water" class="form-control" value="0">
            </div>

            <button type="submit" name="log_entry" class="btn btn-primary w-100">Log Entry</button>
        </form>
    </div>

    <div class="col-md-6">
        <h3><i class="bi bi-calendar-check text-primary"></i> Today's Plan</h3>
        <?php
        require_once '../classes/DietPlan.php';

        // Fetch Active Approved Plan
        $activePlan = null;
        $dayNum = 1;

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
            $activePlan = new DietPlan($pdo, $planData);
            $start = new DateTime($activePlan->createdAt);
            $now = new DateTime();
            $diff = $now->diff($start)->days;
            $duration = $activePlan->duration ?? 7;
            $dayNum = ($diff % $duration) + 1;

            // Fetch meals for this Day
            $mstmt = $pdo->prepare("SELECT * FROM meals WHERE dietPlanID = ? AND day = ? ORDER BY mealID ASC");
            $mstmt->execute([$activePlan->dietPlanID, $dayNum]);
            $todayMeals = $mstmt->fetchAll();
        } else {
            $todayMeals = [];
        }
        ?>

        <?php if (!empty($todayMeals)): ?>
            <p class="text-muted">Day <?= $dayNum ?> Reccomendations</p>
            <div class="list-group mb-4">
                <?php foreach ($todayMeals as $meal):
                    $fstmt = $pdo->prepare("SELECT foodName FROM foods WHERE mealID = ?");
                    $fstmt->execute([$meal['mealID']]);
                    $foods = $fstmt->fetchAll(PDO::FETCH_COLUMN);
                    $foodDesc = implode(', ', $foods);
                    ?>
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= htmlspecialchars($meal['mealType']) ?></strong>
                            <br>
                            <span class="small text-muted"><?= htmlspecialchars($foodDesc) ?></span>
                            <br>
                            <span class="badge bg-secondary"><?= $meal['totalCalories'] ?> kcal</span>
                            <span class="badge bg-info text-dark"><?= $meal['totalProtein'] ?>g Prot</span>
                            <span class="badge bg-light text-dark border"><?= $meal['totalFibre'] ?>g Fib</span>
                            <span class="badge bg-warning text-dark"><?= $meal['totalSugar'] ?? 0 ?>g Sug</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            onclick="fillQuickLog(<?= $meal['totalCalories'] ?>, <?= $meal['totalProtein'] ?>, <?= $meal['totalCarbs'] ?>, <?= $meal['totalFibre'] ?>, <?= $meal['totalSodium'] ?>, <?= $meal['totalSugar'] ?? 0 ?>, '<?= htmlspecialchars(addslashes($meal['mealType'])) ?>')">
                            <i class="bi bi-arrow-left-circle"></i> Use This
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <script>
                function fillQuickLog(cals, prot, carbs, fibre, sodium, sugar, name) {
                    document.querySelector('input[name=\'food_name\']').value = name;
                    document.querySelector('input[name=\'calories\']').value = cals;
                    document.querySelector('input[name=\'protein\']').value = prot;
                    document.querySelector('input[name=\'carbs\']').value = carbs;
                    document.querySelector('input[name=\'fibre\']').value = fibre;
                    document.querySelector('input[name=\'sodium\']').value = sodium;
                    document.querySelector('input[name=\'sugar\']').value = sugar;

                    document.querySelector('#foodLogForm').scrollIntoView({ behavior: 'smooth' });
                }
            </script>
        <?php else: ?>
            <div class="alert alert-light border">
                No active diet plan found. <a href="diet-plan.php">Create one?</a>
            </div>
        <?php endif; ?>

        <h3><i class="bi bi-clock-history"></i> Recent Logs</h3>
        <?php
        // Fetch recent 20 logs
        $lstmt = $pdo->prepare("SELECT * FROM food_logs WHERE elderlyID = ? ORDER BY loggedAt DESC LIMIT 20");
        $lstmt->execute([$user->userID]);
        $logs = $lstmt->fetchAll();
        ?>

        <?php if (empty($logs)): ?>
            <div class="alert alert-secondary">No food history found. Start logging!</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Item</th>
                            <th>Kcal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= date('H:i, M d', strtotime($log['loggedAt'])) ?></td>
                                <td><?= htmlspecialchars($log['foodName']) ?></td>
                                <td><?= $log['calories'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        </ul>
    </div>
</div>

<?php include '../includes/footer.php'; ?>