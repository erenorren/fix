// =============================================
// TRANSAKSI HANDLER - LOAD EVENT VERSION
// =============================================

// Pastikan kode ini dieksekusi setelah DOM siap
(function() {
    console.log("Transaksi handler script LOADED");
    
    // Fungsi utama yang akan dijalankan saat DOM siap
    function initTransaksiHandler() {
        console.log("Initializing transaksi handler...");
        
        // =============================================
        // 1. AUTO-FILL PELANGGAN
        // =============================================
        const selectPelanggan = document.getElementById('selectPelanggan');
        const noHpInput = document.getElementById('p_hp');
        const alamatInput = document.getElementById('p_alamat');
        const newCustomerFields = document.getElementById('newCustomerFields');
        
        if (selectPelanggan && noHpInput && alamatInput) {
            console.log("Setting up pelanggan auto-fill");
            
            selectPelanggan.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                console.log("Pelanggan changed:", this.value);
                
                if (this.value === 'new') {
                    // Mode pelanggan baru
                    if (newCustomerFields) {
                        newCustomerFields.style.display = 'block';
                    }
                    noHpInput.value = '';
                    alamatInput.value = '';
                    noHpInput.required = true;
                    alamatInput.required = true;
                } 
                else if (this.value && this.value !== '') {
                    // Mode pelanggan existing
                    if (newCustomerFields) {
                        newCustomerFields.style.display = 'none';
                    }
                    noHpInput.value = selectedOption.getAttribute('data-hp') || '';
                    alamatInput.value = selectedOption.getAttribute('data-alamat') || '';
                    noHpInput.required = true;
                    alamatInput.required = true;
                }
            });
            
            // Trigger change event awal
            if (selectPelanggan.value) {
                const event = new Event('change');
                selectPelanggan.dispatchEvent(event);
            }
        }
        
        // =============================================
        // 2. KALKULASI HARGA
        // =============================================
        const paketSelect = document.getElementById('paketSelect');
        const lamaInapInput = document.getElementById('lamaInap');
        const totalDisplay = document.getElementById('totalHarga');
        const totalInput = document.getElementById('totalInput');
        
        if (paketSelect && totalDisplay) {
            console.log("Setting up price calculator");
            
            function hitungTotal() {
                const selectedOption = paketSelect.options[paketSelect.selectedIndex];
                const harga = parseInt(selectedOption.getAttribute('data-harga')) || 0;
                const hari = parseInt(lamaInapInput?.value) || 1;
                const total = harga * hari;
                
                totalDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');
                if (totalInput) totalInput.value = total;
            }
            
            paketSelect.addEventListener('change', hitungTotal);
            if (lamaInapInput) {
                lamaInapInput.addEventListener('input', hitungTotal);
            }
            
            // Hitung awal
            setTimeout(hitungTotal, 100);
        }
        
        // =============================================
        // 3. PILIH KANDANG
        // =============================================
        const btnPilihKandang = document.getElementById('btnPilihKandang');
        const panelKandang = document.getElementById('panelKandang');
        const jenisHewanSelect = document.getElementById('jenisHewanSelect');
        
        if (btnPilihKandang && panelKandang) {
            console.log("Setting up kandang picker");
            console.log("Kandang data available:", window.kandangData);
            
            const kandangLabel = document.getElementById('kandangLabel');
            const idKandangInput = document.getElementById('id_kandang');
            const ukuranHewanSelect = document.getElementById('ukuranHewanSelect');
            
            btnPilihKandang.addEventListener('click', function() {
                const jenis = jenisHewanSelect?.value;
                const ukuran = ukuranHewanSelect?.value;
                
                if (!jenis) {
                    alert('Pilih jenis hewan terlebih dahulu');
                    return;
                }
                
                panelKandang.innerHTML = '';
                panelKandang.classList.remove('d-none');
                
                // Filter kandang
                const kandangTersedia = window.kandangData || [];
                let filteredKandang = kandangTersedia.filter(k => k.status === 'tersedia');
                
                // Logika filter
                if (jenis === 'Kucing') {
                    if (ukuran === 'Sedang') {
                        filteredKandang = filteredKandang.filter(k => k.tipe === 'Sedang' || k.tipe === 'Besar');
                    } else if (ukuran === 'Besar') {
                        filteredKandang = filteredKandang.filter(k => k.tipe === 'Besar');
                    }
                } 
                else if (jenis === 'Anjing') {
                    if (ukuran === 'Kecil') {
                        filteredKandang = filteredKandang.filter(k => k.tipe === 'Sedang');
                    } else if (ukuran === 'Sedang') {
                        filteredKandang = filteredKandang.filter(k => k.tipe === 'Sedang' || k.tipe === 'Besar');
                    } else if (ukuran === 'Besar') {
                        filteredKandang = filteredKandang.filter(k => k.tipe === 'Besar');
                    } else {
                        filteredKandang = filteredKandang.filter(k => k.tipe === 'Sedang' || k.tipe === 'Besar');
                    }
                }
                
                // Tampilkan hasil
                if (filteredKandang.length === 0) {
                    panelKandang.innerHTML = `
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-inbox display-6 opacity-50"></i>
                            <p class="mt-2 mb-0">Tidak ada kandang tersedia</p>
                            <small>Untuk ${jenis} ${ukuran ? 'ukuran ' + ukuran : ''}</small>
                        </div>
                    `;
                    return;
                }
                
                filteredKandang.forEach(kandang => {
                    const item = document.createElement('div');
                    item.className = 'p-2 border-bottom hover-bg-light';
                    item.style.cursor = 'pointer';
                    item.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">${kandang.kode_kandang}</span>
                                <small class="text-muted ms-2">${kandang.tipe}</small>
                            </div>
                            <span class="badge bg-success">Tersedia</span>
                        </div>
                    `;
                    
                    item.addEventListener('click', function() {
                        if (kandangLabel) {
                            kandangLabel.textContent = `${kandang.kode_kandang} - ${kandang.tipe}`;
                        }
                        if (idKandangInput) {
                            idKandangInput.value = kandang.id;
                        }
                        panelKandang.classList.add('d-none');
                        
                        if (btnPilihKandang) {
                            btnPilihKandang.classList.remove('btn-outline-secondary');
                            btnPilihKandang.classList.add('btn-outline-success');
                        }
                        
                        console.log("Kandang selected:", kandang);
                    });
                    
                    panelKandang.appendChild(item);
                });
            });
            
            // Reset saat jenis/ukuran berubah
            if (jenisHewanSelect) {
                jenisHewanSelect.addEventListener('change', function() {
                    if (idKandangInput) idKandangInput.value = '';
                    if (kandangLabel) kandangLabel.textContent = 'Pilih kandang yang tersedia';
                    if (btnPilihKandang) {
                        btnPilihKandang.classList.remove('btn-outline-success');
                        btnPilihKandang.classList.add('btn-outline-secondary');
                    }
                });
            }
            
            if (ukuranHewanSelect) {
                ukuranHewanSelect.addEventListener('change', function() {
                    if (idKandangInput) idKandangInput.value = '';
                    if (kandangLabel) kandangLabel.textContent = 'Pilih kandang yang tersedia';
                    if (btnPilihKandang) {
                        btnPilihKandang.classList.remove('btn-outline-success');
                        btnPilihKandang.classList.add('btn-outline-secondary');
                    }
                });
            }
            
            // Tutup panel saat klik di luar
            document.addEventListener('click', function(e) {
                if (panelKandang && !panelKandang.contains(e.target) && 
                    e.target !== btnPilihKandang && 
                    !btnPilihKandang.contains(e.target)) {
                    panelKandang.classList.add('d-none');
                }
            });
        }
        
        // =============================================
        // 4. VALIDASI FORM
        // =============================================
        const formPendaftaran = document.getElementById('formPendaftaran');
        
        if (formPendaftaran) {
            console.log("Setting up form validation");
            
            formPendaftaran.addEventListener('submit', function(e) {
                console.log("Form submission attempted");
                
                // Validasi 1: Kandang
                const idKandang = document.getElementById('id_kandang');
                if (!idKandang || !idKandang.value) {
                    e.preventDefault();
                    alert('⚠️ Silakan pilih kandang terlebih dahulu');
                    if (btnPilihKandang) {
                        btnPilihKandang.focus();
                    }
                    return false;
                }
                
                // Validasi 2: Paket
                const paketSelect = document.getElementById('paketSelect');
                if (!paketSelect || !paketSelect.value) {
                    e.preventDefault();
                    alert('⚠️ Silakan pilih paket layanan');
                    paketSelect.focus();
                    return false;
                }
                
                // Validasi 3: Pelanggan baru
                const selectPelanggan = document.getElementById('selectPelanggan');
                if (selectPelanggan && selectPelanggan.value === 'new') {
                    const namaPelangganBaru = document.querySelector('input[name="nama_pelanggan_baru"]');
                    if (namaPelangganBaru && !namaPelangganBaru.value.trim()) {
                        e.preventDefault();
                        alert('⚠️ Nama pemilik baru harus diisi');
                        namaPelangganBaru.focus();
                        return false;
                    }
                }
                
                console.log("Form validation passed, submitting...");
                return true;
            });
        }
        
        // =============================================
        // 5. FUNGSI CHECKOUT
        // =============================================
        window.prosesCheckout = function(id_transaksi) {
            console.log("Checkout called for ID:", id_transaksi);
            if (confirm('Apakah Anda yakin ingin melakukan check-out hewan ini?')) {
                window.location.href = 'index.php?action=checkoutTransaksi&id=' + id_transaksi;
            }
        };
        
        // =============================================
        // 6. PENCARIAN CHECKOUT
        // =============================================
        const btnCariCheckout = document.getElementById('btnCariCheckout');
        const searchInput = document.getElementById('searchCheckout');
        
        if (btnCariCheckout && searchInput) {
            console.log("Setting up checkout search");
            
            btnCariCheckout.addEventListener('click', function() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const filterKandang = document.getElementById('filterKandang');
                const filterValue = filterKandang ? filterKandang.value : '';
                
                const rows = document.querySelectorAll('.table tbody tr');
                let foundAny = false;
                
                rows.forEach(row => {
                    if (row.cells.length < 8) return;
                    
                    const pemilik = row.cells[1]?.textContent?.toLowerCase() || '';
                    const hewan = row.cells[2]?.textContent?.toLowerCase() || '';
                    const kandang = row.cells[3]?.textContent || '';
                    
                    const matchesSearch = !searchTerm || 
                        pemilik.includes(searchTerm) || 
                        hewan.includes(searchTerm);
                    const matchesFilter = !filterValue || 
                        kandang.includes(filterValue);
                    
                    if (matchesSearch && matchesFilter) {
                        row.style.display = '';
                        foundAny = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Pesan jika tidak ada hasil
                const existingMessage = document.querySelector('.no-results-row');
                if (existingMessage) existingMessage.remove();
                
                if (!foundAny && rows.length > 0) {
                    const tbody = document.querySelector('.table tbody');
                    const messageRow = document.createElement('tr');
                    messageRow.className = 'no-results-row';
                    messageRow.innerHTML = `
                        <td colspan="8" class="text-center py-4">
                            <i class="bi bi-search display-6 text-muted opacity-50"></i>
                            <p class="mt-2 mb-0 text-muted">Tidak ditemukan hasil pencarian</p>
                        </td>
                    `;
                    tbody.appendChild(messageRow);
                }
            });
        }
        
        console.log("Transaksi handler initialized successfully!");
    }
    
    // =============================================
    // LOAD EVENT HANDLERS
    // =============================================
    
    // Metode 1: DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTransaksiHandler);
    } else {
        // DOM sudah siap, langsung jalankan
        initTransaksiHandler();
    }
    
    // Metode 2: window.onload sebagai fallback
    window.addEventListener('load', function() {
        console.log("Window loaded, re-checking handlers");
        // Cek lagi untuk memastikan
        if (!document.getElementById('formPendaftaran')) {
            console.log("Form not found, might be on checkout tab");
        }
    });
    
})();