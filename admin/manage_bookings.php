<?php
session_start();
include '../database/connection.php';

if(isset($_POST['submit'])){

$patient_name = $_POST['patient_name'];
$service = $_POST['service'];
$appointment_date = $_POST['appointment_date'];
$message = $_POST['message'];

// INSERT MATCHING YOUR TABLE
$sql = "INSERT INTO bookings (patient_name, service, appointment_date, message)
        VALUES ('$patient_name','$service','$appointment_date','$message')";

if(mysqli_query($conn,$sql)){
    $success = "Booking successful!";
}else{
    $error = "Error: " . mysqli_error($conn);
}

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Appointment</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background: #f5f7fa; }

        .card {
            max-width: 600px;
            margin: 60px auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body>

<div class="container">
    <div class="card">

        <h3 class="text-center mb-4">Book Appointment</h3>

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

            <div class="mb-3">
                <label>Message</label>
                <textarea name="message" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
            </div>

            <button type="submit" name="submit" class="btn btn-success w-100">
                Book Appointment
            </button>

        </form>

    </div>
</div>

</body>
</html>