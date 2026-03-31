<?php include 'includes/header.php'; ?>
<?php include 'database/connection.php'; ?>

<!-- HERO -->
<section id="home" class="hero d-flex align-items-center text-center">
    <div class="container">
        <div class="floating-card mx-auto" style="max-width:600px;">
            <h1 class="fw-bold">Personalized Home Nursing</h1>
            <p>Your trusted partner in home care services</p>
        </div>
    </div>
</section>

<!-- ABOUT (WHITE BACKGROUND + PARAGRAPH) -->
<section id="about" class="py-5 bg-white text-center">
    <div class="container">
        <div class="mx-auto" style="max-width:700px;">
            <h2 class="fw-bold mb-3">About Mend Nest</h2>
            <p class="text-muted">
                Mend Nest KE is dedicated to delivering high-quality home nursing services that prioritize comfort,
                dignity, and personalized care. Our team of trained professionals ensures that patients receive
                medical and non-medical support in a familiar home environment. We aim to enhance recovery,
                promote independence, and improve overall well-being through compassionate and reliable care.
            </p>
        </div>
    </div>
</section>

<!-- WHY MEND NEST (WHITE + PARAGRAPH) -->
<section id="why" class="py-5 bg-white text-center">
    <div class="container">
        <div class="mx-auto" style="max-width:700px;">
            <h2 class="fw-bold mb-3">Why Choose Mend Nest?</h2>
            <p class="text-muted">
                We provide a comprehensive range of home care services including elderly care, chronic illness
                management, post-surgery recovery, and palliative care. Our approach focuses on personalized
                treatment, safety, and continuous support. With professional caregivers and strong coordination
                with healthcare providers, Mend Nest ensures that every client receives dependable and
                compassionate care tailored to their needs.
            </p>
        </div>
    </div>
</section>

<!-- SERVICES -->
<section id="services" class="py-5 text-center">
    <div class="container">
        <h2 class="fw-bold mb-4">Our Services</h2>

        <div class="row justify-content-center">
            <div class="col-md-4 mb-3">
                <div class="floating-card p-3">
                    <h5>Elderly Support</h5>
                    <p>Daily assistance & care</p>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="floating-card p-3">
                    <h5>Post Surgery Care</h5>
                    <p>Recovery support</p>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="floating-card p-3">
                    <h5>Chronic Illness Management</h5>
                    <p>Continuous care</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TESTIMONIALS -->
<section id="testimonials" class="py-5 bg-light text-center">
    <div class="container">
        <h2 class="fw-bold mb-4">Testimonials</h2>

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
        $result = mysqli_query($conn,"SELECT * FROM testimonials");

        while($row = mysqli_fetch_assoc($result)){
            echo "<p class='mt-2'>\"".$row['message']."\" - ".$row['name']."</p>";
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