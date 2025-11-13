<?php
// submit_feedback.php
session_start();
include("config.php");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get the logged-in user's AccountID
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT AccountID FROM accounts WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$accountID = $user['AccountID'];
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $concernID = $_POST['concern_id'] ?? '';
    $comments = $_POST['comments'] ?? '';
    
    // Validate input
    if (empty($concernID) || empty($comments)) {
        echo json_encode(['success' => false, 'message' => 'Concern ID and comments are required']);
        exit();
    }
    
    // Check if the concern belongs to the user
    $checkStmt = $conn->prepare("SELECT ConcernID FROM concerns WHERE ConcernID = ? AND AccountID = ?");
    $checkStmt->bind_param("ii", $concernID, $accountID);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Concern not found or access denied']);
        exit();
    }
    $checkStmt->close();
    
    // Check if feedback already exists for this concern
    $existingStmt = $conn->prepare("SELECT FeedbackID FROM feedbacks WHERE ConcernID = ? AND AccountID = ?");
    $existingStmt->bind_param("ii", $concernID, $accountID);
    $existingStmt->execute();
    $existingResult = $existingStmt->get_result();
    
    if ($existingResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Feedback already submitted for this concern']);
        exit();
    }
    $existingStmt->close();
    
    // Insert feedback into database
    $insertStmt = $conn->prepare("INSERT INTO feedbacks (ConcernID, AccountID, Comments, Date_Submitted) VALUES (?, ?, ?, NOW())");
    $insertStmt->bind_param("iis", $concernID, $accountID, $comments);
    
    if ($insertStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Feedback submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
    $insertStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>