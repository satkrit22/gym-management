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

// Handle subscription extension
if (isset($_POST['extend_subscription'])) {
    $booking_id = $_POST['booking_id'];
    $extend_months = (int)$_POST['extend_months'];
    
    // Validate that extend_months is one of the allowed values
    if (!in_array($extend_months, [1, 3, 6, 12])) {
        $error_msg = "Invalid subscription period selected.";
    } else {
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
            
            // Calculate days based on selected months
            $newEndDate->add(new DateInterval('P' . $extend_months . 'M'));
            
            // Calculate days for display
            $days_to_add = 0;
            switch($extend_months) {
                case 1:
                    $days_to_add = 30;
                    break;
                case 3:
                    $days_to_add = 90;
                    break;
                case 6:
                    $days_to_add = 180;
                    break;
                case 12:
                    $days_to_add = 365;
                    break;
            }
            
            // Update subscription end date
            $updateSql = "UPDATE submitbookingt_tb SET subscription_end_date = ?, subscription_months = subscription_months + ? WHERE Booking_id = ? AND member_email = ?";
            $updateStmt = $conn->prepare($updateSql);
            $newEndDateStr = $newEndDate->format('Y-m-d');
            $updateStmt->bind_param("siis", $newEndDateStr, $extend_months, $booking_id, $mEmail);
            
            if ($updateStmt->execute()) {
                $success_msg = "Subscription extended successfully! New expiry date: " . $newEndDate->format('Y-m-d') . " (+" . $days_to_add . " days)";
            } else {
                $error_msg = "Error extending subscription.";
            }
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
/* Custom CSS for black table text */
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

            <?php if (isset($success_msg)): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            
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
                
                // Fixed active subscriptions query - check if subscription_end_date is greater than or equal to today
                $activeSubscriptions = $conn->prepare("SELECT COUNT(*) as count FROM submitbookingt_tb WHERE member_email = ? AND subscription_end_date >= CURDATE() AND subscription_end_date IS NOT NULL");
                $activeSubscriptions->bind_param("s", $mEmail);
                $activeSubscriptions->execute();
                $activeSubCount = $activeSubscriptions->get_result()->fetch_assoc()['count'];
                
                // Get class bookings count
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
                <table class="table table-bordered table-hover table-striped ">
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
                    
                    // Fixed subscription end date handling
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
                    
                    // Fixed subscription status calculation
                    $subscriptionStatus = '';
                    $subscriptionClass = '';
                    $daysRemaining = 0;
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
                    
                    // Add extend subscription button for active subscriptions
                    if ($subscriptionEnd !== null && $subscriptionEnd >= $today) {
                        echo '<br><button type="button" class="btn btn-info btn-sm mt-1 extend-subscription" data-toggle="modal" data-target="#extendModal" data-id="' . $row["Booking_id"] . '">
                                <i class="fas fa-plus"></i> Extend
                              </button>';
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

        <!-- Class Bookings Tab -->
        <div class="tab-pane fade" id="schedule-bookings" role="tabpanel">
            <div class="text-center mt-3">
                <p class="bg-info text-white p-2 mb-4">CLASS SCHEDULE BOOKINGS</p>
            </div>

            <?php
            // Get class bookings with event details
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
                echo '</div>'; // Close .row
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
                        <select class="form-control" name="extend_months" required id="extend_months">
                            <option value="">Choose Duration</option>
                            <option value="1">1 Month (30 days)</option>
                            <option value="3">3 Months (90 days)</option>
                            <option value="6">6 Months (180 days)</option>
                            <option value="12">12 Months (365 days)</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <small>Your subscription will be extended from the current expiry date or today's date if already expired.</small>
                    </div>
                    <div id="subscription_preview" class="mt-3" style="display: none;">
                        <h6>Subscription Preview:</h6>
                        <div class="d-flex justify-content-between">
                            <span>Duration:</span>
                            <span id="preview_duration"></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Days Added:</span>
                            <span id="preview_days"></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>New Expiry Date:</span>
                            <span id="preview_date"></span>
                        </div>
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
    
    // Handle tab switching with URL parameters
    if (window.location.hash) {
        $('.nav-tabs a[href="' + window.location.hash + '"]').tab('show');
    }
    
    // Update URL when tab is clicked
    $('.nav-tabs a').on('shown.bs.tab', function (e) {
        window.location.hash = e.target.hash;
    });
    
    // Subscription extension preview
    $('#extend_months').change(function() {
        const months = $(this).val();
        if (!months) {
            $('#subscription_preview').hide();
            return;
        }
        
        let days = 0;
        let durationText = '';
        
        switch(parseInt(months)) {
            case 1:
                days = 30;
                durationText = '1 Month';
                break;
            case 3:
                days = 90;
                durationText = '3 Months';
                break;
            case 6:
                days = 180;
                durationText = '6 Months';
                break;
            case 12:
                days = 365;
                durationText = '12 Months (1 Year)';
                break;
        }
        
        // Calculate new expiry date
        const today = new Date();
        const expiryDate = new Date(today);
        expiryDate.setDate(today.getDate() + days);
        
        $('#preview_duration').text(durationText);
        $('#preview_days').text(days + ' days');
        $('#preview_date').text(expiryDate.toLocaleDateString());
        $('#subscription_preview').show();
    });
});
</script>

<?php include('includes/footer.php'); ?>