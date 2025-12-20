<?php
// classes/Strategies/AIStrategy.php
require_once __DIR__ . '/../../includes/config.php';
require_once 'RecipeStrategy.php';

class AIStrategy implements RecipeStrategy
{

    public function generateRecipe($dietPlan, $preference)
    {
        $apiKey = AI_API_KEY;

        // Throttling: Sleep 2 seconds to avoid Rate Limiting (429)
        sleep(2);

        // Fallback if no key is present
        if (empty($apiKey)) {
            return $this->fallbackRecipe();
        }

        // 1. Construct the Prompt from the Context
        $profileData = "General elderly diet";
        $cuisineText = "";
        $mealType = "meal"; // Default

        // Unpack Context
        $profile = null;
        if (is_array($preference) && isset($preference['profile'])) {
            $profile = $preference['profile'];
            if (!empty($preference['cuisines'])) {
                $cuisineText = "Preferred Cuisines: " . implode(', ', $preference['cuisines']) . ".";
            }
            if (isset($preference['mealType'])) {
                $mealType = $preference['mealType'];
            }
        } elseif (is_object($preference) && get_class($preference) == 'Profile') {
            $profile = $preference;
        }

        if ($profile) {
            $profileData = "Elderly person, Weight: {$profile->weight}kg, Conditions: " . implode(',', $profile->healthCondition) .
                ", Allergies: " . implode(',', $profile->allergies);
        }

        $prompt = "Generate a single healthy **$mealType** recipe for: $profileData. $cuisineText Return ONLY valid raw JSON (no markdown backticks) in this format: " .
            "{ \"name\": \"Recipe Name\", \"ingredients\": [\"Item 1\", \"Item 2\"], \"calories\": 500, \"protein\": 20, \"carbs\": 30, \"fibre\": 5, \"fat\": 10, \"sodium\": 200, \"sugar\": 5 }";

        // 2. Call the API (Google Gemini)
        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . AI_MODEL . ":generateContent?key=" . $apiKey;

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ];

        $maxRetries = 3;
        $attempt = 0;
        $success = false;
        $response = null;
        $httpCode = 0;
        $curlError = ''; // Initialize curlError for logging outside the loop

        do {
            $attempt++;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            // FIX: Disable SSL verification for local dev environments
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200) {
                $success = true;
                break;
            } elseif (in_array($httpCode, [429, 503, 500])) {
                // Rate Limit or Server Error - Retry
                if ($attempt < $maxRetries) {
                    $retryAfter = 5 * $attempt; // Default exponential backoff

                    // Try to parse specific retry delay from Gemini Body
                    $errBody = json_decode($response, true);
                    if (isset($errBody['error']['details'])) {
                        foreach ($errBody['error']['details'] as $detail) {
                            if (isset($detail['retryDelay'])) {
                                $retryAfter = (int) $detail['retryDelay'];
                                if ($retryAfter < 5)
                                    $retryAfter = 5; // Minimum safety
                                break;
                            }
                        }
                    }

                    file_put_contents(__DIR__ . '/../../ai_debug_log.txt', "Rate limit hit. Retrying in $retryAfter seconds...\n", FILE_APPEND);
                    sleep($retryAfter);
                }
            } else {
                // Other permanent errors (400, 401, 404)
                break;
            }

        } while ($attempt < $maxRetries);

        // Debug Logging
        if (!$success) {
            file_put_contents(
                __DIR__ . '/../../ai_debug_log.txt',
                "Time: " . date('Y-m-d H:i:s') . "\n" .
                "HTTP Code: $httpCode\n" .
                "Curl Error: $curlError\n" .
                "Response: $response\n" .
                "--------------------\n",
                FILE_APPEND
            );
        }

        // 3. Parse Response
        if ($success && $httpCode === 200) {
            $result = json_decode($response, true);
            // Gemini Path: candidates[0].content.parts[0].text
            $content = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // Clean JSON
            $content = str_replace(['```json', '```', 'json'], '', $content);
            $content = trim($content);

            $recipe = json_decode($content, true);

            if ($recipe) {
                return $recipe;
            }
        }

        // FAILSAFE: Log first, then Throw if busy
        file_put_contents(__DIR__ . '/../../ai_debug_log.txt', "Single Gen Failed. Code: $httpCode\n", FILE_APPEND);
        if (in_array($httpCode, [429, 503])) {
            throw new Exception("AI Service is currently busy (Rate Limit Reached). Please wait 1 minute and try again.", $httpCode);
        }

        // Return fallback if API fails (but not busy)
        return $this->fallbackRecipe("(AI Unreachable - Check Log/Quota)");
    }

    public function generateDailyMenu($dietPlan, $preference)
    {
        // 1. Prepare Prompt
        $profileData = "General elderly diet";
        $cuisineText = "";

        // Unpack Context
        $profile = null;
        if (is_array($preference) && isset($preference['profile'])) {
            $profile = $preference['profile'];
            if (!empty($preference['cuisines'])) {
                $cuisineText = "Preferred Cuisines: " . implode(', ', $preference['cuisines']) . ".";
            }
        } elseif (is_object($preference) && get_class($preference) == 'Profile') {
            $profile = $preference;
        }

        if ($profile) {
            $profileData = "Elderly person, Weight: {$profile->weight}kg, Conditions: " . implode(',', $profile->healthCondition) .
                ", Allergies: " . implode(',', $profile->allergies);
        }

        $prompt = "Generate a FULL DAY menu (Breakfast, Lunch, Dinner) for: $profileData. $cuisineText Ensure variety (e.g. Porridge for breakfast, Rice for lunch). " .
            "Return ONLY valid raw JSON (no markdown backticks) in this exact format: " .
            "{ \"Breakfast\": { \"name\": \"...\", \"ingredients\": [\"...\"], \"calories\": 400, \"protein\": 10, \"carbs\": 30, \"fibre\": 5, \"fat\": 5, \"sodium\": 200, \"sugar\": 5 }, " .
            "\"Lunch\": { \"name\": \"...\", \"ingredients\": [\"...\"], \"calories\": 600, ... }, " .
            "\"Dinner\": { \"name\": \"...\", \"ingredients\": [\"...\"], \"calories\": 500, ... } }";

        // 2. Call API (Copy-Paste Retry Logic for Robustness)
        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . AI_MODEL . ":generateContent?key=" . AI_API_KEY;
        $data = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ];

        $maxRetries = 3;
        $attempt = 0;
        $success = false;
        $response = null;
        $httpCode = 0;

        do {
            $attempt++;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $success = true;
                break;
            } elseif (in_array($httpCode, [429, 503, 500])) {
                if ($attempt < $maxRetries) {
                    $retryAfter = 5 * $attempt;

                    $errBody = json_decode($response, true);
                    if (isset($errBody['error']['details'])) {
                        foreach ($errBody['error']['details'] as $detail) {
                            if (isset($detail['retryDelay'])) {
                                $retryAfter = max(5, (int) $detail['retryDelay']);
                                break;
                            }
                        }
                    }
                    file_put_contents(__DIR__ . '/../../ai_debug_log.txt', "Batch Gen Rate limit hit. Retrying in $retryAfter seconds...\n", FILE_APPEND);
                    sleep($retryAfter);
                }
            } else {
                break;
            }
        } while ($attempt < $maxRetries);

        // 3. Parse Response
        if ($success && $httpCode === 200) {
            $result = json_decode($response, true);
            $content = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // Cleanup JSON
            $content = str_replace(['```json', '```'], '', $content);
            $menu = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($menu['Breakfast'])) {
                return $menu;
            } else {
                file_put_contents(__DIR__ . '/../../ai_debug_log.txt', "JSON Parse Error in Batch: " . json_last_error_msg() . "\nPopulating fallback.\n", FILE_APPEND);
            }
        }

        // FAILSAFE: Log first, then Throw if busy
        file_put_contents(__DIR__ . '/../../ai_debug_log.txt', "Daily Gen Failed. Code: $httpCode\n", FILE_APPEND);
        if (in_array($httpCode, [429, 503])) {
            throw new Exception("AI Service is currently busy (Rate Limit Reached). Please wait 1 minute and try again.", $httpCode);
        }

        // Fallback if API fails
        return [
            'Breakfast' => $this->fallbackRecipe(),
            'Lunch' => $this->fallbackRecipe(),
            'Dinner' => $this->fallbackRecipe()
        ];
    }

    private function fallbackRecipe($suffix = "")
    {
        return [
            "name" => "Standard Balanced Meal $suffix",
            "ingredients" => ["Chicken", "Rice", "Vegetables"],
            "calories" => 500,
            "protein" => 30,
            "carbs" => 40,
            "fibre" => 5,
            "fat" => 10,
            "sodium" => 300
        ];
    }

    public function generateWeeklyMenu($dietPlan, $preference)
    {
        // 1. Prepare Context
        $profileData = "General elderly diet";
        $cuisineText = "";

        $profile = null;
        if (is_array($preference) && isset($preference['profile'])) {
            $profile = $preference['profile'];
            if (!empty($preference['cuisines'])) {
                $cuisineText = "Preferred Cuisines: " . implode(', ', $preference['cuisines']) . ".";
            }
        } elseif (is_object($preference) && get_class($preference) == 'Profile') {
            $profile = $preference;
        }

        if ($profile) {
            $profileData = "Elderly person, Weight: {$profile->weight}kg, Conditions: " . implode(',', $profile->healthCondition) .
                ", Allergies: " . implode(',', $profile->allergies);
            // Added Limits (Fallback logic if Profile has 0s)
            $calTarget = ($profile->caloriesLimit > 500) ? $profile->caloriesLimit : 1800; // Fallback to 1800 if 0
            $sodLimit = ($profile->sodiumLimit > 0) ? $profile->sodiumLimit : 2300;
            $carbLimit = ($profile->carbsLimit > 0) ? $profile->carbsLimit : 200;

            // GOAL LOGIC: Adjust Calories
            $goal = $preference['goal'] ?? 'maintain';
            $goalText = "Maintain Health";

            if ($goal == 'lose') {
                $calTarget -= 500;
                if ($calTarget < 1200)
                    $calTarget = 1200; // Safety Minimum
                $goalText = "LOSE WEIGHT (Calorie Deficit)";
            } elseif ($goal == 'gain') {
                $calTarget += 500;
                $goalText = "GAIN WEIGHT (Calorie Surplus)";
            }

            $profileData .= ". STRICT LIMITS: Daily Calories target {$calTarget} kcal (approx {$calTarget} kcal/day), Sodium < {$sodLimit}mg, Carbs < {$carbLimit}g.";
            $profileData .= " GOAL: $goalText. Select foods appropriate for this goal.";
        }

        $info = "$profileData. $cuisineText";
        $fullMenu = [];

        // Optimised: Single Batch for 7 Days 
        $batches = [[1, 7]];

        foreach ($batches as $batch) {
            $startDay = $batch[0];
            $endDay = $batch[1];

            // 2. Prompt for specific range
            $prompt = "Generate a MEAL PLAN for Day $startDay to Day $endDay for: $info " .
                "Ensure diverse meals. IMPORTANT: The TOTAL calories for each day MUST be within 10% of the target {$calTarget} kcal. " .
                "Return ONLY valid raw JSON for these days in this format: " .
                "{ \"Day $startDay\": { \"Breakfast\": { \"name\": \"...\", \"calories\": 400, \"protein\": 20, \"carbs\": 30, \"fibre\": 5, \"fat\": 10, \"sodium\": 200, \"sugar\": 5, ... }, ... }, " .
                "  ... " .
                "  \"Day $endDay\": { ... } }";

            // Reuse Retry Logic
            $url = "https://generativelanguage.googleapis.com/v1beta/models/" . AI_MODEL . ":generateContent?key=" . AI_API_KEY;
            $data = ['contents' => [['parts' => [['text' => $prompt]]]]];

            $maxRetries = 3;
            $attempt = 0;
            $success = false;
            $response = null;

            do {
                $attempt++;
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    $success = true;
                    break;
                } elseif (in_array($httpCode, [429, 503, 500])) {
                    if ($attempt < $maxRetries) {
                        $retryAfter = 5 * $attempt;
                        $errBody = json_decode($response, true);
                        if (isset($errBody['error']['details'][0]['retryDelay'])) {
                            $retryAfter = max(5, (int) $errBody['error']['details'][0]['retryDelay']);
                        }
                        sleep($retryAfter);
                    }
                } else {
                    break;
                }
            } while ($attempt < $maxRetries);

            if ($success) {
                $result = json_decode($response, true);
                $content = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $content = str_replace(['```json', '```'], '', $content);
                $batchParams = json_decode($content, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($batchParams)) {
                    $fullMenu = array_merge($fullMenu, $batchParams);
                } else {
                    file_put_contents(__DIR__ . '/../../ai_debug_log.txt', "Batch $startDay-$endDay Parse Error: " . json_last_error_msg() . "\n", FILE_APPEND);
                }
            } else {
                // CRITICAL CHANGE: If Rate Limited or Overloaded, Throw Exception to notify User
                file_put_contents(__DIR__ . '/../../ai_debug_log.txt', "Batch $startDay-$endDay Failed. Code: $httpCode\n", FILE_APPEND);
                if (in_array($httpCode, [429, 503])) {
                    throw new Exception("AI Service is currently busy (Rate Limit Reached). Please wait 1 minute and try again.", $httpCode);
                }
            }
        }

        // Fill missing days with fallback ONLY if not thrown
        for ($i = 1; $i <= 7; $i++) {
            if (!isset($fullMenu["Day $i"]) && !isset($fullMenu["Day $i "])) {
                $fullMenu["Day $i"] = [
                    'Breakfast' => $this->fallbackRecipe(" (Fallback Day $i)"),
                    'Lunch' => $this->fallbackRecipe(" (Fallback Day $i)"),
                    'Dinner' => $this->fallbackRecipe(" (Fallback Day $i)")
                ];
            }
        }

        return $fullMenu;
    }
    public function generateRecipeDetails($name, $ingredients)
    {
        set_time_limit(90); // Allow extra time for AI generation

        $ingText = implode(', ', $ingredients);
        $prompt = "For the recipe '$name' (Ingredients: $ingText), generate:" .
            "1. A simplified Shopping List (array of strings)." .
            "2. Step-by-step Cooking Instructions (array of strings)." .
            "Return ONLY valid raw JSON: { \"shopping_list\": [\"...\"], \"steps\": [\"Step 1...\", \"Step 2...\"] }";

        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . AI_MODEL . ":generateContent?key=" . AI_API_KEY;
        $data = ['contents' => [['parts' => [['text' => $prompt]]]]];

        // Simple single attempt (no complex retry needed for on-demand lazy load, user can refresh)
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 80); // Timeout before PHP execution limit
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            $content = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $content = str_replace(['```json', '```'], '', $content);
            $details = json_decode($content, true);

            if (isset($details['steps'])) {
                return $details;
            }
        }

        // Fallback
        return [
            'shopping_list' => $ingredients,
            'steps' => ["Mix ingredients.", "Cook thoroughly.", "Serve."]
        ];
    }
}
?>