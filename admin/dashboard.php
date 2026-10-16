<?php
require_once '../includes/auth.php';
require_once '../database/connection.php';
$admin = require_role('admin');

// COUNT(*) is far faster than loading every row just to count them
function safe_count(mysqli $conn, string $sql): int {
    try {
        return (int)$conn->query($sql)->fetch_row()[0];
    } catch (mysqli_sql_exception $ex) {
        error_log('Dashboard count failed: ' . $ex->getMessage());
        return 0;
    }
}

$cards = [
    ['Total Bookings',        safe_count($conn, "SELECT COUNT(*) FROM bookings"),                           'bookings.php'],
    ['Pending Bookings',      safe_count($conn, "SELECT COUNT(*) FROM bookings WHERE status = 'Pending'"),  'bookings.php'],
    ['Consultation Requests', safe_count($conn, "SELECT COUNT(*) FROM consultations"),                      'consultations.php'],
    ['Total Patients',        safe_count($conn, "SELECT COUNT(*) FROM patients WHERE role = 'patient'"),    null],
    ['Testimonials',          safe_count($conn, "SELECT COUNT(*) FROM testimonials"),                       'testimonials.php'],
    ['Awaiting Approval',     safe_count($conn, "SELECT COUNT(*) FROM testimonials WHERE is_approved = 0"), 'testimonials.php'],
];

$pageTitle = 'Admin Dashboard';
$active    = 'dashboard';
include '../includes/admin_header.php';
?>

<div class="row">
<?php foreach ($cards as [$label, $count, $link]): ?>
    <div class="col-md-4 mb-4">
        <div class="card-box text-center">
            <h5><?= h($label) ?></h5>
            <h2><?= (int)$count ?></h2>
            <?php if ($link): ?><a href="<?= h($link) ?>" class="small">View</a><?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>

<?php include '../includes/admin_footer.php'; ?>