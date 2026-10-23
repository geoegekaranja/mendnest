<?php
require_once '../includes/auth.php';
require_once '../database/connection.php';
$admin = require_role('admin');

$allowedStatuses = ['Pending', 'Confirmed', 'Cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!csrf_valid()) {
            flash('danger', 'Your session expired. Please try again.');

        } elseif (isset($_POST['update_status'])) {
            $id     = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            if ($id > 0 && in_array($status, $allowedStatuses, true)) {
                $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
                $stmt->bind_param('si', $status, $id);
                $stmt->execute();
                $stmt->close();
                flash('success', 'Status updated.');
            }

        } elseif (isset($_POST['assign_caregiver'])) {
            $id   = (int)($_POST['id'] ?? 0);
            $cgId = (int)($_POST['caregiver_id'] ?? 0);
            $cg   = $cgId > 0 ? $cgId : null;          // null = unassigned

            $valid = true;
            if ($cg !== null) {
                $chk = $conn->prepare("SELECT id FROM caregivers WHERE id = ?");
                $chk->bind_param('i', $cg);
                $chk->execute();
                $valid = $chk->get_result()->num_rows > 0;
                $chk->close();
            }

            if ($id > 0 && $valid) {
                $stmt = $conn->prepare("UPDATE bookings SET caregiver_id = ? WHERE id = ?");
                $stmt->bind_param('ii', $cg, $id);
                $stmt->execute();
                $stmt->close();
                flash('success', $cg === null ? 'Caregiver removed from the booking.' : 'Caregiver assigned.');
            } else {
                flash('danger', 'That caregiver could not be found.');
            }
        }
    } catch (mysqli_sql_exception $ex) {
        error_log('Bookings page error: ' . $ex->getMessage());
        flash('danger', 'Something went wrong. Please try again.');
    }
    redirect('bookings.php' . (($_POST['filter'] ?? '') === 'unassigned' ? '?filter=unassigned' : ''));
}

$filter   = ($_GET['filter'] ?? '') === 'unassigned';
$where    = $filter ? "WHERE b.caregiver_id IS NULL AND b.status <> 'Cancelled'" : '';
$bookings = [];
$caregivers = [];
try {
    $bookings = $conn->query(
        "SELECT b.*, c.specialization AS caregiver_specialization
         FROM bookings b
         LEFT JOIN caregivers c ON c.id = b.caregiver_id
         $where
         ORDER BY b.id DESC"
    )->fetch_all(MYSQLI_ASSOC);

    $caregivers = $conn->query(
        "SELECT id, name, specialization, is_available FROM caregivers ORDER BY name"
    )->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $ex) {
    error_log('Bookings list failed: ' . $ex->getMessage());
}

$pageTitle = 'Manage Bookings';
$active    = 'bookings';
include '../includes/admin_header.php';
?>

<div class="card-box">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><?= $filter ? 'Unassigned bookings' : 'All bookings' ?></h5>
        <a href="bookings.php<?= $filter ? '' : '?filter=unassigned' ?>" class="btn btn-outline-secondary btn-sm">
            <?= $filter ? 'Show all' : 'Show unassigned only' ?>
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Name</th><th>Service</th><th>Caregiver</th><th>Date</th><th>Message</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($bookings as $row): ?>
                <tr>
                    <td><?= h($row['patient_name']) ?></td>
                    <td><?= h($row['service']) ?></td>

                    <td style="min-width:260px;">
                        <form method="POST" class="d-flex gap-1">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                            <input type="hidden" name="filter" value="<?= $filter ? 'unassigned' : '' ?>">
                            <select name="caregiver_id" class="form-select form-select-sm">
                                <option value="0">Unassigned</option>
                                <?php foreach ($caregivers as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>"
                                        <?= (int)$row['caregiver_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                                        <?= h($c['name'] . ' - ' . $c['specialization'] . ($c['is_available'] ? '' : ' (unavailable)')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button name="assign_caregiver" value="1" class="btn btn-outline-success btn-sm">Assign</button>
                        </form>
                        <?php if (!empty($row['caregiver_specialization'])): ?>
                            <small class="text-muted"><?= h($row['caregiver_specialization']) ?></small>
                        <?php endif; ?>
                    </td>

                    <td><?= h($row['appointment_date']) ?></td>
                    <td title="<?= h($row['message']) ?>"><?= h(mb_strimwidth((string)$row['message'], 0, 120, '...')) ?></td>

                    <td>
                        <form method="POST" class="d-flex gap-1">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                            <input type="hidden" name="filter" value="<?= $filter ? 'unassigned' : '' ?>">
                            <select name="status" class="form-select form-select-sm">
                                <?php foreach ($allowedStatuses as $s): ?>
                                    <option <?= ($row['status'] ?? '') === $s ? 'selected' : '' ?>><?= h($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button name="update_status" value="1" class="btn btn-primary btn-sm">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$bookings): ?>
                <tr><td colspan="6" class="text-muted">No bookings to show.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>