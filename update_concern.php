<?php
session_start();
include("config.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $concern_id = $_POST['concern_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    $assigned_to = $_POST['assigned_to'];
    
    // Handle building - use custom if provided
    $building = $_POST['building'];
    if ($building === 'Other' && !empty($_POST['other_building'])) {
        $building = $_POST['other_building'];
    }
    
    // Handle room - use custom if provided
    $room = $_POST['room'];
    if ($room === 'Other' && !empty($_POST['other_room'])) {
        $room = $_POST['other_room'];
    }
    
    // Handle service type - use custom if provided
    $service_type = $_POST['service_type'];
    if ($service_type === 'Other' && !empty($_POST['other_service'])) {
        $service_type = $_POST['other_service'];
    }
    
    // Handle equipment - combine checkboxes and custom
    $equipment = $_POST['equipment'];
    if (!empty($_POST['other_equipment'])) {
        $other_equipment = $_POST['other_equipment'];
        if (!empty($equipment)) {
            $equipment .= ', ' . $other_equipment;
        } else {
            $equipment = $other_equipment;
        }
    }
    
    // Update the concern in database
    $query = "UPDATE Concerns SET 
        Concern_Title = ?,
        Room = ?,
        Service_type = ?,
        Status = ?,
        Assigned_to = ?,
        Description = ?,
        EFname = ?
        WHERE ConcernID = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssi", $title, $room, $service_type, $status, $assigned_to, $description, $equipment, $concern_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Concern updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating concern: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: adminconcerns.php");
    exit();
}
?>