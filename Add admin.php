<?php
require_once '../includes/auth.php';
require_once '../database/connection.php';
$admin = require_role('admin');   // only existing admins can create new admins

$error = '';
$old   = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name']  = trim((string)($_POST['name'] ?? ''));
    $old['email'] = trim((string)($_POST['email'] ?? ''));
    $password     = (string)($_POST['password'] ?? '');
    $confirm      = (string)($_POST['confirm'] ?? '');

    if (!csrf_valid()) {
        $error = 'Your session expired. Please try again.';
    } elseif ($old['name'] === '' || mb_strlen($old['name']) > 100) {
        $error = 'Please enter a name (max 100 characters).';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 150) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8 || strlen($password) > 72) {
        $error = 'Password must be between 8 and 72 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            // The email must be free in BOTH tables, so one login can never be ambiguous
            $exists = false;
            foreach (['admins', 'patients'] as $tbl) {
                $chk = $conn->prepare("SELECT id FROM `$tbl` WHERE email = ? LIMIT 1");
                $chk->bind_param('s', $old['email']);
                $chk->execute();
                if ($chk->get_result()->num_rows > 0) { $exists = true; }
                $chk->close();
            }

            if ($exists) {
                $error = 'An account with this email already exists.';
            } else {
                // The password is ALWAYS hashed here; the account goes into the admins table
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins  = $conn->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
                $ins->bind_param('sss', $old['name'], $old['email'], $hash);
                $ins->execute();
                $ins->close();

                flash('success', 'Admin account created for ' . $old['email'] . '.');
                redirect('add_admin.php');
            }
        } catch (mysqli_sql_exception $ex) {
            error_log('Add admin error: ' . $ex->getMessage());
            $error = ($ex->getCode() === 1062)
                ? 'An account with this email already exists.'
                : 'Could not create the account. Please try again.';
        }
    }
}

$admins = [];
try {
    $admins = $conn->query("SELECT name, email FROM admins ORDER BY id")->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $ex) {
    error_log('Admin list failed: ' . $ex->getMessage());
}

$pageTitle = 'Manage Admins';
$active    = 'admins';
include '../includes/admin_header.php';
?>

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="card-box">
            <h5>Add a new admin</h5>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger"><?= h($error) ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="text" name="name" class="form-control mb-2" placeholder="Full name"
                       maxlength="100" required value="<?= h($old['name']) ?>">
                <input type="email" name="email" class="form-control mb-2" placeholder="Email"
                       maxlength="150" required value="<?= h($old['email']) ?>">
                <input type="password" name="password" class="form-control mb-2"
                       placeholder="Password (min. 8 characters)" minlength="8" maxlength="72"
                       autocomplete="new-password" required>
                <input type="password" name="confirm" class="form-control mb-3"
                       placeholder="Confirm password" minlength="8" maxlength="72"
                       autocomplete="new-password" required>
                <button type="submit" class="btn btn-success w-100">Create admin</button>
            </form>
            <p class="small text-muted mt-3 mb-0">The password is hashed before it is saved. It is never stored as plain text.</p>
        </div>
    </div>

    <div class="col-lg-7 mb-4">
        <div class="card-box">
            <h5>Current admins</h5>
            <table class="table align-middle mb-0">
                <thead><tr><th>Name</th><th>Email</th></tr></thead>
                <tbody>
                <?php foreach ($admins as $a): ?>
                    <tr><td><?= h($a['name']) ?></td><td><?= h($a['email']) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>