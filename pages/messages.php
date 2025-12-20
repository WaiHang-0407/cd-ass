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
        <div class="col-md-4 border-end">
            <h4 class="mb-3">Patients</h4>
            <div class="list-group">
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

                foreach ($patients as $p):
                    $active = ($p['userID'] == $chatPartnerID) ? 'active' : '';
                    ?>
                    <a href="messages.php?chat_with=<?= $p['userID'] ?>"
                        class="list-group-item list-group-item-action <?= $active ?>">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?= htmlspecialchars($p['name']) ?></h6>
                            <!-- <small>3 days ago</small> -->
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
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i></button>
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
</script>

<?php include '../includes/footer.php'; ?>