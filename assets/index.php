<?php include '../includes/header.php'; ?>

<!-- HERO -->
<section class="hero text-center text-white d-flex align-items-center">

<div class="container">
    <link rel="stylesheet" href="/mendnest/css/style.css">


<h1 class="display-4 fw-bold">Professional Home Nursing Care</h1>

<p class="lead">
Mend Nest KE provides compassionate and reliable home nursing services,
ensuring patients receive professional healthcare in the comfort of their homes.
</p>

<a href="consultation.php" class="btn btn-light btn-lg">
Book Consultation
</a>

</div>

</section>


<!-- SERVICES -->
<section class="services py-5">

<div class="container text-center">

<h2 class="fw-bold mb-5">Our Services</h2>

<div class="row g-4">

<?php
$services = [
["Medication & Treatment","Professional administration of medication including injections and IV therapy at home."],
["Wound Care","Expert wound care services including surgical dressing and infection prevention."],
["IV Hydration","Vitamin infusion and hydration therapy to improve recovery, wellness and overall health."]
];

foreach($services as $service){
?>

<div class="col-md-4">
<div class="card service-card p-4">

<h4><?php echo $service[0]; ?></h4>

<p><?php echo $service[1]; ?></p>

<a href="service.php" class="btn btn-success btn-sm">
Learn More
</a>

</div>
</div>

<?php } ?>

</div>

</div>

</section>


<!-- ACCOUNT ACCESS -->
<section class="account-section py-5 bg-light">

<div class="container text-center">

<h2 class="fw-bold">Access Your Account</h2>

<p>
Login to manage consultations, appointments and services with Mend Nest KE.
</p>

<a href="../patient/login.php" class="btn btn-primary btn-lg me-2">
Patient Login
</a>

<a href="../admin/login.php" class="btn btn-dark btn-lg">
Admin Login
</a>

</div>

</section>

<?php include '../includes/footer.php'; ?>