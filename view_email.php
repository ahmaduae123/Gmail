<?php
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php';</script>";
}
$email_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT e.*, u1.name AS sender_name, u2.name AS recipient_name FROM emails e 
                        JOIN users u1 ON e.sender_id = u1.id 
                        JOIN users u2 ON e.recipient_id = u2.id 
                        WHERE e.id = ? AND (e.sender_id = ? OR e.recipient_id = ?)");
$stmt->execute([$email_id, $user_id, $user_id]);
$email = $stmt->fetch();
if ($email && $email['recipient_id'] == $user_id && !$email['is_read']) {
    $stmt = $conn->prepare("UPDATE emails SET is_read = TRUE WHERE id = ?");
    $stmt->execute([$email_id]);
}
if (!$email) {
    echo "<script>window.location.href='dashboard.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail Clone - View Email</title>
    <style>
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background: #f1f3f4;
        }
        .email-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .email-header {
            border-bottom: 1px solid #dadce0;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .email-header h2 {
            margin: 0;
            color: #202124;
        }
        .email-meta {
            color: #5f6368;
            font-size: 14px;
            margin: 10px 0;
        }
        .email-body {
            white-space: pre-wrap;
            color: #202124;
        }
        .back-btn {
            color: #1a73e8;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
        }
        .back-btn:hover {
            text-decoration: underline;
        }
        .star {
            cursor: pointer;
            color: #fbc02d;
            font-size: 20px;
        }
        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .delete-btn {
            background: #d93025;
            color: white;
        }
        .delete-btn:hover {
            background: #b71c1c;
        }
        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <a href="#" onclick="window.location.href='dashboard.php'" class="back-btn">Back to Inbox</a>
        <div class="email-header">
            <h2><?php echo htmlspecialchars($email['subject']); ?></h2>
            <div class="email-meta">
                <span>From: <?php echo htmlspecialchars($email['sender_name']); ?></span><br>
                <span>To: <?php echo htmlspecialchars($email['recipient_name']); ?></span><br>
                <span>Date: <?php echo date('M d, Y H:i', strtotime($email['created_at'])); ?></span>
            </div>
            <span class="star" onclick="toggleStar(<?php echo $email['id']; ?>)">
                <?php echo $email['is_starred'] ? '★' : '☆'; ?>
            </span>
        </div>
        <div class="email-body">
            <?php echo nl2br(htmlspecialchars($email['body'])); ?>
        </div>
        <div class="actions">
            <?php if ($email['status'] != 'trash'): ?>
                <button class="delete-btn" onclick="moveToTrash(<?php echo $email['id']; ?>)">Delete</button>
            <?php endif; ?>
        </div>
    </div>
    <script>
        function toggleStar(emailId) {
            fetch('actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=star&email_id=${emailId}`
            }).then(() => location.reload());
        }
        function moveToTrash(emailId) {
            fetch('actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=trash&email_id=${emailId}`
            }).then(() => window.location.href='dashboard.php');
        }
    </script>
</body>
</html>
