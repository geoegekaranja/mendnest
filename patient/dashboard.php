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
<title>Patient Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: #f4f6f9;
}

.sidebar {
    height: 100vh;
    background: #0e1310;
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
        <h4>Mend Nest</h4>
        <hr>

        <a href="#">Dashboard</a>
        <a href="book.php">Book Appointment</a>
        <a href="my_bookings.php">My Bookings</a>
        <a href="../logout.php">Logout</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="col-md-10 p-4">

        <h3>Welcome, <?php echo $_SESSION['patient']; ?> 👋</h3>

        <div class="row mt-4">

            <div class="col-md-4">
                <div class="card-box text-center">
                    <h5>Book Appointment</h5>
                    <a href="book.php" class="btn btn-success mt-2">Book Now</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-box text-center">
                    <h5>View My Bookings</h5>
                    <a href="my_bookings.php" class="btn btn-primary mt-2">View</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-box text-center">
                    <h5>Logout</h5>
                    <a href="../logout.php" class="btn btn-danger mt-2">Logout</a>
                </div>
            </div>

        </div>

    </div>

</div>
</div>

</body>
</html>