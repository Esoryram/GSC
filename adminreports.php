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
$assignedToQuery  = "SELECT DISTINCT Assigned_to FROM Concerns WHERE Assigned_to IS NOT NULL AND Assigned_to != '' ORDER BY Assigned_to ASC";
$assignedToResult = mysqli_query($conn, $assignedToQuery);
$assignedOptions  = [];

if ($assignedToResult) {
    while ($row = mysqli_fetch_assoc($assignedToResult)) {
        $assignedOptions[] = $row['Assigned_to'];
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
        FROM Concerns c
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
            width = auto; 
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

        .dropdown .username-btn:hover,
        .dropdown .username-btn:focus {
            color: white !important;
            background: none !important;
            border: none !important;
        }

.page-container {
    padding: 30px 40px;
}

.report-controls {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.input-filter-width {
    width: 150px !important;
    padding: 10px 15px;
    font-weight: bold;
    border-radius: 8px;
    border: 1px solid #ced4da;
    background-color: white;
    font-size: 16px;
}

.btn-generate {
    background-color: #198754;
    color: white;
    padding: 10px 20px;
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
    background-color: #198754;
    color: white;
    padding: 5px 40px;
    font-weight: bold;
    border-radius: 8px;
    border: none;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    cursor: pointer;
    transition: background-color 0.3s;
    z-index: 1000;
}

.btn-print:hover {
    background-color: #146c43;
}

@media print {
    .btn-print,
    .btn-generate,
    .report-controls,
    .navbar {
        display: none !important;
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
    background: transparent;
    color: #198754;
    font-size: 20px;
    cursor: pointer;
    margin-left: 5px;
}

.refresh-btn:hover {
    color: #146c43;
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
    <form method="GET" action="adminreports.php">
        <input type="hidden" name="generate" value="1">
        <div class="report-controls">

            <!-- Room Dropdown -->
            <select class="form-select input-filter-width" name="room">
                <option value="All Rooms" <?= ($filterRoom == 'All Rooms' || $filterRoom == '') ? 'selected' : ''; ?>>Room</option>
                <?php foreach ($roomOptions as $room): ?>
                    <option value="<?= htmlspecialchars($room); ?>" <?= ($filterRoom == $room) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($room); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Assigned To Dropdown -->
            <select class="form-select input-filter-width" name="assigned">
                <option value="All Personnel" <?= ($filterAssignedTo == 'All Personnel' || $filterAssignedTo == '') ? 'selected' : ''; ?>>Assigned To</option>
                <?php foreach ($assignedOptions as $person): ?>
                    <option value="<?= htmlspecialchars($person); ?>" <?= ($filterAssignedTo == $person) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($person); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Status Dropdown -->
            <select class="form-select input-filter-width" name="status">
                <option value="All Statuses" <?= ($filterStatus == 'All Statuses' || $filterStatus == '') ? 'selected' : ''; ?>>Status</option>
                <option value="Pending" <?= ($filterStatus == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="In Progress" <?= ($filterStatus == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                <option value="Completed" <?= ($filterStatus == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                <option value="Cancelled" <?= ($filterStatus == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
            </select>

            <!-- Date Filter -->
            <?php $today = date('Y-m-d'); ?>
            <div style="display: flex; align-items: center; gap: 10px;">
                <input type="date" name="date_from" class="form-control input-filter-width"
                       max="<?= $today; ?>"
                       value="<?= isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>"
                       placeholder="From Date">
                <span style="font-weight: bold;">to</span>
                <input type="date" name="date_to" class="form-control input-filter-width"
                       max="<?= $today; ?>"
                       value="<?= isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>"
                       placeholder="To Date">

                <!-- Refresh Button -->
                <button type="button" class="refresh-btn" title="Reset Filters" onclick="window.location.href='adminreports.php'">
                    <i class="fas fa-sync-alt" style="font-size: 30px;"></i>
                </button>
            </div>

            <!-- Generate Button -->
            <button class="btn-generate" type="submit">Generate</button>
            <?php if ($generateClicked): ?>
                <button type="button" class="btn-print" onclick="window.print()">Print</button>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($generateClicked): ?>
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
                                    No concerns found matching the current filters.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
</body>
</html>