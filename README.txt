CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userID VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(255),
    username VARCHAR(255) NOT NULL,
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
    userID VARCHAR(10) NOT NULL,                          -- Foreign key referencing userID from the users table
    FOREIGN KEY (userID) REFERENCES users(userID)  -- Foreign key constraint
);

CREATE TABLE IF NOT EXISTS Caretaker (
    id INT AUTO_INCREMENT PRIMARY KEY,
    caretakerID VARCHAR(10) UNIQUE NOT NULL,
    relationship VARCHAR(255),
    emergencyContact INT,
    userID VARCHAR(10) NOT NULL,
    FOREIGN KEY (userID) REFERENCES users(userID)
);

CREATE TABLE IF NOT EXISTS Dietitian (
    id INT AUTO_INCREMENT PRIMARY KEY,   -- Auto-incremented primary key
    dietitianID VARCHAR(10) UNIQUE NOT NULL, -- Unique dietitianID (e.g., D00001, D00002, etc.)
    qualification TEXT,                  -- List of qualifications (stored as TEXT)
    licenseNo VARCHAR(50),               -- License number (string type)
    userID VARCHAR(10) NOT NULL,                          -- Foreign key referencing userID from the users table
    FOREIGN KEY (userID) REFERENCES users(userID)  -- Foreign key constraint to the users table
);

CREATE TABLE IF NOT EXISTS Profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profileID VARCHAR(10) UNIQUE NOT NULL, -- Unique profileID (e.g., P00001, P00002)
    height DOUBLE,                      -- Height of the individual
    weight DOUBLE,                      -- Weight of the individual
    allergies TEXT,                     -- Allergies (TEXT to store list of allergies, stored as a comma-separated string)
    healthCondition TEXT,               -- Health conditions (TEXT to store list of conditions, stored as a comma-separated string)
    caloriesLimit DOUBLE DEFAULT NULL,  -- Calorie limit (default to NULL)
    carbsLimit DOUBLE DEFAULT NULL,     -- Carbs limit (default to NULL)
    sugarLimit DOUBLE DEFAULT NULL,     -- Sugar limit (default to NULL)
    sodiumLimit DOUBLE DEFAULT NULL,    -- Sodium limit (default to NULL)
    fibreRequirement DOUBLE DEFAULT NULL, -- Fibre requirement (default to NULL)
    softFoodRequirement BOOLEAN,        -- Soft food requirement
    halal BOOLEAN,                      -- Halal requirement
    medicationList TEXT DEFAULT NULL    -- Medication list (TEXT to store list of medications)
);

CREATE TABLE IF NOT EXISTS Recipe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipeID VARCHAR(10) UNIQUE NOT NULL,
    ingredients TEXT, 
    calories DOUBLE,
    protein DOUBLE,
    carbs DOUBLE,
    fibre DOUBLE,
    fat DOUBLE,
    sodium DOUBLE
);

CREATE TABLE IF NOT EXISTS DietPlan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dietPlanID VARCHAR(10) UNIQUE NOT NULL,
    createdAt DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS Meal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mealID VARCHAR(10) UNIQUE NOT NULL,                -- mealID as primary key
    mealType VARCHAR(255),                 -- meal type (e.g., Breakfast, Lunch)
    totalCalories DOUBLE,                  -- total calories in the meal
    totalFibre DOUBLE,                     -- total fibre in the meal
    totalProtein DOUBLE,                   -- total protein in the meal
    totalCarbs DOUBLE,                     -- total carbs in the meal
    totalSodium DOUBLE,                    -- total sodium in the meal
    totalCholesterol DOUBLE,               -- total cholesterol in the meal
    dietPlanID VARCHAR(10),                -- reference to the DietPlan this meal belongs to
    FOREIGN KEY (dietPlanID) REFERENCES DietPlan(dietPlanID)  -- foreign key to DietPlan table
);

CREATE TABLE IF NOT EXISTS Food (
    id INT AUTO_INCREMENT PRIMARY KEY,
    foodID VARCHAR(10) UNIQUE NOT NULL,       -- foodID as primary key
    foodName VARCHAR(255),       -- food name
    recipeID VARCHAR(10),         -- recipe for the food
    mealID VARCHAR(10),          -- foreign key to Meal table
    FOREIGN KEY (recipeID) REFERENCES Recipe(recipeID),
    FOREIGN KEY (mealID) REFERENCES Meal(mealID)
);

CREATE TABLE IF NOT EXISTS DietPlanApproval (
    id INT AUTO_INCREMENT PRIMARY KEY,
    approvalID VARCHAR(10) UNIQUE NOT NULL,                -- Unique ID for approval
    state VARCHAR(50),                        -- State of the diet plan (e.g., Pending, Approved, etc.)
    dietitianID VARCHAR(10),                  -- Foreign key to the Dietitian table
    approvalDate DATETIME,                    -- Date of approval
    dietPlanID VARCHAR(10),                   -- Foreign key to the DietPlan table
    FOREIGN KEY (dietitianID) REFERENCES Dietitian(dietitianID), -- Linking to Dietitian
    FOREIGN KEY (dietPlanID) REFERENCES DietPlan(dietPlanID)  -- Linking to DietPlan
);

CREATE TABLE IF NOT EXISTS Elderly (
    id INT AUTO_INCREMENT PRIMARY KEY,
    elderlyID VARCHAR(10) UNIQUE NOT NULL,
    profileID VARCHAR(10),
    dietPlanID VARCHAR(10),
    caretakerID VARCHAR(10),
    userID VARCHAR(10),
    FOREIGN KEY (profileID) REFERENCES Profile(profileID),
    FOREIGN KEY (dietPlanID) REFERENCES DietPlan(dietPlanID),
    FOREIGN KEY (caretakerID) REFERENCES Caretaker(caretakerID),
    FOREIGN KEY (userID) REFERENCES users(userID)
);