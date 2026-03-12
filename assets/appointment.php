<?php

include "includes/header.php";
include "includes/db.php";

if($_SERVER["REQUEST_METHOD"]=="POST"){

$name=$_POST['name'];
$email=$_POST['email'];
$phone=$_POST['phone'];
$service=$_POST['service'];
$date=$_POST['date'];
$message=$_POST['message'];

$stmt=$conn->prepare("INSERT INTO appointments(name,email,phone,service,appointment_date,message) VALUES(?,?,?,?,?,?)");

$stmt->bind_param("ssssss",$name,$email,$phone,$service,$date,$message);

$stmt->execute();

echo "<div class='alert alert-success text-center'>Appointment booked successfully</div>";
}

?>

<div class="container mt-5">

<h2 class="text-center mb-4">Book Appointment</h2>

<form method="POST">

<div class="row">

<div class="col-md-6">
<input class="form-control mb-3" name="name" placeholder="Full Name" required>
</div>

<div class="col-md-6">
<input class="form-control mb-3" name="email" placeholder="Email" required>
</div>

</div>

<input class="form-control mb-3" name="phone" placeholder="Phone Number">

<select class="form-control mb-3" name="service">

<option>Home Nursing</option>
<option>Elderly Care</option>
<option>Medication Support</option>

</select>

<input class="form-control mb-3" type="date" name="date">

<textarea class="form-control mb-3" name="message" placeholder="Additional notes"></textarea>

<button class="btn btn-success w-100">Book Appointment</button>

</form>

</div>

<?php include "includes/footer.php"; ?>