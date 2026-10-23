<?php
require_once 'includes/auth.php';
require_once 'database/connection.php';

// Already signed in? Send them to the right area.
if ($u = current_user()) {
    redirect_for_role($u['role']);
}

const MAX_ATTEMPTS = 5;     // wrong passwords before the account is locked
const LOCK_MINUTES = 15;

/* Admin passwords typed straight into the database use this marker:
       password column  =  #$YourPassword      (admins table only)
   At the admin's first successful login the site replaces it with a real
   bcrypt hash ($2y$...). The admin logs in with  YourPassword  (WITHOUT the #$). */
const ADMIN_TEMP_PREFIX = '#$';

/* What happens to a "#$..." password after a successful login?
     false = nothing. The password stays exactly as you saved it and works every time.
             Login never rewrites ANY password in the database.
     true  = the site replaces it with a real hash (recommended before the site goes live).
             From then on you log in with the plain password only, and "#$..." stops working. */
const UPGRADE_PASSWORDS_ON_LOGIN = false;

/* The role is decided by WHICH TABLE the account is stored in:
       admins   -> 'admin'
       patients -> 'patient'
   There is no role column that could be wrong. */
const ACCOUNT_TABLES = ['admins' => 'admin', 'patients' => 'patient'];

// On your own computer (localhost) the technical reason for a database error is shown;
// visitors on a live site never see it.
function dev_detail(Throwable $ex): string {
    return in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)
        ? ' [Technical detail: ' . $ex->getMessage() . ']' : '';
}

/* Finds the account by email: admins first, then patients.
   Adds 'role' and 'tbl' (the table it came from) to the row. */
function find_account(mysqli $conn, string $email): ?array {
    foreach (ACCOUNT_TABLES as $tbl => $role) {
        $stmt = $conn->prepare(
            "SELECT id, name, email, password, is_active,
                    (locked_until IS NOT NULL AND locked_until > NOW()) AS is_locked
             FROM `$tbl` WHERE email = ? LIMIT 1"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row) {
            $row['role'] = $role;
            $row['tbl']  = $tbl;
            return $row;
        }
    }
    return null;
}

$error = '';
$info  = isset($_GET['timeout'])
    ? 'You were signed out due to inactivity. Please sign in again.'
    : (isset($_GET['loggedout']) ? 'You have been signed out.' : '');
$mode  = 'login';           // which form to show: 'login' or 'register'
$old   = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ================= LOGIN ================= */
    if (isset($_POST['login'])) {
        $mode = 'login';
        $email    = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $old['email'] = $email;

        if (!csrf_valid()) {
            $error = 'Your session expired. Please try again.';
        } elseif ($email === '' || $password === '') {
            $error = 'Please enter your email and password.';
        } else {
            try {
                $user = find_account($conn, $email);

                if ($user && $user['is_locked']) {
                    $error = 'Too many failed attempts. Please try again in ' . LOCK_MINUTES . ' minutes.';
                } else {
                    // Verify against a dummy hash if the email is unknown, so timing doesn't reveal which emails exist
                    $stored = $user['password'] ?? password_hash('dummy-password', PASSWORD_DEFAULT);

                    if ($user !== null && $user['role'] === 'admin' && str_starts_with($stored, ADMIN_TEMP_PREFIX)) {
                        // "#$" format: only ever accepted for rows in the admins table
                        $ok = hash_equals(substr($stored, strlen(ADMIN_TEMP_PREFIX)), $password);
                    } else {
                        // normal case: a real password hash
                        $ok = password_verify($password, $stored) && $user !== null;
                    }

                    if ($ok && !$user['is_active']) {
                        $error = 'This account has been disabled. Please contact support.';
                    } elseif ($ok) {
                        $tbl = $user['tbl'];   // 'admins' or 'patients' (from the constant above, never from the visitor)

                        // Optional: replace a "#$..." password (or an outdated hash) with a fresh real hash
                        if (UPGRADE_PASSWORDS_ON_LOGIN
                            && (($user['role'] === 'admin' && str_starts_with($user['password'], ADMIN_TEMP_PREFIX))
                                || password_needs_rehash($user['password'], PASSWORD_DEFAULT))) {
                            $new = password_hash($password, PASSWORD_DEFAULT);
                            $up = $conn->prepare("UPDATE `$tbl` SET password = ? WHERE id = ?");
                            $up->bind_param('si', $new, $user['id']);
                            $up->execute();
                            $up->close();
                        }
                        $up = $conn->prepare(
                            "UPDATE `$tbl` SET failed_attempts = 0, locked_until = NULL, last_login_at = NOW() WHERE id = ?"
                        );
                        $up->bind_param('i', $user['id']);
                        $up->execute();
                        $up->close();

                        login_user($user);

if ($user['tbl'] === 'admins') {
    redirect(ROLE_HOME['admin']);
} elseif ($user['tbl'] === 'patients') {
    redirect(ROLE_HOME['patient']);
} else {
    redirect(LOGIN_URL);
}
                    } else {
                        if ($user) {
                            $tbl = $user['tbl'];
                            // locked_until is assigned first so it sees the count BEFORE this failure is added
                            $up = $conn->prepare(
                                "UPDATE `$tbl`
                                 SET locked_until = IF(failed_attempts + 1 >= ?, DATE_ADD(NOW(), INTERVAL ? MINUTE), locked_until),
                                     failed_attempts = failed_attempts + 1
                                 WHERE id = ?"
                            );
                            $max = MAX_ATTEMPTS; $min = LOCK_MINUTES;
                            $up->bind_param('iii', $max, $min, $user['id']);
                            $up->execute();
                            $up->close();
                        }
                        $error = 'Invalid email or password.';   // same message for both cases
                    }
                }
            } catch (mysqli_sql_exception $ex) {
                error_log('Login error: ' . $ex->getMessage());
                $error = 'Something went wrong. Please try again.' . dev_detail($ex);
            }
        }

    /* ================= REGISTER (patients only) ================= */
    } elseif (isset($_POST['register'])) {
        $mode = 'register';
        $old['name']  = trim((string)($_POST['name'] ?? ''));
        $old['email'] = trim((string)($_POST['email'] ?? ''));
        $password     = (string)($_POST['password'] ?? '');
        $confirm      = (string)($_POST['confirm'] ?? '');

        if (!csrf_valid()) {
            $error = 'Your session expired. Please try again.';
        } elseif ($old['name'] === '' || mb_strlen($old['name']) > 100) {
            $error = 'Please enter your full name (max 100 characters).';
        } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 150) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8 || strlen($password) > 72) {
            $error = 'Password must be between 8 and 72 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            try {
                // The email must be free in BOTH tables
                $exists = find_account($conn, $old['email']) !== null;

                if ($exists) {
                    $error = 'An account with this email already exists.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    // Self-registration only ever writes to the patients table, so it can never create an admin
                    $ins = $conn->prepare("INSERT INTO patients (name, email, password) VALUES (?, ?, ?)");
                    $ins->bind_param('sss', $old['name'], $old['email'], $hash);
                    $ins->execute();
                    $ins->close();

                    flash('success', 'Account created successfully! You can now sign in.');
                    redirect(LOGIN_URL);
                }
            } catch (mysqli_sql_exception $ex) {
                if ($ex->getCode() === 1062) {
                    $error = 'An account with this email already exists.';
                } else {
                    error_log('Register error: ' . $ex->getMessage());
                    $error = 'Registration failed. Please try again.' . dev_detail($ex);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login / Register</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family: 'Segoe UI', sans-serif; background: #f9fcfc; }
.auth-box { max-width: 400px; margin: 80px auto; background: white; padding: 30px;
            border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.2); }
.toggle-btn { cursor: pointer; color: #198754; }
</style>
</head>
<body>

<div class="auth-box">

<h3 class="text-center mb-3" id="form-title">Login</h3>

<?= flash_render() ?>
<?php if ($info !== ''): ?><div class="alert alert-info"><?= h($info) ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

<!-- LOGIN FORM -->
<form method="POST" id="loginForm">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="email" name="email" class="form-control mb-2" placeholder="Email" aria-label="Email"
           maxlength="150" autocomplete="username" required
           value="<?= $mode === 'login' ? h($old['email']) : '' ?>">
    <input type="password" name="password" class="form-control mb-2" placeholder="Password"
           aria-label="Password" autocomplete="current-password" required>
    <button type="submit" name="login" class="btn btn-success w-100">Login</button>
</form>

<!-- REGISTER FORM -->
<form method="POST" id="registerForm" style="display:none;">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="text" name="name" class="form-control mb-2" placeholder="Full Name" aria-label="Full name"
           maxlength="100" required value="<?= $mode === 'register' ? h($old['name']) : '' ?>">
    <input type="email" name="email" class="form-control mb-2" placeholder="Email" aria-label="Email"
           maxlength="150" autocomplete="username" required
           value="<?= $mode === 'register' ? h($old['email']) : '' ?>">
    <input type="password" name="password" class="form-control mb-2" placeholder="Password (min. 8 characters)"
           aria-label="Password" minlength="8" maxlength="72" autocomplete="new-password" required>
    <input type="password" name="confirm" class="form-control mb-2" placeholder="Confirm Password"
           aria-label="Confirm password" minlength="8" maxlength="72" autocomplete="new-password" required>
    <button type="submit" name="register" class="btn btn-primary w-100">Register</button>
</form>

<p class="text-center mt-3">
    <span id="toggleText">Don't have an account?</span>
    <span class="toggle-btn" onclick="toggleForm()"> Register</span>
</p>

</div>

<script>
var currentMode = 'login';
function setMode(mode) {
    currentMode = mode;
    var isLogin = (mode === 'login');
    document.getElementById("loginForm").style.display    = isLogin ? "block" : "none";
    document.getElementById("registerForm").style.display = isLogin ? "none"  : "block";
    document.getElementById("form-title").innerText = isLogin ? "Login" : "Register";
    document.getElementById("toggleText").innerText = isLogin ? "Don't have an account?" : "Already have an account?";
    document.querySelector(".toggle-btn").innerText = isLogin ? " Register" : " Login";
}
function toggleForm() { setMode(currentMode === 'login' ? 'register' : 'login'); }
setMode('<?= $mode === 'register' ? 'register' : 'login' ?>');
</script>

</body>
</html>