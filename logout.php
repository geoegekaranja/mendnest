<?php
require_once 'includes/auth.php';

/* Signing out must be a POST with a valid token (the Logout buttons in the site
   menus send one), so another website cannot sign you out through a link. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid()) {
    logout_user();                                   // empties the session and deletes the cookie
    redirect(BASE_URL . '/index.php');               // back to the website's home page
}

// Anything else (someone typed the address):
//   signed in     -> stay signed in and go to their own dashboard
//   not signed in -> the home page
$u = current_user();
redirect($u ? (ROLE_HOME[$u['role']] ?? BASE_URL . '/index.php') : BASE_URL . '/index.php');