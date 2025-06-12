<?php
define('TITLE', 'View Schedule');
define('PAGE', 'schedule');
include('includes/header.php');
include('../dbConnection.php');
session_start();

if (!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

$aEmail = $_SESSION['aEmail'];

// Get trainers from database
function getTrainers($conn) {
    $trainers = array();
    $sql = "SELECT DISTINCT trainer_name FROM trainers_tb WHERE status = 'active' ORDER BY trainer_name ASC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $trainers[] = $row['trainer_name'];
        }
    }
    return $trainers;
}

$trainers = getTrainers($conn);

// Fetch schedule data if edit button is clicked
$edit_schedule_id = isset($_GET['edit_schedule_id']) ? $_GET['edit_schedule_id'] : null;
$schedule_data = null;
$show_edit_modal = false;

if ($edit_schedule_id) {
    $sql = "SELECT * FROM tbl_events WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edit_schedule_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $schedule_data = $result->fetch_assoc();
        $show_edit_modal = true;
        
        // Format datetime fields for form inputs
        $startDateTime = new DateTime($schedule_data['start']);
        $endDateTime = new DateTime($schedule_data['end']);
        
        $schedule_data['formatted_date'] = $startDateTime->format('Y-m-d');
        $schedule_data['formatted_start_time'] = $startDateTime->format('H:i');
        $schedule_data['formatted_end_time'] = $endDateTime->format('H:i');
        
        // Get booking count for warning
        $bookingCountSql = "SELECT COUNT(*) as count FROM submitbookingt_tb WHERE booking_type = ? AND member_date = ?";
        $bookingCountStmt = $conn->prepare($bookingCountSql);
        $scheduleDate = $startDateTime->format('Y-m-d');
        $bookingCountStmt->bind_param("ss", $schedule_data['title'], $scheduleDate);
        $bookingCountStmt->execute();
        $bookingResult = $bookingCountStmt->get_result();
        $schedule_data['booking_count'] = $bookingResult->fetch_assoc()['count'];
    } else {
        $error_msg = "Schedule not found!";
    }
}

// Handle schedule actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $schedule_id = $_POST['schedule_id'] ?? '';
    
    switch ($action) {
        case 'add':
            $title = $_POST['title'];
            $start_date = $_POST['start_date'];
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
            $trainer = $_POST['trainer'] ?? '';
            $capacity = $_POST['capacity'] ?? 20;
            $color = $_POST['color'] ?? '#17a2b8';
            $description = $_POST['description'] ?? '';
            
            $start_datetime = $start_date . ' ' . $start_time;
            $end_datetime = $start_date . ' ' . $end_time;
            
            $sql = "INSERT INTO tbl_events (title, start, end, trainer, capacity, color, description) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssiss", $title, $start_datetime, $end_datetime, $trainer, $capacity, $color, $description);
            
            if ($stmt->execute()) {
                $success_msg = "Schedule added successfully!";
            } else {
                $error_msg = "Error adding schedule: " . $conn->error;
            }
            break;
            
        case 'edit':
            // First get the current schedule data to check what's being changed
            $getCurrentSql = "SELECT * FROM tbl_events WHERE id = ?";
            $getCurrentStmt = $conn->prepare($getCurrentSql);
            $getCurrentStmt->bind_param("i", $schedule_id);
            $getCurrentStmt->execute();
            $currentData = $getCurrentStmt->get_result()->fetch_assoc();
            
            if (!$currentData) {
                $error_msg = "Schedule not found.";
                break;
            }
            
            $title = $_POST['title'];
            $start_date = $_POST['start_date'];
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
            $trainer = $_POST['trainer'] ?? '';
            $capacity = $_POST['capacity'] ?? 20;
            $color = $_POST['color'] ?? '#17a2b8';
            $description = $_POST['description'] ?? '';
            
            $start_datetime = $start_date . ' ' . $start_time;
            $end_datetime = $start_date . ' ' . $end_time;
            
            // Check if there are any bookings for this schedule
            $checkBookingsSql = "SELECT COUNT(*) as booking_count FROM submitbookingt_tb WHERE booking_type = ? AND member_date = ?";
            $checkBookingsStmt = $conn->prepare($checkBookingsSql);
            $currentDate = date('Y-m-d', strtotime($currentData['start']));
            $checkBookingsStmt->bind_param("ss", $currentData['title'], $currentDate);
            $checkBookingsStmt->execute();
            $bookingResult = $checkBookingsStmt->get_result();
            $bookingCount = $bookingResult->fetch_assoc()['booking_count'];
            
            // If there are bookings and major changes are being made, show warning
            if ($bookingCount > 0) {
                $majorChanges = false;
                if ($title != $currentData['title'] || 
                    $start_date != date('Y-m-d', strtotime($currentData['start'])) ||
                    $start_time != date('H:i', strtotime($currentData['start']))) {
                    $majorChanges = true;
                }
                
                if ($majorChanges) {
                    $warning_msg = "Warning: This schedule has " . $bookingCount . " booking(s). Changes have been saved, but members should be notified.";
                }
            }
            
            $sql = "UPDATE tbl_events SET title=?, start=?, end=?, trainer=?, capacity=?, color=?, description=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssissi", $title, $start_datetime, $end_datetime, $trainer, $capacity, $color, $description, $schedule_id);
            
            if ($stmt->execute()) {
                $success_msg = "Schedule updated successfully!";
                if (isset($warning_msg)) {
                    $success_msg .= " " . $warning_msg;
                }
                // Redirect to clear the GET parameter after successful update
                echo "<script> location.href='view_schedule.php'; </script>";
                exit;
            } else {
                $error_msg = "Error updating schedule: " . $conn->error;
            }
            break;
            
        case 'delete':
            // Check if there are bookings before deleting
            $checkDeleteSql = "SELECT title, start FROM tbl_events WHERE id = ?";
            $checkDeleteStmt = $conn->prepare($checkDeleteSql);
            $checkDeleteStmt->bind_param("i", $schedule_id);
            $checkDeleteStmt->execute();
            $scheduleData = $checkDeleteStmt->get_result()->fetch_assoc();
            
            if ($scheduleData) {
                $checkBookingsSql = "SELECT COUNT(*) as booking_count FROM submitbookingt_tb WHERE booking_type = ? AND member_date = ?";
                $checkBookingsStmt = $conn->prepare($checkBookingsSql);
                $scheduleDate = date('Y-m-d', strtotime($scheduleData['start']));
                $checkBookingsStmt->bind_param("ss", $scheduleData['title'], $scheduleDate);
                $checkBookingsStmt->execute();
                $bookingResult = $checkBookingsStmt->get_result();
                $bookingCount = $bookingResult->fetch_assoc()['booking_count'];
                
                if ($bookingCount > 0) {
                    $error_msg = "Cannot delete schedule. There are " . $bookingCount . " booking(s) for this class. Please cancel all bookings first.";
                } else {
                    $sql = "DELETE FROM tbl_events WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $schedule_id);
                    
                    if ($stmt->execute()) {
                        $success_msg = "Schedule deleted successfully!";
                    } else {
                        $error_msg = "Error deleting schedule: " . $conn->error;
                    }
                }
            }
            break;
    }
}

// Get filter parameters
$filter_date = $_GET['filter_date'] ?? '';
$filter_class = $_GET['filter_class'] ?? '';
?>

<div class="col-sm-9 col-md-10 mt-5">
    <div class="text-center">
        <p class="bg-dark text-white p-2 mb-4">GYM SCHEDULE MANAGEMENT</p>
    </div>

    <?php if (isset($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Navigation Buttons -->
    <div class="mb-3">
        <button class="btn btn-primary" data-toggle="modal" data-target="#addScheduleModal">
            <i class="fas fa-plus"></i> Add New Schedule
        </button>
        <a href="dashboard.php" class="btn btn-secondary ml-2">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="manage_trainer.php" class="btn btn-info ml-2">
            <i class="fas fa-users"></i> Manage Trainers
        </a>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Schedule</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-4">
                    <label for="filter_date">Filter by Date:</label>
                    <input type="date" class="form-control" id="filter_date" name="filter_date" 
                           value="<?php echo htmlspecialchars($filter_date); ?>">
                </div>
                <div class="col-md-4">
                    <label for="filter_class">Filter by Class:</label>
                    <select class="form-control" id="filter_class" name="filter_class">
                        <option value="">All Classes</option>
                        <option value="Yoga Class" <?php echo ($filter_class == 'Yoga Class') ? 'selected' : ''; ?>>Yoga Class</option>
                        <option value="Zumba Class" <?php echo ($filter_class == 'Zumba Class') ? 'selected' : ''; ?>>Zumba Class</option>
                        <option value="Cardio Class" <?php echo ($filter_class == 'Cardio Class') ? 'selected' : ''; ?>>Cardio Class</option>
                        <option value="Weight Lifting" <?php echo ($filter_class == 'Weight Lifting') ? 'selected' : ''; ?>>Weight Lifting</option>
                        <option value="Endurance Training" <?php echo ($filter_class == 'Endurance Training') ? 'selected' : ''; ?>>Endurance Training</option>
                        <option value="Personal Training" <?php echo ($filter_class == 'Personal Training') ? 'selected' : ''; ?>>Personal Training</option>
                        <option value="Group Fitness" <?php echo ($filter_class == 'Group Fitness') ? 'selected' : ''; ?>>Group Fitness</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="view_schedule.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Debug: Show current database connection status -->
    <div class="alert alert-info"> 
        Total Events: <?php echo $conn->query("SELECT COUNT(*) as count FROM tbl_events")->fetch_assoc()['count']; ?> | 
        Total Trainers: <?php echo $conn->query("SELECT COUNT(*) as count FROM trainers_tb")->fetch_assoc()['count']; ?>
        <?php if ($show_edit_modal): ?>
            | <strong>Edit Mode:</strong> Loading schedule ID <?php echo $edit_schedule_id; ?>
        <?php endif; ?>
    </div>

    <!-- Schedule Table -->
    <?php
    $sql = "SELECT *, DATE(start) as schedule_date, TIME(start) as start_time, TIME(end) as end_time FROM tbl_events WHERE 1=1";
    $params = array();
    $types = "";

    if (!empty($filter_date)) {
        $sql .= " AND DATE(start) = ?";
        $params[] = $filter_date;
        $types .= "s";
    }

    if (!empty($filter_class)) {
        $sql .= " AND title = ?";
        $params[] = $filter_class;
        $types .= "s";
    }

    $sql .= " ORDER BY start ASC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-table"></i> Schedule List</h5>
        </div>
        <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Class Name</th>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Trainer</th>
                    <th>Capacity</th>
                    <th>Bookings</th>
                    <th>Color</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>';

        while ($row = $result->fetch_assoc()) {
            $startDateTime = new DateTime($row["start"]);
            $endDateTime = new DateTime($row["end"]);
            $isToday = $startDateTime->format('Y-m-d') == date('Y-m-d');
            $isPast = $startDateTime < new DateTime();
            
            // Get booking count for this schedule
            $bookingCountSql = "SELECT COUNT(*) as count FROM submitbookingt_tb WHERE booking_type = ? AND member_date = ?";
            $bookingCountStmt = $conn->prepare($bookingCountSql);
            $scheduleDate = $startDateTime->format('Y-m-d');
            $bookingCountStmt->bind_param("ss", $row["title"], $scheduleDate);
            $bookingCountStmt->execute();
            $bookingCount = $bookingCountStmt->get_result()->fetch_assoc()['count'];
            
            $rowClass = '';
            if ($isToday) $rowClass = 'table-warning';
            elseif ($isPast) $rowClass = 'table-secondary';
            
            // Highlight the row being edited
            if ($edit_schedule_id && $row["id"] == $edit_schedule_id) {
                $rowClass .= ' table-info border-primary';
            }
            
            echo '<tr class="' . $rowClass . '">';
            echo '<td>' . $row["id"] . '</td>';
            echo '<td><strong>' . htmlspecialchars($row["title"]) . '</strong>';
            if ($isToday) echo ' <span class="badge badge-warning">Today</span>';
            if ($isPast) echo ' <span class="badge badge-secondary">Past</span>';
            if ($edit_schedule_id && $row["id"] == $edit_schedule_id) echo ' <span class="badge badge-info">Editing</span>';
            echo '</td>';
            echo '<td>' . $startDateTime->format('Y-m-d') . '</td>';
            echo '<td>' . $startDateTime->format('h:i A') . '</td>';
            echo '<td>' . $endDateTime->format('h:i A') . '</td>';
            echo '<td>' . htmlspecialchars($row["trainer"] ?? 'Not Assigned') . '</td>';
            echo '<td>' . ($row["capacity"] ?? 20) . '</td>';
            echo '<td>';
            if ($bookingCount > 0) {
                echo '<span class="badge badge-info">' . $bookingCount . ' booked</span>';
            } else {
                echo '<span class="badge badge-light">No bookings</span>';
            }
            echo '</td>';
            echo '<td><span class="badge" style="background-color: ' . ($row["color"] ?? '#17a2b8') . '; color: white;">●</span></td>';
            echo '<td>
                <a href="view_schedule.php?edit_schedule_id=' . $row["id"] . '" class="btn btn-sm btn-warning" title="Edit Schedule">
                    <i class="fas fa-edit"></i>
                </a>';
            
            if ($bookingCount > 0) {
                echo '<button class="btn btn-sm btn-secondary ml-1" disabled title="Cannot delete - has bookings">
                        <i class="fas fa-trash"></i>
                      </button>';
            } else {
                echo '<form method="POST" class="d-inline ml-1" onsubmit="return confirm(\'Are you sure you want to delete this schedule?\')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="schedule_id" value="' . $row["id"] . '">
                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Schedule">
                            <i class="fas fa-trash"></i>
                        </button>
                      </form>';
            }
            
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table></div></div></div>';
    } else {
        echo '<div class="alert alert-info text-center">
                <h4><i class="fas fa-info-circle"></i> No Schedules Found</h4>
                <p>No schedules match your filter criteria. <a href="#" data-toggle="modal" data-target="#addScheduleModal">Add a new schedule</a> to get started.</p>
              </div>';
    }
    ?>

    <!-- Schedule Statistics -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Schedule Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <?php
                        $todaySchedules = $conn->query("SELECT COUNT(*) as count FROM tbl_events WHERE DATE(start) = CURDATE()")->fetch_assoc()['count'];
                        $upcomingSchedules = $conn->query("SELECT COUNT(*) as count FROM tbl_events WHERE start > NOW()")->fetch_assoc()['count'];
                        $totalSchedules = $conn->query("SELECT COUNT(*) as count FROM tbl_events")->fetch_assoc()['count'];
                        $pastSchedules = $conn->query("SELECT COUNT(*) as count FROM tbl_events WHERE start < NOW()")->fetch_assoc()['count'];
                        ?>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h3><?php echo $todaySchedules; ?></h3>
                                    <p class="mb-0">Today's Classes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h3><?php echo $upcomingSchedules; ?></h3>
                                    <p class="mb-0">Upcoming Classes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h3><?php echo $totalSchedules; ?></h3>
                                    <p class="mb-0">Total Classes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-secondary text-white">
                                <div class="card-body">
                                    <h3><?php echo $pastSchedules; ?></h3>
                                    <p class="mb-0">Past Classes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Add New Schedule</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-dumbbell"></i> Class Name</label>
                                <select class="form-control" name="title" required>
                                    <option value="">Select Class</option>
                                    <option value="Yoga Class">Yoga Class</option>
                                    <option value="Zumba Class">Zumba Class</option>
                                    <option value="Cardio Class">Cardio Class</option>
                                    <option value="Weight Lifting">Weight Lifting</option>
                                    <option value="Endurance Training">Endurance Training</option>
                                    <option value="Personal Training">Personal Training</option>
                                    <option value="Group Fitness">Group Fitness</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-calendar"></i> Date</label>
                                <input type="date" class="form-control" name="start_date" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-clock"></i> Start Time</label>
                                <input type="time" class="form-control" name="start_time" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-clock"></i> End Time</label>
                                <input type="time" class="form-control" name="end_time" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inputTrainer"><i class="fas fa-user"></i> Select Trainer</label>
                                <select class="form-control" id="inputTrainer" name="trainer" required>
                                    <option value="">Select Trainer</option>
                                    <?php foreach ($trainers as $trainer): ?>
                                        <option value="<?php echo htmlspecialchars($trainer); ?>">
                                            <?php echo htmlspecialchars($trainer); ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <?php if (empty($trainers)): ?>
                                        <option value="" disabled>No trainers available</option>
                                    <?php endif; ?>
                                </select>
                                <?php if (empty($trainers)): ?>
                                    <small class="form-text text-muted">
                                        <a href="manage_trainer.php">Add trainers</a> to assign them to classes.
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-users"></i> Capacity</label>
                                <input type="number" class="form-control" name="capacity" value="20" min="1" max="100">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-palette"></i> Color</label>
                                <select class="form-control" name="color">
                                    <option value="#17a2b8">Blue (Default)</option>
                                    <option value="#28a745">Green</option>
                                    <option value="#ffc107">Yellow</option>
                                    <option value="#dc3545">Red</option>
                                    <option value="#6f42c1">Purple</option>
                                    <option value="#fd7e14">Orange</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-info-circle"></i> Description</label>
                                <textarea class="form-control" name="description" rows="2" placeholder="Additional details"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div class="modal fade" id="editScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Schedule</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="schedule_id" value="<?php echo $schedule_data['id'] ?? ''; ?>">
                    
                    <?php if ($schedule_data && isset($schedule_data['booking_count']) && $schedule_data['booking_count'] > 0): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> This schedule has <?php echo $schedule_data['booking_count']; ?> booking(s). 
                            Changes to class name, date, or time will affect existing bookings.
                        </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-dumbbell"></i> Class Name</label>
                                <select class="form-control" name="title" required>
                                    <option value="">Select Class</option>
                                    <option value="Yoga Class" <?php echo (isset($schedule_data['title']) && $schedule_data['title'] == 'Yoga Class') ? 'selected' : ''; ?>>Yoga Class</option>
                                    <option value="Zumba Class" <?php echo (isset($schedule_data['title']) && $schedule_data['title'] == 'Zumba Class') ? 'selected' : ''; ?>>Zumba Class</option>
                                    <option value="Cardio Class" <?php echo (isset($schedule_data['title']) && $schedule_data['title'] == 'Cardio Class') ? 'selected' : ''; ?>>Cardio Class</option>
                                    <option value="Weight Lifting" <?php echo (isset($schedule_data['title']) && $schedule_data['title'] == 'Weight Lifting') ? 'selected' : ''; ?>>Weight Lifting</option>
                                    <option value="Endurance Training" <?php echo (isset($schedule_data['title']) && $schedule_data['title'] == 'Endurance Training') ? 'selected' : ''; ?>>Endurance Training</option>
                                    <option value="Personal Training" <?php echo (isset($schedule_data['title']) && $schedule_data['title'] == 'Personal Training') ? 'selected' : ''; ?>>Personal Training</option>
                                    <option value="Group Fitness" <?php echo (isset($schedule_data['title']) && $schedule_data['title'] == 'Group Fitness') ? 'selected' : ''; ?>>Group Fitness</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-calendar"></i> Date</label>
                                <input type="date" class="form-control" name="start_date" value="<?php echo htmlspecialchars($schedule_data['formatted_date'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-clock"></i> Start Time</label>
                                <input type="time" class="form-control" name="start_time" value="<?php echo htmlspecialchars($schedule_data['formatted_start_time'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-clock"></i> End Time</label>
                                <input type="time" class="form-control" name="end_time" value="<?php echo htmlspecialchars($schedule_data['formatted_end_time'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Trainer</label>
                                <select class="form-control" name="trainer">
                                    <option value="">Select Trainer</option>
                                    <?php foreach ($trainers as $trainer): ?>
                                        <option value="<?php echo htmlspecialchars($trainer); ?>" <?php echo (isset($schedule_data['trainer']) && $schedule_data['trainer'] == $trainer) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($trainer); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-users"></i> Capacity</label>
                                <input type="number" class="form-control" name="capacity" value="<?php echo htmlspecialchars($schedule_data['capacity'] ?? 20); ?>" min="1" max="100">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-palette"></i> Color</label>
                                <select class="form-control" name="color">
                                    <option value="#17a2b8" <?php echo (isset($schedule_data['color']) && $schedule_data['color'] == '#17a2b8') ? 'selected' : ''; ?>>Blue (Default)</option>
                                    <option value="#28a745" <?php echo (isset($schedule_data['color']) && $schedule_data['color'] == '#28a745') ? 'selected' : ''; ?>>Green</option>
                                    <option value="#ffc107" <?php echo (isset($schedule_data['color']) && $schedule_data['color'] == '#ffc107') ? 'selected' : ''; ?>>Yellow</option>
                                    <option value="#dc3545" <?php echo (isset($schedule_data['color']) && $schedule_data['color'] == '#dc3545') ? 'selected' : ''; ?>>Red</option>
                                    <option value="#6f42c1" <?php echo (isset($schedule_data['color']) && $schedule_data['color'] == '#6f42c1') ? 'selected' : ''; ?>>Purple</option>
                                    <option value="#fd7e14" <?php echo (isset($schedule_data['color']) && $schedule_data['color'] == '#fd7e14') ? 'selected' : ''; ?>>Orange</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-info-circle"></i> Description</label>
                                <textarea class="form-control" name="description" rows="2" placeholder="Additional details"><?php echo htmlspecialchars($schedule_data['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="view_schedule.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript to automatically show edit modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($show_edit_modal): ?>
        // Show the edit modal automatically when schedule data is loaded
        $('#editScheduleModal').modal('show');
    <?php endif; ?>

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
});
</script>

<?php include('includes/footer.php'); ?>
