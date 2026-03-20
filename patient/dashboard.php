<?php
session_start();

if(!isset($_SESSION['patient'])){
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">

<h2>Welcome, <?php echo $_SESSION['patient']; ?></h2>

<a href="../patient/book.php" class="btn btn-success mt-3">Book Appointment</a>

<a href="../logout.php" class="btn btn-danger mt-3">Logout</a>

</div>

</body>
</html>