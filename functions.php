<?php

if(session_status()===PHP_SESSION_NONE){
session_start();
}

function clean($data){
return htmlspecialchars(trim($data));
}

function redirect($url){
header("Location:$url");
exit();
}

function adminLoggedIn(){
return isset($_SESSION['admin_id']);
}

function protectAdmin(){
if(!adminLoggedIn()){
redirect("login.php");
}
}
?>