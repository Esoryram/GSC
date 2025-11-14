<?php 
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}
$username = $_SESSION['username'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;
$activePage = "dashboard";

// FIXED: Total concerns (excluding Completed and Cancelled)
$totalQuery = "SELECT COUNT(*) AS total FROM Concerns WHERE Status NOT IN ('Completed', 'Cancelled')";
$totalResult = mysqli_query($conn, $totalQuery);
$totalRow = mysqli_fetch_assoc($totalResult);
$total = $totalRow['total'] ?? 0;

// Pending concerns
$pendingQuery = "SELECT COUNT(*) AS pending FROM Concerns WHERE Status = 'Pending'";
$pendingResult = mysqli_query($conn, $pendingQuery);
$pendingRow = mysqli_fetch_assoc($pendingResult);
$pending = $pendingRow['pending'] ?? 0;

// In Progress concerns
$inProgressQuery = "SELECT COUNT(*) AS inProgress FROM Concerns WHERE Status = 'In Progress'";
$inProgressResult = mysqli_query($conn, $inProgressQuery);
$inProgressRow = mysqli_fetch_assoc($inProgressResult);
$inProgress = $inProgressRow['inProgress'] ?? 0;

// FIXED: Recent concerns (excluding Completed and Cancelled)
$recentConcernsQuery = "
    SELECT 
        c.ConcernID, 
        c.Concern_Title, 
        c.Room, 
        c.Service_type, 
        c.Concern_Date,
        c.Status, 
        a.Name AS ReportedBy
    FROM Concerns c
    LEFT JOIN Accounts a ON c.AccountID = a.AccountID
    WHERE c.Status NOT IN ('Completed', 'Cancelled')
    ORDER BY c.ConcernID ASC
    LIMIT 3
";
$recentResult = mysqli_query($conn, $recentConcernsQuery);

// Fetch announcements
$today = date('Y-m-d');
$announcementsQuery = "
    SELECT Title, Content, Created_At 
    FROM announcements 
    WHERE end_date > '$today' OR end_date IS NULL
    ORDER BY Created_At DESC
";
$announcementsResult = mysqli_query($conn, $announcementsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

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

        .container {
            padding: 40px 60px;
            gap: 30px;
        }

        .top-dashboard-grid {
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .status-cards-wrapper {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .dashboard-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            text-align: left;
            min-height: 120px;
            border: 1px solid #e5e7eb;
        }

        .dashboard-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }

        .card-icon {
            font-size: 24px;
            opacity: 0.7;
            margin-bottom: 10px;
        }

        .card-value {
            font-size: 44px;
            font-weight: 700;
            margin: 0;
            line-height: 1;
        }

        .card-label {
            font-size: 16px;
            font-weight: 500;
            color: #6b7280;
            margin-top: 5px;
        }

        .card-total {
            color: #275850;
        }

        .card-total .card-icon {
            color: #1f9158;
        }

        .card-pending {
            background-color: #fffbeb;
            color: #b45309;
        }

        .card-pending .card-icon {
            color: #f59e0b;
        }

        .card-inprogress {
            background-color: #ecfdf5;
            color: #047857;
        }

        .card-inprogress .card-icon {
            color: #10b981;
        }

        .announcements-panel {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }

        .announcements-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .announcements-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #1f9158;
            margin: 0;
        }

        .add-btn {
            background-color: #1f9158;
            border: none;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .add-btn:hover {
            background-color: #107040;
            transform: scale(1.1);
        }

        .announcement-item {
            background: #f9fafb;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 10px;
            text-align: left;
            font-size: 14px;
            border-left: 3px solid #1f9158;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        #announcementsContainer {
            max-height: 130px;
            overflow-y: auto;
            scroll-behavior: smooth;
        }

        #announcementsContainer::-webkit-scrollbar {
            width: 6px;
        }

        #announcementsContainer::-webkit-scrollbar-thumb {
            background-color: #1f9158;
            border-radius: 10px;
        }

        #announcementsContainer::-webkit-scrollbar-track {
            background-color: #f0f0f0;
        }

        .recent-concerns-panel {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }

        .recent-concerns-panel h4 {
            margin-bottom: 20px;
            font-weight: 700;
            color: #374151;
            font-size: 20px;
        }

        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .table thead {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 600;
        }

        /* Make all table data cells normal font weight */
        .table tbody td {
            font-weight: normal !important;
        }

        .table tbody tr:hover {
            background-color: #fefefe;
        }

        @media (max-width: 768px) {
            .top-dashboard-grid {
                grid-template-columns: 1fr;
            }

            .status-cards-wrapper {
                grid-template-columns: 1fr;
            }

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

    <div class="container">
        <div class="top-dashboard-grid">
            <!-- Status Cards -->
            <div class="status-cards-wrapper">
                <div class="dashboard-card card-total">
                    <div class="card-icon"><i class="fas fa-boxes"></i></div>
                    <h1 class="card-value"><?php echo $total; ?></h1>
                    <p class="card-label">Active Concerns</p>
                </div>

                <div class="dashboard-card card-pending">
                    <div class="card-icon"><i class="fas fa-clock"></i></div>
                    <h1 class="card-value"><?php echo $pending; ?></h1>
                    <p class="card-label">Pending</p>
                </div>

                <div class="dashboard-card card-inprogress">
                    <div class="card-icon"><i class="fas fa-tasks"></i></div>
                    <h1 class="card-value"><?php echo $inProgress; ?></h1>
                    <p class="card-label">In Progress</p>
                </div>
            </div>

            <!-- Announcements -->
            <div class="announcements-panel">
                <div class="announcements-header">
                    <h3>Announcements</h3>
                    <button 
                        class="add-btn" 
                        title="Add Announcement" 
                        onclick="window.location.href='adminannouncement.php'">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>

                <div id="announcementsContainer">
                    <?php
                    if ($announcementsResult && mysqli_num_rows($announcementsResult) > 0) {
                        while ($a = mysqli_fetch_assoc($announcementsResult)) {
                            echo '<div class="announcement-item">';
                            echo '<div class="fw-bold" style="color:#275850;">' . htmlspecialchars($a['Title']) . '</div>';
                            echo '<div class="text-muted small mb-1" style="font-size:11px;">' .
                                 (isset($a['Created_At']) ? date("F d, Y", strtotime($a['Created_At'])) : '') .
                                 '</div>';
                            echo '<div style="color:#374151;">' . nl2br(htmlspecialchars($a['Content'])) . '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="announcement-item text-muted">No announcements yet.</div>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Recent Concerns -->
        <div class="recent-concerns-panel">
            <h4>Recent Active Concerns</h4>

            <div class="table-responsive mt-2">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Room</th>
                            <th>Service Type</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Reported By</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if ($recentResult && mysqli_num_rows($recentResult) > 0) {
                            while ($row = mysqli_fetch_assoc($recentResult)) {
                                echo "<tr>
                                    <td>{$row['ConcernID']}</td>
                                    <td>" . htmlspecialchars($row['Concern_Title']) . "</td>
                                    <td>" . htmlspecialchars($row['Room']) . "</td>
                                    <td>" . htmlspecialchars($row['Service_type']) . "</td>
                                    <td>" . htmlspecialchars(date('M d, Y', strtotime($row['Concern_Date']))) . "</td>
                                    <td>" . htmlspecialchars($row['Status']) . "</td>
                                    <td>" . htmlspecialchars($row['ReportedBy']) . "</td>
                                </tr>";
                            }
                        } else {
                            echo '<tr><td colspan="9" class="text-center text-muted">No recent concerns found.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="text-center mt-4">
                <a 
                    href="adminconcerns.php" 
                    class="btn btn-lg" 
                    style="
                        background-color: #275850;
                        color: white;
                        font-weight: 600;
                        border: none;
                        border-radius: 8px;
                        padding: 10px 25px;
                        transition: 0.3s ease-in-out;
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    ">
                    View All Concerns
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
</body>
</html>