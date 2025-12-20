<?php
// classes/Strategies/RecipeStrategy.php

interface RecipeStrategy
{
    public function generateRecipe($dietPlan, $preference);
    public function generateDailyMenu($dietPlan, $preference);
    public function generateWeeklyMenu($dietPlan, $preference); // New batch method for 7 days
}

class HalalStrategy implements RecipeStrategy
{
    public function generateRecipe($dietPlan, $preference)
    {
        return [
            "name" => "Halal Grilled Chicken Salad",
            "ingredients" => ["Chicken Breast (Halal)", "Lettuce", "Olive Oil", "Cucumber"],
            "calories" => 450,
            "protein" => 35,
            "carbs" => 10,
            "fibre" => 5,
            "fat" => 15,
            "sodium" => 300
        ];
    }

    public function generateDailyMenu($dietPlan, $preference)
    {
        return [
            'Breakfast' => $this->generateRecipe($dietPlan, $preference),
            'Lunch' => $this->generateRecipe($dietPlan, $preference),
            'Dinner' => $this->generateRecipe($dietPlan, $preference)
        ];
    }

    public function generateWeeklyMenu($dietPlan, $preference)
    {
        $week = [];
        for ($i = 1; $i <= 7; $i++)
            $week["Day $i"] = $this->generateDailyMenu($dietPlan, $preference);
        return $week;
    }
}

class VeganStrategy implements RecipeStrategy
{
    public function generateRecipe($dietPlan, $preference)
    {
        return [
            "name" => "Vegan Quinoa Bowl",
            "ingredients" => ["Quinoa", "Chickpeas", "Spinach", "Avocado"],
            "calories" => 400,
            "protein" => 15,
            "carbs" => 50,
            "fibre" => 12,
            "fat" => 18,
            "sodium" => 150
        ];
    }

    public function generateDailyMenu($dietPlan, $preference)
    {
        return [
            'Breakfast' => $this->generateRecipe($dietPlan, $preference),
            'Lunch' => $this->generateRecipe($dietPlan, $preference),
            'Dinner' => $this->generateRecipe($dietPlan, $preference)
        ];
    }

    public function generateWeeklyMenu($dietPlan, $preference)
    {
        $week = [];
        for ($i = 1; $i <= 7; $i++)
            $week["Day $i"] = $this->generateDailyMenu($dietPlan, $preference);
        return $week;
    }
}

class LowSugarStrategy implements RecipeStrategy
{
    public function generateRecipe($dietPlan, $preference)
    {
        return [
            "name" => "Low Sugar Steamed Fish",
            "ingredients" => ["White Fish", "Broccoli", "Lemon", "Garlic"],
            "calories" => 350,
            "protein" => 30,
            "carbs" => 5,
            "fibre" => 4,
            "fat" => 10,
            "sodium" => 200
        ];
    }

    public function generateDailyMenu($dietPlan, $preference)
    {
        return [
            'Breakfast' => $this->generateRecipe($dietPlan, $preference),
            'Lunch' => $this->generateRecipe($dietPlan, $preference),
            'Dinner' => $this->generateRecipe($dietPlan, $preference)
        ];
    }

    public function generateWeeklyMenu($dietPlan, $preference)
    {
        $week = [];
        for ($i = 1; $i <= 7; $i++)
            $week["Day $i"] = $this->generateDailyMenu($dietPlan, $preference);
        return $week;
    }
}

class SoftFoodStrategy implements RecipeStrategy
{
    public function generateRecipe($dietPlan, $preference)
    {
        return [
            "name" => "Soft Mashed Potatoes & Salmon",
            "ingredients" => ["Potatoes", "Salmon", "Butter", "Milk"],
            "calories" => 500,
            "protein" => 25,
            "carbs" => 40,
            "fibre" => 3,
            "fat" => 20,
            "sodium" => 250
        ];
    }

    public function generateDailyMenu($dietPlan, $preference)
    {
        return [
            'Breakfast' => $this->generateRecipe($dietPlan, $preference),
            'Lunch' => $this->generateRecipe($dietPlan, $preference),
            'Dinner' => $this->generateRecipe($dietPlan, $preference)
        ];
    }

    public function generateWeeklyMenu($dietPlan, $preference)
    {
        $week = [];
        for ($i = 1; $i <= 7; $i++)
            $week["Day $i"] = $this->generateDailyMenu($dietPlan, $preference);
        return $week;
    }
}

class StandardStrategy implements RecipeStrategy
{
    public function generateRecipe($dietPlan, $preference)
    {
        return [
            "name" => "Balanced Roast Turkey",
            "ingredients" => ["Turkey", "Sweet Potato", "Green Beans"],
            "calories" => 550,
            "protein" => 40,
            "carbs" => 35,
            "fibre" => 6,
            "fat" => 12,
            "sodium" => 400
        ];
    }

    public function generateDailyMenu($dietPlan, $preference)
    {
        return [
            'Breakfast' => $this->generateRecipe($dietPlan, $preference),
            'Lunch' => $this->generateRecipe($dietPlan, $preference),
            'Dinner' => $this->generateRecipe($dietPlan, $preference)
        ];
    }

    public function generateWeeklyMenu($dietPlan, $preference)
    {
        $week = [];
        for ($i = 1; $i <= 7; $i++)
            $week["Day $i"] = $this->generateDailyMenu($dietPlan, $preference);
        return $week;
    }
}
?>