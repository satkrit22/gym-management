<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="css/bootstrap.min.css">

  
  <!-- Font Awesome CSS -->
  <link rel="stylesheet" href="css/all.min.css">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">

  <!-- style css -->
  <link rel="stylesheet" href="css/custom.css">

  <title>Grande Fitness</title>
</head>

<body>
  <!-- Start Navigation here -->
  <nav class="navbar navbar-expand-sm navbar-dark bg-dark pl-5 fixed-top">
    <a href="index.php" class="navbar-brand">Grande Fitness</a>
    <span class="navbar-text"></span>
    <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#myMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="myMenu" style="margin-right:50px ;">
      <ul class="navbar-nav pl-5 custom-nav ml-auto">
        <li class="nav-item "><a href="index.php" class="nav-link">Home</a></li>
        <li class="nav-item"><a href="#about" class="nav-link">About</a></li>
        <li class="nav-item"><a href="#Services" class="nav-link">Services</a></li>
        <li class="nav-item"><a href="#Contact" class="nav-link">Contact</a></li>
        <li class="nav-item"><a href="UserRegistration.php" class="nav-link">Sign Up</a></li>
        <li class="nav-item"><a href="member/memberLogin.php" class="nav-link">Login</a></li>
      </ul>
    </div>
  </nav> <!-- End Navigation -->

  <!-- Start Header Jumbotron-->
  <header class="jumbotron back-image" style="background-image: url(images/banner1.jpg);">
    <div class="myclass mainHeading">
      <h1 class="text-uppercase text-danger font-weight-bold">Welcome to Grande Fitness</h1>
      <p class="font-italic">Keep your LIFE fit and healthy.. <br>Join the Professional Fitness center..</p>
      <a href="member/memberLogin.php" class="btn btn-primary mr-4">Login</a>
      <a href="userRegistration.php" class="btn btn-danger mr-4">Sign Up</a>
    </div>
  </header> <!-- End Header Jumbotron -->


<hr>
  <!--Introduction Section End-->
  <!-- start service type -->
  <div class="container mt-5" id="Services">
    <h1 class="text-center"><u> Our Services</u></h1>
    
    <!-- end here -->
  <div class="card-deck mt-4">
    <a href="#" class="btn" style="text-align: left; padding:0px; margin:0px;">
  <div class="card">
    <img src="images/courseimg/06.jpg" class="card-img-top" alt="Guitar"/>
    <div class="card-body">
      <h5 class="card-title"> YOGA CLASSES</h5>
      <p class="card-text text-dark">Will have expericed trainners to <br>help you with your yoga...</p>
    </div>
    <div class="card-footer">
      <p class="card-text d-inline">Price: <small><del> रु 2000
      </del></small> <span
      class="font-weight-bolder"> रु 1500</span></p>
       <a class="btn btn-primary text-white font-weight-bolder float-right" href="userRegistration.php">Book now</a>
    </div>
  </div>
 </a>
 <a href="#" class="btn" style="text-align: left; padding:0px;">
  <div class="card">  
    <img src="images/courseimg/03.jpg" class="card-img-top" alt="Python"/>
    <div class="card-body">
      <h5 class="card-title"> WEIGHT TRAINING</h5>
      <p class="card-text text-dark">From body building to powerlifting, <br>and everything in between...
         </p>
    </div>
    <div class="card-footer">
      <p class="card-text d-inline">Price: <small><del> रु 2000
      </del></small> <span
      class="font-weight-bolder"> रु 1200</span></p> <a class="btn btn-primary text-white font-weight-bolder float-right" href="userRegistration.php">Book now</a>
    </div>
  </div>
 </a>
 <a href="#" class="btn" style="text-align: left; padding:0px;">
  <div class="card">
    <img src="images/courseimg/04.jpg" class="card-img-top" alt="ZUMBA"/>
    <div class="card-body">
      <h5 class="card-title">ZUMBA</h5>
      <p class="card-text text-dark">Join  zumba lessons <br>at profitness gym...
         </p>
    </div>
    <div class="card-footer">
      <p class="card-text d-inline">Price: <small><del> रु 1000
      </del></small> <span
      class="font-weight-bolder"> रु 700</span></p> <a class="btn btn-primary text-white font-weight-bolder float-right" href="userRegistration.php">Book now</a>
    </div>
  </div>
 </a>
</div>
<!-- service 1 end -->
<!-- service 2 start -->
  <div class="card-deck mt-4">
    <a href="#" class="btn" style="text-align: left; padding:0px;">
  <div class="card">
    <img src="images/courseimg/09.jpg" class="card-img-top" alt="Python"/>
    <div class="card-body">
      <h5 class="card-title"> CARDIO CLASSES</h5>
      <p class="card-text text-dark">Helps strengthen the heart and lungs <br>Join our cardio class today...</p>
    </div>
    <div class="card-footer">
      <p class="card-text d-inline">Price: <small><del> रु 9000
      </del></small> <span
      class="font-weight-bolder"> रु 6500</span></p> <a class="btn btn-primary text-white font-weight-bolder float-right" href="userRegistration.php">Book now</a>
    </div>
  </div>
 </a>
 <a href="#" class="btn" style="text-align: left; padding:0px;">
  <div class="card">
    <img src="images/courseimg/05.jpg" class="card-img-top" alt="Python"/>
    <div class="card-body">
      <h5 class="card-title"> GROUP TRAINING</h5>
      <p class="card-text text-dark">Stay motivited and exercise with friends<br> Join our group exercies today...</p>
    </div>
    <div class="card-footer">
      <p class="card-text d-inline">Price: <small><del> रु 6000
      </del></small> <span
      class="font-weight-bolder"> रु 5700</span></p> <a class="btn btn-primary text-white font-weight-bolder float-right" href="userRegistration.php">Book now</a>
    </div>
  </div>
 </a>
 <a href="#" class="btn" style="text-align: left; padding:0px;">
  <div class="card">
    <img src="images/courseimg/08.jpg" class="card-img-top" alt="Python"/>
    <div class="card-body">
      <h5 class="card-title">ENDURANCE TRAINING </h5>
      <p class="card-text text-dark">Build your stamina with us today<br> Join endurance training today classes... </p>
    </div>
    <div class="card-footer">
      <p class="card-text d-inline">Price: <small><del> रु 4000
      </del></small> <span
      class="font-weight-bolder"> रु 2200</span></p> <a class="btn btn-primary text-white font-weight-bolder float-right" href="userRegistration.php">Book now</a>
    </div>
  </div>
 </a>
</div>
<!-- our services end here -->
</div>
<hr>


 
    <!--about us Section-->
    <div class="jumbotron container " id="about">
      <h3 class="text-center">About Us</h3>
      <p class="text-center col-11">
      <h3 class="column-title">Grande Fitness</h3>
       <p>Welcome to Grande Fitness — where fitness meets excellence. Founded with the vision of promoting a healthier, stronger community, Grande Fitness is more than just a gym; it’s a lifestyle destination. We are committed to providing a supportive and motivating environment for individuals of all fitness levels..</p>

       <p>Our features state-of-the-art equipment, expert trainers, and a variety of fitness programs designed to meet your unique goals — whether you're aiming for weight loss, muscle gain, improved endurance, or overall well-being. We believe fitness should be accessible and enjoyable, which is why we offer flexible membership options, personalized training sessions, and group classes. </p>
      </p>
      <p>At Grande Fitness, your journey is our priority. Join us today and take the first step toward becoming the best version of yourself!</p>
      </p>

    </div>
  </div>
  
  <!-- end about us section  -->
 
  
    <!-- Start Happy Customer  -->
  <div class="jumbotron bg-primary" id="Customer">
    <!-- Start Happy Customer Jumbotron -->
    <div class="container">
      <!-- Start Customer Container -->
      <h2 class="text-center text-white">Our Gym Trainers</h2>
      <div class="row mt-5">
        <div class="col-lg-3 col-sm-6">
          <!-- Start Customer 1st Column-->
          <div class="card shadow-lg mb-2">
            <div class="card-body text-center">
              <img src="images/avtar1.jpeg" class="img-fluid" style="border-radius: 100px;">
              <h4 class="card-title">Aashish Thapa</h4>
              <p class="card-text">Trained martial arts expect with years of experice come join the martial arts class
                and start your jonuery.</p> <a class="btn btn-primary text-white font-weight-bolder float-center" href="/Gym management system/trainerprofile/aashishthapa.php">View</a>
            </div>
          </div>
        </div> <!-- End Customer 1st Column-->

        <div class="col-lg-3 col-sm-6">
          <!-- Start Customer 2nd Column-->
          <div class="card shadow-lg mb-2">
            <div class="card-body text-center">
              <img src="images/avtar2.jpeg" class="img-fluid" style="border-radius: 100px;">
              <h4 class="card-title">Anupama</h4>
              <p class="card-text"> Cardio exercises help streghthen your body and mind, i will take you through the cardio gym class.</p><a class="btn btn-primary text-white font-weight-bolder float-center" href="/Gym management system/trainerprofile/anupama.php">View</a>
            </div>
          </div>
        </div> <!-- End Customer 2nd Column-->

        <div class="col-lg-3 col-sm-6">
          <!-- Start Customer 3rd Column-->
          <div class="card shadow-lg mb-2">
            <div class="card-body text-center">
              <img src="images/avtar3.jpeg" class="img-fluid" style="border-radius: 100px;">
              <h4 class="card-title">Bikash Shrestha</h4>
              <p class="card-text">I have been at profitness for 7 years, im a Professional trainer whos major focus is weight lifting</p><a class="btn btn-primary text-white font-weight-bolder float-center" href="/Gym management system/trainerprofile/bikashshrestha.php">View</a>
            </div>
          </div>
        </div> <!-- End Customer 3rd Column-->

        <div class="col-lg-3 col-sm-6">
          <!-- Start Customer 4th Column-->
          <div class="card shadow-lg mb-2">
            <div class="card-body text-center">
              <img src="images/avtar4.jpeg" class="img-fluid" style="border-radius: 100px;">
              <h4 class="card-title">Santoshi</h4>
              <p class="card-text">Im a trained expect in yoga exercises, i will help you improve your body through yoga .</p><a class="btn btn-primary text-white font-weight-bolder float-center" href="/Gym management system/trainerprofile/santoshi.php">View</a>
            </div>
          </div>
        </div> <!-- End Customer 4th Column-->
      </div> <!-- End Customer Row-->
    </div> <!-- End Customer Container -->
  </div> <!-- End Customer Jumbotron -->

  <!--Start Contact Us-->
  <div class="container" id="Contact">
    <!--Start Contact Us Container-->
    <h2 class="text-center mb-4">Contact US</h2> <!-- Contact Us Heading -->
    <div class="row">

      <!--Start Contact Us Row-->
      <?php include('contactform.php'); ?>
      <!-- End Contact Us 1st Column -->
    
  <!-- Start Footer-->
  <footer class="container-fluid bg-dark text-white mt-5" style="border-top: 3px solid #DC3545;">
    <div class="container">
      <!-- Start Footer Container -->
      <div class="row py-3">
        <!-- Start Footer Row -->
        <div class="col-md-6">
          <!-- Start Footer 1st Column -->
          <span class="pr-2">Follow Us On: </span>
          <a href="https://www.facebook.com/nabin.bishwokarma.524" target="_blank" class="pr-2 fi-color"><i class="fab fa-facebook-f"></i></a>
          <a href="https://x.com/NabinBishwoka16" target="_blank" class="pr-2 fi-color"><i class="fab fa-twitter"></i></a>
          <a href="https://www.youtube.com/" target="_blank" class="pr-2 fi-color"><i class="fab fa-youtube"></i></a>
        </div> <!-- End Footer 1st Column -->

        <div class="col-md-6 text-right">
          <!-- Start Footer 2nd Column -->
          <small> Designed by Grande Fitness &copy; 2025.
          </small>
          <small class="ml-2"><a href="Admin/login.php">Admin Login</a></small>
        </div> <!-- End Footer 2nd Column -->
      </div> <!-- End Footer Row -->
    </div> <!-- End Footer Container -->
  </footer> <!-- End Footer -->
</div>
</div>
  <!-- Boostrap JavaScript -->
  <script src="js/jquery.min.js"></script>
  <script src="js/popper.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/all.min.js"></script>
</body>

</html>