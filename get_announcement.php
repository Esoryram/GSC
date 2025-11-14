<?php
include("config.php");

// Only show active announcements within the start and end date
$query = "
    SELECT AnnouncementID, title, content, created_at, start_date, end_date 
    FROM announcements 
    WHERE is_active = 1 
      AND CURDATE() BETWEEN start_date AND end_date
    ORDER BY created_at DESC
";

$result = mysqli_query($conn, $query);
$announcements = [];

while ($row = mysqli_fetch_assoc($result)) {
    $announcements[] = [
        "id" => $row['AnnouncementID'],
        "title" => htmlspecialchars($row['title']),
        "details" => nl2br(htmlspecialchars($row['content'])),
        "date" => date("F d, Y", strtotime($row['created_at']))
    ];
}

header('Content-Type: application/json');
echo json_encode($announcements);
?>