<?php include 'includes/header.php'; ?>
<?php include 'database/connection.php'; ?>

<!-- HERO (WHITE BACKGROUND) -->
<section class="hero-clean text-center">
<div class="container">

<p class="text-uppercase text-muted">Home Health</p>

<h1 class="fw-bold display-5 text-dark">
Personalized Home Nursing
</h1>

<hr class="mx-auto" style="width:60px;">

<p class="text-muted mt-3">
Welcome to Mend Nest KE, your trusted provider for exclusive home-based nursing services.
We focus on illness prevention, recovery support and improving your overall well-being.
</p>

</div>
</section>

<!-- ABOUT -->
<section class="py-5 bg-white">
<div class="container">

<h2 class="text-center fw-bold mb-5">ABOUT US</h2>

<div class="row align-items-center">

<div class="col-md-5 mb-4">
<img src="assets/nurse.jpg" class="img-fluid rounded shadow">
</div>

<div class="col-md-7">
<p>
We are a team of certified nurses and nurse assistants, fully equipped and prepared to deliver quality nursing services.
We offer dependable, professional care for individuals who may not be able to access hospital-based services or prefer recovery at home.
</p>

<p>
We serve post-surgical patients, palliative care clients, individuals with chronic illnesses, and families seeking preventive and wellness healthcare support.
</p>
</div>

</div>
</div>
</section>

<!-- VISION & MISSION -->
<section class="py-5 bg-light text-center">
<div class="container">

<div class="row">

<div class="col-md-6">
<h3>Our Vision</h3>
<p>To make nursing services accessible and available to all individuals and families.</p>
</div>

<div class="col-md-6">
<h3>Our Mission</h3>
<p>To provide personalized, high-quality healthcare that enhances recovery and overall well-being.</p>
</div>

</div>

</div>
</section>

<!-- CORE VALUES -->
<section class="py-5 bg-white text-center">
<div class="container">

<h2 class="fw-bold mb-4">Core Values</h2>

<div class="row">

<div class="col-md-3">Quality Care</div>
<div class="col-md-3">Holistic Health</div>
<div class="col-md-3">Accessibility</div>
<div class="col-md-3">Collaboration</div>
<div class="col-md-3">Comfort</div>

</div>

</div>
</section>

<!-- WHY -->
<section class="py-5 bg-light text-center">
<div class="container">

<h2 class="fw-bold mb-5">WHY MEND NEST?</h2>

<div class="mx-auto" style="max-width:700px;">
<ul class="premium-list">
<li>Care tailored to each client’s needs & preferences</li>
<li>More time per patient for thorough assessments</li>
<li>Extensive education & support for patients and families</li>
<li>Bridges the gap between hospital and home care</li>
</ul>
</div>

</div>
</section>

<!-- SERVICES (9 SERVICES) -->
<section id="services" class="py-5 text-center">
<div class="container">

<h2 class="fw-bold mb-5">Our Services</h2>

<div class="row g-4">

<?php
$services = [
"Elderly Support",
"Postnatal Care",
"Post Surgery Recovery",
"Chronic Illness Management",
"Palliative Care",
"Home Safety Evaluation",
"Nursing Procedures at Home",
"Preventive Care",
"Care Coordination"
];

foreach($services as $service){
?>

<div class="col-md-4">
    <div class="service-card">
        <h5><?php echo $service; ?></h5>
    </div>
</div>

<?php } ?>

</div>

</div>
</section>

<!-- PACKAGES -->
<section class="py-5 bg-white text-center">
<div class="container">

<h2 class="fw-bold mb-5">Care Packages</h2>

<div class="row">

<div class="col-md-4 mb-4">
<div class="package-card">
<h4>Essential Care</h4>
<p>Non-clinical support and daily assistance.</p>
</div>
</div>

<div class="col-md-4 mb-4">
<div class="package-card active">
<h4>Plus Care</h4>
<p>Direct nursing care, monitoring and procedures.</p>
</div>
</div>

<div class="col-md-4 mb-4">
<div class="package-card">
<h4>Integrated Care</h4>
<p>Multidisciplinary and specialized healthcare support.</p>
</div>
</div>

</div>

</div>
</section>

<!-- TESTIMONIALS -->
<section class="py-5 bg-light text-center">
<div class="container">

<h2 class="fw-bold mb-5">Testimonials</h2>

<?php
if(isset($_POST['t_name'])){
$name = $_POST['t_name'];
$message = $_POST['t_message'];

mysqli_query($conn,"INSERT INTO testimonials(name,message) VALUES('$name','$message')");
}
?>

<form method="POST" class="mx-auto mb-4" style="max-width:500px;">
<input type="text" name="t_name" class="form-control mb-3" placeholder="Your Name" required>
<textarea name="t_message" class="form-control mb-3" placeholder="Your Experience"></textarea>
<button class="btn btn-success">Submit</button>
</form>

<?php
$result = mysqli_query($conn,"SELECT * FROM testimonials LIMIT 3");

while($row = mysqli_fetch_assoc($result)){
echo "<p class='mt-2'>\"".$row['message']."\" - ".$row['name']."</p>";
}
?>

</div>
</section>

<!-- CTA -->
<section class="cta-section text-center text-white">
<div class="container">

<h2 class="fw-bold mb-3">Need Professional Home Care?</h2>
<p class="mb-4">Get started with personalized nursing services today.</p>

<a href="consultation.php" class="btn btn-light px-4 py-2 fw-semibold">
Request Care
</a>

</div>
</section>

<!-- CONTACT -->
<section class="py-5 bg-white text-center">
<div class="container">

<h2 class="fw-bold mb-4">Contact Information</h2>

<p>📞 +254 769364171</p>
<p>📧 info@mendnest.co.ke</p>
<p>📍 Spur Mall, second floor, room 532</p>

</div>
</section>

<?php include 'includes/footer.php'; ?>