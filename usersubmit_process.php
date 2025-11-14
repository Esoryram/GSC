<?php
session_start();
include("config.php");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// Get the logged-in user's AccountID
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT AccountID FROM accounts WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // User not found
    echo json_encode(['success' => false, 'message' => 'User not found. Please login again.']);
    exit;
}

$user = $result->fetch_assoc();
$accountID = $user['AccountID'];
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    
    // Handle building - check if it's "Other" or from dropdown
    $building = trim($_POST['building']);
    if ($building === 'Other') {
        $building = trim($_POST['other_building']);
    }
    
    // Handle room - check if it's "Other" or from dropdown
    $room = trim($_POST['room']);
    if ($room === 'Other') {
        $room = trim($_POST['other_room']);
    }
    
    // Handle service type - check if it's "Other" or from dropdown
    $service_type = trim($_POST['Service_type']);
    if ($service_type === 'Other') {
        $service_type = trim($_POST['other_service']);
    }
    
    // Handle equipment - convert array to string
    $equipment_list = '';
    if (isset($_POST['equipment'])) {
        $equipment_array = $_POST['equipment'];
        
        // Handle "Other" equipment
        if (in_array('Other', $equipment_array) && !empty($_POST['other_equipment'])) {
            // Replace "Other" with the actual value
            $key = array_search('Other', $equipment_array);
            $equipment_array[$key] = trim($_POST['other_equipment']);
        }
        
        $equipment_list = implode(', ', $equipment_array);
    }
    
    // Handle file upload (OPTIONAL)
    $attachment_path = '';
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename to prevent conflicts
        $file_extension = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
        $file_name = time() . '_' . uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;
        
        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov'];
        if (in_array(strtolower($file_extension), $allowed_types)) {
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_path)) {
                $attachment_path = $target_path;
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload file.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed types: JPG, PNG, GIF, MP4, MOV.']);
            exit;
        }
    }
    // If no file uploaded, attachment_path remains empty - this is OK now
    
    // Validate required fields
    if (empty($title) || empty($description) || empty($building) || empty($room) || empty($service_type) || empty($equipment_list)) {
        echo json_encode(['success' => false, 'message' => 'Please fill out all required fields.']);
        exit;
    }
    
    // Insert into concerns table
    $sql = "INSERT INTO concerns (Concern_Title, Description, building_name, Room, Service_type, EFname, Attachment, AccountID) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssssssi", $title, $description, $building, $room, $service_type, $equipment_list, $attachment_path, $accountID);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Your concern has been submitted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error submitting concern: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
} else {
    // Not a POST request
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>