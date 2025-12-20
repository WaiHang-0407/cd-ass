<?php
// classes/RecipeGenerator.php
require_once 'Strategies/RecipeStrategy.php';

class RecipeGenerator
{
    private $strategy;

    public function setStrategy(RecipeStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function executeStrategy($dietPlan, $preference = null)
    {
        if (!$this->strategy) {
            $this->strategy = new StandardStrategy();
        }
        return $this->strategy->generateRecipe($dietPlan, $preference);
    }

    public function generateDailyMenu($dietPlan, $preference = null)
    {
        if (!$this->strategy) {
            $this->strategy = new StandardStrategy();
        }
        return $this->strategy->generateDailyMenu($dietPlan, $preference);
    }

    public function generateWeeklyMenu($dietPlan, $preference = null)
    {
        if (!$this->strategy) {
            $this->strategy = new StandardStrategy();
        }
        return $this->strategy->generateWeeklyMenu($dietPlan, $preference);
    }
}
?>