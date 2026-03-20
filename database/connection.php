<?php

$conn = mysqli_connect("localhost", "root", "", "mendnest");

if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}

?>