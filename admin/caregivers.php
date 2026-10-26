<?php
require_once '../includes/auth.php';
require_once '../database/connection.php';
require_once '../includes/accounts.php';
$admin = require_role('admin');

const GENDERS = ['', 'Female', 'Male'];

$uploadDir = dirname(__DIR__) . '/uploads/caregivers';
$error     = '';
$fields    = ['name', 'email', 'phone', 'gender', 'specialization', 'qualification',
              'license_number', 'years_experience', 'languages', 'areas_served', 'bio'];
$form      = array_fill_keys($fields, '');
$form['id'] = 0;
$form['caregiver_code'] = '';
$form['photo'] = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!csrf_valid()) {
            flash('danger', 'Your session expired. Please try again.');
            redirect('caregivers.php');

        } elseif (isset($_POST['save'])) {
            /* ---------- add or update a caregiver ---------- */
            $form['id'] = (int)($_POST['id'] ?? 0);
            foreach ($fields as $k) {
                $form[$k] = trim((string)($_POST[$k] ?? ''));
            }
            $password = (string)($_POST['password'] ?? '');
            $editing  = $form['id'] > 0;
            $years    = $form['years_experience'] === '' ? null : (int)$form['years_experience'];

            $existing = null;
            if ($editing) {
                $q = $conn->prepare("SELECT id, photo, caregiver_code FROM caregivers WHERE id = ?");
                $q->bind_param('i', $form['id']);
                $q->execute();
                $existing = $q->get_result()->fetch_assoc();
                $q->close();
                if ($existing) {
                    $form['caregiver_code'] = (string)$existing['caregiver_code'];
                    $form['photo']          = (string)$existing['photo'];
                }
            }

            if ($editing && !$existing) {
                $error = 'Caregiver not found.';
            } elseif ($form['name'] === '' || mb_strlen($form['name']) > 100) {
                $error = 'Please enter the caregiver\'s name (max 100 characters).';
            } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($form['email']) > 150) {
                $error = 'Please enter a valid email address. It is the caregiver\'s login.';
            } elseif ($form['specialization'] === '' || mb_strlen($form['specialization']) > 150) {
                $error = 'Please enter a specialization (max 150 characters).';
            } elseif ($form['phone'] !== '' && !preg_match('/^\+?[0-9\s\-()]{7,20}$/', $form['phone'])) {
                $error = 'Please enter a valid phone number.';
            } elseif (!in_array($form['gender'], GENDERS, true)) {
                $error = 'Please choose a valid gender option.';
            } elseif (mb_strlen($form['qualification']) > 200 || mb_strlen($form['license_number']) > 60
                      || mb_strlen($form['languages']) > 200 || mb_strlen($form['areas_served']) > 255) {
                $error = 'One of the profile fields is too long.';
            } elseif ($years !== null && ($years < 0 || $years > 60)) {
                $error = 'Years of experience must be between 0 and 60.';
            } elseif (mb_strlen($form['bio']) > 500) {
                $error = 'The short bio must be 500 characters or fewer.';
            } elseif (!$editing && (strlen($password) < 8 || strlen($password) > 72)) {
                $error = 'Set a password of 8 to 72 characters for the new caregiver.';
            } elseif ($editing && $password !== '' && (strlen($password) < 8 || strlen($password) > 72)) {
                $error = 'The new password must be 8 to 72 characters (or leave it empty to keep the current one).';
            } elseif (email_in_use($conn, $form['email'], 'caregivers', $form['id'])) {
                $error = 'This email already belongs to another account.';
            }

            $photoName = null;
            if ($error === '') {
                [$photoName, $photoErr] = save_uploaded_photo($_FILES['photo'] ?? ['error' => UPLOAD_ERR_NO_FILE], $uploadDir);
                if ($photoErr !== '') {
                    $error = $photoErr;
                }
            }

            if ($error === '') {
                $phone = $form['phone']          === '' ? null : $form['phone'];
                $gender= $form['gender']         === '' ? null : $form['gender'];
                $qual  = $form['qualification']  === '' ? null : $form['qualification'];
                $lic   = $form['license_number'] === '' ? null : $form['license_number'];
                $langs = $form['languages']      === '' ? null : $form['languages'];
                $areas = $form['areas_served']   === '' ? null : $form['areas_served'];
                $bio   = $form['bio']            === '' ? null : $form['bio'];

                if ($editing) {
                    $sql   = "UPDATE caregivers SET name = ?, email = ?, phone = ?, gender = ?, specialization = ?,
                                     qualification = ?, license_number = ?, years_experience = ?,
                                     languages = ?, areas_served = ?, bio = ?";
                    $types = 'sssssssisss';
                    $vals  = [$form['name'], $form['email'], $phone, $gender, $form['specialization'],
                              $qual, $lic, $years, $langs, $areas, $bio];
                    if ($photoName !== null) {
                        $sql .= ", photo = ?";  $types .= 's';  $vals[] = $photoName;
                    }
                    if ($password !== '') {
                        // a new password also clears any lock from too many wrong attempts
                        $sql .= ", password = ?, failed_attempts = 0, locked_until = NULL";
                        $types .= 's';  $vals[] = password_hash($password, PASSWORD_DEFAULT);
                    }
                    $sql .= " WHERE id = ?";  $types .= 'i';  $vals[] = $form['id'];

                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param($types, ...$vals);
                    $stmt->execute();
                    $stmt->close();

                    if ($photoName !== null) {
                        delete_caregiver_photo($existing['photo'] ?? null);   // the replaced photo
                    }
                    flash('success', 'Caregiver updated.');
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare(
                        "INSERT INTO caregivers
                         (name, email, password, phone, gender, specialization, qualification, license_number,
                          years_experience, languages, areas_served, bio, photo)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->bind_param('ssssssssissss',
                        $form['name'], $form['email'], $hash, $phone, $gender, $form['specialization'],
                        $qual, $lic, $years, $langs, $areas, $bio, $photoName);
                    $stmt->execute();
                    $newId = (int)$conn->insert_id;
                    $stmt->close();

                    // unique caregiver ID: CG-0001, CG-0002, ...
                    $c = $conn->prepare("UPDATE caregivers SET caregiver_code = CONCAT('CG-', LPAD(id, 4, '0')) WHERE id = ?");
                    $c->bind_param('i', $newId);
                    $c->execute();
                    $c->close();

                    flash('success', 'Caregiver created. They can log in with their email or caregiver ID CG-' . str_pad((string)$newId, 4, '0', STR_PAD_LEFT) . '.');
                }
                redirect('caregivers.php');
            } elseif ($photoName !== null) {
                delete_caregiver_photo($photoName);   // do not keep an upload from a failed save
            }

        } elseif (isset($_POST['toggle_available']) || isset($_POST['toggle_active'])) {
            $col  = isset($_POST['toggle_available']) ? 'is_available' : 'is_active';   // fixed names, not from the visitor
            $id   = (int)($_POST['toggle_available'] ?? $_POST['toggle_active']);
            $stmt = $conn->prepare("UPDATE caregivers SET `$col` = 1 - `$col` WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect('caregivers.php');

        } elseif (isset($_POST['delete'])) {
            $id  = (int)$_POST['delete'];
            $chk = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE caregiver_id = ?");
            $chk->bind_param('i', $id);
            $chk->execute();
            $count = (int)$chk->get_result()->fetch_row()[0];
            $chk->close();

            if ($count > 0) {
                flash('danger', 'This caregiver has bookings, so mark them unavailable and disable the login instead of deleting.');
            } else {
                $p = $conn->prepare("SELECT photo FROM caregivers WHERE id = ?");
                $p->bind_param('i', $id);
                $p->execute();
                $row = $p->get_result()->fetch_assoc();
                $p->close();

                $stmt = $conn->prepare("DELETE FROM caregivers WHERE id = ?");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
                delete_caregiver_photo($row['photo'] ?? null);
                flash('success', 'Caregiver deleted.');
            }
            redirect('caregivers.php');
        }
    } catch (mysqli_sql_exception $ex) {
        error_log('Caregivers page error: ' . $ex->getMessage());
        $error = 'Something went wrong. Please try again.';
    }

} elseif (isset($_GET['edit'])) {
    // Open the form pre-filled with the caregiver to edit
    try {
        $id   = (int)$_GET['edit'];
        $stmt = $conn->prepare("SELECT * FROM caregivers WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if ($row = $stmt->get_result()->fetch_assoc()) {
            $form['id'] = (int)$row['id'];
            foreach ($fields as $k) {
                $form[$k] = (string)($row[$k] ?? '');
            }
            $form['caregiver_code'] = (string)($row['caregiver_code'] ?? '');
            $form['photo']          = (string)($row['photo'] ?? '');
        }
        $stmt->close();
    } catch (mysqli_sql_exception $ex) {
        error_log('Caregiver load failed: ' . $ex->getMessage());
    }
}

$caregivers = [];
try {
    $caregivers = $conn->query(
        "SELECT c.id, c.name, c.caregiver_code, c.email, c.specialization, c.years_experience,
                c.photo, c.is_available, c.is_active, COUNT(b.id) AS booking_count
         FROM caregivers c
         LEFT JOIN bookings b ON b.caregiver_id = c.id
         GROUP BY c.id, c.name, c.caregiver_code, c.email, c.specialization, c.years_experience,
                  c.photo, c.is_available, c.is_active
         ORDER BY c.name"
    )->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $ex) {
    error_log('Caregiver list failed: ' . $ex->getMessage());
}

$editing   = $form['id'] > 0;
$pageTitle = 'Caregivers';
$active    = 'caregivers';
include '../includes/admin_header.php';
?>

<div class="card-box mb-4">
    <h5><?= $editing ? 'Edit caregiver' . ($form['caregiver_code'] !== '' ? ' (' . h($form['caregiver_code']) . ')' : '') : 'Add a caregiver' ?></h5>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= (int)$form['id'] ?>">

        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label small mb-0">Full name *</label>
                <input type="text" name="name" class="form-control" maxlength="100" required value="<?= h($form['name']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-0">Login email *</label>
                <input type="email" name="email" class="form-control" maxlength="150" required value="<?= h($form['email']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-0"><?= $editing ? 'New password (leave empty to keep)' : 'Password *' ?></label>
                <input type="password" name="password" class="form-control" minlength="8" maxlength="72"
                       autocomplete="new-password" <?= $editing ? '' : 'required' ?>>
            </div>

            <div class="col-md-4">
                <label class="form-label small mb-0">Specialization *</label>
                <input type="text" name="specialization" class="form-control" maxlength="150" required
                       placeholder="e.g. Elderly Care" value="<?= h($form['specialization']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-0">Qualification</label>
                <input type="text" name="qualification" class="form-control" maxlength="200"
                       placeholder="e.g. Diploma in Nursing (KRCHN)" value="<?= h($form['qualification']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-0">Licence / registration no.</label>
                <input type="text" name="license_number" class="form-control" maxlength="60" value="<?= h($form['license_number']) ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label small mb-0">Years of experience</label>
                <input type="number" name="years_experience" class="form-control" min="0" max="60" value="<?= h($form['years_experience']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0">Gender</label>
                <select name="gender" class="form-select">
                    <?php foreach (GENDERS as $g): ?>
                        <option value="<?= h($g) ?>" <?= $form['gender'] === $g ? 'selected' : '' ?>><?= $g === '' ? 'Not stated' : h($g) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0">Phone (private)</label>
                <input type="tel" name="phone" class="form-control" maxlength="30" value="<?= h($form['phone']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0">Photo (JPG, PNG or WebP, max 2 MB)</label>
                <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
            </div>

            <div class="col-md-6">
                <label class="form-label small mb-0">Languages spoken</label>
                <input type="text" name="languages" class="form-control" maxlength="200"
                       placeholder="e.g. English, Kiswahili" value="<?= h($form['languages']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label small mb-0">Areas served</label>
                <input type="text" name="areas_served" class="form-control" maxlength="255"
                       placeholder="e.g. Nairobi, Kiambu" value="<?= h($form['areas_served']) ?>">
            </div>

            <div class="col-12">
                <label class="form-label small mb-0">Short bio (shown to patients)</label>
                <textarea name="bio" class="form-control" rows="3" maxlength="500"><?= h($form['bio']) ?></textarea>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" name="save" value="1" class="btn btn-success">
                <?= $editing ? 'Save changes' : 'Create caregiver' ?>
            </button>
            <?php if ($editing): ?>
                <a href="caregivers.php" class="btn btn-outline-secondary">Cancel</a>
            <?php endif; ?>
        </div>
        <p class="small text-muted mt-2 mb-0">
            The caregiver logs in with the email above or their caregiver ID. The phone number is visible to admins and
            the caregiver only. Patients see the profile fields once a caregiver is assigned to them.
        </p>
    </form>
</div>

<div class="card-box">
    <h5>All caregivers</h5>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Caregiver</th><th>Specialization</th><th>Login</th><th>Bookings</th><th>Status</th>
                    <th style="width:330px">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($caregivers as $c): ?>
                <?php $photoUrl = caregiver_photo_url($c['photo'] ?? null); ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($photoUrl): ?>
                                <img src="<?= h($photoUrl) ?>" alt="" width="44" height="44" class="rounded-circle" style="object-fit:cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                     style="width:44px;height:44px;"><?= h(mb_strtoupper(mb_substr((string)$c['name'], 0, 1))) ?></div>
                            <?php endif; ?>
                            <div>
                                <strong><?= h($c['name']) ?></strong><br>
                                <small class="text-muted"><?= h($c['caregiver_code']) ?></small>
                            </div>
                        </div>
                    </td>
                    <td><?= h($c['specialization']) ?>
                        <?php if ($c['years_experience'] !== null): ?>
                            <br><small class="text-muted"><?= (int)$c['years_experience'] ?> yrs</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($c['email']): ?>
                            <small><?= h($c['email']) ?></small>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">No login yet</span>
                        <?php endif; ?>
                    </td>
                    <td><?= (int)$c['booking_count'] ?></td>
                    <td>
                        <span class="badge <?= $c['is_available'] ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $c['is_available'] ? 'Available' : 'Unavailable' ?>
                        </span><br>
                        <span class="badge <?= $c['is_active'] ? 'bg-info text-dark' : 'bg-danger' ?>">
                            <?= $c['is_active'] ? 'Login enabled' : 'Login disabled' ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <a href="caregivers.php?edit=<?= (int)$c['id'] ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                            <form method="POST">
                                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                                <button name="toggle_available" value="<?= (int)$c['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <?= $c['is_available'] ? 'Set unavailable' : 'Set available' ?>
                                </button>
                            </form>
                            <form method="POST">
                                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                                <button name="toggle_active" value="<?= (int)$c['id'] ?>" class="btn btn-outline-warning btn-sm">
                                    <?= $c['is_active'] ? 'Disable login' : 'Enable login' ?>
                                </button>
                            </form>
                            <form method="POST" onsubmit="return confirm('Delete this caregiver?');">
                                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                                <button name="delete" value="<?= (int)$c['id'] ?>" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$caregivers): ?>
                <tr><td colspan="6" class="text-muted">No caregivers yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>