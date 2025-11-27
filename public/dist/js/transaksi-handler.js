// File: public/dist/js/transaksi-handler.js
// --- KODE JAVASCRIPT DIMULAI ---

// Asumsi: Variabel global PHP_DATA.kandangTersedia sudah didefinisikan di views/transaksi.php

document.addEventListener('DOMContentLoaded', function() {
    console.log("=== SCRIPT TRANSAKSI DIMULAI (External) ===");

    // Akses variabel global yang didefinisikan di PHP
    const kandangTersedia = (typeof PHP_DATA !== 'undefined' && PHP_DATA.kandangTersedia) ? PHP_DATA.kandangTersedia : [];
    // const hewanMenginap = (typeof PHP_DATA !== 'undefined' && PHP_DATA.hewanMenginap) ? PHP_DATA.hewanMenginap : [];
    
    // =============================================
    // ELEMEN UTAMA (Pastikan ID-nya sesuai)
    // =============================================
    const selectPelanggan = document.getElementById('selectPelanggan');
    const noHpInput = document.getElementById('p_hp');
    const alamatInput = document.getElementById('p_alamat');
    const newCustomerFields = document.getElementById('newCustomerFields');
    const namaBaruInput = document.querySelector('[name="nama_pelanggan_baru"]');
    
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

    // =============================================
    // Fungsi Utility
    // =============================================
    function formatRupiah(angka) {
        return 'Rp ' + (angka || 0).toLocaleString('id-ID');
    }
    
    function validateForm() {
        const formPendaftaran = document.getElementById('formPendaftaran');
        // ... (Logika Validasi yang sudah ada) ...
        
        // 1. Validasi Kandang
        if (!idKandangInput || !idKandangInput.value) {
            alert('Harap pilih kandang terlebih dahulu!');
            return false;
        }
        
        // 2. Validasi HTML5 default
        if (!formPendaftaran.checkValidity()) {
             return false;
        }

        return true;
    }


    // =============================================
    // 1. AUTO-FILL & LOGIKA PELANGGAN
    // =============================================
    if (selectPelanggan && newCustomerFields && namaBaruInput) {
        
        function toggleNewCustomerFields(isNew) {
            if (isNew) {
                newCustomerFields.style.display = 'block';
                namaBaruInput.required = true;
                namaBaruInput.disabled = false;
                
                noHpInput.value = '';
                alamatInput.value = '';

            } else {
                newCustomerFields.style.display = 'none';
                namaBaruInput.required = false;
                namaBaruInput.disabled = true;
                namaBaruInput.value = ''; 
            }
            noHpInput.required = true;
            alamatInput.required = true;
        }

        selectPelanggan.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption.value === 'new') {
                toggleNewCustomerFields(true);
                
            } else if (selectedOption.value) {
                toggleNewCustomerFields(false);

                noHpInput.value = selectedOption.dataset.hp || '';
                alamatInput.value = selectedOption.dataset.alamat || '';
                
            } else {
                toggleNewCustomerFields(false);
                noHpInput.value = '';
                alamatInput.value = '';
            }
        });
        
        toggleNewCustomerFields(selectPelanggan.value === 'new');
    }


    // =============================================
    // 2. KALKULASI TOTAL HARGA
    // =============================================
    function hitungTotal() {
        let total = 0;
        let hargaPaket = 0;
        let lamaInap = 1;
        let namaPaket = '';
        
        lamaInap = parseInt(lamaInapInput ? lamaInapInput.value : 1) || 1;

        if (paketSelect && paketSelect.value) {
            const selectedOption = paketSelect.options[paketSelect.selectedIndex];
            hargaPaket = parseInt(selectedOption.getAttribute('data-harga')) || 0;
            namaPaket = selectedOption.getAttribute('data-nama') || '';
            
            total = hargaPaket * lamaInap;
            
            if (paketInfo) {
                paketInfo.innerHTML = `
                    <strong>${namaPaket}</strong><br>
                    <small>Harga: ${formatRupiah(hargaPaket)} / hari</small>
                `;
            }
        } else {
            if (paketInfo) {
                paketInfo.textContent = 'Pilih paket untuk melihat detail';
            }
        }

        if (totalHargaElement) {
            totalHargaElement.textContent = formatRupiah(total);
        }
        if (totalInput) {
            totalInput.value = total;
        }
        if (detailPerhitungan) {
            if (hargaPaket > 0) {
                detailPerhitungan.textContent = `${formatRupiah(hargaPaket)} × ${lamaInap} hari`;
            } else {
                detailPerhitungan.textContent = '-';
            }
        }
    }

    if (paketSelect) {
        paketSelect.addEventListener('change', hitungTotal);
    }
    if (lamaInapInput) {
        lamaInapInput.addEventListener('input', hitungTotal);
    }
    hitungTotal();


    // =============================================
    // 3. PEMILIHAN KANDANG
    // =============================================
    if (btnPilihKandang) {
        
        function filterKandang() {
            const jenisHewan = jenisHewanSelect.value;
            const ukuranHewan = ukuranHewanSelect ? ukuranHewanSelect.value : '';
            
            if (!jenisHewan) {
                alert('Pilih jenis hewan terlebih dahulu');
                panelKandang.classList.add('d-none');
                return;
            }

            let tipeKandangYangCocok = ['Kecil', 'Sedang', 'Besar'];
            
            if (jenisHewan === 'Anjing') {
                tipeKandangYangCocok = ['Sedang', 'Besar'];
            } 
            if (ukuranHewan === 'Besar') {
                tipeKandangYangCocok = ['Besar'];
            }
            
            panelKandang.innerHTML = `
                <div class="text-center py-2">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    <span class="text-muted">Memuat kandang tersedia...</span>
                </div>
            `;
            panelKandang.classList.remove('d-none');
            
            setTimeout(() => {
                panelKandang.innerHTML = '';
                let kandangDitemukan = false;
                
                kandangTersedia.forEach(kandang => {
                    // Koreksi: Menggunakan kode kandang.status = 'tersedia'
                    if (kandang.status === 'tersedia' && tipeKandangYangCocok.includes(kandang.tipe)) { 
                        kandangDitemukan = true;
                        
                        const kandangItem = document.createElement('div');
                        kandangItem.className = 'p-2 border-bottom';
                        kandangItem.style.cursor = 'pointer';
                        kandangItem.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-semibold">${kandang.kode_kandang}</span>
                                    <small class="text-muted ms-2">${kandang.tipe}</small>
                                </div>
                                <span class="badge bg-success">Tersedia</span>
                            </div>
                        `;
                        
                        kandangItem.addEventListener('click', function() {
                            kandangLabel.textContent = `${kandang.kode_kandang} - ${kandang.tipe}`;
                            kandangLabel.title = kandang.kode_kandang; // Tambahkan title untuk visual
                            idKandangInput.value = kandang.id_kandang; // <--- PASTIKAN KEY DATABASE BENAR
                            panelKandang.classList.add('d-none');
                            kandangInfo.innerHTML = `<span class="text-success"><i class="bi bi-check-circle"></i> Kandang ${kandang.kode_kandang} dipilih</span>`;
                            validateKandang();
                        });
                        
                        panelKandang.appendChild(kandangItem);
                    }
                });
                
                if (!kandangDitemukan) {
                    panelKandang.innerHTML = `
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-inbox display-6 opacity-50"></i>
                            <p class="mt-2 mb-0">Tidak ada kandang tersedia</p>
                            <small>Untuk ${jenisHewan} ${ukuranHewan ? 'ukuran ' + ukuranHewan : ''}</small>
                        </div>
                    `;
                }
            }, 300);
        }

        btnPilihKandang.addEventListener('click', filterKandang);

        function validateKandang() {
            if (idKandangInput.value) {
                btnPilihKandang.classList.remove('btn-outline-secondary');
                btnPilihKandang.classList.add('btn-outline-success');
            } else {
                btnPilihKandang.classList.remove('btn-outline-success');
                btnPilihKandang.classList.add('btn-outline-secondary');
            }
        }

        function resetKandangPilihan() {
            idKandangInput.value = '';
            kandangLabel.textContent = 'Pilih kandang yang tersedia';
            kandangInfo.innerHTML = 'Pilih kandang yang sesuai dengan jenis dan ukuran hewan';
            panelKandang.classList.add('d-none');
            validateKandang();
        }

        if (jenisHewanSelect) {
            jenisHewanSelect.addEventListener('change', resetKandangPilihan);
        }
        if (ukuranHewanSelect) {
            ukuranHewanSelect.addEventListener('change', resetKandangPilihan);
        }
        
        validateKandang();
    }

    // =============================================
    // 4. FORM SUBMIT HANDLER & VALIDASI
    // =============================================
    const formPendaftaran = document.getElementById('formPendaftaran');
    if (formPendaftaran) {
        formPendaftaran.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
            } else {
                console.log("Form valid, submitting to Controller...");
            }
        });
    }

    // =============================================
    // 5. FUNGSI CHECKOUT & BUKTI PEMBAYARAN (Globalized)
    // =============================================
    window.prosesCheckout = function(idTransaksi) {
        // ... (Logika Checkout menggunakan Fetch API ke index.php?action=read&id=...)
        
        fetch(`index.php?page=transaksi&action=read&id=${idTransaksi}`)
            .then(response => response.json())
            .then(transaksiData => {
                if (transaksiData.error || !transaksiData.id_transaksi) {
                     alert('Gagal memuat data transaksi. Pesan: ' + (transaksiData.error || 'Data tidak ditemukan.'));
                     return;
                }
                
                // Isi modal checkout
                const checkoutContent = document.getElementById('checkoutContent');
                checkoutContent.innerHTML = `
                     <form id="checkoutForm">
                         <input type="hidden" name="id" value="${transaksiData.id_transaksi}">
                         <input type="hidden" name="durasi_hari" value="${transaksiData.durasi}">
                         <input type="hidden" name="total_biaya" value="${transaksiData.total_biaya}">
                         <div class="row">
                            <div class="col-md-6"><h6>Detail Transaksi</h6>...</div>
                             <div class="col-md-6"><h6>Rincian Pembayaran</h6>...</div>
                         </div>
                     </form>
                `;
                
                const modal = new bootstrap.Modal(document.getElementById('modalCheckout'));
                modal.show();
                
                // Setup confirm button untuk mengirim data checkout via POST
                document.getElementById('btnConfirmCheckout').onclick = function() {
                    const form = document.getElementById('checkoutForm');
                    if (form.checkValidity()) {
                        
                        // Kirim data ke Controller via Fetch API (simulasi POST)
                        const formData = new FormData(form);
                        
                        fetch('index.php?action=checkoutTransaksi', {
                            method: 'POST',
                            body: new URLSearchParams(formData).toString(),
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                alert(result.success + ". Memuat bukti pembayaran.");
                                modal.hide();
                                // Arahkan ke Controller untuk menampilkan struk
                                window.location.href = `index.php?page=transaksi&action=cetakBukti&id=${transaksiData.id_transaksi}`;

                            } else {
                                alert('Gagal Check-out: ' + (result.error || 'Terjadi kesalahan server.'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan koneksi saat Check-out.');
                        });
                        
                    } else {
                        alert('Harap lengkapi semua field checkout!');
                    }
                };

            })
            .catch(error => {
                alert('Gagal mengambil data dari server: ' + error);
                console.error('Fetch Error:', error);
            });
    };

    window.tampilkanBuktiBayar = function(transaksiData) {
        // ... (Logika Tampilkan Bukti Bayar) ...
    };
    window.cetakBuktiBayar = function() {
        // ... (Logika Cetak Bukti Bayar) ...
    };

});