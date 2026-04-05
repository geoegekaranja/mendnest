<?php include 'includes/header.php'; ?>

<!-- HERO -->
<section class="hero-clean text-center">
<div class="container">

<p class="text-uppercase text-muted">Our Services</p>

<h1 class="fw-bold display-5 text-dark">
Professional Home Care Services
</h1>

<hr class="mx-auto" style="width:60px;">

<p class="text-muted mt-3 mx-auto" style="max-width:700px;">
We provide a wide range of personalized home nursing services designed to support recovery,
enhance comfort, and improve quality of life for individuals and families.
</p>

</div>
</section>

<!-- SERVICES INTRO -->
<section class="py-5 bg-light text-center">
<div class="container">

<h2 class="fw-bold mb-3">What We Offer</h2>
<p class="text-muted mx-auto" style="max-width:700px;">
Our services are designed to meet different healthcare needs, from daily assistance to
specialized medical care, all delivered in the comfort of your home.
</p>

</div>
</section>

<!-- SERVICES GRID -->
<section class="py-5 text-center">
<div class="container">

<div class="row g-4">

<?php
$services = [
["Elderly Support","Daily assistance, companionship and mobility support for seniors."],
["Postnatal Care","Support for mothers and newborns during recovery period."],
["Post Surgery Recovery","Care and monitoring to ensure safe and quick recovery."],
["Chronic Illness Management","Continuous care for conditions like diabetes and hypertension."],
["Palliative Care","Comfort-focused care for patients with serious illnesses."],
["Home Safety Evaluation","Assessment to improve safety and reduce risks at home."],
["Nursing Procedures","Professional clinical services delivered at home."],
["Preventive Care","Health monitoring and early detection of potential issues."],
["Care Coordination","Working with doctors and families for complete care."]
];

foreach($services as $service){
?>

<div class="col-md-4">
    <div class="service-card h-100">

        <div class="mb-3">
            <i class="bi bi-heart-pulse fs-1 text-success"></i>
        </div>

        <h5 class="fw-bold"><?php echo $service[0]; ?></h5>
        <p class="text-muted"><?php echo $service[1]; ?></p>

    </div>
</div>

<?php } ?>

</div>

</div>
</section>

<!-- WHY CHOOSE US -->
<section class="py-5 bg-light text-center">
<div class="container">

<h2 class="fw-bold mb-4">Why Choose Our Services?</h2>

<div class="mx-auto" style="max-width:700px;">
<ul class="premium-list">
<li>Personalized care tailored to individual needs</li>
<li>Professional and certified healthcare providers</li>
<li>Comfort of receiving care at home</li>
<li>Continuous monitoring and support</li>
</ul>
</div>

</div>
</section>

<!-- CTA -->
<section class="cta-section text-center text-white">
<div class="container">

<h2 class="fw-bold mb-3">Ready to Get Started?</h2>
<p class="mb-4">Book a care service today and experience professional home nursing.</p>

<a href="consultation.php" class="btn btn-light px-4 py-2 fw-semibold">
Request Care
</a>

</div>
</section>

<?php include 'includes/footer.php'; ?>