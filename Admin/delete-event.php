<?php
include('../dbConnection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $id = $_POST['id'];

        $sql = "DELETE FROM tbl_events WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo $stmt->affected_rows; // Return number of affected rows
        } else {
            echo 0;
        }

        $stmt->close();
    } catch (Exception $e) {
        echo 0;
    }
} else {
    echo 0;
}

$conn->close();
?>