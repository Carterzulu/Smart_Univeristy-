<?php

include 'Includes/dbcon.php';

session_start();

  if(isset($_POST['btn'])){

    $userType = $_POST['userType'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    //$password = md5($password);

if($userType == "Administrator"){
    
      $query = "SELECT * FROM tbladmin WHERE emailAddress = '$email' and password='$password'  ";
      $rs = $conn->query($query);
      $num = $rs->num_rows;
      $rows = $rs->fetch_assoc();

      if($num > 0){

        $_SESSION['userId'] = $rows['Id'];
        $_SESSION['firstName'] = $rows['firstName'];
        $_SESSION['emailAddress'] = $rows['emailAddress'];

        echo "<script type = \"text/javascript\">
        window.location = (\"Admin/index.php\")
        </script>";
      }

      else{

        $message = " Invalid Username/Password!";
        echo "<script>showMessage('" . $message . "');</script>";

      }
    }
    else if($userType == "Lecture"){

      $query = "SELECT * FROM tbllecture WHERE emailAddress = '$email' and password='$password' "; 
       
      $rs = $conn->query($query);
      $num = $rs->num_rows;
      $rows = $rs->fetch_assoc();

      if($num > 0){

        $_SESSION['userId'] = $rows['Id'];
       
        echo "<script type = \"text/javascript\">
        window.location = (\"lecture/takeAttendance.php\")
        </script>";
       
     
      
      }

      else{

        $message = " Invalid Username/Password!";
        echo "<script>showMessage('" . $message . "');</script>";

      }
    }
    else{

    
    

    }
}
?>



<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link rel="stylesheet" href="Login form CSS.css">
</head>
<body>
<div class="container">
        <form action=" " method='POST'>
        <h1>Login</h1>

        <div class="input-box">
            <input type="email"  name="email" placeholder="Email address" required>
        </div>

        <div class="input-box">
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <div class="remember-forgot">
        <label for="Login"><input type="checkbox">Remember Me</label>
        <a href="#">Forgotten Password</a>
        </div>
        
        <div class="input-box">
            
            <select class="select" required name="userType">
                <option value="">--Select User Roles--</option>
                <option value="Administrator">Administrator</option>
                <option value="Lecture">Lecture</option>
          </select>
        </div>
        <div id="messageDiv" class="messageDiv" style="display:none;"></div>
        <button type="submit" class="btn" name="btn">Login</button>
    <div class="register-link"> 
        <p>Don't have an account? <a href="Signup.html">Register Here</a></p>
    </form>       
    </div>
    
</div>
<script>
    function showMessage(message) {
    var messageDiv = document.getElementById('messageDiv');
    messageDiv.style.display="block";
    messageDiv.innerHTML = message;
    messageDiv.style.opacity = 1;
    setTimeout(function() {
      messageDiv.style.opacity = 0;
    }, 5000);
  }
  
  
  
     </script> 

</body>
</html>