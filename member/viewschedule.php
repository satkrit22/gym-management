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

// Handle booking request
if (isset($_POST['book_class'])) {
    $class_id = $_POST['class_id'];
    $class_title = $_POST['class_title'];
    $class_date = $_POST['class_date'];
    $class_time = $_POST['class_time'];
    
    // Check if already booked
    $checkSql = "SELECT * FROM tbl_bookings WHERE member_email = ? AND class_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("si", $mEmail, $class_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $error_msg = "You have already booked this class!";
    } else {
        // Insert booking
        $bookSql = "INSERT INTO tbl_bookings (member_email, class_id, class_title, class_date, class_time, booking_date) VALUES (?, ?, ?, ?, ?, NOW())";
        $bookStmt = $conn->prepare($bookSql);
        $bookStmt->bind_param("sisss", $mEmail, $class_id, $class_title, $class_date, $class_time);
        
        if ($bookStmt->execute()) {
            $success_msg = "Class booked successfully!";
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
                    <button class="btn btn-light" onclick="viewTodayClasses()">
                        <i class="fas fa-calendar-day"></i> Today's Classes
                    </button>
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
        // Build SQL query with filters
        $sql = "SELECT e.*, 
                       CASE WHEN b.id IS NOT NULL THEN 1 ELSE 0 END as is_booked
                FROM tbl_events e 
                LEFT JOIN tbl_bookings b ON e.id = b.class_id AND b.member_email = ?
                WHERE 1=1";
        $params = array($mEmail);
        $types = "s";

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
                              </span>';
                    } else {
                        echo '<form method="POST" class="d-inline">
                                <input type="hidden" name="class_id" value="' . $row["id"] . '">
                                <input type="hidden" name="class_title" value="' . htmlspecialchars($row["title"]) . '">
                                <input type="hidden" name="class_date" value="' . $startDateTime->format('Y-m-d') . '">
                                <input type="hidden" name="class_time" value="' . $startDateTime->format('H:i') . '">
                                <button type="submit" name="book_class" class="btn book-btn btn-block">
                                    <i class="fas fa-calendar-plus"></i> Book This Class
                                </button>
                              </form>';
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
                            $myBookings = $conn->query("SELECT COUNT(*) as count FROM tbl_bookings WHERE member_email = '$mEmail'")->fetch_assoc()['count'];
                            $todayBookings = $conn->query("SELECT COUNT(*) as count FROM tbl_bookings b JOIN tbl_events e ON b.class_id = e.id WHERE b.member_email = '$mEmail' AND DATE(e.start) = CURDATE()")->fetch_assoc()['count'];
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

<!-- Class Details Modal -->
<div class="modal fade" id="classModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="classModalTitle">Class Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="classModalBody">
                <!-- Class details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <div id="classModalActions">
                    <!-- Action buttons will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    var today = moment();
    
    var calendar = $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        defaultView: 'month',
        editable: false,
        selectable: false,
        events: "fetch-user-events.php",
        
        eventRender: function (event, element, view) {
            // Add custom styling based on event properties
            if (event.color) {
                element.css('background-color', event.color);
                element.css('border-color', event.color);
            }
            
            // Add tooltip with event details
            element.attr('title', event.title + 
                (event.trainer ? '\nTrainer: ' + event.trainer : '') +
                (event.capacity ? '\nCapacity: ' + event.capacity : '') +
                (event.is_booked ? '\n✓ Booked' : '\n○ Available'));
            
            // Style today's events differently
            if (moment(event.start).isSame(today, 'day')) {
                element.css('background-color', '#ffc107');
                element.css('border-color', '#ffc107');
            }
            
            // Style booked events
            if (event.is_booked) {
                element.css('background-color', '#17a2b8');
                element.css('border-color', '#17a2b8');
            }
        },
        
        eventClick: function (event) {
            showClassDetails(event);
        }
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
});

function showClassDetails(event) {
    var startTime = moment(event.start).format('MMMM Do YYYY, h:mm A');
    var endTime = moment(event.end).format('h:mm A');
    var duration = moment(event.end).diff(moment(event.start), 'minutes');
    var durationText = Math.floor(duration / 60) + ' hours ' + (duration % 60) + ' minutes';
    
    var status = '';
    var statusClass = '';
    var now = moment();
    
    if (now.isAfter(moment(event.end))) {
        status = 'Completed';
        statusClass = 'badge-secondary';
    } else if (now.isBetween(moment(event.start), moment(event.end))) {
        status = 'Ongoing';
        statusClass = 'badge-success';
    } else {
        status = 'Upcoming';
        statusClass = 'badge-primary';
    }
    
    $('#classModalTitle').text(event.title);
    $('#classModalBody').html(`
        <div class="row">
            <div class="col-md-6">
                <p><strong><i class="fas fa-calendar"></i> Date:</strong><br>${startTime}</p>
                <p><strong><i class="fas fa-clock"></i> Duration:</strong><br>${durationText}</p>
                <p><strong><i class="fas fa-info-circle"></i> Status:</strong><br><span class="badge ${statusClass}">${status}</span></p>
            </div>
            <div class="col-md-6">
                ${event.trainer ? `<p><strong><i class="fas fa-user"></i> Trainer:</strong><br>${event.trainer}</p>` : ''}
                <p><strong><i class="fas fa-users"></i> Capacity:</strong><br>${event.capacity || 20} people</p>
                ${event.is_booked ? '<p><strong><i class="fas fa-check"></i> Booking Status:</strong><br><span class="badge badge-info">Booked</span></p>' : ''}
            </div>
        </div>
        ${event.description ? `<div class="mt-3"><strong>Description:</strong><br>${event.description}</div>` : ''}
    `);
    
    var actions = '';
    if (status === 'Upcoming' && !event.is_booked) {
        actions = `<form method="POST" class="d-inline">
                    <input type="hidden" name="class_id" value="${event.id}">
                    <input type="hidden" name="class_title" value="${event.title}">
                    <input type="hidden" name="class_date" value="${moment(event.start).format('YYYY-MM-DD')}">
                    <input type="hidden" name="class_time" value="${moment(event.start).format('HH:mm')}">
                    <button type="submit" name="book_class" class="btn btn-success">
                        <i class="fas fa-calendar-plus"></i> Book This Class
                    </button>
                   </form>`;
    } else if (event.is_booked) {
        actions = '<span class="badge badge-info"><i class="fas fa-check"></i> Already Booked</span>';
    }
    
    $('#classModalActions').html(actions);
    $('#classModal').modal('show');
}

function viewTodayClasses() {
    $('#calendar').fullCalendar('gotoDate', moment());
    $('#calendar').fullCalendar('changeView', 'agendaDay');
}

function refreshCalendar() {
    $('#calendar').fullCalendar('refetchEvents');
    location.reload();
}
</script>

</body>
</html>

<?php
include('includes/footer.php'); 
?>