<?php
session_start();
include("config.php");

// Only allow admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Handle admin response submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'], $_POST['response'])) {
    $feedback_id = intval($_POST['feedback_id']);
    $response = trim($_POST['response']);

    if (!empty($response)) {
        $stmt = $conn->prepare(
            "UPDATE feedbacks SET Admin_Response = ?, Date_Responded = NOW() WHERE FeedbackID = ?"
        );
        $stmt->bind_param("si", $response, $feedback_id);
        
        if ($stmt->execute()) {
            $_SESSION['response_success'] = true;
            $_SESSION['feedback_id'] = $feedback_id;
        } else {
            $_SESSION['response_success'] = false;
        }
        $stmt->close();
        
        // Redirect to avoid form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Check for success message from redirect
$show_success_alert = false;
$feedback_id_success = null;
if (isset($_SESSION['response_success']) && $_SESSION['response_success'] === true) {
    $show_success_alert = true;
    $feedback_id_success = $_SESSION['feedback_id'] ?? null;
    unset($_SESSION['response_success']);
    unset($_SESSION['feedback_id']);
}

// Fetch all feedback with concerns and users INCLUDING Admin_Response
$query = "
    SELECT f.FeedbackID, f.Comments, f.Date_Submitted, f.Admin_Response, f.Date_Responded,
           c.Concern_Title, c.Description, c.Room, c.Service_type,
           a.Username, a.Name
    FROM Feedbacks f
    JOIN Concerns c ON f.ConcernID = c.ConcernID
    JOIN Accounts a ON f.AccountID = a.AccountID
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
    color: white;
}

.table-container {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    margin-top: 30px;
}

/* Style for admin response */
.admin-response {
    background: #e8f5e8;
    border-left: 4px solid #28a745;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 14px;
    margin-top: 5px;
}

.response-date {
    font-size: 12px;
    color: #6c757d;
    font-style: italic;
    margin-top: 5px;
}

.no-response {
    color: #6c757d;
    font-style: italic;
}

/* Make table responsive */
.table-responsive {
    border-radius: 8px;
    overflow: hidden;
}
</style>
</head>
<body>

<div class="navbar">
    <div class="logo">
        <img src="img/LSULogo.png" alt="LSU Logo">
        <h2>Admin Feedback</h2>
    </div>

    <a href="#" id="returnButton" class="return-btn">
        <i class="fas fa-arrow-left me-1"></i> Return
    </a>
</div>

<div class="container">
    <div class="table-container">
        <h2>All User Feedback</h2>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Concern Title</th>
                        <th>Description</th>
                        <th>User Comments</th>
                        <th>Date Submitted</th>
                        <th>Admin Response</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['FeedbackID'] ?></td>
                                <td><?= htmlspecialchars($row['Name'] ?? $row['Username']) ?></td>
                                <td><?= htmlspecialchars($row['Concern_Title']) ?></td>
                                <td><?= htmlspecialchars($row['Description']) ?></td>
                                <td><?= htmlspecialchars($row['Comments']) ?></td>
                                <td><?= date('M d, Y', strtotime($row['Date_Submitted'])) ?></td>
                                <td>
                                    <?php if (!empty($row['Admin_Response'])): ?>
                                        <?= nl2br(htmlspecialchars($row['Admin_Response'])) ?>
                                    <?php else: ?>
                                        <span class="no-response">No response yet</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#responseModal<?= $row['FeedbackID'] ?>"> Respond </button>

                                    <!-- Modal -->
                                    <div class="modal fade" id="responseModal<?= $row['FeedbackID'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $row['FeedbackID'] ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form method="POST" class="modal-content">
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title" id="modalLabel<?= $row['FeedbackID'] ?>">
                                                        <i class="fas fa-reply me-2"></i>Respond to Feedback #<?= $row['FeedbackID'] ?>
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold"><?= htmlspecialchars($row['Name'] ?? $row['Username']) ?> Feedback:</label>
                                                        <div class="form-control" style="background-color: #f8f9fa; min-height: 80px;">
                                                            <?= nl2br(htmlspecialchars($row['Comments'])) ?>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="response<?= $row['FeedbackID'] ?>" class="form-label fw-bold">Your Response:</label>
                                                        <textarea name="response" id="response<?= $row['FeedbackID'] ?>" class="form-control" rows="4" placeholder="Enter your response here..." required><?= htmlspecialchars($row['Admin_Response'] ?? '') ?></textarea>
                                                    </div>
                                                    <input type="hidden" name="feedback_id" value="<?= $row['FeedbackID'] ?>">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="fas fa-paper-plane me-1"></i> Submit Response
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i><br>
                                No feedback available
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
// Store the referrer URL when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Get the referrer (previous page)
    const referrer = document.referrer;
    
    // Store it in sessionStorage for persistence
    if (referrer && !referrer.includes('adminfeedback.php')) {
        sessionStorage.setItem('previousPage', referrer);
    }
    
    // Set the return button href
    const returnButton = document.getElementById('returnButton');
    const previousPage = sessionStorage.getItem('previousPage');
    
    if (previousPage) {
        returnButton.href = previousPage;
    } else {
        // Fallback to admin dashboard if no referrer is available
        returnButton.href = 'admindb.php';
    }

    // Show success alert if response was submitted
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

    // Add confirmation for form submission
    const forms = document.querySelectorAll('form[method="POST"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const responseTextarea = this.querySelector('textarea[name="response"]');
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
                    // Submit the form
                    this.submit();
                }
            });
        });
    });
});
</script>
</body>
</html>