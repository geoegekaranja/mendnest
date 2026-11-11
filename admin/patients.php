<?php
require_once '../includes/auth.php';
require_once '../database/connection.php';
$admin = require_role('admin');

$q = trim((string)($_GET['q'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $back = trim((string)($_POST['q'] ?? ''));
    try {
        if (!csrf_valid()) {
            flash('danger', 'Your session expired. Please try again.');

        } elseif (isset($_POST['reset_password'])) {
            $id = (int)($_POST['id'] ?? 0);
            $pw = (string)($_POST['new_password'] ?? '');

            if ($id < 1) {
                flash('danger', 'Patient not found.');
            } elseif (strlen($pw) < 8 || strlen($pw) > 72) {
                flash('danger', 'The new password must be 8 to 72 characters.');
            } else {
                // Always stored as a hash. A new password also clears any lock.
                $hash = password_hash($pw, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE patients SET password = ?, failed_attempts = 0, locked_until = NULL WHERE id = ?");
                $stmt->bind_param('si', $hash, $id);
                $stmt->execute();
                $stmt->close();
                flash('success', 'The password has been reset. Give the patient the new password and ask them to keep it private.');
            }

        } elseif (isset($_POST['unlock'])) {
            $id   = (int)$_POST['unlock'];
            $stmt = $conn->prepare("UPDATE patients SET failed_attempts = 0, locked_until = NULL WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            flash('success', 'The account has been unlocked.');

        } elseif (isset($_POST['toggle_active'])) {
            $id   = (int)$_POST['toggle_active'];
            $stmt = $conn->prepare("UPDATE patients SET is_active = 1 - is_active WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            flash('success', 'The login has been updated.');
        }
    } catch (mysqli_sql_exception $ex) {
        error_log('Patients page error: ' . $ex->getMessage());
        flash('danger', 'Something went wrong. Please try again.');
    }
    redirect('patients.php' . ($back !== '' ? '?q=' . urlencode($back) : ''));
}

$patients = [];
try {
    $sql = "SELECT p.id, p.name, p.email, p.phone, p.is_active, p.failed_attempts, p.locked_until,
                   p.last_login_at, p.created_at,
                   (p.locked_until IS NOT NULL AND p.locked_until > NOW()) AS is_locked,
                   COUNT(b.id) AS booking_count
            FROM patients p
            LEFT JOIN bookings b ON b.patient_id = p.id";
    if ($q !== '') {
        $sql .= " WHERE p.name LIKE ? OR p.email LIKE ?";
    }
    $sql .= " GROUP BY p.id, p.name, p.email, p.phone, p.is_active, p.failed_attempts, p.locked_until,
                       p.last_login_at, p.created_at
              ORDER BY p.id DESC
              LIMIT 200";

    $stmt = $conn->prepare($sql);
    if ($q !== '') {
        $like = '%' . $q . '%';
        $stmt->bind_param('ss', $like, $like);
    }
    $stmt->execute();
    $patients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $ex) {
    error_log('Patients list failed: ' . $ex->getMessage());
}

$pageTitle = 'Patients';
$active    = 'patients';
include '../includes/admin_header.php';
?>

<div class="card-box">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h5 class="mb-0">All patients (<?= count($patients) ?>)</h5>
        <form method="GET" class="d-flex gap-1">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Search name or email"
                   value="<?= h($q) ?>" style="min-width:220px;">
            <button class="btn btn-outline-secondary btn-sm">Search</button>
            <?php if ($q !== ''): ?><a href="patients.php" class="btn btn-outline-secondary btn-sm">Clear</a><?php endif; ?>
        </form>
    </div>

    <p class="small text-muted">
        Patient passwords are stored as hashes, so nobody can read them. If a patient has forgotten theirs,
        set a new one here, then give it to them.
    </p>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>Patient</th><th>Bookings</th><th>Last login</th><th>Status</th><th style="width:380px">Action</th></tr>
            </thead>
            <tbody>
            <?php foreach ($patients as $p): ?>
                <tr>
                    <td>
                        <strong><?= h($p['name']) ?></strong><br>
                        <small class="text-muted"><?= h($p['email']) ?><?= !empty($p['phone']) ? ' &middot; ' . h($p['phone']) : '' ?></small>
                    </td>
                    <td><?= (int)$p['booking_count'] ?></td>
                    <td><small><?= $p['last_login_at'] ? h($p['last_login_at']) : 'never' ?></small></td>
                    <td>
                        <span class="badge <?= $p['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                            <?= $p['is_active'] ? 'Login enabled' : 'Login disabled' ?>
                        </span>
                        <?php if ($p['is_locked']): ?>
                            <br><span class="badge bg-warning text-dark">Locked until <?= h(substr((string)$p['locked_until'], 11, 5)) ?></span>
                        <?php elseif ((int)$p['failed_attempts'] > 0): ?>
                            <br><small class="text-muted"><?= (int)$p['failed_attempts'] ?> wrong password(s)</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" class="d-flex gap-1 mb-1" autocomplete="off">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <input type="hidden" name="q" value="<?= h($q) ?>">
                            <input type="text" name="new_password" class="form-control form-control-sm"
                                   placeholder="New password (min. 8)" minlength="8" maxlength="72"
                                   autocomplete="new-password" required>
                            <button name="reset_password" value="1" class="btn btn-success btn-sm text-nowrap">Set password</button>
                        </form>
                        <div class="d-flex gap-1">
                            <?php if ($p['is_locked'] || (int)$p['failed_attempts'] > 0): ?>
                                <form method="POST">
                                    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                                    <input type="hidden" name="q" value="<?= h($q) ?>">
                                    <button name="unlock" value="<?= (int)$p['id'] ?>" class="btn btn-outline-primary btn-sm">Unlock</button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" onsubmit="return confirm('Change this patient\'s login access?');">
                                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="q" value="<?= h($q) ?>">
                                <button name="toggle_active" value="<?= (int)$p['id'] ?>" class="btn btn-outline-warning btn-sm">
                                    <?= $p['is_active'] ? 'Disable login' : 'Enable login' ?>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$patients): ?>
                <tr><td colspan="5" class="text-muted">No patients found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>