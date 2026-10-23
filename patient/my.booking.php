<?php
require_once '../includes/auth.php';
require_once '../database/connection.php';
$patient = require_role('patient');

$pid = (int)$patient['id'];
$bookings = [];
try {
    $stmt = $conn->prepare(
        "SELECT b.service, b.appointment_date, b.status, b.message,
                c.name AS caregiver_name, c.specialization
         FROM bookings b
         LEFT JOIN caregivers c ON c.id = b.caregiver_id
         WHERE b.patient_id = ?
         ORDER BY b.id DESC"
    );
    $stmt->bind_param('i', $pid);
    $stmt->execute();
    $bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $ex) {
    error_log('My bookings failed: ' . $ex->getMessage());
}

$statusClass = [
    'Pending'   => 'bg-warning text-dark',
    'Confirmed' => 'bg-success',
    'Cancelled' => 'bg-secondary',
];

$pageTitle = 'My Bookings';
$active    = 'bookings';
include '../includes/dashboard_header.php';
?>

<div class="card-box">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">All my bookings and requests</h5>
        <?php if (is_file(__DIR__ . '/book.php')): ?>
            <a href="book.php" class="btn btn-success btn-sm">New booking</a>
        <?php endif; ?>
    </div>

    <?php if ($bookings): ?>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>Service</th><th>Date</th><th>Caregiver</th><th>Details</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?= h($b['service']) ?></td>
                    <td><?= h($b['appointment_date']) ?></td>
                    <td>
                        <?php if ($b['caregiver_name']): ?>
                            <?= h($b['caregiver_name']) ?><br>
                            <small class="text-muted"><?= h($b['specialization']) ?></small>
                        <?php else: ?>
                            <span class="text-muted">To be assigned</span>
                        <?php endif; ?>
                    </td>
                    <td><small><?= h(mb_strimwidth((string)$b['message'], 0, 120, '...')) ?></small></td>
                    <td>
                        <span class="badge <?= h($statusClass[$b['status']] ?? 'bg-secondary') ?>">
                            <?= h($b['status']) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="text-muted mb-0">You have no bookings yet.</p>
    <?php endif; ?>
</div>

<?php include '../includes/dashboard_footer.php'; ?>