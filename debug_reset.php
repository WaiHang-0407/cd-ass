<?php
// debug_reset.php
require_once 'includes/db.php';
require_once 'classes/GenerateDietPlan.php';
require_once 'classes/Profile.php';

// 1. Get a test user
$stmt = $pdo->query("SELECT userID FROM users WHERE role IN ('User', 'Elderly') LIMIT 1");
$userID = $stmt->fetchColumn();
echo "Target User: $userID\n";

if (!$userID)
    die("No user.");

// 2. FORCE VALID DATA
$pdo->prepare("UPDATE profiles SET caloriesLimit = 2000, carbsLimit=200 WHERE elderlyID = ?")->execute([$userID]);

// Check
$check = $pdo->query("SELECT caloriesLimit FROM profiles WHERE elderlyID = '$userID'")->fetchColumn();
echo "DB Limit BEFORE: $check\n";

// 3. GENERATE
echo "Generating...\n";
$generator = new GenerateDietPlan($pdo);
// Pass dummy cuisines/duration
$generator->generate($userID, ['Chinese'], 1);

// 4. CHECK AFTER
$checkAfter = $pdo->query("SELECT caloriesLimit FROM profiles WHERE elderlyID = '$userID'")->fetchColumn();
echo "DB Limit AFTER: $checkAfter\n";

if ((int) $checkAfter !== 2000) {
    echo "FAIL: DB changed from 2000 to $checkAfter\n";
} else {
    echo "PASS: DB remained 2000\n";
}
?>