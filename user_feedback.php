<?php
// Start a session to track logged-in user
session_start();

// Include database configuration file to connect to the database
include("config.php");

// Redirect to login page if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in username
$username = $_SESSION['username'];

// Get the AccountID of the logged-in user from the database
$stmt = $conn->prepare("SELECT AccountID FROM Accounts WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$accountID = ($row = $result->fetch_assoc()) ? $row['AccountID'] : 0;

// Get the ConcernID from the URL parameter to know which concern the feedback is for
$concernID = isset($_GET['concern_id']) ? intval($_GET['concern_id']) : 0;

// Fetch the details of the selected concern from the database
$stmt = $conn->prepare("SELECT * FROM Concerns WHERE ConcernID = ? AND AccountID = ?");
$stmt->bind_param("ii", $concernID, $accountID);
$stmt->execute();
$concern = $stmt->get_result()->fetch_assoc();

// Handle the feedback form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comments = trim($_POST['comments']); // Get user input from textarea
    if ($comments) {
        // Insert feedback into the Feedback table with current timestamp
        $stmt = $conn->prepare("INSERT INTO Feedback (ConcernID, AccountID, Comments, Date_Submitted) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $concernID, $accountID, $comments);
        if ($stmt->execute()) {
            // Show alert and redirect to archived concerns page after successful submission
            echo "<script>
                    alert('Feedback submitted successfully!');
                    window.location.href='user_archived.php';
                  </script>";
            exit();
        } else {
            // Show error if database insert fails
            echo "<script>alert('Error submitting feedback. Please try again.');</script>";
        }
    } else {
        // Show alert if user submits empty comments
        echo "<script>alert('Comments cannot be empty.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Feedback</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
/* -------------------- DESIGN & LAYOUT STYLES -------------------- */
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

.main {
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: calc(100vh - 80px);
}

.feedback-container {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 700px;
}

.accordion-button {
    background: linear-gradient(135deg, #163a37, #1f9158);
    color: white;
    font-weight: 600;
    border: none;
}

.accordion-button:not(.collapsed) {
    color: white;
    background: linear-gradient(135deg, #1f9158, #163a37);
    box-shadow: none;
}

.accordion-body {
    background: #f8f9fa;
    padding: 20px;
}

.form-label {
    font-weight: 600;
    color: #163a37;
    margin-bottom: 8px;
}

.form-control,
.form-select {
    border-radius: 8px;
    border: 1px solid #ced4da;
}

.form-control:focus {
    border-color: #1f9158;
    box-shadow: 0 0 0 0.2rem rgba(31, 145, 88, 0.25);
}

textarea {
    resize: vertical;
    min-height: 100px;
}

.feedback-btn {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    font-weight: bold;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 16px;
    width: 100%;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 15px;
}

.feedback-btn:hover {
    background: linear-gradient(135deg, #218838, #1ea085);
    transform: translateY(-1px);
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
    .navbar {
        padding: 12px 15px;
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
    
    .feedback-container {
        padding: 20px;
    }
    
    .accordion-body {
        padding: 15px;
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
    
    .feedback-container {
        padding: 15px;
    }
    
    .accordion-body {
        padding: 12px;
    }
    
    .form-control,
    .form-select {
        font-size: 14px;
        padding: 8px 12px;
    }
    
    .feedback-btn {
        padding: 10px 15px;
        font-size: 15px;
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
    
    .feedback-container {
        padding: 12px;
    }
    
    .accordion-body {
        padding: 10px;
    }
}

/* Scrollbar styling for feedback container */
.feedback-container::-webkit-scrollbar {
    width: 6px;
}

.feedback-container::-webkit-scrollbar-thumb {
    background-color: #1f9158;
    border-radius: 10px;
}

.feedback-container::-webkit-scrollbar-track {
    background-color: #f0f0f0;
}
</style>
</head>
<body>

<!-- -------------------- NAVBAR -------------------- -->
<div class="navbar">
    <div class="logo">
        <img src="img/LSULogo.png" alt="LSU Logo">
        <h2>Feedback</h2>
    </div>

    <a href="user_archived.php" class="return-btn">
        <i class="fas fa-arrow-left me-1"></i> Return
    </a>
</div>

<!-- -------------------- MAIN CONTENT -------------------- -->
<div class="main">
    <div class="feedback-container">
        <!-- Show concern details if concern exists -->
        <?php if($concern): ?>
        <div class="accordion" id="feedbackAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingConcern">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseConcern" aria-expanded="false" aria-controls="collapseConcern">
                        Concern Details
                    </button>
                </h2>
                <div id="collapseConcern" class="accordion-collapse collapse" aria-labelledby="headingConcern" data-bs-parent="#feedbackAccordion">
                    <div class="accordion-body">
                        <!-- Concern Title -->
                        <div class="mb-3">
                            <label class="form-label">Concern Title</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($concern['Concern_Title']) ?>" readonly>
                        </div>
                        <!-- Concern Description -->
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($concern['Description']) ?></textarea>
                        </div>
                        <!-- Room & Equipment -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Room</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($concern['Room'] ?? 'N/A') ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Equipment / Facility</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($concern['Equipment'] ?? 'N/A') ?>" readonly>
                            </div>
                        </div>
                        <!-- Problem Type & Priority -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Problem Type</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($concern['Problem_Type']) ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($concern['Priority']) ?>" readonly>
                            </div>
                        </div>
                        <!-- Attachment -->
                        <div class="mb-3">
                            <label class="form-label">Attachment (Photo/Video)</label>
                            <?php if(!empty($concern['Attachment'])): ?>
                                <a href="uploads/<?= htmlspecialchars($concern['Attachment']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm d-inline-block mt-1">View Attachment</a>
                            <?php else: ?>
                                <input type="text" class="form-control" value="No attachment uploaded" readonly>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">Concern not found.</div>
        <?php endif; ?>

        <!-- Feedback Form -->
        <form method="POST" class="mt-4">
            <div class="mb-3">
                <label for="comments" class="form-label">Comments</label>
                <textarea id="comments" name="comments" class="form-control" rows="4" placeholder="Enter your feedback here..." required></textarea>
            </div>
            <button type="submit" class="feedback-btn">Submit Feedback</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>