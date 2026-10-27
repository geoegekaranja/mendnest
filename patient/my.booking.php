<?php
require_once '../includes/auth.php';
require_once '../database/connection.php';
require_once '../includes/accounts.php';
$patient = require_role('patient');

$pid = (int)$patient['id'];
$bookings = [];
try {
    $stmt = $conn->prepare(
        "SELECT b.id, b.service, b.package, b.appointment_date, b.preferred_time, b.duration,
                b.service_address, b.city, b.status, b.message, b.created_at, b.caregiver_id,
                c.name  AS caregiver_name, c.specialization AS caregiver_spec, c.photo AS caregiver_photo,
                rc.name AS requested_name
         FROM bookings b
         LEFT JOIN caregivers c  ON c.id  = b.caregiver_id
         LEFT JOIN caregivers rc ON rc.id = b.requested_caregiver_id
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
    'Completed' => 'bg-info text-dark',
    'Cancelled' => 'bg-secondary',
];

$pageTitle = 'My Bookings';
$active    = 'bookings';
include '../includes/dashboard_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Your care requests and their status.</p>
    <?php if (is_file(__DIR__ . '/book.php')): ?>
        <a href="book.php" class="btn btn-success btn-sm">Request care</a>
    <?php endif; ?>
</div>

<?php foreach ($bookings as $b): ?>
    <?php
    $assigned = $b['caregiver_id'] && $b['caregiver_name'] && $b['status'] !== 'Cancelled';
    $pic      = $assigned ? caregiver_photo_url($b['caregiver_photo'] ?? null) : null;
    ?>
    <div class="card-box mb-3">
        <div class="d-flex justify-content-between flex-wrap gap-2">
            <div>
                <strong><?= h($b['service']) ?></strong>
                <?php if (!empty($b['package'])): ?><span class="text-muted"> &middot; <?= h($b['package']) ?></span><?php endif; ?>
                <div class="small text-muted">Request #<?= (int)$b['id'] ?> &middot; sent <?= h(substr((string)$b['created_at'], 0, 10)) ?></div>
            </div>
            <span class="badge align-self-start <?= h($statusClass[$b['status']] ?? 'bg-secondary') ?>"><?= h($b['status']) ?></span>
        </div>

        <div class="row mt-2 small">
            <div class="col-md-4">
                <strong>When:</strong> <?= h($b['appointment_date']) ?>
                <?= !empty($b['preferred_time']) ? ', ' . h($b['preferred_time']) : '' ?>
                <?= !empty($b['duration']) ? '<br><strong>For:</strong> ' . h($b['duration']) : '' ?>
            </div>
            <div class="col-md-4">
                <?php if (!empty($b['service_address'])): ?>
                    <strong>Where:</strong> <?= h($b['service_address']) ?><?= !empty($b['city']) ? ', ' . h($b['city']) : '' ?>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-muted"><?= h(mb_strimwidth((string)$b['message'], 0, 140, '...')) ?></div>
        </div>

        <hr class="my-2">

        <?php if ($assigned): ?>
            <div class="d-flex align-items-center gap-2">
                <?php if ($pic): ?>
                    <img src="<?= h($pic) ?>" alt="" width="44" height="44" class="rounded-circle" style="object-fit:cover;">
                <?php else: ?>
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                         style="width:44px;height:44px;"><?= h(mb_strtoupper(mb_substr((string)$b['caregiver_name'], 0, 1))) ?></div>
                <?php endif; ?>
                <div class="flex-grow-1">
                    <strong><?= h($b['caregiver_name']) ?></strong><br>
                    <small class="text-muted"><?= h($b['caregiver_spec']) ?></small>
                </div>
                <?php if (is_file(__DIR__ . '/caregiver.php')): ?>
                    <a href="caregiver.php?id=<?= (int)$b['caregiver_id'] ?>" class="btn btn-outline-success btn-sm">View profile</a>
                <?php endif; ?>
            </div>
        <?php elseif ($b['status'] === 'Cancelled'): ?>
            <span class="text-muted small">This request was cancelled.</span>
        <?php elseif (!empty($b['requested_name'])): ?>
            <span class="small">You asked for <strong><?= h($b['requested_name']) ?></strong>. We will confirm the caregiver shortly.</span>
        <?php else: ?>
            <span class="text-muted small">A caregiver will be assigned by our team shortly.</span>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php if (!$bookings): ?>
    <div class="card-box"><p class="text-muted mb-0">You have no care requests yet.</p></div>
<?php endif; ?>

<?php include '../includes/dashboard_footer.php'; ?>