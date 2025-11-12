<?php
session_start();
include("config.php");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit();
}

// Get the logged-in user's AccountID
$username = $_SESSION['username'];
$userQuery = $conn->prepare("SELECT AccountID FROM Accounts WHERE Username = ?");
$userQuery->bind_param("s", $username);
$userQuery->execute();
$userResult = $userQuery->get_result();

if ($userResult->num_rows === 0) {
    die("Error: Account not found.");
}
$user = $userResult->fetch_assoc();
$accountID = $user['AccountID'];

// Handle form data
$title = $_POST['title'];
$description = $_POST['description'];
$room = $_POST['room'];
$equipment = $_POST['equipment'];
$problem_type = $_POST['problem_type'];
$priority = $_POST['priority'];
$attachment = "";

// Handle file upload
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . "_" . basename($_FILES['attachment']['name']);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
        $attachment = $targetPath;
    } else {
        echo "<script>alert('Failed to upload attachment.'); window.history.back();</script>";
        exit();
    }
}

// Insert into Concerns table
$stmt = $conn->prepare("INSERT INTO Concerns (Concern_Title, Room, Description, Problem_Type, Priority, Concern_Date, Attachment, AccountID) 
VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)");
$stmt->bind_param("ssssssi", $title, $room, $description, $problem_type, $priority, $attachment, $accountID);


if ($stmt->execute()) {
    // Get the inserted ConcernID
    $concernID = $conn->insert_id;

    // Also insert into EquipmentFacilities
    $efStmt = $conn->prepare("INSERT INTO EquipmentFacilities (Type, Room, ConcernID) VALUES (?, ?, ?)");
    $efStmt->bind_param("ssi", $equipment, $room, $concernID);
    $efStmt->execute();

    echo "<script>
        alert('Concern submitted successfully!');
        window.location.href='userdb.php';
    </script>";
} else {
    echo "<script>
        alert('Error submitting concern. Please try again.');
        window.history.back();
    </script>";
}
?>
