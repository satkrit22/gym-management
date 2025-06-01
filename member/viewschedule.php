<?php
define('TITLE', 'View Schedule');
define('PAGE', 'viewschedule');
include('includes/header.php');
include('../dbConnection.php');
session_start();

if($_SESSION['is_login']){
    $mEmail = $_SESSION['mEmail'];
} else {
    echo "<script> location.href='memberLogin.php'; </script>";
    exit();
}

// Get member name for booking
$memberSql = "SELECT m_name FROM memberlogin_tb WHERE m_email = ?";
$memberStmt = $conn->prepare($memberSql);
$memberStmt->bind_param("s", $mEmail);
$memberStmt->execute();
$memberResult = $memberStmt->get_result();
$memberName = $memberResult->fetch_assoc()['m_name'] ?? 'Unknown';

// Handle booking request
if (isset($_POST['book_class'])) {
    $class_id = $_POST['class_id'];
    $class_title = $_POST['class_title'];
    $class_date = $_POST['class_date'];
    $class_time = $_POST['class_time'];
    $trainer = $_POST['trainer'] ?? 'TBA';
    $subscription_months = $_POST['subscription_months'] ?? 1; // Default 1 month
    
    // Check if already booked in the main booking table
    $checkSql = "SELECT * FROM submitbookingt_tb WHERE member_email = ? AND booking_type = ? AND member_date = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("sss", $mEmail, $class_title, $class_date);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $error_msg = "You have already booked this class!";
    } else {
        // Calculate subscription end date
        $subscriptionEndDate = new DateTime($class_date);
        $subscriptionEndDate->add(new DateInterval('P' . $subscription_months . 'M'));
        
        // Insert into main booking table (submitbookingt_tb)
        $bookSql = "INSERT INTO submitbookingt_tb (
                        member_name, 
                        member_email, 
                        member_date, 
                        booking_type, 
                        trainer, 
                        subscription_months, 
                        subscription_end_date, 
                        payment_status, 
                        booking_date
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        
        $bookStmt = $conn->prepare($bookSql);
        $subscriptionEndStr = $subscriptionEndDate->format('Y-m-d');
        $bookStmt->bind_param("sssssis", 
            $memberName, 
            $mEmail, 
            $class_date, 
            $class_title, 
            $trainer, 
            $subscription_months, 
            $subscriptionEndStr
        );
        
        if ($bookStmt->execute()) {
            // Also insert into class bookings table for schedule tracking
            $classSql = "INSERT INTO tbl_bookings (member_email, class_id, class_title, class_date, class_time, booking_date) VALUES (?, ?, ?, ?, ?, NOW())";
            $classStmt = $conn->prepare($classSql);
            $classStmt->bind_param("sisss", $mEmail, $class_id, $class_title, $class_date, $class_time);
            $classStmt->execute();
            $classStmt->close();
            
            $success_msg = "Class booked successfully! You can view it in your booking dashboard.";
        } else {
            $error_msg = "Error booking class. Please try again.";
        }
        $bookStmt->close();
    }
    $checkStmt->close();
}

// Get current date for filtering
$currentDate = date('Y-m-d');
$filterDate = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';
$filterClass = isset($_GET['filter_class']) ? $_GET['filter_class'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gym Classes Schedule</title>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .calendar-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        #calendar {
            margin: 20px 0;
        }
        
        .fc-event {
            border-radius: 5px;
            border: none;
            padding: 2px 5px;
            font-weight: 500;
            cursor: pointer;
        }
        
        .fc-day-grid-event {
            margin: 1px 2px;
        }
        
        .fc-time-grid-event {
            border-radius: 3px;
        }
        
        .calendar-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            margin: -20px -20px 20px -20px;
        }
        
        .legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 15px 0;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 3px;
        }
        
        .schedule-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .class-card {
            border-left: 4px solid #28a745;
            transition: transform 0.2s;
        }
        
        .class-card:hover {
            transform: translateY(-2px);
        }
        
        .status-upcoming { color: #007bff; }
        .status-ongoing { color: #28a745; }
        .status-completed { color: #6c757d; }
        
        .book-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 20px;
            padding: 8px 20px;
            color: white;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .book-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }
        
        .booked-badge {
            background: linear-gradient(45deg, #ffc107, #fd7e14);
            color: white;
            border-radius: 15px;
            padding: 5px 15px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>

<div class="col-sm-9 col-md-10 mt-5">
    <div class="text-center">
        <p class="bg-dark text-white p-2 mb-4">GYM CLASSES SCHEDULE</p>
    </div>

    <?php if (isset($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
            <a href="memberProfile.php" class="btn btn-sm btn-outline-success ml-2">
                <i class="fas fa-eye"></i> View My Bookings
            </a>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Calendar Section -->
    <div class="calendar-container">
        <div class="calendar-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-calendar-alt"></i> Class Schedule Calendar</h2>
                    <p class="mb-0">View and book your favorite gym classes</p>
                </div>
                <div class="col-md-4 text-right">
                    <a href="memberProfile.php" class="btn btn-light">
                        <i class="fas fa-bookmark"></i> My Bookings
                    </a>
                    <button class="btn btn-outline-light ml-2" onclick="refreshCalendar()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Calendar Legend -->
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background-color: #28a745;"></div>
                <span>Available Classes</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #17a2b8;"></div>
                <span>Booked Classes</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #ffc107;"></div>
                <span>Today's Classes</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #6c757d;"></div>
                <span>Past Classes</span>
            </div>
        </div>

        <!-- Calendar -->
        <div id="calendar"></div>
    </div>

    <!-- Schedule List Section -->
    <div class="schedule-section">
        <h3 class="mb-4"><i class="fas fa-list"></i> Class Schedule List</h3>
        
        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Classes</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="viewschedule.php" class="row">
                    <div class="col-md-4">
                        <label for="filter_date"><i class="fas fa-calendar"></i> Filter by Date:</label>
                        <input type="date" class="form-control" id="filter_date" name="filter_date" 
                               value="<?php echo htmlspecialchars($filterDate); ?>" min="<?php echo $currentDate; ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="filter_class"><i class="fas fa-dumbbell"></i> Filter by Class:</label>
                        <select class="form-control" id="filter_class" name="filter_class">
                            <option value="">All Classes</option>
                            <option value="Yoga Class" <?php echo ($filterClass == 'Yoga Class') ? 'selected' : ''; ?>>Yoga Class</option>
                            <option value="Zumba Class" <?php echo ($filterClass == 'Zumba Class') ? 'selected' : ''; ?>>Zumba Class</option>
                            <option value="Cardio Class" <?php echo ($filterClass == 'Cardio Class') ? 'selected' : ''; ?>>Cardio Class</option>
                            <option value="Weight Lifting" <?php echo ($filterClass == 'Weight Lifting') ? 'selected' : ''; ?>>Weight Lifting</option>
                            <option value="Endurance Training" <?php echo ($filterClass == 'Endurance Training') ? 'selected' : ''; ?>>Endurance Training</option>
                            <option value="Personal Training" <?php echo ($filterClass == 'Personal Training') ? 'selected' : ''; ?>>Personal Training</option>
                            <option value="Group Fitness" <?php echo ($filterClass == 'Group Fitness') ? 'selected' : ''; ?>>Group Fitness</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="viewschedule.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Schedule Cards/Table -->
        <?php
        // Build SQL query with filters - check both booking tables
        $sql = "SELECT e.*, 
                       CASE WHEN (b.id IS NOT NULL OR sb.Booking_id IS NOT NULL) THEN 1 ELSE 0 END as is_booked
                FROM tbl_events e 
                LEFT JOIN tbl_bookings b ON e.id = b.class_id AND b.member_email = ?
                LEFT JOIN submitbookingt_tb sb ON e.title = sb.booking_type AND DATE(e.start) = sb.member_date AND sb.member_email = ?
                WHERE 1=1";
        $params = array($mEmail, $mEmail);
        $types = "ss";

        if (!empty($filterDate)) {
            $sql .= " AND DATE(e.start) = ?";
            $params[] = $filterDate;
            $types .= "s";
        }

        if (!empty($filterClass)) {
            $sql .= " AND e.title = ?";
            $params[] = $filterClass;
            $types .= "s";
        }

        // Only show future or today's classes
        $sql .= " AND DATE(e.start) >= ? ORDER BY e.start ASC";
        $params[] = $currentDate;
        $types .= "s";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0){
            echo '<div class="row">';
            $cardCount = 0;
            
            while($row = $result->fetch_assoc()){
                $startDateTime = new DateTime($row["start"]);
                $endDateTime = new DateTime($row["end"]);
                $currentDateTime = new DateTime();
                
                // Calculate duration
                $interval = $startDateTime->diff($endDateTime);
                $duration = $interval->format('%h hours %i minutes');
                if ($interval->h == 0) {
                    $duration = $interval->format('%i minutes');
                }
                
                // Determine status
                $status = '';
                $statusClass = '';
                $statusIcon = '';
                if ($currentDateTime > $endDateTime) {
                    $status = 'Completed';
                    $statusClass = 'status-completed';
                    $statusIcon = 'fas fa-check-circle';
                } elseif ($currentDateTime >= $startDateTime && $currentDateTime <= $endDateTime) {
                    $status = 'Ongoing';
                    $statusClass = 'status-ongoing';
                    $statusIcon = 'fas fa-play-circle';
                } else {
                    $status = 'Upcoming';
                    $statusClass = 'status-upcoming';
                    $statusIcon = 'fas fa-clock';
                }

                $isToday = $startDateTime->format('Y-m-d') == date('Y-m-d');
                $cardClass = $isToday ? 'border-warning' : '';
                
                echo '<div class="col-md-6 col-lg-4 mb-4">
                        <div class="card class-card h-100 ' . $cardClass . '">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-dumbbell text-primary"></i> ' . htmlspecialchars($row["title"]) . '
                                    ' . ($isToday ? '<span class="badge badge-warning ml-2">Today</span>' : '') . '
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fas fa-calendar text-muted"></i> 
                                    <strong>' . $startDateTime->format('l, F j, Y') . '</strong>
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-clock text-muted"></i> 
                                    ' . $startDateTime->format('h:i A') . ' - ' . $endDateTime->format('h:i A') . '
                                    <small class="text-muted">(' . $duration . ')</small>
                                </div>
                                ' . (!empty($row["trainer"]) ? '<div class="mb-2">
                                    <i class="fas fa-user text-muted"></i> 
                                    Trainer: ' . htmlspecialchars($row["trainer"]) . '
                                </div>' : '') . '
                                <div class="mb-2">
                                    <i class="fas fa-users text-muted"></i> 
                                    Capacity: ' . ($row["capacity"] ?? 20) . ' people
                                </div>
                                <div class="mb-3">
                                    <i class="' . $statusIcon . ' ' . $statusClass . '"></i> 
                                    <span class="' . $statusClass . '"><strong>' . $status . '</strong></span>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0">';
                
                if ($status == 'Upcoming') {
                    if ($row['is_booked']) {
                        echo '<span class="booked-badge">
                                <i class="fas fa-check"></i> Already Booked
                              </span>
                              <a href="memberProfile.php" class="btn btn-sm btn-outline-info mt-2">
                                <i class="fas fa-eye"></i> View Booking
                              </a>';
                    } else {
                        echo '<button type="button" class="btn book-btn btn-block" data-toggle="modal" data-target="#bookingModal" 
                                data-class-id="' . $row["id"] . '"
                                data-class-title="' . htmlspecialchars($row["title"]) . '"
                                data-class-date="' . $startDateTime->format('Y-m-d') . '"
                                data-class-time="' . $startDateTime->format('H:i') . '"
                                data-trainer="' . htmlspecialchars($row["trainer"] ?? 'TBA') . '">
                                <i class="fas fa-calendar-plus"></i> Book This Class
                              </button>';
                    }
                } else {
                    echo '<span class="text-muted">
                            <i class="fas fa-ban"></i> Booking Not Available
                          </span>';
                }
                
                echo '</div></div></div>';
                
                $cardCount++;
            }
            
            echo '</div>';
        } else {
            echo '<div class="alert alert-info text-center">
                    <h4><i class="fas fa-info-circle"></i> No Classes Found</h4>
                    <p>No classes match your filter criteria or there are no upcoming classes scheduled.</p>
                  </div>';
        }

        $stmt->close();
        ?>

        <!-- Quick Stats -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <?php
                            // Get stats
                            $todayClasses = $conn->query("SELECT COUNT(*) as count FROM tbl_events WHERE DATE(start) = CURDATE() AND start >= NOW()")->fetch_assoc()['count'];
                            $upcomingClasses = $conn->query("SELECT COUNT(*) as count FROM tbl_events WHERE start > NOW()")->fetch_assoc()['count'];
                            $myBookings = $conn->query("SELECT COUNT(*) as count FROM submitbookingt_tb WHERE member_email = '$mEmail'")->fetch_assoc()['count'];
                            $todayBookings = $conn->query("SELECT COUNT(*) as count FROM submitbookingt_tb WHERE member_email = '$mEmail' AND member_date = CURDATE()")->fetch_assoc()['count'];
                            ?>
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h3><i class="fas fa-calendar-day"></i></h3>
                                        <h4><?php echo $todayClasses; ?></h4>
                                        <p class="mb-0">Today's Classes</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h3><i class="fas fa-clock"></i></h3>
                                        <h4><?php echo $upcomingClasses; ?></h4>
                                        <p class="mb-0">Upcoming Classes</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h3><i class="fas fa-bookmark"></i></h3>
                                        <h4><?php echo $myBookings; ?></h4>
                                        <p class="mb-0">My Bookings</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h3><i class="fas fa-star"></i></h3>
                                        <h4><?php echo $todayBookings; ?></h4>
                                        <p class="mb-0">Today's Bookings</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Book Class</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="class_id" id="modal_class_id">
                    <input type="hidden" name="class_title" id="modal_class_title">
                    <input type="hidden" name="class_date" id="modal_class_date">
                    <input type="hidden" name="class_time" id="modal_class_time">
                    <input type="hidden" name="trainer" id="modal_trainer">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-dumbbell"></i> Class:</strong><br><span id="display_class_title"></span></p>
                            <p><strong><i class="fas fa-calendar"></i> Date:</strong><br><span id="display_class_date"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-clock"></i> Time:</strong><br><span id="display_class_time"></span></p>
                            <p><strong><i class="fas fa-user"></i> Trainer:</strong><br><span id="display_trainer"></span></p>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="subscription_months"><i class="fas fa-calendar-alt"></i> Subscription Duration:</label>
                        <select class="form-control" name="subscription_months" id="subscription_months" required>
                            <option value="">Choose Duration</option>
                            <option value="1">1 Month (30 days)</option>
                            <option value="3">3 Months (90 days)</option>
                            <option value="6">6 Months (180 days)</option>
                            <option value="12">12 Months (365 days)</option>
                        </select>
                        <small class="form-text text-muted">This will create a subscription that includes access to this class type.</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Note:</strong> Booking this class will create a subscription that allows you to attend similar classes during the selected period.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="book_class" class="btn btn-success">
                        <i class="fas fa-calendar-plus"></i> Confirm Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle booking modal
    $('#bookingModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var classId = button.data('class-id');
        var classTitle = button.data('class-title');
        var classDate = button.data('class-date');
        var classTime = button.data('class-time');
        var trainer = button.data('trainer');
        
        var modal = $(this);
        modal.find('#modal_class_id').val(classId);
        modal.find('#modal_class_title').val(classTitle);
        modal.find('#modal_class_date').val(classDate);
        modal.find('#modal_class_time').val(classTime);
        modal.find('#modal_trainer').val(trainer);
        
        modal.find('#display_class_title').text(classTitle);
        modal.find('#display_class_date').text(new Date(classDate).toLocaleDateString());
        modal.find('#display_class_time').text(classTime);
        modal.find('#display_trainer').text(trainer);
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
});

function refreshCalendar() {
    location.reload();
}
</script>

</body>
</html>

<?php include('includes/footer.php'); ?>