<?php
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    exit;
}
$action = $_POST['action'];
$email_id = $_POST['email_id'];
if ($action == 'star') {
    $stmt = $conn->prepare("UPDATE emails SET is_starred = NOT is_starred WHERE id = ?");
    $stmt->execute([$email_id]);
} elseif ($action == 'trash') {
    $stmt = $conn->prepare("UPDATE emails SET status = 'trash' WHERE id = ?");
    $stmt->execute([$email_id]);
}
?>
