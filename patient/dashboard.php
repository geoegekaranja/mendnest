<?php
require_once '../includes/auth.php';
require_once '../database/connection.php';
$patient = require_role('patient');   // sends visitors and admins away, same as the admin pages

$pid  = (int)($patient['id'] ?? 0);
$name = $patient['name'] ?? 'Patient';

function patient_count(mysqli $conn, string $sql, int $pid): int {
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        return (int)$stmt->get_result()->fetch_row()[0];
    } catch (mysqli_sql_exception $ex) {
        error_log('Patient dashboard count failed: ' . $ex->getMessage());
        return 0;
    }
}

// Only link to patient pages that exist, so there are never dead links
function patient_link(string $file): ?string {
    return is_file(__DIR__ . '/' . $file) ? $file : null;
}

$totalBookings    = patient_count($conn, "SELECT COUNT(*) FROM bookings WHERE patient_id = ?", $pid);
$pendingBookings  = patient_count($conn, "SELECT COUNT(*) FROM bookings WHERE patient_id = ? AND status = 'Pending'", $pid);
$upcomingBookings = patient_count($conn, "SELECT COUNT(*) FROM bookings WHERE patient_id = ? AND appointment_date >= CURDATE() AND status <> 'Cancelled'", $pid);

$recent = [];
try {
    $stmt = $conn->prepare(
        "SELECT service, appointment_date, status
         FROM bookings
         WHERE patient_id = ?
         ORDER BY appointment_date DESC
         LIMIT 5"
    );
    $stmt->bind_param('i', $pid);
    $stmt->execute();
    $recent = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $ex) {
    error_log('Patient dashboard recent bookings failed: ' . $ex->getMessage());
}

$statusClass = [
    'Pending'   => 'bg-warning text-dark',
    'Confirmed' => 'bg-success',
    'Cancelled' => 'bg-secondary',
];

$actions = [
    ['Book an Appointment', patient_link('book.php'),        'btn-success'],
    ['My Bookings',         patient_link('my_bookings.php'), 'btn-outline-primary'],
    ['Edit Profile',        patient_link('profile.php'),     'btn-outline-secondary'],
];

include '../includes/header.php';
?>

<div class="container my-5">

    <div class="mb-4">
        <h2 class="fw-bold">Welcome, <?= h($name) ?></h2>
        <p class="text-muted mb-0">Manage your home nursing appointments from here.</p>
    </div>

    <div class="row">
        <?php foreach ([
            ['Total Bookings', $totalBookings],
            ['Upcoming',       $upcomingBookings],
            ['Pending',        $pendingBookings],
        ] as [$label, $count]): ?>
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm text-center h-100">
                <div class="card-body">
                    <h6 class="text-muted"><?= h($label) ?></h6>
                    <h2 class="fw-bold"><?= (int)$count ?></h2>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <?php foreach ($actions as [$label, $link, $class]): ?>
            <?php if ($link): ?>
                <a href="<?= h($link) ?>" class="btn <?= h($class) ?>"><?= h($label) ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Recent Bookings</h5>

            <?php if ($recent): ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr><th>Service</th><th>Date</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recent as $b): ?>
                        <tr>
                            <td><?= h($b['service']) ?></td>
                            <td><?= h($b['appointment_date']) ?></td>
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
    </div>

</div>

<?php include '../includes/footer.php'; ?>