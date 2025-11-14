<?php 
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}
$username = $_SESSION['username'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;
$activePage = "concerns";

// Fetch personnel for the assign dropdown
$personnel_result = $conn->query("SELECT * FROM personnels ORDER BY name");
$buildings = $conn->query("SELECT DISTINCT building_name FROM rooms ORDER BY building_name");
$services = $conn->query("SELECT * FROM services ORDER BY Service_type");

// Fetch all rooms with building information for the dropdown
$rooms_result = $conn->query("SELECT roomname, building_name FROM rooms ORDER BY building_name, roomname");

// Fetch equipment/facilities from equipmentfacility table
$equipment_result = $conn->query("SELECT EFname FROM equipmentfacility ORDER BY EFname");

// UPDATED QUERY: Exclude Completed and Cancelled concerns
$query = "
    SELECT 
        c.ConcernID,
        c.Concern_Title,
        r.building_name,
        c.Room,
        c.Service_type,
        c.Concern_Date,
        c.Status,
        a.Name AS ReportedBy,
        c.Assigned_to,
        c.Description,
        c.EFname,
        c.Attachment
    FROM Concerns c
    LEFT JOIN Accounts a ON c.AccountID = a.AccountID
    LEFT JOIN rooms r ON c.Room = r.roomname
    WHERE c.Status NOT IN ('Completed', 'Cancelled')  -- ADDED: Exclude completed and cancelled concerns
    ORDER BY c.ConcernID ASC
";
$result = mysqli_query($conn, $query);

// Prepare rooms data for JavaScript
$rooms_by_building = [];
while ($room = $rooms_result->fetch_assoc()) {
    $building = $room['building_name'];
    if (!isset($rooms_by_building[$building])) {
        $rooms_by_building[$building] = [];
    }
    $rooms_by_building[$building][] = $room['roomname'];
}

// Prepare equipment data
$equipment_list = [];
while ($eq = $equipment_result->fetch_assoc()) {
    $equipment_list[] = $eq['EFname'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concerns</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            background: #f9fafb;
            overflow-x: hidden;
        }

        /* Navbar styling */
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

        /* Logo */
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

        /* Navbar links */
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
        }

        .dropdown .username-btn {
            color: white !important;
            background: none !important;
            border: none !important;
            font-weight: bold;
            font-size: 16px;
        }

        .dropdown .username-btn:hover,
        .dropdown .username-btn:focus {
            color: white !important;
            background: none !important;
            border: none !important;
        }

        .table thead {
            background: #198754;
            color: white;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 30px 40px 10px 40px;
        }

        .page-header h3 {
            color: #198754;
            font-weight: bold;
        }

        .table td,
        .table th {
            padding: 5px 8px;
        }

        .table-container {
            margin: 0 40px 40px 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .assign-btn {
            font-size: 13px;
            padding: 6px 16px;
            border-radius: 6px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            width: 100px;
            transition: 0.3s;
            text-align: center;
            background-color: #198754;
            color: white;
        }

        .assign-btn:hover {
            background-color: #157347;
        }

        .update-btn {
            font-size: 13px;
            padding: 6px 16px;
            border-radius: 6px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            width: 100px;
            transition: 0.3s;
            text-align: center;
            background-color: #0d6efd;
            color: white;
        }

        .update-btn:hover {
            background-color: #0b5ed7;
        }

        .custom-select {
            position: relative;
            user-select: none;
            width: 100%;
        }

        .custom-select .select-selected {
            background-color: white;
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 10px 12px;
            min-height: 44px;
            display: flex;
            align-items: center;
            font-size: 14px;
            cursor: pointer;
            transition: border 0.3s;
        }

        .custom-select .select-selected.placeholder {
            color: #6c757d;
        }

        .custom-select .select-items {
            position: absolute;
            background-color: white;
            border: 1px solid #ced4da;
            border-top: none;
            border-radius: 0 0 6px 6px;
            z-index: 99;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            display: none;
        }

        .custom-select .select-items div {
            padding: 10px 12px;
            cursor: pointer;
            font-size: 14px;
            border-bottom: 1px solid #f8f9fa;
            display: flex;
            align-items: center;
        }

        .custom-select .select-items div:hover {
            background-color: #f8f9fa;
        }

        .custom-select .select-items div:last-child {
            border-bottom: none;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .attachment-preview {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            display: none;
        }

        .view-attachment-btn {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .view-attachment-btn:hover {
            background: #0b5ed7;
        }

        .other-container {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 12px;
            margin-top: 8px;
            display: none;
        }

        .other-container.show {
            display: block;
        }

        .equipment-dropdown {
            position: relative;
            width: 100%;
        }

        .equipment-dropdown .dropdown-toggle {
            background-color: white;
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 10px 12px;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 14px;
            cursor: pointer;
            width: 100%;
            text-align: left;
        }

        .equipment-dropdown .dropdown-toggle.placeholder {
            color: #6c757d;
        }

        .equipment-dropdown .dropdown-menu {
            position: absolute;
            background-color: white;
            border: 1px solid #ced4da;
            border-radius: 6px;
            z-index: 99;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            padding: 10px;
        }

        .equipment-dropdown .dropdown-menu.show {
            display: block;
        }

        .equipment-checklist {
            max-height: 150px;
            overflow-y: auto;
        }

        .equipment-checklist .form-check {
            margin-bottom: 8px;
        }

        .equipment-checklist .form-check-input {
            margin-right: 8px;
        }

        .equipment-checklist .form-check-label {
            font-size: 14px;
            cursor: pointer;
            font-weight: normal;
        }

        .read-only-field {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 10px 12px;
            min-height: 44px;
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #495057;
        }

        .selected-equipment-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 4px;
        }

        .other-input-field {
            margin-top: 8px;
        }

        /* ADDED: Empty state styling */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            color: #198754;
        }

        .empty-state h4 {
            margin-bottom: 8px;
            color: #495057;
        }

        /* UPDATED: Modal header gradient */
        .modal-header-gradient {
            background: linear-gradient(135deg, #087830, #3c4142) !important;
            color: white;
        }
    </style>
</head>

<body>

    <div class="navbar">
        <div class="logo">
            <img src="img/LSULogo.png" alt="LSU Logo">
        </div>

        <div class="links">
            <a href="admindb.php" class="<?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-home me-1"></i> Dashboard
            </a>
            <a href="adminannouncement.php" class="<?php echo ($activePage == 'announcements') ? 'active' : ''; ?>">
                <i class="fas fa-bullhorn"></i> Announcements
            </a>
            <a href="adminconcerns.php" class="<?php echo ($activePage == 'concerns') ? 'active' : ''; ?>">
                <i class="fas fa-list-ul me-1"></i> Concerns
            </a>
            <a href="adminfeedback.php" class="<?php echo ($activePage == 'feedback') ? 'active' : ''; ?>">
                <i class="fas fa-comment-alt"></i> Feedback
            </a>
            <a href="adminreports.php" class="<?php echo ($activePage == 'reports') ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a href="system_data.php" class="<?php echo ($activePage == 'reports') ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> System Data
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

    <div class="page-header">
        <h3><i class="fas fa-list-ul me-2"></i>All Concerns</h3>
    </div>

    <div class="table-container mx-4">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 text-center">
                <thead class="table-success">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Room</th>
                        <th>Service Type</th>
                        <th>Concern Date</th>
                        <th>Status</th>
                        <th>Reported By</th>
                        <th>Assigned To</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    mysqli_data_seek($result, 0);
                    
                    // Check if there are any active concerns
                    if (mysqli_num_rows($result) > 0): 
                        while ($row = mysqli_fetch_assoc($result)): 
                            $statusClass = '';
                            switch ($row['Status']) {
                                case 'Completed':
                                    $statusClass = 'bg-success text-white'; // Green
                                    break;
                                case 'In Progress':
                                    $statusClass = 'bg-primary text-white'; // Blue
                                    break;
                                case 'Pending':
                                    $statusClass = 'bg-warning text-dark'; // Yellow
                                    break;
                                case 'Cancelled':
                                    $statusClass = 'bg-danger text-white'; // Red
                                    break;
                                default:
                                    $statusClass = 'bg-secondary text-white';
                            }

                            $assignedName = trim($row['Assigned_to']);
                            $displayAssigned = empty($assignedName) ? 'Not Assigned' : htmlspecialchars($assignedName);
                            
                            // Determine button text and class based on whether concern is already assigned
                            $buttonText = empty($assignedName) ? 'Assign' : 'Update';
                            $buttonClass = empty($assignedName) ? 'assign-btn' : 'update-btn';
                    ?>
                    <tr>
                        <td><?php echo $row['ConcernID']; ?></td>
                        <td><?php echo htmlspecialchars($row['Concern_Title']); ?></td>
                        <td><?php echo htmlspecialchars($row['Room']); ?></td>
                        <td><?php echo htmlspecialchars($row['Service_type']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($row['Concern_Date'])); ?></td>
                        <td>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($row['Status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['ReportedBy']); ?></td>
                        <td><?php echo $displayAssigned; ?></td>
                        <td>
                            <button 
                                class="<?php echo $buttonClass; ?>" 
                                data-bs-toggle="modal" 
                                data-bs-target="#assignModal"
                                data-concernid="<?php echo $row['ConcernID']; ?>"
                                data-reportedby="<?php echo htmlspecialchars($row['ReportedBy']); ?>"
                                data-currentstatus="<?php echo htmlspecialchars($row['Status']); ?>"
                                data-currentassigned="<?php echo htmlspecialchars($assignedName); ?>"
                                data-title="<?php echo htmlspecialchars($row['Concern_Title']); ?>"
                                data-description="<?php echo htmlspecialchars($row['Description']); ?>"
                                data-room="<?php echo htmlspecialchars($row['Room']); ?>"
                                data-service="<?php echo htmlspecialchars($row['Service_type']); ?>"
                                data-equipment="<?php echo htmlspecialchars($row['EFname']); ?>"
                                data-attachment="<?php echo htmlspecialchars($row['Attachment']); ?>"
                                data-building="<?php echo htmlspecialchars($row['building_name']); ?>"
                                data-isassigned="<?php echo empty($assignedName) ? 'false' : 'true'; ?>">
                                <?php echo $buttonText; ?>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <!-- ADDED: Empty state row -->
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <h4>No Active Concerns</h4>
                                <p>All concerns have been completed or cancelled. New concerns will appear here when submitted.</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Assign Modal -->
    <div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <!-- UPDATED: Changed from bg-success to custom gradient -->
                <div class="modal-header modal-header-gradient">
                    <h5 class="modal-title" id="assignModalLabel">
                        <i class="fas fa-user-check me-2"></i>Manage Concern
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignForm" method="POST" action="update_concern.php">
                    <div class="modal-body">
                        <!-- Top Section: Reported By, Concern ID, Status, Assign To -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Reported By:</label>
                                    <div class="form-control bg-light" id="modalReportedBy"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Concern ID:</label>
                                    <div class="form-control bg-light" id="modalConcernID"></div>
                                    <input type="hidden" name="concern_id" id="concernIdInput">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status:</label>
                                    <div class="custom-select" id="statusSelect">
                                        <div class="select-selected placeholder">Select status</div>
                                        <div class="select-items">
                                            <div data-value="Pending">Pending</div>
                                            <div data-value="In Progress">In Progress</div>
                                            <div data-value="Completed">Completed</div>
                                            <div data-value="Cancelled">Cancelled</div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="status" id="statusInput" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Assign To:</label>
                                    <div class="custom-select" id="personnelSelect">
                                        <div class="select-selected placeholder">Select personnel</div>
                                        <div class="select-items">
                                            <?php 
                                            mysqli_data_seek($personnel_result, 0);
                                            while($personnel = $personnel_result->fetch_assoc()): ?>
                                                <div data-value="<?php echo htmlspecialchars($personnel['name']); ?>">
                                                    <?php echo htmlspecialchars($personnel['name']); ?>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                                    <input type="hidden" name="assigned_to" id="assignedToInput" required>
                                </div>
                            </div>
                        </div>

                        <!-- Middle Section: 3 Columns - 1st Row -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Title:</label>
                                    <div class="read-only-field" id="modalTitleDisplay"></div>
                                    <input type="hidden" name="title" id="modalTitle">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Building:</label>
                                    <div class="custom-select" id="buildingSelect">
                                        <div class="select-selected placeholder">Select building</div>
                                        <div class="select-items">
                                            <?php 
                                            mysqli_data_seek($buildings, 0);
                                            while($building = $buildings->fetch_assoc()): ?>
                                                <div data-value="<?php echo htmlspecialchars($building['building_name']); ?>">
                                                    <?php echo htmlspecialchars($building['building_name']); ?>
                                                </div>
                                            <?php endwhile; ?>
                                            <div data-value="Other">Other</div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="building" id="buildingInput">
                                    <div class="other-container" id="otherBuildingContainer">
                                        <label class="form-label">Enter Building Name:</label>
                                        <input type="text" class="form-control" id="other_building" name="other_building" placeholder="Type building name here">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Service Type:</label>
                                    <div class="custom-select" id="serviceSelect">
                                        <div class="select-selected placeholder">Select service type</div>
                                        <div class="select-items">
                                            <?php 
                                            mysqli_data_seek($services, 0);
                                            while($service = $services->fetch_assoc()): ?>
                                                <div data-value="<?php echo htmlspecialchars($service['Service_type']); ?>">
                                                    <?php echo htmlspecialchars($service['Service_type']); ?>
                                                </div>
                                            <?php endwhile; ?>
                                            <div data-value="Other">Other</div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="service_type" id="serviceInput">
                                    <div class="other-container" id="otherServiceContainer">
                                        <label class="form-label">Enter Service Type:</label>
                                        <input type="text" class="form-control" id="other_service" name="other_service" placeholder="Type service type here">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Middle Section: 3 Columns - 2nd Row -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Description:</label>
                                    <div class="read-only-field" id="modalDescriptionDisplay" style="min-height: auto; height: auto;"></div>
                                    <input type="hidden" name="description" id="modalDescription">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Room:</label>
                                    <div class="custom-select" id="roomSelect">
                                        <div class="select-selected placeholder">Select room</div>
                                        <div class="select-items" id="roomOptions">
                                            <!-- Room options will be populated dynamically -->
                                        </div>
                                    </div>
                                    <input type="hidden" name="room" id="roomInput">
                                    <div class="other-container" id="otherRoomContainer">
                                        <label class="form-label">Enter Room Name:</label>
                                        <input type="text" class="form-control" id="other_room" name="other_room" placeholder="Type room name here">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Equipment/Facility:</label>
                                    <div class="equipment-dropdown" id="equipmentDropdown">
                                        <button type="button" class="dropdown-toggle placeholder" id="equipmentToggle">
                                            Select equipment/facility
                                        </button>
                                        <div class="dropdown-menu" id="equipmentMenu">
                                            <div class="equipment-checklist" id="equipmentChecklist">
                                                <!-- Equipment checkboxes will be populated dynamically -->
                                            </div>
                                            <div class="mt-3 p-2 border-top">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="equipmentOtherCheckbox">
                                                    <label class="form-check-label fw-bold" for="equipmentOtherCheckbox">
                                                        Other
                                                    </label>
                                                </div>
                                                <div class="other-input-field" id="otherEquipmentInput" style="display: none;">
                                                    <label class="form-label small">Enter Equipment Name:</label>
                                                    <input type="text" class="form-control form-control-sm" id="other_equipment" name="other_equipment" placeholder="Type equipment name here">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="equipment" id="equipmentInput">
                                    <div class="selected-equipment-text" id="selectedEquipmentText"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Attachment Section -->
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Attachment:</label>
                                    <div class="d-flex align-items-center gap-3">
                                        <button type="button" class="view-attachment-btn" id="viewAttachmentBtn">
                                            <i class="fas fa-eye me-1"></i>View Attachment
                                        </button>
                                        <span id="attachmentFileName" class="text-muted"></span>
                                    </div>
                                    <img id="attachmentPreview" class="attachment-preview mt-2" alt="Attachment Preview">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>Update Concern
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
    // Rooms data from PHP
    const roomsByBuilding = <?php echo json_encode($rooms_by_building); ?>;
    const equipmentList = <?php echo json_encode($equipment_list); ?>;

    // Custom select functionality for modal
    function initCustomSelect(selectId, hiddenInputId, otherContainerId = null) {
        const select = document.getElementById(selectId);
        const selected = select.querySelector('.select-selected');
        const items = select.querySelector('.select-items');
        const hiddenInput = document.getElementById(hiddenInputId);
        const otherContainer = otherContainerId ? document.getElementById(otherContainerId) : null;

        selected.addEventListener('click', (e) => {
            e.stopPropagation();
            closeAllDropdowns();
            items.style.display = items.style.display === 'block' ? 'none' : 'block';
        });

        const options = items.querySelectorAll('div');
        options.forEach(option => {
            option.addEventListener('click', () => {
                selected.textContent = option.textContent;
                selected.classList.remove('placeholder');
                hiddenInput.value = option.dataset.value;
                items.style.display = 'none';

                // Handle "Other" option
                if (otherContainer) {
                    if (option.dataset.value === 'Other') {
                        otherContainer.classList.add('show');
                        // Clear the hidden input since we're using custom input
                        hiddenInput.value = '';
                    } else {
                        otherContainer.classList.remove('show');
                        // Clear the other input
                        const otherInput = otherContainer.querySelector('input');
                        otherInput.value = '';
                    }
                }

                // Trigger building change event
                if (selectId === 'buildingSelect') {
                    const selectedBuilding = option.dataset.value;
                    updateRoomOptions(selectedBuilding);
                }
            });
        });

        // Close dropdown when clicking elsewhere
        document.addEventListener('click', (e) => {
            if (!select.contains(e.target)) {
                items.style.display = 'none';
            }
        });
    }

    // Initialize equipment dropdown
    function initEquipmentDropdown() {
        const equipmentToggle = document.getElementById('equipmentToggle');
        const equipmentMenu = document.getElementById('equipmentMenu');
        const equipmentChecklist = document.getElementById('equipmentChecklist');
        const equipmentOtherCheckbox = document.getElementById('equipmentOtherCheckbox');
        const otherEquipmentInput = document.getElementById('otherEquipmentInput');
        const equipmentInput = document.getElementById('equipmentInput');
        const selectedEquipmentText = document.getElementById('selectedEquipmentText');

        // Populate equipment checklist
        equipmentChecklist.innerHTML = '';
        equipmentList.forEach(equipment => {
            const div = document.createElement('div');
            div.className = 'form-check';
            div.innerHTML = `
                <input class="form-check-input equipment-checkbox" type="checkbox" value="${equipment}" id="eq_${equipment.replace(/\s+/g, '_')}">
                <label class="form-check-label" for="eq_${equipment.replace(/\s+/g, '_')}" style="font-size: 14px;">
                    ${equipment}
                </label>
            `;
            equipmentChecklist.appendChild(div);
        });

        // Toggle dropdown
        equipmentToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            closeAllDropdowns();
            equipmentMenu.classList.toggle('show');
        });

        // Handle checkbox changes
        equipmentChecklist.addEventListener('change', updateEquipmentSelection);
        
        // Handle "Other" checkbox
        equipmentOtherCheckbox.addEventListener('change', function() {
            if (this.checked) {
                otherEquipmentInput.style.display = 'block';
            } else {
                otherEquipmentInput.style.display = 'none';
                document.getElementById('other_equipment').value = '';
            }
            updateEquipmentSelection();
        });

        // Close dropdown when clicking elsewhere
        document.addEventListener('click', (e) => {
            if (!equipmentDropdown.contains(e.target)) {
                equipmentMenu.classList.remove('show');
            }
        });

        // Update equipment selection
        function updateEquipmentSelection() {
            const selectedEquipment = [];
            const checkboxes = document.querySelectorAll('.equipment-checkbox:checked');
            checkboxes.forEach(checkbox => {
                selectedEquipment.push(checkbox.value);
            });

            // Add "Other" value if checked and has input
            if (equipmentOtherCheckbox.checked) {
                const otherInput = document.getElementById('other_equipment');
                if (otherInput.value.trim() !== '') {
                    selectedEquipment.push(otherInput.value.trim());
                }
            }

            // Update hidden input
            equipmentInput.value = selectedEquipment.join(', ');

            // Update toggle button text
            if (selectedEquipment.length > 0) {
                equipmentToggle.textContent = selectedEquipment.length + ' item(s) selected';
                equipmentToggle.classList.remove('placeholder');
                selectedEquipmentText.textContent = 'Selected: ' + selectedEquipment.join(', ');
            } else {
                equipmentToggle.textContent = 'Select equipment/facility';
                equipmentToggle.classList.add('placeholder');
                selectedEquipmentText.textContent = '';
            }
        }

        // Update when other equipment input changes
        document.getElementById('other_equipment').addEventListener('input', updateEquipmentSelection);
    }

    // Update room options based on selected building
    function updateRoomOptions(building) {
        const roomOptions = document.getElementById('roomOptions');
        const roomSelected = document.querySelector('#roomSelect .select-selected');
        const roomInput = document.getElementById('roomInput');
        const otherRoomContainer = document.getElementById('otherRoomContainer');
        
        roomOptions.innerHTML = '';
        
        if (building && building !== 'Other' && roomsByBuilding[building]) {
            // Add rooms for selected building
            roomsByBuilding[building].forEach(room => {
                const option = document.createElement('div');
                option.setAttribute('data-value', room);
                option.textContent = room;
                roomOptions.appendChild(option);
            });
        } else {
            // If no specific building or "Other" selected, show all rooms
            Object.keys(roomsByBuilding).forEach(bldg => {
                roomsByBuilding[bldg].forEach(room => {
                    const option = document.createElement('div');
                    option.setAttribute('data-value', room);
                    option.textContent = room;
                    roomOptions.appendChild(option);
                });
            });
        }
        
        // Always add Other option
        const otherOption = document.createElement('div');
        otherOption.setAttribute('data-value', 'Other');
        otherOption.textContent = 'Other';
        roomOptions.appendChild(otherOption);
        
        // Re-initialize the room select to make new options clickable
        initCustomSelect('roomSelect', 'roomInput', 'otherRoomContainer');
    }

    // Initialize all custom selects
    function initializeAllSelects() {
        initCustomSelect('personnelSelect', 'assignedToInput');
        initCustomSelect('statusSelect', 'statusInput');
        initCustomSelect('buildingSelect', 'buildingInput', 'otherBuildingContainer');
        initCustomSelect('serviceSelect', 'serviceInput', 'otherServiceContainer');
        
        // Initialize room select with empty options first
        initCustomSelect('roomSelect', 'roomInput', 'otherRoomContainer');
        
        // Initialize equipment dropdown
        initEquipmentDropdown();
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeAllSelects();
        // Hide all other containers initially
        document.querySelectorAll('.other-container').forEach(container => {
            container.classList.remove('show');
        });
    });

    // Function to close all dropdowns
    function closeAllDropdowns() {
        const allSelects = ['personnelSelect', 'statusSelect', 'buildingSelect', 'serviceSelect', 'roomSelect'];
        
        allSelects.forEach(selectId => {
            const select = document.getElementById(selectId);
            if (select) {
                const items = select.querySelector('.select-items');
                if (items) {
                    items.style.display = 'none';
                }
            }
        });

        // Close equipment dropdown
        document.getElementById('equipmentMenu').classList.remove('show');
    }

    // Modal event handler
    const assignModal = document.getElementById('assignModal');
    assignModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const concernId = button.getAttribute('data-concernid');
        const reportedBy = button.getAttribute('data-reportedby');
        const currentStatus = button.getAttribute('data-currentstatus');
        const currentAssigned = button.getAttribute('data-currentassigned');
        const title = button.getAttribute('data-title');
        const description = button.getAttribute('data-description');
        const room = button.getAttribute('data-room');
        const service = button.getAttribute('data-service');
        const equipment = button.getAttribute('data-equipment');
        const attachment = button.getAttribute('data-attachment');
        const building = button.getAttribute('data-building');
        const isAssigned = button.getAttribute('data-isassigned') === 'true';

        // Update modal content
        document.getElementById('modalConcernID').textContent = concernId;
        document.getElementById('modalReportedBy').textContent = reportedBy;
        document.getElementById('concernIdInput').value = concernId;
        
        // Set title and description as read-only
        document.getElementById('modalTitleDisplay').textContent = title || 'No title provided';
        document.getElementById('modalTitle').value = title || '';
        document.getElementById('modalDescriptionDisplay').textContent = description || 'No description provided';
        document.getElementById('modalDescription').value = description || '';

        // Set current values in dropdowns - SHOW USER'S ORIGINAL INPUT IN EDITABLE FIELDS
        const personnelSelected = document.querySelector('#personnelSelect .select-selected');
        const statusSelected = document.querySelector('#statusSelect .select-selected');
        const buildingSelected = document.querySelector('#buildingSelect .select-selected');
        const serviceSelected = document.querySelector('#serviceSelect .select-selected');
        const roomSelected = document.querySelector('#roomSelect .select-selected');
        
        // Set building dropdown with user's original input
        if (building && building !== '') {
            buildingSelected.textContent = building;
            buildingSelected.classList.remove('placeholder');
            document.getElementById('buildingInput').value = building;
            
            // Update room options based on building
            updateRoomOptions(building);
        } else {
            buildingSelected.textContent = 'Select building';
            buildingSelected.classList.add('placeholder');
            document.getElementById('buildingInput').value = '';
        }

        // Set room dropdown with user's original input
        if (room && room !== '') {
            // Check if room exists in the current building's rooms
            let roomExists = false;
            if (building && roomsByBuilding[building]) {
                roomExists = roomsByBuilding[building].includes(room);
            }
            
            if (roomExists) {
                roomSelected.textContent = room;
                roomSelected.classList.remove('placeholder');
                document.getElementById('roomInput').value = room;
            } else {
                // If room doesn't exist in dropdown, set to "Other" and show input
                roomSelected.textContent = 'Other';
                roomSelected.classList.remove('placeholder');
                document.getElementById('roomInput').value = 'Other';
                document.getElementById('other_room').value = room;
                document.getElementById('otherRoomContainer').classList.add('show');
            }
        } else {
            roomSelected.textContent = 'Select room';
            roomSelected.classList.add('placeholder');
            document.getElementById('roomInput').value = '';
        }

        // Set service type dropdown with user's original input
        if (service && service !== '') {
            serviceSelected.textContent = service;
            serviceSelected.classList.remove('placeholder');
            document.getElementById('serviceInput').value = service;
        } else {
            serviceSelected.textContent = 'Select service type';
            serviceSelected.classList.add('placeholder');
            document.getElementById('serviceInput').value = '';
        }

        if (currentAssigned && currentAssigned !== '') {
            personnelSelected.textContent = currentAssigned;
            personnelSelected.classList.remove('placeholder');
            document.getElementById('assignedToInput').value = currentAssigned;
        } else {
            personnelSelected.textContent = 'Select personnel';
            personnelSelected.classList.add('placeholder');
            document.getElementById('assignedToInput').value = '';
        }

        if (currentStatus) {
            statusSelected.textContent = currentStatus;
            statusSelected.classList.remove('placeholder');
            document.getElementById('statusInput').value = currentStatus;
        } else {
            statusSelected.textContent = 'Select status';
            statusSelected.classList.add('placeholder');
            document.getElementById('statusInput').value = '';
        }

        // Pre-select equipment checkboxes with user's original input
        if (equipment && equipment !== '') {
            const equipmentArray = equipment.split(',').map(item => item.trim());
            const checkboxes = document.querySelectorAll('.equipment-checkbox');
            
            // Clear all checkboxes first
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('equipmentOtherCheckbox').checked = false;
            document.getElementById('other_equipment').value = '';
            document.getElementById('otherEquipmentInput').style.display = 'none';
            
            // Check equipment from database
            checkboxes.forEach(checkbox => {
                if (equipmentArray.includes(checkbox.value)) {
                    checkbox.checked = true;
                }
            });
            
            // Check for "Other" equipment
            equipmentArray.forEach(item => {
                if (!equipmentList.includes(item)) {
                    document.getElementById('equipmentOtherCheckbox').checked = true;
                    document.getElementById('other_equipment').value = item;
                    document.getElementById('otherEquipmentInput').style.display = 'block';
                }
            });
            
            // Update equipment display
            updateEquipmentSelection();
        }

        // Handle attachment
        const attachmentBtn = document.getElementById('viewAttachmentBtn');
        const attachmentFileName = document.getElementById('attachmentFileName');
        const attachmentPreview = document.getElementById('attachmentPreview');
        
        if (attachment && attachment !== '') {
            const fileName = attachment.split('/').pop();
            attachmentFileName.textContent = fileName;
            attachmentPreview.src = attachment;
            attachmentBtn.disabled = false;
            attachmentBtn.style.opacity = '1';
            
            attachmentBtn.onclick = function() {
                if (attachmentPreview.style.display === 'block') {
                    attachmentPreview.style.display = 'none';
                } else {
                    attachmentPreview.style.display = 'block';
                }
            };
        } else {
            attachmentFileName.textContent = 'No attachment';
            attachmentBtn.disabled = true;
            attachmentBtn.style.opacity = '0.6';
        }

        // Update modal title based on whether concern is already assigned
        const modalTitle = document.getElementById('assignModalLabel');
        if (isAssigned) {
            modalTitle.innerHTML = '<i class="fas fa-edit me-2"></i>Update Concern';
        } else {
            modalTitle.innerHTML = '<i class="fas fa-user-check me-2"></i>Assign Concern';
        }
    });

    // Form submission with SweetAlert2
    const assignForm = document.getElementById('assignForm');
    assignForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const assignedTo = document.getElementById('assignedToInput').value;
        const status = document.getElementById('statusInput').value;

        if (!assignedTo || !status) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select both personnel and status before submitting.',
                confirmButtonColor: '#198754'
            });
            return;
        }

        // Show loading state
        Swal.fire({
            title: 'Updating Concern...',
            text: 'Please wait while we update the concern.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Prepare form data
        const formData = new FormData(assignForm);

        // Send AJAX request
        fetch('update_concern.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    confirmButtonColor: '#198754',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Close modal and refresh page
                        const modal = bootstrap.Modal.getInstance(document.getElementById('assignModal'));
                        modal.hide();
                        location.reload();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message,
                    confirmButtonColor: '#198754'
                });
            }
        })
        .catch(error => {
            Swal.close();
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'An error occurred while updating the concern. Please try again.',
                confirmButtonColor: '#198754'
            });
        });
    });

    // Close dropdowns when clicking elsewhere
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.custom-select') && !e.target.closest('.equipment-dropdown')) {
            closeAllDropdowns();
        }
    });
</script>
</body>
</html>