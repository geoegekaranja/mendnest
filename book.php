<?php
require_once '../includes/auth.php';
require_once '../database/connection.php';
require_once '../includes/caregivers.php';
$patient = require_role('patient');
date_default_timezone_set('Africa/Nairobi');

const SERVICES = ['Home Nursing', 'Elderly Care', 'Medication Support'];

$caregivers = available_caregivers($conn);
$error = '';
$old   = ['service' => '', 'date' => '', 'caregiver_id' => '0', 'message' => ''];

// Arriving from a caregiver link: pre-select that caregiver
if (isset($_GET['caregiver'])) {
    $old['caregiver_id'] = (string)(int)$_GET['caregiver'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $key => $_) {
        $old[$key] = trim((string)($_POST[$key] ?? ''));
    }
    $cgId = (int)$old['caregiver_id'];
    $d    = DateTime::createFromFormat('Y-m-d', $old['date']);

    if (!csrf_valid()) {
        $error = 'Your session expired. Please try again.';
    } elseif (!in_array($old['service'], SERVICES, true)) {
        $error = 'Please choose a service.';
    } elseif (!$d || $d->format('Y-m-d') !== $old['date']) {
        $error = 'Please choose a valid date.';
    } elseif ($old['date'] < date('Y-m-d')) {
        $error = 'The date cannot be in the past.';
    } elseif (mb_strlen($old['message']) > 2000) {
        $error = 'Notes must be 2000 characters or fewer.';
    } elseif ($cgId > 0 && !caregiver_is_available($caregivers, $cgId)) {
        $error = 'The selected caregiver is no longer available. Please choose another.';
    } else {
        $pid  = (int)$patient['id'];
        $name = (string)$patient['name'];
        $cg   = $cgId > 0 ? $cgId : null;     // null = no preference

        try {
            $stmt = $conn->prepare(
                "INSERT INTO bookings
                 (patient_name, service, appointment_date, message, patient_id, caregiver_id, status, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())"
            );
            $stmt->bind_param('ssssii', $name, $old['service'], $old['date'], $old['message'], $pid, $cg);
            $stmt->execute();
            $stmt->close();

            flash('success', 'Your booking request has been sent. We will confirm it shortly.');
            redirect('my_bookings.php');
        } catch (mysqli_sql_exception $ex) {
            error_log('Patient booking failed: ' . $ex->getMessage());
            $error = 'Could not save your booking. Please try again.';
        }
    }
}

$pageTitle = 'Book an Appointment';
$active    = 'book';
include '../includes/dashboard_header.php';
?>

<div class="card-box" style="max-width: 800px;">
    <h5>Book an appointment</h5>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

        <label class="form-label fw-semibold">Service *</label>
        <select name="service" class="form-select mb-3" required>
            <option value="">Select a service</option>
            <?php foreach (SERVICES as $s): ?>
                <option value="<?= h($s) ?>" <?= $old['service'] === $s ? 'selected' : '' ?>><?= h($s) ?></option>
            <?php endforeach; ?>
        </select>

        <label class="form-label fw-semibold">Preferred date *</label>
        <input type="date" name="date" class="form-control mb-2"
               min="<?= date('Y-m-d') ?>" value="<?= h($old['date']) ?>" required>

        <?php
        $selectedCaregiver = (int)$old['caregiver_id'];
        include '../includes/caregiver_picker.php';
        ?>

        <label class="form-label fw-semibold">Additional notes</label>
        <textarea name="message" class="form-control mb-3" rows="3" maxlength="2000"
                  placeholder="Anything the caregiver should know"><?= h($old['message']) ?></textarea>

        <button type="submit" class="btn btn-success w-100">Send booking request</button>
    </form>
</div>

<?php include '../includes/dashboard_footer.php'; ?>