<?php 
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}
$username   = $_SESSION['username'];
$name       = isset($_SESSION['name']) ? $_SESSION['name'] : $username;
$activePage = "reports";

$filterRoom       = isset($_GET['room']) ? mysqli_real_escape_string($conn, $_GET['room']) : '';
$filterAssignedTo = isset($_GET['assigned']) ? mysqli_real_escape_string($conn, $_GET['assigned']) : '';
$filterStatus     = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$filterService    = isset($_GET['service']) ? mysqli_real_escape_string($conn, $_GET['service']) : '';
$filterDateFrom   = isset($_GET['date_from']) ? mysqli_real_escape_string($conn, $_GET['date_from']) : '';
$filterDateTo     = isset($_GET['date_to']) ? mysqli_real_escape_string($conn, $_GET['date_to']) : '';
$generateClicked  = isset($_GET['generate']); // Check if Generate button was clicked

// Fetch unique room numbers for the dropdown
$roomsQuery  = "SELECT DISTINCT Room FROM Concerns WHERE Room IS NOT NULL AND Room != '' ORDER BY Room ASC";
$roomsResult = mysqli_query($conn, $roomsQuery);
$roomOptions = [];

if ($roomsResult) {
    while ($row = mysqli_fetch_assoc($roomsResult)) {
        $roomOptions[] = $row['Room'];
    }
}

// Fetch unique assigned personnel
$assignedToQuery  = "SELECT DISTINCT Assigned_to FROM concerns WHERE Assigned_to IS NOT NULL AND Assigned_to != '' ORDER BY Assigned_to ASC";
$assignedToResult = mysqli_query($conn, $assignedToQuery);
$assignedOptions  = [];

if ($assignedToResult) {
    while ($row = mysqli_fetch_assoc($assignedToResult)) {
        $assignedOptions[] = $row['Assigned_to'];
    }
}

// Fetch unique service types
$serviceQuery  = "SELECT DISTINCT Service_type FROM concerns WHERE Service_type IS NOT NULL AND Service_type != '' ORDER BY Service_type ASC";
$serviceResult = mysqli_query($conn, $serviceQuery);
$serviceOptions = [];

if ($serviceResult) {
    while ($row = mysqli_fetch_assoc($serviceResult)) {
        $serviceOptions[] = $row['Service_type'];
    }
}

// Fetch concerns only if Generate was clicked
$concernsData = [];
if ($generateClicked) {
    $query = "
        SELECT 
            c.ConcernID,
            c.Description,
            c.Room,
            c.Service_type,
            c.Concern_Date,
            c.Status,
            c.Assigned_to
        FROM concerns c
        WHERE 1=1
    ";

    if (!empty($filterRoom) && $filterRoom !== 'All Rooms') {
        $query .= " AND c.Room = '$filterRoom'";
    }
    if (!empty($filterAssignedTo) && $filterAssignedTo !== 'All Personnel') {
        $query .= " AND c.Assigned_to = '$filterAssignedTo'";
    }
    if (!empty($filterStatus) && $filterStatus !== 'All Statuses') {
        $query .= " AND c.Status = '$filterStatus'";
    }
    if (!empty($filterService) && $filterService !== 'All Services') {
        $query .= " AND c.Service_type = '$filterService'";
    }
    if (!empty($filterDateFrom) && !empty($filterDateTo)) {
        $query .= " AND DATE(c.Concern_Date) BETWEEN '$filterDateFrom' AND '$filterDateTo'";
    } elseif (!empty($filterDateFrom)) {
        $query .= " AND DATE(c.Concern_Date) >= '$filterDateFrom'";
    } elseif (!empty($filterDateTo)) {
        $query .= " AND DATE(c.Concern_Date) <= '$filterDateTo'";
    }

    $query .= " ORDER BY c.ConcernID ASC";
    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Format the date to "Nov 14, 2025" format
            $row['Formatted_Date'] = date('M j, Y', strtotime($row['Concern_Date']));
            $concernsData[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<!-- Google Fonts Poppins -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    /* Your existing CSS styles remain the same */
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
        color: white;
        background: none;
        border: none;
        font-weight: bold;
        font-size: 16px;
    }

    .dropdown .username-btn:hover,
    .dropdown .username-btn:focus {
        color: white;
        background: none;
        border: none;
    }

    .page-container {
        padding: 30px 40px;
        position: relative;
    }

    .report-controls {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .custom-select-wrapper {
        position: relative;
        width: 140px;
    }

    .custom-select {
        width: 100%;
        padding: 8px 35px 8px 12px;
        font-weight: bold;
        border-radius: 8px;
        border: 1px solid #ced4da;
        background-color: white;
        font-size: 14px;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 16px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .custom-select:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
        outline: none;
    }

    .custom-select:hover {
        border-color: #198754;
    }

    .custom-select option {
        padding: 8px 12px;
        font-weight: 600;
    }

    .custom-select option:hover {
        background-color: #198754;
        color: white;
    }

    .btn-generate {
        background-color: #198754;
        color: white;
        padding: 8px 16px;
        font-weight: bold;
        border-radius: 8px;
        border: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        transition: background-color 0.3s;
        margin-left: auto;
    }

    .btn-generate:hover {
        background-color: #146c43;
    }

    .btn-print {
        position: fixed;
        bottom: 20px;
        right: 40px;
        background-color: #0d6efd;
        color: white;
        padding: 10px 25px;
        font-weight: bold;
        border-radius: 8px;
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-print:hover {
        background-color: #0b5ed7;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.4);
    }

    /* Enhanced Print Styles */
    @media print {
        body {
            font-family: 'Poppins', sans-serif;
            font-size: 12pt;
            background: white;
            color: black;
            margin: 0;
            padding: 0;
        }
        
        .btn-print,
        .btn-generate,
        .report-controls,
        .navbar,
        .page-title {
            display: none;
        }
        
        .page-container {
            padding: 0;
            margin: 0;
            width: 100%;
        }
        
        .print-header {
            display: block;
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #198754;
        }
        
        .print-title {
            color: #198754;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .print-subtitle {
            color: #6c757d;
            font-size: 14px;
        }
        
        .print-filter-info {
            margin: 15px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 12pt;
        }
        
        .print-filter-info strong {
            color: #198754;
        }
        
        .table-container {
            border: 1px solid #dee2e6;
            border-radius: 0;
            box-shadow: none;
            overflow: visible;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }
        
        .table thead {
            background-color: #198754;
            color: white;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .table th, .table td {
            padding: 8px;
            border: 1px solid #dee2e6;
        }
        
        .table-bordered {
            border: 1px solid #dee2e6;
        }
        
        .badge {
            border: 1px solid #000;
            padding: 4px 8px;
            font-size: 9pt;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .bg-success {
            background-color: #198754;
            color: white;
        }
        
        .bg-primary {
            background-color: #0d6efd;
            color: white;
        }
        
        .bg-warning {
            background-color: #ffc107;
            color: black;
        }
        
        .bg-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .bg-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        @page {
            size: landscape;
            margin: 0.5in;
        }
    }

    .table-container {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .table thead {
        background-color: #198754;
        color: white;
    }

    .table-bordered {
        border: 1px solid #dee2e6;
    }

    .refresh-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: #6c757d;
        color: white;
        font-size: 14px;
        cursor: pointer;
        border-radius: 8px;
        padding: 8px 12px;
        transition: background-color 0.3s;
    }

    .refresh-btn:hover {
        background: #5a6268;
    }

    .date-range-container {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f8f9fa;
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .date-range-label {
        font-weight: bold;
        color: #495057;
        white-space: nowrap;
    }

    .filter-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .page-title {
        color: #198754;
        font-weight: bold;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .action-buttons {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
    }

    .date-input {
        padding: 8px 12px;
        font-weight: bold;
        border-radius: 8px;
        border: 1px solid #ced4da;
        background-color: white;
        font-size: 14px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .date-input:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
        outline: none;
    }

    .date-input:hover {
        border-color: #198754;
    }

    .print-header {
        display: none;
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
            <a href="system_data.php" class="<?php echo ($activePage == 'system_data') ? 'active' : ''; ?>">
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

<div class="page-container">
    <h3 class="page-title">
        <i class="fas fa-chart-bar me-2"></i>Reports & Analytics
    </h3>
    
    <form method="GET" action="adminreports.php" id="reportForm">
        <input type="hidden" name="generate" value="1">
        
        <div class="filter-row">
            <div class="filter-group">
                <!-- Room Dropdown -->
                <div class="custom-select-wrapper">
                    <select class="custom-select" name="room">
                        <option value="All Rooms" <?= ($filterRoom == 'All Rooms' || $filterRoom == '') ? 'selected' : ''; ?>>All Rooms</option>
                        <?php foreach ($roomOptions as $room): ?>
                            <option value="<?= htmlspecialchars($room); ?>" <?= ($filterRoom == $room) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($room); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Dropdown -->
                <div class="custom-select-wrapper">
                    <select class="custom-select" name="status">
                        <option value="All Statuses" <?= ($filterStatus == 'All Statuses' || $filterStatus == '') ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="Pending" <?= ($filterStatus == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="In Progress" <?= ($filterStatus == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Completed" <?= ($filterStatus == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="Cancelled" <?= ($filterStatus == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>

                <!-- Assigned To Dropdown -->
                <div class="custom-select-wrapper">
                    <select class="custom-select" name="assigned">
                        <option value="All Personnel" <?= ($filterAssignedTo == 'All Personnel' || $filterAssignedTo == '') ? 'selected' : ''; ?>>All Personnel</option>
                        <?php foreach ($assignedOptions as $person): ?>
                            <option value="<?= htmlspecialchars($person); ?>" <?= ($filterAssignedTo == $person) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($person); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Service Type Dropdown -->
                <div class="custom-select-wrapper">
                    <select class="custom-select" name="service">
                        <option value="All Services" <?= ($filterService == 'All Services' || $filterService == '') ? 'selected' : ''; ?>>All Services</option>
                        <?php foreach ($serviceOptions as $service): ?>
                            <option value="<?= htmlspecialchars($service); ?>" <?= ($filterService == $service) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($service); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="action-buttons">
                <!-- Date Range -->
                <div class="date-range-container">
                    <span class="date-range-label">Date Range:</span>
                    <?php $today = date('Y-m-d'); ?>
                    <input type="date" name="date_from" class="date-input"
                           max="<?= $today; ?>"
                           value="<?= isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>"
                           placeholder="From">
                    <span style="font-weight: bold;">to</span>
                    <input type="date" name="date_to" class="date-input"
                           max="<?= $today; ?>"
                           value="<?= isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>"
                           placeholder="To">
                </div>

                <!-- Refresh Button -->
                <button type="button" class="refresh-btn" title="Reset All Filters" onclick="resetFilters()">
                    <i class="fas fa-sync-alt"></i>
                </button>

                <!-- Generate Button -->
                <button class="btn-generate" type="button" id="generateBtn">
                    <i class="fas fa-play me-1"></i> Generate Report
                </button>
            </div>
        </div>
    </form>

    <?php if ($generateClicked): ?>
        <!-- Print Header (only visible when printing) -->
        <div class="print-header">
            <div class="print-title">Concerns Report</div>
            <div class="print-subtitle">Generated on <?= date('F j, Y'); ?></div>
            
            <!-- Filter Information for Print -->
            <div class="print-filter-info">
                <strong>Filters Applied:</strong><br>
                <?php
                $filterText = [];
                if (!empty($filterRoom) && $filterRoom !== 'All Rooms') $filterText[] = "Room: $filterRoom";
                if (!empty($filterStatus) && $filterStatus !== 'All Statuses') $filterText[] = "Status: $filterStatus";
                if (!empty($filterAssignedTo) && $filterAssignedTo !== 'All Personnel') $filterText[] = "Assigned To: $filterAssignedTo";
                if (!empty($filterService) && $filterService !== 'All Services') $filterText[] = "Service Type: $filterService";
                if (!empty($filterDateFrom) || !empty($filterDateTo)) {
                    $dateRange = "";
                    if (!empty($filterDateFrom)) $dateRange .= "From: $filterDateFrom";
                    if (!empty($filterDateTo)) $dateRange .= (empty($dateRange) ? "" : " ") . "To: $filterDateTo";
                    $filterText[] = "Date Range: $dateRange";
                }
                
                if (empty($filterText)) {
                    echo "No specific filters applied (showing all records)";
                } else {
                    echo implode(' | ', $filterText);
                }
                ?>
            </div>
        </div>
        
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Room</th>
                            <th>Type</th>
                            <th>Concern Date</th>
                            <th>Status</th>
                            <th>Assigned</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($concernsData)): ?>
                            <?php foreach ($concernsData as $row): ?>
                                <tr>
                                    <td><?= $row['ConcernID']; ?></td>
                                    <td><?= htmlspecialchars($row['Description']); ?></td>
                                    <td><?= htmlspecialchars($row['Room']); ?></td>
                                    <td><?= htmlspecialchars($row['Service_type']); ?></td>
                                    <td><?= $row['Formatted_Date']; ?></td>
                                    <td>
                                        <?php 
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
                                        ?>
                                        
                                        <span class="badge <?= $statusClass; ?> rounded-pill px-2 py-1">
                                            <?= htmlspecialchars($row['Status']); ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($row['Assigned_to']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-search fa-2x mb-3"></i><br>
                                    No concerns found matching the current filters.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Print button positioned at bottom right -->
        <?php if (!empty($concernsData)): ?>
            <button type="button" class="btn-print" id="printBtn">
                <i class="fas fa-print"></i> Print Report
            </button>
        <?php endif; ?>
    <?php endif; ?>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Generate Report Button with SweetAlert2
        const generateBtn = document.getElementById('generateBtn');
        if (generateBtn) {
            generateBtn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Generate Report?',
                    text: "This will generate a report based on your current filter selections.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, generate report!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Generating Report...',
                            text: 'Please wait while we process your request.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Submit the form
                        document.getElementById('reportForm').submit();
                    }
                });
            });
        }

        // Print Button with SweetAlert2
        const printBtn = document.getElementById('printBtn');
        if (printBtn) {
            printBtn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Print Report?',
                    text: "This will open the print dialog. Make sure your printer is ready.",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, print now!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.print();
                    }
                });
            });
        }
    });

    // Reset filters function
    function resetFilters() {
        Swal.fire({
            title: 'Reset Filters?',
            text: "This will clear all filter selections and refresh the page.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#6c757d',
            cancelButtonColor: '#198754',
            confirmButtonText: 'Yes, reset all!',
            cancelButtonText: 'Keep filters',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'adminreports.php';
            }
        });
    }
    </script>
</body>
</html>