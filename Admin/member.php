<?php
define('TITLE', 'Members');
define('PAGE', 'members');
include('includes/header.php'); 
include('../dbConnection.php');
session_start();

if (!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

$aEmail = $_SESSION['aEmail'];

// Handle member actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $member_id = $_POST['member_id'] ?? '';
    
    switch ($action) {
        case 'activate':
            $sql = "UPDATE memberlogin_tb SET status = 'active' WHERE m_login_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $member_id);
            if ($stmt->execute()) {
                $success_msg = "Member activated successfully!";
            }
            break;
            
        case 'deactivate':
            $sql = "UPDATE memberlogin_tb SET status = 'inactive' WHERE m_login_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $member_id);
            if ($stmt->execute()) {
                $success_msg = "Member deactivated successfully!";
            }
            break;
    }
}

// Delete handler
if (isset($_REQUEST['delete'])) {
    $id = $_REQUEST['id'];
    
    // First delete related bookings
    $deleteBookings = "DELETE FROM submitbookingt_tb WHERE member_email = (SELECT m_email FROM memberlogin_tb WHERE m_login_id = ?)";
    $stmt1 = $conn->prepare($deleteBookings);
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    
    // Then delete member
    $sql = "DELETE FROM memberlogin_tb WHERE m_login_id = ?";
    $stmt2 = $conn->prepare($sql);
    $stmt2->bind_param("i", $id);
    
    if ($stmt2->execute()) {
        echo '<meta http-equiv="refresh" content="0;URL=?deleted" />';
    } else {
        $error_msg = "Unable to delete member.";
    }
}
?>

<div class="col-sm-9 col-md-10 mt-5 text-center">
    <p class="bg-dark text-white p-2">GYM MEMBERS MANAGEMENT</p>

    <?php if (isset($success_msg)): ?>
        <div class="alert alert-success"><?php echo $success_msg; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <!-- Member Statistics -->
    <div class="row mb-4">
        <?php
        $totalMembers = $conn->query("SELECT COUNT(*) as count FROM memberlogin_tb")->fetch_assoc()['count'];
        $activeMembers = $conn->query("SELECT COUNT(*) as count FROM memberlogin_tb WHERE status = 'active' OR status IS NULL")->fetch_assoc()['count'];
        $inactiveMembers = $totalMembers - $activeMembers;
        ?>
        <div class="col-md-4">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <h3><?php echo $totalMembers; ?></h3>
                    <p>Total Members</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h3><?php echo $activeMembers; ?></h3>
                    <p>Active Members</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-warning text-white">
                <div class="card-body">
                    <h3><?php echo $inactiveMembers; ?></h3>
                    <p>Inactive Members</p>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Get members with their booking information
    $sql = "SELECT m.*, 
                   COUNT(b.Booking_id) as total_bookings,
                   MAX(b.member_date) as last_booking_date,
                   MAX(b.subscription_end_date) as subscription_end,
                   b.payment_status as last_payment_status
            FROM memberlogin_tb m 
            LEFT JOIN submitbookingt_tb b ON m.m_email = b.member_email 
            GROUP BY m.m_login_id 
            ORDER BY m.m_login_id DESC";
    
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo '<div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Total Bookings</th>
                    <th>Last Booking</th>
                    <th>Subscription Status</th>
                    <th>Payment Status</th>
                    <th>Member Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>';

        while ($row = $result->fetch_assoc()) {
            $memberStatus = $row['status'] ?? 'active';
            $subscriptionStatus = 'No Subscription';
            $subscriptionClass = 'badge-secondary';
            
            if ($row['subscription_end']) {
                $endDate = new DateTime($row['subscription_end']);
                $today = new DateTime();
                
                if ($endDate > $today) {
                    $subscriptionStatus = 'Active (Expires: ' . $endDate->format('Y-m-d') . ')';
                    $subscriptionClass = 'badge-success';
                } else {
                    $subscriptionStatus = 'Expired (' . $endDate->format('Y-m-d') . ')';
                    $subscriptionClass = 'badge-danger';
                }
            }
            
            $paymentBadge = '';
            switch ($row['last_payment_status']) {
                case 'paid':
                    $paymentBadge = '<span class="badge badge-success">Paid</span>';
                    break;
                case 'pending':
                    $paymentBadge = '<span class="badge badge-warning">Pending</span>';
                    break;
                case 'failed':
                    $paymentBadge = '<span class="badge badge-danger">Failed</span>';
                    break;
                default:
                    $paymentBadge = '<span class="badge badge-secondary">N/A</span>';
            }
            
            echo '<tr>';
            echo '<td><strong>#' . $row["m_login_id"] . '</strong></td>';
            echo '<td>' . htmlspecialchars($row["m_name"]) . '</td>';
            echo '<td>' . htmlspecialchars($row["m_email"]) . '</td>';
            echo '<td><span class="badge badge-info">' . $row["total_bookings"] . '</span></td>';
            echo '<td>' . ($row["last_booking_date"] ? $row["last_booking_date"] : 'Never') . '</td>';
            echo '<td><span class="badge ' . $subscriptionClass . '">' . $subscriptionStatus . '</span></td>';
            echo '<td>' . $paymentBadge . '</td>';
            echo '<td>';
            
            if ($memberStatus == 'active') {
                echo '<span class="badge badge-success">Active</span>';
            } else {
                echo '<span class="badge badge-secondary">Inactive</span>';
            }
            
            echo '</td>';
            echo '<td>
                <form action="editmeb.php" method="POST" class="d-inline">
                    <input type="hidden" name="id" value="' . $row["m_login_id"] . '">
                    <button type="submit" class="btn btn-sm btn-success mr-1" name="view" title="Edit Member">
                        <i class="fas fa-pen"></i>
                    </button>
                </form>';
                
            if ($memberStatus == 'active') {
                echo '<form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="deactivate">
                        <input type="hidden" name="member_id" value="' . $row["m_login_id"] . '">
                        <button type="submit" class="btn btn-sm btn-warning mr-1" title="Deactivate Member" onclick="return confirm(\'Deactivate this member?\')">
                            <i class="fas fa-pause"></i>
                        </button>
                      </form>';
            } else {
                echo '<form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="activate">
                        <input type="hidden" name="member_id" value="' . $row["m_login_id"] . '">
                        <button type="submit" class="btn btn-sm btn-info mr-1" title="Activate Member">
                            <i class="fas fa-play"></i>
                        </button>
                      </form>';
            }
            
            echo '<form action="" method="POST" class="d-inline">
                    <input type="hidden" name="id" value="' . $row["m_login_id"] . '">
                    <button type="submit" class="btn btn-sm btn-danger" name="delete" title="Delete Member" onclick="return confirm(\'Are you sure you want to delete this member? This will also delete all their bookings.\')">
                        <i class="far fa-trash-alt"></i>
                    </button>
                  </form>
            </td>';
            echo '</tr>';
        }

        echo '</tbody></table></div>';
    } else {
        echo '<div class="alert alert-info">No members found.</div>';
    }
    ?>
</div>

<!-- Add Member Button -->
<div>
    <a class="btn btn-success box" href="insertmeb.php" title="Add New Member">
        <i class="fas fa-plus fa-2x"></i>
    </a>
</div>

<?php include('includes/footer.php'); ?>