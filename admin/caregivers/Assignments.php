<?php
require_once '../includes/auth.php';
require_once '../database/connection.php';
$me  = require_role('caregiver');
$cid = (int)$me['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete'])) {
    try {
        if (!csrf_valid()) {
            flash('danger', 'Your session expired. Please try again.');
        } else {
            // Only a Confirmed booking that belongs to THIS caregiver can be completed by them
            $id   = (int)$_POST['complete'];
            $stmt = $conn->prepare(
                "UPDATE bookings SET status = 'Completed' WHERE id = ? AND caregiver_id = ? AND status = 'Confirmed'"
            );
            $stmt->bind_param('ii', $id, $cid);
            $stmt->execute();
            $changed = $stmt->affected_rows > 0;
            $stmt->close();
            flash($changed ? 'success' : 'danger',
                  $changed ? 'Marked as completed.' : 'That booking could not be updated.');
        }
    } catch (mysqli_sql_exception $ex) {
        error_log('Mark completed failed: ' . $ex->getMessage());
        flash('danger', 'Something went wrong. Please try again.');
    }
    redirect('assignments.php');
}

$rows = [];
try {
    $stmt = $conn->prepare(
        "SELECT * FROM bookings
         WHERE caregiver_id = ? AND status <> 'Cancelled'
         ORDER BY (status = 'Completed'), appointment_date ASC, id ASC"
    );
    $stmt->bind_param('i', $cid);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $ex) {
    error_log('Caregiver assignments failed: ' . $ex->getMessage());
}

$statusClass = [
    'Pending'   => 'bg-warning text-dark',
    'Confirmed' => 'bg-success',
    'Completed' => 'bg-info text-dark',
];

$pageTitle = 'My Assignments';
$active    = 'assignments';
include '../includes/dashboard_header.php';
?>

<?php foreach ($rows as $r): ?>
    <div class="card-box mb-3">
        <div class="d-flex justify-content-between flex-wrap gap-2">
            <div>
                <strong class="fs-5"><?= h($r['patient_name']) ?></strong>
                <div class="small text-muted">Booking #<?= (int)$r['id'] ?><?= !empty($r['phone']) ? ' &middot; ' . h($r['phone']) : '' ?></div>
            </div>
            <span class="badge align-self-start <?= h($statusClass[$r['status']] ?? 'bg-secondary') ?>"><?= h($r['status']) ?></span>
        </div>

        <div class="row mt-2 small">
            <div class="col-md-4">
                <strong>Care:</strong> <?= h($r['service']) ?><?= !empty($r['package']) ? ' (' . h($r['package']) . ')' : '' ?><br>
                <strong>When:</strong> <?= h($r['appointment_date']) ?><?= !empty($r['preferred_time']) ? ', ' . h($r['preferred_time']) : '' ?>
                <?= !empty($r['duration']) ? '<br><strong>For:</strong> ' . h($r['duration']) : '' ?>
            </div>
            <div class="col-md-4">
                <?php if (!empty($r['service_address'])): ?>
                    <strong>Where:</strong> <?= h($r['service_address']) ?><?= !empty($r['city']) ? ', ' . h($r['city']) : '' ?>
                <?php endif; ?>
            </div>
            <div class="col-md-4"><strong>Care needed:</strong> <?= h($r['message']) ?></div>
        </div>

        <?php if ($r['status'] === 'Confirmed'): ?>
            <form method="POST" class="mt-2" onsubmit="return confirm('Mark this visit as completed?');">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <button name="complete" value="<?= (int)$r['id'] ?>" class="btn btn-outline-success btn-sm">Mark completed</button>
            </form>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php if (!$rows): ?>
    <div class="card-box"><p class="text-muted mb-0">Nothing is assigned to you yet. New assignments appear here once an admin confirms them.</p></div>
<?php endif; ?>

<?php include '../includes/dashboard_footer.php'; ?>