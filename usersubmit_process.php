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
$stmt = $conn->prepare("SELECT AccountID FROM accounts WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // User not found
    $_SESSION['error'] = "User not found. Please login again.";
    header("Location: user_login.php");
    exit();
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
    
    // Handle file upload
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
                $_SESSION['error'] = "Failed to upload file.";
                header("Location: usersubmit.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid file type. Allowed types: JPG, PNG, GIF, MP4, MOV.";
            header("Location: usersubmit.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Please attach a file.";
        header("Location: usersubmit.php");
        exit();
    }
    
    // Validate required fields
    if (empty($title) || empty($description) || empty($building) || empty($room) || empty($service_type) || empty($equipment_list)) {
        $_SESSION['error'] = "Please fill out all required fields.";
        header("Location: usersubmit.php");
        exit();
    }
    
    // Insert into concerns table - MAKE SURE AccountID is included
    $sql = "INSERT INTO concerns (Concern_Title, Description, building_name, Room, Service_type, EFname, Attachment, AccountID) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssssssi", $title, $description, $building, $room, $service_type, $equipment_list, $attachment_path, $accountID);
        
        if ($stmt->execute()) {
            $_SESSION['error'] = "Error submitting concern: " . $stmt->error;
            header("Location: usersubmit.php");
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: usersubmit.php");
        exit();
    }
} else {
    // Not a POST request
    $_SESSION['error'] = "Invalid request method.";
    header("Location: usersubmit.php");
    exit();
}
?>