<?php
include('../dbConnection.php');
session_start();
if (!isset($_SESSION['is_login'])) {
    if (isset($_REQUEST['mEmail'])) {
        $mEmail = mysqli_real_escape_string($conn, trim($_REQUEST['mEmail']));
        $mPassword = trim($_REQUEST['mPassword']);

        // Get hashed password from DB
        $sql = "SELECT m_password FROM memberlogin_tb WHERE m_email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $mEmail);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($hashedPassword);
            $stmt->fetch();

            if (password_verify($mPassword, $hashedPassword)) {
                $_SESSION['is_login'] = true;
                $_SESSION['mEmail'] = $mEmail;
                echo "<script> location.href='memberProfile.php'; </script>";
                exit;
            } else {
                $msg = '<div class="alert alert-warning mt-2" role="alert"> Invalid Password </div>';
            }
        } else {
            $msg = '<div class="alert alert-warning mt-2" role="alert"> Email not found </div>';
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="../css/bootstrap.min.css">

  <!-- Font Awesome CSS -->
  <link rel="stylesheet" href="../css/all.min.css">

  <style>
    .custom-margin {
         margin-top: 8vh;
      }
   </style>
  <title>Login</title>
</head>

<body class="bg-light">
  <div class="mb-3 text-center mt-5 text-dark" style="font-size: 30px;">
    <span><u><b>Grande Fitness</b></u></span>
  </div>
  <p class="text-center" style="font-size: 20px;"> <i class=" text-danger text-white"></i> <span><u>MEMBER AREA</u></span>
  </p>
  <div class="container-fluid mb-5">
    <div class="row justify-content-center custom-margin">
      <div class="col-sm-6 col-md-4">
        <form action="" class="shadow-lg p-4 bg-white" method="POST">
          <div class="form-group">
            <i class="fas fa-user"></i><label for="email" class="pl-2 font-weight-bold">Email</label><input type="email"
              class="form-control" placeholder="Email" name="mEmail">
            <small class="form-text">We'll never share your email with anyone else.</small>
          </div>
          <div class="form-group">
            <i class="fas fa-key"></i><label for="pass" class="pl-2 font-weight-bold">Password</label><input type="password"
              class="form-control" placeholder="Password" name="mPassword">
          </div>
          <button type="submit" class="btn btn-primary mt-3 btn-block shadow-sm font-weight-bold">Login</button>
          <?php if(isset($msg)) {echo $msg; } ?>
        </form>
        <div class="text-center"><a class="btn btn-secondary mt-3 shadow-sm font-weight-bold" href="../index.php">Back
            to Home</a></div>
      </div>
    </div>
  </div>
  </main>

           
  <!-- Boostrap JavaScript -->
  <script src="../js/jquery.min.js"></script>
  <script src="../js/popper.min.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <script src="../js/all.min.js"></script>
</body>

</html>