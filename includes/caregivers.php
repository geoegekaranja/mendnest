<?php
require_once '../includes/auth.php';
require_once '../database/connection.php';
require_once '../includes/accounts.php';
$patient = require_role('patient');

$pid = (int)$patient['id'];
$id  = (int)($_GET['id'] ?? 0);
$cg  = null;

try {
    // A patient may only open the profile of a caregiver assigned to one of their own bookings
    $chk = $conn->prepare(
        "SELECT 1 FROM bookings WHERE patient_id = ? AND caregiver_id = ? AND status <> 'Cancelled' LIMIT 1"
    );
    $chk->bind_param('ii', $pid, $id);
    $chk->execute();
    $allowed = $chk->get_result()->num_rows > 0;
    $chk->close();

    if ($allowed) {
        // The phone number and email are private, so they are never selected here
        $stmt = $conn->prepare(
            "SELECT name, caregiver_code, specialization, qualification, license_number, years_experience,
                    gender, languages, areas_served, bio, photo
             FROM caregivers WHERE id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $cg = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
} catch (mysqli_sql_exception $ex) {
    error_log('Caregiver profile (patient view) failed: ' . $ex->getMessage());
}

if (!$cg) {
    flash('danger', 'That caregiver profile is not available to you.');
    redirect('my_bookings.php');
}

$pic = caregiver_photo_url($cg['photo'] ?? null);

$pageTitle = 'Your caregiver';
$active    = 'bookings';
include '../includes/dashboard_header.php';
?>

<div class="card-box" style="max-width: 760px;">
    <div class="d-flex align-items-center gap-3 mb-3">
        <?php if ($pic): ?>
            <img src="<?= h($pic) ?>" alt="" width="96" height="96" class="rounded-circle" style="object-fit:cover;">
        <?php else: ?>
            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fs-2"
                 style="width:96px;height:96px;"><?= h(mb_strtoupper(mb_substr((string)$cg['name'], 0, 1))) ?></div>
        <?php endif; ?>
        <div>
            <h3 class="mb-0"><?= h($cg['name']) ?></h3>
            <span class="badge bg-success"><?= h($cg['specialization']) ?></span>
            <div class="small text-muted mt-1">Caregiver ID <?= h($cg['caregiver_code']) ?></div>
        </div>
    </div>

    <?php if (!empty($cg['bio'])): ?>
        <p><?= nl2br(h($cg['bio'])) ?></p>
    <?php endif; ?>

    <table class="table table-sm">
        <?php if (!empty($cg['qualification'])): ?>
            <tr><th style="width:190px">Qualification</th><td><?= h($cg['qualification']) ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($cg['license_number'])): ?>
            <tr><th>Licence / registration no.</th><td><?= h($cg['license_number']) ?></td></tr>
        <?php endif; ?>
        <?php if ($cg['years_experience'] !== null): ?>
            <tr><th>Experience</th><td><?= (int)$cg['years_experience'] ?> years</td></tr>
        <?php endif; ?>
        <?php if (!empty($cg['languages'])): ?>
            <tr><th>Languages</th><td><?= h($cg['languages']) ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($cg['areas_served'])): ?>
            <tr><th>Areas served</th><td><?= h($cg['areas_served']) ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($cg['gender'])): ?>
            <tr><th>Gender</th><td><?= h($cg['gender']) ?></td></tr>
        <?php endif; ?>
    </table>

    <a href="my_bookings.php" class="btn btn-outline-secondary btn-sm">Back to my bookings</a>
</div>

<?php include '../includes/dashboard_footer.php'; ?>