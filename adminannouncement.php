    <?php
    session_start();
    include("config.php");

    if (!isset($_SESSION['username'])) {
        header("Location: admin_login.php");
        exit();
    }
    $username = $_SESSION['username'];
    $name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;

    // Check for alert messages
    if (isset($_SESSION['alert_type']) && isset($_SESSION['alert_message'])) {
        $alert_type = $_SESSION['alert_type'];
        $alert_message = $_SESSION['alert_message'];
        
        // Clear the session variables
        unset($_SESSION['alert_type']);
        unset($_SESSION['alert_message']);
    } else {
        $alert_type = '';
        $alert_message = '';
    }

    // Fetch all announcements
    $announcementsQuery = "SELECT * FROM Announcements ORDER BY created_at DESC";
    $announcementsResult = mysqli_query($conn, $announcementsQuery);
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Announcement Management</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        <!-- Google Fonts Poppins -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
    body {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        font-weight: 600; 
        background: #f4f4f4;
    }

    /* Navbar - Responsive */
    .navbar {
        display: flex;
        align-items: center;
        background: linear-gradient(135deg, #087830, #3c4142);
        padding: 15px;
        color: white;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        position: relative;
        flex-wrap: wrap;
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
        margin-left: 20px;
        font-size: 24px;
        margin-top: 2px;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .navbar h2 {
            font-size: 20px;
            margin-left: 15px;
        }
        
        .navbar {
            padding: 12px 15px;
        }
    }

    @media (max-width: 480px) {
        .navbar h2 {
            font-size: 18px;
            margin-left: 10px;
        }
        
        .logo img {
            height: 30px;
        }
    }

    .return-btn {
        background: #107040;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: background 0.3s;
        font-size: 14px;
        margin-left: auto;
        white-space: nowrap;
    }

    .return-btn:hover {
        background: #07532e;
        color: white;
    }

    @media (max-width: 480px) {
        .return-btn {
            padding: 6px 12px;
            font-size: 13px;
        }
        
        .return-btn i {
            margin-right: 4px;
        }
    }

    /* Container - Responsive */
    .container {
        padding: 20px;
        gap: 30px;
        max-width: 1400px;
        margin: 0 auto;
    }

    @media (min-width: 768px) {
        .container {
            padding: 30px 40px;
        }
    }

    @media (min-width: 1200px) {
        .container {
            padding: 40px 60px;
        }
    }

    /* ===== HORIZONTAL SCROLLABLE CARDS ===== */
    .announcements-section {
        margin-bottom: 30px;
    }

    .announcements-section h3 {
        color: #1f9158;
        font-weight: bold;
        margin-bottom: 20px;
        font-size: 24px;
    }

    @media (max-width: 768px) {
        .announcements-section h3 {
            font-size: 22px;
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .announcements-section h3 {
            font-size: 20px;
            margin-bottom: 15px;
        }
    }

    .horizontal-scroll-wrapper {
        display: flex;
        overflow-x: auto;
        gap: 25px;
        padding: 20px;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        height: 300px;
        width: 100%;
        max-width: 930px; /* Limit maximum width */
        margin: 0 auto; /
        background: white;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    @media (max-width: 768px) {
        .horizontal-scroll-wrapper {
            height: 280px;
            padding: 15px;
        }
    }

    @media (max-width: 480px) {
        .horizontal-scroll-wrapper {
            height: 260px;
            padding: 12px;
            gap: 15px;
        }
    }

    .horizontal-scroll-wrapper::-webkit-scrollbar {
        height: 12px;
    }

    .horizontal-scroll-wrapper::-webkit-scrollbar-thumb {
        background: #1f9158;
        border-radius: 10px;
    }

    .horizontal-scroll-wrapper::-webkit-scrollbar-thumb:hover {
        background: #107040;
    }

    /* Cards - Responsive */
    .dashboard-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        height: 250px;
        min-width: 275px;
        max-width: 278px;
        margin-top: -10px;
        border: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex-shrink: 0;
    }

    @media (max-width: 768px) {
        .dashboard-card {
            min-width: 260px;
            max-width: 260px;
            height: 240px;
            padding: 18px;
        }
    }

    @media (max-width: 480px) {
        .dashboard-card {
            min-width: 240px;
            max-width: 240px;
            height: 230px;
            padding: 15px;
        }
    }

    .dashboard-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    }

    .card-icon {
        font-size: 22px;
        color: #1f9158;
        margin-bottom: 10px;
    }

    @media (max-width: 480px) {
        .card-icon {
            font-size: 20px;
            margin-bottom: 8px;
        }
    }

    .card-value {
        font-weight: 600;
        font-size: 16px;
        color: #275850;
        margin-bottom: 10px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @media (max-width: 480px) {
        .card-value {
            font-size: 15px;
            margin-bottom: 8px;
        }
    }

    .card-label {
        font-size: 14px;
        color: #555;
        line-height: 1.4;
        margin-bottom: 10px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
    }

    @media (max-width: 480px) {
        .card-label {
            font-size: 13px;
            margin-bottom: 8px;
            -webkit-line-clamp: 2;
        }
    }

    .card-date {
    font-size: 11px;
    color: #9ca3af;
    margin-bottom: 10px;
    font-style: italic;
    }
  
    .card-date strong {
    color: #000000;
    font-weight: 600;
    }

    @media (max-width: 480px) {
        .card-date {
            font-size: 11px;
            margin-bottom: 8px;
        }
    }

    .card-buttons {
        display: flex;
        gap: 8px;
        margin-top: auto;
    }

    @media (max-width: 480px) {
        .card-buttons {
            gap: 6px;
        }
    }

    .update-btn,
    .delete-btn {
        flex: 1;
        padding: 8px 0;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    @media (max-width: 480px) {
        .update-btn,
        .delete-btn {
            padding: 10px 0;
            font-size: 12px;
            min-height: 36px;
        }
    }

    .update-btn {
        background-color: #0d6efd;
        color: white;
    }

    .update-btn:hover {
        background-color: #084298;
    }

    .delete-btn {
        background-color: #dc3545;
        color: white;
    }

    .delete-btn:hover {
        background-color: #a71d2a;
    }

    /* ===== FORM ===== */
    .form-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        margin-bottom: 40px;
        border: 1px solid #e5e7eb;
        max-width: 950px;  /* Limit maximum width */
        margin: 0 auto 40px auto; 
    }

    @media (max-width: 768px) {
        .form-card {
            padding: 20px;
        }
    }

    @media (max-width: 480px) {
        .form-card {
            padding: 18px;
            margin-bottom: 30px;
        }
    }

    .form-card h3 {
        font-size: 20px;
        color: #1f9158;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .form-card h3 {
            font-size: 18px;
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .form-card h3 {
            font-size: 17px;
            margin-bottom: 15px;
        }
    }

    .form-control {
        font-size: 16px;
        padding: 10px 15px;
        border: 2px solid #e5e7eb;
        border-radius: 6px;
        transition: border-color 0.2s ease;
    }

    @media (max-width: 480px) {
        .form-control {
            font-size: 15px;
            padding: 12px 15px;
        }
    }

    .form-control:focus {
        border-color: #1f9158;
        box-shadow: 0 0 0 0.2rem rgba(31, 145, 88, 0.25);
    }

    .form-label {
        font-weight: 600;
        color: #275850;
        margin-bottom: 8px;
        font-size: 16px;
    }

    @media (max-width: 480px) {
        .form-label {
            font-size: 15px;
        }
    }

    .submit-btn {
        width: 100%;
        padding: 12px;
        background: linear-gradient(90deg, #0c3c2f, #116546);
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        margin-top: 20px;
        transition: background 0.3s;
        font-weight: 600;
    }

    @media (max-width: 480px) {
        .submit-btn {
            padding: 14px;
            font-size: 15px;
            margin-top: 15px;
        }
    }

    .submit-btn:hover {
        background: #07532e;
    }

    /* Form row adjustments for mobile */
    @media (max-width: 768px) {
        .row .col-md-6 {
            margin-bottom: 1rem;
        }
    }

    /* No announcements message */
    .no-announcements {
        text-align: center;
        color: #6b7280;
        padding: 40px;
        font-style: italic;
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        width: 100%;
    }

    @media (max-width: 768px) {
        .no-announcements {
            padding: 30px 20px;
        }
    }

    @media (max-width: 480px) {
        .no-announcements {
            padding: 25px 15px;
        }
        
        .no-announcements i {
            font-size: 2.5rem !important;
        }
    }

    /* Modal Responsive */
    @media (max-width: 576px) {
        .modal-dialog {
            margin: 20px 10px;
        }
        
        .modal-content {
            border-radius: 10px;
        }
        
        .modal-body {
            padding: 20px 15px;
        }
        
        .modal-header {
            padding: 15px 20px;
        }
    }

    /* Ensure buttons are touch-friendly on mobile */
    @media (max-width: 768px) {
        button, 
        .btn, 
        .update-btn, 
        .delete-btn, 
        .return-btn, 
        .submit-btn {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    }
    </style>
    </head>

    <body>

        <div class="navbar">
            <div class="logo">
                <img src="img/LSULogo.png" alt="LSU Logo">
                <h2>Announcements</h2>
            </div>

            <a href="#" id="returnButton" class="return-btn">
                <i class="fas fa-arrow-left me-1"></i> Return
            </a>
        </div>

        <div class="container">
            <!-- Recent Announcements - Horizontal Scroll -->
            <div class="announcements-section">
                
                <?php if ($announcementsResult && mysqli_num_rows($announcementsResult) > 0): ?>
                    <div class="horizontal-scroll-wrapper" id="announcementsScroll">
                        <?php while ($a = mysqli_fetch_assoc($announcementsResult)): ?>
                            <div class="dashboard-card">
                                <div>
                                    <div class="card-icon"><i class="fas fa-bullhorn"></i></div>
                                    <div class="card-value" title="<?= htmlspecialchars($a['title']) ?>">
                                        <?= htmlspecialchars($a['title']) ?>
                                    </div>
                                    <div class="card-label" title="<?= htmlspecialchars($a['content']) ?>">
                                        <?= htmlspecialchars($a['content']) ?>
                                    </div>
                                    <div class="card-date">
                                        <strong>Start:</strong> <?= date("M d, Y", strtotime($a['start_date'])) ?><br>
                                        <strong>End:</strong> <?= $a['end_date'] ? date("M d, Y", strtotime($a['end_date'])) : 'No end date' ?>
                                    </div>
                                </div>

                                <div class="card-buttons">
                                    <button 
                                        class="update-btn"
                                        data-id="<?= $a['AnnouncementID'] ?>"
                                        data-title="<?= htmlspecialchars($a['title']) ?>"
                                        data-content="<?= htmlspecialchars($a['content']) ?>"
                                        data-start="<?= $a['start_date'] ?>"
                                        data-end="<?= $a['end_date'] ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#updateModal">
                                        Update
                                    </button>

                                    <button 
                                        class="delete-btn"
                                        onclick="deleteAnnouncement(<?= $a['AnnouncementID'] ?>)">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-announcements">
                        <i class="fas fa-bullhorn fa-3x mb-3"></i><br>
                        No announcements yet. Create your first announcement below!
                    </div>
                <?php endif; ?>
            </div>

            <!-- Post Announcement Form -->
            <div class="form-card">
                <h3>Post New Announcement</h3>
                <form action="announcement_process.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Announcement Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea class="form-control" name="content" rows="4" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date">
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">Post Announcement</button>
                </form>
            </div>
        </div>

        <!-- Update Modal -->
        <div class="modal fade" id="updateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content form-card">
                    <div class="modal-header border-0 py-3" style="background-color: #087830;">
                        <h3 class="modal-title w-100 text-center text-white mb-0" style="font-size: 1.3rem;"><i class="fas fa-edit me-2"></i>Update Announcement </h3>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form id="updateForm" action="announcement_update_process.php" method="POST">
                        <input type="hidden" name="AnnouncementID" id="update_id">

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Announcement Title</label>
                                <input type="text" class="form-control" id="update_title" name="title" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Content</label>
                                <textarea class="form-control" id="update_content" name="content" rows="4" required></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="update_start" name="start_date" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="update_end" name="end_date">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="submit" class="btn btn-success">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            // Fill modal with data
            $('#updateModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                $('#update_id').val(button.data('id'));
                $('#update_title').val(button.data('title'));
                $('#update_content').val(button.data('content'));
                $('#update_start').val(button.data('start'));
                $('#update_end').val(button.data('end'));
            });

            // Delete announcement with confirmation
            function deleteAnnouncement(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'announcement_delete.php?id=' + id;
                    }
                });
            }

            // Show success/error messages
            <?php if (!empty($alert_type) && !empty($alert_message)): ?>
                Swal.fire({
                    icon: '<?php echo $alert_type; ?>',
                    title: '<?php echo $alert_type === 'success' ? 'Success!' : 'Error!'; ?>',
                    text: '<?php echo $alert_message; ?>',
                    confirmButtonColor: '#198754',
                    confirmButtonText: 'OK',
                    position: 'center',
                    timer: 3000,
                    timerProgressBar: true
                });
            <?php endif; ?>
        </script>

        <script>
            // Store the referrer URL when the page loads
            document.addEventListener('DOMContentLoaded', function() {
                const referrer = document.referrer;
                
                if (referrer && !referrer.includes('adminannouncement.php')) {
                    sessionStorage.setItem('previousPage', referrer);
                }
                
                const returnButton = document.getElementById('returnButton');
                const previousPage = sessionStorage.getItem('previousPage');
                
                if (previousPage) {
                    returnButton.href = previousPage;
                } else {
                    returnButton.href = 'adminconcerns.php';
                }
            });
        </script>

    </body>
    </html>