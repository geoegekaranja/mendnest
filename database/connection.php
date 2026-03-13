<?php

session_start();
include 'connection.php';

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