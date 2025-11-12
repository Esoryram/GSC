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

// list of assignable staff
$assigneeOptions = [
    'Mr. Noronio', 
    'Ms. Cruz', 
    'Mr. Ace', 
    'Facility Staff 1', 
    'Facility Staff 2',
    'Facility Staff 3',
];

// status dropdown options
$statusOptions = ['Pending', 'In Progress', 'Completed', 'Cancelled']; 

$concernID = intval($_GET['id'] ?? 0);

if ($concernID === 0) {
    header("Location: adminconcerns.php");
    exit();
}

// Get Concern Data
$query = "
    SELECT 
        c.ConcernID,
        c.Concern_Title,
        c.Description,
        c.Room,
        c.Problem_Type,
        c.Priority,
        c.Status,
        c.Concern_Date,
        c.Attachment,
        c.Assigned_to,
        a.Name AS ReportedBy,
        e.EFID,
        e.Type AS EquipmentType,
        e.Room AS EquipmentRoom
    FROM Concerns c
    LEFT JOIN Accounts a ON c.AccountID = a.AccountID
    LEFT JOIN equipmentfacilities e ON c.ConcernID = e.ConcernID
    WHERE c.ConcernID = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $concernID);
$stmt->execute();
$result = $stmt->get_result();
$concern = $result->fetch_assoc();
$stmt->close();

if (!$concern) {
    header("Location: adminconcerns.php");
    exit();
}

// --- HANDLE UPDATE SUBMIT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] ?? '';
    $newAssignee = $_POST['assigned_to'] ?? '';
    $newRoom = $_POST['room'] ?? '';
    $newPriority = $_POST['priority'] ?? '';
    $newProblemType = $_POST['problem_type'] ?? '';
    $newEquipmentType = $_POST['equipment_type'] ?? '';

    // Update Concerns table
    $updateQuery = "UPDATE Concerns 
                    SET Status = ?, Assigned_to = ?, Room = ?, Problem_Type = ?, Priority = ?
                    WHERE ConcernID = ?";
    $stmt1 = $conn->prepare($updateQuery);
    $stmt1->bind_param("sssssi", $newStatus, $newAssignee, $newRoom, $newProblemType, $newPriority, $concernID);
    $stmt1->execute();
    $stmt1->close();

    // Update or insert into equipmentfacilities
    $checkEF = $conn->prepare("SELECT EFID FROM equipmentfacilities WHERE ConcernID = ?");
    $checkEF->bind_param("i", $concernID);
    $checkEF->execute();
    $checkEF->store_result();

    if ($checkEF->num_rows > 0) {
        $updateEF = $conn->prepare("UPDATE equipmentfacilities SET Type = ?, Room = ? WHERE ConcernID = ?");
        $updateEF->bind_param("ssi", $newEquipmentType, $newRoom, $concernID);
        $updateEF->execute();
        $updateEF->close();
    } else {
        $insertEF = $conn->prepare("INSERT INTO equipmentfacilities (Type, Room, ConcernID) VALUES (?, ?, ?)");
        $insertEF->bind_param("ssi", $newEquipmentType, $newRoom, $concernID);
        $insertEF->execute();
        $insertEF->close();
    }
    $checkEF->close();
 
    echo "
    <script>
        alert('Concern #{$concernID} updated successfully!');
        window.location.href = 'adminconcerns.php';
    </script>
    ";
    exit();
}

$concernDate = date("l d M, Y", strtotime($concern['Concern_Date']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Concern #<?php echo $concernID; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
<style>
body {
    background: white;
    font-family: Arial, sans-serif;
    margin: 0;
}
.navbar {
    display: flex; 
    align-items: center;
    background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
    padding: 15px 30px; 
    color: white;
}
.logo {
    display: flex;
    align-items: center;
    margin-right: 25px; 
}
.logo img {
    height: 40px; 
    width: auto; 
}
.navbar .links {
    display: flex;
    gap: 20px;
    margin-right: auto;
}
.navbar .links a {
    color: white; text-decoration: none; font-weight: bold; padding: 6px 12px;
    border-radius: 5px; transition: 0.3s;
}
.navbar .links a.active { background: #4ba06f; }
.navbar .links a:hover { background: #107040; }

.dropdown { position: relative; display: flex; align-items: center; gap: 5px; }
.dropdown .username { font-weight: bold; font-size: 16px; padding: 6px 12px; }
.dropdown-menu { 
    display: none; position: absolute; top: 100%; right: 0; background: white; 
    min-width: 180px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); border-radius: 5px; 
    overflow: hidden; z-index: 10;
}
.dropdown:hover .dropdown-menu { display: block; } 
.dropdown-menu a { display: block; padding: 12px 16px; text-decoration: none; color: #333; font-size: 14px; }
.dropdown-menu a:hover { background: #f1f1f1; }

.page-container {
    max-width: 900px; 
    margin: 40px auto;
    padding: 0 30px; 
}

.top-info-bar-grid {
    display: flex;
    justify-content: space-between;
    align-items: flex-start; 
    margin-bottom: 25px;
    flex-wrap: wrap; 
    gap: 15px 30px; 
}

.left-info-group {
    display: flex;
    flex-direction: column;
    gap: 15px; 
}


.right-info-group {
    display: flex;
    flex-direction: column; 
    gap: 15px; 
}
.right-info-group .info-group {
    width: 100%; 
}

.info-group {
    display: flex;
    align-items: stretch; 
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    background-color: white; 
}

.info-label-green {
    background-color: #163a37; 
    color: white;
    font-weight: bold;
    padding: 8px 12px;
    white-space: nowrap;
    display: flex; 
    align-items: center;
}

.info-value-white {
    background-color: white; 
    padding: 8px 12px;
    color: #343a40;
    font-weight: bold;
    min-width: 120px; 
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    border: 1px solid #ced4da;
    border-left: none; 
    display: flex; 
    align-items: center; 
}

.status-select, .assignee-select {
    font-weight: bold;
    background-color: white; 
    color: #343a40;
    border: 1px solid #ced4da;
    padding: 8px 12px;
    border-radius: 0; 
    appearance: none; 
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20d%3D%22M9.293%2012.95l.707.707L15.657%208l-1.414-1.414L10%2010.828%205.757%206.586%204.343%208z%22%20fill%3D%22%23343a40%22%2F%3E%3C%2Fsvg%3E");
    background-repeat: no-repeat;
    background-position: right 0.7em center;
    background-size: 0.8em auto;
    cursor: pointer;
    border-left: none;
    flex-grow: 1; 
    min-width: unset; 
}

.info-group .id-value-white {
    min-width: 50px;
    text-align: center;
}

.date-header {
    background: linear-gradient(90deg, #163a37, #1f9158);
    color: white;
    padding: 15px 20px;
    text-align: center;
    font-size: 1.25rem;
    font-weight: bold;
    border-radius: 8px;
    margin-bottom: 30px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.details-card-wrapper {
    background-color: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); 
}

.detail-row {
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 25px;
    column-gap: 50px; 
    row-gap: 25px;
}
.detail-field { flex: 1 1 45%; min-width: 250px; }
.detail-field.full-width { flex-basis: 100%; }

.detail-field label {
    font-weight: bold;
    color: #333; 
    display: block;
    margin-bottom: 5px;
    font-size: 1.1rem;
}

.content-box {
    min-height: 40px;
    padding: 10px 15px;
    border: 1px solid #ced4da;
    border-radius: 8px;
    background-color: #f8f8f8; 
    color: #495057;
    word-wrap: break-word;
    white-space: pre-wrap;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.05); 
}

.file-attachment-box {
    display: flex; align-items: stretch; border: 1px solid #ced4da;
    border-radius: 8px; background-color: #f8f8f8; 
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
}

.file-label-inner {
    background-color: #f0f0f0;
    padding: 10px 15px; border-right: 1px solid #ced4da;
    font-weight: bold; color: #333; 
    border-top-left-radius: 7px; border-bottom-left-radius: 7px;
    white-space: nowrap; display: flex; align-items: center;
}

.file-link-container { padding: 10px 15px; flex-grow: 1; display: flex; align-items: center; }
.file-link-container a { color: #198754; text-decoration: none; font-weight: bold; }
.file-link-container a:hover { text-decoration: underline; }

.action-buttons {
    display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px;
}

.btn-update {
    background: #198754; color: white; font-weight: bold; padding: 10px 20px;
    border-radius: 8px; border: none; transition: background-color 0.3s, transform 0.2s;
}
.btn-update:hover { background: #146c43; transform: translateY(-1px); }

.btn-return {
    background-color: #6c757d; color: white; font-weight: bold; padding: 10px 20px;
    border-radius: 8px; border: none; transition: background-color 0.3s, transform 0.2s;
}
.btn-return:hover { background-color: #5a6268; transform: translateY(-1px); }

.success-alert {
    padding: 15px;
    margin-bottom: 20px;
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    border-radius: 8px;
    font-weight: bold;
}

@media (max-width: 768px) {
    .top-info-bar-grid {
        flex-direction: column;
        align-items: center;
    }
    .left-info-group, .right-info-group {
        width: 100%;
        justify-content: space-between;
    }
    .info-group { width: 48%; } /

    .right-info-group { 
        flex-direction: column; 
        width: 100%;
    }
    .right-info-group .info-group {
        width: 100%; 
    }
    .left-info-group .info-group { width: 100%; } 
    
    .id-value-white, .info-value-white { min-width: 50px; }
    .detail-row { flex-direction: column; gap: 15px; }
    .detail-field { min-width: 100%; flex-basis: 100%; }
    .action-buttons { flex-direction: column-reverse; }
    .action-buttons .btn { width: 100%; }
}
</style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="logo">
        <img src="img/LSULogo.png" alt="LSU Logo"> 
    </div>
    <div class="links">
        <a href="admindb.php" class="<?php echo ($activePage=="dashboard")?"active":""; ?>">Dashboard</a>
        <a href="adminconcerns.php" class="<?php echo ($activePage=="concerns")?"active":""; ?>">Concerns</a>
        <a href="adminreports.php" class="<?php echo ($activePage=="reports")?"active":""; ?>">Reports</a>
        <a href="adminfeedback.php" class="<?php echo ($activePage=="feedback")?"active":""; ?>">Feedback</a>
        <a href="adminannouncement.php" class="<?php echo ($activePage=="announcements")?"active":""; ?>">Announcements</a>
    </div>
    <div class="dropdown">
        <span class="username"><?php echo htmlspecialchars($name); ?></span>
        <span class="dropdown-toggle">
            <div class="dropdown-menu">
                <a href="#">Change Password</a>
                <a href="login.php">Logout</a>
            </div>
        </span>
    </div>
</div>

<div class="page-container">
    <form method="POST">
        <input type="hidden" name="concern_id" value="<?php echo $concernID; ?>">

        <div class="top-info-bar-grid">
            <div class="left-info-group">
                <div class="info-group">
                    <div class="info-label-green">Reported by:</div>
                    <div class="info-value-white"><?php echo htmlspecialchars($concern['ReportedBy'] ?: 'N/A'); ?></div>
                </div>
                <div class="info-group">
                    <div class="info-label-green">ID</div>
                    <div class="info-value-white id-value-white"><?php echo htmlspecialchars($concern['ConcernID']); ?></div>
                </div>
            </div>

            <div class="right-info-group">
                <div class="info-group">
                    <div class="info-label-green">Assigned:</div>
                    <select class="assignee-select" name="assigned_to">
                        <option value="">Assign</option>
                        <?php 
                        $currentAssignee = htmlspecialchars($concern['Assigned_to'] ?: '');
                        foreach ($assigneeOptions as $option): 
                            $selected = (strcasecmp($currentAssignee, $option) === 0) ? 'selected' : '';
                        ?>
                            <option value="<?php echo htmlspecialchars($option); ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($option); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="info-group">
                    <div class="info-label-green">Status:</div>
                    <select class="status-select" name="status">
                        <?php 
                        $currentStatus = htmlspecialchars($concern['Status'] ?: 'Pending');
                        foreach ($statusOptions as $option): 
                            $selected = (strcasecmp($currentStatus, $option) === 0) ? 'selected' : '';
                        ?>
                            <option value="<?php echo htmlspecialchars($option); ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($option); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="date-header">
            <?php echo $concernDate; ?>
        </div>

        <div class="details-card-wrapper">
            <div class="detail-field full-width">
                <label>Concern Title:</label>
                <div class="content-box"><?php echo htmlspecialchars($concern['Concern_Title'] ?: 'N/A'); ?></div>
            </div>

            <div class="detail-field full-width">
                <label>Description:</label>
                <div class="content-box"><?php echo nl2br(htmlspecialchars($concern['Description'] ?: 'N/A')); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-field">
                    <label>Room:</label>
                    <input type="text" class="form-control" name="room" value="<?php echo htmlspecialchars($concern['Room'] ?: ''); ?>" required>
                </div>

                <div class="detail-field">
                    <label>Equipment/Facility:</label>
                    <input type="text" class="form-control" name="equipment_type" value="<?php echo htmlspecialchars($concern['EquipmentType'] ?: ''); ?>" required>
                </div>

                <div class="detail-field">
                    <label>Problem Type:</label>
                    <input type="text" class="form-control" name="problem_type" value="<?php echo htmlspecialchars($concern['Problem_Type'] ?: ''); ?>" required>
                </div>

                <div class="detail-field">
                    <label>Priority:</label>
                    <select class="form-control" name="priority" required>
                        <?php 
                        $priorities = ['Low', 'Medium', 'High', 'Urgent'];
                        $currentPriority = htmlspecialchars($concern['Priority'] ?: '');
                        foreach ($priorities as $p): 
                            $selected = (strcasecmp($p, $currentPriority) === 0) ? 'selected' : '';
                            echo "<option value='$p' $selected>$p</option>";
                        endforeach;
                        ?>
                    </select>
                </div>
            </div>

            <div class="detail-field full-width">
                <label>Attachment (Photo/Video):</label>
                <div class="file-attachment-box">
                    <div class="file-label-inner">File:</div>
                    <div class="file-link-container">
                        <?php if (!empty($concern['Attachment'])): ?>
                            <a href="<?php echo htmlspecialchars($concern['Attachment']); ?>" target="_blank">Click to View Attachment</a>
                        <?php else: ?>
                            <span class="text-muted">No file attached</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <a href="adminconcerns.php" class="btn btn-secondary">Return</a>
                <button type="submit" class="btn btn-success">Update</button>
            </div>
        </div>
    </form>
</div>
</body>
</html>
