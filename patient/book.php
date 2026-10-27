<?php
require_once '../includes/auth.php';
require_once '../database/connection.php';
require_once '../includes/accounts.php';
require_once '../includes/caregivers.php';
$patient = require_role('patient');
date_default_timezone_set('Africa/Nairobi');

const PACKAGES  = ['Essential Care', 'Plus Care', 'Integrated Care'];
const SERVICES  = ['Elderly Support', 'Postnatal Care', 'Post Surgery Recovery', 'Chronic Illness Management',
                   'Palliative Care', 'Home Safety Evaluation', 'Nursing Procedures at Home',
                   'Preventive Care', 'Care Coordination'];
const TIMES     = ['Morning', 'Afternoon', 'Evening'];
const DURATIONS = ['1 Day', '1 Week', '1 Month'];

$pid        = (int)$patient['id'];
$caregivers = available_caregivers($conn);
$error      = '';
$old = array_fill_keys(['package', 'service', 'date', 'time', 'duration', 'phone',
                        'service_address', 'city', 'notes', 'caregiver_id'], '');
$old['caregiver_id'] = '0';

// Pre-fill the phone number the patient saved before, if any
try {
    $q = $conn->prepare("SELECT phone FROM patients WHERE id = ?");
    $q->bind_param('i', $pid);
    $q->execute();
    $old['phone'] = (string)($q->get_result()->fetch_row()[0] ?? '');
    $q->close();
} catch (mysqli_sql_exception $ex) {
    error_log('Phone prefill failed: ' . $ex->getMessage());
}

// "Choose this package" links arrive with the package chosen
if (isset($_GET['package']) && in_array($_GET['package'], PACKAGES, true)) {
    $old['package'] = $_GET['package'];
}
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
    } elseif (!in_array($old['package'], PACKAGES, true)) {
        $error = 'Please choose a care package.';
    } elseif (!in_array($old['service'], SERVICES, true)) {
        $error = 'Please choose the service you need.';
    } elseif (!$d || $d->format('Y-m-d') !== $old['date']) {
        $error = 'Please choose a valid start date.';
    } elseif ($old['date'] < date('Y-m-d')) {
        $error = 'The start date cannot be in the past.';
    } elseif (!in_array($old['time'], TIMES, true)) {
        $error = 'Please choose a time slot.';
    } elseif (!in_array($old['duration'], DURATIONS, true)) {
        $error = 'Please choose how long you need care.';
    } elseif (!preg_match('/^\+?[0-9\s\-()]{7,20}$/', $old['phone'])) {
        $error = 'Please enter a valid phone number.';
    } elseif ($old['service_address'] === '' || mb_strlen($old['service_address']) > 255) {
        $error = 'Please enter where the care will take place (max 255 characters).';
    } elseif ($old['city'] === '' || mb_strlen($old['city']) > 100) {
        $error = 'Please enter the city or town.';
    } elseif ($old['notes'] === '' || mb_strlen($old['notes']) > 2000) {
        $error = 'Please describe the care needed (max 2000 characters).';
    } elseif ($cgId > 0 && !caregiver_is_available($caregivers, $cgId)) {
        $error = 'The caregiver you chose is no longer available. Please choose another.';
    } elseif (empty($_POST['confirm'])) {
        $error = 'Please confirm that the information is accurate.';
    } else {
        $name = (string)$patient['name'];
        $req  = $cgId > 0 ? $cgId : null;     // a preference only; the admin assigns

        try {
            $stmt = $conn->prepare(
                "INSERT INTO bookings
                 (patient_name, service, package, appointment_date, preferred_time, duration, phone,
                  service_address, city, message, patient_id, requested_caregiver_id, status, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())"
            );
            $stmt->bind_param('ssssssssssii',
                $name, $old['service'], $old['package'], $old['date'], $old['time'], $old['duration'],
                $old['phone'], $old['service_address'], $old['city'], $old['notes'], $pid, $req);
            $stmt->execute();
            $stmt->close();

            flash('success', 'Your care request has been sent. Our team will confirm it and assign a caregiver.');
            redirect('my_bookings.php');
        } catch (mysqli_sql_exception $ex) {
            error_log('Patient booking failed: ' . $ex->getMessage());
            $error = 'We could not save your request. Please try again.';
        }
    }
}

$pageTitle = 'Request Care';
$active    = 'book';
include '../includes/dashboard_header.php';

function opts(array $items, string $current): string {
    $html = '';
    foreach ($items as $i) {
        $html .= '<option value="' . h($i) . '"' . ($i === $current ? ' selected' : '') . '>' . h($i) . '</option>';
    }
    return $html;
}
?>

<div class="card-box" style="max-width: 900px;">
    <h5>Request care at home</h5>
    <p class="text-muted small">Tell us what you need. Our team reviews every request, assigns a caregiver and confirms with you.</p>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label small mb-0">Care package *</label>
                <select name="package" class="form-select" required>
                    <option value="">Select a package</option>
                    <?= opts(PACKAGES, $old['package']) ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small mb-0">Service needed *</label>
                <select name="service" class="form-select" required>
                    <option value="">Select a service</option>
                    <?= opts(SERVICES, $old['service']) ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label small mb-0">Preferred start date *</label>
                <input type="date" name="date" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= h($old['date']) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-0">Time slot *</label>
                <select name="time" class="form-select" required>
                    <option value="">Select a time</option>
                    <?= opts(TIMES, $old['time']) ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-0">Estimated duration *</label>
                <select name="duration" class="form-select" required>
                    <option value="">Select duration</option>
                    <?= opts(DURATIONS, $old['duration']) ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label small mb-0">Phone *</label>
                <input type="tel" name="phone" class="form-control" maxlength="30" placeholder="+254 712 345678"
                       value="<?= h($old['phone']) ?>" required>
            </div>
            <div class="col-md-5">
                <label class="form-label small mb-0">Where will the care take place? *</label>
                <input type="text" name="service_address" class="form-control" maxlength="255"
                       placeholder="Estate, street, house number" value="<?= h($old['service_address']) ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0">City / town *</label>
                <input type="text" name="city" class="form-control" maxlength="100" value="<?= h($old['city']) ?>" required>
            </div>
        </div>

        <?php
        $selectedCaregiver = (int)$old['caregiver_id'];
        include '../includes/caregiver_picker.php';
        ?>

        <label class="form-label small mb-0">Care needed and any special requirements *</label>
        <textarea name="notes" class="form-control mb-3" rows="4" maxlength="2000" required><?= h($old['notes']) ?></textarea>

        <div class="form-check mb-3">
            <input type="checkbox" id="confirmInfo" name="confirm" value="1" class="form-check-input" required>
            <label for="confirmInfo" class="form-check-label small">I confirm that the information provided is accurate.</label>
        </div>

        <button type="submit" class="btn btn-success w-100">Send care request</button>
    </form>
</div>

<?php include '../includes/dashboard_footer.php'; ?>