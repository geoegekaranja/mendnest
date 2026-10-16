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
<section class="py-5 text-center">
<div class="container">

<h2 class="fw-bold mb-5">Our Vision & Mission</h2>

<div class="row g-4 justify-content-center">

<div class="col-md-5">
    <div class="info-card">
        <h4>Our Vision</h4>
        <p>
            To make nursing services accessible and available to all individuals and families.
        </p>
    </div>
</div>

<div class="col-md-5">
    <div class="info-card">
        <h4>Our Mission</h4>
        <p>
            To provide personalized, high-quality healthcare that enhances recovery and overall well-being.
        </p>
    </div>
</div>

</div>

</div>
</section>

<!-- CORE VALUES -->
<section class="py-5 text-center">
<div class="container">

<h2 class="fw-bold mb-5">Core Values</h2>

<div class="row g-4 justify-content-center">

<div class="col-md-3 col-6">
    <div class="info-card small-card">Quality Care</div>
</div>

<div class="col-md-3 col-6">
    <div class="info-card small-card">Holistic Health</div>
</div>

<div class="col-md-3 col-6">
    <div class="info-card small-card">Accessibility</div>
</div>

<div class="col-md-3 col-6">
    <div class="info-card small-card">Collaboration</div>
</div>

<div class="col-md-3 col-6">
    <div class="info-card small-card">Comfort</div>
</div>

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

<?php
/* Paste this block where your TESTIMONIALS + CTA sections were.
   REQUIREMENTS for the page that contains it (e.g. index.php):
     1. session_start();  must be the very first line of the page,
        before `include '../includes/header.php'`.
     2. $conn must already be available (connection.php included above).
*/

if (!function_exists('e')) {
    function e(string $v): string {
        return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
    }
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// One-time messages left by submit_testimonial.php
$t_success = $_SESSION['t_success'] ?? '';
$t_errors  = $_SESSION['t_errors']  ?? [];
$t_old     = $_SESSION['t_old']     ?? ['name' => '', 'message' => ''];
unset($_SESSION['t_success'], $_SESSION['t_errors'], $_SESSION['t_old']);

// Only approved testimonials, newest first
$testimonials = [];
try {
    $res = $conn->query(
        "SELECT name, message FROM testimonials
         WHERE is_approved = 1
         ORDER BY created_at DESC
         LIMIT 3"
    );
    $testimonials = $res->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $ex) {
    error_log('Testimonials fetch failed: ' . $ex->getMessage());
}
?>

<!-- TESTIMONIALS -->
<section id="testimonials" class="py-5 bg-light text-center">
<div class="container">

<h2 class="fw-bold mb-5">Testimonials</h2>

<?php if ($t_success !== ''): ?>
    <div class="alert alert-success mx-auto" style="max-width:500px;">
        <?= e($t_success) ?>
    </div>
<?php endif; ?>

<?php if ($t_errors): ?>
    <div class="alert alert-danger mx-auto text-start" style="max-width:500px;">
        <ul class="mb-0">
            <?php foreach ($t_errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="submit_testimonial.php" class="mx-auto mb-4" style="max-width:500px;">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

    <!-- Honeypot (hidden from humans) -->
    <div style="position:absolute; left:-9999px;" aria-hidden="true">
        <label for="t_website">Leave this field empty</label>
        <input type="text" id="t_website" name="website" tabindex="-1" autocomplete="off">
    </div>

    <label for="t_name" class="visually-hidden">Your Name</label>
    <input type="text" id="t_name" name="t_name" class="form-control mb-3"
           maxlength="100" value="<?= e($t_old['name']) ?>"
           placeholder="Your Name" required>

    <label for="t_message" class="visually-hidden">Your Experience</label>
    <textarea id="t_message" name="t_message" class="form-control mb-3" rows="4"
              minlength="10" maxlength="1000" placeholder="Your Experience"
              required><?= e($t_old['message']) ?></textarea>

    <button type="submit" class="btn btn-success">Submit</button>
</form>

<?php if ($testimonials): ?>
    <?php foreach ($testimonials as $row): ?>
        <p class="mt-2">&ldquo;<?= e($row['message']) ?>&rdquo; &mdash; <?= e($row['name']) ?></p>
    <?php endforeach; ?>
<?php else: ?>
    <p class="text-muted">Be the first to share your experience.</p>
<?php endif; ?>

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