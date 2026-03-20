<?php

session_start(); // ONLY HERE

include 'connection.php';

if(isset($_POST['login'])){

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM patients WHERE email='$email' AND password='$password'";
$result = mysqli_query($conn,$sql);

if(mysqli_num_rows($result)==1){

$_SESSION['patient'] = $email;
header("Location: dashboard.php");
exit();

}else{
    echo "Invalid login credentials";
}

}

?>

<div class="alert alert-danger">
<?php echo $error; ?>
</div>

<?php } ?>

<form method="POST">

<div class="mb-3">
<label class="form-label">Username</label>
<input type="text" name="username" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Password</label>
<input type="password" name="password" class="form-control" required>
</div>

<button class="btn btn-success w-100" name="login">
Login
</button>

</form>

</div>

</body>
</html>