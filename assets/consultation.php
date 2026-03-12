<?php
include 'database/connection.php';

if(isset($_POST['book'])){

$name = $_POST['name'];
$service = $_POST['service'];
$date = $_POST['date'];
$message = $_POST['message'];

$sql="INSERT INTO bookings(patient_name,service,appointment_date,message)
VALUES('$name','$service','$date','$message')";

mysqli_query($conn,$sql);

echo "Booking submitted successfully";

}

?>

<form method="POST">

<input type="text" name="name" placeholder="Name">

<select name="service">

<option>Medication</option>
<option>Wound Care</option>
<option>IV Hydration</option>

</select>

<input type="date" name="date">

<textarea name="message"></textarea>

<button name="book">
Book Consultation
</button>

</form>