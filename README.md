# SISTEM PENITIPAN HEWAN
Capstone Project: Mata Kuliah Praktikum Pemrograman Berbasis Objek. <br>
SIP Hewan menyediakan pencatatan transaksi penitipan dan pengambilan hewan secara otomatis, perhitungan biaya berdasarkan layanan, pencarian data cepat, penyimpanan data rapi dan terintegrasi, meminimalkan human error, mempercepat proses kasir, serta menghasilkan bukti transaksi yang akurat dan mudah dicetak.

- [Link Laporan](https://docs.google.com/document/d/1AOxgPGOtluXbt7jCimmJJigYgbgEY-6fWdtiOjgleZ8/edit?usp=sharing)
- [Commit at Presentation Day](https://github.com/erenorren/fix/commit/70ab83959765aa2b5f87dbe5166aea154c8a87a7)

## Team
- H1101241036 - Regisha Sheren
- H1101241006 - Febrianti Khumairoh
- H1101241034 - Adella Rheina Sweeta
- H1101241044 - Aisyah
- H1101241050 - Kharizma Rizkiah

## Cara Pakai Di Local
- run `composer install`
- buat file `.env`, ambil dari `.env.example`
- Run server Database, run Query (`db.sql` untuk database MYSQL)
- Run server `php -S localhost:8080 -t public`

## Cara deploy ke vercel
- run `vercel --prod` (bisa koneksi ke repo github untuk auto run)
- setup database: jika pakai `supabase`, run `db-pg.sql` di **SQL Editor** supabase, sesuaikan konfigurasi `DB_*` seperti pada contoh di `.env.prod.example`, dan paste-kan ke dalam **vercel environment variable**