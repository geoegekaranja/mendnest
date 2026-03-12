<?php

include '../database/connection.php';

$result=mysqli_query($conn,"SELECT * FROM bookings");

while($row=mysqli_fetch_assoc($result)){

echo $row['patient_name']." - ".$row['service']." - ".$row['appointment_date'];

echo "<br>";

}

?>