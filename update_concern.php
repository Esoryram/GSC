<?php
session_start();
include("config.php");
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$concern_id = $_POST['concern_id'] ?? '';
$assigned_to = $_POST['assigned_to'] ?? '';
$status = $_POST['status'] ?? '';
$building = $_POST['building'] ?? '';
$room = $_POST['room'] ?? '';
$service_type = $_POST['service_type'] ?? '';
$equipment = $_POST['equipment'] ?? '';

// Handle "Other" fields
if (isset($_POST['other_building']) && !empty($_POST['other_building'])) {
    $building = $_POST['other_building'];
}

if (isset($_POST['other_room']) && !empty($_POST['other_room'])) {
    $room = $_POST['other_room'];
}

if (isset($_POST['other_service']) && !empty($_POST['other_service'])) {
    $service_type = $_POST['other_service'];
}

if (isset($_POST['other_equipment']) && !empty($_POST['other_equipment'])) {
    $equipment = $_POST['other_equipment'];
}

if (!$concern_id || !$assigned_to || !$status) {
    echo json_encode(['success' => false, 'message' => 'Please provide all required fields.']);
    exit;
}

try {
    // Update concern in database
    $stmt = $conn->prepare("UPDATE Concerns SET Assigned_to = ?, Status = ?, building_name = ?, Room = ?, Service_type = ?, EFname = ? WHERE ConcernID = ?");
    
    if (!$stmt) {
        throw new Exception('Database preparation failed: ' . $conn->error);
    }

    $stmt->bind_param("ssssssi", $assigned_to, $status, $building, $room, $service_type, $equipment, $concern_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Concern updated successfully!']);
    } else {
        throw new Exception('Failed to update concern: ' . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Concern update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>