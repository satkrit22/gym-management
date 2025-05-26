<?php
include('../dbConnection.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $title = $_POST['title'];
        $start = $_POST['start'];
        $end = $_POST['end'];
        $trainer = $_POST['trainer'] ?? '';
        $capacity = $_POST['capacity'] ?? 20;
        $color = $_POST['color'] ?? '#17a2b8';
        $description = $_POST['description'] ?? '';

        $sql = "INSERT INTO tbl_events (title, start, end, trainer, capacity, color, description) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssiss", $title, $start, $end, $trainer, $capacity, $color, $description);

        if ($stmt->execute()) {
            echo json_encode(array('status' => 'success', 'message' => 'Event added successfully', 'id' => $conn->insert_id));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Failed to add event'));
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(array('status' => 'error', 'message' => 'Database error: ' . $e->getMessage()));
    }
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid request method'));
}

$conn->close();
?>