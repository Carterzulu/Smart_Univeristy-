<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PAYSTACK</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <form id="paymentForm" method="post">
    <div class="form-submit">
    <center>
      
    <input type="text" placeholder="Enter Your Name" name="name"><br>
      <input type="email" placeholder="Enter email Address" name="email"><br>
      <input type="text" placeholder="Enter Amount" name="amount"><br> <br>
      <input type="text" placeholder="payment purpose" name="campaign"><br> <br>
    
    </center>
      <center><button type="submit" onclick="payWithPaystack()"> Make Payment </button></center>

    </div>
  </form>
  <script src="https://js.paystack.co/v1/inline.js"></script>

  <?php
  include 'configs.php';
$db_server = "localhost";
$db_user ="root";
$db_password = "";
$db_name = "fos_db";

$conn = mysqli_connect($db_server,$db_user,$db_password,$db_name);

if($conn){
    
} 
else{
    echo "database not connected";
}
  if(isset($_POST['amount'])&& isset($_POST['email'])&& isset($_POST['name']) && isset($_POST['campaign'])){
    $email = $_POST['email'];
    $campain= $_POST['campaign'];
    $name=$_POST['name'];
    $amount = $_POST['amount'];
    $currency = "NGN";

    $query = "INSERT INTO donations (name,campaign,email,amount,currency) VALUES ('$name','$campain','$email','$amount','$currency')";
    mysqli_query($conn,$query);
  }

  ?>

  <script type="text/javascript">
    const paymentForm = document.getElementById('paymentForm');
    paymentForm.addEventListener("submit", payWithPaystack, false);

    function payWithPaystack(e) {
      e.preventDefault();
      let handler = PaystackPop.setup({
        key: '<?php echo $PublicKey; ?>', // Replace with your public key
        Name: '<?php echo $name; ?>',
        email: '<?php echo $email; ?>',
        amount: <?php echo $amount; ?> * 100,
        currency: '<?php echo $currency; ?>', // Use GHS for Ghana Cedis or USD for US Dollars or KES for Kenya Shillings
        ref: '' + Math.floor((Math.random() * 1000000000) + 1), // generates a pseudo-unique reference. Please replace with a reference you generated. Or remove the line entirely so our API will generate one for you
        // label: "Optional string that replaces customer email"
        onClose: function() {
          alert('Transaction was not completed, window closed.');
        },
        callback: function(response) {
          let message = 'Payment complete! Reference: ' + response.reference;
          alert(message);
          window.location.href = "index.php?reference=" + response.reference;
        }
      });

      handler.openIframe();
    }
  </script>

  <script src="https://js.paystack.co/v1/inline.js"></script>
</body>

</html>