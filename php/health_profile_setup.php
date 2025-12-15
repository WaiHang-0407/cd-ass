<?php
include('../db.php'); // Include the database connection file
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id']; // Get the userID from session

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form values
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $medication = $_POST['medication'];
    $allergies = isset($_POST['allergies']) ? implode(",", $_POST['allergies']) : '';  // Allergies as a comma-separated string
    $healthConditions = isset($_POST['healthConditions']) ? implode(",", $_POST['healthConditions']) : ''; // Health conditions as a comma-separated string
    $softfood = isset($_POST['softfood']) ? $_POST['softfood'] : null;
    $halal = isset($_POST['halal']) ? $_POST['halal'] : null;
    $otherAllergies = isset($_POST['allergiesInput']) ? $_POST['allergiesInput'] : ''; // Other allergies input
    $otherConditions = isset($_POST['otherDisease']) ? $_POST['otherDisease'] : ''; // Other health conditions input

    // Step 1: Update user table with new details (name, age, gender)
    $updateUserQuery = "UPDATE users SET name = ?, age = ?, gender = ? WHERE userID = ?";
    $stmt = $_db->prepare($updateUserQuery);
    if (!$stmt->execute([$name, $age, $gender, $userID])) {
        echo "Error updating user table: " . implode(", ", $_db->errorInfo());
        exit();
    }

    // Step 2: Insert/Update the profile table
    $profileID = 'P' . str_pad($userID, 5, '0', STR_PAD_LEFT); // Generate profileID (example: P00001, P00002, etc.)
    $insertProfileQuery = "INSERT INTO Profile (profileID, height, weight, allergies, healthCondition, softFoodRequirement, halal, medicationList) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE 
                           height = VALUES(height), weight = VALUES(weight), allergies = VALUES(allergies), 
                           healthCondition = VALUES(healthCondition), softFoodRequirement = VALUES(softFoodRequirement), 
                           halal = VALUES(halal), medicationList = VALUES(medicationList)";
    $stmt = $_db->prepare($insertProfileQuery);
    if (!$stmt->execute([$profileID, $height, $weight, $allergies, $healthConditions, $softfood, $halal, $medication])) {
        echo "Error inserting/updating profile: " . implode(", ", $_db->errorInfo());
        exit();
    }

    // Step 3: Verify the profile was inserted correctly
    $checkProfileQuery = "SELECT * FROM Profile WHERE profileID = ?";
    $stmt = $_db->prepare($checkProfileQuery);
    $stmt->execute([$profileID]);
    $profileCheck = $stmt->fetch();
    if (!$profileCheck) {
        echo "Error: Profile not found after insertion.";
        exit();
    } else {
        echo "Profile inserted successfully with ID: " . $profileID;
    }

    // Step 4: Check if the userID exists in the elderly table
    $checkElderlyQuery = "SELECT * FROM elderly WHERE userID = ?";
    $stmt = $_db->prepare($checkElderlyQuery);
    $stmt->execute([$userID]);
    $elderlyCheck = $stmt->fetch();
    if (!$elderlyCheck) {
        echo "No record found for userID in elderly table.";
        exit();
    }

    // Step 5: Update the elderly table with the newly created profileID
    echo "Updating elderly table with profileID = $profileID for userID = $userID\n";

    // Update the elderly table using userID, no need for elderlyID
    $updateElderlyQuery = "UPDATE elderly SET profileID = ? WHERE userID = ?";
    $stmt = $_db->prepare($updateElderlyQuery);

    if (!$stmt->execute([$profileID, $userID])) {
        echo "Error updating elderly table: " . implode(", ", $_db->errorInfo());
        exit();
    }

    // Step 6: Verify the elderly table update
    $checkElderlyQuery = "SELECT * FROM elderly WHERE userID = ? AND profileID = ?";
    $stmt = $_db->prepare($checkElderlyQuery);
    $stmt->execute([$userID, $profileID]);
    $elderly = $stmt->fetch();

    if (!$elderly) {
        echo "Error: Elderly profileID not updated.";
        exit();
    } else {
        echo "Elderly table updated successfully with profileID: " . $profileID;
    }

    // Step 7: Redirect to a success page or profile page after the update
    header("Location: elderly_main.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Profile Setup</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6; /* Soft light background */
            color: #333; /* Dark text for readability */
            margin: 30px 0;
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Align content at the top */
            flex-direction: column;
            padding-top: 80px; /* Add space for fixed header */
            overflow-y: auto; /* Enable scrolling when content overflows */
            min-height: 100%; /* Ensure the body takes the full height and can scroll */
        }

        h2 {
            font-size: 2.2rem;
            color: #2980b9;
            margin-bottom: 15px;
        }

        /* Adjust the space to leave for the header */
        .form-container {
            margin-top: 50px; /* This is the space for the fixed header */
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        /* Container Styling */
        .container {
            background-color: #ffffff;
            padding: 20px 40px;
            border-radius: 12px;
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
            box-sizing: border-box;
        }

        /* Paragraph Styling */
        p {
            text-align: center;
            color: #777;
            font-size: 16px;
            margin-bottom: 30px;
        }

        /* Form Step Styling */
        .form-step {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column; /* Align label and input vertically */
            align-items: flex-start;
            position: relative; /* Enable absolute positioning for label */
        }

        /* Input Fields with Floating Labels */
        .input-container {
            position: relative;
            margin-bottom: 30px;
        }

        .input-container input {
            width: 100%;
            padding: 18px 20px;
            padding-top: 24px; /* Add space for the label */
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 1.1rem;
            outline: none;
            transition: 0.3s ease-in-out;
            box-sizing: border-box;
        }

        .input-container input:focus {
            border-color: #2980b9;
            box-shadow: 0 0 5px rgba(41, 128, 185, 0.3);
        }

        /* Label Styling (Floating Box) */
        .input-container label {
            position: absolute;
            top: 20px; /* Position label inside the input initially */
            left: 20px;
            color: #7f8c8d;
            font-size: 1.1rem;
            pointer-events: none;
            background-color: #ffffff; /* Background color for the floating box */
            padding: 0 5px; /* Padding to create the box effect */
            transition: 0.3s ease all; /* Smooth transition */
            z-index: 1;
        }

        /* Move label above the input when focused or text is entered */
        .input-container input:focus + label,
        .input-container input:not(:placeholder-shown) + label {
            top: -10px;
            left: 20px;
            color: #2980b9;
            font-size: 1rem;
        }

        /* Hide the Default Eye Icon */
        .input-container input::-webkit-outer-spin-button,
        .input-container input::-webkit-inner-spin-button,
        .input-container input[type="password"] {
            appearance: none;
            -webkit-appearance: none; /* Hide the default eye icon in Webkit browsers (Chrome, Safari) */
        }

        /* General Styles for Radio Button Group */
        .gender-container {
            position: relative;
            margin-bottom: 30px;
            text-align: left;
            display: flex;
        }

        .gender-label {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin: 0 50px 0 23px;
            text-align: left;
        }

        .gender-options {
            display: flex;                  /* Using flexbox to align items horizontally */
            justify-content: space-between; /* Distribute space between radio buttons */
            gap: 50px;                      /* Reduce the space between radio buttons */
            align-items: center;            /* Vertically align the radio buttons */
        }

        .gender-option {
            display: flex;
            align-items: center;            /* Align the radio button with the label */
            gap: 10px;                       /* Space between radio button and label */
            font-size: 1.1rem;
            color: #7f8c8d;
            cursor: pointer;
        }

        .gender-option input {
            width: 20px;
            height: 20px;
            border-radius: 50%;             /* Make the radio button round */
            border: 2px solid #2980b9;     /* Blue border */
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .gender-option input:checked {
            background-color: #2980b9;     /* Blue when checked */
            border-color: #2980b9;
        }

        .gender-option input:checked + .role-label-text {
            color: #2980b9;                /* Text color when the radio is checked */
        }

        .gender-option:hover input {
            background-color: #f0f0f0;     /* Light gray background on hover */
        }

        .gender-label-text {
            font-size: 1.1rem;
            color: #7f8c8d;
            transition: color 0.3s ease;
        }

        .gender-option:hover .gender-label-text {
            color: #2980b9;                /* Change text color on hover */
        }

        .allergies-checkbox {
            display: flex;
            flex-direction: column; /* Stack checkboxes vertically */
            gap: 15px; /* Adds space between checkboxes */
            width: 100%;
        }

        .allergies-option {
            display: flex;
            padding: 5px 20px;
        }

        /* Styling for individual checkbox and label */
        .allergies-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            border: 2px solid #2980b9; /* Blue border for checkboxes */
            cursor: pointer;
            transition: all 0.3s ease;
        }

        /* Checkbox Label Styling */
        .allergies-checkbox label {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin-left: 10px; /* Space between checkbox and label */
            transition: color 0.3s ease;
            cursor: pointer; /* Ensure the label also feels clickable */
        }

        /* Change label text color when checkbox is checked */
        .allergies-checkbox input[type="checkbox"]:checked + label {
            color: #2980b9; /* Blue text when selected */
        }

        /* Checkbox hover effect */
        .allergies-checkbox input[type="checkbox"]:hover {
            background-color: #f0f0f0; /* Light gray background on hover */
            border-color: #2980b9; /* Ensure border stays blue */
        }

        /* When checkbox is checked, change the background */
        .allergies-checkbox input[type="checkbox"]:checked {
            background-color: #2980b9; /* Blue background when checked */
            border-color: #2980b9; /* Blue border when checked */
            box-shadow: 0 0 5px rgba(41, 128, 185, 0.4); /* Optional glow effect */
        }

        /* For checked checkboxes, adjust the label color */
        .allergies-checkbox input[type="checkbox"]:checked + label {
            color: #2980b9; /* Change label text color to blue when checked */
        }

        .softfood-container {
            position: relative;
            margin-bottom: 30px;
            text-align: left;
        }

        .softfood-label {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin: 0 50px 0 23px;
            text-align: left;
        }

        .softfood-options {
            display: flex;                  /* Using flexbox to align items horizontally */
            gap: 50px;                      /* Reduce the space between radio buttons */
            align-items: center;            /* Vertically align the radio buttons */
            margin: 20px;
        }

        .softfood-option {
            display: flex;
            align-items: center;            /* Align the radio button with the label */
            gap: 10px;                       /* Space between radio button and label */
            font-size: 1.1rem;
            color: #7f8c8d;
            cursor: pointer;
        }

        .softfood-option input {
            width: 20px;
            height: 20px;
            border-radius: 50%;             /* Make the radio button round */
            border: 2px solid #2980b9;     /* Blue border */
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .softfood-option input:checked {
            background-color: #2980b9;     /* Blue when checked */
            border-color: #2980b9;
        }

        .softfood-option input:checked + .role-label-text {
            color: #2980b9;                /* Text color when the radio is checked */
        }

        .softfood-option:hover input {
            background-color: #f0f0f0;     /* Light gray background on hover */
        }

        .softfood-label-text {
            font-size: 1.1rem;
            color: #7f8c8d;
            transition: color 0.3s ease;
        }

        .softfood-option:hover .gender-label-text {
            color: #2980b9;                /* Change text color on hover */
        }

        /* Styling for the Checkbox Container */
        .checkbox-container {
            display: flex;
            flex-direction: column; /* Stack the checkboxes vertically */
            gap: 15px; /* Adds space between checkboxes */
            width: 100%;
            margin-left: 0; /* Align checkboxes to the left */
            margin-bottom: 20px;
        }

        .checkbox-container label {
            font-size: 1.1rem;
            color: #7f8c8d;
            text-align: left;
            margin-left: 23px;
        }

        /* Styling for the Checkbox Group (Health Conditions) */
        .condition-checkbox {
            display: flex;
            flex-direction: column; /* Stack checkboxes vertically */
            gap: 15px; /* Adds space between checkboxes */
            width: 100%;
        }

        .condition-option {
            display: flex;
            padding: 5px 20px;
        }

        /* Styling for individual checkbox and label */
        .condition-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            border: 2px solid #2980b9; /* Blue border for checkboxes */
            cursor: pointer;
            transition: all 0.3s ease;
        }

        /* Checkbox Label Styling */
        .condition-checkbox label {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin-left: 23px; /* Space between checkbox and label */
            transition: color 0.3s ease;
            cursor: pointer; /* Ensure the label also feels clickable */
        }

        /* Change label text color when checkbox is checked */
        .condition-checkbox input[type="checkbox"]:checked + label {
            color: #2980b9; /* Blue text when selected */
        }

        /* Checkbox hover effect */
        .condition-checkbox input[type="checkbox"]:hover {
            background-color: #f0f0f0; /* Light gray background on hover */
            border-color: #2980b9; /* Ensure border stays blue */
        }

        /* When checkbox is checked, change the background */
        .condition-checkbox input[type="checkbox"]:checked {
            background-color: #2980b9; /* Blue background when checked */
            border-color: #2980b9; /* Blue border when checked */
            box-shadow: 0 0 5px rgba(41, 128, 185, 0.4); /* Optional glow effect */
        }

        /* For checked checkboxes, adjust the label color */
        .condition-checkbox input[type="checkbox"]:checked + label {
            color: #2980b9; /* Change label text color to blue when checked */
        }

        .halal-container {
            position: relative;
            margin-bottom: 30px;
            text-align: left;
        }

        .halal-label {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin: 0 50px 0 23px;
            text-align: left;
        }

        .halal-options {
            display: flex;                  /* Using flexbox to align items horizontally */
            gap: 50px;                      /* Reduce the space between radio buttons */
            align-items: center;            /* Vertically align the radio buttons */
            margin: 20px;
        }

        .halal-option {
            display: flex;
            align-items: center;            /* Align the radio button with the label */
            gap: 10px;                       /* Space between radio button and label */
            font-size: 1.1rem;
            color: #7f8c8d;
            cursor: pointer;
        }

        .halal-option input {
            width: 20px;
            height: 20px;
            border-radius: 50%;             /* Make the radio button round */
            border: 2px solid #2980b9;     /* Blue border */
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .halal-option input:checked {
            background-color: #2980b9;     /* Blue when checked */
            border-color: #2980b9;
        }

        .halal-option input:checked + .role-label-text {
            color: #2980b9;                /* Text color when the radio is checked */
        }

        .halal-option:hover input {
            background-color: #f0f0f0;     /* Light gray background on hover */
        }

        .halal-label-text {
            font-size: 1.1rem;
            color: #7f8c8d;
            transition: color 0.3s ease;
        }

        .halal-option:hover .gender-label-text {
            color: #2980b9;                /* Change text color on hover */
        }

        #otherInput {
            padding: 0px 20px;
        }

        #otherInput input {
            width: 100%;
            padding: 10px; /* Vertical padding for height */
            font-size: 1.1rem;
            border: none; /* Remove all borders */
            border-bottom: 2px solid #2980b9; /* Blue bottom border */
            outline: none; /* Remove focus outline */
            background-color: transparent; /* Make background transparent */
            transition: 0.3s ease-in-out;
            box-sizing: border-box;
        }

        #otherInput input:focus {
            border-bottom: 2px solid #4CAF50; /* Change border color on focus */
        }

        #otherAllergiesInput {
            padding: 0px 20px;
        }

        #otherAllergiesInput input {
            width: 100%;
            padding: 10px; /* Vertical padding for height */
            font-size: 1.1rem;
            border: none; /* Remove all borders */
            border-bottom: 2px solid #2980b9; /* Blue bottom border */
            outline: none; /* Remove focus outline */
            background-color: transparent; /* Make background transparent */
            transition: 0.3s ease-in-out;
            box-sizing: border-box;
        }

        #otherAllergiesInput input:focus {
            border-bottom: 2px solid #4CAF50; /* Change border color on focus */
        }

        /* Button Styling */
        .btn-submit {
            background-color: #2980b9;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease; /* Smooth hover effect */
        }

        .btn-submit:hover {
            background-color: #1c5e87;
        }

        @media (max-width: 600px) {
            .container {
                width: 90%;
                padding: 15px;
            }

            h1 {
                font-size: 20px;
            }

            label, input {
                font-size: 14px;
            }
        }

    </style>
</head>
<body>
    <div class="form-container">
        <div class="container">
            <h2>Health Profile Setup</h2>
            <p>Please answer the questions to complete your profile.</p>

            <form action="health_profile_setup.php" method="POST" onsubmit="return validateForm()">
                <div class="input-container">
                    <input type="text" id="name" name="name" required placeholder=" ">
                    <label for="name">Name</label>
                </div>

                <div class="input-container">
                    <input type="number" id="age" name="age" required placeholder=" ">
                    <label for="age">Age</label>
                </div>

                <div class="gender-container">
                    <label for="gender" class="gender-label">Gender :</label>
                    <div class="gender-options">
                        <div class="gender-option">
                            <input type="radio" id="male" name="gender" value="male" required>
                            <span class="gender-label-text">Male</span>
                        </div>
                        <div class="gender-option">
                            <input type="radio" id="female" name="gender" value="female" required>
                            <span class="gender-label-text">Female</span>
                        </div>
                    </div>
                </div>

                <div class="input-container">
                    <input type="number" id="height" name="height" required placeholder="">
                    <label for="height">Height</label>
                </div>

                <div class="input-container">
                    <input type="number" id="weight" name="weight" required placeholder="">
                    <label for="weight">Weight</label>
                </div>

                <div class="input-container">
                    <input type="text" id="medication" name="medication" placeholder="">
                    <label for="medication">Medications (if any):</label>
                </div>

                <div class="checkbox-container">
                    <label for="allergies">Allergies:</label>
                    <div class="allergies-checkbox">
                        <div class="allergies-option">
                            <input type="checkbox" id="peanuts" name="allergies[]" value="Peanuts" onclick="handleCheckboxChange()">
                            <label for="peanuts">Peanuts</label>
                        </div>
                        
                        <div class="allergies-option">
                            <input type="checkbox" id="soy" name="allergies[]" value="Soy" onclick="handleCheckboxChange()">
                            <label for="soy">Soy</label>
                        </div>
                        
                        <div class="allergies-option">
                            <input type="checkbox" id="eggs" name="allergies[]" value="Eggs" onclick="handleCheckboxChange()">
                            <label for="eggs">Eggs</label>
                        </div>
                        
                        <div class="allergies-option">
                            <input type="checkbox" id="fish" name="allergies[]" value="Fish" onclick="handleCheckboxChange()">
                            <label for="fish">Fish</label>
                        </div>
                        
                        <div class="allergies-option">
                            <input type="checkbox" id="milk" name="allergies[]" value="Milk" onclick="handleCheckboxChange()">
                            <label for="milk">Milk</label>
                        </div>

                        <div class="allergies-option">
                            <input type="checkbox" id="sesame" name="allergies[]" value="Sesame" onclick="handleCheckboxChange()">
                            <label for="sesame">Sesame</label>
                        </div>

                        <div class="allergies-option">
                            <input type="checkbox" id="noneAllergies" name="allergies[]" value="None" onclick="handleCheckboxChange()">
                            <label for="noneAllergies">None</label>
                        </div>

                        <div class="allergies-option">
                            <input type="checkbox" id="otherAllergies" name="allergies[]" value="Other Allergies" onclick="handleCheckboxChange(); toggleOtherAllergiesInput()">
                            <label for="otherAllergies">Other:</label>
                        </div>
                        <div id="otherAllergiesInput" style="display: none;">
                            <input type="text" id="allergiesInput" name="allergiesInput" placeholder="Enter allergies">
                        </div>
                    </div>
                </div>

                <div class="checkbox-container">
                    <label for="healthCondition">Health Condition:</label>
                    <div class="condition-checkbox">
                        <div class="condition-option">
                            <input type="checkbox" id="diabetes" name="healthConditions[]" value="Diabetes" onclick="handleCheckboxChange()">
                            <label for="diabetes">Diabetes</label>
                        </div>
                        
                        <div class="condition-option">
                            <input type="checkbox" id="hypertension" name="healthConditions[]" value="Hypertension" onclick="handleCheckboxChange()">
                            <label for="hypertension">High Blood Pressure</label>
                        </div>
                        
                        <div class="condition-option">
                            <input type="checkbox" id="heartDisease" name="healthConditions[]" value="Heart Disease" onclick="handleCheckboxChange()">
                            <label for="heartDisease">Heart Disease</label>
                        </div>
                        
                        <div class="condition-option">
                            <input type="checkbox" id="obesity" name="healthConditions[]" value="Obesity" onclick="handleCheckboxChange()">
                            <label for="obesity">Obesity</label>
                        </div>
                        
                        <div class="condition-option">
                            <input type="checkbox" id="chronicKidneyDisease" name="healthConditions[]" value="Chronic Kidney Disease" onclick="handleCheckboxChange()">
                            <label for="chronicKidneyDisease">Chronic Kidney Disease</label>
                        </div>

                        <div class="condition-option">
                            <input type="checkbox" id="noneCondition" name="healthConditions[]" value="None" onclick="handleCheckboxChange()">
                            <label for="noneCondition">None</label>
                        </div>

                        <div class="condition-option">
                            <input type="checkbox" id="other" name="healthConditions[]" value="Other" onclick="handleCheckboxChange(); toggleOtherInput()">
                            <label for="other">Other:</label>
                        </div>
                        <div id="otherInput" style="display: none;">
                            <input type="text" id="otherDisease" name="otherDisease" placeholder="Enter health condition">
                        </div>
                    </div>
                </div>

                <div class="softfood-container">
                    <label for="softfood" class="softfood-label">Do you require soft food?</label>
                    <div class="softfood-options">
                        <div class="softfood-option">
                            <input type="radio" id="yes" name="softfood" value="yes">
                            <span class="softfood-label-text">Yes</span>
                        </div>
                        <div class="softfood-option">
                            <input type="radio" id="no" name="softfood" value="no">
                            <span class="softfood-label-text">No</span>
                        </div>
                    </div>
                </div>

                <div class="halal-container">
                    <label for="halal" class="halal-label">Do you require halal food?</label>
                    <div class="halal-options">
                        <div class="halal-option">
                            <input type="radio" id="yes-halal" name="halal" value="yes">
                            <span class="halal-label-text">Yes</span>
                        </div>
                        <div class="halal-option">
                            <input type="radio" id="no-halal" name="halal" value="no">
                            <span class="halal-label-text">No</span>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
    

    <script>
        function toggleOtherInput() {
            const otherConditionCheckbox = document.getElementById("other");
            const otherConditionInput = document.getElementById("otherInput");
            
            // Show the input field when "Other" checkbox is checked and set it to required
            if (otherConditionCheckbox.checked) {
                otherConditionInput.style.display = "block";
                otherConditionInput.setAttribute("required", "required");
            } else {
                otherConditionInput.style.display = "none";
                otherConditionInput.removeAttribute("required");
            }
        }

        // Ensure the input is validated when the form is submitted
        function validateForm() {
            const otherConditionCheckbox = document.getElementById("other");
            const otherConditionInput = document.getElementById("otherDisease");
            const otherAllergiesCheckbox = document.getElementById("otherAllergies");
            const otherAllergiesInput = document.getElementById("allergiesInput");

            // If the "Other" checkbox is checked and the input is empty, show an alert
            if (otherConditionCheckbox.checked && otherConditionInput.value.trim() === "") {
                alert("Please specify the health condition.");
                return false; // Prevent form submission
            } 

            if (otherAllergiesCheckbox.checked && otherAllergiesInput.value.trim() === "") {
                alert("Please specify the allergy.");
                return false;
            }

            return true; // Allow form submission
        }

        function toggleOtherAllergiesInput() {
            const otherConditionCheckbox = document.getElementById("otherAllergies");
            const otherConditionInput = document.getElementById("otherAllergiesInput");

            // Show the input field when "Other" checkbox is checked
            if (otherConditionCheckbox.checked) {
                otherConditionInput.style.display = "block";
                otherConditionInput.setAttribute("required", "required");
            } else {
                otherConditionInput.style.display = "none";
                otherConditionInput.removeAttribute("required");
            }
        }

        // Function to handle the interaction between checkboxes
        function handleCheckboxChange() {
            const noneAllergiesCheckbox = document.getElementById("noneAllergies");
            const otherAllergiesCheckbox = document.getElementById("otherAllergies");
            const noneConditionCheckbox = document.getElementById("noneCondition");
            const otherConditionCheckbox = document.getElementById("other");
            const healthConditionCheckboxes = document.querySelectorAll('.condition-checkbox input[type="checkbox"]');
            const allergiesCheckboxes = document.querySelectorAll('.allergies-checkbox input[type="checkbox"]');

            // If "None" checkbox is checked, uncheck all other checkboxes except "None"
            if (noneAllergiesCheckbox.checked) {
                allergiesCheckboxes.forEach((checkbox) => {
                    if (checkbox !== noneAllergiesCheckbox) {
                        checkbox.checked = false;
                    }
                });
            }

            // If "Other" checkbox is checked, uncheck "None"
            if (otherAllergiesCheckbox.checked) {
                noneAllergiesCheckbox.checked = false;
            }

            if (noneConditionCheckbox.checked) {
                healthConditionCheckboxes.forEach((checkbox) => {
                    if (checkbox !== noneConditionCheckbox) {
                        checkbox.checked = false;
                    }
                });
            }

            // If "Other" checkbox is checked, uncheck "None"
            if (otherConditionCheckbox.checked) {
                noneConditionCheckbox.checked = false;
            }
        }

        // Trigger the `handleHealthConditionChange` function when a checkbox is clicked
        document.querySelectorAll('.condition-checkbox input[type="checkbox"]').forEach((checkbox) => {
            checkbox.addEventListener('change', handleCheckboxChange);
        });

    </script>

    <?php include('_foot.php'); ?>
</body>
</html>
