</main>

    <footer class="text-center text-muted small py-3">
        &copy; <?= date('Y') ?> Mend Nest KE Home Nursing
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    var body = document.body;
    document.getElementById('dashToggle').addEventListener('click', function () {
        body.classList.toggle('sidebar-open');
    });
    document.getElementById('dashBackdrop').addEventListener('click', function () {
        body.classList.remove('sidebar-open');
    });
})();
</script>
</body>
</html>