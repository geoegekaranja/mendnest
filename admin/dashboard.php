<?php
session_start();
include '../database/connection.php';

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

// COUNTS
$bookings = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM bookings"));
$patients = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM patients"));
$testimonials = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM testimonials"));
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: #f4f6f9;
}

.sidebar {
    height: 100vh;
    background: #343a40;
    color: white;
    padding: 20px;
}

.sidebar a {
    color: white;
    display: block;
    margin: 10px 0;
    text-decoration: none;
}

.card-box {
    border-radius: 10px;
    padding: 20px;
    background: white;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>

</head>

<body>

<div class="container-fluid">
<div class="row">

    <!-- SIDEBAR -->
    <div class="col-md-2 sidebar">
        <h4>Admin Panel</h4>
        <hr>

        <a href="#">Dashboard</a>
        <a href="bookings.php">Bookings</a>
        <a href="testimonials.php">Testimonials</a>
        <a href="blog.php">Blog</a>
        <a href="../logout.php">Logout</a>
    </div>

    <!-- MAIN -->
    <div class="col-md-10 p-4">

        <h3>Admin Dashboard</h3>

        <div class="row mt-4">

            <div class="col-md-4">
                <div class="card-box text-center">
                    <h5>Total Bookings</h5>
                    <h2><?php echo $bookings; ?></h2>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-box text-center">
                    <h5>Total Patients</h5>
                    <h2><?php echo $patients; ?></h2>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-box text-center">
                    <h5>Testimonials</h5>
                    <h2><?php echo $testimonials; ?></h2>
                </div>
            </div>

        </div>

    </div>

</div>
</div>

</body>
</html>