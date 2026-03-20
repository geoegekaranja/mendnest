<?php
session_start();
include '../database/connection.php';

$error = "";

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM patients WHERE email=? AND password=? AND role='admin'");
    $stmt->bind_param("ss",$email,$password);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){

        $_SESSION['user'] = $email;
        $_SESSION['role'] = 'admin';

        header("Location: dashboard.php");
        exit();

    } else {
        $error = "Invalid admin credentials";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg,#343a40,#000);
    height: 100vh;
}

.login-box {
    max-width: 400px;
    margin: 100px auto;
    background: #fff;
    padding: 30px;
    border-radius: 10px;
}
</style>
</head>

<body>

<div class="login-box">

<h3 class="text-center mb-3">Admin Login</h3>

<?php if($error){ ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>

<form method="POST">

<input type="email" name="email" class="form-control mb-2" placeholder="Email" required>

<input type="password" name="password" class="form-control mb-2" placeholder="Password" required>

<button name="login" class="btn btn-dark w-100">Login</button>

</form>

</div>

</body>
</html>