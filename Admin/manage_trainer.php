<?php
define('TITLE', 'Manage Trainers');
define('PAGE', 'trainers');
include('includes/header.php');
include('../dbConnection.php');
session_start();

if (!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Handle trainer actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'add':
            $trainer_name = @$_POST['trainer_name'];
$email = @$_POST['email'];
$phone = @$_POST['phone'];
$specialization = @$_POST['specialization'];
$experience_years = @$_POST['experience_years'];
$hire_date = @$_POST['hire_date'] ? $_POST['hire_date'] : NULL;

            // If hire_date is empty, set it to NULL for the query
            if (empty($hire_date)) {
                $hire_date = NULL;  // Set hire_date to NULL if it's not provided
                $stmt = $conn->prepare("INSERT INTO trainers_tb (trainer_name, email, phone, specialization, experience_years, hire_date) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $trainer_name, $email, $phone, $specialization, $experience_years, $hire_date);
            } else {
                $stmt = $conn->prepare("INSERT INTO trainers_tb (trainer_name, email, phone, specialization, experience_years, hire_date) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssss", $trainer_name, $email, $phone, $specialization, $experience_years, $hire_date);
            }

            if ($stmt->execute()) {
                $success_msg = "Trainer added successfully!";
            } else {
                $error_msg = "Error adding trainer: " . $conn->error;
            }
            break;
            
        case 'edit':
            $trainer_id = $_POST['trainer_id'];
            $trainer_name = $_POST['trainer_name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $specialization = $_POST['specialization'];
            $experience_years = $_POST['experience_years'];
            $status = $_POST['status'];
            $hire_date = $_POST['hire_date'];
            
            $sql = "UPDATE trainers_tb SET trainer_name=?, email=?, phone=?, specialization=?, experience_years=?, status=?, hire_date=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssissi", $trainer_name, $email, $phone, $specialization, $experience_years, $status, $hire_date, $trainer_id);
            
            if ($stmt->execute()) {
                $success_msg = "Trainer updated successfully!";
                // Redirect to clear the GET parameter after successful update
                echo "<script> location.href='manage_trainer.php'; </script>";
                exit;
            } else {
                $error_msg = "Error updating trainer: " . $conn->error;
            }
            break;
            
        case 'delete':
            $trainer_id = $_POST['trainer_id'];
            
            // Check if trainer is assigned to any schedules
            $checkSql = "SELECT COUNT(*) as count FROM tbl_events WHERE trainer = (SELECT trainer_name FROM trainers_tb WHERE id = ?)";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("i", $trainer_id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $count = $checkResult->fetch_assoc()['count'];
            
            if ($count > 0) {
                $error_msg = "Cannot delete trainer. They are assigned to " . $count . " schedule(s).";
            } else {
                $sql = "DELETE FROM trainers_tb WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $trainer_id);
                
                if ($stmt->execute()) {
                    $success_msg = "Trainer deleted successfully!";
                } else {
                    $error_msg = "Error deleting trainer.";
                }
            }
            break;
    }
}

// Fetch trainer data if edit button is clicked
$edit_trainer_id = isset($_GET['edit_trainer_id']) ? $_GET['edit_trainer_id'] : null;
$trainer_data = null;
$show_edit_modal = false;

if ($edit_trainer_id) {
    $sql = "SELECT * FROM trainers_tb WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edit_trainer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $trainer_data = $result->fetch_assoc();
        $show_edit_modal = true;
    } else {
        echo "<div class='alert alert-danger'>Trainer not found!</div>";
    }
}
?>

<div class="col-sm-9 col-md-10 mt-5">
    <div class="text-center">
        <p class="bg-dark text-white p-2 mb-4">TRAINER MANAGEMENT</p>
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
        <button class="btn btn-primary" data-toggle="modal" data-target="#addTrainerModal">
            <i class="fas fa-plus"></i> Add New Trainer
        </button>
        <a href="view_schedule.php" class="btn btn-secondary ml-2">
            <i class="fas fa-calendar"></i> Back to Schedule
        </a>
        <a href="dashboard.php" class="btn btn-info ml-2">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
    </div>

    <!-- Debug: Show current database connection status -->
    <div class="alert alert-info">
        Total Trainers: <?php echo $conn->query("SELECT COUNT(*) as count FROM trainers_tb")->fetch_assoc()['count']; ?> | 
        Active Trainers: <?php echo $conn->query("SELECT COUNT(*) as count FROM trainers_tb WHERE status = 'active'")->fetch_assoc()['count']; ?>
    </div>

    <!-- Trainers Table -->
    <?php
    $sql = "SELECT * FROM trainers_tb ORDER BY trainer_name ASC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo '<div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-users"></i> Trainers List</h5>
        </div>
        <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Specialization</th>
                    <th>Experience</th>
                    <th>Hire Date</th>
                    <th>Status</th>
                    <th>Assigned Classes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>';

        while ($row = $result->fetch_assoc()) {
            $statusClass = $row['status'] == 'active' ? 'badge-success' : 'badge-secondary';
            
            // Get assigned schedules count
            $scheduleCountSql = "SELECT COUNT(*) as count FROM tbl_events WHERE trainer = ?";
            $scheduleCountStmt = $conn->prepare($scheduleCountSql);
            $scheduleCountStmt->bind_param("s", $row['trainer_name']);
            $scheduleCountStmt->execute();
            $scheduleCount = $scheduleCountStmt->get_result()->fetch_assoc()['count'];
            
            echo '<tr>';
            echo '<td>' . $row["id"] . '</td>';
            echo '<td><strong>' . htmlspecialchars($row["trainer_name"]) . '</strong></td>';
            echo '<td>' . htmlspecialchars($row["email"] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($row["phone"] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($row["specialization"] ?? 'N/A') . '</td>';
            echo '<td>' . ($row["experience_years"] ?? 0) . ' years</td>';
            echo '<td>' . ($row["hire_date"] ? date('Y-m-d', strtotime($row["hire_date"])) : 'N/A') . '</td>';
            echo '<td><span class="badge ' . $statusClass . '">' . ucfirst($row["status"]) . '</span></td>';
            echo '<td>';
            if ($scheduleCount > 0) {
                echo '<span class="badge badge-info">' . $scheduleCount . ' classes</span>';
            } else {
                echo '<span class="badge badge-light">No classes</span>';
            }
            echo '</td>';
            echo '<td>
                <a href="manage_trainer.php?edit_trainer_id=' . $row["id"] . '" class="btn btn-sm btn-warning" title="Edit Trainer">
                    <i class="fas fa-edit"></i>
                </a>';
            
            if ($scheduleCount > 0) {
                echo '<button class="btn btn-sm btn-secondary ml-1" disabled title="Cannot delete - assigned to classes">
                        <i class="fas fa-trash"></i>
                      </button>';
            } else {
                echo '<form method="POST" class="d-inline ml-1" onsubmit="return confirm(\'Are you sure you want to delete this trainer?\')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="trainer_id" value="' . $row["id"] . '">
                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Trainer">
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
                <h4><i class="fas fa-info-circle"></i> No Trainers Found</h4>
                <p><a href="#" data-toggle="modal" data-target="#addTrainerModal">Add a new trainer</a> to get started.</p>
              </div>';
    }
    ?>
</div>

<!-- Add Trainer Modal -->
<div class="modal fade" id="addTrainerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Add New Trainer</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Trainer Name</label>
                                <input type="text" class="form-control" name="trainer_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> Phone</label>
                                <input type="text" class="form-control" name="phone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-calendar"></i> Date of Birth</label>
                                <input type="date" class="form-control" name="dob">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-genderless"></i> Gender</label>
                                <select class="form-control" name="gender">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-home"></i> Address</label>
                                <textarea class="form-control" name="address"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-image"></i> Profile Image</label>
                        <input type="file" class="form-control" name="image">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Trainer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Edit Trainer Modal -->
<div class="modal fade" id="editTrainerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Trainer</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="trainer_id" value="<?php echo $trainer_data['id'] ?? ''; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Trainer Name</label>
                                <input type="text" class="form-control" name="trainer_name" value="<?php echo htmlspecialchars($trainer_data['trainer_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($trainer_data['email'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> Phone</label>
                                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($trainer_data['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-calendar"></i> Hire Date</label>
                                <input type="date" class="form-control" name="hire_date" value="<?php echo htmlspecialchars($trainer_data['hire_date'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-dumbbell"></i> Specialization</label>
                                <input type="text" class="form-control" name="specialization" value="<?php echo htmlspecialchars($trainer_data['specialization'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-star"></i> Experience (Years)</label>
                                <input type="number" class="form-control" name="experience_years" min="0" max="50" value="<?php echo htmlspecialchars($trainer_data['experience_years'] ?? 0); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-toggle-on"></i> Status</label>
                                <select class="form-control" name="status">
                                    <option value="active" <?php echo isset($trainer_data['status']) && $trainer_data['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo isset($trainer_data['status']) && $trainer_data['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="manage_trainer.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Trainer
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
        // Show the edit modal automatically when trainer data is loaded
        $('#editTrainerModal').modal('show');
    <?php endif; ?>
});
</script>

<?php include('includes/footer.php'); ?>
