<?php

include 'database/connection.php';

$result=mysqli_query($conn,"SELECT * FROM testimonials");

while($row=mysqli_fetch_assoc($result)){

echo "<h4>".$row['name']."</h4>";

echo "<p>".$row['message']."</p>";

}

?>