<?php 
define('TITLE', 'Submit Booking'); 
define('PAGE', 'SubmitBooking'); 
include('includes/header.php');  
include('../dbConnection.php'); 
session_start(); 

// Redirect if not logged in
if (!isset($_SESSION['is_login'])) {
    echo "<script> location.href='memberLogin.php'; </script>";
    exit();
}

$mEmail = $_SESSION['mEmail'];

// Get member name
$sql = "SELECT m_name FROM memberlogin_tb WHERE m_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $mEmail);
$stmt->execute();
$stmt->bind_result($mName);
$stmt->fetch();
$stmt->close();

// Pre-fill class/date if redirected from viewschedule
$preSelectedClass = isset($_GET['class']) ? $_GET['class'] : '';
$preSelectedDate = isset($_GET['date']) ? $_GET['date'] : '';

// Booking submission logic
if (isset($_POST['Submitbooking'])) {
    if (
        empty($mName) || empty($mEmail) || empty($_POST['membermobile']) || 
        empty($_POST['bookingtype']) || empty($_POST['trainer']) || 
        empty($_POST['bookingdate']) || empty($_POST['memberadd1']) || 
        empty($_POST['subscription'])
    ) {
        $msg = '<div class="alert alert-warning col-sm-6 ml-5 mt-2" role="alert"> All Fields Are Required </div>';
    } else {
        // Prevent double booking
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM submitbookingt_tb WHERE member_email = ? AND booking_type = ? AND member_date = ?");
        $checkStmt->bind_param("sss", $mEmail, $_POST['bookingtype'], $_POST['bookingdate']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $existingBooking = $checkResult->fetch_assoc()['count'];
        $checkStmt->close();

        if ($existingBooking > 0) {
            $msg = '<div class="alert alert-warning col-sm-6 ml-5 mt-2" role="alert"> You already have a booking for this class on this date! </div>';
        } else {
            $mmobile = $_POST['membermobile'];
            $btype = $_POST['bookingtype'];
            $trai = $_POST['trainer'];
            $madd1 = $_POST['memberadd1'];
            $bdate = $_POST['bookingdate'];
            $subscription = $_POST['subscription'];

            $stmt = $conn->prepare("INSERT INTO submitbookingt_tb 
                (member_name, member_email, member_mobile, member_add1, booking_type, trainer, member_date, subscription_months) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssi", $mName, $mEmail, $mmobile, $madd1, $btype, $trai, $bdate, $subscription);

            if ($stmt->execute()) {
                $genid = $conn->insert_id;
                $_SESSION['myid'] = $genid;
                echo "<script>alert('Booking Successful! Booking ID: $genid'); location.href='mybooking.php';</script>";
                exit();
            } else {
                $msg = '<div class="alert alert-danger col-sm-6 ml-5 mt-2" role="alert"> Unable to make booking: ' . $conn->error . '</div>';
            }

            $stmt->close();
        }
    }
}
?>

<!-- Booking Form -->
<div class="col-sm-8 mt-5 mx-auto">
    <div class="card">
        <div class="card-header bg-primary text-white text-center">
            <h3><b>Make Booking</b></h3>
        </div>
        <div class="card-body">
            <?php if (!empty($preSelectedClass)): ?>
                <div class="alert alert-info">
                    <strong>Pre-selected:</strong> <?php echo htmlspecialchars($preSelectedClass); ?> 
                    <?php if (!empty($preSelectedDate)): ?>
                        on <?php echo htmlspecialchars($preSelectedDate); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form action="SubmitBooking.php" method="POST">
                <?php if (isset($msg)) { echo $msg; } ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="inputName">Full Name</label>
                            <input type="text" class="form-control" id="inputName" name="membername" 
                                   value="<?php echo htmlspecialchars($mName); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="inputEmail">Email</label>
                            <input type="email" class="form-control" id="inputEmail" name="memberemail" 
                                   value="<?php echo htmlspecialchars($mEmail); ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="inputMobile">Mobile</label>
                            <input type="text" class="form-control" id="inputMobile" name="membermobile"
                                   placeholder="Enter mobile number" required maxlength="10" pattern="^(98|97)\d{8}$"
                                   title="Enter a valid 10-digit Nepali mobile number starting with 98 or 97"
                                   onkeypress="return isInputNumber(event)">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="inputbookingtype">Booking Type</label>
                            <select class="form-control" id="inputbookingtype" name="bookingtype" required>
                                <option value="">Select</option>
                                <option <?php echo ($preSelectedClass == 'Yoga class') ? 'selected' : ''; ?>>Yoga class</option>
                                <option <?php echo ($preSelectedClass == 'Zumba class') ? 'selected' : ''; ?>>Zumba class</option>
                                <option <?php echo ($preSelectedClass == 'Cardio class') ? 'selected' : ''; ?>>Cardio class</option>
                                <option <?php echo ($preSelectedClass == 'Weight lifting') ? 'selected' : ''; ?>>Weight lifting</option>
                                <option <?php echo ($preSelectedClass == 'Endurance Training') ? 'selected' : ''; ?>>Endurance Training</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="inputTrainer">Select Trainer</label>
                            <select class="form-control" id="inputTrainer" name="trainer" required>
                                <option value="">Select</option>
                                <option>Aashish Thapa (4:00AM-9:00AM)</option>
                                <option>Bikash Thapa (9:00AM-4:00PM)</option>
                                <option>Anupama (9:00AM-4:00PM)</option>
                                <option>Santoshi (4:00AM-9:00AM)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="inputDate">Date</label>
                            <input type="date" class="form-control" id="inputDate" name="bookingdate" 
                                   value="<?php echo htmlspecialchars($preSelectedDate); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputSubscription">Subscription Duration (in months)</label>
                    <select class="form-control" id="inputSubscription" name="subscription" required>
                        <option value="">Select</option>
                        <option value="1">1 Month</option>
                        <option value="3">3 Months</option>
                        <option value="6">6 Months</option>
                        <option value="12">12 Months</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="inputAddress">Address</label>
                    <input type="text" class="form-control" id="inputAddress" placeholder="Add address" name="memberadd1" required>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg" name="Submitbooking">Submit Booking</button>
                    <a href="viewschedule.php" class="btn btn-secondary btn-lg ml-2">Back to Schedule</a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JS input validation -->
<script>
function isInputNumber(evt) {
    var ch = String.fromCharCode(evt.which);
    if (!(/[0-9]/.test(ch))) {
        evt.preventDefault();
        return false;
    }
}

// Set min date to today
window.onload = function () {
    var today = new Date();
    var day = ("0" + today.getDate()).slice(-2);
    var month = ("0" + (today.getMonth() + 1)).slice(-2);
    var year = today.getFullYear();
    var todayDate = year + "-" + month + "-" + day;
    document.getElementById("inputDate").setAttribute("min", todayDate);
};
</script>

<?php 
include('includes/footer.php'); 
$conn->close(); 
?>
