// transaksi-simple.js - SIMPLE FIXED VERSION

console.log("=== TRANSAKSI JS LOADED ===");

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // 1. AUTO-FILL PELANGGAN - WORKING VERSION
    // ============================================
    const selectPelanggan = document.getElementById('selectPelanggan');
    const noHpInput = document.getElementById('p_hp');
    const alamatInput = document.getElementById('p_alamat');
    const newCustomerFields = document.getElementById('newCustomerFields');
    
    if (selectPelanggan && noHpInput && alamatInput) {
        console.log("Auto-fill elements found");
        
        // Toggle new customer form
        function toggleNewCustomer(show) {
            if (newCustomerFields) {
                newCustomerFields.style.display = show ? 'block' : 'none';
            }
        }
        
        // Handle dropdown change
        selectPelanggan.addEventListener('change', function() {
            console.log("Pelanggan selected:", this.value);
            
            if (this.value === 'new') {
                // New customer
                toggleNewCustomer(true);
                noHpInput.value = '';
                alamatInput.value = '';
            } else if (this.value) {
                // Existing customer
                toggleNewCustomer(false);
                
                // Get data from selected option
                const selectedOption = this.options[this.selectedIndex];
                const hp = selectedOption.getAttribute('data-hp') || '';
                const alamat = selectedOption.getAttribute('data-alamat') || '';
                
                console.log("Filling data:", { hp, alamat });
                noHpInput.value = hp;
                alamatInput.value = alamat;
            }
        });
        
        // Initialize
        if (selectPelanggan.value === 'new') {
            toggleNewCustomer(true);
        }
    }
    
    // ============================================
    // 2. KALKULASI HARGA LAYANAN - WORKING
    // ============================================
    const paketSelect = document.getElementById('paketSelect');
    const lamaInapInput = document.getElementById('lamaInap');
    const totalHargaElement = document.getElementById('totalHarga');
    const totalInput = document.getElementById('totalInput');
    const detailPerhitungan = document.getElementById('detailPerhitungan');
    
    function hitungTotalHarga() {
        if (!paketSelect || !paketSelect.value) return;
        
        // Get selected package
        const selectedOption = paketSelect.options[paketSelect.selectedIndex];
        const hargaPerHari = parseInt(selectedOption.getAttribute('data-harga')) || 0;
        const namaPaket = selectedOption.getAttribute('data-nama') || '';
        const lamaInap = parseInt(lamaInapInput.value) || 1;
        
        // Calculate
        const total = hargaPerHari * lamaInap;
        
        // Update display
        if (totalHargaElement) {
            totalHargaElement.textContent = 'Rp ' + total.toLocaleString('id-ID');
        }
        if (totalInput) {
            totalInput.value = total;
        }
        if (detailPerhitungan) {
            detailPerhitungan.textContent = `Rp ${hargaPerHari.toLocaleString('id-ID')} × ${lamaInap} hari`;
        }
        
        console.log("Harga calculated:", { hargaPerHari, lamaInap, total });
    }
    
    // Attach events
    if (paketSelect) {
        paketSelect.addEventListener('change', hitungTotalHarga);
    }
    if (lamaInapInput) {
        lamaInapInput.addEventListener('input', hitungTotalHarga);
    }
    
    // Initial calculation
    setTimeout(hitungTotalHarga, 100);
    
    // ============================================
    // 3. PILIH KANDANG - SIMPLE VERSION
    // ============================================
    const btnPilihKandang = document.getElementById('btnPilihKandang');
    const panelKandang = document.getElementById('panelKandang');
    const jenisHewanSelect = document.getElementById('jenisHewanSelect');
    const ukuranHewanSelect = document.getElementById('ukuranHewanSelect');
    const idKandangInput = document.getElementById('id_kandang');
    const kandangLabel = document.getElementById('kandangLabel');
    
    if (btnPilihKandang) {
        // Kandang data dari PHP (harus ada di window.PHP_DATA)
        const kandangData = window.PHP_DATA?.kandangTersedia || [];
        console.log("Kandang data:", kandangData);
        
        btnPilihKandang.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Check if jenis hewan selected
            if (!jenisHewanSelect || !jenisHewanSelect.value) {
                alert('Pilih jenis hewan terlebih dahulu');
                return;
            }
            
            const jenis = jenisHewanSelect.value;
            const ukuran = ukuranHewanSelect ? ukuranHewanSelect.value : '';
            
            // Filter kandang berdasarkan aturan
            const kandangFiltered = kandangData.filter(k => {
                if (k.status !== 'tersedia') return false;
                
                if (jenis === 'Kucing') {
                    if (ukuran === 'Kecil') return ['Kecil', 'Sedang', 'Besar'].includes(k.tipe);
                    if (ukuran === 'Sedang') return ['Sedang', 'Besar'].includes(k.tipe);
                    if (ukuran === 'Besar') return ['Besar'].includes(k.tipe);
                    return ['Kecil', 'Sedang', 'Besar'].includes(k.tipe);
                } else if (jenis === 'Anjing') {
                    if (ukuran === 'Kecil') return ['Sedang'].includes(k.tipe);
                    if (ukuran === 'Sedang') return ['Sedang', 'Besar'].includes(k.tipe);
                    if (ukuran === 'Besar') return ['Besar'].includes(k.tipe);
                    return ['Sedang', 'Besar'].includes(k.tipe);
                }
                return false;
            });
            
            console.log("Kandang filtered:", kandangFiltered);
            
            // Show panel
            panelKandang.innerHTML = '';
            panelKandang.classList.remove('d-none');
            
            if (kandangFiltered.length === 0) {
                panelKandang.innerHTML = `
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-inbox"></i>
                        <p class="mt-2 mb-0">Tidak ada kandang tersedia</p>
                    </div>
                `;
                return;
            }
            
            // Add kandang options
            kandangFiltered.forEach(k => {
                const div = document.createElement('div');
                div.className = 'p-2 border-bottom hover-bg';
                div.style.cursor = 'pointer';
                div.innerHTML = `
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>${k.kode_kandang}</strong>
                            <small class="text-muted ms-2">${k.tipe}</small>
                        </div>
                        <span class="badge bg-success">Tersedia</span>
                    </div>
                `;
                
                div.addEventListener('click', function() {
                    // Select this kandang
                    kandangLabel.textContent = `${k.kode_kandang} (${k.tipe})`;
                    idKandangInput.value = k.id;
                    panelKandang.classList.add('d-none');
                    
                    // Update button style
                    btnPilihKandang.classList.remove('btn-outline-secondary');
                    btnPilihKandang.classList.add('btn-outline-success');
                    
                    console.log("Kandang selected:", k);
                });
                
                panelKandang.appendChild(div);
            });
        });
    }
    
    // ============================================
    // 4. FORM VALIDATION
    // ============================================
    const formPendaftaran = document.getElementById('formPendaftaran');
    if (formPendaftaran) {
        formPendaftaran.addEventListener('submit', function(e) {
            // Validate kandang
            if (!idKandangInput || !idKandangInput.value) {
                e.preventDefault();
                alert('Silakan pilih kandang terlebih dahulu');
                return;
            }
            
            console.log("Form submitting...");
        });
    }
    
    console.log("Transaksi JS initialized successfully");

    // =============================================
    // 5. CHECKOUT/PENGEMBALIAN FUNCTIONALITY
    // =============================================
    // Fungsi global untuk proses checkout
    window.prosesCheckout = function(id_transaksi) {
        if (confirm(`Apakah Anda yakin ingin melakukan check-out untuk transaksi ini?`)) {
            window.location.href = `index.php?action=checkoutTransaksi&id=${id_transaksi}`;
        }
    };
    
    // Search functionality for checkout tab
    const searchCheckout = document.getElementById('searchCheckout');
    const filterKandang = document.getElementById('filterKandang');
    const btnCariCheckout = document.getElementById('btnCariCheckout');
    
    if (btnCariCheckout) {
        btnCariCheckout.addEventListener('click', function() {
            const searchTerm = searchCheckout.value.toLowerCase();
            const kandangFilter = filterKandang.value;
            
            const rows = document.querySelectorAll('.table-responsive tbody tr');
            let found = false;
            
            rows.forEach(row => {
                const pemilik = row.cells[1].textContent.toLowerCase();
                const hewan = row.cells[2].textContent.toLowerCase();
                const kandang = row.cells[3].textContent;
                
                const matchSearch = pemilik.includes(searchTerm) || hewan.includes(searchTerm);
                const matchKandang = !kandangFilter || kandang.includes(kandangFilter);
                
                if (matchSearch && matchKandang) {
                    row.style.display = '';
                    found = true;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show no results message
            const noResults = document.querySelector('.no-results-message');
            if (!found && !noResults) {
                const tbody = document.querySelector('.table-responsive tbody');
                const tr = document.createElement('tr');
                tr.className = 'no-results-message';
                tr.innerHTML = `
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-search display-6 opacity-50"></i>
                        <p class="mt-3 mb-0">Tidak ditemukan hasil pencarian</p>
                    </td>
                `;
                tbody.appendChild(tr);
            } else if (found && noResults) {
                noResults.remove();
            }
        });
    }
    
    console.log("Transaksi handler initialized successfully");
});