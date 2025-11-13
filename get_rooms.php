<?php
include("config.php");

if (isset($_GET['building'])) {
    $building = $_GET['building'];
    
    $sql = "SELECT roomname FROM rooms WHERE building_name = ? ORDER BY roomname";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $building);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($rooms);
} else {
    echo json_encode([]);
}
?>