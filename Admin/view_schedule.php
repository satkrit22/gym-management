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
                $error_msg = "Error adding schedule.";
            }
            break;
            
        case 'edit':
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
            
            $sql = "UPDATE tbl_events SET title=?, start=?, end=?, trainer=?, capacity=?, color=?, description=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssissi", $title, $start_datetime, $end_datetime, $trainer, $capacity, $color, $description, $schedule_id);
            
            if ($stmt->execute()) {
                $success_msg = "Schedule updated successfully!";
            } else {
                $error_msg = "Error updating schedule.";
            }
            break;
            
        case 'delete':
            $sql = "DELETE FROM tbl_events WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $schedule_id);
            
            if ($stmt->execute()) {
                $success_msg = "Schedule deleted successfully!";
            } else {
                $error_msg = "Error deleting schedule.";
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
            <?php echo $success_msg; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $error_msg; ?>
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
            
            $rowClass = '';
            if ($isToday) $rowClass = 'table-warning';
            elseif ($isPast) $rowClass = 'table-secondary';
            
            echo '<tr class="' . $rowClass . '">';
            echo '<td>' . $row["id"] . '</td>';
            echo '<td><strong>' . htmlspecialchars($row["title"]) . '</strong>';
            if ($isToday) echo ' <span class="badge badge-warning">Today</span>';
            if ($isPast) echo ' <span class="badge badge-secondary">Past</span>';
            echo '</td>';
            echo '<td>' . $startDateTime->format('Y-m-d') . '</td>';
            echo '<td>' . $startDateTime->format('h:i A') . '</td>';
            echo '<td>' . $endDateTime->format('h:i A') . '</td>';
            echo '<td>' . htmlspecialchars($row["trainer"] ?? 'Not Assigned') . '</td>';
            echo '<td>' . ($row["capacity"] ?? 20) . '</td>';
            echo '<td><span class="badge" style="background-color: ' . ($row["color"] ?? '#17a2b8') . '; color: white;">●</span></td>';
            echo '<td>
                <button class="btn btn-sm btn-warning edit-schedule" 
                        data-id="' . $row["id"] . '"
                        data-title="' . htmlspecialchars($row["title"]) . '"
                        data-date="' . $startDateTime->format('Y-m-d') . '"
                        data-start-time="' . $startDateTime->format('H:i') . '"
                        data-end-time="' . $endDateTime->format('H:i') . '"
                        data-trainer="' . htmlspecialchars($row["trainer"] ?? '') . '"
                        data-capacity="' . ($row["capacity"] ?? 20) . '"
                        data-color="' . htmlspecialchars($row["color"] ?? '#17a2b8') . '"
                        data-description="' . htmlspecialchars($row["description"] ?? '') . '"
                        data-toggle="modal" data-target="#editScheduleModal"
                        title="Edit Schedule">
                    <i class="fas fa-edit"></i>
                </button>
                <form method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to delete this schedule?\')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="schedule_id" value="' . $row["id"] . '">
                    <button type="submit" class="btn btn-sm btn-danger" title="Delete Schedule">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </td>';
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
                                <label><i class="fas fa-user"></i> Trainer</label>
                                <input type="text" class="form-control" name="trainer" placeholder="Trainer Name">
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
                    <input type="hidden" name="schedule_id" id="edit_schedule_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-dumbbell"></i> Class Name</label>
                                <select class="form-control" name="title" id="edit_title" required>
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
                                <input type="date" class="form-control" name="start_date" id="edit_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-clock"></i> Start Time</label>
                                <input type="time" class="form-control" name="start_time" id="edit_start_time" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-clock"></i> End Time</label>
                                <input type="time" class="form-control" name="end_time" id="edit_end_time" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Trainer</label>
                                <input type="text" class="form-control" name="trainer" id="edit_trainer" placeholder="Trainer Name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-users"></i> Capacity</label>
                                <input type="number" class="form-control" name="capacity" id="edit_capacity" min="1" max="100">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-palette"></i> Color</label>
                                <select class="form-control" name="color" id="edit_color">
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
                                <textarea class="form-control" name="description" id="edit_description" rows="2" placeholder="Additional details"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Edit schedule button click handler
    $('.edit-schedule').click(function() {
        $('#edit_schedule_id').val($(this).data('id'));
        $('#edit_title').val($(this).data('title'));
        $('#edit_date').val($(this).data('date'));
        $('#edit_start_time').val($(this).data('start-time'));
        $('#edit_end_time').val($(this).data('end-time'));
        $('#edit_trainer').val($(this).data('trainer'));
        $('#edit_capacity').val($(this).data('capacity'));
        $('#edit_color').val($(this).data('color'));
        $('#edit_description').val($(this).data('description'));
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
});
</script>

<?php include('includes/footer.php'); ?>