<?php
session_start();
include("config.php");
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$currentPassword = $data['currentPassword'] ?? '';
$newPassword = $data['newPassword'] ?? '';

if (!$currentPassword || !$newPassword) {
    echo json_encode(['success' => false, 'message' => 'Please provide all fields.']);
    exit;
}

// Get username from session
$username = $_SESSION['username'];

try {
    // Verify current password using username (more reliable than accountID)
    $stmt = $conn->prepare("SELECT AccountID, Password FROM accounts WHERE Username = ?");
    if (!$stmt) {
        throw new Exception('Database preparation failed.');
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    $user = $result->fetch_assoc();
    $storedHash = $user['Password'];
    $accountID = $user['AccountID'];

    // Verify current password
    if (!password_verify($currentPassword, $storedHash)) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
        exit;
    }

    // Hash new password and update
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = $conn->prepare("UPDATE accounts SET Password = ? WHERE AccountID = ?");
    
    if (!$updateStmt) {
        throw new Exception('Database update preparation failed.');
    }
    
    $updateStmt->bind_param("si", $newHash, $accountID);

    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Password updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update password.']);
    }
    
    $updateStmt->close();
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Password change error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
}

$conn->close();
?>