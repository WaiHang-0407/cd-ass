<?php
// pages/recipe.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../classes/Strategies/AIStrategy.php';

requireRole(['User', 'Elderly', 'Dietitian', 'Caretaker']);
$user = getCurrentUser();

$recipeID = $_GET['id'] ?? null;
if (!$recipeID) {
    die("Invalid Recipe ID");
}

// 1. Fetch Recipe
$stmt = $pdo->prepare("SELECT * FROM recipes WHERE recipeID = ?");
$stmt->execute([$recipeID]);
$recipe = $stmt->fetch();

if (!$recipe) {
    die("Recipe not found");
}

$ingredients = json_decode($recipe['ingredients'], true) ?? [];
$instructions = $recipe['instructions'];

// 2. Lazy Generation (If instructions missing)
if (empty($instructions)) {
    $ai = new AIStrategy();
    try {
        $details = $ai->generateRecipeDetails($recipe['name'], $ingredients);

        // Save to DB
        $instructions = json_encode($details['steps']);
        // Optionally update ingredients if refined list provided
        // $finalIngredients = json_encode($details['shopping_list']); 

        $upd = $pdo->prepare("UPDATE recipes SET instructions = ? WHERE recipeID = ?");
        $upd->execute([$instructions, $recipeID]);

        // Refresh page to load clean data? or just use variables
    } catch (Exception $e) {
        $instructions = json_encode(["Error generating instructions. Please try again."]);
    }
}

$steps = json_decode($instructions, true) ?? [];

// 3. Fetch Shopping List Status
$shopStmt = $pdo->prepare("SELECT item FROM shopping_items WHERE userID = ?");
$shopStmt->execute([$user->userID]);
$myShoppingList = $shopStmt->fetchAll(PDO::FETCH_COLUMN);

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <a href="diet-plan.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to Plan</a>

        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h2 class="h4 m-0"><?= htmlspecialchars($recipe['name']) ?></h2>
                <div>
                    <button class="btn btn-light btn-sm" onclick="speakAll()"><i class="bi bi-megaphone-fill"></i> Read
                        Aloud</button>
                </div>
            </div>
            <div class="card-body">

                <!-- Stats -->
                <div class="d-flex justify-content-around mb-4 p-2 bg-light rounded">
                    <div class="text-center">
                        <strong class="d-block text-muted">Calories</strong>
                        <span class="fs-5"><?= $recipe['calories'] ?></span>
                    </div>
                    <div class="text-center">
                        <strong class="d-block text-muted">Protein</strong>
                        <span class="fs-5"><?= $recipe['protein'] ?>g</span>
                    </div>
                    <div class="text-center">
                        <strong class="d-block text-muted">Fibre</strong>
                        <span class="fs-5"><?= $recipe['fibre'] ?>g</span>
                    </div>
                </div>

                <div class="row">
                    <!-- Shopping List -->
                    <div class="col-md-5 mb-4 border-end">
                        <h4 class="text-success"><i class="bi bi-cart-check"></i> Shopping List</h4>
                        <div class="list-group list-group-flush">
                            <?php foreach ($ingredients as $ing): 
                                $isChecked = in_array($ing, $myShoppingList) ? 'checked' : '';
                            ?>
                                <label class="list-group-item">
                                    <input class="form-check-input me-1" type="checkbox" 
                                           <?= $isChecked ?>
                                           onchange="toggleItem(this, '<?= htmlspecialchars(addslashes($ing), ENT_QUOTES) ?>')">
                                    <?= htmlspecialchars($ing) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="col-md-7">
                        <h4 class="text-info"><i class="bi bi-list-ol"></i> Instructions</h4>
                        <ol class="list-group list-group-numbered list-group-flush">
                            <?php foreach ($steps as $getStep): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-start py-3">
                                    <div class="ms-2 me-auto">
                                        <?= htmlspecialchars($getStep) ?>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary ms-3" style="min-width: 40px;"
                                        title="Read Step"
                                        onclick="speakText('<?= htmlspecialchars(addslashes($getStep), ENT_QUOTES) ?>')">
                                        <i class="bi bi-volume-up-fill"></i>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    function toggleItem(checkbox, itemName) {
        // Disable temporarily
        checkbox.disabled = true;

        fetch('../ajax/shopping_list.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'toggle',
                item: itemName
            })
        })
        .then(response => response.json())
        .then(data => {
            checkbox.disabled = false;
            if (!data.success) {
                alert("Error saving item");
                checkbox.checked = !checkbox.checked; // Revert
            }
        })
        .catch((error) => {
            checkbox.disabled = false;
            console.error('Error:', error);
            checkbox.checked = !checkbox.checked;
        });
    }

    function speakText(text) {
        window.speechSynthesis.cancel(); // Stop previous
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 0.9; // Slightly slower for elderly
        window.speechSynthesis.speak(utterance);
    }

    function speakAll() {
        window.speechSynthesis.cancel();
        const steps = <?= json_encode($steps) ?>;
        let text = "Recipe for <?= addslashes($recipe['name']) ?>. ";
        text += "First, check your ingredients. ";
        text += "Now, here are the steps. ";
        steps.forEach((s, i) => {
            text += "Step " + (i + 1) + ": " + s + ". ";
        });
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 0.9;
        window.speechSynthesis.speak(utterance);
    }
</script>

<?php include '../includes/footer.php'; ?>