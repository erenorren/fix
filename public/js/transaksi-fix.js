// transaksi-fix.js - SIMPLE WORKING VERSION

console.log("=== TRANSAKSI FIX JS LOADED ===");

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // 1. CEK DATA DARI PHP
    // ============================================
    console.log("PHP Data available:", window.PHP_DATA);
    
    // ============================================
    // 2. AUTO-FILL PELANGGAN (FIXED)
    // ============================================
    const selectPelanggan = document.getElementById('selectPelanggan');
    const noHpInput = document.getElementById('p_hp');
    const alamatInput = document.getElementById('p_alamat');
    
    if (selectPelanggan && noHpInput && alamatInput) {
        console.log("Found pelanggan elements");
        
        selectPelanggan.addEventListener('change', function() {
            console.log("Pelanggan changed to:", this.value);
            
            if (this.value === 'new') {
                // Show new customer field
                const newFields = document.getElementById('newCustomerFields');
                if (newFields) newFields.style.display = 'block';
                
                // Clear existing data
                noHpInput.value = '';
                alamatInput.value = '';
            } else if (this.value) {
                // Hide new customer field
                const newFields = document.getElementById('newCustomerFields');
                if (newFields) newFields.style.display = 'none';
                
                // Get selected option data
                const selectedOption = this.options[this.selectedIndex];
                const hp = selectedOption.getAttribute('data-hp') || '';
                const alamat = selectedOption.getAttribute('data-alamat') || '';
                
                console.log("Auto-fill with:", { hp, alamat });
                
                // Fill the inputs
                noHpInput.value = hp;
                alamatInput.value = alamat;
            }
        });
        
        // Initialize
        if (selectPelanggan.value === 'new') {
            const newFields = document.getElementById('newCustomerFields');
            if (newFields) newFields.style.display = 'block';
        }
    }
    
    // ============================================
    // 3. KALKULASI HARGA PAKET (FIXED)
    // ============================================
    const paketSelect = document.getElementById('paketSelect');
    const lamaInapInput = document.getElementById('lamaInap');
    const totalHargaElement = document.getElementById('totalHarga');
    const totalInput = document.getElementById('totalInput');
    
    function hitungTotal() {
        if (!paketSelect || !paketSelect.value) {
            console.log("No paket selected");
            return;
        }
        
        // Get selected paket
        const selectedOption = paketSelect.options[paketSelect.selectedIndex];
        const harga = parseInt(selectedOption.getAttribute('data-harga')) || 0;
        const nama = selectedOption.getAttribute('data-nama') || '';
        const lama = parseInt(lamaInapInput.value) || 1;
        const total = harga * lama;
        
        console.log("Calculating:", { harga, nama, lama, total });
        
        // Update display
        if (totalHargaElement) {
            totalHargaElement.textContent = 'Rp ' + total.toLocaleString('id-ID');
        }
        if (totalInput) {
            totalInput.value = total;
        }
        
        // Update paket info
        const paketInfo = document.getElementById('paketInfo');
        if (paketInfo) {
            paketInfo.innerHTML = `<strong>${nama}</strong><br><small>Harga: Rp ${harga.toLocaleString('id-ID')} / hari</small>`;
        }
    }
    
    // Attach events
    if (paketSelect) {
        paketSelect.addEventListener('change', hitungTotal);
        console.log("Paket select event attached");
    }
    
    if (lamaInapInput) {
        lamaInapInput.addEventListener('input', hitungTotal);
        console.log("Lama inap event attached");
    }
    
    // Initial calculation
    setTimeout(hitungTotal, 500);
    
    // ============================================
    // 4. PILIH KANDANG (SIMPLE VERSION)
    // ============================================
    const btnPilihKandang = document.getElementById('btnPilihKandang');
    const panelKandang = document.getElementById('panelKandang');
    const jenisHewanSelect = document.getElementById('jenisHewanSelect');
    const ukuranHewanSelect = document.getElementById('ukuranHewanSelect');
    
    if (btnPilihKandang) {
        btnPilihKandang.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (!jenisHewanSelect || !jenisHewanSelect.value) {
                alert('Pilih jenis hewan terlebih dahulu');
                return;
            }
            
            // Get kandang data
            const kandangData = window.PHP_DATA?.kandangTersedia || [];
            console.log("Available kandang:", kandangData);
            
            const jenis = jenisHewanSelect.value;
            const ukuran = ukuranHewanSelect ? ukuranHewanSelect.value : '';
            
            // Filter kandang
            const filtered = kandangData.filter(k => {
                if (k.status !== 'tersedia') return false;
                
                if (jenis === 'Kucing') {
                    if (ukuran === 'Kecil') return true; // Semua kandang
                    if (ukuran === 'Sedang') return k.tipe !== 'Kecil';
                    if (ukuran === 'Besar') return k.tipe === 'Besar';
                    return true;
                } else if (jenis === 'Anjing') {
                    if (ukuran === 'Kecil') return k.tipe === 'Sedang';
                    if (ukuran === 'Sedang') return k.tipe !== 'Kecil';
                    if (ukuran === 'Besar') return k.tipe === 'Besar';
                    return k.tipe !== 'Kecil';
                }
                return false;
            });
            
            console.log("Filtered kandang:", filtered);
            
            // Display kandang
            panelKandang.innerHTML = '';
            panelKandang.classList.remove('d-none');
            
            if (filtered.length === 0) {
                panelKandang.innerHTML = `
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-inbox"></i>
                        <p>Tidak ada kandang tersedia</p>
                    </div>
                `;
                return;
            }
            
            filtered.forEach(k => {
                const div = document.createElement('div');
                div.className = 'p-2 border-bottom';
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
                    document.getElementById('kandangLabel').textContent = `${k.kode_kandang} (${k.tipe})`;
                    document.getElementById('id_kandang').value = k.id;
                    panelKandang.classList.add('d-none');
                    
                    // Update button style
                    btnPilihKandang.classList.remove('btn-outline-secondary');
                    btnPilihKandang.classList.add('btn-outline-success');
                });
                
                panelKandang.appendChild(div);
            });
        });
    }
    
    // ============================================
    // 5. FORM VALIDATION
    // ============================================
    const form = document.getElementById('formPendaftaran');
    if (form) {
        form.addEventListener('submit', function(e) {
            const idKandang = document.getElementById('id_kandang').value;
            if (!idKandang) {
                e.preventDefault();
                alert('Silakan pilih kandang terlebih dahulu');
                return;
            }
            
            console.log("Form submitted successfully");
        });
    }
    
    console.log("Transaksi JS initialization complete");
});