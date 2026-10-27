<?php
require_once '../includes/auth.php';
require_once '../database/connection.php';
require_once '../includes/accounts.php';
$admin = require_role('admin');

$allowedStatuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];
$filters         = ['unassigned', 'pending'];
$filter          = in_array($_GET['filter'] ?? '', $filters, true) ? $_GET['filter'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $back = in_array($_POST['filter'] ?? '', $filters, true) ? $_POST['filter'] : '';
    try {
        if (!csrf_valid()) {
            flash('danger', 'Your session expired. Please try again.');

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

            if ($id < 1 || !$valid) {
                flash('danger', 'That caregiver could not be found.');
            } elseif ($cg === null) {
                $stmt = $conn->prepare("UPDATE bookings SET caregiver_id = NULL WHERE id = ?");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
                flash('success', 'Caregiver removed from the booking.');
            } else {
                // Assigning a caregiver to a Pending request confirms it
                $stmt = $conn->prepare(
                    "UPDATE bookings SET caregiver_id = ?, status = IF(status = 'Pending', 'Confirmed', status) WHERE id = ?"
                );
                $stmt->bind_param('ii', $cg, $id);
                $stmt->execute();
                $stmt->close();
                flash('success', 'Caregiver assigned. The patient can now see their profile.');
            }

        } elseif (isset($_POST['update_status'])) {
            $id     = (int)($_POST['id'] ?? 0);
            $status = (string)($_POST['status'] ?? '');

            if ($id < 1 || !in_array($status, $allowedStatuses, true)) {
                flash('danger', 'Invalid status.');
            } else {
                $c = $conn->prepare("SELECT caregiver_id FROM bookings WHERE id = ?");
                $c->bind_param('i', $id);
                $c->execute();
                $row = $c->get_result()->fetch_assoc();
                $c->close();

                if (in_array($status, ['Confirmed', 'Completed'], true) && empty($row['caregiver_id'])) {
                    flash('danger', 'Assign a caregiver before marking a booking ' . strtolower($status) . '.');
                } else {
                    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
                    $stmt->bind_param('si', $status, $id);
                    $stmt->execute();
                    $stmt->close();
                    flash('success', 'Status updated.');
                }
            }
        }
    } catch (mysqli_sql_exception $ex) {
        error_log('Bookings page error: ' . $ex->getMessage());
        flash('danger', 'Something went wrong. Please try again.');
    }
    redirect('bookings.php' . ($back !== '' ? '?filter=' . $back : ''));
}

$where = '';
if ($filter === 'unassigned') {
    $where = "WHERE b.caregiver_id IS NULL AND b.status NOT IN ('Cancelled','Completed')";
} elseif ($filter === 'pending') {
    $where = "WHERE b.status = 'Pending'";
}

$bookings   = [];
$caregivers = [];
try {
    $bookings = $conn->query(
        "SELECT b.*, c.name AS caregiver_name, c.specialization AS caregiver_spec, c.caregiver_code,
                rc.name AS requested_name
         FROM bookings b
         LEFT JOIN caregivers c  ON c.id  = b.caregiver_id
         LEFT JOIN caregivers rc ON rc.id = b.requested_caregiver_id
         $where
         ORDER BY b.id DESC
         LIMIT 100"
    )->fetch_all(MYSQLI_ASSOC);

    $caregivers = $conn->query(
        "SELECT id, name, specialization, is_available, is_active FROM caregivers ORDER BY name"
    )->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $ex) {
    error_log('Bookings list failed: ' . $ex->getMessage());
}

$statusClass = [
    'Pending'   => 'bg-warning text-dark',
    'Confirmed' => 'bg-success',
    'Completed' => 'bg-info text-dark',
    'Cancelled' => 'bg-secondary',
];
$tabs = ['' => 'All', 'unassigned' => 'Needs a caregiver', 'pending' => 'Pending'];

$pageTitle = 'Manage Bookings';
$active    = 'bookings';
include '../includes/admin_header.php';
?>

<ul class="nav nav-pills mb-3">
    <?php foreach ($tabs as $key => $label): ?>
        <li class="nav-item">
            <a class="nav-link <?= $filter === $key ? 'active bg-success' : 'text-success' ?>"
               href="bookings.php<?= $key !== '' ? '?filter=' . h($key) : '' ?>"><?= h($label) ?></a>
        </li>
    <?php endforeach; ?>
</ul>

<?php foreach ($bookings as $b): ?>
    <?php $selected = (int)($b['caregiver_id'] ?: ($b['requested_caregiver_id'] ?? 0)); ?>
    <div class="card-box mb-3">
        <div class="d-flex justify-content-between flex-wrap gap-2">
            <div>
                <strong class="fs-5"><?= h($b['patient_name']) ?></strong>
                <div class="small text-muted">
                    Booking #<?= (int)$b['id'] ?> &middot; <?= h(substr((string)$b['created_at'], 0, 16)) ?>
                    <?= !empty($b['phone']) ? ' &middot; ' . h($b['phone']) : '' ?>
                </div>
            </div>
            <span class="badge align-self-start <?= h($statusClass[$b['status']] ?? 'bg-secondary') ?>"><?= h($b['status']) ?></span>
        </div>

        <div class="row mt-2 small">
            <div class="col-md-4">
                <strong>Care:</strong> <?= h($b['service']) ?><?= !empty($b['package']) ? ' (' . h($b['package']) . ')' : '' ?><br>
                <strong>When:</strong> <?= h($b['appointment_date']) ?><?= !empty($b['preferred_time']) ? ', ' . h($b['preferred_time']) : '' ?>
                <?= !empty($b['duration']) ? '<br><strong>For:</strong> ' . h($b['duration']) : '' ?>
            </div>
            <div class="col-md-4">
                <?php if (!empty($b['service_address'])): ?>
                    <strong>Where:</strong> <?= h($b['service_address']) ?><?= !empty($b['city']) ? ', ' . h($b['city']) : '' ?><br>
                <?php endif; ?>
                <?php if (!empty($b['requested_name'])): ?>
                    <strong>Patient prefers:</strong> <?= h($b['requested_name']) ?>
                <?php endif; ?>
            </div>
            <div class="col-md-4"><strong>Notes:</strong> <?= h($b['message']) ?></div>
        </div>

        <hr class="my-2">

        <div class="row g-2 align-items-end">
            <div class="col-lg-7">
                <form method="POST" class="d-flex gap-1">
                    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                    <input type="hidden" name="filter" value="<?= h($filter) ?>">
                    <select name="caregiver_id" class="form-select form-select-sm">
                        <option value="0">Unassigned</option>
                        <?php foreach ($caregivers as $c): ?>
                            <?php
                            $note = '';
                            if (!$c['is_available'] || !$c['is_active']) { $note = ' (unavailable)'; }
                            if ((int)$c['id'] === (int)($b['requested_caregiver_id'] ?? 0)) { $note .= ' (preferred)'; }
                            ?>
                            <option value="<?= (int)$c['id'] ?>" <?= $selected === (int)$c['id'] ? 'selected' : '' ?>>
                                <?= h($c['name'] . ' - ' . $c['specialization'] . $note) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button name="assign_caregiver" value="1" class="btn btn-success btn-sm">Assign</button>
                </form>
                <?php if (!empty($b['caregiver_name'])): ?>
                    <div class="small text-muted mt-1">Assigned: <?= h($b['caregiver_name']) ?> (<?= h($b['caregiver_code']) ?>), <?= h($b['caregiver_spec']) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-lg-5">
                <form method="POST" class="d-flex gap-1">
                    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                    <input type="hidden" name="filter" value="<?= h($filter) ?>">
                    <select name="status" class="form-select form-select-sm">
                        <?php foreach ($allowedStatuses as $s): ?>
                            <option <?= $b['status'] === $s ? 'selected' : '' ?>><?= h($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button name="update_status" value="1" class="btn btn-primary btn-sm">Save status</button>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php if (!$bookings): ?>
    <div class="card-box"><p class="text-muted mb-0">No bookings to show.</p></div>
<?php endif; ?>

<?php include '../includes/admin_footer.php'; ?>