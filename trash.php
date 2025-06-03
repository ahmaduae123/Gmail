<?php
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php';</script>";
}
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$query = "SELECT e.*, u.name AS sender_name FROM emails e JOIN users u ON e.sender_id = u.id WHERE (e.sender_id = ? OR e.recipient_id = ?) AND e.status = 'trash'";
$params = [$user_id, $user_id];
if ($search) {
    $query .= " AND (e.subject LIKE ? OR e.body LIKE ? OR u.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filter == 'unread') {
    $query .= " AND e.is_read = FALSE";
} elseif ($filter == 'starred') {
    $query .= " AND e.is_starred = TRUE";
} elseif ($filter == 'attachments') {
    $query .= " AND e.has_attachment = TRUE";
}
$stmt = $conn->prepare($query);
$stmt->execute($params);
$emails = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail Clone - Trash</title>
    <style>
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background: #f1f3f4;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: white;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar a {
            display: block;
            padding: 10px;
            color: #202124;
            text-decoration: none;
            margin-bottom: 5px;
            border-radius: 4px;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #e8f0fe;
            color: #1a73e8;
        }
        .main {
            flex: 1;
            padding: 20px;
        }
        .compose-btn {
            display: inline-block;
            padding: 12px 24px;
            background: #1a73e8;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .compose-btn:hover {
            background: #1557b0;
        }
        .search-bar {
            display: flex;
            margin-bottom: 20px;
        }
        .search-bar input {
            flex: 1;
            padding: 10px;
            border: 1px solid #dadce0;
            border-radius: 4px 0 0 4px;
            font-size: 16px;
        }
        .search-bar button {
            padding: 10px;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        .filter-bar {
            margin-bottom: 20px;
        }
        .filter-bar a {
            margin-right: 10px;
            color: #1a73e8;
            text-decoration: none;
        }
        .filter-bar a:hover {
            text-decoration: underline;
        }
        .email-list {
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        .email-item {
            padding: 15px;
            border-bottom: 1px solid #dadce0;
            display: flex;
            align-items: center;
        }
        .email-item a {
            flex: 1;
            color: #202124;
            text-decoration: none;
            display: flex;
        }
        .email-item a:hover {
            background: #f1f3f4;
        }
        .email-sender {
            width: 200px;
        }
        .email-subject {
            flex: 1;
        }
        .email-date {
            width: 150px;
            text-align: right;
            color: #5f6368;
        }
        .star {
            cursor: pointer;
            color: #fbc02d;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background: white;
            border-bottom: 1px solid #dadce0;
        }
        .logout {
            color: #1a73e8;
            text-decoration: none;
        }
        .logout:hover {
            text-decoration: underline;
        }
        @media (max-width: 800px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                box-shadow: none;
            }
            .main {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?></h2>
        <a href="#" onclick="window.location.href='logout.php'" class="logout">Sign out</a>
    </div>
    <div class="container">
        <div class="sidebar">
            <a href="#" onclick="window.location.href='compose.php'" class="compose-btn">Compose</a>
            <a href="#" onclick="window.location.href='dashboard.php'">Inbox</a>
            <a href="#" onclick="window.location.href='sent.php'">Sent</a>
            <a href="#" onclick="window.location.href='drafts.php'">Drafts</a>
            <a href="#" onclick="window.location.href='trash.php'" class="active">Trash</a>
        </div>
        <div class="main">
            <div class="search-bar">
                <input type="text" id="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search trash">
                <button onclick="searchEmails()">Search</button>
            </div>
            <div class="filter-bar">
                <a href="#" onclick="filterEmails('')">All</a>
                <a href="#" onclick="filterEmails('unread')">Unread</a>
                <a href="#" onclick="filterEmails('starred')">Starred</a>
                <a href="#" onclick="filterEmails('attachments')">With Attachments</a>
            </div>
            <div class="email-list">
                <?php foreach ($emails as $email): ?>
                    <div class="email-item">
                        <span class="star" onclick="toggleStar(<?php echo $email['id']; ?>)">
                            <?php echo $email['is_starred'] ? '★' : '☆'; ?>
                        </span>
                        <a href="#" onclick="window.location.href='view_email.php?id=<?php echo $email['id']; ?>'">
                            <span class="email-sender"><?php echo htmlspecialchars($email['sender_name']); ?></span>
                            <span class="email-subject"><?php echo htmlspecialchars($email['subject']); ?></span>
                            <span class="email-date"><?php echo date('M d, Y', strtotime($email['created_at'])); ?></span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <script>
        function searchEmails() {
            const search = document.getElementById('search').value;
            const filter = '<?php echo $filter; ?>';
            window.location.href = `trash.php?search=${encodeURIComponent(search)}&filter=${filter}`;
        }
        function filterEmails(filter) {
            const search = document.getElementById('search').value;
            window.location.href = `trash.php?search=${encodeURIComponent(search)}&filter=${filter}`;
        }
        function toggleStar(emailId) {
            fetch('actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=star&email_id=${emailId}`
            }).then(() => location.reload());
        }
    </script>
</body>
</html>
