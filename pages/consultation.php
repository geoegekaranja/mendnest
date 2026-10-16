
<?php
include '../database/connection.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['submit'])) {

    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $package = trim($_POST['package'] ?? '');
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $duration = trim($_POST['duration'] ?? '');
    $service_address = trim($_POST['service_address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (
        $fname === '' || $lname === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $phone === '' || $address === '' ||
        $city === '' || $country === '' ||
        $package === '' || $date === '' ||
        !in_array($time, ['Morning', 'Afternoon', 'Evening'], true) ||
        $duration === '' || $service_address === '' ||
        $notes === ''
    ) {
        $error = 'Please complete all fields with valid information.';
    } else {
        $sql = "INSERT INTO consultations
        (fname, lname, email, phone, address, city, country,
         package, preferred_date, preferred_time, duration,
         service_address, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                'sssssssssssss',
                $fname,
                $lname,
                $email,
                $phone,
                $address,
                $city,
                $country,
                $package,
                $date,
                $time,
                $duration,
                $service_address,
                $notes
            );

            if (mysqli_stmt_execute($stmt)) {
                $success = 'Your care consultation request has been submitted successfully!';
            } else {
                $error = 'Unable to submit your request. Please try again.';
            }

            mysqli_stmt_close($stmt);
        } else {
            $error = 'A database error occurred. Please try again.';
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<section class="care-section">
<div class="container">

<h2 class="fw-bold mb-4">Care Consultation Form</h2>

<?php if ($success !== ''): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<form method="POST" class="care-form">

<div class="row">
    <div class="col-md-6">
        <label>First Name *</label>
        <input type="text" name="fname" maxlength="100"
               class="form-control" required>
    </div>

    <div class="col-md-6">
        <label>Last Name *</label>
        <input type="text" name="lname" maxlength="100"
               class="form-control" required>
    </div>
</div>

<label>Email Address *</label>
<input type="email" name="email" maxlength="150"
       class="form-control" required>

<label>Phone *</label>
<input type="tel" name="phone" maxlength="30"
       placeholder="+254 712 345678"
       class="form-control" required>

<label>Street Address *</label>
<input type="text" name="address" maxlength="255"
       class="form-control" required>

<label>City *</label>
<input type="text" name="city" maxlength="100"
       class="form-control" required>

<label>Country *</label>
<select name="country" class="form-select" required>
    <option value="">Select country</option>
    <option value="Kenya">Kenya</option>
    <option value="Uganda">Uganda</option>
    <option value="Tanzania">Tanzania</option>
</select>

<label>Select a Care Package *</label>
<select name="package" class="form-select" required>
    <option value="">Select care package</option>
    <option value="Essential Care">Essential Care</option>
    <option value="Plus Care">Plus Care</option>
    <option value="Integrated Care">Integrated Care</option>
</select>

<label>Preferred Start Date *</label>
<input type="date" name="date" class="form-control"
       min="<?= date('Y-m-d') ?>" required>

<label>Preferred Time Slot *</label>
<select name="time" class="form-select" required>
    <option value="">Select time slot</option>
    <option value="Morning">Morning</option>
    <option value="Afternoon">Afternoon</option>
    <option value="Evening">Evening</option>
</select>

<label>Estimated Duration of Service *</label>
<select name="duration" class="form-select" required>
    <option value="">Select duration</option>
    <option value="1 Day">1 Day</option>
    <option value="1 Week">1 Week</option>
    <option value="1 Month">1 Month</option>
</select>

<label>Service Location / Address *</label>
<input type="text" name="service_address" maxlength="255"
       class="form-control" required>

<label>Additional Notes / Specific Needs *</label>
<textarea name="notes" class="form-control"
          rows="4" required></textarea>

<div class="form-check mt-3">
    <input type="checkbox" id="confirmInfo"
           class="form-check-input" required>
    <label for="confirmInfo" class="form-check-label">
        I confirm that the information provided is accurate.
    </label>
</div>

<button type="submit" name="submit" value="1"
        class="submit-btn mt-3">
    Submit Consultation Request
</button>

</form>
</div>
</section>

<?php include '../includes/footer.php'; ?>
