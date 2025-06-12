<?php
include('../dbConnection.php');
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if admin is logged in
if (!isset($_SESSION['is_adminlogin'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized - Admin not logged in']);
    exit;
}

// Check if schedule_id is provided
if (!isset($_GET['schedule_id']) || empty($_GET['schedule_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Schedule ID is required']);
    exit;
}

$schedule_id = intval($_GET['schedule_id']);

try {
    // Check database connection
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Fetch schedule data from tbl_events
    $sql = "SELECT id, title, start, end, trainer, capacity, color, description FROM tbl_events WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $schedule_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Schedule not found with ID: ' . $schedule_id]);
        exit;
    }
    
    $data = $result->fetch_assoc();
    
    // Format the datetime fields
    $startDateTime = new DateTime($data['start']);
    $endDateTime = new DateTime($data['end']);
    
    $editData = array(
        'id' => $data['id'],
        'title' => $data['title'],
        'date' => $startDateTime->format('Y-m-d'),
        'start_time' => $startDateTime->format('H:i'),
        'end_time' => $endDateTime->format('H:i'),
        'trainer' => $data['trainer'] ?? '',
        'capacity' => $data['capacity'] ?? 20,
        'color' => $data['color'] ?? '#17a2b8',
        'description' => $data['description'] ?? ''
    );
    
    // Get booking count for this schedule
    $bookingCountSql = "SELECT COUNT(*) as count FROM submitbookingt_tb WHERE booking_type = ? AND member_date = ?";
    $bookingCountStmt = $conn->prepare($bookingCountSql);
    
    if ($bookingCountStmt) {
        $scheduleDate = $startDateTime->format('Y-m-d');
        $bookingCountStmt->bind_param("ss", $data['title'], $scheduleDate);
        $bookingCountStmt->execute();
        $bookingResult = $bookingCountStmt->get_result();
        $bookingCount = $bookingResult->fetch_assoc()['count'];
        $editData['booking_count'] = $bookingCount;
    } else {
        $editData['booking_count'] = 0;
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($editData);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
?>