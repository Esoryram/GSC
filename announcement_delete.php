<?php
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}

// Delete announcement by ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // sanitize input

    $deleteQuery = "DELETE FROM Announcements WHERE AnnouncementID = $id";
    if (mysqli_query($conn, $deleteQuery)) {
        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_message'] = 'Announcement deleted successfully!';
    } else {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'Error deleting announcement: ' . mysqli_error($conn);
    }
}

header("Location: adminannouncement.php");
exit();
?>