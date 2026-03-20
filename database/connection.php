<?php

$conn = mysqli_connect("localhost", "root", "", "mendnest_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>