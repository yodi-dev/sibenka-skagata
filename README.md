<div align="center">

# 🛠️ SIBENKA (Sistem Inventaris Bengkel Skagata)

**Sistem Informasi Pengelolaan Inventaris, Sirkulasi Alat & Bahan Praktik, serta Pengadaan (RAB) Berbasis Web di Lingkungan Bengkel Kejuruan SMK Negeri 3 Yogyakarta**

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white)](https://alpinejs.dev)
[![Vite](https://img.shields.io/badge/Vite-7.x-646CFF?style=for-the-badge&logo=vite&logoColor=white)](https://vitejs.dev)

</div>

---

## 📌 1. Latar Belakang & Tujuan

SMK Negeri 3 Yogyakarta (Skagata) memiliki beragam bengkel kejuruan (TKJ, TAV, TITL, TKR, TBSM, DKV, Tata Boga, dll.) dengan sirkulasi peminjaman alat praktik dan konsumsi bahan yang padat setiap hari.

**SIBENKA** dirancang untuk:

1. **Mencegah Kehilangan & Kerusakan Alat:** Mencatat sirkulasi peminjaman alat secara akuntabel dan menyediakan alur cek fisik kondisi barang saat pengembalian.
2. **Efisiensi Pengelolaan Bahan Habis Pakai (BHP):** Memonitor pemakaian bahan praktik siswa dan mendeteksi stok yang menipis secara _real-time_.
3. **Penyusunan RAB Pengadaan Cepat & Akurat:** Membantu Toolman mengusulkan Rencana Anggaran Biaya (RAB) secara otomatis berbasis stok limit dan alat rusak berat untuk disetujui Waka Sarpras.

---

## 🌐 2. Live Demo & Akun Pengujian Portofolio

Aplikasi SIBENKA telah aktif dan dapat diakses langsung secara online (*Live Cloud Production*) tanpa perlu instalasi lokal:

🔗 **Akses Aplikasi Demo:** [https://sibenka-skagata.onrender.com](https://sibenka-skagata.onrender.com)

> [!NOTE]
> Karena menggunakan layanan *Render Free Tier*, server web akan masuk ke mode *sleep* saat tidak ada lalu lintas pengunjung. Pemuatan awal (*cold start*) mungkin membutuhkan waktu sekitar 30–50 detik.

### 🔑 Tabel Kredensial Akun Demo (Password: `password`)

Seluruh akun demo menggunakan password seragam: **`password`**

| Peran (Role) | Nama Akun | Email Login | Password | Bengkel / Cakupan | Fokus Fitur yang Dapat Diuji |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Waka Sarpras** | `Waka Sarpras (Demo)` | `waka@skagata.sch.id` | `password` | *Semua Bengkel* | Dashboard analitik aset, approval usulan RAB Pengadaan, laporan mutasi & konsumsi. |
| **Kepala Gudang** | `Kepala Gudang (Demo)` | `gudang@skagata.sch.id` | `password` | *Semua Bengkel* | Pengawasan stok sarpras sekolah & master inventaris umum. |
| **Toolman TKJ** | `Budi Toolman (TKJ)` | `toolman.tkj@skagata.sch.id` | `password` | **TKJ** | Manajemen katalog alat/BHP TKJ, verifikasi antrean pinjam, cek fisik pengembalian, draf RAB. |
| **Toolman TKR** | `Joko Toolman (TKR)` | `toolman.tkr@skagata.sch.id` | `password` | **TKR** | Manajemen katalog alat otomotif TKR, pencatatan bahan habis pakai, kartu stok mutasi. |
| **Siswa TKJ** | `Ahmad Pratama (Siswa TKJ)` | `siswa.tkj@skagata.sch.id` | `password` | **TKJ** | Eksplorasi katalog alat TKJ, keranjang multi-item, pengajuan pinjam, tiket sirkulasi. |
| **Siswa TKR** | `Rian Ramadhan (Siswa TKR)` | `siswa.tkr@skagata.sch.id` | `password` | **TKR** | Pengajuan peminjaman alat praktikum otomotif & pelacakan status tiket. |
| **Guru (Umum)** | `Drs. Hendro Wibowo (Guru)` | `guru@skagata.sch.id` | `password` | *Lintas Bengkel* | Peminjaman alat antar-kejuruan (bebas memilih bengkel TKJ maupun TKR). |

### 💡 Rekomendasi Skenario Uji Coba:
1. **Alur Peminjaman (Siswa/Guru):** Login sebagai `siswa.tkj@skagata.sch.id`, telusuri katalog barang, masukkan *Routerboard MikroTik* ke keranjang, lalu ajukan pinjam.
2. **Alur Verifikasi & Pengembalian (Toolman):** Login sebagai `toolman.tkj@skagata.sch.id`, setujui peminjaman di antrean sirkulasi. Saat barang dikembalikan, buka menu pengembalian untuk input cek kondisi fisik (Baik / Rusak).
3. **Alur Usulan & Persetujuan RAB (Toolman & Waka):** Sebagai toolman buat usulan RAB pengadaan baru atau generate otomatis, lalu login sebagai `waka@skagata.sch.id` untuk meninjau dan menyetujui (ACC) dokumen RAB.

### 🔄 Perintah Pemulihan / Reset Data Demo:
Jika data demo telah dimodifikasi selama pengujian dan ingin dikembalikan ke kondisi awal:
```bash
php artisan app:reset-demo --force
```

---

## 👥 3. Hak Akses & Peran Pengguna (User Roles)

Sistem membagi akses ke dalam 3 tingkatan pengguna:

| Peran (Role)      | Target Pengguna | Layout Navigasi                         | Tanggung Jawab Utama                                                                                                                                                                        |
| :---------------- | :-------------- | :-------------------------------------- | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Super Admin**   | Waka Sarpras    | _Sidebar Navigation_                    | Monitoring analitik aset seluruh sekolah, verifikasi/persetujuan RAB pengadaan dari bengkel, rekap laporan mutasi & konsumsi bahan, manajemen master bengkel & akun toolman.                |
| **Admin Bengkel** | Toolman Jurusan | _Sidebar Navigation_                    | Manajemen katalog barang bengkel (CRUD alat & BHP), verifikasi antrean peminjaman siswa/guru, cek fisik pengembalian, pengajuan draf RAB otomatis, manajemen peminjam (approval & suspend). |
| **Peminjam**      | Siswa & Guru    | _Top Navbar + Mobile Bottom Navigation_ | Eksplorasi katalog alat/bahan (_mobile-first_), keranjang peminjaman multi-barang, formulir pengajuan cerdas, pemantauan status tiket sirkulasi.                                            |

---

## 🚀 3. Fitur Utama Berdasarkan Peran

### A. Super Admin (Waka Sarpras)

- 📊 **Dashboard Analitik Eksekutif:** Statistik total aset, alat aktif dipinjam, barang rusak, stok kritis, dan grafik perbandingan antar jurusan.
- 📝 **Verifikasi RAB Pengadaan:** Antarmuka interaktif peninjauan usulan RAB dari tiap bengkel dengan opsi **Setujui (ACC)**, **Catatan Revisi**, atau **Tolak**, dilengkapi rincian spesifikasi teknis barang.
- 📈 **Laporan & Rekapitulasi:** Rekapitulasi mutasi aset dan laporan konsumsi bahan habis pakai yang siap cetak / ekspor.
- 🏢 **Master Data Terpusat:** Pengelolaan data jurusan/bengkel dan manajemen akun Toolman penanggung jawab.

### B. Admin Bengkel (Toolman)

- 📦 **Manajemen Master Barang (Quantity-Based):** Pendataan alat inventaris dan bahan habis pakai dengan informasi spesifikasi, stok total, stok tersedia, dan lokasi rak/lemari.
- 📋 **Sirkulasi Peminjaman (Antrean ACC):** Tinjau permohonan pinjam alat/bahan dari siswa dan guru secara cepat.
- 🔍 **Sirkulasi Pengembalian & Cek Fisik:** Form verifikasi pengembalian alat dengan pencatatan kondisi fisik (Baik / Rusak Ringan / Rusak Berat / Hilang).
- ⚡ **Pengajuan RAB Otomatis:** Tombol pintar _generate_ draf RAB dari barang berstatus stok menipis dan alat rusak berat.
- 🛑 **Manajemen Peminjam:** Persetujuan registrasi akun siswa/guru baru serta penangguhan (_suspend_) akun yang melanggar tata tertib bengkel.

### C. Peminjam (Siswa & Guru)

- 📱 **Katalog Interaktif (Mobile-First):** Tampilan responsif dengan pencarian instan, filter chip kategori (_Alat Inventaris_ vs _Bahan Habis Pakai_), serta indikator ketersediaan stok.
- 🛒 **Keranjang Peminjaman (Multi-Item):** Siswa dapat memilih beberapa barang sekaligus dalam satu transaksi peminjaman.
- 🧠 **Sistem Pintar Formulir Pengajuan:**
    - _Bahan Habis Pakai (BHP):_ Input kalender/jadwal pengembalian **otomatis disembunyikan**, karena BHP tidak perlu dikembalikan.
    - _Alat Inventaris:_ Wajib mencantumkan batas pengembalian pada hari yang sama.
- 🎟️ **Tiket Peminjaman Saya:** Pelacakan status tiket secara _real-time_ (_Pending_, _Active / Sedang Dipinjam_, _Selesai_), dan tombol aksi ajukan pengembalian.

---

## ⚖️ 4. Aturan Bisnis Sistem (Business Rules)

> [!IMPORTANT]
>
> - **Quantity-Based Inventory:** Barang dicatat berdasarkan kuantitas fisik (misal: 5 Unit Router), bukan nomor seri satuan per unit.
> - **Aturan Pengembalian Alat:** Alat inventaris **wajib dikembalikan pada hari yang sama** sebelum jam bengkel berakhir (maksimal 15:30 WIB).
> - **Bahan Habis Pakai (BHP):** Bahan habis pakai (kabel, konektor, timah solder, dll.) **tidak perlu dikembalikan**. Begitu disetujui Toolman, stok otomatis berkurang secara permanen.
> - **Sanksi Penalti (_Suspend_):** Peminjam yang menghilangkan alat, merusak, atau terlambat mengembalikan akan di-_suspend_ oleh Toolman dan tidak dapat membuat pengajuan baru hingga urusan diselesaikan di luar sistem.
> - **Alur Stok RAB:** Persetujuan RAB oleh Waka Sarpras tidak otomatis menambah stok aplikasi. Stok fisik ditambahkan secara manual oleh Toolman setelah barang fisik resmi diterima di bengkel.
> - **Notifikasi:** Notifikasi berjalan secara visual di dalam aplikasi (_In-App Badge & Toasts_), tanpa ketergantungan API pihak ketiga (Email/WhatsApp).

---

## 📂 5. Struktur Direktori Proyek

```text
inventaris-skagata/
├── app/
│   ├── Http/
│   │   └── Controllers/       # Controller logic aplikasi
│   └── Models/                # Eloquent models
├── resources/
│   ├── css/
│   │   └── app.css            # Konfigurasi Tailwind & typography Inter
│   ├── js/
│   │   ├── app.js             # Inisialisasi Alpine.js & Bootstrap
│   │   └── bootstrap.js       # Axios setup
│   └── views/
│       ├── auth/              # Halaman Login & Registrasi
│       ├── layouts/
│       │   ├── admin.blade.php      # Layout Sidebar (Super Admin & Toolman)
│       │   ├── peminjam.blade.php   # Layout Mobile-First (Siswa & Guru)
│       │   └── auth.blade.php       # Layout autentikasi
│       ├── superadmin/        # Modul Waka Sarpras (Dashboard, Pengadaan, Master, Laporan)
│       ├── toolman/           # Modul Admin Bengkel (Dashboard, Barang, Sirkulasi, RAB, Users)
│       ├── peminjam/          # Modul Siswa/Guru (Katalog, Pengajuan, Tiket Saya)
│       └── profile/           # Manajemen profil akun
├── routes/
│   ├── web.php                # Definisi route sistem Sibenka
│   └── auth.php               # Route autentikasi Breeze
├── PRD Sibenka - v2.1.md      # Dokumen Product Requirement Document (v2.1 Extended)
└── README.md                  # Dokumentasi teknis proyek
```

---

## 🛠️ 6. Teknologi yang Digunakan

- **Backend:** [PHP 8.2+](https://php.net) & [Laravel 12 Framework](https://laravel.com)
- **Frontend Engine:** [Blade Templating Engine](https://laravel.com/docs/blade)
- **CSS Framework:** [Tailwind CSS v3 / v4](https://tailwindcss.com) (Basis warna identitas hijau SMKN 3 Yk: `Emerald-600` / `#059669`)
- **Interaktivitas Klien:** [Alpine.js](https://alpinejs.dev) untuk interaktivitas reaktif, modal, drawer, dan state management lokal
- **Build Tool:** [Vite](https://vitejs.dev)
- **Database:** MySQL / SQLite
- **Lingkungan Lokal:** [Laravel Herd](https://herd.laravel.com) (Windows / macOS)

---

## 💻 7. Panduan Instalasi & Menjalankan Aplikasi

### Prasyarat:

- PHP >= 8.2
- Composer >= 2.x
- Node.js >= 18.x & NPM
- Laravel Herd (atau Laragon / XAMPP)

### Langkah-langkah:

1. **Clone Repository & Masuk ke Direktori:**

    ```bash
    git clone https://github.com/yodi-dev/inventaris-skagata.git
    cd inventaris-skagata
    ```

2. **Instal Dependensi PHP (Composer):**

    ```bash
    composer install
    ```

3. **Salin File Environment & Generate App Key:**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Instal Dependensi Frontend (NPM):**

    ```bash
    npm install
    ```

5. **Migrasi Database & Seeder Awal:**

    ```bash
    php artisan migrate --seed
    ```

    _(Menyiapkan skema basis data dan 2 akun administrator awal: Waka Sarpras & Kepala Gudang)_

6. **Jalankan Server Pengembang (Development):**
    - **Jika menggunakan Laravel Herd:**
      Aplikasi langsung dapat diakses pada browser di:
      `http://inventaris-skagata.test`
    - **Atau jalankan secara manual:**

        ```bash
        # Terminal 1: Menjalankan Vite asset bundler
        npm run dev

        # Terminal 2: Menjalankan server Laravel
        php artisan serve
        ```

        Buka [http://127.0.0.1:8000](http://127.0.0.1:8000) pada browser Anda.

---

## 🧭 8. Peta Rute Utama Aplikasi

| Modul           | URL Endpoint                   | Keterangan Halaman                                      |
| :-------------- | :----------------------------- | :------------------------------------------------------ |
| **Login**       | `/login`                       | Pintu masuk autentikasi pengguna                        |
| **Super Admin** | `/superadmin/dashboard`        | Dashboard analitik Waka Sarpras                         |
| **Super Admin** | `/superadmin/pengadaan`        | Verifikasi draf RAB Pengadaan                           |
| **Super Admin** | `/superadmin/bengkel`          | Manajemen master bengkel                                |
| **Super Admin** | `/superadmin/toolman`          | Manajemen akun Toolman                                  |
| **Super Admin** | `/superadmin/laporan/mutasi`   | Laporan mutasi aset bengkel                             |
| **Super Admin** | `/superadmin/laporan/konsumsi` | Laporan konsumsi bahan (BHP)                            |
| **Toolman**     | `/toolman/dashboard`           | Dashboard operasional bengkel jurusan                   |
| **Toolman**     | `/toolman/barang`              | Manajemen master alat & bahan (Katalog, Import, Export) |
| **Toolman**     | `/toolman/lokasi`              | Manajemen master lokasi penyimpanan                     |
| **Toolman**     | `/toolman/satuan`              | Manajemen master satuan barang                          |
| **Toolman**     | `/toolman/sumber-dana`         | Manajemen master sumber dana anggaran                   |
| **Toolman**     | `/toolman/peminjaman`          | Antrean persetujuan pinjam siswa/guru                   |
| **Toolman**     | `/toolman/pengembalian`        | Verifikasi pengembalian & cek fisik barang              |
| **Toolman**     | `/toolman/pengadaan`           | Penyusunan RAB & penerimaan fisik barang                |
| **Toolman**     | `/toolman/peminjam`            | Manajemen akun peminjam (Approval & Suspend)            |
| **Toolman**     | `/toolman/mutasi`              | Riwayat kartu stok & kartu barang                       |
| **Peminjam**    | `/peminjam/dashboard`          | Dashboard peminjam & ringkasan peminjaman               |
| **Peminjam**    | `/peminjam/katalog`            | Katalog barang praktik (_Mobile-First_ & Keranjang)     |
| **Peminjam**    | `/peminjam/tiket`              | Tiket aktif & riwayat peminjaman                        |

---

## 📄 9. Lisensi & Hak Cipta

Dikembangkan untuk kebutuhan digitalisasi operasional bengkel kejuruan di **SMK Negeri 3 Yogyakarta**.  
Hak cipta dilindungi undang-undang &copy; 2026.
