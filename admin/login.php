<?php

session_start();

if(isset($_POST['login'])){

$user=$_POST['username'];
$pass=$_POST['password'];

if($user=="admin" && $pass=="admin123"){

$_SESSION['admin']=$user;

header("Location:dashboard.php");

}

}

?>

<form method="POST">

<input type="text" name="username">

<input type="password" name="password">

<button name="login">Login</button>

</form>