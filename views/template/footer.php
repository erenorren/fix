</div> </main>

    <footer class="app-footer border-top small text-muted py-2 px-3">
        <div class="d-flex justify-content-end">
            <span>&copy; <?= date('Y') ?> Sistem Penitipan Hewan</span>
        </div>
    </footer>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

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

            // Optional: Tutup submenu lain saat satu dibuka (Accordion effect)
            /*
            document.querySelectorAll('.submenu.show').forEach(function (sm) {
                if (sm !== menu) sm.classList.remove('show');
            });
            document.querySelectorAll('.has-dropdown.active-dropdown').forEach(function (lnk) {
                if (lnk !== link) lnk.classList.remove('active-dropdown');
            });
            */

            // Toggle submenu saat ini
            menu.classList.toggle('show');
            link.classList.toggle('active-dropdown');
        });
    });

    // --- 2. HANDLER SIDEBAR MOBILE MANUAL (Fallback) ---
    // Jika tombol native AdminLTE macet, script ini akan memaksa sidebar bekerja
    const toggleBtn = document.querySelector('[data-lte-toggle="sidebar"]');
    const body = document.body;

    if(toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            // Cek apakah AdminLTE JS sudah handle?
            // Biasanya AdminLTE akan menambah class 'sidebar-open' di mobile
            
            // Jaga-jaga delay JS, kita bisa bantu toggle class manual jika perlu
            // Uncomment baris di bawah jika tombol masih tidak respon sama sekali:
            // e.preventDefault();
            // if (window.innerWidth < 992) {
            //    body.classList.toggle('sidebar-open');
            // } else {
            //    body.classList.toggle('sidebar-collapse');
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