<?php
// classes/GenerateDietPlan.php
require_once __DIR__ . '/DietPlan.php';
require_once __DIR__ . '/Profile.php';

class GenerateDietPlan
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function generate($elderlyID, $cuisines = [], $duration = 1, $goal = 'maintain')
    {
        // Increase time limit for AI generation (Week/Month takes time)
        set_time_limit(300);

        // 1. Transaction Start
        try {
            $this->pdo->beginTransaction();

            // 2. Load Profile
            $profile = new Profile($this->pdo, $elderlyID);

            // 2. Create Diet Plan Record
            $plan = DietPlan::create($this->pdo, $elderlyID);

            // 3. Determine Strategy based on Profile
            require_once __DIR__ . '/RecipeGenerator.php';
            require_once __DIR__ . '/Strategies/AIStrategy.php'; // Include AI Strategy

            $generator = new RecipeGenerator();
            $generator->setStrategy(new AIStrategy());

            // Context: Combine Profile, Cuisines AND Goal
            $contextData = [
                'profile' => $profile,
                'cuisines' => $cuisines,
                'goal' => $goal // Pass Goal to Strategy
            ];

            // 4. Generate Based on Duration
            if ($duration == 1) {
                // Single Day
                $dailyMenu = $generator->generateDailyMenu($plan->dietPlanID, $contextData);
                $this->saveDailyBatch($plan->dietPlanID, 1, $dailyMenu);
            } elseif ($duration == 7) {
                // Weekly (7 Days)
                $weeklyMenu = $generator->generateWeeklyMenu($plan->dietPlanID, $contextData);
                $this->saveWeeklyBatch($plan->dietPlanID, 0, $weeklyMenu);
            } elseif ($duration == 30) {
                // Monthly (30 Days) -> 4 Weeks + 2 Days
                // Week 1-4
                for ($w = 0; $w < 4; $w++) {
                    $weeklyMenu = $generator->generateWeeklyMenu($plan->dietPlanID, $contextData);
                    $this->saveWeeklyBatch($plan->dietPlanID, $w * 7, $weeklyMenu);
                }
                // Day 29, 30
                for ($d = 29; $d <= 30; $d++) {
                    $dailyMenu = $generator->generateDailyMenu($plan->dietPlanID, $contextData);
                    $this->saveDailyBatch($plan->dietPlanID, $d, $dailyMenu);
                }
            }

            $this->pdo->commit();
            return $plan;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            // Return error string instead of Object
            return "Error: " . $e->getMessage();
        }
    }

    private function saveWeeklyBatch($planID, $startDayOffset, $weeklyMenu)
    {
        // $weeklyMenu is ['Day 1' => [...], 'Day 2' => ...]
        foreach ($weeklyMenu as $dayKey => $dayMenu) {
            // Extract Day Number from key "Day 1" -> 1
            if (preg_match('/(\d+)/', $dayKey, $matches)) {
                $dayIndex = (int) $matches[1];
                $actualDay = $startDayOffset + $dayIndex;
                $this->saveDailyBatch($planID, $actualDay, $dayMenu);
            }
        }
    }

    private function saveDailyBatch($planID, $day, $dailyMenu)
    {
        foreach (['Breakfast', 'Lunch', 'Dinner'] as $mealType) {
            $recipeData = $dailyMenu[$mealType] ?? null;
            if ($recipeData) {
                $this->saveMeal($planID, $day, $mealType, $recipeData);
            }
        }
    }

    private function saveMeal($planID, $day, $mealType, $recipeData)
    {
        // If Strategy returned null or empty (shouldn't happen with fallback), ensure default
        if (!$recipeData)
            $recipeData = ["name" => "Error", "calories" => 0];

        // normalize keys (AI might use 'fiber' vs 'fibre')
        $fibre = $recipeData['fibre'] ?? $recipeData['fiber'] ?? 0;
        $carbs = $recipeData['carbs'] ?? $recipeData['carbohydrates'] ?? 0; // Robustness

        // 1. Save Meal
        $mealID = uniqid('M_');
        // Added day column to INSERT
        $stmt = $this->pdo->prepare("INSERT INTO meals (mealID, dietPlanID, day, mealType, totalCalories, totalProtein, totalCarbs, totalFibre, totalSodium, totalSugar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $mealID,
            $planID,
            $day,
            $mealType,
            $recipeData['calories'] ?? 0,
            $recipeData['protein'] ?? 0,
            $carbs,
            $fibre,
            $recipeData['sodium'] ?? 0,
            $recipeData['sugar'] ?? 0
        ]);

        // 2. Create Recipe Record
        $recipeID = uniqid('R_');
        $stmtR = $this->pdo->prepare("INSERT INTO recipes (recipeID, name, ingredients, calories, protein, carbs, fibre, fat, sodium) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtR->execute([
            $recipeID,
            $recipeData['name'] ?? 'Unknown',
            json_encode($recipeData['ingredients'] ?? []),
            $recipeData['calories'] ?? 0,
            $recipeData['protein'] ?? 0,
            $carbs,
            $fibre,
            $recipeData['fat'] ?? 0,
            $recipeData['sodium'] ?? 0
        ]);

        // Link Food
        $stmt2 = $this->pdo->prepare("INSERT INTO foods (foodID, mealID, recipeID, foodName) VALUES (?, ?, ?, ?)");
        $stmt2->execute([uniqid('F_'), $mealID, $recipeID, $recipeData['name'] ?? 'Unknown']);
    }
}
?>