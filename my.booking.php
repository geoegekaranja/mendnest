<?php
session_start();
include '../database/connection.php';

$success = "";

if(isset($_POST['submit'])){

$patient_name = $_POST['patient_name'];
$service = $_POST['service'];
$date = $_POST['appointment_date'];
$message = $_POST['message'];

mysqli_query($conn,"INSERT INTO bookings (patient_name,service,appointment_date,message)
VALUES ('$patient_name','$service','$date','$message')");

$success = "Booking submitted successfully!";
}
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-5">

<h3 class="text-center mb-4">Book Appointment</h3>

<?php if($success){ ?>
<div class="alert alert-success text-center"><?php echo $success; ?></div>
<?php } ?>

<div class="row justify-content-center">
<div class="col-md-6">

<form method="POST" class="card shadow">

<input type="text" name="patient_name" class="form-control mb-3" placeholder="Full Name" required>

<select name="service" class="form-control mb-3">
<option>Home Nursing</option>
<option>Elderly Care</option>
<option>Medical Support</option>
</select>

<input type="date" name="appointment_date" class="form-control mb-3" required>

<textarea name="message" class="form-control mb-3" placeholder="Message"></textarea>

<button name="submit" class="btn btn-success">Book Now</button>

</form>

</div>
</div>

</div>

<?php include '../includes/footer.php'; ?>