<?php
// Start session and include database configuration
session_start();
include("config.php");

// Redirect to login if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit();
}

// Get logged-in user's username and display name
$username = $_SESSION['username'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;

// FIXED: Detect where the user came from for return button
$return_url = "userconcerns.php"; // Default return URL

// Check URL parameter first
if (isset($_GET['from'])) {
    if ($_GET['from'] === 'dashboard') {
        $return_url = "userdb.php";
        $_SESSION['archived_from'] = 'dashboard';
    } elseif ($_GET['from'] === 'concerns') {
        $return_url = "userconcerns.php";
        $_SESSION['archived_from'] = 'concerns';
    }
} 
// Then check session
elseif (isset($_SESSION['archived_from'])) {
    if ($_SESSION['archived_from'] === 'dashboard') {
        $return_url = "userdb.php";
    } elseif ($_SESSION['archived_from'] === 'concerns') {
        $return_url = "userconcerns.php";
    }
}
// Finally check HTTP referrer as fallback
elseif (isset($_SERVER['HTTP_REFERER'])) {
    $referrer = $_SERVER['HTTP_REFERER'];
    if (strpos($referrer, 'userdb.php') !== false) {
        $return_url = "userdb.php";
        $_SESSION['archived_from'] = 'dashboard';
    } elseif (strpos($referrer, 'userconcerns.php') !== false) {
        $return_url = "userconcerns.php";
        $_SESSION['archived_from'] = 'concerns';
    }
}

// Fetch AccountID of the logged-in user
$userQuery = "SELECT AccountID FROM accounts WHERE Username = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$userResult = $stmt->get_result();
$userRow = $userResult->fetch_assoc();
$accountID = $userRow ? $userRow['AccountID'] : 0;
$stmt->close();

// Fetch completed or cancelled concerns with feedback status and admin response
$concernsQuery = "SELECT c.ConcernID, c.Concern_Title, c.Description, c.building_name, c.Room, 
                         c.Service_type, c.EFname, c.Assigned_to, c.Attachment, c.Status, c.Concern_Date,
                         f.FeedbackID, f.Comments as FeedbackComments, f.Date_Submitted as FeedbackDate,
                         f.Admin_Response, f.Date_Responded
                  FROM concerns c 
                  LEFT JOIN feedbacks f ON c.ConcernID = f.ConcernID AND f.AccountID = ?
                  WHERE c.AccountID = ? AND (c.Status = 'Completed' OR c.Status = 'Cancelled') 
                  ORDER BY c.Concern_Date DESC";
$stmt2 = $conn->prepare($concernsQuery);
$stmt2->bind_param("ii", $accountID, $accountID);
$stmt2->execute();
$concernsResult = $stmt2->get_result();
$stmt2->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, shrink-to-fit=no">
<title>Archived Concerns</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
/* General Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    background: #f9fafb;
    overflow-x: hidden;
    min-height: 100vh;
}

/* Navbar */
.navbar {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #087830, #3c4142);
    padding: 12px 15px;
    color: white;
    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    position: relative;
    width: 100%;
}

.navbar-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo img {
    height: 35px;
    width: auto;
    object-fit: contain;
}

.navbar h2 {
    font-size: 24px;
    margin: 0;
}

.return-btn {
    background: #107040;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: background 0.3s;
    font-size: 14px;
    min-height: 44px;
    margin-left: auto;
}

.return-btn:hover {
    background: #07532e;
    color: white;
}

/* Container */
.container {
    padding: 20px 15px;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}

/* Concern Container */
.concern-container {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 850px;
    margin: 0 auto;
    max-height: 625px;
    margin-top: 15px;
    overflow-y: auto;
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

/* Accordion - Consistent with userconcerns.php */
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

.status-completed {
    background: #d1fae5;
    color: #065f46;
}

.status-cancelled {
    background: #fef3c7;
    color: #b45309;
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

/* Submit Feedback Button */
.feedback-btn {
    background: #28a745;
    color: white;
    font-weight: bold;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    min-width: 180px;
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.feedback-btn:hover {
    background: #087830;
    color: white;
}

.feedback-btn:disabled {
    background: #6c757d;
    cursor: not-allowed;
    transform: none;
}

.feedback-btn:disabled:hover {
    background: #6c757d;
}

/* Feedback Submitted State */
.feedback-submitted {
    background: #6c757d;
    cursor: not-allowed;
}

.feedback-submitted-text {
    color: #28a745;
    font-weight: bold;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Admin Response Styles - Now matching user feedback exactly */
.admin-response-section {
    margin-top: 15px;
}

.admin-response-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.admin-response-icon {
    color: #0d6efd;
    font-size: 16px;
}

.admin-response-title {
    font-weight: bold;
    color: #0d6efd;
    font-size: 14px;
}

.admin-response-date {
    color: #6c757d;
    font-size: 12px;
    margin-top: 5px;
    margin-bottom: 8px;
}

.admin-response-text {
    color: #495057;
    margin-bottom: 0;
    font-size: 14px;
    line-height: 1.5;
}

/* Attachment Styles */
.attachment-btn {
    background: #198754;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    font-size: 14px;
    min-height: 44px;
}

.attachment-btn:hover {
    background: #146c43;
    color: white;
}

.attachment-btn i {
    font-size: 14px;
}

/* Modal Styles */
.modal-header {
    background: linear-gradient(135deg, #087830, #3c4142);
    color: white;
}

.modal-header .btn-close {
    filter: invert(1);
}

.image-modal .modal-dialog {
    max-width: 100%;
    width: 1000px;
    max-height: 90vh;
}

.image-modal .modal-content {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.image-modal .modal-body {
    text-align: center;
    padding: 0;
    background: #f8f9fa;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
    max-height: 70vh;
}

.image-modal img {
    max-width: 100%;
    max-height: 70vh;
    object-fit: contain;
    border-radius: 0;
}

.file-modal .modal-dialog {
    max-width: 500px;
}

.file-modal .modal-body {
    text-align: center;
    padding: 40px 20px;
}

.file-icon {
    font-size: 64px;
    color: #6c757d;
    margin-bottom: 20px;
}

.file-info {
    margin-bottom: 20px;
}

.file-name {
    font-weight: bold;
    color: #495057;
    margin-bottom: 5px;
}

.file-type {
    color: #6c757d;
    font-size: 14px;
}

.download-btn {
    background: #198754;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background-color 0.3s;
    font-weight: 500;
    min-height: 44px;
}

.download-btn:hover {
    background: #146c43;
    color: white;
}

/* No attachment styling */
.no-attachment {
    color: #6c757d;
    font-style: italic;
}

/* Enhanced highlight styles */
.accordion-item.highlighted {
    background-color: #e8f5e8;
    border-left: 4px solid #1f9158;
    transition: all 0.3s ease;
}

.accordion-item.highlighted .accordion-button {
    background: linear-gradient(90deg, #1f9158, #163a37);
}

/* Scrollbar for WebKit */
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

/* Focus styles for accessibility */
.return-btn:focus,
.feedback-btn:focus,
.attachment-btn:focus,
.download-btn:focus,
.btn:focus {
    outline: 2px solid #087830;
    outline-offset: 2px;
}

/* Responsive adjustments - Made consistent with provided media queries */
@media (max-width: 768px) {
    .navbar {
        padding: 10px 12px;
        flex-wrap: wrap;
    }
    
    .navbar-left {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
    }
    
    .navbar h2 {
        font-size: 18px;
        margin-left: 10px;
    }
    
    .return-btn {
        order: 2;
        margin-left: auto;
        font-size: 13px;
        padding: 6px 12px;
        min-height: 40px;
    }
    
    .container {
        padding: 15px;
    }
    
    .concern-container {
        padding: 20px;
        margin: 0 10px 20px;
        max-height: 65vh;
    }
    
    .accordion-button {
        padding: 12px;
        font-size: 13px;
        min-height: 55px;
    }
    
    .accordion-body {
        padding: 12px;
    }
    
    .feedback-btn {
        width: 100%;
        margin-top: 10px;
        font-size: 13px;
        padding: 8px 16px;
        min-height: 40px;
    }

    .image-modal .modal-dialog {
        max-width: 95%;
        margin: 10px auto;
    }

    .image-modal .modal-body {
        min-height: 300px;
        max-height: 60vh;
    }
    
    .form-field .form-control {
        font-size: 14px;
        padding: 10px 12px;
    }
}

@media (max-width: 480px) {
    .navbar {
        padding: 8px 10px;
    }
    
    .navbar-left {
        gap: 8px;
    }
    
    .navbar h2 {
        font-size: 16px;
        margin-left: 5px;
    }
    
    .return-btn {
        padding: 5px 10px;
        font-size: 12px;
        min-height: 35px;
    }
    
    .container {
        padding: 10px;
    }

    .concern-container {
        padding: 10px;
        max-height: 1000px;
        max-width: 350px;
        margin-top: 15px;
    }
            
    .accordion-button {
        padding: 10px;
        font-size: 10px;
        min-height: 50px;
        
    }
    
    .accordion-body {
        padding: 10px;
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
        font-size: 12px;
        min-height: 30px;
        padding: 6px 8px;
    }
    
    .status-badge {
        font-size: 8px;
        padding: 5px 10px;
    }

    .attachment-btn {
        width: 100%;
        justify-content: center;
        margin-top: 8px;
        font-size: 13px;
        padding: 6px 12px;
        min-height: 40px;
    }
    
    .feedback-btn {
        font-size: 10px;
        padding: 6px 12px;
        min-height: 38px;
    }
}

@media (min-width: 481px) and (max-width: 768px) {
    .navbar {
        padding: 12px 15px;
    }
    
    .navbar h2 {
        font-size: 20px;
    }
    
    .concern-container {
        padding: 20px;
    }
    
    .accordion-button {
        font-size: 14px;
    }
}

@media (max-width: 576px) {
    .concern-container {
        padding: 12px;
        margin: 10px;
    }
    
    .concern-header {
        font-size: 15px;
        padding: 10px;
    }
    
    .navbar h2 {
        font-size: 15px;
    }
    
    .return-btn {
        font-size: 12px;
        padding: 4px 8px;
    }
    
    .feedback-btn {
        font-size: 12px;
    }
}

/* Reduced motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms;
        animation-iteration-count: 1;
        transition-duration: 0.01ms;
    }
    
    .accordion-item {
        transition: none;
    }
    
    .feedback-btn {
        transition: none;
    }
    
    .attachment-btn {
        transition: none;
    }
}
</style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="navbar-left">
        <div class="logo">
            <img src="img/LSULogo.png" alt="LSU Logo">
        </div>
        <h2>Archived Concerns</h2>
    </div>

    <a href="<?= $return_url ?>" class="return-btn">
        <i class="fas fa-arrow-left me-1"></i> Return
    </a>
</div>

<!-- Main Content -->
<div class="container">
    <div class="concern-container">
        <div class="concern-header">Your Archived Concerns</div>

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
                    $concernID = $row['ConcernID'];
                    $date = date("l, d M Y", strtotime($row['Concern_Date']));
                    $hasFeedback = !empty($row['FeedbackID']);
                    $feedbackDate = $hasFeedback ? date('M d, Y', strtotime($row['FeedbackDate'])) : '';
                    $hasAdminResponse = !empty($row['Admin_Response']);
                    $adminResponseDate = $hasAdminResponse ? date('M d, Y', strtotime($row['Date_Responded'])) : '';
            ?>
                <div class="accordion-item">
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
                            
                            <div class="form-field">
                                <label>Concern Title</label>
                                <div class="form-control"><?= htmlspecialchars($row['Concern_Title']) ?></div>
                            </div>
                            
                            
                            <div class="form-field">
                                <label>Description</label>
                                <div class="form-control"><?= htmlspecialchars($row['Description']) ?></div>
                            </div>

                           
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
                                            <?= !empty($row['building_name']) ? htmlspecialchars($row['building_name']) : 'Not specified' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                           
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

                            
                            <div class="form-field">
                                <label>Assigned To</label>
                                <div class="form-control"><?= !empty($row['Assigned_to']) ? htmlspecialchars($row['Assigned_to']) : 'Not assigned yet' ?></div>
                            </div>
                            
                            
                            <div class="form-field">
                                <label>Attachment</label>
                                <div class="form-control">
                                    <?php if (!empty($row['Attachment'])): 
                                        $attachment = htmlspecialchars($row['Attachment']);
                                        $fileExtension = strtolower(pathinfo($attachment, PATHINFO_EXTENSION));
                                        $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                                        $fileName = basename($attachment);
                                    ?>
                                        <?php if ($isImage): ?>
                                            <button type="button" class="attachment-btn" 
                                                    data-bs-toggle="modal" data-bs-target="#imageModal"
                                                    onclick="openImageModal('<?= $attachment ?>', '<?= $fileName ?>')">
                                                <i class="fas fa-paperclip"></i>
                                                View Attachment
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="attachment-btn" 
                                                    data-bs-toggle="modal" data-bs-target="#fileModal"
                                                    onclick="openFileModal('<?= $attachment ?>', '<?= $fileName ?>', '<?= strtoupper($fileExtension) ?>')">
                                                <i class="fas fa-paperclip"></i>
                                                View Attachment (<?= strtoupper($fileExtension) ?>)
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="no-attachment">No attachment</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            
                            <div class="form-field">
                                <label>Feedback Status</label>
                                <div class="form-control">
                                    <?php if ($hasFeedback): ?>
                                       
                                        <div class="feedback-submitted-text">
                                            <i class="fas fa-check-circle text-success"></i>
                                            <span>Feedback submitted on <?= $feedbackDate ?></span>
                                        </div>
                                        <?php if (!empty($row['FeedbackComments'])): ?>
                                            <div class="mt-2 p-2 bg-light rounded">
                                                <strong>Your feedback:</strong>
                                                <p class="mb-0"><?= htmlspecialchars($row['FeedbackComments']) ?></p>
                                            </div>
                                        <?php endif; ?>

                                        
                                        <?php if ($hasAdminResponse): ?>
                                            <div class="admin-response-section">
                                                <div class="admin-response-header">
                                                    <i class="fas fa-user-shield admin-response-icon"></i>
                                                    <span class="admin-response-title">Admin Response</span>
                                                </div>
                                                <?php if ($adminResponseDate): ?>
                                                    <div class="admin-response-date">
                                                        Responded on: <?= $adminResponseDate ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="mt-2 p-2 bg-light rounded">
                                                    <strong>Admin Response:</strong>
                                                    <p class="admin-response-text mb-0"><?= htmlspecialchars($row['Admin_Response']) ?></p>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="mt-2 text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                Waiting for admin response...
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No feedback submitted yet</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Feedback Button -->
                            <div class="text-end mt-3">
                                <?php if ($hasFeedback): ?>
                                    <button class="feedback-btn" disabled>
                                        <i class="fas fa-check me-1"></i> Feedback Already Submitted
                                    </button>
                                <?php else: ?>
                                    <button class="feedback-btn" data-bs-toggle="modal" data-bs-target="#feedbackModal" data-concernid="<?= $concernID ?>">
                                        <i class="fas fa-comment me-1"></i> Submit Feedback
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                $index++;
                endwhile; 
            else: ?>
                <div class="alert alert-info text-center">You have no archived concerns yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #087830, #3c4142); color:white;">
                <h5 class="modal-title" id="feedbackModalLabel">
                    <i class="fas fa-comment me-2"></i>Submit Feedback
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="feedbackForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="concern_id" id="modalConcernID">
                    <div class="mb-3">
                        <label for="comments" class="form-label fw-bold">Your Feedback/Comments:</label>
                        <textarea class="form-control" id="comments" name="comments" rows="4" placeholder="Enter your feedback about how this concern was handled..." required></textarea>
                        <div class="form-text">You can only submit feedback once per concern.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success w-100" style="min-height: 44px;">
                        <i class="fas fa-paper-plane me-1"></i> Submit Feedback
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade image-modal" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Attachment Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="modalImage" src="" alt="Concern Attachment" class="img-fluid">
            </div>
            <div class="modal-footer">
                <a href="#" id="downloadImage" class="download-btn">
                    <i class="fas fa-download me-1"></i> Download Image
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- File Preview Modal -->
<div class="modal fade file-modal" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileModalLabel">File Attachment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="file-icon">
                    <i class="fas fa-file"></i>
                </div>
                <div class="file-info">
                    <div class="file-name" id="fileName"></div>
                    <div class="file-type" id="fileType"></div>
                </div>
                <p class="text-muted">This file type cannot be previewed in the browser.</p>
            </div>
            <div class="modal-footer">
                <a href="#" id="downloadFile" class="download-btn">
                    <i class="fas fa-download me-1"></i> Download File
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
// Handle feedback modal
const feedbackModal = document.getElementById('feedbackModal');
feedbackModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const concernId = button.getAttribute('data-concernid');
    
    // Set the concern ID in the hidden input
    document.getElementById('modalConcernID').value = concernId;
});

// Handle image preview modal
function openImageModal(imageSrc, fileName) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('downloadImage').href = imageSrc;
    document.getElementById('imageModalLabel').textContent = 'Preview: ' + fileName;
}

// Handle file preview modal
function openFileModal(fileUrl, fileName, fileType) {
    document.getElementById('fileName').textContent = fileName;
    document.getElementById('fileType').textContent = 'File type: ' + fileType;
    document.getElementById('downloadFile').href = fileUrl;
    document.getElementById('fileModalLabel').textContent = 'File: ' + fileName;
}

// Handle form submission with AJAX
document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
    submitBtn.disabled = true;
    
    fetch('submit_feedback.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('feedbackModal'));
            modal.hide();
            
          
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Your feedback has been submitted successfully.',
                confirmButtonColor: '#087830'
            }).then(() => {
              
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to submit feedback. Please try again.',
                confirmButtonColor: '#dc3545'
            });
            
            
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred. Please try again.',
            confirmButtonColor: '#dc3545'
        });
        
        // Reset button state on error
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>
</body>
</html>