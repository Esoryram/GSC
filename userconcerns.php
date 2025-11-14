<?php
// Start session and include database configuration
session_start();
include("config.php");

// Redirect to login page if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit();
}

// Set username and display name
$username = $_SESSION['username'];
$name = $_SESSION['name'] ?? $username;

// Set the active page for navbar highlighting
$activePage = "concerns";

// Fetch the AccountID of the logged-in user
$stmtUser = $conn->prepare("SELECT AccountID FROM Accounts WHERE Username = ?");
$stmtUser->bind_param("s", $username);
$stmtUser->execute();
$userRow = $stmtUser->get_result()->fetch_assoc();
$accountID = $userRow['AccountID'] ?? 0;
$stmtUser->close();

// Check if a specific concern should be opened from dashboard
$openConcernId = isset($_GET['open_concern']) ? intval($_GET['open_concern']) : null;

// Verify the concern belongs to the current user
if ($openConcernId) {
    $stmt = $conn->prepare("SELECT ConcernID FROM Concerns WHERE ConcernID = ? AND AccountID = ?");
    $stmt->bind_param("ii", $openConcernId, $accountID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Concern doesn't belong to user or doesn't exist
        $openConcernId = null;
    }
    $stmt->close();
}

// FIXED: Fetch concerns for the logged-in user with correct field names
$stmt = $conn->prepare(
    "SELECT c.ConcernID, c.Concern_Title, c.Description, c.building_name, c.Room, 
            c.Service_type, c.EFname, c.Attachment, c.Concern_Date, 
            c.Assigned_to, c.Status 
     FROM Concerns c 
     WHERE c.AccountID = ? 
     AND c.Status NOT IN ('Completed', 'Cancelled') 
     ORDER BY c.Concern_Date DESC"
);
$stmt->bind_param("i", $accountID);
$stmt->execute();
$concernsResult = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- FIXED: Better viewport for iOS devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, shrink-to-fit=no">
    <title>Concerns</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
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
            background: linear-gradient(135deg, #087830, #3c4142);
            min-width: 180px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            overflow: hidden;
            z-index: 1000;
            border: none;
        }

        .dropdown:hover .dropdown-menu,
        .dropdown:focus-within .dropdown-menu {
            display: block;
        }

        .dropdown-menu a {
            display: block;
            padding: 12px 16px;
            color: white; 
            text-decoration: none;
            font-size: 14px;
            min-height: 44px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s;
        }

        .dropdown-menu a:hover {
            background: #107040; 
            color: white; 
        }

        .concern-container {
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            max-height: 70vh;
            margin-top: 25px;
            overflow-y: auto;
            box-sizing: border-box;
        }

        .concern-header {
            background: linear-gradient(135deg, #087830, #3c4142);
            color: white;
            font-weight: bold;
            padding: 12px;
            border-radius: 10px;
            font-size: 16px;
            margin-bottom: 15px;
            text-align: center;
        }

        .accordion-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .accordion-button {
            background: linear-gradient(135deg, #087830, #3c4142);
            color: white;
            font-weight: bold;
            border: none;
            padding: 15px;
            font-size: 14px;
            min-height: 60px;
            display: flex;
            align-items: center;
        }

        .accordion-button:not(.collapsed) {
            background: linear-gradient(135deg, #087830, #3c4142);
        }

        .accordion-body {
            background: #f8f9fa;
            padding: 15px;
        }

        /* Status Badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
            display: inline-block;
        }

        .status-pending {
            background: #fef3c7;
            color: #b45309;
        }

        .status-in-progress {
            background: #bfdbfe;
            color: #1e40af;
        }

        .form-field {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-field label {
            font-weight: bold;
            color: #163a37;
            margin-bottom: 8px;
            display: block;
            font-size: 14px;
        }

        .form-field .form-control {
            background: #fff;
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 12px;
            font-size: 16px; 
            color: #495057;
            width: 100%;
            box-sizing: border-box;
            min-height: 44px; 
        }

        .no-attachment {
            color: #6c757d;
            font-style: italic;
        }

        .accordion-item.highlighted {
            background-color: #e8f5e8;
            border-left: 4px solid #1f9158;
            transition: all 0.3s ease;
        }

        .accordion-item.highlighted .accordion-button {
            background: linear-gradient(90deg, #1f9158, #163a37);
        }

        .accordion-collapse {
            transition: all 0.3s ease;
        }

        .accordion-item:target {
            background-color: #e8f5e8;
            border-left: 4px solid #1f9158;
            animation: highlight 2s ease;
        }

        @keyframes highlight {
            0% { 
                background-color: #e8f5e8;
                box-shadow: 0 0 10px rgba(31, 145, 88, 0.3);
            }
            100% { 
                background-color: #f8f9fa;
                box-shadow: none;
            }
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
            
            .main {
                padding: 8px;
            }
            
            .concern-container {
                padding: 12px;
                max-height: 65vh;
            }
            
            .concern-header {
                font-size: 15px;
                padding: 10px;
            }
            
            .accordion-button {
                padding: 12px;
                font-size: 13px;
                min-height: 55px;
            }
            
            .accordion-body {
                padding: 12px;
            }
            
            .accordion-body .row {
                margin-bottom: 12px;
                flex-direction: column;
            }

            .accordion-body .col-md-6 {
                width: 100%;
                margin-bottom: 12px;
            }
    
            .accordion-body .col-md-6:last-child {
                margin-bottom: 0;
            }
            
            .form-field {
                margin-bottom: 12px;
            }
            
            .form-field .form-control {
                font-size: 16px; 
            }
            
            .status-badge {
                font-size: 11px;
                padding: 5px 10px;
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
            
            .concern-container {
                padding: 10px;
            }
            
            .accordion-button {
                padding: 10px;
                font-size: 12px;
            }
            
            .accordion-body {
                padding: 10px;
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
            
            .concern-container {
                padding: 15px;
                max-height: 60vh;
            }
            
            .accordion-body .row {
                margin-bottom: 15px;
            }
        }

        .concern-container::-webkit-scrollbar {
            width: 6px;
        }

        .concern-container::-webkit-scrollbar-thumb {
            background-color: #1f9158;
            border-radius: 10px;
        }

        .concern-container::-webkit-scrollbar-track {
            background-color: #f0f0f0;
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

    <div class="concern-container">
        <div class="concern-header">Your Submitted Concerns</div>
        <div class="accordion" id="concernsAccordion">
            <?php if ($concernsResult && $concernsResult->num_rows > 0): ?>
                <?php $index = 1; ?>
                <?php while ($row = $concernsResult->fetch_assoc()): ?>
                    <?php
                    $status = $row['Status'] ?? 'Unknown';
                    $statusClass = match($status) {
                        'In Progress' => 'status-in-progress',
                        'Pending' => 'status-pending',
                        default => 'bg-light text-dark'
                    };
                    $date = date("l, d M Y", strtotime($row['Concern_Date']));
                    ?>
                    <!-- Add ID to each accordion item for anchor targeting -->
                    <div class="accordion-item" id="concern-<?= $row['ConcernID'] ?>">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#concern<?= $index ?>" aria-expanded="false" 
                                    aria-controls="concern<?= $index ?>">
                                <span class="d-flex justify-content-between w-100 align-items-center flex-wrap">
                                    <span class="me-2" style="font-size: 13px;"><?= $date ?></span>
                                    <span class="me-2" style="font-size: 20px;"><?= htmlspecialchars($row['Concern_Title']) ?></span>
                                    <span class="badge <?= $statusClass ?> status-badge"><?= htmlspecialchars($status) ?></span>
                                </span>
                            </button>
                        </h2>
                        <div id="concern<?= $index ?>" class="accordion-collapse collapse" data-bs-parent="#concernsAccordion">
                            <div class="accordion-body">
                                <!-- Concern Title -->
                                <div class="form-field">
                                    <label>Concern Title</label>
                                    <div class="form-control"><?= htmlspecialchars($row['Concern_Title']) ?></div>
                                </div>
                                
                                <!-- Description -->
                                <div class="form-field">
                                    <label>Description</label>
                                    <div class="form-control"><?= htmlspecialchars($row['Description']) ?></div>
                                </div>

                                <!-- Room and Building in same row -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-field">
                                            <label>Room</label>
                                            <div class="form-control"><?= htmlspecialchars($row['Room']) ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-field">
                                            <label>Building</label>
                                            <div class="form-control">
                                                <!-- FIXED: Use building_name from database -->
                                                <?= !empty($row['building_name']) ? htmlspecialchars($row['building_name']) : 'Not specified' ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Service Type and Equipment/Facility in same row -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-field">
                                            <label>Service Type</label>
                                            <div class="form-control"><?= htmlspecialchars($row['Service_type']) ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-field">
                                            <label>Equipment / Facility</label>
                                            <div class="form-control">
                                                <?= !empty($row['EFname']) ? htmlspecialchars($row['EFname']) : 'Not specified' ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Assigned To -->
                                <div class="form-field">
                                    <label>Assigned To</label>
                                    <div class="form-control"><?= !empty($row['Assigned_to']) ? htmlspecialchars($row['Assigned_to']) : 'Not assigned yet' ?></div>
                                </div>
                                
                                <!-- Attachment - Always show this field -->
                                <div class="form-field">
                                    <label>Attachment</label>
                                    <div class="form-control">
                                        <?php if (!empty($row['Attachment'])): ?>
                                            <?php 
                                            $attachment = htmlspecialchars($row['Attachment']);
                                            if (preg_match('/\.(jpg|jpeg|png|gif|bmp)$/i', $attachment)): 
                                            ?>
                                                <a href="<?= $attachment ?>" target="_blank" class="text-decoration-none">
                                                    <i class="fas fa-image me-1"></i>View Image
                                                </a>
                                            <?php else: ?>
                                                <?= $attachment ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="no-attachment">No attachment</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $index++; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info text-center">You have not submitted any concerns yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
    // FIXED: Better mobile menu toggle with touch support
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

        // FIXED: Prevent body scroll when menu is open on mobile
        navbarToggle.addEventListener('click', function() {
            if (navbarLinks.classList.contains('show')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });

        // Handle both anchor navigation and open_concern parameter
        function autoOpenConcern() {
            const urlParams = new URLSearchParams(window.location.search);
            const openConcernId = urlParams.get('open_concern');
            
            if (openConcernId) {
                const targetElement = document.getElementById('concern-' + openConcernId);
                
                if (targetElement) {
                    // Scroll to the element
                    setTimeout(() => {
                        targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        
                        // Add highlight effect
                        targetElement.classList.add('highlighted');
                        
                        // Remove highlight after 3 seconds
                        setTimeout(() => {
                            targetElement.classList.remove('highlighted');
                        }, 3000);
                    }, 300);
                    
                    // Auto-expand the accordion
                    const accordionButton = targetElement.querySelector('.accordion-button');
                    const accordionCollapse = targetElement.querySelector('.accordion-collapse');
                    
                    if (accordionButton && accordionCollapse) {
                        // Close all other accordions first
                        const allAccordions = document.querySelectorAll('.accordion-collapse');
                        allAccordions.forEach(acc => {
                            if (acc !== accordionCollapse) {
                                acc.classList.remove('show');
                                const btn = acc.previousElementSibling?.querySelector('.accordion-button');
                                if (btn) {
                                    btn.classList.add('collapsed');
                                    btn.setAttribute('aria-expanded', 'false');
                                }
                            }
                        });
                        
                        // Open the target accordion
                        setTimeout(() => {
                            accordionCollapse.classList.add('show');
                            accordionButton.classList.remove('collapsed');
                            accordionButton.setAttribute('aria-expanded', 'true');
                        }, 500);
                    }
                    
                    // Clean URL without removing history (remove open_concern parameter)
                    const cleanUrl = window.location.pathname + window.location.search.replace(/[?&]open_concern=[^&]*/, '').replace(/^&/, '?').replace(/[?&]$/, '');
                    const newUrl = cleanUrl === window.location.pathname ? cleanUrl : cleanUrl;
                    window.history.replaceState({}, document.title, newUrl);
                }
            }
            
            // Also handle regular anchor navigation
            if (window.location.hash) {
                const targetId = window.location.hash.substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    setTimeout(() => {
                        targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 300);
                    
                    const accordionButton = targetElement.querySelector('.accordion-button');
                    const accordionCollapse = targetElement.querySelector('.accordion-collapse');
                    
                    if (accordionButton && accordionCollapse) {
                        const allAccordions = document.querySelectorAll('.accordion-collapse');
                        allAccordions.forEach(acc => {
                            if (acc !== accordionCollapse) {
                                acc.classList.remove('show');
                            }
                        });
                        
                        setTimeout(() => {
                            accordionCollapse.classList.add('show');
                            accordionButton.classList.remove('collapsed');
                            accordionButton.setAttribute('aria-expanded', 'true');
                        }, 400);
                    }
                }
            }
        }

        // Run auto-open function
        autoOpenConcern();
    });

    // Handle back/forward browser navigation
    window.addEventListener('hashchange', function() {
        if (window.location.hash) {
            const targetId = window.location.hash.substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                setTimeout(() => {
                    targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
            }
        }
    });
</script>

<!-- Include Change Password Modal -->
<?php include('change_password_modal.php'); ?>
</body>
</html>