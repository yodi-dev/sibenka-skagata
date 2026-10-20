@extends('layouts.admin')

@section('title', 'Buku Panduan & SOP Toolman')
@section('header_title', 'Panduan Operasional Staf Bengkel')

@section('content')
    <div x-data="{
        activeTab: 'alur-persetujuan',
        faqOpen: null
    }" class="space-y-6 max-w-6xl mx-auto pb-12">

        <!-- HEADER HERO (Print-Friendly) -->
        <div class="bg-gradient-to-r from-slate-900 via-emerald-950 to-primary-900 rounded-2xl p-6 sm:p-7 text-white shadow-md relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-emerald-300 text-xs font-semibold border border-white/10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Pedoman Standar Operasional Prosedur (SOP)
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight">
                        Panduan Kerja Toolman &bull; Bengkel {{ $bengkel->nama ?? 'Kejuruan' }}
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-300 max-w-2xl">
                        Standar tata kelola sirkulasi alat inventaris, pengawasan bahan praktik (BHP), verifikasi fisik bertahap, dan realisasi RAB pengadaan bengkel sekolah.
                    </p>
                </div>

                <button type="button" onclick="window.print()"
                    class="print:hidden inline-flex items-center justify-center gap-2 px-4 py-2 bg-white/15 hover:bg-white/25 active:bg-white/30 text-white rounded-xl text-xs font-bold backdrop-blur-md border border-white/20 transition-all shadow-xs shrink-0 self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Panduan Staf</span>
                </button>
            </div>
        </div>

        <!-- TAB NAVIGATION (Print: Hidden) -->
        <div class="print:hidden flex items-center gap-2 overflow-x-auto no-scrollbar border-b border-gray-200 pb-2">
            <button type="button" @click="activeTab = 'alur-persetujuan'"
                class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all shrink-0 flex items-center gap-2 border"
                :class="activeTab === 'alur-persetujuan'
                    ? 'bg-primary-600 text-white border-primary-600 shadow-sm'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span>1. Persetujuan 2-Tahap</span>
            </button>

            <button type="button" @click="activeTab = 'cek-fisik'"
                class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all shrink-0 flex items-center gap-2 border"
                :class="activeTab === 'cek-fisik'
                    ? 'bg-primary-600 text-white border-primary-600 shadow-sm'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                </svg>
                <span>2. Cek Fisik 3-Tab</span>
            </button>

            <button type="button" @click="activeTab = 'manajemen-barang'"
                class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all shrink-0 flex items-center gap-2 border"
                :class="activeTab === 'manajemen-barang'
                    ? 'bg-primary-600 text-white border-primary-600 shadow-sm'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span>3. Barang &amp; Impor Excel</span>
            </button>

            <button type="button" @click="activeTab = 'pengadaan-rab'"
                class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all shrink-0 flex items-center gap-2 border"
                :class="activeTab === 'pengadaan-rab'
                    ? 'bg-primary-600 text-white border-primary-600 shadow-sm'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <span>4. RAB &amp; Receive Fisik</span>
            </button>

            <button type="button" @click="activeTab = 'manajemen-user'"
                class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all shrink-0 flex items-center gap-2 border"
                :class="activeTab === 'manajemen-user'
                    ? 'bg-primary-600 text-white border-primary-600 shadow-sm'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>5. Peminjam &amp; Suspend</span>
            </button>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 1: PERSETUJUAN 2-TAHAP (TWO-STAGE APPROVAL)          -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'alur-persetujuan'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-primary-100 text-primary-700 font-black text-sm flex items-center justify-center">1</span>
                        SOP Persetujuan Bertahap (Two-Stage Approval)
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Menjelaskan alur penanganan tiket pemesanan di muka (advance booking) dan penyerahan barang fisik di meja bengkel.
                    </p>
                </div>

                <!-- 2 Stage Comparison Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Stage 1 -->
                    <div class="border border-emerald-200 rounded-xl p-5 bg-emerald-50/30 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-emerald-800 uppercase tracking-wider font-mono">Tahap 1: Booking Jadwal</span>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Tombol 'Setujui Jadwal'</span>
                        </div>
                        <h3 class="font-bold text-sm text-gray-900">Mengamankan Kuota Alat Terjadwal</h3>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Dilakukan saat peminjam mengajukan tanggal pinjam di masa depan (maksimal 14 hari). Toolman menekan tombol <strong>Setujui Jadwal</strong>.
                        </p>
                        <div class="p-3 bg-white rounded-lg border border-emerald-100 text-xs text-gray-700 space-y-1">
                            <p><strong>Dampak Sistem:</strong></p>
                            <ul class="list-disc list-inside space-y-0.5 text-gray-600">
                                <li>Status tiket berubah menjadi <strong class="text-emerald-700">Jadwal Disetujui</strong>.</li>
                                <li>Stok fisik (<code class="font-mono text-emerald-700">stok_tersedia</code>) <strong>TIDAK BERKURANG</strong> karena barang masih di rak.</li>
                                <li>Kuota reservasi (<code class="font-mono text-emerald-700">stok_reserved</code>) <strong>BERTAMBAH</strong>, sehingga sisa bebas (<code class="font-mono">stok_bebas</code>) terkunci dari peminjam lain.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Stage 2 -->
                    <div class="border border-blue-200 rounded-xl p-5 bg-blue-50/30 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-blue-800 uppercase tracking-wider font-mono">Tahap 2: Serahkan Fisik</span>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">Tombol 'Serahkan Barang'</span>
                        </div>
                        <h3 class="font-bold text-sm text-gray-900">Pemotongan Stok Fisik di Hari-H</h3>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Dilakukan pada hari-H ketika peminjam datang ke meja Toolman dan menerima fisik alat yang siap dipakai.
                        </p>
                        <div class="p-3 bg-white rounded-lg border border-blue-100 text-xs text-gray-700 space-y-1">
                            <p><strong>Dampak Sistem:</strong></p>
                            <ul class="list-disc list-inside space-y-0.5 text-gray-600">
                                <li>Status tiket berubah menjadi <strong class="text-blue-700">Sedang Dipinjam</strong> (Active).</li>
                                <li>Stok fisik (<code class="font-mono text-blue-700">stok_tersedia</code>) berkurang sesuai kuantitas diserahkan.</li>
                                <li>Kuantitas dipinjam (<code class="font-mono text-blue-700">stok_dipinjam</code>) bertambah. Kuota reservasi otomatis dilepas.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Info Box: Pembatalan Jadwal -->
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-950 flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="space-y-1 text-xs">
                        <p class="font-bold text-amber-900">Bagaimana Jika Siswa Membatalkan Jadwal?</p>
                        <p class="text-amber-800 leading-relaxed">
                            Jika peminjam berhalangan hadir atau praktikum dibatalkan, Toolman cukup menekan tombol <strong>Batalkan Tiket</strong> (Tolak) dengan memasukkan alasan pembatalan. Kuota reservasi (<code class="font-mono">stok_reserved</code>) otomatis dilepaskan seketika dan kuota bebas kembali utuh untuk siswa lain.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 2: CEK FISIK 3-TAB PENGEMBALIAN                     -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'cek-fisik'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-primary-100 text-primary-700 font-black text-sm flex items-center justify-center">2</span>
                        SOP Pemeriksaan Fisik 3-Tab di Meja Pengembalian
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Halaman <em>Cek Fisik &amp; Pengembalian</em> membagi alur kerja ke dalam 3 tab untuk mencegah tombol cek fisik ditekan sebelum barang tiba di meja.
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- Tab 1 Explanation -->
                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/50 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-primary-100 text-primary-800 border border-primary-200">Tab 1: Menunggu Cek Fisik (Default)</span>
                            <span class="text-xs text-gray-500 font-medium">&bull; Antrean Meja Toolman</span>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Memuat daftar tiket yang peminjamnya <strong>sudah menekan tombol 'Kembalikan'</strong> di aplikasi. Toolman menekan tombol hijau <strong>Cek Fisik &amp; Konfirmasi Kembali</strong>, menghitung jumlah unit kembali baik, rusak, atau hilang, lalu menyimpan hasil inspeksi.
                        </p>
                    </div>

                    <!-- Tab 2 Explanation -->
                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/50 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">Tab 2: Sedang Dipinjam</span>
                            <span class="text-xs text-gray-500 font-medium">&bull; Monitoring Barang di Lapangan</span>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Memuat transaksi yang alatnya masih berada di luar bengkel. Tombol cek fisik tidak aktif secara bebas. Apabila siswa mengembalikan barang langsung tanpa menekan aplikasi, Toolman dapat menekan tombol cadangan <strong>Terima &amp; Cek Fisik Langsung</strong> yang diproteksi dialog modal konfirmasi.
                        </p>
                    </div>

                    <!-- Tab 3 Explanation -->
                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/50 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Tab 3: Riwayat Pengembalian</span>
                            <span class="text-xs text-gray-500 font-medium">&bull; Arsip Selesai</span>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Menyimpan arsip seluruh transaksi yang telah tuntas diperiksa fisiknya, lengkap dengan rincian alat kembali baik, rusak, atau hilang serta tautan cetak bukti pengembalian.
                        </p>
                    </div>
                </div>

                <!-- Rumus Pencatatan Fisik -->
                <div class="p-4 rounded-xl bg-slate-900 text-white space-y-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-400">Formula Pembaruan Stok Otomatis Saat Cek Fisik:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs pt-1">
                        <div class="bg-white/10 p-2.5 rounded-lg border border-white/10">
                            <span class="text-emerald-300 font-bold block mb-1">Kembali Baik:</span>
                            Memindahkan unit dari <code class="text-emerald-200 font-mono">stok_dipinjam</code> kembali ke <code class="text-emerald-200 font-mono">stok_tersedia</code>.
                        </div>
                        <div class="bg-white/10 p-2.5 rounded-lg border border-white/10">
                            <span class="text-amber-300 font-bold block mb-1">Kembali Rusak:</span>
                            Memindahkan unit dari <code class="text-amber-200 font-mono">stok_dipinjam</code> ke <code class="text-amber-200 font-mono">stok_rusak</code>.
                        </div>
                        <div class="bg-white/10 p-2.5 rounded-lg border border-white/10">
                            <span class="text-rose-300 font-bold block mb-1">Hilang:</span>
                            Mengurangi <code class="text-rose-200 font-mono">stok_dipinjam</code> dan memotong <code class="text-rose-200 font-mono">stok_total</code> secara permanen.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 3: MANAJEMEN BARANG & IMPOR EXCEL                    -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'manajemen-barang'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-primary-100 text-primary-700 font-black text-sm flex items-center justify-center">3</span>
                        Manajemen Persediaan &amp; Standar Impor Spreadsheet
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Ketentuan penataan data persediaan barang bengkel dan aturan keamanan berkas spreadsheet.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="border border-gray-200 rounded-xl p-5 space-y-3">
                        <h3 class="font-bold text-sm text-gray-900">Ketentuan Kode &amp; Klasifikasi</h3>
                        <ul class="space-y-2 text-xs text-gray-600 leading-relaxed">
                            <li class="flex items-start gap-2">
                                <span class="text-primary-600 font-bold">&bull;</span>
                                <span><strong>Isolasi Kode Barang:</strong> Kode barang bersifat unik per bengkel. Kode yang sama dapat digunakan pada bengkel lain tanpa konflik.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-primary-600 font-bold">&bull;</span>
                                <span><strong>Satuan &amp; Sumber Dana:</strong> Gunakan satuan baku dan sumber dana yang telah terdaftar pada menu Master Data untuk menjaga keseragaman laporan.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-primary-600 font-bold">&bull;</span>
                                <span><strong>Cetak Kartu Barang:</strong> Setiap barang memiliki kartu riwayat mutasi yang dapat dicetak resmi dalam format F4/Folio dari menu aksi barang.</span>
                            </li>
                        </ul>
                    </div>

                    <div class="border border-gray-200 rounded-xl p-5 space-y-3">
                        <h3 class="font-bold text-sm text-gray-900">Aturan Berkas Impor (.xlsx / .csv)</h3>
                        <ul class="space-y-2 text-xs text-gray-600 leading-relaxed">
                            <li class="flex items-start gap-2">
                                <span class="text-primary-600 font-bold">&bull;</span>
                                <span><strong>Unduh Template Resmi:</strong> Selalu gunakan tombol <em>Unduh Template Excel</em> di halaman Manajemen Barang agar urutan kolom sesuai sistem.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-primary-600 font-bold">&bull;</span>
                                <span><strong>Format Diterima:</strong> Hanya file modern <code class="font-mono text-primary-700">.xlsx</code> dan <code class="font-mono text-primary-700">.csv</code>. Berkas lama <code class="font-mono text-rose-700">.xls</code> otomatis ditolak demi keamanan.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-primary-600 font-bold">&bull;</span>
                                <span><strong>Integritas All-or-Nothing:</strong> Jika terdapat satu baris data yang salah format atau menabrak duplikasi, seluruh berkas impor dibatalkan tanpa menyisakan data sampah.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 4: PENGADAAN (RAB) & RECEIVE FISIK                   -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'pengadaan-rab'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-primary-100 text-primary-700 font-black text-sm flex items-center justify-center">4</span>
                        SOP Pengusulan RAB &amp; Konfirmasi Penerimaan Barang (Receive)
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Siklus perencanaan pengadaan barang tahunan dari penyusunan draf hingga penambahan stok otomatis.
                    </p>
                </div>

                <!-- 4 Steps of RAB -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/60 space-y-1.5">
                        <span class="text-xs font-black text-primary-600 font-mono">1. BUAT DRAF</span>
                        <h4 class="font-bold text-xs text-gray-900">Susun Rincian Item</h4>
                        <p class="text-[11px] text-gray-600 leading-relaxed">
                            Toolman menyusun usulan barang baru atau penambahan stok barang eksisting lengkap dengan harga perkiraan dan jenis definitif (Inventaris / BHP).
                        </p>
                    </div>

                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/60 space-y-1.5">
                        <span class="text-xs font-black text-primary-600 font-mono">2. AJUKAN RAB</span>
                        <h4 class="font-bold text-xs text-gray-900">Kirim ke Waka Sarpras</h4>
                        <p class="text-[11px] text-gray-600 leading-relaxed">
                            Setelah draf tuntas, klik tombol <em>Ajukan Sekarang</em>. Dokumen berpindah ke antrean telaah Waka Sarpras sekolah.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/60 space-y-1.5">
                        <span class="text-xs font-black text-primary-600 font-mono">3. TELAAH WAKA</span>
                        <h4 class="font-bold text-xs text-gray-900">Keputusan Review</h4>
                        <p class="text-[11px] text-gray-600 leading-relaxed">
                            Waka Sarpras menyetujui (Approved), meminta perbaikan dengan catatan (Revisi), atau menolak (Rejected).
                        </p>
                    </div>

                    <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/50 space-y-1.5">
                        <span class="text-xs font-black text-emerald-700 font-mono">4. RECEIVE FISIK</span>
                        <h4 class="font-bold text-xs text-emerald-950">Konfirmasi Kedatangan</h4>
                        <p class="text-[11px] text-emerald-900 leading-relaxed">
                            Saat paket barang fisik tiba di bengkel, Toolman menekan tombol <strong>Terima &amp; Tambah Stok</strong>. Stok bertambah otomatis dan mutasi tercatat resmi.
                        </p>
                    </div>
                </div>

                <!-- Callout Anti-Tampering -->
                <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-950 text-xs leading-relaxed">
                    <strong>Prinsip Kunci Anti-Manipulasi:</strong> Persetujuan Waka Sarpras mengunci klasifikasi jenis barang (<code class="font-mono">inventaris</code> vs <code class="font-mono">bhp</code>) dan batas minimum stok. Saat Toolman melakukan aksi <em>receive</em>, sistem tidak mengizinkan perubahan klasifikasi yang telah disetujui resmi oleh pihak sekolah.
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 5: MANAJEMEN AKUN & SANKSI SUSPEND                   -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'manajemen-user'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-primary-100 text-primary-700 font-black text-sm flex items-center justify-center">5</span>
                        Manajemen Akun Peminjam &amp; Penegakan Disiplin (Suspend)
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Kewenangan Toolman dalam memvalidasi akun baru dan menegakkan tata tertib bengkel.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/50 space-y-1.5 text-xs sm:text-sm">
                        <h4 class="font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-primary-600"></span>
                            Verifikasi Siswa Baru (Menunggu Acc)
                        </h4>
                        <p class="text-gray-600 leading-relaxed text-xs">
                            Siswa baru yang mendaftar mandiri akan berstatus <em>Menunggu Acc</em>. Buka menu <strong>Data Peminjam</strong>, periksa kesesuaian NISN dan jurusan, lalu klik <strong>Setujui Akun</strong> agar siswa dapat mulai meminjam alat praktikum.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/50 space-y-1.5 text-xs sm:text-sm">
                        <h4 class="font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                            Penangguhan Akun (Suspend)
                        </h4>
                        <p class="text-gray-600 leading-relaxed text-xs">
                            Jika peminjam menghilangkan alat, merusak fasilitas bengkel, atau terlambat mengembalikan alat berulang kali, Toolman berhak menekan tombol <strong>Tangguhkan (Suspend)</strong>. Akun yang disuspend otomatis diblokir sistem dari peminjaman baru di semua jurusan.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/50 space-y-1.5 text-xs sm:text-sm">
                        <h4 class="font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            Pemulihan Akun (Activate)
                        </h4>
                        <p class="text-gray-600 leading-relaxed text-xs">
                            Setelah siswa menyelesaikan kewajiban ganti rugi fisik atau menyelesaikan masa sanksi tata tertib, Toolman memulihkan akun dengan menekan tombol <strong>Aktifkan Kembali</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
