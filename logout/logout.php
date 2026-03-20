<?php
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy session
session_destroy();

// Redirect to auth page
header("Location: auth.php");
exit();
?>