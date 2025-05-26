<?php
session_start();

define('TITLE', 'My Booking');
define('PAGE', 'MyBooking');
include('includes/header.php');
include('../dbConnection.php');

if (!isset($_SESSION['is_login'])) {
    echo "<script> location.href='memberLogin.php'; </script>";
    exit();
}

$mEmail = $_SESSION['mEmail'];

// Handle subscription extension
if (isset($_POST['extend_subscription'])) {
    $booking_id = $_POST['booking_id'];
    $extend_months = $_POST['extend_months'];
    
    // Get current subscription end date
    $sql = "SELECT subscription_end_date FROM submitbookingt_tb WHERE Booking_id = ? AND member_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $booking_id, $mEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $currentEndDate = new DateTime($row['subscription_end_date']);
        $today = new DateTime();
        
        // If subscription has expired, start from today, otherwise extend from current end date
        $newStartDate = ($currentEndDate < $today) ? $today : $currentEndDate;
        $newEndDate = clone $newStartDate;
        $newEndDate->add(new DateInterval('P' . $extend_months . 'M'));
        
        // Update subscription end date
        $updateSql = "UPDATE submitbookingt_tb SET subscription_end_date = ?, subscription_months = subscription_months + ? WHERE Booking_id = ? AND member_email = ?";
        $updateStmt = $conn->prepare($updateSql);
        $newEndDateStr = $newEndDate->format('Y-m-d');
        $updateStmt->bind_param("siis", $newEndDateStr, $extend_months, $booking_id, $mEmail);
        
        if ($updateStmt->execute()) {
            $success_msg = "Subscription extended successfully! New expiry date: " . $newEndDate->format('Y-m-d');
        } else {
            $error_msg = "Error extending subscription.";
        }
    }
}
?>

<div class="col-sm-9 col-md-10 mt-5">
    <div class="text-center">
        <p class="bg-dark text-white p-2 mb-4">MY BOOKINGS & SUBSCRIPTIONS</p>
    </div>

    <?php if (isset($success_msg)): ?>
        <div class="alert alert-success"><?php echo $success_msg; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <!-- Subscription Overview -->
    <?php
    $subscriptionSql = "SELECT *, 
                               DATEDIFF(subscription_end_date, CURDATE()) as days_remaining,
                               CASE 
                                   WHEN subscription_end_date < CURDATE() THEN 'expired'
                                   WHEN DATEDIFF(subscription_end_date, CURDATE()) <= 7 THEN 'expiring_soon'
                                   ELSE 'active'
                               END as subscription_status
                        FROM submitbookingt_tb 
                        WHERE member_email = ? 
                        ORDER BY subscription_end_date DESC 
                        LIMIT 1";
    $subscriptionStmt = $conn->prepare($subscriptionSql);
    $subscriptionStmt->bind_param("s", $mEmail);
    $subscriptionStmt->execute();
    $subscriptionResult = $subscriptionStmt->get_result();
    
    if ($subscriptionResult->num_rows > 0) {
        $subscription = $subscriptionResult->fetch_assoc();
        $statusClass = '';
        $statusText = '';
        $actionButton = '';
        
        switch ($subscription['subscription_status']) {
            case 'expired':
                $statusClass = 'alert-danger';
                $statusText = 'Your subscription has expired on ' . $subscription['subscription_end_date'];
                $actionButton = '<button class="btn btn-warning extend-subscription" data-id="' . $subscription['Booking_id'] . '" data-toggle="modal" data-target="#extendModal">Renew Subscription</button>';
                break;
            case 'expiring_soon':
                $statusClass = 'alert-warning';
                $statusText = 'Your subscription expires in ' . $subscription['days_remaining'] . ' days (' . $subscription['subscription_end_date'] . ')';
                $actionButton = '<button class="btn btn-info extend-subscription" data-id="' . $subscription['Booking_id'] . '" data-toggle="modal" data-target="#extendModal">Extend Subscription</button>';
                break;
            case 'active':
                $statusClass = 'alert-success';
                $statusText = 'Your subscription is active until ' . $subscription['subscription_end_date'] . ' (' . $subscription['days_remaining'] . ' days remaining)';
                $actionButton = '<button class="btn btn-outline-info extend-subscription" data-id="' . $subscription['Booking_id'] . '" data-toggle="modal" data-target="#extendModal">Extend Subscription</button>';
                break;
        }
        
        echo '<div class="' . $statusClass . ' d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Subscription Status</h5>
                    <p class="mb-0">' . $statusText . '</p>
                </div>
                <div>' . $actionButton . '</div>
              </div>';
    }
    ?>

    <!-- Booking Statistics -->
    <div class="row mb-4">
        <?php
        $totalBookings = $conn->prepare("SELECT COUNT(*) as count FROM submitbookingt_tb WHERE member_email = ?");
        $totalBookings->bind_param("s", $mEmail);
        $totalBookings->execute();
        $totalCount = $totalBookings->get_result()->fetch_assoc()['count'];

        $upcomingBookings = $conn->prepare("SELECT COUNT(*) as count FROM submitbookingt_tb WHERE member_email = ? AND member_date >= CURDATE()");
        $upcomingBookings->bind_param("s", $mEmail);
        $upcomingBookings->execute();
        $upcomingCount = $upcomingBookings->get_result()->fetch_assoc()['count'];

        $pastBookings = $totalCount - $upcomingCount;
        
        $activeSubscriptions = $conn->prepare("SELECT COUNT(*) as count FROM submitbookingt_tb WHERE member_email = ? AND subscription_end_date >= CURDATE()");
        $activeSubscriptions->bind_param("s", $mEmail);
        $activeSubscriptions->execute();
        $activeSubCount = $activeSubscriptions->get_result()->fetch_assoc()['count'];
        ?>
        <div class="col-md-3">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <h3><?php echo $totalCount; ?></h3>
                    <p>Total Bookings</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h3><?php echo $upcomingCount; ?></h3>
                    <p>Upcoming Bookings</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-secondary text-white">
                <div class="card-body">
                    <h3><?php echo $pastBookings; ?></h3>
                    <p>Past Bookings</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-info text-white">
                <div class="card-body">
                    <h3><?php echo $activeSubCount; ?></h3>
                    <p>Active Subscriptions</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Filter Bookings</h5></div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <a href="?filter=all" class="btn btn-outline-primary <?php echo (!isset($_GET['filter']) || $_GET['filter'] == 'all') ? 'active' : ''; ?>">All</a>
                </div>
                <div class="col-md-3">
                    <a href="?filter=upcoming" class="btn btn-outline-success <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'upcoming') ? 'active' : ''; ?>">Upcoming</a>
                </div>
                <div class="col-md-3">
                    <a href="?filter=past" class="btn btn-outline-secondary <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'past') ? 'active' : ''; ?>">Past</a>
                </div>
                <div class="col-md-3">
                    <a href="?filter=active_subscription" class="btn btn-outline-info <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'active_subscription') ? 'active' : ''; ?>">Active Subscriptions</a>
                </div>
            </div>
        </div>
    </div>

    <?php
    $filter = $_GET['filter'] ?? 'all';
    $sql = "SELECT * FROM submitbookingt_tb WHERE member_email = ?";
    
    if ($filter == 'upcoming') {
        $sql .= " AND member_date >= CURDATE()";
    } elseif ($filter == 'past') {
        $sql .= " AND member_date < CURDATE()";
    } elseif ($filter == 'active_subscription') {
        $sql .= " AND subscription_end_date >= CURDATE()";
    }
    
    $sql .= " ORDER BY member_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $mEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Booking ID</th>
                <th>Name</th>
                <th>Package</th>
                <th>Trainer</th>
                <th>Date</th>
                <th>Subscription</th>
                <th>Expires On</th>
                <th>Payment Status</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>';

        while ($row = $result->fetch_assoc()) {
            $bookingDate = new DateTime($row["member_date"]);
            $subscriptionEnd = new DateTime($row["subscription_end_date"]);
            $today = new DateTime('today');
            
            $status = '';
            $statusClass = '';
            $canCancel = false;

            if ($bookingDate < $today) {
                $status = 'Completed';
                $statusClass = 'badge-secondary';
            } elseif ($bookingDate == $today) {
                $status = 'Today';
                $statusClass = 'badge-warning';
            } else {
                $status = 'Upcoming';
                $statusClass = 'badge-success';
                $canCancel = true;
            }
            
            // Subscription status
            $subscriptionStatus = '';
            $subscriptionClass = '';
            if ($subscriptionEnd >= $today) {
                $daysRemaining = $today->diff($subscriptionEnd)->days;
                $subscriptionStatus = 'Active (' . $daysRemaining . ' days)';
                $subscriptionClass = 'badge-success';
            } else {
                $subscriptionStatus = 'Expired';
                $subscriptionClass = 'badge-danger';
            }
            
            // Payment status
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
                default:
                    $paymentBadge = '<span class="badge badge-secondary">N/A</span>';
            }

            echo '<tr>
                <td><strong>#' . $row["Booking_id"] . '</strong></td>
                <td>' . htmlspecialchars($row["member_name"]) . '</td>
                <td><span class="badge badge-info">' . htmlspecialchars($row["booking_type"]) . '</span></td>
                <td>' . htmlspecialchars($row["trainer"]) . '</td>
                <td>' . $bookingDate->format('Y-m-d') . '</td>
                <td>' . htmlspecialchars($row["subscription_months"]) . ' Month(s)</td>
                <td>' . $subscriptionEnd->format('Y-m-d') . '<br><span class="badge ' . $subscriptionClass . '">' . $subscriptionStatus . '</span></td>
                <td>' . $paymentBadge . '</td>
                <td><span class="badge ' . $statusClass . '">' . $status . '</span></td>
                <td>';

            if ($canCancel) {
                echo '<form method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to cancel this booking?\')">
                        <input type="hidden" name="id" value="' . $row["Booking_id"] . '">
                        <button type="submit" class="btn btn-danger btn-sm" name="delete">
                            <i class="far fa-trash-alt"></i> Cancel
                        </button>
                      </form>';
            } else {
                echo '<span class="text-muted">Cannot Cancel</span>';
            }

            echo '</td></tr>';
        }

        echo '</tbody></table></div>';
    } else {
        $filterText = '';
        switch ($filter) {
            case 'upcoming':
                $filterText = 'upcoming ';
                break;
            case 'past':
                $filterText = 'past ';
                break;
            case 'active_subscription':
                $filterText = 'active subscription ';
                break;
        }
        
        echo '<div class="alert alert-info text-center">
                <h4>No ' . ucfirst($filterText) . 'Bookings Found</h4>
                <p>You don\'t have any ' . $filterText . 'bookings at the moment.</p>
                <a href="SubmitBooking.php" class="btn btn-primary">Make a New Booking</a>
              </div>';
    }

    // Cancel Booking Logic
    if (isset($_POST['delete'])) {
        $bookingId = $_POST['id'];
        $checkStmt = $conn->prepare("SELECT member_date FROM submitbookingt_tb WHERE Booking_id = ? AND member_email = ?");
        $checkStmt->bind_param("is", $bookingId, $mEmail);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $bookingData = $checkResult->fetch_assoc();
            $bookingDate = new DateTime($bookingData['member_date']);
            $today = new DateTime('today');

            if ($bookingDate > $today) {
                $deleteStmt = $conn->prepare("DELETE FROM submitbookingt_tb WHERE Booking_id = ? AND member_email = ?");
                $deleteStmt->bind_param("is", $bookingId, $mEmail);
                if ($deleteStmt->execute()) {
                    echo '<script>alert("Booking cancelled successfully!"); window.location.href="mybooking.php";</script>';
                } else {
                    echo '<div class="alert alert-danger">Unable to cancel booking.</div>';
                }
                $deleteStmt->close();
            } else {
                echo '<div class="alert alert-warning">Cannot cancel booking for today or past dates.</div>';
            }
        }
        $checkStmt->close();
    }

    $stmt->close();
    ?>

    <!-- Quick Actions -->
    <div class="text-center mt-4">
        <a href="SubmitBooking.php" class="btn btn-primary btn-lg">
            <i class="fas fa-plus"></i> Make New Booking
        </a>
        <a href="viewschedule.php" class="btn btn-info btn-lg ml-2">
            <i class="fas fa-calendar"></i> View Schedule
        </a>
    </div>
</div>

<!-- Extend Subscription Modal -->
<div class="modal fade" id="extendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Extend Subscription</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="booking_id" id="extend_booking_id">
                    <div class="form-group">
                        <label>Select Extension Period</label>
                        <select class="form-control" name="extend_months" required>
                            <option value="">Choose Duration</option>
                            <option value="1">1 Month</option>
                            <option value="3">3 Months</option>
                            <option value="6">6 Months</option>
                            <option value="12">12 Months</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <small>Your subscription will be extended from the current expiry date or today's date if already expired.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="extend_subscription" class="btn btn-primary">Extend Subscription</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.extend-subscription').click(function() {
        $('#extend_booking_id').val($(this).data('id'));
    });
});
</script>

<?php include('includes/footer.php'); ?>