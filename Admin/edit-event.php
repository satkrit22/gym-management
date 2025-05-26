<?php
include('../dbConnection.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $start = $_POST['start'];
        $end = $_POST['end'];
        $trainer = $_POST['trainer'] ?? '';
        $capacity = $_POST['capacity'] ?? 20;
        $color = $_POST['color'] ?? '#17a2b8';
        $description = $_POST['description'] ?? '';

        $sql = "UPDATE tbl_events SET title=?, start=?, end=?, trainer=?, capacity=?, color=?, description=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssissi", $title, $start, $end, $trainer, $capacity, $color, $description, $id);

        if ($stmt->execute()) {
            echo json_encode(array('status' => 'success', 'message' => 'Event updated successfully'));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Failed to update event'));
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