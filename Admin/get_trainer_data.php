<?php
include('../dbConnection.php');

if (isset($_GET['trainer_id'])) {
    $trainer_id = $_GET['trainer_id'];
    
    // Fetch trainer data
    $sql = "SELECT * FROM trainers_tb WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $trainer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $trainerData = $result->fetch_assoc();
        
        // Fetch assigned schedules count
        $scheduleCountSql = "SELECT COUNT(*) as count FROM tbl_events WHERE trainer = ?";
        $scheduleCountStmt = $conn->prepare($scheduleCountSql);
        $scheduleCountStmt->bind_param("s", $trainerData['trainer_name']);
        $scheduleCountStmt->execute();
        $scheduleCountResult = $scheduleCountStmt->get_result();
        $assignedSchedules = $scheduleCountResult->fetch_assoc()['count'];
        
        $response = [
            'id' => $trainerData['id'],
            'trainer_name' => $trainerData['trainer_name'],
            'email' => $trainerData['email'],
            'phone' => $trainerData['phone'],
            'specialization' => $trainerData['specialization'],
            'experience_years' => $trainerData['experience_years'],
            'hire_date' => $trainerData['hire_date'],
            'status' => $trainerData['status'],
            'assigned_schedules' => $assignedSchedules
        ];
        
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Trainer not found']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
<script>
    $.ajax({
    url: 'get_trainer_data.php',
    type: 'GET',
    data: {
        trainer_id: trainerId
    },
    dataType: 'json',
    timeout: 10000, // 10 second timeout
    success: function(data) {
        console.log('AJAX Success - Data received:', data);
        
        if (data.error) {
            $('#error_text').text(data.error);
            $('#error_message').show();
            $('#loading_indicator').hide();
            return;
        }
        
        // Populate form fields with database values
        $('#edit_trainer_id').val(data.id);
        $('#edit_trainer_name').val(data.trainer_name);
        $('#edit_email').val(data.email);
        $('#edit_phone').val(data.phone);
        $('#edit_specialization').val(data.specialization);
        $('#edit_experience').val(data.experience_years);
        $('#edit_hire_date').val(data.hire_date);
        $('#edit_status').val(data.status);
        
        // Show current values in helper text
        $('#current_trainer_name').text(data.trainer_name);
        $('#current_email').text(data.email || 'Not provided');
        $('#current_phone').text(data.phone || 'Not provided');
        $('#current_specialization').text(data.specialization || 'Not specified');
        $('#current_experience').text(data.experience_years + ' years');
        $('#current_hire_date').text(data.hire_date || 'Not set');
        $('#current_status').text(data.status.charAt(0).toUpperCase() + data.status.slice(1));
        
        // Show assignment warning if trainer has assigned schedules
        if (data.assigned_schedules > 0) {
            $('#assigned_count_display').text(data.assigned_schedules);
            $('#assignment_warning').show();
        } else {
            $('#assignment_warning').hide();
        }
        
        // Hide loading and show form
        $('#loading_indicator').hide();
        $('#edit_form_content').show();
        $('#update_trainer_btn').prop('disabled', false);
    },
    error: function(xhr, status, error) {
        console.error('AJAX Error:', {
            status: status,
            error: error,
            responseText: xhr.responseText,
            statusCode: xhr.status
        });
        
        var errorMsg = 'Error loading trainer data: ';
        if (xhr.status === 404) {
            errorMsg += 'Trainer not found';
        } else if (xhr.status === 401) {
            errorMsg += 'Unauthorized access';
        } else if (status === 'timeout') {
            errorMsg += 'Request timed out';
        } else {
            errorMsg += error || 'Unknown error';
        }
        
        $('#error_text').text(errorMsg);
        $('#error_message').show();
        $('#loading_indicator').hide();
    }
});
</script>