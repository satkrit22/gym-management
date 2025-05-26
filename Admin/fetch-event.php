<?php
require_once "dbConnection.php";

// Only fetch events from today onwards and up to 1 month ahead
$today = date('Y-m-d');
$oneMonthFromNow = date('Y-m-d', strtotime('+1 month'));

$sql = "SELECT id, title, start, end, trainer, capacity, color, description 
        FROM tbl_events 
        WHERE DATE(start) >= ? AND DATE(start) <= ?
        ORDER BY start ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $today, $oneMonthFromNow);
$stmt->execute();
$result = $stmt->get_result();

$eventArray = array();
while ($row = $result->fetch_assoc()) {
    // Add additional properties for FullCalendar
    $event = array(
        'id' => $row['id'],
        'title' => $row['title'],
        'start' => $row['start'],
        'end' => $row['end'],
        'trainer' => $row['trainer'],
        'capacity' => $row['capacity'],
        'color' => $row['color'] ?: '#17a2b8',
        'description' => $row['description'],
        'textColor' => '#ffffff'
    );
    
    // Add trainer info to title if available
    if (!empty($row['trainer'])) {
        $event['title'] = $row['title'] . ' - ' . $row['trainer'];
    }
    
    array_push($eventArray, $event);
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($eventArray);
?>