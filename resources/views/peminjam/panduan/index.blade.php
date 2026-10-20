@extends('layouts.peminjam')

@section('title', 'Buku Panduan & SOP Peminjam')

@section('content')
    <div x-data="{
        activeTab: 'alur-pinjam',
        faqOpen: null
    }" class="space-y-6 max-w-5xl mx-auto pb-12">

        <!-- HEADER HERO (Print-Friendly) -->
        <div class="bg-gradient-to-r from-slate-900 via-emerald-950 to-primary-900 rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
            <!-- Decorative Accent -->
            <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-primary-500/10 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-emerald-300 text-xs font-semibold border border-white/10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Pusat Bantuan &amp; Panduan Peminjam
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight">Panduan Sirkulasi &amp; SOP Bengkel</h1>
                    <p class="text-xs sm:text-sm text-gray-300 max-w-xl">
                        Pelajari alur peminjaman alat, pemesanan terjadwal (advance booking), prosedur cek fisik, serta tata tertib bengkel kejuruan SMK Negeri 3 Yogyakarta.
                    </p>
                </div>
                
                <!-- Print Button (Hidden on Print) -->
                <button type="button" onclick="window.print()" 
                    class="print:hidden inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white/15 hover:bg-white/25 active:bg-white/30 text-white rounded-xl text-xs font-bold backdrop-blur-md border border-white/20 transition-all shadow-xs shrink-0 self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak SOP Bengkel</span>
                </button>
            </div>
        </div>

        <!-- TAB NAVIGATION (Print: Hidden) -->
        <div class="print:hidden flex items-center gap-2 overflow-x-auto no-scrollbar border-b border-gray-200 pb-2">
            <button type="button" @click="activeTab = 'alur-pinjam'"
                class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all shrink-0 flex items-center gap-2 border"
                :class="activeTab === 'alur-pinjam'
                    ? 'bg-primary-600 text-white border-primary-600 shadow-sm'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>1. Alur Peminjaman</span>
            </button>

            <button type="button" @click="activeTab = 'alur-kembali'"
                class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all shrink-0 flex items-center gap-2 border"
                :class="activeTab === 'alur-kembali'
                    ? 'bg-primary-600 text-white border-primary-600 shadow-sm'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span>2. Alur Pengembalian</span>
            </button>

            <button type="button" @click="activeTab = 'status'"
                class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all shrink-0 flex items-center gap-2 border"
                :class="activeTab === 'status'
                    ? 'bg-primary-600 text-white border-primary-600 shadow-sm'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <span>3. Kamus Status</span>
            </button>

            <button type="button" @click="activeTab = 'tata-tertib'"
                class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all shrink-0 flex items-center gap-2 border"
                :class="activeTab === 'tata-tertib'
                    ? 'bg-primary-600 text-white border-primary-600 shadow-sm'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>4. Tata Tertib &amp; Sanksi</span>
            </button>

            <button type="button" @click="activeTab = 'faq'"
                class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all shrink-0 flex items-center gap-2 border"
                :class="activeTab === 'faq'
                    ? 'bg-primary-600 text-white border-primary-600 shadow-sm'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>5. Tanya Jawab (FAQ)</span>
            </button>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 1: ALUR PEMINJAMAN & ADVANCE BOOKING (14 HARI)       -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'alur-pinjam'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-primary-100 text-primary-700 font-black text-sm flex items-center justify-center">1</span>
                        Alur Pengajuan &amp; Peminjaman Alat Praktik
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Peminjam dapat meminjam alat untuk digunakan hari ini juga atau memesan jadwal hingga <strong>14 hari ke depan</strong>.
                    </p>
                </div>

                <!-- Step Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Step 1 -->
                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/60 relative space-y-2">
                        <span class="text-xs font-black text-primary-600 font-mono">LANGKAH 01</span>
                        <h3 class="font-bold text-sm text-gray-900">Pilih Alat &amp; Bahan</h3>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Buka menu <strong>Katalog</strong>, pilih alat inventaris atau bahan habis pakai (BHP) yang dibutuhkan, lalu masukkan ke keranjang.
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/60 relative space-y-2">
                        <span class="text-xs font-black text-primary-600 font-mono">LANGKAH 02</span>
                        <h3 class="font-bold text-sm text-gray-900">Tentukan Jadwal &amp; Keperluan</h3>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Tentukan <strong>tanggal pinjam</strong> (bisa hari-H atau hingga 14 hari ke depan), jam batas pengembalian, dan tuliskan keperluan praktikum.
                        </p>
                    </div>

                    <!-- Step 3 -->
                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/60 relative space-y-2">
                        <span class="text-xs font-black text-primary-600 font-mono">LANGKAH 03</span>
                        <h3 class="font-bold text-sm text-gray-900">Persetujuan Toolman</h3>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Toolman akan menyetujui jadwal Anda (status menjadi <strong>Jadwal Disetujui</strong>). Kuota barang langsung diamankan untuk Anda.
                        </p>
                    </div>

                    <!-- Step 4 -->
                    <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/50 relative space-y-2">
                        <span class="text-xs font-black text-emerald-700 font-mono">LANGKAH 04</span>
                        <h3 class="font-bold text-sm text-emerald-950">Ambil Fisik Barang di Hari-H</h3>
                        <p class="text-xs text-emerald-900 leading-relaxed">
                            Datang ke meja Toolman pada tanggal pinjam. Tunjukkan nomor tiket <strong>#TRX-...</strong>, Toolman menyerahkan alat, dan status menjadi <strong>Sedang Dipinjam</strong>.
                        </p>
                    </div>
                </div>

                <!-- Info Box: Inventaris vs BHP -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-950 space-y-1.5">
                        <div class="flex items-center gap-2 font-bold text-xs uppercase tracking-wider text-blue-800">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Barang Inventaris (Alat Praktik)
                        </div>
                        <p class="text-xs text-blue-900 leading-relaxed">
                            Contoh: Multimeter, Mesin Bor, Obeng, Tang, Osiloskop. <strong>Wajib dikembalikan</strong> ke bengkel dalam kondisi baik sebelum jam operasional tutup.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-950 space-y-1.5">
                        <div class="flex items-center gap-2 font-bold text-xs uppercase tracking-wider text-amber-800">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            Bahan Habis Pakai (BHP)
                        </div>
                        <p class="text-xs text-amber-900 leading-relaxed">
                            Contoh: Timah Solder, Kabel Jumper, Amplas, Oli, Mur Baut. <strong>Tidak perlu dikembalikan</strong> karena terkonsumsi selama praktikum.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 2: ALUR PENGEMBALIAN & CEK FISIK                     -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'alur-kembali'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-primary-100 text-primary-700 font-black text-sm flex items-center justify-center">2</span>
                        Alur Pengembalian Alat &amp; Pemeriksaan Fisik
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Kembalikan alat secara tertib untuk menjaga persediaan alat bengkel tetap lengkap dan terawat.
                    </p>
                </div>

                <!-- 3 Steps Pengembalian -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/60 space-y-2">
                        <span class="text-xs font-black text-primary-600 font-mono">LANGKAH 01</span>
                        <h3 class="font-bold text-sm text-gray-900">Tekan 'Kembalikan' di Aplikasi</h3>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Buka menu <strong>Tiket Saya</strong> atau Beranda, cari pinjaman aktif Anda, lalu klik tombol <strong>Kembalikan</strong>. Status tiket akan berganti menjadi <strong>Menunggu Cek Fisik</strong>.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/60 space-y-2">
                        <span class="text-xs font-black text-primary-600 font-mono">LANGKAH 02</span>
                        <h3 class="font-bold text-sm text-gray-900">Serahkan Alat ke Meja Toolman</h3>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Bawa seluruh alat fisik ke meja petugas Toolman. Bersihkan alat dari sisa oli/debu praktikum sebelum diserahkan.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/50 space-y-2">
                        <span class="text-xs font-black text-emerald-700 font-mono">LANGKAH 03</span>
                        <h3 class="font-bold text-sm text-emerald-950">Inspeksi &amp; Konfirmasi Selesai</h3>
                        <p class="text-xs text-emerald-900 leading-relaxed">
                            Toolman memeriksa kondisi fisik alat (Jumlah Baik, Rusak, atau Hilang). Setelah diinspeksi, status tiket ditutup menjadi <strong>Selesai</strong> dan bukti pengembalian dapat dicetak.
                        </p>
                    </div>
                </div>

                <!-- Callout Penting -->
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-950 flex items-start gap-3">
                    <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="space-y-1 text-xs">
                        <p class="font-bold text-rose-900">Penting: Jangan Tinggalkan Alat Tanpa Konfirmasi Cek Fisik!</p>
                        <p class="text-rose-800 leading-relaxed">
                            Meletakkan alat begitu saja di bengkel tanpa diperiksa oleh Toolman berisiko dianggap belum mengembalikan atau alat tertukar dengan siswa lain. Pastikan Anda menyaksikan proses cek fisik Toolman hingga status tiket dinyatakan selesai.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 3: KAMUS STATUS TRANSAKSI                             -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'status'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-primary-100 text-primary-700 font-black text-sm flex items-center justify-center">3</span>
                        Kamus Status Tiket &amp; Arti Badge Warna
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Pahami arti setiap status transaksi peminjaman pada aplikasi SIBENKA:
                    </p>
                </div>

                <!-- Table of Statuses -->
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                        <thead class="bg-slate-50 text-gray-600 font-bold">
                            <tr>
                                <th class="px-4 py-3 text-left w-36 sm:w-48">Status &amp; Badge</th>
                                <th class="px-4 py-3 text-left">Arti / Kondisi Transaksi</th>
                                <th class="px-4 py-3 text-left w-48 sm:w-60">Tindakan Anda</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <tr>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 border border-yellow-200">
                                        Menunggu Acc
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    Permohonan pinjam telah dikirim ke sistem, sedang menunggu ditinjau oleh staf Toolman bengkel.
                                </td>
                                <td class="px-4 py-3 text-gray-500 font-medium">
                                    Tunggu notifikasi / verifikasi dari Toolman.
                                </td>
                            </tr>

                            <tr>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Jadwal Disetujui
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    Jadwal peminjaman terencana Anda telah disetujui resmi. Kuota alat telah diamankan dan tidak dapat diambil orang lain.
                                </td>
                                <td class="px-4 py-3 text-emerald-700 font-bold">
                                    Ambil alat di meja Toolman pada tanggal &amp; jam pinjam.
                                </td>
                            </tr>

                            <tr>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                        Sedang Dipinjam
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    Alat fisik telah diserahkan dan saat ini sedang berada dalam penggunaan dan tanggung jawab Anda.
                                </td>
                                <td class="px-4 py-3 text-gray-500 font-medium">
                                    Gunakan alat dengan tertib dan jaga keutuhannya.
                                </td>
                            </tr>

                            <tr>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-200">
                                        Cek Meja / Menunggu Cek
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    Anda sudah mengajukan pengembalian di aplikasi. Menunggu verifikasi fisik oleh Toolman di meja bengkel.
                                </td>
                                <td class="px-4 py-3 text-purple-700 font-medium">
                                    Serahkan alat fisik ke meja Toolman sekarang.
                                </td>
                            </tr>

                            <tr>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200 animate-pulse">
                                        Terlambat
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    Waktu peminjaman telah melewati batas waktu pengembalian yang disepakati.
                                </td>
                                <td class="px-4 py-3 text-rose-700 font-bold">
                                    Segera kembalikan alat ke bengkel hari ini juga!
                                </td>
                            </tr>

                            <tr>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Selesai
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    Seluruh alat fisik telah tuntas diperiksa kondisinya oleh Toolman. Transaksi telah ditutup.
                                </td>
                                <td class="px-4 py-3 text-gray-500 font-medium">
                                    Tidak ada tindakan (bukti kembali dapat dicetak).
                                </td>
                            </tr>

                            <tr>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-300">
                                        Ditolak
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    Pengajuan pinjam tidak dapat disetujui (misal stok alat habis, alat sedang maintenance, atau jadwal bentrok).
                                </td>
                                <td class="px-4 py-3 text-gray-500 font-medium">
                                    Lihat alasan penolakan pada detail tiket.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 4: TATA TERTIB & SANKSI BENGKEL                      -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'tata-tertib'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-primary-100 text-primary-700 font-black text-sm flex items-center justify-center">4</span>
                        Tata Tertib &amp; Kebijakan Sanksi Bengkel SMKN 3 Yogyakarta
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Pedoman kedisiplinan dan tanggung jawab penggunaan sarana prasarana sekolah:
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- Aturan 1 -->
                    <div class="flex items-start gap-3 p-4 rounded-xl border border-gray-200 bg-slate-50/50">
                        <div class="w-8 h-8 rounded-lg bg-primary-600 text-white font-black text-xs flex items-center justify-center shrink-0">1</div>
                        <div class="space-y-1 text-xs sm:text-sm">
                            <h4 class="font-bold text-gray-900">Batas Waktu Pengembalian Harian</h4>
                            <p class="text-gray-600 leading-relaxed">
                                Seluruh alat inventaris yang dipinjam pada hari kegiatan belajar mengajar wajib dikembalikan paling lambat pukul <strong>15.30 WIB</strong> (atau 30 menit sebelum bengkel ditutup). Alat tidak diperbolehkan dibawa pulang ke rumah tanpa izin tertulis dari Kepala Bengkel.
                            </p>
                        </div>
                    </div>

                    <!-- Aturan 2 -->
                    <div class="flex items-start gap-3 p-4 rounded-xl border border-gray-200 bg-slate-50/50">
                        <div class="w-8 h-8 rounded-lg bg-primary-600 text-white font-black text-xs flex items-center justify-center shrink-0">2</div>
                        <div class="space-y-1 text-xs sm:text-sm">
                            <h4 class="font-bold text-gray-900">Tanggung Jawab Alat Rusak atau Hilang</h4>
                            <p class="text-gray-600 leading-relaxed">
                                Peminjam bertanggung jawab penuh atas keutuhan alat selama masa pinjam. Apabila alat rusak akibat kelalaian atau hilang, peminjam wajib mengganti alat dengan spesifikasi dan merek yang setara melalui koordinasi langsung dengan petugas Toolman.
                            </p>
                        </div>
                    </div>

                    <!-- Aturan 3 -->
                    <div class="flex items-start gap-3 p-4 rounded-xl border border-gray-200 bg-slate-50/50">
                        <div class="w-8 h-8 rounded-lg bg-primary-600 text-white font-black text-xs flex items-center justify-center shrink-0">3</div>
                        <div class="space-y-1 text-xs sm:text-sm">
                            <h4 class="font-bold text-gray-900">Sanksi Penangguhan Akun (Suspend)</h4>
                            <p class="text-gray-600 leading-relaxed">
                                Akun peminjam yang terlambat berulang kali atau belum menyelesaikan kewajiban ganti rugi fisik akan diubah statusnya menjadi <strong>Suspend</strong> oleh Toolman. Selama berstatus suspend, peminjam tidak dapat meminjam alat apapun di seluruh unit bengkel sekolah hingga kewajiban diselesaikan.
                            </p>
                        </div>
                    </div>

                    <!-- Aturan 4 -->
                    <div class="flex items-start gap-3 p-4 rounded-xl border border-gray-200 bg-slate-50/50">
                        <div class="w-8 h-8 rounded-lg bg-primary-600 text-white font-black text-xs flex items-center justify-center shrink-0">4</div>
                        <div class="space-y-1 text-xs sm:text-sm">
                            <h4 class="font-bold text-gray-900">Kebijakan Akun Siswa vs Guru</h4>
                            <p class="text-gray-600 leading-relaxed">
                                <strong>Siswa</strong> hanya diizinkan meminjam alat pada bengkel jurusannya masing-masing. <strong>Guru</strong> memiliki hak akses peminjaman lintas bengkel sesuai mata pelajaran produktif yang diampu.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 5: TANYA JAWAB (FAQ INTERAKTIF)                      -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'faq'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-primary-100 text-primary-700 font-black text-sm flex items-center justify-center">5</span>
                        Pertanyaan yang Sering Diajukan (FAQ)
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Temukan jawaban cepat untuk pertanyaan umum seputar penggunaan SIBENKA:
                    </p>
                </div>

                <div class="space-y-3">
                    <!-- FAQ 1 -->
                    <div class="border border-gray-200 rounded-xl overflow-hidden transition-colors">
                        <button type="button" @click="faqOpen = (faqOpen === 1 ? null : 1)" 
                            class="w-full px-5 py-3.5 text-left font-bold text-xs sm:text-sm text-gray-900 flex items-center justify-between gap-3 hover:bg-gray-50">
                            <span>Bisakah saya meminjam alat untuk minggu depan?</span>
                            <svg class="w-4 h-4 text-gray-500 transition-transform shrink-0" :class="faqOpen === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="faqOpen === 1" x-collapse class="px-5 pb-4 pt-1 text-xs text-gray-600 border-t border-gray-100 leading-relaxed bg-slate-50/50">
                            <strong>Bisa!</strong> Sistem SIBENKA mendukung fitur <em>Advance Booking</em> hingga <strong>14 hari ke depan</strong>. Saat mengisi formulir pengajuan, pilih tanggal peminjaman yang Anda rencanakan. Setelah disetujui Toolman, kuota alat akan terkunci untuk Anda.
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="border border-gray-200 rounded-xl overflow-hidden transition-colors">
                        <button type="button" @click="faqOpen = (faqOpen === 2 ? null : 2)" 
                            class="w-full px-5 py-3.5 text-left font-bold text-xs sm:text-sm text-gray-900 flex items-center justify-between gap-3 hover:bg-gray-50">
                            <span>Mengapa akun saya masih berstatus 'Menunggu Acc'?</span>
                            <svg class="w-4 h-4 text-gray-500 transition-transform shrink-0" :class="faqOpen === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="faqOpen === 2" x-collapse class="px-5 pb-4 pt-1 text-xs text-gray-600 border-t border-gray-100 leading-relaxed bg-slate-50/50">
                            Setiap pendaftaran mandiri siswa baru wajib diverifikasi secara manual oleh staf Toolman bengkel jurusan Anda untuk mencegah akun palsu. Silakan temui Toolman di bengkel jurusan Anda dan tunjukkan kartu identitas/kartu pelajar untuk disetujui akunnya.
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="border border-gray-200 rounded-xl overflow-hidden transition-colors">
                        <button type="button" @click="faqOpen = (faqOpen === 3 ? null : 3)" 
                            class="w-full px-5 py-3.5 text-left font-bold text-xs sm:text-sm text-gray-900 flex items-center justify-between gap-3 hover:bg-gray-50">
                            <span>Bagaimana jika saya lupa kata sandi akun saya?</span>
                            <svg class="w-4 h-4 text-gray-500 transition-transform shrink-0" :class="faqOpen === 3 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="faqOpen === 3" x-collapse class="px-5 pb-4 pt-1 text-xs text-gray-600 border-t border-gray-100 leading-relaxed bg-slate-50/50">
                            Aplikasi SIBENKA tidak menggunakan pengiriman email reset password publik demi keamanan data sekolah. Cukup lapor ke petugas Toolman bengkel Anda atau administrator sekolah untuk mereset kata sandi akun Anda secara langsung.
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="border border-gray-200 rounded-xl overflow-hidden transition-colors">
                        <button type="button" @click="faqOpen = (faqOpen === 4 ? null : 4)" 
                            class="w-full px-5 py-3.5 text-left font-bold text-xs sm:text-sm text-gray-900 flex items-center justify-between gap-3 hover:bg-gray-50">
                            <span>Apakah saya bisa mencetak bukti peminjaman jika tiket saya ditolak?</span>
                            <svg class="w-4 h-4 text-gray-500 transition-transform shrink-0" :class="faqOpen === 4 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="faqOpen === 4" x-collapse class="px-5 pb-4 pt-1 text-xs text-gray-600 border-t border-gray-100 leading-relaxed bg-slate-50/50">
                            <strong>Tidak bisa.</strong> Sesuai SOP integritas sirkulasi, tiket peminjaman yang berstatus <em>Ditolak</em> dikunci dari pencetakan bukti fisik karena barang tidak pernah dipinjamkan. Bukti cetak hanya tersedia untuk transaksi yang telah disetujui / aktif atau telah selesai dikembalikan.
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
