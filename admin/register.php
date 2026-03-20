<?php
include 'database/connection.php';

$success = "";
$error = "";

if(isset($_POST['register'])){

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user exists
    $check = mysqli_query($conn,"SELECT * FROM patients WHERE email='$email'");

    if(mysqli_num_rows($check) > 0){
        $error = "Email already exists!";
    } else {

        // Insert user
        $sql = "INSERT INTO patients (name,email,password)
                VALUES ('$name','$email','$password')";

        if(mysqli_query($conn,$sql)){
            $success = "Account created successfully!";
        } else {
            $error = "Error creating account";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5" style="max-width:400px;">

<h3 class="text-center">Create Account</h3>

<?php if($success){ ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php } ?>

<?php if($error){ ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>

<form method="POST">

<input type="text" name="name" class="form-control mb-2" placeholder="Full Name" required>

<input type="email" name="email" class="form-control mb-2" placeholder="Email" required>

<input type="password" name="password" class="form-control mb-2" placeholder="Password" required>

<button name="register" class="btn btn-success w-100">Register</button>

</form>

<p class="mt-3 text-center">
Already have an account? <a href="login.php">Login</a>
</p>

</div>

</body>
</html>