<?php
date_default_timezone_set('Africa/Nairobi');
require_once '../includes/auth.php';          // starts the session; gives current_user(), h(), csrf helpers
require_once '../database/connection.php';

/* A consultation is a conversation with our team about someone's care needs.
   It is NOT a booking: it is saved only in the consultations table, and an admin follows it up. */

/* ---------- Allowed values (single source of truth) ---------- */
const COUNTRIES = ['Kenya', 'Uganda', 'Tanzania'];
const PACKAGES  = ['Essential Care', 'Plus Care', 'Integrated Care'];
const TIMES     = ['Morning', 'Afternoon', 'Evening'];
const DURATIONS = ['1 Day', '1 Week', '1 Month'];

/* ---------- Helpers ---------- */
function e(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
function options(array $items, string $current): string {
    $html = '';
    foreach ($items as $item) {
        $sel = ($item === $current) ? ' selected' : '';
        $html .= '<option value="' . e($item) . '"' . $sel . '>' . e($item) . '</option>';
    }
    return $html;
}

/* ---------- CSRF token ---------- */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/* ---------- Flash message (set after successful redirect) ---------- */
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

$user      = current_user();
$isPatient = $user && $user['role'] === 'patient';

$errors = [];
$old = array_fill_keys([
    'fname','lname','email','phone','address','city','country',
    'package','date','time','duration','service_address','notes'
], '');

// Signed-in patients get their details filled in
if ($isPatient && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $parts = preg_split('/\s+/', trim((string)$user['name']), 2);
    $old['fname'] = $parts[0] ?? '';
    $old['lname'] = $parts[1] ?? '';
    $old['email'] = (string)$user['email'];
}

// A package link can pre-select the package
if (isset($_GET['package']) && in_array($_GET['package'], PACKAGES, true)) {
    $old['package'] = $_GET['package'];
}

/* ---------- Handle submission ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) {
        $errors[] = 'Your session expired. Please reload the page and try again.';
    }

    foreach ($old as $key => $_) {
        $old[$key] = trim((string)($_POST[$key] ?? ''));
    }

    // Required text fields with max lengths (match DB column sizes)
    $limits = [
        'fname' => [100, 'First name'],       'lname' => [100, 'Last name'],
        'email' => [150, 'Email'],            'phone' => [30,  'Phone'],
        'address' => [255, 'Street address'], 'city' => [100, 'City'],
        'service_address' => [255, 'Service address'],
    ];
    foreach ($limits as $key => [$max, $label]) {
        if ($old[$key] === '') {
            $errors[] = "$label is required.";
        } elseif (mb_strlen($old[$key]) > $max) {
            $errors[] = "$label must be $max characters or fewer.";
        }
    }

    if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($old['phone'] !== '' && !preg_match('/^\+?[0-9\s\-()]{7,20}$/', $old['phone'])) {
        $errors[] = 'Please enter a valid phone number.';
    }

    // Whitelist dropdowns on the server (client-side can be bypassed)
    if (!in_array($old['country'],  COUNTRIES, true)) $errors[] = 'Please select a valid country.';
    if (!in_array($old['package'],  PACKAGES,  true)) $errors[] = 'Please select a valid care package.';
    if (!in_array($old['time'],     TIMES,     true)) $errors[] = 'Please select a valid time slot.';
    if (!in_array($old['duration'], DURATIONS, true)) $errors[] = 'Please select a valid duration.';

    // Real, non-past date
    $d = DateTime::createFromFormat('Y-m-d', $old['date']);
    if (!$d || $d->format('Y-m-d') !== $old['date']) {
        $errors[] = 'Please enter a valid start date.';
    } elseif ($old['date'] < date('Y-m-d')) {
        $errors[] = 'Start date cannot be in the past.';
    }

    if ($old['notes'] === '') {
        $errors[] = 'Additional notes are required.';
    } elseif (mb_strlen($old['notes']) > 2000) {
        $errors[] = 'Notes must be 2000 characters or fewer.';
    }

    if (empty($_POST['confirm'])) {
        $errors[] = 'Please confirm that the information is accurate.';
    }

    /* ---------- Insert: the consultation only ---------- */
    if (!$errors) {
        $sql = "INSERT INTO consultations
                (fname, lname, email, phone, address, city, country,
                 package, preferred_date, preferred_time, duration,
                 service_address, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'sssssssssssss',
                $old['fname'], $old['lname'], $old['email'], $old['phone'],
                $old['address'], $old['city'], $old['country'], $old['package'],
                $old['date'], $old['time'], $old['duration'],
                $old['service_address'], $old['notes']
            );
            $stmt->execute();
            $stmt->close();

            // Post/Redirect/Get: prevents duplicate submissions on refresh
            $_SESSION['flash_success'] = 'Your consultation request has been received. Our team will contact you shortly.';
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        } catch (mysqli_sql_exception $ex) {
            error_log('Consultation insert failed: ' . $ex->getMessage());
            $errors[] = 'Unable to submit your request. Please try again.';
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<section class="care-section">
<div class="container">

<h2 class="fw-bold mb-2">Care Consultation Form</h2>
<p class="text-muted mb-4">
    A consultation is a conversation with our team about your care needs. It does not book a caregiver.
    Ready to book care? <a href="<?= e(BASE_URL) ?>/request_care.php">Request care here</a>.
</p>

<?php if ($success !== ''): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" class="care-form">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

<div class="row">
    <div class="col-md-6">
        <label for="fname">First Name *</label>
        <input type="text" id="fname" name="fname" maxlength="100"
               value="<?= e($old['fname']) ?>" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label for="lname">Last Name *</label>
        <input type="text" id="lname" name="lname" maxlength="100"
               value="<?= e($old['lname']) ?>" class="form-control" required>
    </div>
</div>

<label for="email">Email Address *</label>
<input type="email" id="email" name="email" maxlength="150"
       value="<?= e($old['email']) ?>" class="form-control" required>

<label for="phone">Phone *</label>
<input type="tel" id="phone" name="phone" maxlength="30"
       placeholder="+254 712 345678" value="<?= e($old['phone']) ?>"
       class="form-control" required>

<label for="address">Street Address *</label>
<input type="text" id="address" name="address" maxlength="255"
       value="<?= e($old['address']) ?>" class="form-control" required>

<label for="city">City *</label>
<input type="text" id="city" name="city" maxlength="100"
       value="<?= e($old['city']) ?>" class="form-control" required>

<label for="country">Country *</label>
<select id="country" name="country" class="form-select" required>
    <option value="">Select country</option>
    <?= options(COUNTRIES, $old['country']) ?>
</select>

<label for="package">Care Package of Interest *</label>
<select id="package" name="package" class="form-select" required>
    <option value="">Select care package</option>
    <?= options(PACKAGES, $old['package']) ?>
</select>

<label for="date">Preferred Start Date *</label>
<input type="date" id="date" name="date" class="form-control"
       min="<?= date('Y-m-d') ?>" value="<?= e($old['date']) ?>" required>

<label for="time">Preferred Time Slot *</label>
<select id="time" name="time" class="form-select" required>
    <option value="">Select time slot</option>
    <?= options(TIMES, $old['time']) ?>
</select>

<label for="duration">Estimated Duration of Service *</label>
<select id="duration" name="duration" class="form-select" required>
    <option value="">Select duration</option>
    <?= options(DURATIONS, $old['duration']) ?>
</select>

<label for="service_address">Service Location / Address *</label>
<input type="text" id="service_address" name="service_address" maxlength="255"
       value="<?= e($old['service_address']) ?>" class="form-control" required>

<label for="notes">Additional Notes / Specific Needs *</label>
<textarea id="notes" name="notes" class="form-control" rows="4"
          maxlength="2000" required><?= e($old['notes']) ?></textarea>

<div class="form-check mt-3">
    <input type="checkbox" id="confirmInfo" name="confirm" value="1"
           class="form-check-input" required>
    <label for="confirmInfo" class="form-check-label">
        I confirm that the information provided is accurate.
    </label>
</div>

<button type="submit" name="submit" value="1" class="submit-btn mt-3">
    Submit Consultation Request
</button>

</form>
</div>
</section>

<?php include '../includes/footer.php'; ?>