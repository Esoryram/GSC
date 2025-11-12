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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_room':
                $roomname = $_POST['roomname'];
                $building_name = $_POST['building_name'];
                $date_added = date('Y-m-d');
                
                $stmt = $conn->prepare("INSERT INTO rooms (roomname, building_name, date_added) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $roomname, $building_name, $date_added);
                $stmt->execute();
                break;
                
            case 'edit_room':
                $id = $_POST['id'];
                $roomname = $_POST['roomname'];
                $building_name = $_POST['building_name'];
                
                $stmt = $conn->prepare("UPDATE rooms SET roomname = ?, building_name = ? WHERE id = ?");
                $stmt->bind_param("ssi", $roomname, $building_name, $id);
                $stmt->execute();
                break;
                
            case 'delete_room':
                $id = $_POST['id'];
                $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                break;
                
            case 'add_equipment':
                $EFname = $_POST['EFname'];
                
                $stmt = $conn->prepare("INSERT INTO equipmentfacility (EFname) VALUES (?)");
                $stmt->bind_param("s", $EFname);
                $stmt->execute();
                break;
                
            case 'edit_equipment':
                $EFID = $_POST['EFID'];
                $EFname = $_POST['EFname'];
                
                $stmt = $conn->prepare("UPDATE equipmentfacility SET EFname = ? WHERE EFID = ?");
                $stmt->bind_param("si", $EFname, $EFID);
                $stmt->execute();
                break;
                
            case 'delete_equipment':
                $EFID = $_POST['EFID'];
                $stmt = $conn->prepare("DELETE FROM equipmentfacility WHERE EFID = ?");
                $stmt->bind_param("i", $EFID);
                $stmt->execute();
                break;
                
            case 'add_personnel':
                $name = $_POST['name'];
                
                $stmt = $conn->prepare("INSERT INTO personnels (name) VALUES (?)");
                $stmt->bind_param("s", $name);
                $stmt->execute();
                break;
                
            case 'edit_personnel':
                $PersonnelId = $_POST['PersonnelId'];
                $name = $_POST['name'];
                
                $stmt = $conn->prepare("UPDATE personnels SET name = ? WHERE PersonnelId = ?");
                $stmt->bind_param("si", $name, $PersonnelId);
                $stmt->execute();
                break;
                
            case 'delete_personnel':
                $PersonnelId = $_POST['PersonnelId'];
                $stmt = $conn->prepare("DELETE FROM personnels WHERE PersonnelId = ?");
                $stmt->bind_param("i", $PersonnelId);
                $stmt->execute();
                break;
                
            case 'add_service':
                $Service_type = $_POST['Service_type'];
                
                $stmt = $conn->prepare("INSERT INTO services (Service_type) VALUES (?)");
                $stmt->bind_param("s", $Service_type);
                $stmt->execute();
                break;
                
            case 'edit_service':
                $serviceID = $_POST['serviceID'];
                $Service_type = $_POST['Service_type'];
                
                $stmt = $conn->prepare("UPDATE services SET Service_type = ? WHERE serviceID = ?");
                $stmt->bind_param("si", $Service_type, $serviceID);
                $stmt->execute();
                break;
                
            case 'delete_service':
                $serviceID = $_POST['serviceID'];
                $stmt = $conn->prepare("DELETE FROM services WHERE serviceID = ?");
                $stmt->bind_param("i", $serviceID);
                $stmt->execute();
                break;
        }
        
        // Refresh page to show updated data
        header("Location: system_data.php");
        exit();
    }
}

// Fetch data for display
$rooms = $conn->query("SELECT * FROM rooms ORDER BY id DESC");
$equipment = $conn->query("SELECT * FROM equipmentfacility ORDER BY EFID DESC");
$personnel = $conn->query("SELECT * FROM personnels ORDER BY PersonnelId DESC");
$services = $conn->query("SELECT * FROM services ORDER BY serviceID DESC");
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
            font-weight: 600;
            background: #f9fafb;
            overflow-x: hidden;
        }

        /* Navbar styling - matching your existing style */
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
            font-weight: bold; 
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
            background: white;
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
            color: #333;
            font-size: 14px;
        }

        .dropdown .username-btn {
            color: white !important;
            background: none !important;
            border: none !important;
            font-weight: bold;
            font-size: 16px;
        }

        .container {
            padding: 40px 60px;
            gap: 30px;
        }

        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .table thead {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 600;
        }

        .nav-tabs .nav-link {
            color: #495057;
            font-weight: 500;
        }

        .nav-tabs .nav-link.active {
            font-weight: 600;
            background-color: #fff;
            border-bottom-color: #fff;
        }

        .btn-primary {
            background-color: #275850;
            border-color: #275850;
        }

        .btn-primary:hover {
            background-color: #1f9158;
            border-color: #1f9158;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
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

    <div class="container mt-4">
        <h2 class="mb-4"><i class="fas fa-database me-2"></i>System Data Management</h2>
        
        <!-- Tabs for different data types -->
        <ul class="nav nav-tabs" id="dataTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="rooms-tab" data-bs-toggle="tab" data-bs-target="#rooms" type="button" role="tab">
                    <i class="fas fa-door-open me-1"></i>Rooms
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="equipment-tab" data-bs-toggle="tab" data-bs-target="#equipment" type="button" role="tab">
                    <i class="fas fa-desktop me-1"></i>Equipment/Facilities
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="personnel-tab" data-bs-toggle="tab" data-bs-target="#personnel" type="button" role="tab">
                    <i class="fas fa-users me-1"></i>Personnel
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="services-tab" data-bs-toggle="tab" data-bs-target="#services" type="button" role="tab">
                    <i class="fas fa-concierge-bell me-1"></i>Services
                </button>
            </li>
        </ul>
        
        <div class="tab-content p-3 border border-top-0 rounded-bottom" id="dataTabsContent">
            <!-- Rooms Tab -->
            <div class="tab-pane fade show active" id="rooms" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Room Management</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                        <i class="fas fa-plus me-1"></i>Add Room
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Room Name</th>
                                <th>Building Name</th>
                                <th>Date Added</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $rooms->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['roomname']); ?></td>
                                <td><?php echo htmlspecialchars($row['building_name']); ?></td>
                                <td><?php echo $row['date_added']; ?></td>
                                <td><?php echo $row['last_updated']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-room" 
                                            data-id="<?php echo $row['id']; ?>"
                                            data-roomname="<?php echo htmlspecialchars($row['roomname']); ?>"
                                            data-building="<?php echo htmlspecialchars($row['building_name']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-room" data-id="<?php echo $row['id']; ?>">
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
            <div class="tab-pane fade" id="equipment" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Equipment/Facility Management</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
                        <i class="fas fa-plus me-1"></i>Add Equipment/Facility
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Date Added</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $equipment->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['EFID']; ?></td>
                                <td><?php echo htmlspecialchars($row['EFname']); ?></td>
                                <td><?php echo $row['date_added']; ?></td>
                                <td><?php echo $row['last_updated']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-equipment" 
                                            data-id="<?php echo $row['EFID']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['EFname']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-equipment" data-id="<?php echo $row['EFID']; ?>">
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
            <div class="tab-pane fade" id="personnel" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Personnel Management</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPersonnelModal">
                        <i class="fas fa-plus me-1"></i>Add Personnel
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $personnel->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['PersonnelId']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo $row['date_added']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-personnel" 
                                            data-id="<?php echo $row['PersonnelId']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['name']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-personnel" data-id="<?php echo $row['PersonnelId']; ?>">
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
            <div class="tab-pane fade" id="services" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Service Management</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                        <i class="fas fa-plus me-1"></i>Add Service
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Service Type</th>
                                <th>Date Added</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $services->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['serviceID']; ?></td>
                                <td><?php echo htmlspecialchars($row['Service_type']); ?></td>
                                <td><?php echo $row['date_added']; ?></td>
                                <td><?php echo $row['last_updated']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-service" 
                                            data-id="<?php echo $row['serviceID']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['Service_type']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-service" data-id="<?php echo $row['serviceID']; ?>">
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
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Room</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_room">
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Room</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_room">
                        <input type="hidden" name="id" id="edit_room_id">
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Equipment/Facility</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_equipment">
                        <div class="mb-3">
                            <label for="EFname" class="form-label">Equipment/Facility Name</label>
                            <input type="text" class="form-control" id="EFname" name="EFname" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Equipment/Facility</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_equipment">
                        <input type="hidden" name="EFID" id="edit_equipment_id">
                        <div class="mb-3">
                            <label for="edit_EFname" class="form-label">Equipment/Facility Name</label>
                            <input type="text" class="form-control" id="edit_EFname" name="EFname" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Personnel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_personnel">
                        <div class="mb-3">
                            <label for="name" class="form-label">Personnel Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Personnel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_personnel">
                        <input type="hidden" name="PersonnelId" id="edit_personnel_id">
                        <div class="mb-3">
                            <label for="edit_personnel_name" class="form-label">Personnel Name</label>
                            <input type="text" class="form-control" id="edit_personnel_name" name="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_service">
                        <div class="mb-3">
                            <label for="Service_type" class="form-label">Service Type</label>
                            <input type="text" class="form-control" id="Service_type" name="Service_type" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_service">
                        <input type="hidden" name="serviceID" id="edit_service_id">
                        <div class="mb-3">
                            <label for="edit_service_name" class="form-label">Service Type</label>
                            <input type="text" class="form-control" id="edit_service_name" name="Service_type" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="deleteForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this item? This action cannot be undone.</p>
                        <input type="hidden" name="action" id="delete_action">
                        <input type="hidden" name="id" id="delete_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Room edit functionality
            const editRoomButtons = document.querySelectorAll('.edit-room');
            editRoomButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const roomname = this.getAttribute('data-roomname');
                    const building = this.getAttribute('data-building');
                    
                    document.getElementById('edit_room_id').value = id;
                    document.getElementById('edit_roomname').value = roomname;
                    document.getElementById('edit_building_name').value = building;
                    
                    const editModal = new bootstrap.Modal(document.getElementById('editRoomModal'));
                    editModal.show();
                });
            });
            
            // Room delete functionality
            const deleteRoomButtons = document.querySelectorAll('.delete-room');
            deleteRoomButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    
                    document.getElementById('delete_action').value = 'delete_room';
                    document.getElementById('delete_id').value = id;
                    
                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                    deleteModal.show();
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
            
            // Equipment delete functionality
            const deleteEquipmentButtons = document.querySelectorAll('.delete-equipment');
            deleteEquipmentButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    
                    document.getElementById('delete_action').value = 'delete_equipment';
                    document.getElementById('delete_id').value = id;
                    
                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                    deleteModal.show();
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
            
            // Personnel delete functionality
            const deletePersonnelButtons = document.querySelectorAll('.delete-personnel');
            deletePersonnelButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    
                    document.getElementById('delete_action').value = 'delete_personnel';
                    document.getElementById('delete_id').value = id;
                    
                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                    deleteModal.show();
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
            
            // Service delete functionality
            const deleteServiceButtons = document.querySelectorAll('.delete-service');
            deleteServiceButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    
                    document.getElementById('delete_action').value = 'delete_service';
                    document.getElementById('delete_id').value = id;
                    
                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                    deleteModal.show();
                });
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>