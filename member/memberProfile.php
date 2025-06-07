<?php
session_start();

define('TITLE', 'My Account');
define('PAGE', 'MyAccount');
include('includes/header.php');
include('../dbConnection.php');

if (!isset($_SESSION['is_login'])) {
    echo "<script> location.href='memberLogin.php'; </script>";
    exit();
}

$mEmail = $_SESSION['mEmail'];

// Get member profile information
$sql = "SELECT * FROM memberlogin_tb WHERE m_email='$mEmail'";
$result = $conn->query($sql);
if($result->num_rows == 1){
    $row = $result->fetch_assoc();
    $mName = $row["m_name"];
}

// Handle profile update
if(isset($_REQUEST['nameupdate'])){
    if(($_REQUEST['rName'] == "")){
        $profileMsg = '<div class="alert alert-warning" role="alert"> Fill All Fields </div>';
    } else {
        $mName = $_REQUEST["rName"];
        $sql = "UPDATE memberlogin_tb SET m_name = '$mName' WHERE m_email = '$mEmail'";
        if($conn->query($sql) == TRUE){
            $profileMsg = '<div class="alert alert-success" role="alert"> Profile Updated Successfully </div>';
        } else {
            $profileMsg = '<div class="alert alert-danger" role="alert"> Unable to Update Profile </div>';
        }
    }
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
            // Also delete from tbl_bookings if it exists
            $deleteClassBooking = $conn->prepare("DELETE FROM tbl_bookings WHERE member_email = ? AND class_date = ?");
            $deleteClassBooking->bind_param("ss", $mEmail, $bookingData['member_date']);
            $deleteClassBooking->execute();
            $deleteClassBooking->close();
            
            // Delete from main booking table
            $deleteStmt = $conn->prepare("DELETE FROM submitbookingt_tb WHERE Booking_id = ? AND member_email = ?");
            $deleteStmt->bind_param("is", $bookingId, $mEmail);
            if ($deleteStmt->execute()) {
                echo '<script>alert("Booking cancelled successfully!"); window.location.href="' . $_SERVER['PHP_SELF'] . '";</script>';
            } else {
                $error_msg = "Unable to cancel booking.";
            }
            $deleteStmt->close();
        } else {
            $error_msg = "Cannot cancel booking for today or past dates.";
        }
    }
    $checkStmt->close();
}
?>

<style>
.table-dark-text {
    color: #000 !important;
}
.table-dark-text th,
.table-dark-text td {
    color: #000 !important;
}
.table-dark-text .badge {
    color: #fff !important;
}
</style>

<div class="col-sm-9 col-md-10 mt-5">
    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="bookings-tab" data-toggle="tab" href="#bookings" role="tab">My Bookings</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="schedule-bookings-tab" data-toggle="tab" href="#schedule-bookings" role="tab">Class Bookings</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab">Profile Settings</a>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        <!-- Main Bookings Tab -->
        <div class="tab-pane fade show active" id="bookings" role="tabpanel">
            <div class="text-center mt-3">
                <p class="bg-dark text-white p-2 mb-4">MY BOOKINGS & SUBSCRIPTIONS</p>
            </div>

            <?php if (isset($error_msg)): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>

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
                
                $activeSubscriptions = $conn->prepare("SELECT COUNT(*) as count FROM submitbookingt_tb WHERE member_email = ? AND subscription_end_date >= CURDATE() AND subscription_end_date IS NOT NULL");
                $activeSubscriptions->bind_param("s", $mEmail);
                $activeSubscriptions->execute();
                $activeSubCount = $activeSubscriptions->get_result()->fetch_assoc()['count'];
                
                $classBookings = $conn->prepare("SELECT COUNT(*) as count FROM tbl_bookings WHERE member_email = ?");
                $classBookings->bind_param("s", $mEmail);
                $classBookings->execute();
                $classBookingCount = $classBookings->get_result()->fetch_assoc()['count'];
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
                    <div class="card text-center bg-info text-white">
                        <div class="card-body">
                            <h3><?php echo $classBookingCount; ?></h3>
                            <p>Class Bookings</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center bg-warning text-white">
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
                $sql .= " AND subscription_end_date >= CURDATE() AND subscription_end_date IS NOT NULL";
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
                    <tr style="color: white !important;">
                        <th>Booking ID</th>
                        <th>Name</th>
                        <th>Package/Class</th>
                        <th>Trainer</th>
                        <th>Date</th>
                        <th>Subscription</th>
                        <th>Expires On</th>
                        <th>Payment Status</th>
                        <th>Status</th>
                        <th>Source</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="table-dark-text">';

                while ($row = $result->fetch_assoc()) {
                    $bookingDate = new DateTime($row["member_date"]);
                    $today = new DateTime('today');
                    
                    $subscriptionEnd = null;
                    if (!empty($row["subscription_end_date"]) && $row["subscription_end_date"] != '0000-00-00') {
                        $subscriptionEnd = new DateTime($row["subscription_end_date"]);
                    }
                    
                    $status = '';
                    $statusClass = '';
                    $canCancel = false;

                    if ($bookingDate < $today) {
                        $status = 'Completed';
                        $statusClass = 'badge-secondary';
                    } elseif ($bookingDate->format('Y-m-d') == $today->format('Y-m-d')) {
                        $status = 'Today';
                        $statusClass = 'badge-warning';
                    } else {
                        $status = 'Upcoming';
                        $statusClass = 'badge-success';
                        $canCancel = true;
                    }
                    
                    $subscriptionStatus = '';
                    $subscriptionClass = '';
                    $subscriptionEndDisplay = 'N/A';
                    
                    if ($subscriptionEnd !== null) {
                        $subscriptionEndDisplay = $subscriptionEnd->format('Y-m-d');
                        
                        if ($subscriptionEnd >= $today) {
                            $interval = $today->diff($subscriptionEnd);
                            $daysRemaining = $interval->days;
                            $subscriptionStatus = 'Active (' . $daysRemaining . ' days left)';
                            $subscriptionClass = 'badge-success';
                        } else {
                            $expiredDays = $today->diff($subscriptionEnd)->days;
                            $subscriptionStatus = 'Expired (' . $expiredDays . ' days ago)';
                            $subscriptionClass = 'badge-danger';
                        }
                    } else {
                        $subscriptionStatus = 'No Subscription';
                        $subscriptionClass = 'badge-secondary';
                    }
                    
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
                    
                    // Check if this is a class booking
                    $isClassBooking = false;
                    $classCheck = $conn->prepare("SELECT * FROM tbl_bookings WHERE member_email = ? AND class_date = ? AND class_title = ?");
                    $classCheck->bind_param("sss", $mEmail, $row["member_date"], $row["booking_type"]);
                    $classCheck->execute();
                    $classResult = $classCheck->get_result();
                    if ($classResult->num_rows > 0) {
                        $isClassBooking = true;
                    }
                    $classCheck->close();

                    echo '<tr>
                        <td><strong>#' . $row["Booking_id"] . '</strong></td>
                        <td>' . htmlspecialchars($row["member_name"]) . '</td>
                        <td><span class="badge ' . ($isClassBooking ? 'badge-info' : 'badge-primary') . '">' . htmlspecialchars($row["booking_type"]) . '</span></td>
                        <td>' . htmlspecialchars($row["trainer"]) . '</td>
                        <td>' . $bookingDate->format('Y-m-d') . '</td>
                        <td>' . htmlspecialchars($row["subscription_months"]) . ' Month(s)</td>
                        <td>' . $subscriptionEndDisplay . '<br><span class="badge ' . $subscriptionClass . '">' . $subscriptionStatus . '</span></td>
                        <td>' . $paymentBadge . '</td>
                        <td><span class="badge ' . $statusClass . '">' . $status . '</span></td>
                        <td><span class="badge ' . ($isClassBooking ? 'badge-success' : 'badge-secondary') . '">' . ($isClassBooking ? 'Class Schedule' : 'Direct Booking') . '</span></td>
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
                        <a href="viewschedule.php" class="btn btn-primary">Book a Class</a>
                      </div>';
            }

            $stmt->close();
            ?>

            <!-- Quick Actions -->
            <div class="text-center mt-4">
                <a href="viewschedule.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus"></i> Book New Class
                </a>
                <a href="SubmitBooking.php" class="btn btn-info btn-lg ml-2">
                    <i class="fas fa-calendar"></i> Direct Booking
                </a>
            </div>
        </div>

        <!-- Class Bookings Tab -->
        <div class="tab-pane fade" id="schedule-bookings" role="tabpanel">
            <div class="text-center mt-3">
                <p class="bg-info text-white p-2 mb-4">CLASS SCHEDULE BOOKINGS</p>
            </div>

            <?php
            $classBookingsSql = "SELECT cb.*, e.start, e.end, e.trainer as event_trainer, e.capacity,
                                        CASE 
                                            WHEN DATE(e.start) < CURDATE() THEN 'completed'
                                            WHEN DATE(e.start) = CURDATE() THEN 'today'
                                            ELSE 'upcoming'
                                        END as class_status
                                 FROM tbl_bookings cb 
                                 LEFT JOIN tbl_events e ON cb.class_id = e.id 
                                 WHERE cb.member_email = ? 
                                 ORDER BY cb.class_date DESC";
            $classStmt = $conn->prepare($classBookingsSql);
            $classStmt->bind_param("s", $mEmail);
            $classStmt->execute();
            $classResult = $classStmt->get_result();

            if ($classResult->num_rows > 0) {
                echo '<div class="row">';
                while ($classRow = $classResult->fetch_assoc()) {
                    $classDate = new DateTime($classRow["class_date"]);
                    $classTime = $classRow["class_time"];
                    $bookingDate = new DateTime($classRow["booking_date"]);
                    
                    $statusClass = '';
                    $statusIcon = '';
                    $statusText = '';
                    
                    switch ($classRow['class_status']) {
                        case 'completed':
                            $statusClass = 'border-secondary';
                            $statusIcon = 'fas fa-check-circle text-secondary';
                            $statusText = 'Completed';
                            break;
                        case 'today':
                            $statusClass = 'border-warning';
                            $statusIcon = 'fas fa-clock text-warning';
                            $statusText = 'Today';
                            break;
                        case 'upcoming':
                            $statusClass = 'border-success';
                            $statusIcon = 'fas fa-calendar text-success';
                            $statusText = 'Upcoming';
                            break;
                    }

                    echo '<div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 ' . $statusClass . '">
                    <div class="card-header bg-light" style="color: black;">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-dumbbell text-primary"></i> ' . htmlspecialchars($classRow["class_title"]) . '
                        </h5>
                    </div>
                    <div class="card-body" style="color: black;">
                        <div class="mb-2">
                            <i class="fas fa-calendar text-muted"></i> 
                            <strong>' . $classDate->format('l, F j, Y') . '</strong>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-clock text-muted"></i> 
                            ' . date('h:i A', strtotime($classTime)) . '
                        </div>';

                    if (!empty($classRow["event_trainer"])) {
                        echo '<div class="mb-2">
                                <i class="fas fa-user text-muted"></i> 
                                Trainer: ' . htmlspecialchars($classRow["event_trainer"]) . '
                              </div>';
                    }

                    echo '<div class="mb-2">
                            <i class="fas fa-bookmark text-muted"></i> 
                            Booked on: ' . $bookingDate->format('M j, Y') . '
                          </div>
                          <div class="mb-3">
                            <i class="' . $statusIcon . '"></i> 
                            <span><strong>' . $statusText . '</strong></span>
                          </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 text-center" style="color: black;">
                            <span class="badge badge-info">
                                <i class="fas fa-check"></i> Booked from Schedule
                            </span>
                        </div>
                    </div>
                  </div>';
                }
                echo '</div>';
            } else {
                echo '<div class="alert alert-info text-center" style="color: black;">
                        <h4><i class="fas fa-info-circle"></i> No Class Bookings Found</h4>
                        <p>You haven\'t booked any classes from the schedule yet.</p>
                        <a href="viewschedule.php" class="btn btn-primary">
                            <i class="fas fa-calendar"></i> View Class Schedule
                        </a>
                      </div>';
            }

            $classStmt->close();
            ?>
        </div>

        <!-- Profile Tab -->
        <div class="tab-pane fade" id="profile" role="tabpanel">
            <div class="row justify-content-center mt-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header table-dark-text">
                            <h3 class="text-center mb-0">Update Profile Information</h3>
                        </div>
                        <div class="card-body">
                            <?php if(isset($profileMsg)) {echo $profileMsg; } ?>
                            <form method="POST">
                                <div class="form-group">
                                    <label for="inputEmail">Email Address</label>
                                    <input type="email" class="form-control" id="inputEmail" value="<?php echo $mEmail ?>" readonly>
                                    <small class="form-text text-muted">Note: Email address cannot be changed</small>
                                </div>
                                <div class="form-group">
                                    <label for="inputName">Full Name</label>
                                    <input type="text" class="form-control" id="inputName" name="rName" required value="<?php echo $mName ?>">
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg" name="nameupdate">
                                        <i class="fas fa-save"></i> Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle tab switching with URL parameters
    if (window.location.hash) {
        $('.nav-tabs a[href="' + window.location.hash + '"]').tab('show');
    }
    
    // Update URL when tab is clicked
    $('.nav-tabs a').on('shown.bs.tab', function (e) {
        window.location.hash = e.target.hash;
    });
});
</script>

<?php include('includes/footer.php'); ?>