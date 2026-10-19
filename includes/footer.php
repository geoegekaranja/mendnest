<footer class="bg-dark text-white p-4 mt-5">
<div class="container">

    <div class="row g-4 text-center text-md-start">

        <div class="col-md-4">
            <h6 class="fw-bold">Mend Nest KE</h6>
            <p class="small mb-0">Home Nursing<br>Spur Mall, 2nd Floor S32 | Nairobi Kenya</p>
        </div>

        <?php if (!empty($navItems)): ?>
        <div class="col-md-4">
            <h6 class="fw-bold">Quick Links</h6>
            <ul class="list-unstyled small mb-0">
                <?php foreach ($navItems as $label => $url): ?>
                    <?php if ($url): ?>
                        <li><a href="<?= h($url) ?>" class="text-white-50 text-decoration-none"><?= h($label) ?></a></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="col-md-4">
            <h6 class="fw-bold">Get in Touch</h6>
            <p class="small mb-0">
                <a href="tel:+254769364171" class="text-white-50 text-decoration-none">+254 769364171</a><br>
                <a href="mailto:info@mendnest.co.ke" class="text-white-50 text-decoration-none">info@mendnest.co.ke</a>
            </p>
        </div>

    </div>

    <hr class="border-secondary my-3">
    <p class="text-center small mb-0">© <?php echo date("Y"); ?> Mend Nest KE Home Nursing</p>

</div>
</footer>

<!-- AOS JavaScript -->
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

<script>
AOS.init({
    duration: 1000,
    once: true
});
</script>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>