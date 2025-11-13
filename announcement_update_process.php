<?php
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $start_date = $_POST['start_date'] ?? date('Y-m-d');
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

    // Get the logged-in user's AccountID
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT AccountID FROM accounts WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $accountID = $user['AccountID'] ?? null;

    // Insert into announcements
    if ($title && $content && $accountID) {
        $insert = $conn->prepare("
            INSERT INTO announcements (title, content, AccountID, start_date, end_date, is_active)
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $insert->bind_param("ssiss", $title, $content, $accountID, $start_date, $end_date);

        if ($insert->execute()) {
            $_SESSION['alert_type'] = 'success';
            $_SESSION['alert_message'] = 'Announcement posted successfully!';
        } else {
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_message'] = 'Error posting announcement.';
        }
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'Please fill out all required fields.';
    }
    
    header("Location: adminannouncement.php");
    exit();
}
?>