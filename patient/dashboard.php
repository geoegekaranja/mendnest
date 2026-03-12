<?php

session_start();

if(!isset($_SESSION['patient'])){
header("Location:login.php");
}

?>

<h2>Welcome Patient</h2>

<a href="logout.php">Logout</a>