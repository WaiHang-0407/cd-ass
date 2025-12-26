<?php
// classes/DietPlan.php

// --- State Pattern Interfaces and Classes ---

interface DietPlanState
{
    public function notifyUser($message);
    public function checkStatus($context);
}

class PendingDietPlanApprovalState implements DietPlanState
{
    public function notifyUser($message)
    {
        // In a real app, this might send an email or system notification
        return "Notification to User: Your diet plan is Pending Approval. $message";
    }

    public function checkStatus($context)
    {
        return "Pending";
    }

    public function approve($context, $dietitianID)
    {
        // Transition to Approved
        $context->setState(new ApproveDietPlanState());
        $context->updateDBStatus('Approved', $dietitianID);
    }

    public function revise($context, $dietitianID)
    {
        // Transition to Revise
        $context->setState(new ReviseDietPlanState());
        $context->updateDBStatus('Revise', $dietitianID);
    }
}

class ApproveDietPlanState implements DietPlanState
{
    public function notifyUser($message)
    {
        return "Notification to User: Your diet plan is APPROVED! $message";
    }

    public function checkStatus($context)
    {
        return "Approved";
    }

    public function activateDietPlan()
    {
        // Logic to make this the active plan
    }
}

class ReviseDietPlanState implements DietPlanState
{
    public function notifyUser($message)
    {
        return "Notification to User: Your diet plan requires REVISION. $message";
    }

    public function checkStatus($context)
    {
        return "Revise";
    }
}

// --- DietPlanApproval Class (Context) ---

class DietPlanApproval
{
    private $pdo;
    private $dietPlanID;
    private $state; // Current State Object

    public function __construct($pdo, $dietPlanID, $statusStr = 'Pending')
    {
        $this->pdo = $pdo;
        $this->dietPlanID = $dietPlanID;

        switch ($statusStr) {
            case 'Approved':
                $this->state = new ApproveDietPlanState();
                break;
            case 'Revise':
                $this->state = new ReviseDietPlanState();
                break;
            default:
                $this->state = new PendingDietPlanApprovalState();
                break;
        }
    }

    public function setState(DietPlanState $state)
    {
        $this->state = $state;
    }

    public function getStatus()
    {
        return $this->state->checkStatus($this);
    }

    public function updateDBStatus($statusStr, $dietitianID)
    {
        $dietitianExists = false;
        if (!empty($dietitianID)) {
            try {
                $chk = $this->pdo->prepare("SELECT 1 FROM dietitians WHERE dietitianID = ? LIMIT 1");
                $chk->execute([$dietitianID]);
                $dietitianExists = (bool) $chk->fetchColumn();
            } catch (Exception $e) {
                $dietitianExists = false;
            }
        }

        if ($dietitianExists) {
            $stmt = $this->pdo->prepare("UPDATE diet_plan_approvals SET status = ?, dietitianID = ?, approvalDate = NOW() WHERE dietPlanID = ?");
            $stmt->execute([$statusStr, $dietitianID, $this->dietPlanID]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE diet_plan_approvals SET status = ?, dietitianID = NULL, approvalDate = NOW() WHERE dietPlanID = ?");
            $stmt->execute([$statusStr, $this->dietPlanID]);
        }
    }

    public function approve($dietitianID)
    {
        if ($this->state instanceof PendingDietPlanApprovalState || $this->state instanceof ReviseDietPlanState) {
            $this->state->approve($this, $dietitianID);
            $this->setState(new ApproveDietPlanState());
            $this->updateDBStatus('Approved', $dietitianID);
        }
    }

    public function revise($dietitianID)
    {
        $this->setState(new ReviseDietPlanState());
        $this->updateDBStatus('Revise', $dietitianID);
    }
}

// --- DietPlan Class ---

class DietPlan
{
    private $pdo;
    public $dietPlanID;
    public $elderlyID;
    public $createdAt;
    public $approval; // DietPlanApproval Object
    public $meals = []; // List of Meal objects

    public function __construct($pdo, $data = null)
    {
        $this->pdo = $pdo;
        if ($data) {
            $this->dietPlanID = $data['dietPlanID'];
            $this->elderlyID = $data['elderlyID'];
            $this->createdAt = $data['createdAt'];

            // Load Approval Status
            // In a real optimized query we'd join, but here we lazy load
            $stmt = $pdo->prepare("SELECT status FROM diet_plan_approvals WHERE dietPlanID = ?");
            $stmt->execute([$this->dietPlanID]);
            $res = $stmt->fetch();
            $status = $res ? $res['status'] : 'Pending';

            $this->approval = new DietPlanApproval($pdo, $this->dietPlanID, $status);
        }
    }

    public static function create($pdo, $elderlyID)
    {
        $id = uniqid('DP_');
        $stmt = $pdo->prepare("INSERT INTO diet_plans (dietPlanID, elderlyID) VALUES (?, ?)");
        $stmt->execute([$id, $elderlyID]);

        // Create initial Approval entry
        $stmt2 = $pdo->prepare("INSERT INTO diet_plan_approvals (approvalID, dietPlanID, status) VALUES (?, ?, 'Pending')");
        $stmt2->execute([uniqid('AP_'), $id]);

        return new DietPlan($pdo, ['dietPlanID' => $id, 'elderlyID' => $elderlyID, 'createdAt' => date('Y-m-d H:i:s')]);
    }

    public function getMeals()
    {
        if (empty($this->meals)) {
            $stmt = $this->pdo->prepare("SELECT * FROM meals WHERE dietPlanID = ? ORDER BY day ASC, FIELD(mealType, 'Breakfast', 'Lunch', 'Dinner')");
            $stmt->execute([$this->dietPlanID]);
            $this->meals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $this->meals;
    }
}
?>