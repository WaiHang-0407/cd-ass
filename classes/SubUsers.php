<?php
// classes/SubUsers.php
require_once 'User.php';

class Elderly extends User
{
    protected $elderlyID;
    public $assignedDietitianID;

    // Additional properties can be loaded lazily
    public function __construct($pdo, $data = null)
    {
        parent::__construct($pdo, $data);
        $this->elderlyID = $this->userID; // 1:1 mapping
        $this->assignedDietitianID = $data['assignedDietitianID'] ?? null;

        // If ID exists but assignedDietitianID is missing (e.g. created from Users query), fetch it
        if ($this->userID && $this->assignedDietitianID === null) {
            $stmt = $this->pdo->prepare("SELECT assignedDietitianID FROM elderly WHERE elderlyID = ?");
            $stmt->execute([$this->userID]);
            $res = $stmt->fetchColumn();
            if ($res) {
                $this->assignedDietitianID = $res;
            }
        }
    }

    public function getProfile()
    {
        // Fetch profile from DB
        require_once 'Profile.php';
        return new Profile($this->pdo, $this->userID);
    }

    public function getDietPlan()
    {
        // Fetch active diet plan
        require_once 'DietPlan.php';
        $stmt = $this->pdo->prepare("
            SELECT dp.* 
            FROM diet_plans dp 
            JOIN diet_plan_approvals dpa ON dp.dietPlanID = dpa.dietPlanID
            WHERE dp.elderlyID = ? AND dpa.status = 'Approved'
            ORDER BY dp.createdAt DESC LIMIT 1
        ");
        $stmt->execute([$this->userID]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new DietPlan($this->pdo, $data);
        }
        return null;
    }

    public function save()
    {
        if (parent::save()) {
            // Use INSERT ON DUPLICATE KEY UPDATE to Isolate from FK Cascades
            $stmt = $this->pdo->prepare("INSERT INTO elderly (elderlyID, assignedDietitianID) VALUES (?, ?) ON DUPLICATE KEY UPDATE assignedDietitianID = VALUES(assignedDietitianID)");
            $stmt->execute([$this->userID, $this->assignedDietitianID]);
            return true;
        }
        return false;
    }
}

class Dietitian extends User
{
    public $qualification = [];
    public $licenseNo;

    public function __construct($pdo, $data = null)
    {
        parent::__construct($pdo, $data);
        if ($this->userID) {
            // Fetch extra details
            $stmt = $this->pdo->prepare("SELECT * FROM dietitians WHERE dietitianID = ?");
            $stmt->execute([$this->userID]);
            $details = $stmt->fetch();
            if ($details) {
                $this->licenseNo = $details['licenseNo'];
                $this->qualification = json_decode($details['qualification'], true) ?? [];
            }
        }
    }

    public function save()
    {
        if (parent::save()) {
            $stmt = $this->pdo->prepare("INSERT INTO dietitians (dietitianID, licenseNo, qualification) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE licenseNo = VALUES(licenseNo), qualification = VALUES(qualification)");
            $stmt->execute([$this->userID, $this->licenseNo, json_encode($this->qualification)]);
            return true;
        }
        return false;
    }
}

class Admin extends User
{
    public function getAdminID()
    {
        return $this->userID;
    }

    public function save()
    {
        if (parent::save()) {
            $stmt = $this->pdo->prepare("REPLACE INTO admins (adminID) VALUES (?)");
            $stmt->execute([$this->userID]);
            return true;
        }
        return false;
    }
}

class Caretaker extends Elderly
{
    public $relationship;
    public $emergencyContact;

    public function __construct($pdo, $data = null)
    {
        parent::__construct($pdo, $data);
        if ($this->userID) {
            $stmt = $this->pdo->prepare("SELECT * FROM caretakers WHERE caretakerID = ?");
            $stmt->execute([$this->userID]);
            $details = $stmt->fetch();
            if ($details) {
                $this->relationship = $details['relationship'];
                $this->emergencyContact = $details['emergencyContact'];
            }
        }
    }

    public function save()
    {
        // 1. Save to Users and Elderly tables (via Parent)
        // This ensures they have a Profile and Diet Plan capability
        if (parent::save()) {
            // 2. Save/Update Caretakers specific info
            // Check if exists first to decide Insert/Update (or use REPLACE INTO if SQLite/MySQL allows)
            // For now simple INSERT IGNORE or Check
            $check = $this->pdo->prepare("SELECT caretakerID FROM caretakers WHERE caretakerID = ?");
            $check->execute([$this->userID]);
            if ($check->rowCount() == 0) {
                $stmt = $this->pdo->prepare("INSERT INTO caretakers (caretakerID, relationship, emergencyContact) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE relationship = VALUES(relationship), emergencyContact = VALUES(emergencyContact)");
                $stmt->execute([$this->userID, $this->relationship ?? '', $this->emergencyContact ?? '']);
            }
            return true;
        }
        return false;
    }

    public function linkPatient($patientID)
    {
        $stmt = $this->pdo->prepare("INSERT INTO user_links (caretakerID, patientID) VALUES (?, ?)");
        try {
            return $stmt->execute([$this->userID, $patientID]);
        } catch (Exception $e) {
            return false; // Likely duplicate or constraint
        }
    }

    public function getLinkedPatients()
    {
        $stmt = $this->pdo->prepare("
            SELECT u.* 
            FROM user_links ul
            JOIN users u ON ul.patientID = u.userID
            WHERE ul.caretakerID = ?
        ");
        $stmt->execute([$this->userID]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
?>