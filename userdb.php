<?php
// Start session and include database configuration
session_start();
include("config.php");

// Redirect user to login page if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit();
}

// Get the logged-in username and display name
$username = $_SESSION['username'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;
$activePage = "dashboard";

// Fetch AccountID from session or database
$accountID = $_SESSION['account_id'] ?? null;

if (!$accountID) {
    // Query database to get AccountID for the current user
    $stmt = $conn->prepare("SELECT AccountID FROM accounts WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $accountID = $row['AccountID'];
        $_SESSION['account_id'] = $accountID;
    } else {
        // Handle error - user not found
        header("Location: user_login.php");
        exit();
    }
    $stmt->close(); 
}

// Initialize counters for concern statuses
$total = 0;
$pending = 0;
$inProgress = 0;
$completed = 0; 

if ($accountID) {
    // Define queries for total and status-specific concerns
    $queries = [
        "total"      => "SELECT COUNT(*) AS total FROM concerns WHERE AccountID = ?",
        "pending"    => "SELECT COUNT(*) AS pending FROM concerns WHERE AccountID = ? AND Status = 'Pending'",
        "inProgress" => "SELECT COUNT(*) AS inProgress FROM concerns WHERE AccountID = ? AND Status = 'In Progress'",
        "completed"   => "SELECT COUNT(*) AS completed FROM concerns WHERE AccountID = ? AND Status = 'Completed'"
    ];

    // Execute each query and store results in corresponding variable
    foreach ($queries as $key => $sql) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $accountID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        ${$key} = $row[$key] ?? 0;
        $stmt->close();
    }

    // Fetch recent concerns (ALL statuses including Completed) for display
    $recentConcerns = [];
    
    // FIXED: Only select the fields we need to display
    $stmt = $conn->prepare("
    SELECT ConcernID, Concern_Title, Room, Service_type, Status, Concern_Date
    FROM concerns
    WHERE AccountID = ? 
    ORDER BY Concern_Date DESC
    LIMIT 5");
    
    $stmt->bind_param("i", $accountID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $recentConcerns[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, shrink-to-fit=no">
    <title>My Dashboard | Concern Tracker</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS for popups -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- FontAwesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        * {
            -webkit-tap-highlight-color: transparent;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        input, textarea, .form-control {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
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

        .logo img {
            height: 35px;
            width: auto;
            object-fit: contain;
            margin-right: 10px;
        }

        .navbar .links {
            display: flex;
            gap: 10px;
            margin-right: auto;
        }

        .navbar .links a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            padding: 6px 10px;
            border-radius: 5px;
            transition: 0.3s;
        }

        .navbar .links a.active {
            background: #4ba06f;
            border: 1px solid #07491f;
            box-shadow: 0 4px 6px rgba(0,0,0,0.4);
        }

        .navbar .links a:hover {
            background: #107040;
        }

        .navbar-toggler {
            display: none;
            background: transparent;
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 16px;
            margin-left: 10px;
            cursor: pointer;
        }

        .dropdown {
            position: relative;
        }

        .dropdown-toggle {
            cursor: pointer;
            font-size: 14px;
            padding: 8px 12px;
            border-radius: 5px;
            color: white;
            background: transparent;
            border: none;
            min-height: 44px;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            min-width: 180px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            overflow: hidden;
            z-index: 1000;
        }

        .dropdown:hover .dropdown-menu,
        .dropdown:focus-within .dropdown-menu {
            display: block;
        }

        .dropdown-menu a {
            display: block;
            padding: 12px 16px;
            color: #333;
            text-decoration: none;
            font-size: 14px;
            min-height: 44px;
            display: flex;
            align-items: center;
        }

        .dropdown-menu a:hover {
            background: #f1f1f1;
        }

        .container {
            padding: 20px 15px;
        }

        .top-dashboard-grid {
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 20px; 
            margin-bottom: 25px;
        }

        .status-cards-wrapper {
            display: grid;
            grid-template-columns: repeat(4, 1fr); 
            gap: 15px;
        }

        .dashboard-card {
            background: white;
            border-radius: 12px;
            padding: 20px; 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); 
            transition: transform 0.2s, box-shadow 0.2s;
            text-align: left;
            min-height: 110px;
            border: 1px solid #e5e7eb; 
        }
        .dashboard-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }
        .card-icon { 
            font-size: 22px; 
            opacity: 0.7; 
            margin-bottom: 8px; 
        }

        .card-value { 
            font-size: 36px; 
            font-weight: 700; 
            margin: 0; 
            line-height: 1; 
        }

        .card-label { 
            font-size: 14px; 
            font-weight: 500; 
            color: #6b7280; 
            margin-top: 5px; 
            text-transform: capitalize; 
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
            background-color: #e0f2fe; 
            color: #075985; 
        }

        .card-inprogress .card-icon { 
            color: #38bdf8; 
        }

        .card-completed { 
            background-color: #ecfdf5; 
            color: #087830; 
        }

        .card-completed .card-icon { 
            color: #087830; 
        }

        .cta-section {
            text-align: center;
            margin: 20px 0;
        }

        #submitConcernBtn {
            padding: 12px 25px; 
            font-size: 16px; 
            font-weight: 700;
            border-radius: 10px;
            background: #1f9158; 
            color: white;
            border: none;
            text-decoration: none; 
            display: inline-block; 
            transition: background 0.3s, transform 0.1s;
            box-shadow: 0 4px 10px rgba(31, 145, 88, 0.4);
            width: 300px;
        }

        #submitConcernBtn:hover {
            background: #107040;
            transform: translateY(-2px);
            color: white;
        }

        .recent-concerns-panel {
            background: white;
            border-radius: 12px; 
            padding: 20px; 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); 
            margin-top: 20px;
            overflow-x: auto;
        }

        .table {
            min-width: 600px;
        }

        .table th, .table td { 
            vertical-align: middle; 
            font-size: 14px; 
            padding: 10px 8px;
        }

        .status-pill {
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 12px;
            min-width: 80px;
            display: inline-block;
            text-align: center;
        }

        .status-pending { 
            background: #fef3c7; 
            color: #b45309; 
        }

        .status-in-progress { 
            background: #bfdbfe; 
            color: #1e40af; 
        }

        .status-completed { 
            background: #d1fae5; 
            color: #065f46; 
        }

        .announcements-panel {
            background: white;
            border-radius: 12px;
            padding: 15px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .announcements-panel h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1f9158;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }

        .announcement-item {
            background: #f9fafb;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 10px;
            font-size: 12px;
            border-left: 3px solid #1f9158;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            cursor: pointer;
            transition: background 0.2s;
        }

        .announcement-item:hover {
            background: #f0f4f8;
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

        @media (max-width: 480px) {
            .navbar {
                padding: 10px 12px;
                flex-wrap: wrap;
            }
            
            .navbar-toggler {
                display: block;
                order: 2;
            }
            
            .navbar .links {
                display: none;
                width: 100%;
                flex-direction: column;
                gap: 8px;
                margin-top: 10px;
                order: 3;
            }
            
            .navbar .links.show {
                display: flex;
            }
            
            .navbar .links a {
                padding: 12px 15px;
                text-align: center;
                font-size: 15px;
                min-height: 44px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .logo {
                order: 1;
                margin-right: auto;
            }
            
            .dropdown {
                order: 2;
                margin-left: auto;
            }
            
            .container {
                padding: 10px;
            }
            
            .top-dashboard-grid { 
                grid-template-columns: 1fr; 
            } 
            
            .status-cards-wrapper { 
                grid-template-columns: repeat(2, 1fr); 
            } 
            
            .dashboard-card {
                padding: 15px;
                min-height: 100px;
            }
            
            .card-value {
                font-size: 32px;
            }
            
            .recent-concerns-panel {
                padding: 15px;
            }
            
            .table th, .table td {
                font-size: 12px;
                padding: 8px 6px;
            }
            
            .btn-sm {
                padding: 5px 8px;
                font-size: 12px;
            }
            
            .announcements-panel {
                padding: 12px;
            }
            
            .announcement-item {
                padding: 8px 10px;
                font-size: 12px;
            }
        }

        @media (max-width: 390px) {
            .navbar .links a {
                font-size: 14px;
                padding: 10px 12px;
            }
            
            .dropdown-toggle {
                font-size: 13px;
                padding: 6px 10px;
            }
            
            .status-cards-wrapper { 
                grid-template-columns: 1fr; 
            } 
            
            .card-value {
                font-size: 28px;
            }
        }

        @media (min-width: 481px) and (max-width: 768px) {
            .navbar {
                padding: 12px 15px;
            }
            
            .navbar .links a {
                font-size: 14px;
                padding: 8px 12px;
            }
            
            .top-dashboard-grid { 
                grid-template-columns: 1fr; 
            } 
            
            .status-cards-wrapper { 
                grid-template-columns: repeat(2, 1fr); 
            } 
        }

        @media (max-width: 576px) { 
            .status-cards-wrapper { 
                grid-template-columns: 1fr; 
            } 
            
            .card-value {
                font-size: 28px;
            }
            
            .container {
                padding: 10px;
            }
            
            .announcements-panel {
                padding: 12px;
            }
            
            .announcement-item {
                padding: 8px 10px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="logo">
        <img src="img/LSULogo.png" alt="LSU Logo">
    </div>

    <div class="links" id="navbarLinks">
        <a href="userdb.php" class="<?= $activePage == 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-home me-1"></i> Dashboard
        </a>
        <a href="usersubmit.php" class="<?= $activePage == 'newconcerns' ? 'active' : '' ?>">
            <i class="fas fa-plus-circle me-1"></i> Submit New Concern
        </a>
        <a href="userconcerns.php" class="<?= $activePage == 'concerns' ? 'active' : '' ?>">
            <i class="fas fa-list-ul me-1"></i> All Concerns
        </a>
    </div>

    <button class="navbar-toggler" type="button" id="navbarToggle" aria-label="Toggle navigation">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dropdown ms-auto">
        <button class="btn dropdown-toggle username-btn" aria-expanded="false" aria-haspopup="true">
                <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($name) ?>
            </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                <i class="fas fa-key me-2"></i>Change Password
            </a></li>
            <li><a class="dropdown-item" href="user_archived.php">
                <i class="fas fa-archive me-2"></i>Archived Concerns
            </a></li>
            <li><a class="dropdown-item" href="index.php">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a></li>
        </ul>
    </div>
</div>

<div class="container">
    <div class="top-dashboard-grid">
        <div class="status-cards-wrapper">
            <div class="dashboard-card card-total">
                <div class="card-icon"><i class="fas fa-boxes"></i></div>
                <h1 class="card-value"><?= $total ?></h1>
                <p class="card-label">Total Concerns</p>
            </div>

            <div class="dashboard-card card-pending">
                <div class="card-icon"><i class="fas fa-clock"></i></div>
                <h1 class="card-value"><?= $pending ?></h1>
                <p class="card-label">Pending</p>
            </div>

            <div class="dashboard-card card-inprogress">
                <div class="card-icon"><i class="fas fa-tasks"></i></div>
                <h1 class="card-value"><?= $inProgress ?></h1>
                <p class="card-label">In Progress</p>
            </div>
            
            <div class="dashboard-card card-completed">
                <div class="card-icon"><i class="fas fa-check-circle"></i></div>
                <h1 class="card-value"><?= $completed ?></h1>
                <p class="card-label">Completed</p>
            </div>
        </div>

        <div class="announcements-panel">
            <h3>Announcements</h3>
            <div id="announcementsContainer">
                <div class="announcement-item">Loading announcements...</div>
            </div>
        </div>
    </div>

    <div class="cta-section">
        <a href="usersubmit.php" id="submitConcernBtn"><i class="fas fa-plus me-2"></i> Report a New Concern</a>
    </div>

    <div class="recent-concerns-panel">
        <h3>My Recent Concerns</h3>

        <?php if (!empty($recentConcerns)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Title</th>
                            <th scope="col">Room/Area</th>
                            <th scope="col">Service Type</th>
                            <th scope="col">Date Submitted</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentConcerns as $concern): ?>
                            <tr>
                                <td><?= htmlspecialchars($concern['ConcernID']) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($concern['Concern_Title']) ?></td>
                                <td><?= htmlspecialchars($concern['Room']) ?></td>
                                <td><?= htmlspecialchars($concern['Service_type']) ?></td>
                                <td><?= date('M d, Y', strtotime($concern['Concern_Date'])) ?></td>
                                <td>
                                    <?php
                                    $status = htmlspecialchars($concern['Status']);
                                    $statusClass = strtolower(str_replace(' ', '-', $status));
                                    echo '<span class="status-pill status-' . $statusClass . '">' . $status . '</span>';
                                    ?>
                                </td>
                                <td>
                                    <a href="userconcerns.php?open_concern=<?= $concern['ConcernID'] ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-3">
                <a href="userconcerns.php" class="btn btn-sm btn-outline-secondary">View All Concerns <i class="fas fa-arrow-right ms-2"></i></a>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                You haven't submitted any concerns yet. Click the button above to report an issue!
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
    // Mobile menu functionality
    document.addEventListener('DOMContentLoaded', function() {
        const navbarToggle = document.getElementById('navbarToggle');
        const navbarLinks = document.getElementById('navbarLinks');
        
        if (navbarToggle) {
            navbarToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                navbarLinks.classList.toggle('show');
            });
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const navbar = document.querySelector('.navbar');
            if (!navbar.contains(event.target) && navbarLinks.classList.contains('show')) {
                navbarLinks.classList.remove('show');
            }
        });

        // Prevent body scroll when menu is open on mobile
        navbarToggle.addEventListener('click', function() {
            if (navbarLinks.classList.contains('show')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });

        // Load announcements
        loadAnnouncements();
        // Refresh announcements every 30 seconds
        setInterval(loadAnnouncements, 30000);
    });

    function loadAnnouncements() {
        fetch('get_announcement.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(announcements => {
                const container = document.getElementById('announcementsContainer');
                container.innerHTML = '';

                if (!announcements || !announcements.length) {
                    container.innerHTML = '<div class="announcement-item text-muted">No active announcements.</div>';
                    return;
                }

                announcements.forEach(a => {
                    const btn = document.createElement('button');
                    btn.className = 'announcement-item w-100 text-start border-0 bg-transparent';
                    btn.innerHTML = `
                        <div class="fw-bold" style="color:#275850;">${a.title}</div>
                        <div class="text-muted small mb-1" style="font-size:12px;">${a.date}</div>
                    `;
                    btn.addEventListener('click', () => showAnnouncementModal(a));
                    container.appendChild(btn);
                });
            })
            .catch((error) => {
                console.error('Error loading announcements:', error);
                document.getElementById('announcementsContainer').innerHTML =
                    '<div class="announcement-item text-danger">Error loading announcements.</div>';
            });
    }

    function showAnnouncementModal(a) {
        const modalTitle = document.getElementById('announcementModalLabel');
        const modalBody = document.getElementById('announcementModalBody');

        modalTitle.textContent = a.title;
        modalBody.innerHTML = `
            <p class="text-muted" style="font-size:12px;">Posted on ${a.date}</p>
            <div style="white-space:pre-line;">${a.details || a.content || 'No details available.'}</div>
        `;

        const modal = new bootstrap.Modal(document.getElementById('announcementModal'));
        modal.show();
    }
</script>

<!-- Announcement Modal -->
<div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#1f9158; color:white;">
        <h5 class="modal-title" id="announcementModalLabel">Announcement</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="announcementModalBody" style="font-size:14px;"></div>
    </div>
  </div>
</div>

<!-- Include Change Password Modal -->
<?php include('change_password_modal.php'); ?>
</body>
</html>