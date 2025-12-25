<?php
// classes/Profile.php

class Profile
{
    private $pdo;
    private $profileID;
    private $elderlyID;
    public $height = 0;
    public $weight = 0;
    public $bmi = 0;
    public $age = 0;      // New Property
    public $gender = '';  // New Property
    public $allergies = [];
    public $healthCondition = [];
    public $caloriesLimit = 0;
    public $carbsLimit = 0;
    public $sugarLimit = 0;
    public $sodiumLimit = 0;
    public $fibreRequirement = 0;
    public $softFoodRequirement = false;
    public $medicationList = [];

    public function __construct($pdo, $elderlyID = null)
    {
        $this->pdo = $pdo;
        $this->elderlyID = $elderlyID;
        if ($elderlyID) {
            $this->load();
        }
    }

    private function load()
    {
        // Join with Users table to get Age and Gender
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.age, u.gender 
            FROM profiles p
            JOIN users u ON p.elderlyID = u.userID
            WHERE p.elderlyID = ?
        ");
        $stmt->execute([$this->elderlyID]);
        $data = $stmt->fetch();

        if ($data) {
            $this->profileID = $data['profileID'];
            $this->height = $data['height'];
            $this->weight = $data['weight'];
            $this->bmi = $data['bmi'];
            $this->age = $data['age'];       // Populate Age
            $this->gender = $data['gender']; // Populate Gender
            $this->allergies = json_decode($data['allergies'], true) ?? [];
            $this->healthCondition = json_decode($data['healthCondition'], true) ?? [];
            $this->caloriesLimit = $data['caloriesLimit'];
            $this->carbsLimit = $data['carbsLimit'];
            $this->sugarLimit = $data['sugarLimit'];
            $this->sodiumLimit = $data['sodiumLimit'];
            $this->fibreRequirement = $data['fibreRequirement'];
            $this->softFoodRequirement = (bool) $data['softFoodRequirement'];
            $this->medicationList = json_decode($data['medicationList'], true) ?? [];
        }
    }

    public function calculateDietaryLimits($age, $gender)
    {
        if ($this->weight <= 0 || $this->height <= 0)
            return;

        // 1. Calculate BMR (Mifflin-St Jeor Equation)
        $bmr = (10 * $this->weight) + (6.25 * $this->height) - (5 * $age);
        if (strtolower($gender) == 'male') {
            $bmr += 5;
        } else {
            $bmr -= 161;
        }

        // 2. TDEE (Sedentary default for elderly)
        $tdee = $bmr * 1.2;
        $this->caloriesLimit = round($tdee);

        // 3. Macronutrients & Limits (Default Guidelines)
        // Carbs: ~50% of cal, Protein ~20%, Fat ~30%
        $this->carbsLimit = round(($tdee * 0.50) / 4);

        // Default Limits
        $this->sugarLimit = 30; // recommended max added sugar
        $this->sodiumLimit = 2300; // standard limit
        $this->fibreRequirement = 25; // standard

        // 4. Adjust based on Health Conditions (Case-insensitive check)
        $conditions = array_map('strtolower', $this->healthCondition);

        // -- Diabetes --
        if (in_array('diabetes', $conditions)) {
            $this->sugarLimit = 20; // Stricter sugar limit
            $this->carbsLimit = round(($tdee * 0.45) / 4); // Reduces carbs to 45%
            $this->fibreRequirement = 30; // Higher fibre recommended
        }

        // -- Hypertension / Heart Disease / Kidney Disease --
        // All benefit from lower sodium
        if (
            in_array('hypertension (high blood pressure)', $conditions) ||
            in_array('heart disease', $conditions) ||
            in_array('kidney disease', $conditions)
        ) {
            $this->sodiumLimit = 1500; // Low sodium diet
        }

        // -- High Cholesterol --
        if (in_array('high cholesterol', $conditions)) {
            $this->fibreRequirement = 30; // High fibre helps lower cholesterol
            // Logic: Less Saturated fat usually means slightly less calories from fat
            $this->caloriesLimit = round($tdee * 0.95);
        }

        // -- Combined: Diabetes AND High Cholesterol --
        // If both are present, boost fibre further
        if (in_array('diabetes', $conditions) && in_array('high cholesterol', $conditions)) {
            $this->fibreRequirement = 35;
        }

        // -- Osteoporosis --
        // Focus is calcium (not tracked), but ensure sufficient calories/protein
        // No specific limit change for the fields we have, keep defaults.

        // -- Weight Management (Implicit) --
        // If BMI is high (>30), slightly deficit calories could be suggested, 
        // but for elderly we are cautious about restriction without professional oversight.
        // We will stick to TDEE (Maintenance) logic for now unless explicitly dieting.
    }

    public function save()
    {
        // Calculate BMI automatically
        if ($this->height > 0 && $this->weight > 0) {
            $h_m = $this->height / 100;
            $this->bmi = $this->weight / ($h_m * $h_m);
        }

        $isNew = false;
        if (!$this->profileID) {
            $isNew = true;
            $this->profileID = uniqid('P_');
            $stmt = $this->pdo->prepare("INSERT INTO profiles (
                profileID, elderlyID, height, weight, bmi, allergies, healthCondition, 
                caloriesLimit, carbsLimit, sugarLimit, sodiumLimit, fibreRequirement, 
                softFoodRequirement, medicationList
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        } else {
            $stmt = $this->pdo->prepare("UPDATE profiles SET 
                height=?, weight=?, bmi=?, allergies=?, healthCondition=?, 
                caloriesLimit=?, carbsLimit=?, sugarLimit=?, sodiumLimit=?, fibreRequirement=?, 
                softFoodRequirement=?, medicationList=? WHERE profileID=?");
        }

        $params = [
            $this->height ?? 0,
            $this->weight ?? 0,
            $this->bmi ?? 0,
            json_encode($this->allergies),
            json_encode($this->healthCondition),
            $this->caloriesLimit ?? 0,
            $this->carbsLimit ?? 0,
            $this->sugarLimit ?? 0,
            $this->sodiumLimit ?? 0,
            $this->fibreRequirement ?? 0,
            (int) $this->softFoodRequirement,
            json_encode($this->medicationList)
        ];

        if ($isNew) {
            array_unshift($params, $this->profileID, $this->elderlyID); // Add IDs to front for INSERT
        } else {
            $params[] = $this->profileID; // Add ID to end for UPDATE
        }

        return $stmt->execute($params);
    }

    // Setters
    public function setHeight(float $h)
    {
        $this->height = $h;
    }
    public function setWeight(float $w)
    {
        $this->weight = $w;
    }
    public function setBMI(float $b)
    {
        $this->bmi = $b;
    }
    public function addAllergy(string $a)
    {
        if (!in_array($a, $this->allergies))
            $this->allergies[] = $a;
    }
    public function removeAllergy(string $a)
    {
        $key = array_search($a, $this->allergies);
        if ($key !== false)
            unset($this->allergies[$key]);
    }
    public function addHealthCondition(string $c)
    {
        if (!in_array($c, $this->healthCondition))
            $this->healthCondition[] = $c;
    }
    public function removeHealthCondition(string $c)
    {
        $key = array_search($c, $this->healthCondition);
        if ($key !== false)
            unset($this->healthCondition[$key]);
    }
    public function setCaloriesLimit(float $v)
    {
        $this->caloriesLimit = $v;
    }
    public function setCarbsLimit(float $v)
    {
        $this->carbsLimit = $v;
    }
    public function setSugarLimit(float $v)
    {
        $this->sugarLimit = $v;
    }
    public function setSodiumLimit(float $v)
    {
        $this->sodiumLimit = $v;
    }
    public function setFibreRequirement(float $v)
    {
        $this->fibreRequirement = $v;
    }
    public function setSoftFoodRequirement(bool $v)
    {
        $this->softFoodRequirement = $v;
    }
    public function addMedication(string $m)
    {
        if (!in_array($m, $this->medicationList))
            $this->medicationList[] = $m;
    }

    public function getMedications()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM medications WHERE elderlyID = ? ORDER BY name ASC");
        $stmt->execute([$this->elderlyID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function clearMedications()
    {
        $stmt = $this->pdo->prepare("DELETE FROM medications WHERE elderlyID = ?");
        $stmt->execute([$this->elderlyID]);
    }

    public function addMedicationDetailed($name, $dosage, $freq)
    {
        $id = uniqid('MED_');
        $stmt = $this->pdo->prepare("INSERT INTO medications (medicationID, elderlyID, name, dosage, frequency) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id, $this->elderlyID, $name, $dosage, $freq]);
    }

    // Getters for UI
    public function getProfileID()
    {
        return $this->profileID;
    }
    // ... Direct property access used in this simple impl, but getters can be added if strict encapsulation needed
}
?>