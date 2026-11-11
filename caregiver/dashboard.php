<?php
require_once '../includes/auth.php';
require_once '../database/connection.php';
require_once '../includes/accounts.php';
$me  = require_role('caregiver');
$cid = (int)$me['id'];

function cg_count(mysqli $conn, string $sql, int $cid): int {
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $cid);
        $stmt->execute();
        $n = (int)$stmt->get_result()->fetch_row()[0];
        $stmt->close();
        return $n;
    } catch (mysqli_sql_exception $ex) {
        error_log('Caregiver dashboard count failed: ' . $ex->getMessage());
        return 0;
    }
}

$assigned  = cg_count($conn, "SELECT COUNT(*) FROM bookings WHERE caregiver_id = ? AND status IN ('Pending','Confirmed')", $cid);
$upcoming  = cg_count($conn, "SELECT COUNT(*) FROM bookings WHERE caregiver_id = ? AND status = 'Confirmed' AND appointment_date >= CURDATE()", $cid);
$completed = cg_count($conn, "SELECT COUNT(*) FROM bookings WHERE caregiver_id = ? AND status = 'Completed'", $cid);

$profile = ['caregiver_code' => '', 'specialization' => '', 'photo' => null, 'is_available' => 1];
$visits  = [];
try {
    $stmt = $conn->prepare("SELECT caregiver_code, specialization, photo, is_available FROM caregivers WHERE id = ?");
    $stmt->bind_param('i', $cid);
    $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc() ?: $profile;
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT * FROM bookings
         WHERE caregiver_id = ? AND status IN ('Pending','Confirmed') AND appointment_date >= CURDATE()
         ORDER BY appointment_date ASC, id ASC
         LIMIT 8"
    );
    $stmt->bind_param('i', $cid);
    $stmt->execute();
    $visits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $ex) {
    error_log('Caregiver dashboard load failed: ' . $ex->getMessage());
}

$statusClass = [
    'Pending'   => 'bg-warning text-dark',
    'Confirmed' => 'bg-success',
    'Completed' => 'bg-info text-dark',
    'Cancelled' => 'bg-secondary',
];
$photoUrl = caregiver_photo_url($profile['photo'] ?? null);

$pageTitle = 'My Dashboard';
$active    = 'dashboard';
include '../includes/dashboard_header.php';
?>

<div class="card-box mb-4 d-flex align-items-center gap-3">
    <?php if ($photoUrl): ?>
        <img src="<?= h($photoUrl) ?>" alt="" width="64" height="64" class="rounded-circle" style="object-fit:cover;">
    <?php else: ?>
        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fs-4"
             style="width:64px;height:64px;"><?= h(mb_strtoupper(mb_substr((string)$me['name'], 0, 1))) ?></div>
    <?php endif; ?>
    <div>
        <h4 class="mb-0">Welcome, <?= h($me['name']) ?></h4>
        <div class="text-muted">
            Caregiver ID <strong><?= h($profile['caregiver_code']) ?></strong>
            <?php if (!empty($profile['specialization'])): ?> &middot; <?= h($profile['specialization']) ?><?php endif; ?>
        </div>
        <?php if (!$profile['is_available']): ?>
            <span class="badge bg-secondary">Currently marked unavailable for new assignments</span>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <?php foreach ([
        ['Active assignments', $assigned,  'bi-clipboard2-pulse', 'primary'],
        ['Upcoming visits',    $upcoming,  'bi-calendar-event',   'success'],
        ['Completed',          $completed, 'bi-check2-circle',    'info'],
    ] as [$label, $count, $icon, $colour]): ?>
    <div class="col-md-4 mb-4">
        <div class="card-box h-100 d-flex align-items-center gap-3">
            <div class="rounded-3 bg-<?= h($colour) ?> bg-opacity-10 text-<?= h($colour) ?> d-flex align-items-center justify-content-center"
                 style="width:56px;height:56px;font-size:1.6rem;"><i class="bi <?= h($icon) ?>"></i></div>
            <div>
                <h5 class="mb-0"><?= h($label) ?></h5>
                <h2 class="mb-0"><?= (int)$count ?></h2>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card-box">
    <h5 class="mb-3">Upcoming care assigned to you</h5>

    <?php if ($visits): ?>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>Patient</th><th>Service</th><th>Date</th><th>Where / contact</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php foreach ($visits as $v): ?>
                <tr>
                    <td><?= h($v['patient_name']) ?></td>
                    <td><?= h($v['service']) ?></td>
                    <td>
                        <?= h($v['appointment_date']) ?>
                        <?php if (!empty($v['preferred_time'])): ?><br><small class="text-muted"><?= h($v['preferred_time']) ?></small><?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($v['service_address'])): ?>
                            <?= h($v['service_address']) ?><?= !empty($v['city']) ? ', ' . h($v['city']) : '' ?><br>
                        <?php endif; ?>
                        <?php if (!empty($v['phone'])): ?><small class="text-muted"><?= h($v['phone']) ?></small><?php endif; ?>
                    </td>
                    <td><span class="badge <?= h($statusClass[$v['status']] ?? 'bg-secondary') ?>"><?= h($v['status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="text-muted mb-0">Nothing is assigned to you yet. New assignments will appear here once an admin confirms them.</p>
    <?php endif; ?>
</div>

<?php include '../includes/dashboard_footer.php'; ?>
