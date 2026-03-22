<?php
include '../database/connection.php';

if(isset($_POST['book'])){

$name = $_POST['name'];
$service = $_POST['service'];
$date = $_POST['date'];
$message = $_POST['message'];

$sql="INSERT INTO bookings(patient_name,service,appointment_date,message)
VALUES('$name','$service','$date','$message')";

mysqli_query($conn,$sql);

echo "<div class='alert alert-success'>Booking submitted successfully</div>";
}
?>

<link rel="stylesheet" href="/mendnest/css/style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<section class="consultation-section">
<div class="container">

<h2 class="mb-4 fw-bold">Care Request Form</h2>

<form method="POST" class="consultation-form">

<div class="row">

    <!-- NAME -->
    <div class="col-md-6 mb-3">
        <label>Full Name *</label>
        <input type="text" name="name" class="form-control" required>
    </div>

    <!-- SERVICE -->
    <div class="col-md-6 mb-3">
        <label>Select Service *</label>
        <select name="service" class="form-control" required>
            <option value="">Choose Service</option>
            <option>Medication</option>
            <option>Wound Care</option>
            <option>IV Hydration</option>
        </select>
    </div>

    <!-- DATE -->
    <div class="col-md-6 mb-3">
        <label>Preferred Date *</label>
        <input type="date" name="date" class="form-control" required>
    </div>

    <!-- MESSAGE -->
    <div class="col-md-12 mb-3">
        <label>Additional Notes</label>
        <textarea name="message" class="form-control" rows="4"></textarea>
    </div>

</div>

<button type="submit" name="book" class="btn btn-success w-100">
    Submit Request
</button>

</form>

</div>
</section>