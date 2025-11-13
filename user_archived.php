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

// Fetch completed or cancelled concerns
$concernsQuery = "SELECT ConcernID, Concern_Title, Description, building_name, Room, Service_type, EFname, Assigned_to, Attachment, Status, Concern_Date 
                  FROM concerns 
                  WHERE AccountID = ? AND (Status = 'Completed' OR Status = 'Cancelled') 
                  ORDER BY Concern_Date DESC";
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
    margin-top: 25px;
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

/* Modal Styles */
.modal-header {
    background: linear-gradient(135deg, #087830, #3c4142);
    color: white;
}

.modal-header .btn-close {
    filter: invert(1);
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

    <a href="<?= $return_url ?>" class="return-btn">
    <i class="fas fa-arrow-left me-1"></i> Return
</a>
</div>

<!-- Main Content -->
<div class="main">

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
                    $concernID = $row['ConcernID'];
                    $date = date("l, d M Y", strtotime($row['Concern_Date']));
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
                        <button class="feedback-btn" data-bs-toggle="modal" data-bs-target="#feedbackModal" data-concernid="<?= $concernID ?>">
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
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-field">
                                        <label>Equipment / Facility</label>
                                        <div class="form-control"><?= !empty($row['EFname']) ? htmlspecialchars($row['EFname']) : 'Not specified' ?></div>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-field">
                                        <label>Service Type</label>
                                        <div class="form-control"><?= htmlspecialchars($row['Service_type']) ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-field">
                                        <label>Room</label>
                                        <div class="form-control"><?= htmlspecialchars($row['Room']) ?></div>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-field">
                                        <label>Building</label>
                                        <div class="form-control">
                                            <?= !empty($row['building_name']) ? htmlspecialchars($row['building_name']) : 'Not specified' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-field">
                                <label>Assigned To</label>
                                <div class="form-control"><?= !empty($row['Assigned_to']) ? htmlspecialchars($row['Assigned_to']) : 'Not assigned' ?></div>
                            </div>
                            <?php if (!empty($row['Attachment'])): ?>
                            <div class="form-field">
                                <label>Attachment</label>
                                <div class="form-control">
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
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php 
                $index++;
                endwhile; 
            else: ?>
                <div class="alert alert-info">No completed or cancelled concerns to display.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feedbackModalLabel">
                    <i class="fas fa-comment me-2"></i>Submit Feedback
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="feedbackForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="concern_id" id="modalConcernID">
                    <div class="mb-3">
                        <label for="comments" class="form-label fw-bold">Your Feedback/Comments:</label>
                        <textarea class="form-control" id="comments" name="comments" rows="4" placeholder="Enter your feedback about how this concern was handled..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <!-- REMOVED: Cancel button -->
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-paper-plane me-1"></i> Submit Feedback
                    </button>
                </div>
            </form>
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

// Handle form submission with AJAX
document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('submit_feedback.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('feedbackModal'));
            modal.hide();
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Your feedback has been submitted successfully.',
                confirmButtonColor: '#087830'
            });
            
            // Clear form
            document.getElementById('feedbackForm').reset();
            
            // Optionally disable the feedback button for this concern
            const concernId = document.getElementById('modalConcernID').value;
            const feedbackButtons = document.querySelectorAll(`[data-concernid="${concernId}"]`);
            feedbackButtons.forEach(btn => {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-check me-1"></i> Feedback Submitted';
                btn.style.backgroundColor = '#6c757d';
                btn.style.cursor = 'not-allowed';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to submit feedback. Please try again.',
                confirmButtonColor: '#dc3545'
            });
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
    });
});
</script>
</body>
</html>