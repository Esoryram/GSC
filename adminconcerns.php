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

$query = "
    SELECT 
        c.ConcernID,
        c.Concern_Title,
        c.Room,
        c.Service_type,
        c.Concern_Date,
        c.Status,
        a.Name AS ReportedBy,
        c.Assigned_to
    FROM Concerns c
    LEFT JOIN Accounts a ON c.AccountID = a.AccountID
    ORDER BY c.ConcernID ASC
";
$result = mysqli_query($conn, $query);
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

        .table th:nth-last-child(1),
        .table td:nth-last-child(1) {
            width: 250px;
            text-align: center;
        }

        .table-container {
            margin: 0 40px 40px 40px;
        }

        .assign-btn {
            font-size: 13px;
            padding: 6px 16px;
            border-radius: 6px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            width: 150px;
            transition: 0.3s;
            text-align: center;
        }

        .assign-btn.unassigned {
            background-color: #ff0000;
            color: white;
        }

        .assign-btn.assigned {
            background-color: #198754;
            color: #212529;
        }

        .assign-btn:hover {
            opacity: 0.9;
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
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    mysqli_data_seek($result, 0);
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
                        if (empty($assignedName)) {
                            $buttonText = "Assign";
                            $buttonClass = "assign-btn unassigned";
                        } else {
                            $buttonText = htmlspecialchars($assignedName);
                            $buttonClass = "assign-btn assigned";
                        }
                    ?>
                    <tr>
                        <td><?php echo $row['ConcernID']; ?></td>
                        <td><?php echo htmlspecialchars($row['Concern_Title']); ?></td>
                        <td><?php echo htmlspecialchars($row['Room']); ?></td>
                        <td><?php echo htmlspecialchars($row['Service_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['Concern_Date']); ?></td>
                        <td>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($row['Status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['ReportedBy']); ?></td>
                        <td>
                            <button 
                                class="<?php echo $buttonClass; ?>" 
                                onclick="window.location.href='view_concern.php?id=<?php echo $row['ConcernID']; ?>'">
                                <?php echo $buttonText; ?>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
</body>
</html>