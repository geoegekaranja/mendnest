<?php include 'includes/header.php'; ?>

<section class="py-5 bg-light">
<div class="container">
<h2 class="fw-bold mb-4">Care Request Form</h2>

<form class="bg-white p-4 rounded shadow">

<div class="row">
<div class="col-md-6 mb-3">
<input type="text" class="form-control" placeholder="First Name" required>
</div>

<div class="col-md-6 mb-3">
<input type="text" class="form-control" placeholder="Last Name" required>
</div>
</div>

<input type="email" class="form-control mb-3" placeholder="Email Address">

<input type="tel" class="form-control mb-3" placeholder="+254 712 345678">

<input type="text" class="form-control mb-3" placeholder="Street Address">

<select class="form-control mb-3">
<option>Select Care Package</option>
<option>Essential Care</option>
<option>Plus Care</option>
<option>Integrated Care</option>
</select>

<input type="date" class="form-control mb-3">

<textarea class="form-control mb-3" placeholder="Additional Notes"></textarea>

<button class="btn btn-success w-100">Submit</button>

</form>
</div>
</section>

<?php include 'includes/footer.php'; ?>