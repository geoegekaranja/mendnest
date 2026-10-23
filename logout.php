<?php
require_once 'includes/auth.php';

/* Signing out must be a POST with a valid token (the Logout buttons in the site
   menus send one), so another website cannot sign you out through a link. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid()) {
    logout_user();                                   // empties the session and deletes the cookie
    redirect(LOGIN_URL . '?loggedout=1');
}

// Anything else (someone typed the address): stay signed in and go to the right place
$u = current_user();
redirect($u ? (ROLE_HOME[$u['role']] ?? LOGIN_URL) : LOGIN_URL);