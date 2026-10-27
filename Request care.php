<?php
require_once 'includes/auth.php';

/* Every "Request Care" and "Choose this package" button points here.
   - A signed-in patient goes straight to the booking form.
   - A visitor is sent to log in or register first, then returned to the booking form.
   - An admin or caregiver is sent to their own dashboard (care is requested by patients). */

const CARE_PACKAGES = ['Essential Care', 'Plus Care', 'Integrated Care'];

$target  = BASE_URL . '/patient/book.php';
$package = (string)($_GET['package'] ?? '');
if (in_array($package, CARE_PACKAGES, true)) {
    $target .= '?package=' . rawurlencode($package);
}

$user = current_user();

if ($user && $user['role'] === 'patient') {
    redirect($target);
}

if ($user) {
    flash('info', 'Care requests are made from a patient account.');
    redirect(ROLE_HOME[$user['role']] ?? LOGIN_URL);
}

// A visitor: remember where to come back to (login.php checks this address again)
$_SESSION['after_login'] = $target;
flash('info', 'Please log in or register to request care. You will return to the booking form afterwards.');
redirect(LOGIN_URL);