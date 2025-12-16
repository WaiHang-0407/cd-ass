CREATE TABLE IF NOT EXISTS users (
    userID INT AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY,
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
    adminID INT AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY, 
    userID INT NOT NULL,                          -- Changed to INT to match users table
    FOREIGN KEY (userID) REFERENCES users(userID)  -- Foreign key constraint
);

CREATE TABLE IF NOT EXISTS Caretaker (
    caretakerID INT AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY,
    relationship VARCHAR(255),
    emergencyContact INT,
    userID INT NOT NULL,                          -- Changed to INT to match users table
    FOREIGN KEY (userID) REFERENCES users(userID)
);

CREATE TABLE IF NOT EXISTS Dietitian (
    dietitianID INT AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY, 
    qualification TEXT,                  
    licenseNo VARCHAR(50),               
    userID INT NOT NULL,                          -- Changed to INT to match users table
    FOREIGN KEY (userID) REFERENCES users(userID)  
);

CREATE TABLE IF NOT EXISTS Profile (
    profileID INT AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY, 
    height DOUBLE,                     
    weight DOUBLE,                     
    allergies TEXT,                    
    healthCondition TEXT,              
    caloriesLimit DOUBLE DEFAULT NULL,  
    carbsLimit DOUBLE DEFAULT NULL,     
    sugarLimit DOUBLE DEFAULT NULL,     
    sodiumLimit DOUBLE DEFAULT NULL,    
    fibreRequirement DOUBLE DEFAULT NULL, 
    softFoodRequirement BOOLEAN,        
    halal BOOLEAN,                      
    medicationList TEXT DEFAULT NULL    
);

CREATE TABLE IF NOT EXISTS Recipe (
    recipeID INT AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY,
    ingredients TEXT, 
    calories DOUBLE,
    protein DOUBLE,
    carbs DOUBLE,
    fibre DOUBLE,
    fat DOUBLE,
    sodium DOUBLE
);

CREATE TABLE IF NOT EXISTS DietPlan (
    dietPlanID INT AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY,
    createdAt DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS Meal (
    mealID INT AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY,                
    mealType VARCHAR(255),                 
    totalCalories DOUBLE,                 
    totalFibre DOUBLE,                    
    totalProtein DOUBLE,                  
    totalCarbs DOUBLE,                    
    totalSodium DOUBLE,                   
    totalCholesterol DOUBLE,              
    dietPlanID INT,                       -- Changed to INT to match DietPlan table
    FOREIGN KEY (dietPlanID) REFERENCES DietPlan(dietPlanID)  
);

CREATE TABLE IF NOT EXISTS Food (
    foodID INT AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY,       
    foodName VARCHAR(255),       
    recipeID INT,                -- Changed to INT to match Recipe table
    mealID INT,                  -- Changed to INT to match Meal table
    FOREIGN KEY (recipeID) REFERENCES Recipe(recipeID),
    FOREIGN KEY (mealID) REFERENCES Meal(mealID)
);

CREATE TABLE IF NOT EXISTS DietPlanApproval (
    approvalID INT AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY,                
    state VARCHAR(50),                        
    dietitianID INT,                          -- Changed to INT to match Dietitian table
    approvalDate DATETIME,                    
    dietPlanID INT,                          
    FOREIGN KEY (dietitianID) REFERENCES Dietitian(dietitianID), 
    FOREIGN KEY (dietPlanID) REFERENCES DietPlan(dietPlanID)  
);

CREATE TABLE IF NOT EXISTS Elderly (
    elderlyID INT AUTO_INCREMENT UNIQUE NOT NULL PRIMARY KEY,
    profileID INT,                           -- Changed to INT to match Profile table
    dietPlanID INT,                          -- Changed to INT to match DietPlan table
    caretakerID INT,                         -- Changed to INT to match Caretaker table
    userID INT,                              -- Changed to INT to match users table
    FOREIGN KEY (profileID) REFERENCES Profile(profileID),
    FOREIGN KEY (dietPlanID) REFERENCES DietPlan(dietPlanID),
    FOREIGN KEY (caretakerID) REFERENCES Caretaker(caretakerID),
    FOREIGN KEY (userID) REFERENCES users(userID)
);
