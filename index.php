<?php include 'includes/header.php'; ?>
<?php include 'database/connection.php'; ?>

<!-- HERO -->
<section id="home" class="hero d-flex align-items-center text-center">
<div class="collapse navbar-collapse" id="mainNavbar">

<div class="floating-card mx-auto" style="max-width:600px;">
<h1 class="fw-bold">Personalized Home Nursing</h1>
<p>Your trusted partner in home care services</p>

<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
    <span class="navbar-toggler-icon"></span>
</button>
</div>

</div>
</section>

<!-- ABOUT -->
<section id="about" class="section text-center">
<div class="collapse navbar-collapse" id="mainNavbar">

<div class="floating-card mx-auto" style="max-width:700px;">
<h2>About Us</h2>
<p>
We are a team of certified caregivers offering professional healthcare services at home.
We support recovery and improve quality of life in the comfort of your home.
</p>
</div>

</div>
</section>

<!-- VISION & MISSION -->
<section class="section text-center">
<div class="collapse navbar-collapse" id="mainNavbar">

<div class="row justify-content-center">

<div class="col-md-5 accent-card m-2">
<h3>Our Vision</h3>
<p>To make nursing services accessible to individuals & families.</p>
</div>

<div class="col-md-5 accent-card m-2">
<h3>Our Mission</h3>
<p>To provide quality, compassionate and personalized healthcare.</p>
</div>

</div>

</div>
</section>

<!-- CORE VALUES -->
<section id="values" class="section text-center">
<div class="collapse navbar-collapse" id="mainNavbar">

<h2>Core Values</h2>

<div class="row justify-content-center">

<div class="col-md-3 floating-card m-2">Adult Care</div>
<div class="col-md-3 floating-card m-2">Holistic Health</div>
<div class="col-md-3 floating-card m-2">Community</div>
<div class="col-md-3 floating-card m-2">Accessibility</div>

</div>

</div>
</section>

<!-- SERVICES -->
<section id="services" class="section text-center">
<div class="collapse navbar-collapse" id="mainNavbar">

<h2>Our Services</h2>

<div class="row justify-content-center">

<div class="col-md-4 floating-card m-2">
<h5>Elderly Support</h5>
<p>Daily assistance & care</p>
</div>

<div class="col-md-4 floating-card m-2">
<h5>Post Surgery Care</h5>
<p>Recovery support</p>
</div>

<div class="col-md-4 floating-card m-2">
<h5>Chronic Illness Management</h5>
<p>Continuous care</p>
</div>

<div class="col-md-4 floating-card m-2">
<h5>Palliative Care</h5>
<p>Comfort-focused care</p>
</div>

<div class="col-md-4 floating-card m-2">
<h5>Home Safety Evaluation</h5>
<p>Risk assessment</p>
</div>

<div class="col-md-4 floating-card m-2">
<h5>Nursing Procedures</h5>
<p>Professional treatments</p>
</div>

</div>

</div>
</section>

<!-- CARE PACKAGES -->
<section id="packages" class="section text-center">
<div class="collapse navbar-collapse" id="mainNavbar">

<h2>Care Packages</h2>

<div class="row justify-content-center">

<div class="col-md-4 accent-card m-2">
<h4>Essential Care</h4>
<p>Non-clinical support, daily living assistance</p>
</div>

<div class="col-md-4 accent-card m-2">
<h4>Plus Care</h4>
<p>Direct nursing care, monitoring & procedures</p>
</div>

<div class="col-md-4 accent-card m-2">
<h4>Integrated Care</h4>
<p>Multidisciplinary support & specialized care</p>
</div>

</div>

</div>
</section>

<!-- WHY MEND NEST -->
<section id="why" class="section text-center">
<div class="collapse navbar-collapse" id="mainNavbar">

<div class="floating-card mx-auto" style="max-width:700px;">
<h2>Why Mend Nest?</h2>

<ul class="text-start">
<li>Elderly Care</li>
<li>Chronic Illness Management</li>
<li>Home Safety Evaluation</li>
<li>Postnatal Care</li>
<li>Palliative Care</li>
<li>Nursing Procedures at Home</li>
<li>Post Surgery Care</li>
<li>Preventive Care</li>
<li>Care Coordination with Healthcare Professionals</li>
</ul>
</div>

</div>
</section>

<!-- TESTIMONIALS -->
<section id="testimonials" class="section text-center">
<div class="collapse navbar-collapse" id="mainNavbar">

<h2>Testimonials</h2>

<?php
if(isset($_POST['t_name'])){
    $name = $_POST['t_name'];
    $message = $_POST['t_message'];

    mysqli_query($conn,"INSERT INTO testimonials(name,message) VALUES('$name','$message')");
}
?>

<form method="POST" class="floating-card mx-auto" style="max-width:500px;">

<input type="text" name="t_name" class="form-control mb-3" placeholder="Your Name" required>

<textarea name="t_message" class="form-control mb-3" placeholder="Your Experience"></textarea>

<button class="btn">Submit</button>

</form>

<?php
$result = mysqli_query($conn,"SELECT * FROM testimonials");

while($row = mysqli_fetch_assoc($result)){
    echo "<p class='mt-3'>\"".$row['message']."\" - ".$row['name']."</p>";
}
?>

</div>
</section>

<!-- REQUEST CARE -->
<section id="request" class="section text-center">
<div class="collapse navbar-collapse" id="mainNavbar">

<h2>Request Care</h2>

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

<p class="mt-4">
📞 +254 769 604 111 <br>
📧 info@mendnest.co.ke
</p>

</div>
</section>

<?php include 'includes/footer.php'; ?>