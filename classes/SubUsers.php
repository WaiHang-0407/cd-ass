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
    }

    public function getProfile()
    {
        // Fetch profile from DB
        return null; // Implementation in next steps
    }

    public function getDietPlan()
    {
        return null; // Implementation in next steps
    }

    public function save()
    {
        if (parent::save()) {
            $stmt = $this->pdo->prepare("INSERT INTO elderly (elderlyID, assignedDietitianID) VALUES (?, ?)");
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
            $stmt = $this->pdo->prepare("INSERT INTO dietitians (dietitianID, licenseNo, qualification) VALUES (?, ?, ?)");
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
            $stmt = $this->pdo->prepare("INSERT INTO admins (adminID) VALUES (?)");
            $stmt->execute([$this->userID]);
            return true;
        }
        return false;
    }
}

class Caretaker extends User
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
        if (parent::save()) {
            $stmt = $this->pdo->prepare("INSERT INTO caretakers (caretakerID, relationship, emergencyContact) VALUES (?, ?, ?)");
            $stmt->execute([$this->userID, $this->relationship, $this->emergencyContact]);
            return true;
        }
        return false;
    }
}
?>