<?php
// classes/Progress.php
require_once 'States/ProgressState.php';

class Progress
{
    private $pdo;
    private $progressID;
    private $elderlyID;
    public $date;
    public $caloriesTaken = 0;
    public $proteinTaken = 0;
    public $waterIntake = 0;
    // New Nutrients
    public $fiberTaken = 0;
    public $carbohydrateTaken = 0;
    public $sodiumTaken = 0;
    public $sugarTaken = 0;
    public $fatTaken = 0;

    public $state; // ProgressState

    public function __construct($pdo, $elderlyID)
    {
        $this->pdo = $pdo;
        $this->elderlyID = $elderlyID;
        $this->date = date('Y-m-d');
        $this->loadOrCreate();
    }

    private function loadOrCreate()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM progress WHERE elderlyID = ? AND date = ?");
        $stmt->execute([$this->elderlyID, $this->date]);
        $data = $stmt->fetch();

        if ($data) {
            $this->progressID = $data['progressID'];
            $this->caloriesTaken = $data['caloriesTaken'];
            $this->proteinTaken = $data['proteinTaken'];
            $this->waterIntake = $data['waterIntake'];

            // Load New Properties
            $this->fiberTaken = $data['fiberTaken'] ?? 0;
            $this->carbohydrateTaken = $data['carbohydrateTaken'] ?? 0;
            $this->sodiumTaken = $data['sodiumTaken'] ?? 0;
            $this->sugarTaken = $data['sugarTaken'] ?? 0;
            $this->fatTaken = $data['fatTaken'] ?? 0;

            $this->setStateFromString($data['state']);
        } else {
            // Find valid Diet Plan ID
            $stmtP = $this->pdo->prepare("SELECT dietPlanID FROM diet_plans WHERE elderlyID = ? ORDER BY createdAt DESC LIMIT 1");
            $stmtP->execute([$this->elderlyID]);
            $plan = $stmtP->fetch();

            $dietPlanID = $plan ? $plan['dietPlanID'] : null;

            if (!$dietPlanID) {
                // Create a placeholder plan to satisfy FK
                $dietPlanID = uniqid('DP_');
                $stmtNew = $this->pdo->prepare("INSERT INTO diet_plans (dietPlanID, elderlyID) VALUES (?, ?)");
                $stmtNew->execute([$dietPlanID, $this->elderlyID]);
            }

            $this->progressID = uniqid('PG_');
            $this->state = new GreenState();
            $stmt = $this->pdo->prepare("INSERT INTO progress (progressID, elderlyID, dietPlanID, date, state) VALUES (?, ?, ?, ?, 'Green')");
            $stmt->execute([$this->progressID, $this->elderlyID, $dietPlanID, $this->date]);
        }
    }

    private function setStateFromString($str)
    {
        switch ($str) {
            case 'Red':
                $this->state = new RedState();
                break;
            case 'Yellow':
                $this->state = new YellowState();
                break;
            default:
                $this->state = new GreenState();
        }
    }

    public function addIntake($cals, $prot, $water, $fibre = 0, $carbs = 0, $sodium = 0, $sugar = 0, $foodName = 'Manual Entry')
    {
        $this->caloriesTaken += $cals;
        $this->proteinTaken += $prot;
        $this->waterIntake += $water;

        $this->fiberTaken += $fibre;
        $this->carbohydrateTaken += $carbs;
        $this->sodiumTaken += $sodium;
        $this->sugarTaken += $sugar;

        // Log to food_logs table
        try {
            $logID = uniqid('L_');
            $logStmt = $this->pdo->prepare("INSERT INTO food_logs (logID, elderlyID, foodName, calories, protein, carbs, fibre, sugar, sodium) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $logStmt->execute([
                $logID,
                $this->elderlyID,
                $foodName,
                $cals,
                $prot,
                $carbs,
                $fibre,
                $sugar,
                $sodium
            ]);
        } catch (Exception $e) {
            // Silently fail logging if error, don't stop main progress update
        }

        $this->evaluateState();
        $this->save();
    }

    private function evaluateState()
    {
        // Dynamic Assessment based on Profile
        require_once __DIR__ . '/Profile.php';
        $profile = new Profile($this->pdo, $this->elderlyID);

        $calLimit = ($profile->caloriesLimit > 0) ? $profile->caloriesLimit : 2000;
        $sodLimit = ($profile->sodiumLimit > 0) ? $profile->sodiumLimit : 2300;
        $sugarLimit = ($profile->sugarLimit > 0) ? $profile->sugarLimit : 50;

        // Ratios
        $calRatio = $this->caloriesTaken / $calLimit;

        // 1. Critical Checks (Red)
        if ($calRatio > 1.25) { // 25% Overeating
            $this->state = new RedState();
            return;
        }
        if ($this->sodiumTaken > ($sodLimit * 1.2)) { // High Sodium
            $this->state = new RedState();
            return;
        }

        // 2. Warning Checks (Yellow)
        if ($calRatio > 1.1) { // 10% Overeating
            $this->state = new YellowState();
            return;
        }
        if ($this->waterIntake < 1.0) { // Low Hydration
            $this->state = new YellowState();
            return;
        }
        if ($this->sodiumTaken > $sodLimit) {
            $this->state = new YellowState();
            return;
        }
        if ($this->sugarTaken > $sugarLimit) {
            $this->state = new YellowState();
            return;
        }

        // 3. Healthy (Green)
        $this->state = new GreenState();
    }

    public function save()
    {
        $stateStr = 'Green';
        if ($this->state instanceof YellowState)
            $stateStr = 'Yellow';
        if ($this->state instanceof RedState)
            $stateStr = 'Red';

        $stmt = $this->pdo->prepare("UPDATE progress SET caloriesTaken=?, proteinTaken=?, waterIntake=?, fiberTaken=?, carbohydrateTaken=?, sodiumTaken=?, sugarTaken=?, fatTaken=?, state=? WHERE progressID=?");
        $stmt->execute([
            $this->caloriesTaken,
            $this->proteinTaken,
            $this->waterIntake,
            $this->fiberTaken,
            $this->carbohydrateTaken,
            $this->sodiumTaken,
            $this->sugarTaken,
            $this->fatTaken,
            $stateStr,
            $this->progressID
        ]);
    }

    public function getStatusColor()
    {
        if ($this->state instanceof RedState)
            return 'danger';
        if ($this->state instanceof YellowState)
            return 'warning';
        return 'success';
    }

    public function getStreak()
    {
        // Find consecutive dates with 'Green' state, working backwards from yesterday
        // Including Today if it is Green
        $streak = 0;

        // Check Today first
        if ($this->state instanceof GreenState) {
            $streak++;
        }

        // Check past days
        $checkDate = date('Y-m-d', strtotime('-1 day'));

        while (true) {
            $stmt = $this->pdo->prepare("SELECT state FROM progress WHERE elderlyID = ? AND date = ?");
            $stmt->execute([$this->elderlyID, $checkDate]);
            $res = $stmt->fetch();

            if ($res && $res['state'] === 'Green') {
                $streak++;
                $checkDate = date('Y-m-d', strtotime($checkDate . ' -1 day'));
            } else {
                break;
            }
        }
        return $streak;
    }
}
?>