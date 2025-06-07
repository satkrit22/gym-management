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
            $trainer_name = $_POST['trainer_name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $specialization = $_POST['specialization'];
            $experience_years = $_POST['experience_years'];
            $hire_date = $_POST['hire_date'];
            
            $sql = "INSERT INTO trainers_tb (trainer_name, email, phone, specialization, experience_years, hire_date) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssiss", $trainer_name, $email, $phone, $specialization, $experience_years, $hire_date);
            
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
?>

<div class="col-sm-9 col-md-10 mt-5">
    <div class="text-center">
        <p class="bg-dark text-white p-2 mb-4">TRAINER MANAGEMENT</p>
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>';

        while ($row = $result->fetch_assoc()) {
            $statusClass = $row['status'] == 'active' ? 'badge-success' : 'badge-secondary';
            
            echo '<tr>';
            echo '<td>' . $row["id"] . '</td>';
            echo '<td><strong>' . htmlspecialchars($row["trainer_name"]) . '</strong></td>';
            echo '<td>' . htmlspecialchars($row["email"] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($row["phone"] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($row["specialization"] ?? 'N/A') . '</td>';
            echo '<td>' . ($row["experience_years"] ?? 0) . ' years</td>';
            echo '<td>' . ($row["hire_date"] ? date('Y-m-d', strtotime($row["hire_date"])) : 'N/A') . '</td>';
            echo '<td><span class="badge ' . $statusClass . '">' . ucfirst($row["status"]) . '</span></td>';
            echo '<td>
                <button class="btn btn-sm btn-warning edit-trainer" 
                        data-id="' . $row["id"] . '"
                        data-name="' . htmlspecialchars($row["trainer_name"]) . '"
                        data-email="' . htmlspecialchars($row["email"] ?? '') . '"
                        data-phone="' . htmlspecialchars($row["phone"] ?? '') . '"
                        data-specialization="' . htmlspecialchars($row["specialization"] ?? '') . '"
                        data-experience="' . ($row["experience_years"] ?? 0) . '"
                        data-status="' . $row["status"] . '"
                        data-hire-date="' . ($row["hire_date"] ?? '') . '"
                        data-toggle="modal" data-target="#editTrainerModal"
                        title="Edit Trainer">
                    <i class="fas fa-edit"></i>
                </button>
                <form method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to delete this trainer?\')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="trainer_id" value="' . $row["id"] . '">
                    <button type="submit" class="btn btn-sm btn-danger" title="Delete Trainer">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </td>';
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
            <form method="POST">
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
                                <input type="email" class="form-control" name="email">
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
                                <label><i class="fas fa-calendar"></i> Hire Date</label>
                                <input type="date" class="form-control" name="hire_date">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-dumbbell"></i> Specialization</label>
                                <input type="text" class="form-control" name="specialization" placeholder="e.g., Weight Training, Yoga">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-star"></i> Experience (Years)</label>
                                <input type="number" class="form-control" name="experience_years" min="0" max="50" value="0">
                            </div>
                        </div>
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
                    <input type="hidden" name="trainer_id" id="edit_trainer_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Trainer Name</label>
                                <input type="text" class="form-control" name="trainer_name" id="edit_trainer_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" class="form-control" name="email" id="edit_email">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> Phone</label>
                                <input type="text" class="form-control" name="phone" id="edit_phone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-calendar"></i> Hire Date</label>
                                <input type="date" class="form-control" name="hire_date" id="edit_hire_date">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-dumbbell"></i> Specialization</label>
                                <input type="text" class="form-control" name="specialization" id="edit_specialization">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-star"></i> Experience (Years)</label>
                                <input type="number" class="form-control" name="experience_years" id="edit_experience" min="0" max="50">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-toggle-on"></i> Status</label>
                                <select class="form-control" name="status" id="edit_status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Trainer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Edit trainer button click handler
    $('.edit-trainer').click(function() {
        $('#edit_trainer_id').val($(this).data('id'));
        $('#edit_trainer_name').val($(this).data('name'));
        $('#edit_email').val($(this).data('email'));
        $('#edit_phone').val($(this).data('phone'));
        $('#edit_specialization').val($(this).data('specialization'));
        $('#edit_experience').val($(this).data('experience'));
        $('#edit_status').val($(this).data('status'));
        $('#edit_hire_date').val($(this).data('hire-date'));
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
});
</script>

<?php include('includes/footer.php'); ?>