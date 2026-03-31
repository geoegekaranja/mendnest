<?php include 'includes/header.php'; ?>
<?php include 'database/connection.php'; ?>

<!-- HERO -->
<section id="home" class="hero text-white d-flex align-items-center">
<div class="container text-center">
<h1 class="fw-bold">Personalized Home Nursing</h1>
<p>Your trusted partner in home care services</p>
<a href="#contact" class="btn btn-light">Request Care</a>
<button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#careModal">
Request Care
</button>
</div>
</section>

<!-- ABOUT -->
<section id="about" class="section white-section">
<div class="container">
<h2>About Us</h2>
<p>
We are a team of certified caregivers offering professional healthcare services at home.
We support recovery and improve quality of life in the comfort of your home.
</p>
</div>
</section>

<!-- VISION & MISSION -->
<section class="section bg-light">
<div class="container row text-center">

<div class="col-md-6">
<h3>Our Vision</h3>
<p>To make nursing services accessible to individuals & families.</p>
</div>

<div class="col-md-6">
<h3>Our Mission</h3>
<p>To provide quality, compassionate and personalized healthcare.</p>
</div>

</div>
</section>

<!-- CORE VALUES -->
<section id="Values" class="section white-section">
<div class="container text-center">
<h2>Core Values</h2>
<div class="row">

<div class="col-md-3">Adult Care</div>
<div class="col-md-3">Holistic Health</div>
<div class="col-md-3">Community</div>
<div class="col-md-3">Accessibility</div>

</div>
</div>
</section>

<!-- SERVICES -->
<section id="SERVIVES" class="section white-section">

<div class="container">
<h2>Our Services</h2>

<div class="row">

<div class="col-md-4">
<h5>Elderly Support</h5>
<p>Daily assistance & care</p>
</div>

<div class="col-md-4">
<h5>Post Surgery Care</h5>
<p>Recovery support</p>
</div>

<div class="col-md-4">
<h5>Chronic Illness Management</h5>
<p>Continuous care</p>
</div>

<div class="col-md-4">
<h5>Palliative Care</h5>
<p>Comfort-focused care</p>
</div>

<div class="col-md-4">
<h5>Home Safety Evaluation</h5>
<p>Risk assessment</p>
</div>

<div class="col-md-4">
<h5>Nursing Procedures</h5>
<p>Professional treatments</p>
</div>

</div>
</div>
</section>

<!-- CARE PACKAGES -->
<section id="PACKAGES" class="section white-section">
<div class="container text-center">
<h2>Care Packages</h2>

<div class="row">

<div class="col-md-4 card p-3">
<h4>Essential Care</h4>
<p>Non-clinical support, daily living assistance</p>
</div>

<div class="col-md-4 card p-3">
<h4>Plus Care</h4>
<p>Direct nursing care, monitoring & procedures</p>
</div>

<div class="col-md-4 card p-3">
<h4>Integrated Care</h4>
<p>Multidisciplinary support & specialized care</p>
</div>

</div>
</div>
</section>

<!-- WHY MEND NEST -->
<section id="WHY MENDNEST" class="section white-section">
<div class="container">
<h2>Why Mend Nest?</h2>

<ul>
<li>Personalized care tailored to each patient</li>
<li>More time with patients</li>
<li>Holistic health discussions</li>
<li>Emotional & family support</li>
<li>Flexible day/night care</li>
</ul>

</div>
</section>

<!-- TESTIMONIALS -->
<section id="testimonials" class="section">
<div class="container text-center">
<h2>Testimonials</h2>
<h4 class="mt-5">Leave a Testimonial</h4>

<form method="POST">

<input type="text" name="t_name" class="form-control mb-3" placeholder="Your Name" required>

<textarea name="t_message" class="form-control mb-3" placeholder="Your Experience"></textarea>

<button class="btn btn-success">Submit</button>

</form>

<?php
$result = mysqli_query($conn,"SELECT * FROM testimonials");

while($row = mysqli_fetch_assoc($result)){
    echo "<p>\"".$row['message']."\" - ".$row['name']."</p>";
}
?>

</div>
</section>

<section id="request" class="section white-section">
<div class="container text-center">

<h2 class="mb-4">Request Care</h2>
<p class="mb-5">Fill in your details and we will get back to you</p>

<div class="request-box mx-auto">

<form method="POST">

<input type="text" name="name" class="form-control mb-3" placeholder="Full Name" required>

<input type="tel" name="phone" class="form-control mb-3" placeholder="Phone Number" required>

<select name="service" class="form-control mb-3">
    <option>Select Service</option>
    <option>Home Nursing</option>
    <option>Elderly Care</option>
    <option>Post Surgery Care</option>
    <option>Postnatal Care</option>
    <option>Chronic Illness Management</option>
    <option>Palliative Care</option>
    <option>Preventive Care</option>
    <option>Home Safety Evaluation</option>
    <option>Nursing Procedures at Home</option>
    <option>Care Coordination</option>
</select>

<textarea name="message" class="form-control mb-3" placeholder="Describe your needs"></textarea>

<button class="btn">Submit Request</button>

</form>

</div>

</div>
</section>

<p class="mt-3">
📞 +254 769 604 111 <br>
📧 info@mendnest.co.ke
</p>

</div>
</section>

<?php include 'includes/footer.php'; ?>