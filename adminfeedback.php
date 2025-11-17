<?php
session_start();
include("config.php");

// Only allow admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

$username = $_SESSION['username'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;
$activePage = "feedback";

// Handle admin response submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'], $_POST['response'])) {
    $feedback_id = intval($_POST['feedback_id']);
    $response = trim($_POST['response']);

    if (!empty($response)) {
        // Check if response already exists
        $check_stmt = $conn->prepare("SELECT Admin_Response FROM feedbacks WHERE FeedbackID = ?");
        $check_stmt->bind_param("i", $feedback_id);
        $check_stmt->execute();
        $check_stmt->bind_result($existing_response);
        $check_stmt->fetch();
        $check_stmt->close();

        if (empty($existing_response)) {
            $stmt = $conn->prepare(
                "UPDATE feedbacks SET Admin_Response = ?, Date_Responded = NOW() WHERE FeedbackID = ?"
            );
            $stmt->bind_param("si", $response, $feedback_id);
            
            if ($stmt->execute()) {
                $_SESSION['response_success'] = true;
                $_SESSION['feedback_id'] = $feedback_id;
            } else {
                $_SESSION['response_success'] = false;
                $_SESSION['response_error'] = "Failed to submit response. Please try again.";
            }
            $stmt->close();
        } else {
            $_SESSION['response_success'] = false;
            $_SESSION['response_error'] = "Response already submitted for this feedback.";
        }
        
        // Redirect to avoid form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Check for success/error message from redirect
$show_success_alert = false;
$show_error_alert = false;
$alert_message = "";
$feedback_id_success = null;

if (isset($_SESSION['response_success'])) {
    if ($_SESSION['response_success'] === true) {
        $show_success_alert = true;
        $feedback_id_success = $_SESSION['feedback_id'] ?? null;
    } else {
        $show_error_alert = true;
        $alert_message = $_SESSION['response_error'] ?? "An error occurred while submitting your response.";
    }
    unset($_SESSION['response_success']);
    unset($_SESSION['response_error']);
    unset($_SESSION['feedback_id']);
}

// Fetch all feedback with concerns and users INCLUDING Admin_Response
$query = "
    SELECT f.FeedbackID, f.Comments, f.Date_Submitted, f.Admin_Response, f.Date_Responded,
           c.Concern_Title, c.Description, c.Room, c.Service_type,
           a.Username, a.Name
    FROM Feedbacks f
    JOIN concerns c ON f.ConcernID = c.ConcernID
    JOIN accounts a ON f.AccountID = a.AccountID
    ORDER BY f.Date_Submitted DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Feedback</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
            padding: 8px 12px;
            vertical-align: middle;
        }
        
        .table tbody td {
            font-weight: normal;
        }

        .table-container {
            margin: 0 40px 40px 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .respond-btn {
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

        .respond-btn:hover {
            background-color: #157347;
        }

        .responded-btn {
            font-size: 13px;
            padding: 6px 16px;
            border-radius: 6px;
            border: none;
            font-weight: bold;
            width: 100px;
            text-align: center;
            background-color: #6c757d;
            color: white;
            cursor: not-allowed;
        }

        .no-response {
            color: #6c757d;
            font-style: italic;
        }

        /* UPDATED: Modal header gradient */
        .modal-header-gradient {
            background: linear-gradient(135deg, #087830, #3c4142);
            color: white;
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

        /* REMOVED: User comment box styling */
        .user-comment {
            /* Removed background and border to make it normal text */
            padding: 0;
            margin: 0;
        }

        .comment-label {
            /* Removed label styling */
            display: none;
        }

        /* Modal text styling */
        .feedback-text {
            font-size: 14px;
            line-height: 1.5;
            color: #495057;
            margin-bottom: 15px;
        }

        /* ADDED: Smaller centered modal */
        .custom-modal {
            max-width: 600px;
            margin: 1.75rem auto;
        }

        .custom-modal .modal-content {
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
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
        <h3><i class="fas fa-comment-alt me-2"></i>User Feedback</h3>
    </div>

    <!-- UPDATED: Removed container div, table is directly in table-container -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-success">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Room</th>
                        <th>Service Type</th>
                        <th>Comments</th>
                        <th>Date Submitted</th>
                        <th>Admin Response</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <?php 
                            $hasResponse = !empty($row['Admin_Response']);
                            $buttonClass = $hasResponse ? 'responded-btn' : 'respond-btn';
                            $buttonText = $hasResponse ? 'Responded' : 'Respond';
                            $disabled = $hasResponse ? 'disabled' : '';
                            ?>
                            <tr>
                                <td><?= $row['FeedbackID'] ?></td>
                                <td><?= htmlspecialchars($row['Name'] ?? $row['Username']) ?></td>
                                <td><?= htmlspecialchars($row['Room']) ?></td>
                                <td><?= htmlspecialchars($row['Service_type']) ?></td>
                                <td>
                                    <div class="user-comment">
                                        <?= nl2br(htmlspecialchars($row['Comments'])) ?>
                                    </div>
                                </td>
                                <td><?= date('M j, Y', strtotime($row['Date_Submitted'])) ?></td>
                                <td>
                                    <?php if ($hasResponse): ?>
                                        <div class="admin-response">
                                            <?= nl2br(htmlspecialchars($row['Admin_Response'])) ?>
                                            <!-- REMOVED: Date display from admin response -->
                                        </div>
                                    <?php else: ?>
                                        <span class="no-response">No response yet</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button 
                                        class="<?= $buttonClass ?>" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#responseModal<?= $row['FeedbackID'] ?>"
                                        <?= $disabled ?>>
                                        <?= $buttonText ?>
                                    </button>

                                    <!-- Response Modal -->
                                    <!-- UPDATED: Added custom-modal class and removed modal-lg -->
                                    <div class="modal fade" id="responseModal<?= $row['FeedbackID'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $row['FeedbackID'] ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered custom-modal">
                                            <form method="POST" class="modal-content">
                                                <div class="modal-header modal-header-gradient">
                                                    <h5 class="modal-title" id="modalLabel<?= $row['FeedbackID'] ?>">
                                                        <i class="fas fa-reply me-2"></i>Respond to Feedback #<?= $row['FeedbackID'] ?>
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Feedback:</label>
                                                        <div class="feedback-text">
                                                            <?= nl2br(htmlspecialchars($row['Comments'])) ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="response<?= $row['FeedbackID'] ?>" class="form-label fw-bold">Your Response:</label>
                                                        <textarea 
                                                            name="response" 
                                                            id="response<?= $row['FeedbackID'] ?>" 
                                                            class="form-control" 
                                                            rows="5" 
                                                            placeholder="Enter your response here..." 
                                                            required
                                                            <?= $hasResponse ? 'readonly' : '' ?>><?= htmlspecialchars($row['Admin_Response'] ?? '') ?></textarea>
                                                        <?php if ($hasResponse): ?>
                                                            <div class="form-text text-muted mt-2">
                                                                <i class="fas fa-info-circle me-1"></i>
                                                                Response already submitted. You cannot modify it.
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <input type="hidden" name="feedback_id" value="<?= $row['FeedbackID'] ?>">
                                                </div>
                                                <div class="modal-footer">
                                                    <?php if (!$hasResponse): ?>
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="fas fa-paper-plane me-1"></i> Submit Response
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-success" disabled>
                                                            <i class="fas fa-check me-1"></i> Already Responded
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h4>No Feedback Available</h4>
                                    <p>User feedback will appear here when submitted.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show success/error alerts
        <?php if ($show_success_alert): ?>
            Swal.fire({
                icon: 'success',
                title: 'Response Submitted!',
                text: 'Your response has been successfully submitted to the user.',
                confirmButtonColor: '#198754',
                confirmButtonText: 'OK',
                timer: 3000,
                timerProgressBar: true
            });
        <?php endif; ?>

        <?php if ($show_error_alert): ?>
            Swal.fire({
                icon: 'error',
                title: 'Submission Failed',
                text: '<?= $alert_message ?>',
                confirmButtonColor: '#198754',
                confirmButtonText: 'OK'
            });
        <?php endif; ?>

        // Add confirmation for form submission
        const forms = document.querySelectorAll('form[method="POST"]');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const responseTextarea = this.querySelector('textarea[name="response"]');
                const feedbackId = this.querySelector('input[name="feedback_id"]').value;
                
                // Check if already responded
                const respondButton = document.querySelector(`button[data-bs-target="#responseModal${feedbackId}"]`);
                if (respondButton && respondButton.classList.contains('responded-btn')) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Already Responded',
                        text: 'You have already submitted a response for this feedback.',
                        confirmButtonColor: '#198754'
                    });
                    return;
                }

                if (responseTextarea && responseTextarea.value.trim() === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Empty Response',
                        text: 'Please enter a response before submitting.',
                        confirmButtonColor: '#198754'
                    });
                    return;
                }

                e.preventDefault(); // Prevent immediate submission
                
                Swal.fire({
                    title: 'Submit Response?',
                    text: "Are you sure you want to submit this response to the user?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, submit it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Submitting...',
                            text: 'Please wait while we submit your response.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Submit the form
                        this.submit();
                    }
                });
            });
        });

        // Auto-focus on textarea when modal opens (only for non-responded feedback)
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.addEventListener('shown.bs.modal', function() {
                const textarea = this.querySelector('textarea');
                const respondButton = document.querySelector(`button[data-bs-target="#${this.id}"]`);
                
                if (textarea && respondButton && !respondButton.classList.contains('responded-btn')) {
                    textarea.focus();
                }
            });
        });
    });
    </script>
</body>
</html>