<?php
session_start();
include '../database/connection.php';

if(isset($_POST['submit'])){

$patient_name = $_POST['patient_name'];
$email = $_POST['email'];
$service = $_POST['service'];
$appointment_date = $_POST['appointment_date'];

// Insert into DB
$sql = "INSERT INTO bookings (patient_name, email, service, appointment_date)
        VALUES ('$patient_name','$email','$service','$appointment_date')";

if(mysqli_query($conn,$sql)){
    $success = "Booking successful!";
}else{
    $error = "Something went wrong!";
}

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Appointment | Mend Nest KE</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <a href="patient/book.php" class="nav-link text-white d-inline">Book Appointment</a>
    <!-- Custom CSS -->
    <style>
        body {
            background: #f5f7fa;
        }

        .booking-card {
            max-width: 600px;
            margin: 60px auto;
            padding: 30px;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .btn-success {
            width: 100%;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="booking-card">

        <h3 class="text-center mb-4">Book a Consultation</h3>

        <?php if(isset($success)){ ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php } ?>

        <?php if(isset($error)){ ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>

        <form method="POST">

            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" name="patient_name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Service</label>
                <select name="service" class="form-control" required>
                    <option value="">Select Service</option>
                    <option>Home Nursing</option>
                    <option>Elderly Care</option>
                    <option>Medical Support</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Appointment Date</label>
                <input type="date" name="appointment_date" class="form-control" required>
            </div>

            <button type="submit" name="submit" class="btn btn-success">
                Book Appointment
            </button>

        </form>

    </div>

</div>

</body>
</html>