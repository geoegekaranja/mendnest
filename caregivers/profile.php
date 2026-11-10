<?php
require_once '../includes/auth.php';
require_once '../database/connection.php';
require_once '../includes/accounts.php';
$me  = require_role('caregiver');
$cid = (int)$me['id'];

$profileError  = '';
$passwordError = '';

// Load the current profile
$row = null;
try {
    $stmt = $conn->prepare("SELECT * FROM caregivers WHERE id = ?");
    $stmt->bind_param('i', $cid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (mysqli_sql_exception $ex) {
    error_log('Caregiver profile load failed: ' . $ex->getMessage());
}
if (!$row) {
    flash('danger', 'Your profile could not be loaded. Please contact the admin.');
    redirect(ROLE_HOME['caregiver']);
}

$form = [
    'phone'        => (string)($row['phone'] ?? ''),
    'languages'    => (string)($row['languages'] ?? ''),
    'areas_served' => (string)($row['areas_served'] ?? ''),
    'bio'          => (string)($row['bio'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!csrf_valid()) {
            flash('danger', 'Your session expired. Please try again.');
            redirect('profile.php');

        } elseif (isset($_POST['save_profile'])) {
            foreach ($form as $k => $_) {
                $form[$k] = trim((string)($_POST[$k] ?? ''));
            }
            if ($form['phone'] !== '' && !preg_match('/^\+?[0-9\s\-()]{7,20}$/', $form['phone'])) {
                $profileError = 'Please enter a valid phone number.';
            } elseif (mb_strlen($form['languages']) > 200 || mb_strlen($form['areas_served']) > 255) {
                $profileError = 'Languages or areas served is too long.';
            } elseif (mb_strlen($form['bio']) > 500) {
                $profileError = 'The bio must be 500 characters or fewer.';
            } else {
                $phone = $form['phone']        === '' ? null : $form['phone'];
                $langs = $form['languages']    === '' ? null : $form['languages'];
                $areas = $form['areas_served'] === '' ? null : $form['areas_served'];
                $bio   = $form['bio']          === '' ? null : $form['bio'];

                $stmt = $conn->prepare("UPDATE caregivers SET phone = ?, languages = ?, areas_served = ?, bio = ? WHERE id = ?");
                $stmt->bind_param('ssssi', $phone, $langs, $areas, $bio, $cid);
                $stmt->execute();
                $stmt->close();
                flash('success', 'Your profile has been updated.');
                redirect('profile.php');
            }

        } elseif (isset($_POST['change_password'])) {
            $current = (string)($_POST['current_password'] ?? '');
            $new     = (string)($_POST['new_password'] ?? '');
            $confirm = (string)($_POST['confirm_password'] ?? '');

            if (!password_verify($current, (string)($row['password'] ?? ''))) {
                $passwordError = 'Your current password is not correct.';
            } elseif (strlen($new) < 8 || strlen($new) > 72) {
                $passwordError = 'The new password must be 8 to 72 characters.';
            } elseif ($new !== $confirm) {
                $passwordError = 'The new passwords do not match.';
            } else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE caregivers SET password = ? WHERE id = ?");
                $stmt->bind_param('si', $hash, $cid);
                $stmt->execute();
                $stmt->close();
                flash('success', 'Your password has been changed.');
                redirect('profile.php');
            }
        }
    } catch (mysqli_sql_exception $ex) {
        error_log('Caregiver profile save failed: ' . $ex->getMessage());
        $profileError = 'Something went wrong. Please try again.';
    }
}

$photoUrl = caregiver_photo_url($row['photo'] ?? null);

$pageTitle = 'My Profile';
$active    = 'profile';
include '../includes/dashboard_header.php';
?>

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="card-box">
            <div class="d-flex align-items-center gap-3 mb-3">
                <?php if ($photoUrl): ?>
                    <img src="<?= h($photoUrl) ?>" alt="" width="72" height="72" class="rounded-circle" style="object-fit:cover;">
                <?php else: ?>
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fs-3"
                         style="width:72px;height:72px;"><?= h(mb_strtoupper(mb_substr((string)$row['name'], 0, 1))) ?></div>
                <?php endif; ?>
                <div>
                    <h5 class="mb-0 text-body" style="text-transform:none;letter-spacing:0;font-size:1.1rem;"><?= h($row['name']) ?></h5>
                    <div class="text-muted small">Caregiver ID <strong><?= h($row['caregiver_code']) ?></strong></div>
                </div>
            </div>

            <table class="table table-sm mb-2">
                <tr><th>Login email</th><td><?= h($row['email']) ?></td></tr>
                <tr><th>Specialization</th><td><?= h($row['specialization']) ?></td></tr>
                <tr><th>Qualification</th><td><?= h($row['qualification'] ?: '-') ?></td></tr>
                <tr><th>Licence no.</th><td><?= h($row['license_number'] ?: '-') ?></td></tr>
                <tr><th>Experience</th><td><?= $row['years_experience'] !== null ? (int)$row['years_experience'] . ' years' : '-' ?></td></tr>
            </table>
            <p class="small text-muted mb-0">These details are managed by the admin. To change them, or to add a photo, contact the admin.</p>
        </div>
    </div>

    <div class="col-lg-7 mb-4">
        <div class="card-box mb-4">
            <h5>Details patients see</h5>
            <?php if ($profileError !== ''): ?><div class="alert alert-danger"><?= h($profileError) ?></div><?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <label class="form-label small mb-0">Phone (private, visible to admins only)</label>
                <input type="tel" name="phone" class="form-control mb-2" maxlength="30" value="<?= h($form['phone']) ?>">
                <label class="form-label small mb-0">Languages spoken</label>
                <input type="text" name="languages" class="form-control mb-2" maxlength="200" value="<?= h($form['languages']) ?>">
                <label class="form-label small mb-0">Areas served</label>
                <input type="text" name="areas_served" class="form-control mb-2" maxlength="255" value="<?= h($form['areas_served']) ?>">
                <label class="form-label small mb-0">Short bio</label>
                <textarea name="bio" class="form-control mb-3" rows="3" maxlength="500"><?= h($form['bio']) ?></textarea>
                <button type="submit" name="save_profile" value="1" class="btn btn-success">Save profile</button>
            </form>
        </div>

        <div class="card-box">
            <h5>Change password</h5>
            <?php if ($passwordError !== ''): ?><div class="alert alert-danger"><?= h($passwordError) ?></div><?php endif; ?>
            <form method="POST" autocomplete="off">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="password" name="current_password" class="form-control mb-2" placeholder="Current password"
                       autocomplete="current-password" required>
                <input type="password" name="new_password" class="form-control mb-2" placeholder="New password (min. 8 characters)"
                       minlength="8" maxlength="72" autocomplete="new-password" required>
                <input type="password" name="confirm_password" class="form-control mb-3" placeholder="Confirm new password"
                       minlength="8" maxlength="72" autocomplete="new-password" required>
                <button type="submit" name="change_password" value="1" class="btn btn-outline-success">Change password</button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/dashboard_footer.php'; ?>