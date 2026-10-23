<?php
require_once '../includes/auth.php';
require_once '../database/connection.php';
$admin = require_role('admin');   // patients and visitors are sent away

// COUNT(*) is far faster than loading every row just to count them
function safe_count(mysqli $conn, string $sql): int {
    try {
        return (int)$conn->query($sql)->fetch_row()[0];
    } catch (mysqli_sql_exception $ex) {
        error_log('Dashboard count failed: ' . $ex->getMessage());
        return 0;
    }
}

// Only link to admin pages that exist, so there are never dead links
function admin_link(string $file): ?string {
    return is_file(__DIR__ . '/' . $file) ? $file : null;
}

// [label, count, link, icon, colour]
$cards = [
    ['Total Bookings',        safe_count($conn, "SELECT COUNT(*) FROM bookings"),                           admin_link('bookings.php'),      'bi-calendar-check', 'primary'],
    ['Pending Bookings',      safe_count($conn, "SELECT COUNT(*) FROM bookings WHERE status = 'Pending'"),  admin_link('bookings.php'),      'bi-hourglass-split', 'warning'],
    ['Consultation Requests', safe_count($conn, "SELECT COUNT(*) FROM consultations"),                      admin_link('consultations.php'), 'bi-chat-dots',      'info'],
    ['Total Patients',        safe_count($conn, "SELECT COUNT(*) FROM patients WHERE role = 'patient'"),    null,                            'bi-people',         'success'],
    ['Testimonials',          safe_count($conn, "SELECT COUNT(*) FROM testimonials"),                       admin_link('testimonials.php'),  'bi-star',           'secondary'],
    ['Awaiting Approval',     safe_count($conn, "SELECT COUNT(*) FROM testimonials WHERE is_approved = 0"), admin_link('testimonials.php'),  'bi-patch-question', 'danger'],
];

// Latest 5 bookings
$recent = [];
try {
    $recent = $conn->query(
        "SELECT patient_name, service, appointment_date, status
         FROM bookings ORDER BY id DESC LIMIT 5"
    )->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $ex) {
    error_log('Dashboard recent bookings failed: ' . $ex->getMessage());
}

$statusClass = [
    'Pending'   => 'bg-warning text-dark',
    'Confirmed' => 'bg-success',
    'Cancelled' => 'bg-secondary',
];

$pageTitle = 'Admin Dashboard';
$active    = 'dashboard';
include '../includes/admin_header.php';
?>

<p class="text-muted mb-4">Welcome back, <strong><?= h($admin['name']) ?></strong>. Here is what is happening today.</p>

<div class="row">
<?php foreach ($cards as [$label, $count, $link, $icon, $colour]): ?>
    <div class="col-md-6 col-xl-4 mb-4">
        <div class="card-box h-100 d-flex align-items-center gap-3">
            <div class="rounded-3 bg-<?= h($colour) ?> bg-opacity-10 text-<?= h($colour) ?> d-flex align-items-center justify-content-center"
                 style="width:56px;height:56px;font-size:1.6rem;">
                <i class="bi <?= h($icon) ?>"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="mb-0"><?= h($label) ?></h5>
                <h2 class="mb-0"><?= (int)$count ?></h2>
                <?php if ($link): ?><a href="<?= h($link) ?>" class="small">View all</a><?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<div class="card-box">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Recent Bookings</h5>
        <?php if (admin_link('bookings.php')): ?>
            <a href="bookings.php" class="small">Manage bookings</a>
        <?php endif; ?>
    </div>

    <?php if ($recent): ?>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>Patient</th><th>Service</th><th>Date</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php foreach ($recent as $b): ?>
                <tr>
                    <td><?= h($b['patient_name']) ?></td>
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
        <p class="text-muted mb-0">No bookings yet.</p>
    <?php endif; ?>
</div>

<?php include '../includes/admin_footer.php'; ?>