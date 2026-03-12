<?php
session_start();
include '../database/connection.php';

if(isset($_POST['login'])){

$email=$_POST['email'];
$password=$_POST['password'];

$sql="SELECT * FROM patients WHERE email='$email' AND password='$password'";
$result=mysqli_query($conn,$sql);

if(mysqli_num_rows($result)==1){

$_SESSION['patient']=$email;
header("Location: dashboard.php");

}

}
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-5">

<h2>Patient Login</h2>

<form method="POST">

<input type="email" name="email" placeholder="Email">

<input type="password" name="password" placeholder="Password">

<button name="login">Login</button>

</form>

</div>

<?php include '../includes/footer.php'; ?>

