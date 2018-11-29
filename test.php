<?php 
echo "fgdsg";
if($_POST){
echo '<pre>';print_r($_POST);
die('test');
}

?>

<form action="https://www.sandbox.paypal.com/cgi-bin/webscr&amp;pal=V4T754QB63XXL" method="post" id="paypal_form">
   
      <input type="hidden" name="cmd" value="_cart">
  <input type="hidden" name="upload" value="1">
  <input type="hidden" name="business" value="webastral.buss-1@gmail.com">
      <input type="hidden" name="item_name_1" value="Luxury personalised velvet dog bed includes 2 free cushions">
  <input type="hidden" name="item_number_1" value="black/silver">
  <input type="hidden" name="amount_1" value="55">
  <input type="hidden" name="quantity_1" value="1">
  <input type="hidden" name="weight_1" value="2">
      <input type="hidden" name="on0_1" value="Enter Your Dog Name">
  <input type="hidden" name="os0_1" value="gdfgfdg">
      <input type="hidden" name="on1_1" value="Size">
  <input type="hidden" name="os1_1" value="Medium ">
      <input type="hidden" name="on2_1" value="Seller note">
  <input type="hidden" name="os2_1" value="dfgdfggdg">
          <input type="hidden" name="item_name_2" value="Luxury personalised velvet dog bed includes 2 free cushions">
  <input type="hidden" name="item_number_2" value="black/silver">
  <input type="hidden" name="amount_2" value="45">
  <input type="hidden" name="quantity_2" value="1">
  <input type="hidden" name="weight_2" value="2">
      <input type="hidden" name="on0_2" value="Enter Your Dog Name">
  <input type="hidden" name="os0_2" value="dgdfgd">
      <input type="hidden" name="on1_2" value="Size">
  <input type="hidden" name="os1_2" value="Small ">
          <input type="hidden" name="item_name_3" value="Luxury personalised velvet dog bed includes 2 free cushions">
  <input type="hidden" name="item_number_3" value="black/silver">
  <input type="hidden" name="amount_3" value="45">
  <input type="hidden" name="quantity_3" value="1">
  <input type="hidden" name="weight_3" value="2">
      <input type="hidden" name="on0_3" value="Enter Your Dog Name">
  <input type="hidden" name="os0_3" value="yuyt">
      <input type="hidden" name="on1_3" value="Size">
  <input type="hidden" name="os1_3" value="Small ">
      <input type="hidden" name="on2_3" value="Seller note">
  <input type="hidden" name="os2_3" value="tyutut">
            <input type="hidden" name="amount" value="145">
    <input type="hidden" name="currency_code" value="GBP">
   <input type="hidden" name="lc" value="en-gb">
  <input type="hidden" name="rm" value="2">
  <input type="hidden" name="no_note" value="1">
  <input type="hidden" name="no_shipping" value="1">
  <input type="hidden" name="charset" value="utf-8">
 <input type="hidden" name="return" value="https://doggy.co.uk/test.php">
  <input type="hidden" name="notify_url" value="https://doggy.co.uk/test.php">
  <input type="hidden" name="cancel_return" value="https://doggy.co.uk/test.php">
  <input type="hidden" name="paymentaction" value="authorization">
  <input type="hidden" name="bn" value="OpenCart_2.0_WPS">
<input type="hidden" name="address_override" value="1">
<input type="hidden" name="no_shipping" value="2">
  
  
  <input type="submit" value="submit">
      </form>
<span id="h_timer"></span>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js">
</script>
<script>

var countDownDate = new Date(Date.now()).getTime()+ (120 * 60 * 1000);

// Update the count down every 1 second
var x = setInterval(function() {
    // Get todays date and time
    var now = new Date().getTime();
    
    // Find the distance between now and the count down date
    var distance = countDownDate - now;
    
    // Time calculations for days, hours, minutes and seconds
    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((distance % (1000 * 60)) / 1000);
      var hour_text = 'Hours';
    if(hours <= 1){
      var hour_text = 'Hour'
    }
    var min_text = 'minutes';
    if(minutes <= 1){
      var min_text = 'minute';
    }

     var sec_text = 'Seconds';
    if(seconds <= 1){
      var sec_text = 'Second';
    }

    var countd = 0;
    if(hours == 1){
      countd = countd+60;
    }
    if(minutes != 0){
      countd = countd+minutes;
    }
    // Output the result in an element with id="demo"
    document.getElementById("h_timer").innerHTML =  "<strong> "+hours + " " +hour_text + " " + minutes + " " + min_text+ " " + seconds + " " + sec_text+"</strong>";
 
    // If the count down is over, write some text 
    if (distance < 0) {
       // return 'EXPIRED';
    }


  }, 1000);
// var return = coundowntimer();
// alert(return);

</script>

<script>
// setInterval(function() {
//          heroVel2 = 1;
      
//          console.log(heroVel2);
//          heroVel2 = heroVel2+1
//     }, 2000);
        </script>

<!--  $to = 'ramanjindal.inext@gmail.com';
$subject = "Booking Email";

$message = 'Assuring you the best of our services.';

// Always set content-type when sending HTML email
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

// More headers
$headers .= 'From: <admin@webappsitesdemo.com>' . "\r\n";
//$headers .= 'Cc: myboss@example.com' . "\r\n";

$mail = mail($to,$subject,$message,$headers); ?> -->