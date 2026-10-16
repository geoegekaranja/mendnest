<?php
session_start();
include '../database/connection.php';

$result = mysqli_query($conn,"SELECT * FROM bookings");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Bookings</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

<h3>All Bookings</h3>

<table class="table table-bordered">
<tr>
    <th>Name</th>
    <th>Service</th>
    <th>Date</th>
    <th>Message</th>
</tr>

<?php while($row=mysqli_fetch_assoc($result)){ ?>
<tr>
    <td><?php echo $row['patient_name']; ?></td>
    <td><?php echo $row['service']; ?></td>
    <td><?php echo $row['appointment_date']; ?></td>
    <td><?php echo $row['message']; ?></td>
</tr>
<?php } ?>

</table>

</div>

</body>
</html>