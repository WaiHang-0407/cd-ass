<?php
// classes/User.php

interface UserStrategy
{
    public function login($password);
    public function getID();
}

class User implements UserStrategy
{
    protected $pdo;
    public $userID;
    public $name;
    public $username;
    protected $password; // Hash
    public $email;
    public $phoneNo;
    public $age;
    public $gender;
    public $role;

    public function __construct($pdo, $data = null)
    {
        $this->pdo = $pdo;
        if ($data) {
            $this->userID = $data['userID'] ?? null;
            $this->name = $data['name'] ?? '';
            $this->username = $data['username'] ?? '';
            $this->password = $data['password'] ?? '';
            $this->email = $data['email'] ?? '';
            $this->phoneNo = $data['phoneNo'] ?? 0;
            $this->age = $data['age'] ?? 0;
            $this->gender = $data['gender'] ?? '';
            $this->role = $data['role'] ?? '';
        }
    }

    // Setters
    public function setName($name)
    {
        $this->name = $name;
    }
    public function setUsername($username)
    {
        $this->username = $username;
    }
    public function setPassword($password)
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }
    public function setEmail($email)
    {
        $this->email = $email;
    }
    public function setPhoneNo($phoneNo)
    {
        $this->phoneNo = $phoneNo;
    }
    public function setAge($age)
    {
        $this->age = $age;
    }
    public function setGender($gender)
    {
        $this->gender = $gender;
    }
    public function setRole($role)
    {
        $this->role = $role;
    }

    // Getters
    public function getUserID()
    {
        return $this->userID;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getUsername()
    {
        return $this->username;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getPhoneNo()
    {
        return $this->phoneNo;
    }
    public function getAge()
    {
        return $this->age;
    }
    public function getGender()
    {
        return $this->gender;
    }
    public function getRole()
    {
        return $this->role;
    }
    public function getAdminID()
    {
        return null;
    } // Default null, overridden by Admin

    // Interface Implementation
    public function getID()
    {
        return $this->userID;
    }

    public function login($password)
    {
        // Basic check, typically called after loading user by username
        // In this design, we assume the object is populated via a fetch first
        return password_verify($password, $this->password);
    }

    // Static Helper to load a user
    public static function findByUsername($pdo, $identifier)
    {
        // Allow login by Username, Email, or Phone
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? OR phoneNo = ?");
        $stmt->execute([$identifier, $identifier, $identifier]);
        $data = $stmt->fetch();

        if (!$data)
            return null;

        // Factory method to return correct subclass
        switch ($data['role']) {
            case 'Admin':
                return new Admin($pdo, $data);
            case 'Elderly':
            case 'User':
                return new Elderly($pdo, $data);
            case 'Dietitian':
                return new Dietitian($pdo, $data);
            case 'Caretaker':
                return new Caretaker($pdo, $data);
            default:
                return new User($pdo, $data);
        }
    }

    public function save()
    {
        // Insert or Update logic
        if (!$this->userID) {
            $this->userID = uniqid('U_'); // User ID generation strategy
            $stmt = $this->pdo->prepare("INSERT INTO users (userID, name, username, password, email, phoneNo, age, gender, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$this->userID, $this->name, $this->username, $this->password, $this->email, $this->phoneNo, $this->age, $this->gender, $this->role]);
            return true;
        }
        return false;
    }
}
?>