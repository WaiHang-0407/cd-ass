<?php
// pages/messages.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../classes/Message.php';

requireLogin();
$user = getCurrentUser();
$msgSystem = new Message($pdo);

$chatPartnerID = null;
$chatPartnerName = '';

// Determine Chat Partner
if ($user->role == 'User' || $user->role == 'Elderly') {
    // Patient: Always chat with assigned dietitian
    $stmt = $pdo->prepare("SELECT assignedDietitianID FROM elderly WHERE elderlyID = ?");
    $stmt->execute([$user->userID]);
    $assignedID = $stmt->fetchColumn();

    if (!$assignedID) {
        $error = "You have not been assigned a dietitian yet.";
    } else {
        $chatPartnerID = $assignedID;
        // Fetch Name
        $nStmt = $pdo->prepare("SELECT name FROM users WHERE userID = ?");
        $nStmt->execute([$assignedID]);
        $chatPartnerName = $nStmt->fetchColumn();
    }

} elseif ($user->role == 'Dietitian') {
    // Dietitian: Chat with selected patient
    $chatPartnerID = $_GET['chat_with'] ?? null;

    if ($chatPartnerID) {
        $nStmt = $pdo->prepare("SELECT name FROM users WHERE userID = ?");
        $nStmt->execute([$chatPartnerID]);
        $chatPartnerName = $nStmt->fetchColumn();
    }
} elseif ($user->role == 'Caretaker') {
    // Caretaker: Chat with Patient's Dietitian
    // We expect ?patient_id=XYZ to know WHICH patient we are talking about
    // Or we defaults to the first linked patient if none provided (or handled in dashboard link)

    $targetPatientID = $_GET['patient_id'] ?? null;
    $patientName = "";

    if ($targetPatientID) {
        // Verify link exists
        // (Assuming we trust the link for now or add check)

        // Fetch Patient Name
        $patStmt = $pdo->prepare("SELECT name FROM users WHERE userID = ?");
        $patStmt->execute([$targetPatientID]);
        $patientName = $patStmt->fetchColumn();

        // Fetch Dietitian
        $stmt = $pdo->prepare("SELECT assignedDietitianID FROM elderly WHERE elderlyID = ?");
        $stmt->execute([$targetPatientID]);
        $assignedID = $stmt->fetchColumn();

        if ($assignedID) {
            $chatPartnerID = $assignedID;
            // Fetch Dietitian Name
            $nStmt = $pdo->prepare("SELECT name FROM users WHERE userID = ?");
            $nStmt->execute([$assignedID]);
            $dietitianName = $nStmt->fetchColumn();
            $chatPartnerName = "$dietitianName (Dietitian for $patientName)";
        } else {
            $error = "This patient has not been assigned a dietitian yet.";
        }
    } else {
        $error = "Please select a patient from the dashboard to message their dietitian.";
    }
}

// Handle Send
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && $chatPartnerID) {
    $msgSystem->send($user->userID, $chatPartnerID, $_POST['message']);
    // Redirect to self to prevent resubmission
    $redirectUrl = "messages.php";
    if ($user->role == 'Dietitian') {
        $redirectUrl .= "?chat_with=" . $chatPartnerID;
    }
    header("Location: $redirectUrl");
    exit;
}

include '../includes/header.php';
?>

<div class="row h-100">
    <!-- Sidebar (For Dietitians only) -->
    <?php if ($user->role == 'Dietitian'): ?>
        <div class="col-md-4 border-end overflow-auto" style="max-height: 500px;">
            <div class="p-2 sticky-top bg-white border-bottom z-1">
                <input type="text" id="userSearch" class="form-control form-control-sm"
                    placeholder="Search patients or caretakers...">
            </div>
            <h5 class="mb-3 mt-3 text-primary px-2">Patients</h5>
            <div class="list-group mb-4 shadow-sm">
                <?php
                // Fetch assigned patients
                $pStmt = $pdo->prepare("
                    SELECT u.userID, u.name 
                    FROM users u 
                    JOIN elderly e ON u.userID = e.elderlyID 
                    WHERE e.assignedDietitianID = ?
                ");
                $pStmt->execute([$user->userID]);
                $patients = $pStmt->fetchAll();

                if (empty($patients))
                    echo "<div class='p-2 text-muted small'>No patients assigned.</div>";

                foreach ($patients as $p):
                    $active = ($p['userID'] == $chatPartnerID) ? 'active' : '';
                    ?>
                    <a href="messages.php?chat_with=<?= $p['userID'] ?>"
                        class="list-group-item list-group-item-action <?= $active ?>">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <h6 class="mb-0"><?= htmlspecialchars($p['name']) ?></h6>
                            <i class="bi bi-person text-secondary"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <h5 class="mb-3 text-secondary">Caretakers</h5>
            <div class="list-group shadow-sm">
                <?php
                // Fetch Caretakers
                $cStmt = $pdo->prepare("
                    SELECT DISTINCT u.userID, u.name, u_patient.name as patientName
                    FROM users u
                    JOIN user_links ce ON u.userID = ce.caretakerID
                    JOIN elderly e ON ce.patientID = e.elderlyID
                    JOIN users u_patient ON e.elderlyID = u_patient.userID
                    WHERE e.assignedDietitianID = ?
                ");
                $cStmt->execute([$user->userID]);
                $caretakers = $cStmt->fetchAll();

                if (empty($caretakers))
                    echo "<div class='p-2 text-muted small'>No caretakers linked.</div>";

                foreach ($caretakers as $c):
                    $active = ($c['userID'] == $chatPartnerID) ? 'active' : '';
                    ?>
                    <a href="messages.php?chat_with=<?= $c['userID'] ?>"
                        class="list-group-item list-group-item-action <?= $active ?>">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?= htmlspecialchars($c['name']) ?></h6>
                            <small class="badge bg-light text-dark border">For:
                                <?= htmlspecialchars($c['patientName']) ?></small>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Chat Area -->
    <div class="<?= ($user->role == 'Dietitian') ? 'col-md-8' : 'col-12' ?>">
        <?php if ($chatPartnerID): ?>
            <div class="card shadow-sm h-100" style="min-height: 500px;">
                <div class="card-header bg-primary text-white">
                    Chat with <strong><?= htmlspecialchars($chatPartnerName) ?></strong>
                </div>
                <div class="card-body d-flex flex-column" style="height: 400px; overflow-y: auto;" id="chatBox">
                    <?php
                    $messages = $msgSystem->getConversation($user->userID, $chatPartnerID);
                    if (empty($messages)): ?>
                        <div class="text-center text-muted mt-5">No messages yet. Start the conversation!</div>
                    <?php else:
                        foreach ($messages as $msg):
                            $isMe = ($msg['senderID'] == $user->userID);
                            $align = $isMe ? 'align-self-end text-end' : 'align-self-start';
                            $color = $isMe ? 'bg-primary text-white' : 'bg-light text-dark border';
                            ?>
                            <div class="d-flex flex-column <?= $align ?> mb-2" style="max-width: 70%;">
                                <div class="p-2 rounded <?= $color ?>">
                                    <?= htmlspecialchars($msg['message']) ?>
                                </div>
                                <small class="text-muted"
                                    style="font-size: 0.7rem;"><?= date('H:i', strtotime($msg['sentAt'])) ?></small>
                            </div>
                        <?php endforeach; endif; ?>
                </div>
                <div class="card-footer">
                    <form method="post" class="d-flex">
                        <input type="text" name="message" class="form-control me-2" placeholder="Type a message..." required
                            autocomplete="off">
                        <button type="submit" class="btn btn-primary">Send <i class="bi bi-send"></i></button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info mt-3">
                <?php if ($user->role == 'Dietitian'): ?>
                    Please select a patient to start chatting.
                <?php else: ?>
                    <?= $error ?? "Connecting to dietitian..." ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Scroll to bottom
    const chatBox = document.getElementById('chatBox');
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Search Filter
    const searchInput = document.getElementById('userSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const filter = this.value.toLowerCase();
            const items = document.querySelectorAll('.list-group-item');

            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(filter)) {
                    item.classList.remove('d-none');
                } else {
                    item.classList.add('d-none');
                }
            });
        });
    }
</script>

<?php include '../includes/footer.php'; ?>