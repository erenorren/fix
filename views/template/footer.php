        </div> <!-- /.app-content -->
    </main>

    <footer class="app-footer border-top small text-muted py-2 px-3">
        <div class="d-flex justify-content-end">
            <span>&copy; <?= date('Y') ?> Sistem Penitipan Hewan</span>
        </div>
    </footer>

</div> <!-- /.app-wrapper -->

<!-- Bootstrap bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

<!-- AdminLTE & custom JS (root-relative) -->
<script src="/js/adminlte.js"></script>
<script src="/js/dashboard.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- 1. HANDLER DROPDOWN SIDEBAR ---
    const dropdownLinks = document.querySelectorAll('.has-dropdown');
    
    dropdownLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = link.getAttribute('data-target');
            const menu = document.querySelector(targetId);
            if (!menu) return;
            menu.classList.toggle('show');
            link.classList.toggle('active-dropdown');
        });
    });

    // --- 2. FALLBACK TOGGLE SIDEBAR MOBILE (opsional) ---
    const toggleBtn = document.querySelector('[data-lte-toggle="sidebar"]');
    const body = document.body;

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            // AdminLTE biasanya sudah handle ini,
            // fallback manual bisa diaktifkan kalau butuh:
            // if (window.innerWidth < 992) {
            //     body.classList.toggle('sidebar-open');
            // } else {
            //     body.classList.toggle('sidebar-collapse');
            // }
        });
    }
});
</script>

<?php
if (!empty($extraScripts)) {
    echo $extraScripts;
}
?>

</body>
</html>
