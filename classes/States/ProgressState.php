<?php
// classes/States/ProgressState.php

interface ProgressState
{
    public function displayStatus();
    public function notifyUser($message);
    public function getAdvice();
}

class GreenState implements ProgressState
{
    public function displayStatus()
    {
        return "Green (Healthy)";
    }
    public function notifyUser($message)
    {
        return "Great job! " . $message;
    }
    public function getAdvice()
    {
        return "You are doing great! Keep up the good work.";
    }
}

class YellowState implements ProgressState
{
    public function displayStatus()
    {
        return "Yellow (Caution)";
    }
    public function notifyUser($message)
    {
        return "Warning: " . $message;
    }
    public function getAdvice()
    {
        return "Please watch your intake. Try to balance your next meal.";
    }
}

class RedState implements ProgressState
{
    public function displayStatus()
    {
        return "Red (Critical)";
    }
    public function notifyUser($message)
    {
        return "CRITICAL: " . $message;
    }
    public function getAdvice()
    {
        return "Please contact your dietitian immediately. Your intake is off balance.";
    }
}
?>