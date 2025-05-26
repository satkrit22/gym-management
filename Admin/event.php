<?php
define('TITLE', 'Gym Schedule Calendar');
define('PAGE', 'Event');
 
include('../dbConnection.php');
session_start();

if (!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

$aEmail = $_SESSION['aEmail'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gym Schedule Calendar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
            margin-left: 17%;
            max-width: 1200px;
        }
        
        #calendar {
            margin: 20px 0;
        }
        
        .fc-event {
            border-radius: 5px;
            border: none;
            padding: 2px 5px;
            font-weight: 500;
        }
        
        .fc-day-grid-event {
            margin: 1px 2px;
        }
        
        .fc-time-grid-event {
            border-radius: 3px;
        }
        
        .response {
            margin: 20px 0;
            text-align: center;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px 20px;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            display: inline-block;
            margin: 10px 0;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px 20px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            display: inline-block;
            margin: 10px 0;
        }
        
        .calendar-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            margin: -20px -20px 20px -20px;
        }
        
        .btn-calendar {
            margin: 5px;
        }
        
        .fc-past {
            background-color: #f8f9fa !important;
            color: #6c757d !important;
        }
        
        .fc-disabled {
            background-color: #e9ecef !important;
            color: #adb5bd !important;
            cursor: not-allowed !important;
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
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div class="container-fluid">
        <div class="calendar-container">
            <div class="calendar-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2><i class="fas fa-calendar-alt"></i> Gym Schedule Calendar</h2>
                        <p class="mb-0">Manage your gym classes and training sessions</p>
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="view_schedule.php" class="btn btn-light btn-calendar">
                            <i class="fas fa-list"></i> List View
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-light btn-calendar">
                            <i class="fas fa-arrow-left"></i> Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Calendar Legend -->
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #28a745;"></div>
                    <span>Available for Booking</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #17a2b8;"></div>
                    <span>Scheduled Classes</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #ffc107;"></div>
                    <span>Today's Classes</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #6c757d;"></div>
                    <span>Past/Unavailable</span>
                </div>
            </div>

            <!-- Response Messages -->
            <div class="response"></div>

            <!-- Calendar -->
            <div id="calendar"></div>

            <!-- Quick Actions -->
            <div class="text-center mt-4">
                <button class="btn btn-primary btn-lg" onclick="addQuickEvent()">
                    <i class="fas fa-plus"></i> Quick Add Class
                </button>
                <button class="btn btn-info btn-lg ml-2" onclick="viewTodayEvents()">
                    <i class="fas fa-calendar-day"></i> Today's Classes
                </button>
                <button class="btn btn-success btn-lg ml-2" onclick="refreshCalendar()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Add/Edit Event Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalTitle">Add New Class</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="eventForm">
                        <input type="hidden" id="eventId" name="eventId">
                        <div class="form-group">
                            <label for="eventTitle">Class Type</label>
                            <select class="form-control" id="eventTitle" name="eventTitle" required>
                                <option value="">Select Class Type</option>
                                <option value="Yoga Class">Yoga Class</option>
                                <option value="Zumba Class">Zumba Class</option>
                                <option value="Cardio Class">Cardio Class</option>
                                <option value="Weight Lifting">Weight Lifting</option>
                                <option value="Endurance Training">Endurance Training</option>
                                <option value="Personal Training">Personal Training</option>
                                <option value="Group Fitness">Group Fitness</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="eventDate">Date</label>
                                    <input type="date" class="form-control" id="eventDate" name="eventDate" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="eventDuration">Duration (minutes)</label>
                                    <select class="form-control" id="eventDuration" name="eventDuration" required>
                                        <option value="30">30 minutes</option>
                                        <option value="45">45 minutes</option>
                                        <option value="60" selected>60 minutes</option>
                                        <option value="90">90 minutes</option>
                                        <option value="120">120 minutes</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="eventStartTime">Start Time</label>
                                    <input type="time" class="form-control" id="eventStartTime" name="eventStartTime" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="eventTrainer">Trainer</label>
                                    <input type="text" class="form-control" id="eventTrainer" name="eventTrainer" placeholder="Trainer Name">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="eventCapacity">Capacity</label>
                                    <input type="number" class="form-control" id="eventCapacity" name="eventCapacity" value="20" min="1" max="100">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="eventColor">Color</label>
                                    <select class="form-control" id="eventColor" name="eventColor">
                                        <option value="#17a2b8">Blue (Default)</option>
                                        <option value="#28a745">Green</option>
                                        <option value="#ffc107">Yellow</option>
                                        <option value="#dc3545">Red</option>
                                        <option value="#6f42c1">Purple</option>
                                        <option value="#fd7e14">Orange</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="eventDescription">Description (Optional)</label>
                            <textarea class="form-control" id="eventDescription" name="eventDescription" rows="2" placeholder="Additional details about the class"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="deleteEventBtn" style="display: none;" onclick="deleteEvent()">Delete</button>
                    <button type="button" class="btn btn-primary" onclick="saveEvent()">Save Class</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Get current date and one month from now
            var today = moment();
            var oneMonthFromNow = moment().add(1, 'month');
            
            // Set minimum date for date input
            $('#eventDate').attr('min', today.format('YYYY-MM-DD'));
            $('#eventDate').attr('max', oneMonthFromNow.format('YYYY-MM-DD'));
            
            var calendar = $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                defaultView: 'month',
                editable: true,
                selectable: true,
                selectHelper: true,
                events: "fetch-event.php",
                
                // Restrict calendar to current month and next month only
                validRange: {
                    start: today.format('YYYY-MM-DD'),
                    end: oneMonthFromNow.format('YYYY-MM-DD')
                },
                
                // Disable past dates
                dayRender: function(date, cell) {
                    if (date.isBefore(today, 'day')) {
                        cell.addClass('fc-past fc-disabled');
                        cell.css('pointer-events', 'none');
                    }
                },
                
                eventRender: function (event, element, view) {
                    // Add custom styling based on event properties
                    if (event.color) {
                        element.css('background-color', event.color);
                        element.css('border-color', event.color);
                    }
                    
                    // Add tooltip with event details
                    element.attr('title', event.title + 
                        (event.trainer ? '\nTrainer: ' + event.trainer : '') +
                        (event.capacity ? '\nCapacity: ' + event.capacity : ''));
                    
                    // Style today's events differently
                    if (moment(event.start).isSame(today, 'day')) {
                        element.addClass('today-event');
                        element.css('background-color', '#ffc107');
                        element.css('border-color', '#ffc107');
                    }
                },
                
                select: function (start, end, allDay) {
                    var selectedDate = moment(start);
                    
                    // Check if selected date is in the past
                    if (selectedDate.isBefore(today, 'day')) {
                        displayMessage("Cannot schedule classes for past dates!", 'error');
                        calendar.fullCalendar('unselect');
                        return;
                    }
                    
                    // Check if selected date is beyond one month
                    if (selectedDate.isAfter(oneMonthFromNow, 'day')) {
                        displayMessage("Cannot schedule classes beyond one month from today!", 'error');
                        calendar.fullCalendar('unselect');
                        return;
                    }
                    
                    // Open modal for new event
                    openEventModal(null, selectedDate.format('YYYY-MM-DD'));
                    calendar.fullCalendar('unselect');
                },
                
                eventClick: function (event) {
                    // Open modal for editing existing event
                    openEventModal(event);
                },
                
                eventDrop: function (event, delta) {
                    var newStart = moment(event.start);
                    
                    // Prevent moving to past dates
                    if (newStart.isBefore(today, 'day')) {
                        displayMessage("Cannot move classes to past dates!", 'error');
                        calendar.fullCalendar('revertEvent');
                        return;
                    }
                    
                    // Prevent moving beyond one month
                    if (newStart.isAfter(oneMonthFromNow, 'day')) {
                        displayMessage("Cannot move classes beyond one month from today!", 'error');
                        calendar.fullCalendar('revertEvent');
                        return;
                    }
                    
                    updateEventDateTime(event);
                },
                
                eventResize: function (event, delta) {
                    updateEventDateTime(event);
                }
            });
        });

        function openEventModal(event, selectedDate) {
            if (event) {
                // Edit existing event
                $('#eventModalTitle').text('Edit Class');
                $('#eventId').val(event.id);
                $('#eventTitle').val(event.title);
                $('#eventDate').val(moment(event.start).format('YYYY-MM-DD'));
                $('#eventStartTime').val(moment(event.start).format('HH:mm'));
                
                // Calculate duration
                var duration = moment(event.end).diff(moment(event.start), 'minutes');
                $('#eventDuration').val(duration);
                
                $('#eventTrainer').val(event.trainer || '');
                $('#eventCapacity').val(event.capacity || 20);
                $('#eventColor').val(event.color || '#17a2b8');
                $('#eventDescription').val(event.description || '');
                
                $('#deleteEventBtn').show();
            } else {
                // New event
                $('#eventModalTitle').text('Add New Class');
                $('#eventForm')[0].reset();
                $('#eventId').val('');
                $('#eventDate').val(selectedDate);
                $('#eventStartTime').val('09:00');
                $('#eventDuration').val('60');
                $('#eventCapacity').val('20');
                $('#eventColor').val('#17a2b8');
                $('#deleteEventBtn').hide();
            }
            
            $('#eventModal').modal('show');
        }

        function saveEvent() {
            var eventId = $('#eventId').val();
            var title = $('#eventTitle').val();
            var date = $('#eventDate').val();
            var startTime = $('#eventStartTime').val();
            var duration = parseInt($('#eventDuration').val());
            var trainer = $('#eventTrainer').val();
            var capacity = $('#eventCapacity').val();
            var color = $('#eventColor').val();
            var description = $('#eventDescription').val();
            
            if (!title || !date || !startTime) {
                displayMessage("Please fill in all required fields!", 'error');
                return;
            }
            
            // Create start and end datetime
            var startDateTime = moment(date + ' ' + startTime);
            var endDateTime = moment(startDateTime).add(duration, 'minutes');
            
            var eventData = {
                id: eventId,
                title: title,
                start: startDateTime.format('YYYY-MM-DD HH:mm:ss'),
                end: endDateTime.format('YYYY-MM-DD HH:mm:ss'),
                trainer: trainer,
                capacity: capacity,
                color: color,
                description: description
            };
            
            var url = eventId ? 'edit-event.php' : 'add-event.php';
            
            $.ajax({
                url: url,
                data: eventData,
                type: "POST",
                success: function (response) {
                    $('#eventModal').modal('hide');
                    $('#calendar').fullCalendar('refetchEvents');
                    displayMessage(eventId ? "Class updated successfully!" : "Class added successfully!", 'success');
                },
                error: function() {
                    displayMessage("Error saving class. Please try again.", 'error');
                }
            });
        }

        function deleteEvent() {
            var eventId = $('#eventId').val();
            
            if (confirm("Are you sure you want to delete this class?")) {
                $.ajax({
                    type: "POST",
                    url: "delete-event.php",
                    data: "id=" + eventId,
                    success: function (response) {
                        if(parseInt(response) > 0) {
                            $('#eventModal').modal('hide');
                            $('#calendar').fullCalendar('refetchEvents');
                            displayMessage("Class deleted successfully!", 'success');
                        } else {
                            displayMessage("Error deleting class.", 'error');
                        }
                    }
                });
            }
        }

        function updateEventDateTime(event) {
            var start = moment(event.start).format("YYYY-MM-DD HH:mm:ss");
            var end = moment(event.end).format("YYYY-MM-DD HH:mm:ss");
            
            $.ajax({
                url: 'edit-event.php',
                data: {
                    id: event.id,
                    title: event.title,
                    start: start,
                    end: end,
                    trainer: event.trainer,
                    capacity: event.capacity,
                    color: event.color,
                    description: event.description
                },
                type: "POST",
                success: function (response) {
                    displayMessage("Class time updated successfully!", 'success');
                }
            });
        }

        function addQuickEvent() {
            var today = moment().format('YYYY-MM-DD');
            openEventModal(null, today);
        }

        function viewTodayEvents() {
            $('#calendar').fullCalendar('gotoDate', moment());
            $('#calendar').fullCalendar('changeView', 'agendaDay');
        }

        function refreshCalendar() {
            $('#calendar').fullCalendar('refetchEvents');
            displayMessage("Calendar refreshed!", 'success');
        }

        function displayMessage(message, type) {
            var className = type === 'error' ? 'error' : 'success';
            $(".response").html("<div class='" + className + "'>" + message + "</div>");
            setTimeout(function() { 
                $("." + className).fadeOut(); 
            }, 3000);
        }

        // Update end time when start time or duration changes
        $('#eventStartTime, #eventDuration').on('change', function() {
            var startTime = $('#eventStartTime').val();
            var duration = parseInt($('#eventDuration').val());
            
            if (startTime && duration) {
                var start = moment('2000-01-01 ' + startTime);
                var end = moment(start).add(duration, 'minutes');
                // You can display the end time if needed
            }
        });
    </script>
</body>
</html>

<?php include('includes/footer.php'); ?>