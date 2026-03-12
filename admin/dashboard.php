<?php

session_start();

if(!isset($_SESSION['admin'])){
header("Location:login.php");
}

?>

<h1>Admin Dashboard</h1>

<ul>

<li><a href="manage_bookings.php">Bookings</a></li>
<li><a href="manage_blog.php">Blog</a></li>
<li><a href="manage_testimonials.php">Testimonials</a></li>

</ul>