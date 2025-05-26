<?php
define('TITLE', 'Bookings');
define('PAGE', 'bookings');
include('includes/header.php'); 
include('../dbConnection.php');
session_start();

if (!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

$aEmail = $_SESSION['aEmail'];

// Handle payment status update
if (isset($_POST['update_payment'])) {
    $booking_id = $_POST['booking_id'];
    $payment_status = $_POST['payment_status'];
    
    $sql = "UPDATE submitbookingt_tb SET payment_status = ? WHERE Booking_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $payment_status, $booking_id);
    
    if ($stmt->execute()) {
        $success_msg = "Payment status updated successfully!";
    } else {
        $error_msg = "Error updating payment status.";
    }
}

// Handle booking deletion
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $sql = "DELETE FROM submitbookingt_tb WHERE Booking_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success_msg = "Booking deleted successfully!";
    } else {
        $error_msg = "Error deleting booking.";
    }
}

// Get filter parameters
$filter_status = $_GET['filter_status'] ?? '';
$filter_payment = $_GET['filter_payment'] ?? '';
?>

<div class="col-sm-9 col-md-10 mt-5 text-center">
    <p class="bg-dark text-white p-2">MEMBER BOOKING MANAGEMENT</p>

    <?php if (isset($success_msg)): ?>
        <div class="alert alert-success"><?php echo $success_msg; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white text-left">
            <h5 class="mb-0">Filter Bookings</h5>
        </div>
        <div class="card-body text-left">
            <form method="GET" class="row">
                <div class="col-md-4">
                    <label for="filter_status">Filter by Status:</label>
                    <select class="form-control" id="filter_status" name="filter_status">
                        <option value="">All Status</option>
                        <option value="upcoming" <?php echo ($filter_status == 'upcoming') ? 'selected' : ''; ?>>Upcoming</option>
                        <option value="today" <?php echo ($filter_status == 'today') ? 'selected' : ''; ?>>Today</option>
                        <option value="completed" <?php echo ($filter_status == 'completed') ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter_payment">Filter by Payment:</label>
                    <select class="form-control" id="filter_payment" name="filter_payment">
                        <option value="">All Payments</option>
                        <option value="pending" <?php echo ($filter_payment == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="paid" <?php echo ($filter_payment == 'paid') ? 'selected' : ''; ?>>Paid</option>
                        <option value="failed" <?php echo ($filter_payment == 'failed') ? 'selected' : ''; ?>>Failed</option>
                        <option value="refunded" <?php echo ($filter_payment == 'refunded') ? 'selected' : ''; ?>>Refunded</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2">Filter</button>
                    <a href="bookings.php" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Booking Statistics -->
    <div class="row mb-4">
        <?php
        $totalBookings = $conn->query("SELECT COUNT(*) as count FROM submitbookingt_tb")->fetch_assoc()['count'];
        $paidBookings = $conn->query("SELECT COUNT(*) as count FROM submitbookingt_tb WHERE payment_status = 'paid'")->fetch_assoc()['count'];
        $pendingPayments = $conn->query("SELECT COUNT(*) as count FROM submitbookingt_tb WHERE payment_status = 'pending'")->fetch_assoc()['count'];
        ?>
        <div class="col-md-4">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <h3><?php echo $totalBookings; ?></h3>
                    <p>Total Bookings</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h3><?php echo $paidBookings; ?></h3>
                    <p>Paid Bookings</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-warning text-white">
                <div class="card-body">
                    <h3><?php echo $pendingPayments; ?></h3>
                    <p>Pending Payments</p>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Build SQL query with filters
    $sql = "SELECT * FROM submitbookingt_tb WHERE 1=1";
    $params = array();
    $types = "";

    if (!empty($filter_status)) {
        switch ($filter_status) {
            case 'upcoming':
                $sql .= " AND member_date > CURDATE()";
                break;
            case 'today':
                $sql .= " AND member_date = CURDATE()";
                break;
            case 'completed':
                $sql .= " AND member_date < CURDATE()";
                break;
        }
    }

    if (!empty($filter_payment)) {
        $sql .= " AND payment_status = ?";
        $params[] = $filter_payment;
        $types .= "s";
    }

    $sql .= " ORDER BY Booking_id DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Package</th>
                    <th>Mobile</th>
                    <th>Date</th>
                    <th>Payment Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>';
        
        while ($row = $result->fetch_assoc()) {
            $bookingDate = new DateTime($row["member_date"]);
            $today = new DateTime('today');
            
            // Determine booking status
            $bookingStatus = '';
            if ($bookingDate < $today) {
                $bookingStatus = 'Completed';
            } elseif ($bookingDate == $today) {
                $bookingStatus = 'Today';
            } else {
                $bookingStatus = 'Upcoming';
            }
            
            // Payment status badge
            $paymentBadge = '';
            switch ($row['payment_status']) {
                case 'paid':
                    $paymentBadge = '<span class="badge badge-success">Paid</span>';
                    break;
                case 'pending':
                    $paymentBadge = '<span class="badge badge-warning">Pending</span>';
                    break;
                case 'failed':
                    $paymentBadge = '<span class="badge badge-danger">Failed</span>';
                    break;
                case 'refunded':
                    $paymentBadge = '<span class="badge badge-info">Refunded</span>';
                    break;
                default:
                    $paymentBadge = '<span class="badge badge-secondary">Unknown</span>';
            }
            
            echo '<tr>';
            echo '<td><strong>#' . $row["Booking_id"] . '</strong></td>';
            echo '<td>' . htmlspecialchars($row["member_name"]) . '</td>';
            echo '<td>' . htmlspecialchars($row["member_email"]) . '</td>';
            echo '<td><span class="badge badge-info">' . htmlspecialchars($row["booking_type"]) . '</span><br><small>' . $row["subscription_months"] . ' Month(s)</small></td>';
            echo '<td>' . htmlspecialchars($row["member_mobile"]) . '</td>';
            echo '<td>' . $bookingDate->format('Y-m-d') . '<br><small>' . $bookingStatus . '</small></td>';
            echo '<td>' . $paymentBadge . '</td>';
            echo '<td>
                <button class="btn btn-sm btn-warning update-payment" 
                        data-id="' . $row["Booking_id"] . '"
                        data-status="' . $row["payment_status"] . '"
                        data-toggle="modal" data-target="#paymentModal">
                    <i class="fas fa-credit-card"></i>
                </button>
                <form method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to delete this booking?\')">
                    <input type="hidden" name="id" value="' . $row["Booking_id"] . '">
                    <button type="submit" class="btn btn-sm btn-danger" name="delete">
                        <i class="far fa-trash-alt"></i>
                    </button>
                </form>
            </td>';
            echo '</tr>';
        }

        echo '</tbody></table></div>';
    } else {
        echo '<div class="alert alert-info">No bookings found.</div>';
    }
    ?>
</div>

<!-- Payment Status Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Payment Status</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="booking_id" id="payment_booking_id">
                    <div class="form-group">
                        <label>Payment Status</label>
                        <select class="form-control" name="payment_status" id="payment_status" required>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="update_payment" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.update-payment').click(function() {
        $('#payment_booking_id').val($(this).data('id'));
        $('#payment_status').val($(this).data('status'));
    });
});
</script>

<?php include('includes/footer.php'); ?>