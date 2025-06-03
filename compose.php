<?php
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php';</script>";
}
$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient_email = $_POST['recipient'];
    $subject = $_POST['subject'];
    $body = $_POST['body'];
    $status = isset($_POST['save_draft']) ? 'draft' : 'sent';
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$recipient_email]);
    $recipient = $stmt->fetch();
    if ($recipient || $status == 'draft') {
        $recipient_id = $recipient ? $recipient['id'] : $user_id;
        $stmt = $conn->prepare("INSERT INTO emails (sender_id, recipient_id, subject, body, status, has_attachment) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $recipient_id, $subject, $body, $status, false]);
        $redirect = $status == 'draft' ? 'drafts.php' : 'sent.php';
        echo "<script>window.location.href='$redirect';</script>";
    } else {
        $error = "Recipient not found";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail Clone - Compose</title>
    <style>
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background: #f1f3f4;
        }
        .compose-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h2 {
            color: #202124;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #5f6368;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #dadce0;
            border-radius: 4px;
            font-size: 16px;
        }
        textarea {
            height: 300px;
            resize: vertical;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #1a73e8;
            box-shadow: 0 0 0 2px rgba(26,115,232,0.2);
        }
        .buttons {
            display: flex;
            gap: 10px;
        }
        button {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .send-btn {
            background: #1a73e8;
            color: white;
        }
        .send-btn:hover {
            background: #1557b0;
        }
        .draft-btn {
            background: #dadce0;
            color: #202124;
        }
        .draft-btn:hover {
            background: #c4c7cc;
        }
        .error {
            color: #d93025;
            margin-bottom: 20px;
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
        @media (max-width: 600px) {
            .compose-container {
                margin: 10px;
                padding: 15px;
            }
            .buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="compose-container">
        <a href="#" onclick="window.location.href='dashboard.php'" class="back-btn">Back to Inbox</a>
        <h2>New Message</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <div class="form-group">
                <label>To</label>
                <input type="email" name="recipient" required>
            </div>
            <div class="form-group">
                <label>Subject</label>
                <input type="text" name="subject">
            </div>
            <div class="form-group">
                <label>Body</label>
                <textarea name="body" required></textarea>
            </div>
            <div class="buttons">
                <button type="submit" class="send-btn">Send</button>
                <button type="submit" name="save_draft" class="draft-btn">Save Draft</button>
            </div>
        </form>
    </div>
</body>
</html>
