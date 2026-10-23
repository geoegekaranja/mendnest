<?php
require_once '../includes/auth.php';
require_once '../database/connection.php';
$admin = require_role('admin');

$error = '';
$form  = ['id' => 0, 'name' => '', 'specialization' => '', 'years_experience' => '', 'bio' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!csrf_valid()) {
            flash('danger', 'Your session expired. Please try again.');
            redirect('caregivers.php');

        } elseif (isset($_POST['add']) || isset($_POST['update'])) {
            $form['id'] = (int)($_POST['id'] ?? 0);
            foreach (['name', 'specialization', 'years_experience', 'bio'] as $key) {
                $form[$key] = trim((string)($_POST[$key] ?? ''));
            }
            $years = $form['years_experience'] === '' ? null : (int)$form['years_experience'];

            if ($form['name'] === '' || mb_strlen($form['name']) > 100) {
                $error = 'Please enter the caregiver\'s name (max 100 characters).';
            } elseif ($form['specialization'] === '' || mb_strlen($form['specialization']) > 150) {
                $error = 'Please enter a specialization (max 150 characters).';
            } elseif ($years !== null && ($years < 0 || $years > 60)) {
                $error = 'Years of experience must be between 0 and 60.';
            } elseif (mb_strlen($form['bio']) > 500) {
                $error = 'The short bio must be 500 characters or fewer.';
            } elseif (isset($_POST['update']) && $form['id'] < 1) {
                $error = 'Caregiver not found.';
            } else {
                if (isset($_POST['update'])) {
                    $stmt = $conn->prepare(
                        "UPDATE caregivers SET name = ?, specialization = ?, bio = ?, years_experience = ? WHERE id = ?"
                    );
                    $stmt->bind_param('sssii', $form['name'], $form['specialization'], $form['bio'], $years, $form['id']);
                    $msg = 'Caregiver updated.';
                } else {
                    $stmt = $conn->prepare(
                        "INSERT INTO caregivers (name, specialization, bio, years_experience) VALUES (?, ?, ?, ?)"
                    );
                    $stmt->bind_param('sssi', $form['name'], $form['specialization'], $form['bio'], $years);
                    $msg = 'Caregiver added.';
                }
                $stmt->execute();
                $stmt->close();
                flash('success', $msg);
                redirect('caregivers.php');
            }

        } elseif (isset($_POST['toggle'])) {
            $id   = (int)$_POST['toggle'];
            $stmt = $conn->prepare("UPDATE caregivers SET is_available = 1 - is_available WHERE id = ?");
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
                flash('danger', 'This caregiver has bookings, so mark them unavailable instead of deleting.');
            } else {
                $stmt = $conn->prepare("DELETE FROM caregivers WHERE id = ?");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
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
        $stmt = $conn->prepare("SELECT id, name, specialization, years_experience, bio FROM caregivers WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if ($row = $stmt->get_result()->fetch_assoc()) {
            $form = [
                'id'               => (int)$row['id'],
                'name'             => (string)$row['name'],
                'specialization'   => (string)$row['specialization'],
                'years_experience' => $row['years_experience'] === null ? '' : (string)$row['years_experience'],
                'bio'              => (string)$row['bio'],
            ];
        }
        $stmt->close();
    } catch (mysqli_sql_exception $ex) {
        error_log('Caregiver load failed: ' . $ex->getMessage());
    }
}

$caregivers = [];
try {
    $caregivers = $conn->query(
        "SELECT c.id, c.name, c.specialization, c.years_experience, c.is_available,
                COUNT(b.id) AS booking_count
         FROM caregivers c
         LEFT JOIN bookings b ON b.caregiver_id = c.id
         GROUP BY c.id, c.name, c.specialization, c.years_experience, c.is_available
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

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card-box">
            <h5><?= $editing ? 'Edit caregiver' : 'Add a caregiver' ?></h5>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger"><?= h($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int)$form['id'] ?>">
                <input type="text" name="name" class="form-control mb-2" placeholder="Full name"
                       maxlength="100" required value="<?= h($form['name']) ?>">
                <input type="text" name="specialization" class="form-control mb-2"
                       placeholder="Specialization (e.g. Elderly Care)" maxlength="150" required
                       value="<?= h($form['specialization']) ?>">
                <input type="number" name="years_experience" class="form-control mb-2"
                       placeholder="Years of experience" min="0" max="60"
                       value="<?= h($form['years_experience']) ?>">
                <textarea name="bio" class="form-control mb-3" rows="3" maxlength="500"
                          placeholder="Short bio (shown to patients)"><?= h($form['bio']) ?></textarea>

                <?php if ($editing): ?>
                    <button type="submit" name="update" value="1" class="btn btn-success w-100 mb-2">Save changes</button>
                    <a href="caregivers.php" class="btn btn-outline-secondary w-100">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add" value="1" class="btn btn-success w-100">Add caregiver</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="col-lg-8 mb-4">
        <div class="card-box">
            <h5>All caregivers</h5>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th><th>Specialization</th><th>Exp.</th><th>Bookings</th>
                            <th>Status</th><th style="width:260px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($caregivers as $c): ?>
                        <tr>
                            <td><?= h($c['name']) ?></td>
                            <td><?= h($c['specialization']) ?></td>
                            <td><?= $c['years_experience'] !== null ? (int)$c['years_experience'] . ' yrs' : '-' ?></td>
                            <td><?= (int)$c['booking_count'] ?></td>
                            <td>
                                <span class="badge <?= $c['is_available'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $c['is_available'] ? 'Available' : 'Unavailable' ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="caregivers.php?edit=<?= (int)$c['id'] ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                                    <form method="POST">
                                        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                                        <button name="toggle" value="<?= (int)$c['id'] ?>" class="btn btn-outline-primary btn-sm">
                                            <?= $c['is_available'] ? 'Set unavailable' : 'Set available' ?>
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
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>