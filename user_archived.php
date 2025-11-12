<?php
// Start session and include database configuration
session_start();
include("config.php");

// Redirect to login if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get logged-in user's username and display name
$username = $_SESSION['username'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;

// Determine the return page for the "Return" button
if (isset($_GET['return'])) {
    $returnPage = $_GET['return'];
} else {
    $referrer = isset($_SERVER['HTTP_REFERER']) ? basename($_SERVER['HTTP_REFERER']) : '';
    $returnPage = ($referrer === 'userconcerns.php') ? 'userconcerns.php' : 'userdb.php';
}

// Fetch AccountID of the logged-in user
$userQuery = "SELECT AccountID FROM Accounts WHERE Username = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$userResult = $stmt->get_result();
$userRow = $userResult->fetch_assoc();
$accountID = $userRow ? $userRow['AccountID'] : 0;
$stmt->close();

// Fetch only completed or cancelled concerns of the logged-in user
$concernsQuery = "SELECT * FROM Concerns WHERE AccountID = ? AND (Status = 'Completed' OR Status = 'Cancelled') ORDER BY Concern_Date DESC";
$stmt2 = $conn->prepare($concernsQuery);
$stmt2->bind_param("i", $accountID);
$stmt2->execute();
$concernsResult = $stmt2->get_result();
$stmt2->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Archived Concerns</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
/* General Styles */
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    background: #f4f4f4;
}

/* Navbar */
.navbar {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #087830, #3c4142);
    padding: 15px 15px;
    color: white;
    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    position: relative;
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

.navbar h2 {
    margin-left: 50px;
    font-size: 24px;
    margin-top: 2px;
}

.return-btn {
    background: #107040;
    color: white;
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    text-decoration: none;
    display: inline-block;
    transition: background 0.3s;
    font-size: 14px;
    margin-left: auto;
}
.return-btn:hover {
    background: #07532e;
}

/* Main Layout */
.main {
    padding: 10px;
    text-align: center;
}

/* Submit Button Outside Box */
.submit-btn-top {
    background: linear-gradient(90deg, #163a37, #1f9158);
    color: white;
    font-weight: bold;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    margin-bottom: 0;
    transition: all 0.3s ease;
    font-size: 14px;
    white-space: nowrap;
}
.submit-btn-top:hover {
    background: linear-gradient(90deg, #1f9158, #163a37);
    transform: translateY(-1px);
}

/* Concern Box */
.concern-container {
    background: white;
    padding: 15px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    max-width: 850px;
    margin: 0 auto;
    max-height: 550px;
    overflow-y: auto;
}

.concern-header {
    background: linear-gradient(90deg, #163a37, #1f9158);
    color: white;
    font-weight: bold;
    padding: 8px;
    border-radius: 10px;
    font-size: 18px;
    margin-bottom: 20px;
    text-align: center;
}

/* Accordion */
.accordion-item {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 10px;
    overflow: hidden;
}

.accordion-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.accordion-button {
    background: linear-gradient(90deg, #163a37, #1f9158);
    color: white;
    font-weight: bold;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 10px 15px;
    flex: 1;
    min-width: 200px;
    text-align: left;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.accordion-button:not(.collapsed) {
    background: linear-gradient(90deg, #1f9158, #163a37);
}

.accordion-body {
    background: #f8f9fa;
    padding: 15px;
}

/* Status Badges */
.status-completed {
    background-color: #d1edff; 
    color: #087830; 
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    margin-left: 10px;
}

.status-cancelled {
    background-color: #f8d7da;
    color: #721c24;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    margin-left: 10px;
}

/* Form Fields */
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
    background-color: #ffffff;
    border: 1px solid #ced4da;
    border-radius: 6px;
    padding: 10px 15px;
    font-size: 14px;
    color: #495057;
    width: 100%;
    box-sizing: border-box;
}

/* Row layout for form fields */
.form-row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -10px;
}

.form-col {
    flex: 1;
    min-width: 200px;
    padding: 0 10px;
    margin-bottom: 15px;
}

/* Submit Feedback Button */
.feedback-btn {
    background: #28a745;
    color: white;
    font-weight: bold;
    border: none;
    padding: 8px 15px;
    border-radius: 8px;
    margin-left: 10px;
    font-size: 14px;
    min-width: 150px;
    height: auto;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    margin-top: 5px;
}

.feedback-btn:hover {
    background: #087830;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .navbar {
        padding: 10px 15px;
        flex-wrap: wrap;
    }

    .logo {
        margin-right: 10px;
    }
    
    .navbar h2 {
        font-size: 16px;
        margin-left: 20px;
        margin-top: 10px;
    }
    
    .return-btn {
        padding: 5px 10px;
        font-size: 13px;
    }
    
    .main {
        padding: 15px;
    }
    
    .concern-container {
        padding: 10px;
        max-height: 500px;
    }
    
    .accordion-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .accordion-button {
        min-width: auto;
        margin-bottom: 5px;
    }
    
    .feedback-btn {
        margin-left: 0;
        width: 100%;
        margin-top: 5px;
    }
    
    .form-col {
        flex: 100%;
        min-width: 100%;
    }
    
    .submit-btn-top {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .d-flex.justify-content-end {
        justify-content: center !important;
    }
}

@media (max-width: 576px) {
    .navbar {
        padding: 10px 12px;
    }
    
    .logo img {
        height: 35px;
    }
    
    .navbar h2 {
        font-size: 15px;
        margin-left: 10px;
    }
    
    .return-btn {
        padding: 4px 8px;
        font-size: 12px;
    }
    
    .main {
        padding: 10px;
    }
}

@media (max-width: 400px) {
    .navbar {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    
    .logo {
        justify-content: center;
        margin-right: 0;
    }
    
    .concern-header {
        font-size: 16px;
        padding: 6px;
    }
    
    .accordion-button {
        padding: 8px 12px;
        font-size: 14px;
    }
    
    .form-field .form-control {
        padding: 8px 12px;
        font-size: 13px;
    }
    
    .status-completed,
    .status-cancelled {
        font-size: 11px;
        padding: 3px 6px;
    }
}
</style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="logo">
        <img src="img/LSULogo.png" alt="LSU Logo">
        <h2>Archived Concerns</h2>
    </div>

    <a href="userconcerns.php" class="return-btn">
        <i class="fas fa-arrow-left me-1"></i> Return
    </a>
</div>

<!-- Main Content -->
<div class="main">

    <!-- Submit New Concern Button -->
    <div class="d-flex justify-content-end mb-3" style="max-width: 850px; margin: 0 auto;">
        <button class="submit-btn-top" onclick="window.location.href='usersubmit.php'">
            + Submit New Concern
        </button>
    </div>

    <div class="concern-container">
        <div class="concern-header">Concerns Details</div>

        <div class="accordion" id="concernsAccordion">
            <?php if ($concernsResult && $concernsResult->num_rows > 0): 
                $index = 1;
                while ($row = $concernsResult->fetch_assoc()):
                    $status = $row['Status'] ?? 'Unknown';
                    $statusClass = match($status) {
                        'Completed' => 'status-completed',
                        'Cancelled' => 'status-cancelled',
                        default => 'bg-light text-dark'
                    };
                    $concernID = $row['ConcernID']; // ✅ Define concern ID
                    $date = date("l, d M Y", strtotime($row['Concern_Date']));
            ?>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#concern<?= $index ?>" aria-expanded="false">
                            <span><?= $date ?></span>
                            <span class="badge <?= $statusClass; ?>">
                                <?= htmlspecialchars($status); ?>
                            </span>
                        </button>
                        <button class="feedback-btn" onclick="window.location.href='user_feedback.php?concern_id=<?= $concernID ?>'">
                            Submit Feedback
                        </button>
                    </h2>
                    <div id="concern<?= $index ?>" class="accordion-collapse collapse" data-bs-parent="#concernsAccordion">
                        <div class="accordion-body">
                            <div class="form-field">
                                <label>Concern Title</label>
                                <div class="form-control"><?= htmlspecialchars($row['Concern_Title']) ?></div>
                            </div>
                            <div class="form-field">
                                <label>Description</label>
                                <div class="form-control"><?= htmlspecialchars($row['Description']) ?></div>
                            </div>
                            
                            <!-- Room and Equipment/Facility in one row -->
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-field">
                                        <label>Room</label>
                                        <div class="form-control"><?= htmlspecialchars($row['Room'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-field">
                                        <label>Equipment/Facility</label>
                                        <div class="form-control"><?= htmlspecialchars($row['Equipment_Facility'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Problem Type and Priority in one row -->
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-field">
                                        <label>Problem Type</label>
                                        <div class="form-control"><?= htmlspecialchars($row['Problem_Type']) ?></div>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-field">
                                        <label>Priority</label>
                                        <div class="form-control"><?= htmlspecialchars($row['Priority']) ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-field">
                                <label>Assigned To</label>
                                <div class="form-control"><?= htmlspecialchars($row['Assigned_to']) ?></div>
                            </div>
                            <div class="form-field">
                                <label>Attachment</label>
                                <div class="form-control"><?= htmlspecialchars($row['Attachment']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                $index++;
                endwhile; 
            else: ?>
                <div class="alert alert-info">No completed concerns to display.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>