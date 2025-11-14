<?php 
session_start();
include("config.php");

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

$username = $_SESSION['username'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;
$activePage = "system_data";

// Get current active tab from session or default to 'rooms'
$activeTab = isset($_SESSION['active_data_tab']) ? $_SESSION['active_data_tab'] : 'rooms';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $success = false;
        $message = '';
        
        try {
            switch ($_POST['action']) {
                case 'add_room':
                    $roomname = $_POST['roomname'];
                    $building_name = $_POST['building_name'];
                    
                    $stmt = $conn->prepare("INSERT INTO rooms (roomname, building_name) VALUES (?, ?)");
                    $stmt->bind_param("ss", $roomname, $building_name);
                    if ($stmt->execute()) {
                        $success = true;
                        $message = 'Room added successfully!';
                    }
                    break;
                    
                case 'edit_room':
                    $RoomID = $_POST['RoomID'];
                    $roomname = $_POST['roomname'];
                    $building_name = $_POST['building_name'];
                    
                    $stmt = $conn->prepare("UPDATE rooms SET roomname = ?, building_name = ? WHERE RoomID = ?");
                    $stmt->bind_param("ssi", $roomname, $building_name, $RoomID);
                    if ($stmt->execute()) {
                        $success = true;
                        $message = 'Room updated successfully!';
                    }
                    break;
                    
                case 'delete_room':
                    $RoomID = $_POST['RoomID'];
                    $stmt = $conn->prepare("DELETE FROM rooms WHERE RoomID = ?");
                    $stmt->bind_param("i", $RoomID);
                    if ($stmt->execute()) {
                        $success = true;
                        $message = 'Room deleted successfully!';
                    }
                    break;
                    
                case 'add_equipment':
                    $EFname = $_POST['EFname'];
                    
                    $stmt = $conn->prepare("INSERT INTO equipmentfacility (EFname) VALUES (?)");
                    $stmt->bind_param("s", $EFname);
                    if ($stmt->execute()) {
                        $success = true;
                        $message = 'Equipment/Facility added successfully!';
                    }
                    break;
                    
                case 'edit_equipment':
                    $EFID = $_POST['EFID'];
                    $EFname = $_POST['EFname'];
                    
                    $stmt = $conn->prepare("UPDATE equipmentfacility SET EFname = ? WHERE EFID = ?");
                    $stmt->bind_param("si", $EFname, $EFID);
                    if ($stmt->execute()) {
                        $success = true;
                        $message = 'Equipment/Facility updated successfully!';
                    }
                    break;
                    
                case 'delete_equipment':
                    $EFID = $_POST['EFID'];
                    $stmt = $conn->prepare("DELETE FROM equipmentfacility WHERE EFID = ?");
                    $stmt->bind_param("i", $EFID);
                    if ($stmt->execute()) {
                        $success = true;
                        $message = 'Equipment/Facility deleted successfully!';
                    }
                    break;
                    
                case 'add_personnel':
                    $name = $_POST['name'];
                    
                    $stmt = $conn->prepare("INSERT INTO personnels (name) VALUES (?)");
                    $stmt->bind_param("s", $name);
                    if ($stmt->execute()) {
                        $success = true;
                        $message = 'Personnel added successfully!';
                    }
                    break;
                    
                case 'edit_personnel':
                    $PersonnelId = $_POST['PersonnelId'];
                    $name = $_POST['name'];
                    
                    $stmt = $conn->prepare("UPDATE personnels SET name = ? WHERE PersonnelId = ?");
                    $stmt->bind_param("si", $name, $PersonnelId);
                    if ($stmt->execute()) {
                        $success = true;
                        $message = 'Personnel updated successfully!';
                    }
                    break;
                    
                case 'delete_personnel':
                    $PersonnelId = $_POST['PersonnelId'];
                    $stmt = $conn->prepare("DELETE FROM personnels WHERE PersonnelId = ?");
                    $stmt->bind_param("i", $PersonnelId);
                    if ($stmt->execute()) {
                        $success = true;
                        $message = 'Personnel deleted successfully!';
                    }
                    break;
                    
                case 'add_service':
                    $Service_type = $_POST['Service_type'];
                    
                    $stmt = $conn->prepare("INSERT INTO services (Service_type) VALUES (?)");
                    $stmt->bind_param("s", $Service_type);
                    if ($stmt->execute()) {
                        $success = true;
                        $message = 'Service added successfully!';
                    }
                    break;
                    
                case 'edit_service':
                    $ServiceID = $_POST['ServiceID'];
                    $Service_type = $_POST['Service_type'];
                    
                    $stmt = $conn->prepare("UPDATE services SET Service_type = ? WHERE ServiceID = ?");
                    $stmt->bind_param("si", $Service_type, $ServiceID);
                    if ($stmt->execute()) {
                        $success = true;
                        $message = 'Service updated successfully!';
                    }
                    break;
                    
                case 'delete_service':
                    $ServiceID = $_POST['ServiceID'];
                    $stmt = $conn->prepare("DELETE FROM services WHERE ServiceID = ?");
                    $stmt->bind_param("i", $ServiceID);
                    if ($stmt->execute()) {
                        $success = true;
                        $message = 'Service deleted successfully!';
                    }
                    break;
            }
            
            // Store success status and message in session for SweetAlert
            $_SESSION['alert'] = [
                'success' => $success,
                'message' => $success ? $message : 'Operation failed. Please try again.',
                'active_tab' => $_POST['active_tab'] ?? $activeTab
            ];
            
        } catch (Exception $e) {
            $_SESSION['alert'] = [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'active_tab' => $_POST['active_tab'] ?? $activeTab
            ];
        }
        
        // Redirect back to maintain state
        header("Location: system_data.php");
        exit();
    }
}

// Store current tab in session when navigating
if (isset($_GET['tab'])) {
    $_SESSION['active_data_tab'] = $_GET['tab'];
    $activeTab = $_SESSION['active_data_tab'];
}

// Fetch data for display
$rooms = $conn->query("SELECT * FROM rooms ORDER BY RoomID DESC");
$equipment = $conn->query("SELECT * FROM equipmentfacility ORDER BY EFID DESC");
$personnel = $conn->query("SELECT * FROM personnels ORDER BY PersonnelId DESC");
$services = $conn->query("SELECT * FROM services ORDER BY ServiceID DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Data Management - GSC System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            background: #f9fafb;
            overflow-x: hidden;
        }

        .navbar {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #087830, #3c4142);
            padding: 12px 15px;
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            position: relative;
            width: 100%;
            box-sizing: border-box;
        }

        .logo {
            display: flex;
            align-items: center;
            margin-right: 15px; 
        }

        .logo img {
            height: 35px; 
            width: auto; 
            object-fit: contain;
        }

        .navbar .links {
            display: flex;
            gap: 12px;
            margin-right: auto;
        }

        .navbar .links a {
            color: white; 
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            padding: 8px 12px; 
            border-radius: 5px;
            transition: all 0.3s ease;
            min-height: 44px;
            display: flex;
            align-items: center;
        }

        .navbar .links a.active {
            background: #4ba06f;
            border: 1px solid #07491f;
            box-shadow: 0 4px 6px rgba(0,0,0,0.4);
            color: white;
        }

        .navbar .links a:hover {
            background: #107040;
            color: white;
        }

        .navbar .links a i {
            margin-right: 5px;
        }

        .dropdown {
            position: relative;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: linear-gradient(135deg, #087830, #3c4142);
            min-width: 180px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            border-radius: 5px;
            overflow: hidden;
            z-index: 10;
        }

        .dropdown:hover .dropdown-menu {
            display: block;
        }

        .dropdown-menu a {
            display: block;
            padding: 12px 16px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            font-weight: 400;
        }

        .dropdown .username-btn {
            color: white !important;
            background: none !important;
            border: none !important;
            font-weight: 600;
            font-size: 16px;
        }

        .container {
            padding: 40px 60px;
            gap: 30px;
        }

        /* Scrollable table container */
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            max-height: 500px;
            overflow-y: auto;
        }

        .table-responsive table {
            margin-bottom: 0;
        }

        .table thead {
            background-color: #198754;
            color: white;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table thead th {
            border-bottom: none;
            font-weight: 600;
            padding: 12px 8px;
        }

        .table tbody td {
            padding: 10px 8px;
            font-weight: 400;
            vertical-align: middle;
        }

        .nav-tabs .nav-link {
            color: #495057;
            font-weight: 500;
            border: none;
            padding: 12px 20px;
        }

        .nav-tabs .nav-link.active {
            font-weight: 600;
            background-color: #198754;
            color: white;
            border: none;
        }

        .nav-tabs .nav-link:hover {
            border: none;
            color: #198754;
        }

        .btn-primary {
            background-color: #198754;
            border-color: #198754;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: #146c43;
            border-color: #146c43;
        }

        .tab-content {
            background: white;
            border-radius: 0 8px 8px 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .tab-pane {
            padding: 20px;
        }

        h2, h4 {
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            .table-responsive {
                max-height: 400px;
            }
        }

        /* Custom scrollbar */
        .table-responsive::-webkit-scrollbar {
            width: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 0 4px 4px 0;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo">
            <img src="img/LSULogo.png" alt="LSU Logo">
        </div>

        <div class="links">
            <a href="admindb.php">
                <i class="fas fa-home me-1"></i> Dashboard
            </a>
            <a href="adminannouncement.php">
                <i class="fas fa-bullhorn"></i> Announcements
            </a>
            <a href="adminconcerns.php">
                <i class="fas fa-list-ul me-1"></i> Concerns
            </a>
            <a href="adminfeedback.php">
                <i class="fas fa-comment-alt"></i> Feedback
            </a>
            <a href="adminreports.php">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a href="system_data.php" class="active">
                <i class="fas fa-database me-1"></i> System Data
            </a>
        </div>

        <!-- User dropdown -->
        <div class="dropdown ms-auto">
            <button class="btn dropdown-toggle username-btn" aria-expanded="false" aria-haspopup="true">
                <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($name) ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="fas fa-key me-2"></i>Change Password
                </a></li>
                <li><a class="dropdown-item" href="index.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a></li>
            </ul>
        </div>
    </div>

    <!-- Include Change Password Modal -->
    <?php include('change_password_modal.php'); ?>

    <div class="container mt-4">
        <h2 class="mb-4"><i class="fas fa-database me-2"></i>System Data Management</h2>
        
        <!-- Tabs for different data types -->
        <ul class="nav nav-tabs" id="dataTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'rooms' ? 'active' : '' ?>" id="rooms-tab" data-bs-toggle="tab" data-bs-target="#rooms" type="button" role="tab" onclick="setActiveTab('rooms')">
                    <i class="fas fa-door-open me-1"></i>Rooms
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'equipment' ? 'active' : '' ?>" id="equipment-tab" data-bs-toggle="tab" data-bs-target="#equipment" type="button" role="tab" onclick="setActiveTab('equipment')">
                    <i class="fas fa-desktop me-1"></i>Equipment/Facilities
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'personnel' ? 'active' : '' ?>" id="personnel-tab" data-bs-toggle="tab" data-bs-target="#personnel" type="button" role="tab" onclick="setActiveTab('personnel')">
                    <i class="fas fa-users me-1"></i>Personnel
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'services' ? 'active' : '' ?>" id="services-tab" data-bs-toggle="tab" data-bs-target="#services" type="button" role="tab" onclick="setActiveTab('services')">
                    <i class="fas fa-concierge-bell me-1"></i>Services
                </button>
            </li>
        </ul>
        
        <div class="tab-content p-3 border border-top-0 rounded-bottom" id="dataTabsContent">
            <!-- Rooms Tab -->
            <div class="tab-pane fade <?= $activeTab === 'rooms' ? 'show active' : '' ?>" id="rooms" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Room Management</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                        <i class="fas fa-plus me-1"></i>Add Room
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Room Name</th>
                                <th>Building Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $rooms->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['RoomID']; ?></td>
                                <td><?php echo htmlspecialchars($row['roomname']); ?></td>
                                <td><?php echo htmlspecialchars($row['building_name']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-room" 
                                            data-id="<?php echo $row['RoomID']; ?>"
                                            data-roomname="<?php echo htmlspecialchars($row['roomname']); ?>"
                                            data-building="<?php echo htmlspecialchars($row['building_name']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-room" 
                                            data-id="<?php echo $row['RoomID']; ?>"
                                            data-type="room"
                                            data-name="<?php echo htmlspecialchars($row['roomname']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Equipment/Facilities Tab -->
            <div class="tab-pane fade <?= $activeTab === 'equipment' ? 'show active' : '' ?>" id="equipment" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Equipment/Facility Management</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
                        <i class="fas fa-plus me-1"></i>Add Equipment/Facility
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $equipment->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['EFID']; ?></td>
                                <td><?php echo htmlspecialchars($row['EFname']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-equipment" 
                                            data-id="<?php echo $row['EFID']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['EFname']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-equipment" 
                                            data-id="<?php echo $row['EFID']; ?>"
                                            data-type="equipment"
                                            data-name="<?php echo htmlspecialchars($row['EFname']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Personnel Tab -->
            <div class="tab-pane fade <?= $activeTab === 'personnel' ? 'show active' : '' ?>" id="personnel" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Personnel Management</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPersonnelModal">
                        <i class="fas fa-plus me-1"></i>Add Personnel
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $personnel->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['PersonnelId']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-personnel" 
                                            data-id="<?php echo $row['PersonnelId']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['name']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-personnel" 
                                            data-id="<?php echo $row['PersonnelId']; ?>"
                                            data-type="personnel"
                                            data-name="<?php echo htmlspecialchars($row['name']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Services Tab -->
            <div class="tab-pane fade <?= $activeTab === 'services' ? 'show active' : '' ?>" id="services" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Service Management</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                        <i class="fas fa-plus me-1"></i>Add Service
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Service Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $services->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['ServiceID']; ?></td>
                                <td><?php echo htmlspecialchars($row['Service_type']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-service" 
                                            data-id="<?php echo $row['ServiceID']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['Service_type']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-service" 
                                            data-id="<?php echo $row['ServiceID']; ?>"
                                            data-type="service"
                                            data-name="<?php echo htmlspecialchars($row['Service_type']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Room Modal -->
    <div class="modal fade" id="addRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="addRoomForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Room</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_room">
                        <input type="hidden" name="active_tab" value="<?= $activeTab ?>">
                        <div class="mb-3">
                            <label for="roomname" class="form-label">Room Name</label>
                            <input type="text" class="form-control" id="roomname" name="roomname" required>
                        </div>
                        <div class="mb-3">
                            <label for="building_name" class="form-label">Building Name</label>
                            <input type="text" class="form-control" id="building_name" name="building_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Add Room</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Room Modal -->
    <div class="modal fade" id="editRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="editRoomForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Room</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_room">
                        <input type="hidden" name="RoomID" id="edit_room_RoomID">
                        <input type="hidden" name="active_tab" value="<?= $activeTab ?>">
                        <div class="mb-3">
                            <label for="edit_roomname" class="form-label">Room Name</label>
                            <input type="text" class="form-control" id="edit_roomname" name="roomname" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_building_name" class="form-label">Building Name</label>
                            <input type="text" class="form-control" id="edit_building_name" name="building_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Room</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Equipment Modal -->
    <div class="modal fade" id="addEquipmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="addEquipmentForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Equipment/Facility</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_equipment">
                        <input type="hidden" name="active_tab" value="<?= $activeTab ?>">
                        <div class="mb-3">
                            <label for="EFname" class="form-label">Equipment/Facility Name</label>
                            <input type="text" class="form-control" id="EFname" name="EFname" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Add Equipment/Facility</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Equipment Modal -->
    <div class="modal fade" id="editEquipmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="editEquipmentForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Equipment/Facility</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_equipment">
                        <input type="hidden" name="EFID" id="edit_equipment_id">
                        <input type="hidden" name="active_tab" value="<?= $activeTab ?>">
                        <div class="mb-3">
                            <label for="edit_EFname" class="form-label">Equipment/Facility Name</label>
                            <input type="text" class="form-control" id="edit_EFname" name="EFname" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Equipment/Facility</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Personnel Modal -->
    <div class="modal fade" id="addPersonnelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="addPersonnelForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Personnel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_personnel">
                        <input type="hidden" name="active_tab" value="<?= $activeTab ?>">
                        <div class="mb-3">
                            <label for="name" class="form-label">Personnel Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Add Personnel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Personnel Modal -->
    <div class="modal fade" id="editPersonnelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="editPersonnelForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Personnel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_personnel">
                        <input type="hidden" name="PersonnelId" id="edit_personnel_id">
                        <input type="hidden" name="active_tab" value="<?= $activeTab ?>">
                        <div class="mb-3">
                            <label for="edit_personnel_name" class="form-label">Personnel Name</label>
                            <input type="text" class="form-control" id="edit_personnel_name" name="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Personnel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Service Modal -->
    <div class="modal fade" id="addServiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="addServiceForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_service">
                        <input type="hidden" name="active_tab" value="<?= $activeTab ?>">
                        <div class="mb-3">
                            <label for="Service_type" class="form-label">Service Type</label>
                            <input type="text" class="form-control" id="Service_type" name="Service_type" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Add Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Service Modal -->
    <div class="modal fade" id="editServiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="editServiceForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_service">
                        <input type="hidden" name="ServiceID" id="edit_service_id">
                        <input type="hidden" name="active_tab" value="<?= $activeTab ?>">
                        <div class="mb-3">
                            <label for="edit_service_name" class="form-label">Service Type</label>
                            <input type="text" class="form-control" id="edit_service_name" name="Service_type" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <script>
        // Function to set active tab
        function setActiveTab(tabName) {
            // Update the URL with the active tab parameter
            window.history.replaceState(null, null, '?tab=' + tabName);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Show SweetAlert2 notifications if any
            <?php if (isset($_SESSION['alert'])): ?>
                const alert = <?= json_encode($_SESSION['alert']) ?>;
                if (alert.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: alert.message,
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: alert.message,
                        timer: 4000,
                        showConfirmButton: true
                    });
                }
                <?php unset($_SESSION['alert']); ?>
            <?php endif; ?>

            // Room edit functionality
            const editRoomButtons = document.querySelectorAll('.edit-room');
            editRoomButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const roomname = this.getAttribute('data-roomname');
                    const building = this.getAttribute('data-building');
                    
                    document.getElementById('edit_room_RoomID').value = id;
                    document.getElementById('edit_roomname').value = roomname;
                    document.getElementById('edit_building_name').value = building;
                    
                    const editModal = new bootstrap.Modal(document.getElementById('editRoomModal'));
                    editModal.show();
                });
            });
            
            // Equipment edit functionality
            const editEquipmentButtons = document.querySelectorAll('.edit-equipment');
            editEquipmentButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    
                    document.getElementById('edit_equipment_id').value = id;
                    document.getElementById('edit_EFname').value = name;
                    
                    const editModal = new bootstrap.Modal(document.getElementById('editEquipmentModal'));
                    editModal.show();
                });
            });
            
            // Personnel edit functionality
            const editPersonnelButtons = document.querySelectorAll('.edit-personnel');
            editPersonnelButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    
                    document.getElementById('edit_personnel_id').value = id;
                    document.getElementById('edit_personnel_name').value = name;
                    
                    const editModal = new bootstrap.Modal(document.getElementById('editPersonnelModal'));
                    editModal.show();
                });
            });
            
            // Service edit functionality
            const editServiceButtons = document.querySelectorAll('.edit-service');
            editServiceButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    
                    document.getElementById('edit_service_id').value = id;
                    document.getElementById('edit_service_name').value = name;
                    
                    const editModal = new bootstrap.Modal(document.getElementById('editServiceModal'));
                    editModal.show();
                });
            });

            // Delete functionality with SweetAlert2
            const deleteButtons = document.querySelectorAll('.delete-room, .delete-equipment, .delete-personnel, .delete-service');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const type = this.getAttribute('data-type');
                    const name = this.getAttribute('data-name');
                    
                    let action = '';
                    let title = '';
                    
                    switch(type) {
                        case 'room':
                            action = 'delete_room';
                            title = 'Delete Room';
                            break;
                        case 'equipment':
                            action = 'delete_equipment';
                            title = 'Delete Equipment/Facility';
                            break;
                        case 'personnel':
                            action = 'delete_personnel';
                            title = 'Delete Personnel';
                            break;
                        case 'service':
                            action = 'delete_service';
                            title = 'Delete Service';
                            break;
                    }
                    
                    Swal.fire({
                        title: title,
                        html: `Are you sure you want to delete <strong>"${name}"</strong>?<br>This action cannot be undone.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Create a form and submit it
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = 'system_data.php';
                            
                            const actionInput = document.createElement('input');
                            actionInput.type = 'hidden';
                            actionInput.name = 'action';
                            actionInput.value = action;
                            
                            const activeTabInput = document.createElement('input');
                            activeTabInput.type = 'hidden';
                            activeTabInput.name = 'active_tab';
                            activeTabInput.value = '<?= $activeTab ?>';
                            
                            form.appendChild(actionInput);
                            form.appendChild(activeTabInput);
                            
                            // Add the appropriate ID field based on type
                            let idFieldName = '';
                            switch(type) {
                                case 'room':
                                    idFieldName = 'RoomID';
                                    break;
                                case 'equipment':
                                    idFieldName = 'EFID';
                                    break;
                                case 'personnel':
                                    idFieldName = 'PersonnelId';
                                    break;
                                case 'service':
                                    idFieldName = 'ServiceID';
                                    break;
                            }
                            
                            const idInput = document.createElement('input');
                            idInput.type = 'hidden';
                            idInput.name = idFieldName;
                            idInput.value = id;
                            form.appendChild(idInput);
                            
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            });

            // Add SweetAlert2 confirmation for form submissions
            const forms = document.querySelectorAll('form[id$="Form"]');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const action = formData.get('action');
                    
                    let title = '';
                    let text = '';
                    let confirmButtonText = '';
                    
                    if (action.includes('add')) {
                        title = 'Confirm Add';
                        text = 'Are you sure you want to add this item?';
                        confirmButtonText = 'Yes, add it!';
                    } else if (action.includes('edit')) {
                        title = 'Confirm Update';
                        text = 'Are you sure you want to update this item?';
                        confirmButtonText = 'Yes, update it!';
                    }
                    
                    Swal.fire({
                        title: title,
                        text: text,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: confirmButtonText,
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>