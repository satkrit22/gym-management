<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session first
session_start();

define('TITLE', 'Dashboard');
define('PAGE', 'dashboard');
include('includes/header.php'); 
include('../dbConnection.php');

// Check if admin is logged in
if (!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

$aEmail = $_SESSION['aEmail'];

// Fetch total members
$sql = "SELECT * FROM memberlogin_tb";
$result = $conn->query($sql);
$totaltech = $result ? $result->num_rows : 0;

// Fetch total schedules (safe even if table is missing)
$sql = "SELECT * FROM tbl_events";
$result = @$conn->query($sql); // Suppress warning if table is missing
$totalmeb = ($result) ? $result->num_rows : 0;

// Fetch total bookings
$sql = "SELECT * FROM submitbookingt_tb";
$result = $conn->query($sql);
$totalbok = $result ? $result->num_rows : 0;
?>

<div class="col-sm-9 col-md-10">
  <div class="row mx-5 text-center">
    <!-- Total Schedules -->
    <div class="col-sm-4 mt-5">
      <div class="card text-white bg-secondary mb-3" style="max-width: 18rem;">
        <div class="card-header">Total Schedules</div>
        <div class="card-body">
          <h4 class="card-title"><?php echo $totalmeb; ?></h4>
          <a class="btn text-white" href="view_schedule.php">More info</a>
        </div>
      </div>
    </div>

    <!-- Total Members -->
    <div class="col-sm-4 mt-5">
      <div class="card text-white bg-success mb-3" style="max-width: 18rem;">
        <div class="card-header">Number of Members</div>
        <div class="card-body">
          <h4 class="card-title"><?php echo $totaltech; ?></h4>
          <a class="btn text-white" href="member.php">More info</a>
        </div>
      </div>
    </div>

    <!-- Bookings -->
    <div class="col-sm-4 mt-5">
      <div class="card text-white bg-info mb-3" style="max-width: 18rem;">
        <div class="card-header">Bookings</div>
        <div class="card-body">
          <h4 class="card-title"><?php echo $totalbok; ?></h4>
          <a class="btn text-white" href="bookings.php">More info</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Registered Members Table -->
  <div class="mx-5 mt-5 text-center">
    <p class="bg-primary text-white p-2"><b>Registered Members</b></p>
    <?php
    $sql = "SELECT * FROM memberlogin_tb";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
      echo '<table class="table table-bordered table-hover">
              <thead>
                <tr class="table-secondary">
                  <th scope="col">Member ID</th>
                  <th scope="col">Name</th>
                  <th scope="col">Email</th>
                </tr>
              </thead>
              <tbody>';

      while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<th scope="row">' . $row["m_login_id"] . '</th>';
        echo '<td>' . $row["m_name"] . '</td>';
        echo '<td>' . $row["m_email"] . '</td>';
        echo '</tr>';
      }

      echo '</tbody></table>';
    } else {
      echo '<div class="alert alert-info">No registered members found.</div>';
    }
    ?>
  </div>
</div>

<?php include('includes/footer.php'); ?>
