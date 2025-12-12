CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userID VARCHAR(6) UNIQUE,
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
