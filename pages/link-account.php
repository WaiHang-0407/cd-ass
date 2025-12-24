<?php
// pages/link-account.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../classes/SubUsers.php';

requireRole(['Caretaker', 'User', 'Elderly']); // Allow all just in case, logic handles checks

$user = getCurrentUser();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (!empty($email)) {
        // Find user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $targetUser = $stmt->fetch();

        if ($targetUser) {
            // Logic handled in Caretaker class usually, but since we are refacting on the fly:
            // We can instantiate a Caretaker object even if current user is just 'User' role in session,
            // IF we assume they have the capability. 
            // Better: Check role. Only Caretakers can link for now as per req?
            // "Caretaker role to a normal user that can link" -> Yes.

            // RECIPROCAL LINKING LOGIC
            // 1. Check if they matched me (They requested ME)
            $stmt = $pdo->prepare("SELECT * FROM link_requests WHERE initiatorID = ? AND targetID = ?");
            $stmt->execute([$targetUser['userID'], $user->userID]); // Them -> Me
            $match = $stmt->fetch();

            if ($match) {
                // Mutual Handshake Confirmed!
                // Establish the link based on Roles

                // Determine who is Caretaker and Patient
                $caretakerID = null;
                $patientID = null;

                // Priority: Explicit Caretaker Role
                if ($user->role == 'Caretaker') {
                    $caretakerID = $user->userID;
                    $patientID = $targetUser['userID'];
                } elseif ($targetUser['role'] == 'Caretaker') {
                    $caretakerID = $targetUser['userID'];
                    $patientID = $user->userID;
                } elseif ($user->role == 'Elderly') {
                    $caretakerID = $targetUser['userID'];
                    $patientID = $user->userID;
                } elseif ($targetUser['role'] == 'Elderly') {
                    $caretakerID = $user->userID;
                    $patientID = $targetUser['userID'];
                } else {
                    // Ambiguous Roles (Both 'User').
                    // Assume the Initiator of the Request is the Caretaker.
                    if ($match['initiatorID'] == $user->userID) {
                        // I initiated the request (and now linking logic is running? Wait. 
                        // If I initiated, I am waiting. I cannot Confirm my own request.
                        // So correct: this block only runs if I am Confirming THEIR request.
                        // So I am Target. They are Initiator.
                        // If They initiated, They are Caretaker.
                        $caretakerID = $targetUser['userID'];
                        $patientID = $user->userID;
                    } else {
                        // They initiated. They are Caretaker.
                        $caretakerID = $targetUser['userID'];
                        $patientID = $user->userID;
                    }

                    // Wait. Logic check:
                    // If I am submitting this form, "Target" is the email I entered.
                    // If Match Found, `initiatorID` MUST be Them (Target).
                    // Because I (Me) queried for `initiatorID = Them`.
                    // So `match` implies THEY initiated.
                    // So They are Caretaker (by default assumption).
                    $caretakerID = $targetUser['userID'];
                    $patientID = $user->userID;
                }

                // Correction: What if Patient initiated the request? (P -> C).
                // Then I (Caretaker) am confirming.
                // Me = C. Them = P.
                // Me Role = 'Caretaker' (Caught by top block).
                // If Me Role = 'User' (Ambiguous).
                // Them Role = 'User'.
                // They initiated.
                // If P initiated, Caretaker is Me (Target).
                // If C initiated, Caretaker is Them (Initiator).
                // Without Roles, we can't know for sure who is who unless we assume "Requestor = Caretaker".
                // User requirement: "Caretaker enter Patient email". (C -> P).
                // "Patient enter Caretaker email". (P -> C).
                // If P enters C email first. Request is P -> C.
                // C confirms. Match found (Init=P).
                // If we assume Init=Caretaker, then P=Caretaker? Wrong.

                // Safe Bet: Look at the `link_requests` table? No extra info there.
                // Look at the DB again.
                // The User likely registered as "Caretaker" role so top block should catch it.
                // If they registered as "User", they are not a Caretaker.
                // So Top Block is reliable IF they used correct Registration form.
                // User said "I am seeing userID at caretaker column".
                // This means `caretakerID` got populated with the wrong ID.
                // This implies the code thought "Me" was Caretaker when I was Patient (or vice versa).

                // Let's FORCE a check on the `caretakers` table just in case Role string is 'User' but they exist in `caretakers`.
                // Or `elderly` table.

                // Check if Me is in Caretakers table
                $stmtC = $pdo->prepare("SELECT 1 FROM caretakers WHERE caretakerID = ?");
                $stmtC->execute([$user->userID]);
                $meIsCaretaker = $stmtC->fetchColumn();

                // Check if Them is in Caretakers table
                $stmtC->execute([$targetUser['userID']]);
                $themIsCaretaker = $stmtC->fetchColumn();

                if ($meIsCaretaker) {
                    $caretakerID = $user->userID;
                    $patientID = $targetUser['userID'];
                } elseif ($themIsCaretaker) {
                    $caretakerID = $targetUser['userID'];
                    $patientID = $user->userID;
                } else {
                    // Fallback if neither found in caretakers table
                    // Assume based on Role string
                    if ($user->role == 'Caretaker') {
                        $caretakerID = $user->userID;
                        $patientID = $targetUser['userID'];
                    } elseif ($targetUser['role'] == 'Caretaker') {
                        $caretakerID = $targetUser['userID'];
                        $patientID = $user->userID;
                    } else {
                        // Total ambiguity. Default to Me = Caretaker (as per original logic logic if I am confirming)
                        // BUT if I am confirming a request from someone else, and I am not Caretaker...
                        // If I am Patient confirming Caretaker's request.
                        // They initiated. They are Caretaker.
                        $caretakerID = $targetUser['userID'];
                        $patientID = $user->userID;
                    }
                }

                // Insert into user_links
                // Using generic PDO insert here or the class method if fits
                $linkStmt = $pdo->prepare("INSERT INTO user_links (caretakerID, patientID) VALUES (?, ?)");
                try {
                    $linkStmt->execute([$caretakerID, $patientID]);

                    // Cleanup Request
                    $delStmt = $pdo->prepare("DELETE FROM link_requests WHERE requestID = ?");
                    $delStmt->execute([$match['requestID']]);

                    $msg = "<div class='alert alert-success'>Success! Account linked. You can now switch views in Dashboard.</div>";
                } catch (Exception $e) {
                    $msg = "<div class='alert alert-danger'>Error creating link. Link might already exist.</div>";
                }

            } else {
                // 2. No match yet, create request (Me -> Them)
                // Check if I already requested
                $check = $pdo->prepare("SELECT * FROM link_requests WHERE initiatorID = ? AND targetID = ?");
                $check->execute([$user->userID, $targetUser['userID']]);

                if ($check->rowCount() > 0) {
                    $msg = "<div class='alert alert-info'>Request already sent. Waiting for them to enter your email.</div>";
                } else {
                    $ins = $pdo->prepare("INSERT INTO link_requests (initiatorID, targetID) VALUES (?, ?)");
                    $ins->execute([$user->userID, $targetUser['userID']]);
                    $msg = "<div class='alert alert-warning'>Request sent! Ask " . htmlspecialchars($targetUser['name']) . " to enter <b>your email</b> to confirm.</div>";
                }
            }
        } else {
            $msg = "<div class='alert alert-danger'>User with this email not found.</div>";
        }
    }
}

include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Link an Account</h5>
            </div>
            <div class="card-body">
                <p>Link an elderly account to view their progress and diet plans.</p>
                <?= $msg ?>
                <form method="post">
                    <div class="mb-3">
                        <label>User's Email Address</label>
                        <input type="email" name="email" class="form-control" required
                            placeholder="e.g. grandpa@example.com">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Link Account</button>
                </form>
            </div>
        </div>
        <div class="mt-3 text-center">
            <a href="dashboard.php">&larr; Back to Dashboard</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>