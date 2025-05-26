<?php    
define('TITLE', 'Update Event');
define('PAGE', 'Event');
include('includes/header.php'); 
include('../dbConnection.php');
session_start();

if (!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

$aEmail = $_SESSION['aEmail'];

// Update event
if (isset($_POST['eventupdate'])) {
    if (empty($_POST['id']) || empty($_POST['title']) || empty($_POST['start']) || empty($_POST['end'])) {
        $msg = '<div class="alert alert-warning col-sm-6 ml-5 mt-2" role="alert">Fill All Fields</div>';
    } else {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $start = $_POST['start'];
        $end = $_POST['end'];
        $trainer = $_POST['trainer'] ?? '';
        $capacity = $_POST['capacity'] ?? 20;
        $description = $_POST['description'] ?? '';
        
        // Validate date restrictions
        $eventDate = date('Y-m-d', strtotime($start));
        $today = date('Y-m-d');
        $oneMonthFromNow = date('Y-m-d', strtotime('+1 month'));
        
        if ($eventDate < $today) {
            $msg = '<div class="alert alert-danger col-sm-6 ml-5 mt-2" role="alert">Cannot schedule events for past dates</div>';
        } elseif ($eventDate > $oneMonthFromNow) {
            $msg = '<div class="alert alert-danger col-sm-6 ml-5 mt-2" role="alert">Cannot schedule events more than 1 month ahead</div>';
        } else {
            $sql = "UPDATE tbl_events SET 
                    title = ?, 
                    start = ?, 
                    end = ?, 
                    trainer = ?, 
                    capacity = ?, 
                    description = ?, 
                    updated_at = NOW() 
                    WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssisi", $title, $start, $end, $trainer, $capacity, $description, $id);
            
            if ($stmt->execute()) {
                $msg = '<div class="alert alert-success col-sm-6 ml-5 mt-2" role="alert">Updated Successfully</div>';
            } else {
                $msg = '<div class="alert alert-danger col-sm-6 ml-5 mt-2" role="alert">Unable to Update</div>';
            }
        }
    }
}
?>

<div class="col-sm-8 mt-5 mx-3 jumbotron">
    <h3 class="text-center">Update Schedule</h3>
    
    <?php
    if (isset($_REQUEST['view'])) {
        $sql = "SELECT * FROM tbl_events WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_REQUEST['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    }
    ?>
    
    <form action="" method="POST">
        <div class="form-group">
            <label for="id">Event ID</label>
            <input type="text" class="form-control" id="id" name="id" 
                   value="<?php echo isset($row['id']) ? $row['id'] : ''; ?>" readonly>
        </div>
        
        <div class="form-group">
            <label for="title">Class Type</label>
            <select class="form-control" id="title" name="title" required>
                <option value="">Select Class Type</option>
                <option value="Yoga Class" <?php echo (isset($row['title']) && $row['title'] == 'Yoga Class') ? 'selected' : ''; ?>>Yoga Class</option>
                <option value="Zumba Class" <?php echo (isset($row['title']) && $row['title'] == 'Zumba Class') ? 'selected' : ''; ?>>Zumba Class</option>
                <option value="Cardio Class" <?php echo (isset($row['title']) && $row['title'] == 'Cardio Class') ? 'selected' : ''; ?>>Cardio Class</option>
                <option value="Weight Lifting" <?php echo (isset($row['title']) && $row['title'] == 'Weight Lifting') ? 'selected' : ''; ?>>Weight Lifting</option>
                <option value="Endurance Training" <?php echo (isset($row['title']) && $row['title'] == 'Endurance Training') ? 'selected' : ''; ?>>Endurance Training</option>
                <option value="Personal Training" <?php echo (isset($row['title']) && $row['title'] == 'Personal Training') ? 'selected' : ''; ?>>Personal Training</option>
                <option value="Group Fitness" <?php echo (isset($row['title']) && $row['title'] == 'Group Fitness') ? 'selected' : ''; ?>>Group Fitness</option>
            </select>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="start">Start Date & Time</label>
                    <input type="datetime-local" class="form-control" id="start" name="start" 
                           value="<?php echo isset($row['start']) ? date('Y-m-d\TH:i', strtotime($row['start'])) : ''; ?>"
                           min="<?php echo date('Y-m-d\TH:i'); ?>"
                           max="<?php echo date('Y-m-d\TH:i', strtotime('+1 month')); ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="end">End Date & Time</label>
                    <input type="datetime-local" class="form-control" id="end" name="end" 
                           value="<?php echo isset($row['end']) ? date('Y-m-d\TH:i', strtotime($row['end'])) : ''; ?>"
                           min="<?php echo date('Y-m-d\TH:i'); ?>"
                           max="<?php echo date('Y-m-d\TH:i', strtotime('+1 month')); ?>" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="trainer">Trainer</label>
                    <input type="text" class="form-control" id="trainer" name="trainer" 
                           value="<?php echo isset($row['trainer']) ? htmlspecialchars($row['trainer']) : ''; ?>" 
                           placeholder="Trainer Name">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="capacity">Capacity</label>
                    <input type="number" class="form-control" id="capacity" name="capacity" 
                           value="<?php echo isset($row['capacity']) ? $row['capacity'] : '20'; ?>" 
                           min="1" max="100">
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" 
                      placeholder="Additional details about the class"><?php echo isset($row['description']) ? htmlspecialchars($row['description']) : ''; ?></textarea>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-success" id="eventupdate" name="eventupdate">
                <i class="fas fa-save"></i> Update Event
            </button>
            <a href="event.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Calendar
            </a>
            <a href="view_schedule.php" class="btn btn-info">
                <i class="fas fa-list"></i> List View
            </a>
        </div>
        
        <?php if (isset($msg)) { echo $msg; } ?>
    </form>
</div>

<script>
// Auto-update end time when start time changes
document.getElementById('start').addEventListener('change', function() {
    var startTime = new Date(this.value);
    var endTime = new Date(startTime.getTime() + (60 * 60 * 1000)); // Add 1 hour
    
    var endInput = document.getElementById('end');
    endInput.value = endTime.toISOString().slice(0, 16);
});

// Validate that end time is after start time
document.getElementById('end').addEventListener('change', function() {
    var startTime = new Date(document.getElementById('start').value);
    var endTime = new Date(this.value);
    
    if (endTime <= startTime) {
        alert('End time must be after start time');
        var newEndTime = new Date(startTime.getTime() + (60 * 60 * 1000));
        this.value = newEndTime.toISOString().slice(0, 16);
    }
});
</script>

<?php include('includes/footer.php'); ?>