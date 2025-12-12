CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userID VARCHAR(6) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    phoneNo INT NOT NULL,
    password VARCHAR(255) NOT NULL,
    age INT,
    gender VARCHAR(10),
    role ENUM('elderly', 'caretaker', 'admin', 'dietitian') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS Admin (
    id INT AUTO_INCREMENT PRIMARY KEY,  -- Auto-incremented primary key for Admin table
    adminID VARCHAR(10) UNIQUE NOT NULL, -- Unique adminID (e.g., A00001, A00002, etc.)
    userID VARCHAR(6) NOT NULL,                          -- Foreign key referencing userID from the users table
    FOREIGN KEY (userID) REFERENCES users(userID)  -- Foreign key constraint
);

CREATE TABLE IF NOT EXISTS Caretaker (
    id INT AUTO_INCREMENT PRIMARY KEY,
    caretakerID VARCHAR(6) UNIQUE NOT NULL,
    relationship VARCHAR(255),
    emergencyContact INT,
    userID VARCHAR(6) NOT NULL,
    FOREIGN KEY (userID) REFERENCES users(userID)
);

CREATE TABLE IF NOT EXISTS Dietitian (
    id INT AUTO_INCREMENT PRIMARY KEY,   -- Auto-incremented primary key
    dietitianID VARCHAR(6) UNIQUE NOT NULL, -- Unique dietitianID (e.g., D00001, D00002, etc.)
    qualification TEXT,                  -- List of qualifications (stored as TEXT)
    licenseNo VARCHAR(50),               -- License number (string type)
    userID VARCHAR(6) NOT NULL,                          -- Foreign key referencing userID from the users table
    FOREIGN KEY (userID) REFERENCES users(userID)  -- Foreign key constraint to the users table
);

CREATE TABLE Elderly (
    id INT AUTO_INCREMENT PRIMARY KEY,  -- Auto-incremented primary key for Elderly table
    elderlyID VARCHAR(10) UNIQUE NOT NULL,  -- Unique elderlyID (e.g., E00001, E00002)
    profileID VARCHAR(6),                        -- Foreign key referencing Profile table
    dietPlanID VARCHAR(6),                       -- Foreign key referencing DietPlan table
    caretakerID VARCHAR(6),               -- Foreign key referencing Caretaker userID (VARCHAR(6))
    userID VARCHAR(6) NOT NULL,                    -- Foreign key referencing users table's userID (VARCHAR(6))
    FOREIGN KEY (profileID) REFERENCES Profile(profileID),  -- Foreign key to Profile table
    FOREIGN KEY (dietPlanID) REFERENCES DietPlan(dietPlanID), -- Foreign key to DietPlan table
    FOREIGN KEY (caretakerID) REFERENCES Caretaker(caretakerID), -- Foreign key to Caretaker table
    FOREIGN KEY (userID) REFERENCES users(userID) -- Foreign key to users table
);

CREATE TABLE IF NOT EXISTS Profile (
    id INT AUTO_INCREMENT PRIMARY KEY,  -- Auto-incremented primary key for Profile table
    profileID VARCHAR(10) UNIQUE NOT NULL,  -- Unique profileID (e.g., P00001, P00002)
    height DOUBLE,                      -- Height of the individual (double type)
    weight DOUBLE,                      -- Weight of the individual (double type)
    bmi DOUBLE,                         -- BMI (double type)
    allergies TEXT,                     -- Allergies (TEXT to store list of allergies, stored as a comma-separated string)
    healthCondition TEXT,               -- Health conditions (TEXT to store list of conditions, stored as a comma-separated string)
    caloriesLimit DOUBLE,               -- Calorie limit (double type)
    carbsLimit DOUBLE,                  -- Carbs limit (double type)
    sugarLimit DOUBLE,                  -- Sugar limit (double type)
    sodiumLimit DOUBLE,                 -- Sodium limit (double type)
    fibreRequirement DOUBLE,            -- Fibre requirement (double type)
    softFoodRequirement BOOLEAN,        -- Soft food requirement (boolean type)
    medicationList TEXT                 -- Medication list (TEXT to store list of medications as a comma-separated string)
);



