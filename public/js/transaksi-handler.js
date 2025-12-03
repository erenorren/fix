// /js/transaksi-handler.js - PERBAIKAN COMPLETE

document.addEventListener('DOMContentLoaded', function() {
    console.log("=== SCRIPT TRANSAKSI DIMULAI ===");

    // =============================================
    // ELEMEN UTAMA
    // =============================================
    
    const paketSelect = document.getElementById('paketSelect');
    const lamaInapInput = document.getElementById('lamaInap');
    const totalHargaElement = document.getElementById('totalHarga');
    const totalInput = document.getElementById('totalInput');
    const detailPerhitungan = document.getElementById('detailPerhitungan');
    const paketInfo = document.getElementById('paketInfo');

    const btnPilihKandang = document.getElementById('btnPilihKandang');
    const panelKandang = document.getElementById('panelKandang');
    const kandangLabel = document.getElementById('kandangLabel');
    const idKandangInput = document.getElementById('id_kandang');
    const kandangInfo = document.getElementById('kandangInfo');
    const jenisHewanSelect = document.getElementById('jenisHewanSelect');
    const ukuranHewanSelect = document.getElementById('ukuranHewanSelect');

    const formPendaftaran = document.getElementById('formPendaftaran');

// DIBAWAH BAGIAN 1. AUTO-FILL DATA PELANGGAN, GANTI DENGAN:

// REPLACE BAGIAN AUTO-FILL PELANGGAN DENGAN INI:

// =============================================
// 1. AUTO-FILL DATA PELANGGAN - SIMPLE VERSION
// =============================================
console.log("=== INIT AUTO-FILL PELANGGAN ===");

const selectPelanggan = document.getElementById('selectPelanggan');
const noHpInput = document.getElementById('p_hp');
const alamatInput = document.getElementById('p_alamat');
const newCustomerFields = document.getElementById('newCustomerFields');
const namaBaruInput = document.querySelector('[name="nama_pelanggan_baru"]');

if (selectPelanggan && noHpInput && alamatInput) {
    console.log("Semua elemen ditemukan!");
    
    // Debug: tampilkan semua options
    console.log("Jumlah options:", selectPelanggan.options.length);
    for (let i = 0; i < selectPelanggan.options.length; i++) {
        const opt = selectPelanggan.options[i];
        console.log(`Option ${i}:`, {
            value: opt.value,
            text: opt.text,
            dataHp: opt.getAttribute('data-hp'),
            dataAlamat: opt.getAttribute('data-alamat')
        });
    }
    
    // Fungsi untuk toggle form pelanggan baru
    function toggleNewCustomerForm(show) {
        console.log("Toggle new customer form:", show);
        
        if (show) {
            // Tampilkan form pelanggan baru
            if (newCustomerFields) newCustomerFields.style.display = 'block';
            if (namaBaruInput) {
                namaBaruInput.required = true;
                namaBaruInput.disabled = false;
            }
            
            // Kosongkan semua field
            if (noHpInput) noHpInput.value = '';
            if (alamatInput) alamatInput.value = '';
            
        } else {
            // Sembunyikan form pelanggan baru
            if (newCustomerFields) newCustomerFields.style.display = 'none';
            if (namaBaruInput) {
                namaBaruInput.required = false;
                namaBaruInput.disabled = true;
                namaBaruInput.value = '';
            }
        }
    }
    
    // Event listener untuk dropdown
    selectPelanggan.addEventListener('change', function() {
        console.log("=== DROPDOWN BERUBAH ===");
        console.log("Nilai dipilih:", this.value);
        console.log("Option dipilih:", this.options[this.selectedIndex]);
        
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value === 'new') {
            console.log("Mode: Tambah pemilik baru");
            toggleNewCustomerForm(true);
            
        } else if (this.value && this.value !== '') {
            console.log("Mode: Pemilih pelanggan existing");
            toggleNewCustomerForm(false);
            
            // Ambil data dari data-attribute
            const hp = selectedOption.getAttribute('data-hp');
            const alamat = selectedOption.getAttribute('data-alamat');
            
            console.log("Data dari option:", { hp, alamat });
            
            // Isi field otomatis
            if (noHpInput) noHpInput.value = hp || '';
            if (alamatInput) alamatInput.value = alamat || '';
            
        } else {
            console.log("Mode: Tidak ada pilihan");
            toggleNewCustomerForm(false);
            if (noHpInput) noHpInput.value = '';
            if (alamatInput) alamatInput.value = '';
        }
    });
    
    // Inisialisasi awal
    console.log("Nilai awal dropdown:", selectPelanggan.value);
    if (selectPelanggan.value === 'new') {
        toggleNewCustomerForm(true);
    } else {
        toggleNewCustomerForm(false);
        // Jika sudah ada pilihan, isi data
        if (selectPelanggan.value) {
            const selectedOption = selectPelanggan.options[selectPelanggan.selectedIndex];
            if (selectedOption && noHpInput && alamatInput) {
                noHpInput.value = selectedOption.getAttribute('data-hp') || '';
                alamatInput.value = selectedOption.getAttribute('data-alamat') || '';
            }
        }
    }
    
} else {
    console.error("Elemen tidak ditemukan!");
    console.log("selectPelanggan:", selectPelanggan);
    console.log("noHpInput:", noHpInput);
    console.log("alamatInput:", alamatInput);
}

// =============================================
// KALKULASI HARGA - PASTI BEKERJA
// =============================================

// Fungsi hitung total
function hitungTotalSekarang() {
    // Ambil elemen
    const paketSelect = document.getElementById('paketSelect');
    const lamaInapInput = document.getElementById('lamaInap');
    const totalDisplay = document.getElementById('totalHarga');
    const totalHidden = document.getElementById('totalInput');
    
    // Jika elemen tidak ada, berhenti
    if (!paketSelect || !totalDisplay) return;
    
    // Ambil harga dari data-attribute
    const selectedOption = paketSelect.options[pakapSelect.selectedIndex];
    const hargaPerHari = parseInt(selectedOption.getAttribute('data-harga')) || 0;
    const lamaInap = parseInt(lamaInapInput.value) || 1;
    
    // Hitung total
    const total = hargaPerHari * lamaInap;
    
    // Update tampilan
    totalDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');
    if (totalHidden) totalHidden.value = total;
}

// Pasang event saat halaman load
document.addEventListener('DOMContentLoaded', function() {
    // Ambil elemen dropdown paket
    const paketSelect = document.getElementById('paketSelect');
    
    // Jika dropdown ada, pasang event listener
    if (paketSelect) {
        // Event saat pilih paket berubah
        paketSelect.addEventListener('change', hitungTotalSekarang);
        
        // Hitung saat pertama kali load
        setTimeout(hitungTotalSekarang, 100);
    }
    
    // Juga pasang event untuk input lama inap
    const lamaInapInput = document.getElementById('lamaInap');
    if (lamaInapInput) {
        lamaInapInput.addEventListener('input', hitungTotalSekarang);
    }
});

    // =============================================
    // 3. PEMILIHAN KANDANG DENGAN FILTER YANG BENAR
    // =============================================
    if (btnPilihKandang && jenisHewanSelect) {
        const kandangData = window.PHP_DATA?.kandangTersedia || [];
        console.log("Data kandang tersedia:", kandangData);
        
        function getTipeKandangYangCocok(jenis, ukuran) {
            console.log("Get tipe kandang untuk:", jenis, ukuran);
            
            if (jenis === 'Kucing') {
                if (ukuran === 'Kecil') {
                    return ['Kecil', 'Sedang', 'Besar'];
                } else if (ukuran === 'Sedang') {
                    return ['Sedang', 'Besar'];
                } else if (ukuran === 'Besar') {
                    return ['Besar'];
                } else {
                    return ['Kecil', 'Sedang', 'Besar']; // default
                }
            } 
            else if (jenis === 'Anjing') {
                if (ukuran === 'Kecil') {
                    return ['Sedang']; // Anjing kecil → kandang sedang saja
                } else if (ukuran === 'Sedang') {
                    return ['Sedang', 'Besar'];
                } else if (ukuran === 'Besar') {
                    return ['Besar'];
                } else {
                    return ['Sedang', 'Besar']; // default
                }
            }
            return [];
        }
        
        function showKandangPanel() {
            if (!jenisHewanSelect.value) {
                alert('Pilih jenis hewan terlebih dahulu');
                return;
            }
            
            const jenis = jenisHewanSelect.value;
            const ukuran = ukuranHewanSelect ? ukuranHewanSelect.value : '';
            const tipeCocok = getTipeKandangYangCocok(jenis, ukuran);
            
            console.log("Filter kandang:", { jenis, ukuran, tipeCocok });
            
            // Clear panel
            panelKandang.innerHTML = '';
            panelKandang.classList.remove('d-none');
            
            // Filter kandang
            const kandangFiltered = kandangData.filter(k => {
                return k.status === 'tersedia' && tipeCocok.includes(k.tipe);
            });
            
            console.log("Kandang tersedia setelah filter:", kandangFiltered);
            
            if (kandangFiltered.length === 0) {
                panelKandang.innerHTML = `
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-inbox display-6 opacity-50"></i>
                        <p class="mt-2 mb-0">Tidak ada kandang tersedia</p>
                        <small>Untuk ${jenis} ${ukuran ? 'ukuran ' + ukuran : ''}</small>
                    </div>
                `;
                return;
            }
            
            // Display kandang
            kandangFiltered.forEach(kandang => {
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
                    selectKandang(kandang);
                });
                
                panelKandang.appendChild(item);
            });
        }
        
        function selectKandang(kandang) {
            kandangLabel.textContent = `${kandang.kode_kandang} - ${kandang.tipe}`;
            idKandangInput.value = kandang.id;
            panelKandang.classList.add('d-none');
            
            if (kandangInfo) {
                kandangInfo.innerHTML = `
                    <span class="text-success">
                        <i class="bi bi-check-circle"></i> Kandang ${kandang.kode_kandang} (${kandang.tipe}) dipilih
                    </span>
                `;
            }
            
            // Update button style
            btnPilihKandang.classList.remove('btn-outline-secondary');
            btnPilihKandang.classList.add('btn-outline-success');
            
            console.log("Kandang dipilih:", kandang);
        }
        
        function resetKandangSelection() {
            idKandangInput.value = '';
            kandangLabel.textContent = 'Pilih kandang yang tersedia';
            panelKandang.classList.add('d-none');
            
            if (kandangInfo) {
                kandangInfo.innerHTML = `
                    Pilih kandang: 
                    <span id="kandangRuleInfo">
                        Kucing kecil: semua kandang | Kucing sedang: sedang & besar | Kucing besar: besar saja | 
                        Anjing kecil: sedang | Anjing sedang: sedang & besar | Anjing besar: besar saja
                    </span>
                `;
            }
            
            // Reset button style
            btnPilihKandang.classList.remove('btn-outline-success');
            btnPilihKandang.classList.add('btn-outline-secondary');
        }
        
        // Event Listeners
        btnPilihKandang.addEventListener('click', showKandangPanel);
        
        if (jenisHewanSelect) {
            jenisHewanSelect.addEventListener('change', resetKandangSelection);
        }
        
        if (ukuranHewanSelect) {
            ukuranHewanSelect.addEventListener('change', resetKandangSelection);
        }
        
        // Close panel when clicking outside
        document.addEventListener('click', function(e) {
            if (!panelKandang.contains(e.target) && 
                e.target !== btnPilihKandang && 
                !btnPilihKandang.contains(e.target)) {
                panelKandang.classList.add('d-none');
            }
        });
    }

    // =============================================
    // 4. FORM VALIDATION
    // =============================================
    if (formPendaftaran) {
        formPendaftaran.addEventListener('submit', function(e) {
            // Basic validation
            if (!idKandangInput || !idKandangInput.value) {
                e.preventDefault();
                alert('Silakan pilih kandang terlebih dahulu');
                btnPilihKandang.focus();
                return;
            }
            
            if (!paketSelect || !paketSelect.value) {
                e.preventDefault();
                alert('Silakan pilih paket layanan');
                paketSelect.focus();
                return;
            }
            
            // Validate total
            const total = parseInt(totalInput.value) || 0;
            if (total <= 0) {
                e.preventDefault();
                alert('Total biaya tidak valid. Periksa paket dan lama inap.');
                return;
            }
            
            console.log("Form submitted dengan data:", {
                kandang: idKandangInput.value,
                paket: paketSelect.value,
                total: totalInput.value
            });
        });
    }

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