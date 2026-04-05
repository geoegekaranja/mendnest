<?php 
include '../database/connection.php';

if(isset($_POST['submit'])){
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $country = $_POST['country'];
    $package = $_POST['package'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $duration = $_POST['duration'];
    $service_address = $_POST['service_address'];
    $notes = $_POST['notes'];

    mysqli_query($conn,"INSERT INTO requests 
    (fname,lname,email,phone,address,city,country,package,date,time,duration,service_address,notes)
    VALUES('$fname','$lname','$email','$phone','$address','$city','$country','$package','$date','$time','$duration','$service_address','$notes')");
}
?>

<?php include '../includes/header.php'; ?>

<section class="care-section">
<div class="container">

<h2 class="fw-bold mb-4">Care Request Form</h2>

<form method="POST" class="care-form">

<!-- NAME -->
<div class="row">
<div class="col-md-6">
<label>First Name *</label>
<input type="text" name="fname" required>
</div>

<div class="col-md-6">
<label>Last Name *</label>
<input type="text" name="lname" required>
</div>
</div>

<!-- EMAIL -->
<label>Email Address *</label>
<input type="email" name="email" required>

<!-- PHONE -->
<label>Phone *</label>
<input type="tel" name="phone" placeholder="+254 712 345678" required>

<!-- ADDRESS -->
<label>Street Address *</label>
<input type="text" name="address" required>

<!-- CITY -->
<label>City *</label>
<input type="text" name="city" required>

<!-- COUNTRY -->
<label>Country *</label>
<select name="country">
<option>Select country</option>
<option>Kenya</option>
<option>Uganda</option>
<option>Tanzania</option>
</select>

<!-- PACKAGE -->
<label>Select a Care Package *</label>
<select name="package">
<option>Care Package</option>
<option>Essential Care</option>
<option>Plus Care</option>
<option>Integrated Care</option>
</select>

<!-- DATE -->
<label>Preferred Start Date *</label>
<input type="date" name="date">

<!-- TIME -->
<label>Preferred Time Slot *</label>
<select name="time">
<option>Service Delivery Time</option>
<option>Morning</option>
<option>Afternoon</option>
<option>Evening</option>
</select>

<!-- DURATION -->
<label>Estimated Duration of Service *</label>
<select name="duration">
<option>Service Duration</option>
<option>1 Day</option>
<option>1 Week</option>
<option>1 Month</option>
</select>

<!-- SERVICE ADDRESS -->
<label>Service Location / Address *</label>
<input type="text" name="service_address">

<!-- NOTES -->
<label>Additional Notes / Specific Needs *</label>
<textarea name="notes"></textarea>

<!-- CHECKBOX -->
<div class="form-check mt-3">
<input type="checkbox" required>
<label>I confirm that the information provided is accurate.</label>
</div>

<!-- BUTTON -->
<button name="submit" class="submit-btn mt-3">Submit</button>

</form>

</div>
</section>

<?php include '../includes/footer.php'; ?>