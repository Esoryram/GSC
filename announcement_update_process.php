<?php
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['AnnouncementID'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];

    $sql = "UPDATE Announcements 
            SET title=?, content=?, start_date=?, end_date=? 
            WHERE AnnouncementID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $title, $content, $start, $end, $id);

    if ($stmt->execute()) {
        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'Announcement updated successfully!';
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'Error updating announcement.';
    }
    $stmt->close();
    
    header("Location: adminannouncement.php");
    exit();
}
?>