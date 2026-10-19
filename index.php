<?php
require_once 'includes/header.php';      // starts the session, builds the menu, defines page_url()
require_once 'database/connection.php';

// Links to the other pages (null if a file doesn't exist, in which case that button is hidden)
$consultUrl  = page_url('consultation.php');
$aboutUrl    = page_url('about.php');
$servicesUrl = page_url('services.php');
$blogUrl     = page_url('blog.php');
$contactUrl  = page_url('contact.php');

// One-time messages left by pages/submit_testimonial.php
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

<div class="mt-4">
    <?php if ($consultUrl): ?>
        <a href="<?= h($consultUrl) ?>" class="btn btn-success px-4 me-2 mb-2">Request Care</a>
    <?php endif; ?>
    <a href="#services" class="btn btn-outline-success px-4 mb-2">Our Services</a>
</div>

</div>
</section>

<!-- ABOUT -->
<section class="py-5 bg-white">
<div class="container">

<h2 class="text-center fw-bold mb-5">ABOUT US</h2>

<div class="row align-items-center">

<div class="col-md-5 mb-4">
<img src="assets/nurse.jpg" alt="Mend Nest nurse" class="img-fluid rounded shadow">
</div>

<div class="col-md-7">
<p>
We are a team of certified nurses and nurse assistants, fully equipped and prepared to deliver quality nursing services.
We offer dependable, professional care for individuals who may not be able to access hospital-based services or prefer recovery at home.
</p>

<p>
We serve post-surgical patients, palliative care clients, individuals with chronic illnesses, and families seeking preventive and wellness healthcare support.
</p>

<?php if ($aboutUrl): ?>
    <a href="<?= h($aboutUrl) ?>" class="btn btn-outline-success">Learn more about us</a>
<?php endif; ?>
</div>

</div>
</div>
</section>

<!-- VISION & MISSION -->
<section class="py-5 text-center">
<div class="container">

<h2 class="fw-bold mb-5">Our Vision &amp; Mission</h2>

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
<li>Care tailored to each client’s needs &amp; preferences</li>
<li>More time per patient for thorough assessments</li>
<li>Extensive education &amp; support for patients and families</li>
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

foreach ($services as $service):
?>

<div class="col-md-4">
    <div class="service-card">
        <h5><?= h($service) ?></h5>
    </div>
</div>

<?php endforeach; ?>

</div>

<?php if ($servicesUrl): ?>
    <div class="mt-4">
        <a href="<?= h($servicesUrl) ?>" class="btn btn-outline-success px-4">View all services</a>
    </div>
<?php endif; ?>

</div>
</section>

<!-- PACKAGES -->
<section class="py-5 bg-white text-center">
<div class="container">

<h2 class="fw-bold mb-5">Care Packages</h2>

<div class="row">

<?php
$packages = [
    ['Essential Care',  'Non-clinical support and daily assistance.',            false],
    ['Plus Care',       'Direct nursing care, monitoring and procedures.',       true],
    ['Integrated Care', 'Multidisciplinary and specialized healthcare support.', false],
];
foreach ($packages as [$pkgName, $pkgDesc, $pkgActive]):
?>
<div class="col-md-4 mb-4">
<div class="package-card<?= $pkgActive ? ' active' : '' ?>">
<h4><?= h($pkgName) ?></h4>
<p><?= h($pkgDesc) ?></p>
<?php if ($consultUrl): ?>
    <!-- opens the consultation form with this package already selected -->
    <a href="<?= h($consultUrl . '?package=' . rawurlencode($pkgName)) ?>"
       class="btn btn-sm btn-success">Choose this package</a>
<?php endif; ?>
</div>
</div>
<?php endforeach; ?>

</div>

</div>
</section>

<!-- BLOG -->
<?php if ($blogUrl): ?>
<section class="py-5 text-center">
<div class="container">

<h2 class="fw-bold mb-3">Health Tips &amp; News</h2>
<p class="text-muted mb-4">
Practical advice from our nurses on recovery, elder care and healthy living at home.
</p>
<a href="<?= h($blogUrl) ?>" class="btn btn-outline-success px-4">Read our blog</a>

</div>
</section>
<?php endif; ?>

<!-- TESTIMONIALS -->
<section id="testimonials" class="py-5 bg-light text-center">
<div class="container">

<h2 class="fw-bold mb-5">Testimonials</h2>

<?php if ($t_success !== ''): ?>
    <div class="alert alert-success mx-auto" style="max-width:500px;">
        <?= h($t_success) ?>
    </div>
<?php endif; ?>

<?php if ($t_errors): ?>
    <div class="alert alert-danger mx-auto text-start" style="max-width:500px;">
        <ul class="mb-0">
            <?php foreach ($t_errors as $err): ?>
                <li><?= h($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?= h(BASE_URL) ?>/pages/submit_testimonial.php"
      class="mx-auto mb-4" style="max-width:500px;">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

    <!-- Honeypot (hidden from humans) -->
    <div style="position:absolute; left:-9999px;" aria-hidden="true">
        <label for="t_website">Leave this field empty</label>
        <input type="text" id="t_website" name="website" tabindex="-1" autocomplete="off">
    </div>

    <label for="t_name" class="visually-hidden">Your Name</label>
    <input type="text" id="t_name" name="t_name" class="form-control mb-3"
           maxlength="100" value="<?= h($t_old['name']) ?>"
           placeholder="Your Name" required>

    <label for="t_message" class="visually-hidden">Your Experience</label>
    <textarea id="t_message" name="t_message" class="form-control mb-3" rows="4"
              minlength="10" maxlength="1000" placeholder="Your Experience"
              required><?= h($t_old['message']) ?></textarea>

    <button type="submit" class="btn btn-success">Submit</button>
</form>

<?php if ($testimonials): ?>
    <?php foreach ($testimonials as $row): ?>
        <p class="mt-2">&ldquo;<?= h($row['message']) ?>&rdquo; &mdash; <?= h($row['name']) ?></p>
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

<?php if ($consultUrl): ?>
    <a href="<?= h($consultUrl) ?>" class="btn btn-light px-4 py-2 fw-semibold me-2 mb-2">
    Request Care
    </a>
<?php endif; ?>

<?php if ($bookUrl): ?>
    <a href="<?= h($bookUrl) ?>" class="btn btn-outline-light px-4 py-2 fw-semibold mb-2">
    <?= h($bookLabel) ?>
    </a>
<?php endif; ?>

</div>
</section>

<!-- CONTACT -->
<section class="py-5 bg-white text-center">
<div class="container">

<h2 class="fw-bold mb-4">Contact Information</h2>

<p>📞 <a href="tel:+254769364171" class="text-decoration-none text-dark">+254 769364171</a></p>
<p>📧 <a href="mailto:info@mendnest.co.ke" class="text-decoration-none text-dark">info@mendnest.co.ke</a></p>
<p>📍 Spur Mall, second floor, room 532</p>

<?php if ($contactUrl): ?>
    <a href="<?= h($contactUrl) ?>" class="btn btn-success px-4 mt-2">Send us a message</a>
<?php endif; ?>

</div>
</section>

<?php include 'includes/footer.php'; ?>