
<?php 
include '../includes/header.php';
include '../database/connection.php';
?>


<section class="request-section">
<div class="container">

<div class="request-card mx-auto">

<h2 class="fw-bold mb-4 text-center">Care Request Form</h2>

<form method="POST">

<!-- NAMES -->
<div class="row">
<div class="col-md-6 mb-3">
<input type="text" name="fname" class="form-control" placeholder="First Name" required>
</div>

<div class="col-md-6 mb-3">
<input type="text" name="lname" class="form-control" placeholder="Last Name" required>
</div>
</div>

<!-- EMAIL -->
<input type="email" name="email" class="form-control mb-3" placeholder="Email Address" required>

<!-- PHONE -->
<input type="tel" name="phone" class="form-control mb-3" placeholder="+254 712 345678" required>

<!-- ADDRESS -->
<input type="text" name="address" class="form-control mb-3" placeholder="Street Address">

<!-- PACKAGE -->
<select name="package" class="form-control mb-3">
<option value="">Select Care Package</option>
<option>Essential Care</option>
<option>Plus Care</option>
<option>Integrated Care</option>
</select>

<!-- DATE -->
<input type="date" name="date" class="form-control mb-3">

<!-- NOTES -->
<textarea name="notes" class="form-control mb-3" placeholder="Additional Notes"></textarea>

<!-- BUTTON -->
<button name="submit" class="btn btn-success w-100">
Submit Request
</button>

</form>

</div>
</div>
</section>

 include '../includes/footer.php'; ?>