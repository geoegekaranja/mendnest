<?php
require_once 'includes/auth.php';

// POST + CSRF token: signs out. (Prevents other sites from logging users out silently.)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (csrf_valid()) {
        logout_user();
    }
    redirect(LOGIN_URL);
}

if (!current_user()) {
    redirect(LOGIN_URL);
}
// GET (e.g. an old plain "Logout" link): ask for confirmation instead of signing out silently
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign out</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container text-center" style="max-width:400px; margin-top:100px;">
    <h4 class="mb-3">Sign out of your account?</h4>
    <form method="POST">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <button class="btn btn-danger">Yes, sign out</button>
        <a href="<?= h(ROLE_HOME[current_user()['role']] ?? LOGIN_URL) ?>" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
</body>
</html>