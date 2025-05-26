<?php
include('../dbConnection.php');
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['is_login'])) {
    echo json_encode(array('error' => 'Not logged in'));
    exit;
}

$mEmail = $_SESSION['mEmail'];

try {
    $sql = "SELECT e.id, e.title, e.start, e.end, e.trainer, e.capacity, e.color, e.description,
                   CASE WHEN b.id IS NOT NULL THEN 1 ELSE 0 END as is_booked
            FROM tbl_events e 
            LEFT JOIN tbl_bookings b ON e.id = b.class_id AND b.member_email = ?
            WHERE DATE(e.start) >= CURDATE()
            ORDER BY e.start";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $mEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    $events = array();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $color = $row['color'] ?? '#28a745';
            
            // Change color based on booking status
            if ($row['is_booked']) {
                $color = '#17a2b8'; // Blue for booked classes
            }
            
            // Yellow for today's classes
            if (date('Y-m-d', strtotime($row['start'])) == date('Y-m-d')) {
                $color = '#ffc107';
            }
            
            $events[] = array(
                'id' => $row['id'],
                'title' => $row['title'],
                'start' => $row['start'],
                'end' => $row['end'],
                'trainer' => $row['trainer'],
                'capacity' => $row['capacity'],
                'color' => $color,
                'description' => $row['description'],
                'is_booked' => (bool)$row['is_booked']
            );
        }
    }

    echo json_encode($events);
} catch (Exception $e) {
    echo json_encode(array('error' => 'Database error: ' . $e->getMessage()));
}

$conn->close();
?>