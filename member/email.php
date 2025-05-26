
<?php
define('TITLE', 'Email');
define('PAGE', 'email');
include('includes/header.php'); 
include('../dbConnection.php');
 session_start();
 if($_SESSION['is_login']){
  $mEmail = $_SESSION['mEmail'];
 } else {
  echo "<script> location.href='memberLogin.php'; </script>";
 }?>
<div class="col-sm-9 col-md-10 mt-5 text-center">
<div class="container">

<h3 class="text-center">Contact us</h3><br />

<div class="row">
  <div class="col-md-8">
      <form action="/post" method="post">
        <input class="form-control" name="name" placeholder="Name..." /><br />
        <input class="form-control" name="phone" placeholder="Phone..." /><br />
        <input class="form-control" name="email" placeholder="E-mail..." /><br />
        <textarea class="form-control" name="text" placeholder="How can we help you?" style="height:150px;"></textarea><br />
        <input class="btn btn-primary" type="submit" value="Send" /><br /><br />
      </form>
  </div>
  <div class="col-md-4">
    <b>Customer service:</b> <br />
    Phone: +977 9866208163<br />
    E-mail: <a href="#">grandefit@gmail.com</a><br />
    <br /><br />
    Grande Fitness Club<br />
    Tokha, <br />
    Kathmandu<br />
    Phone: +977 9866208163<br />
    <a href="#">grandefit@gmail.com</a><br />
  </div>
</div>
</div>
<?php
include('includes/footer.php'); 
?>