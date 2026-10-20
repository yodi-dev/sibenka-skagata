@extends('layouts.admin')

@section('title', 'Buku Panduan Waka Sarpras')
@section('header_title', 'Panduan Eksekutif Waka Sarpras')

@section('content')
    <div x-data="{
        activeTab: 'monitoring-aset',
        faqOpen: null
    }" class="space-y-6 max-w-6xl mx-auto pb-12">

        <!-- HEADER HERO (Print-Friendly) -->
        <div class="bg-gradient-to-r from-slate-900 via-emerald-950 to-primary-900 rounded-2xl p-6 sm:p-7 text-white shadow-md relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-emerald-300 text-xs font-semibold border border-white/10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Pedoman Eksekutif Pengawasan Sarpras
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight">
                        Panduan Waka Sarpras &bull; SMK Negeri 3 Yogyakarta
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-300 max-w-2xl">
                        Pengawasan aset lintas bengkel kejuruan, verifikasi penganggaran RAB, penerbitan rekapitulasi mutasi dan konsumsi bahan, serta tata kelola akun petugas.
                    </p>
                </div>

                <button type="button" onclick="window.print()"
                    class="print:hidden inline-flex items-center justify-center gap-2 px-4 py-2 bg-white/15 hover:bg-white/25 active:bg-white/30 text-white rounded-xl text-xs font-bold backdrop-blur-md border border-white/20 transition-all shadow-xs shrink-0 self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Panduan Waka</span>
                </button>
            </div>
        </div>

        <!-- TAB NAVIGATION (Print: Hidden) -->
        <div class="print:hidden flex items-center gap-2 overflow-x-auto no-scrollbar border-b border-gray-200 pb-2">
            <button type="button" @click="activeTab = 'monitoring-aset'"
                class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all shrink-0 flex items-center gap-2 border"
                :class="activeTab === 'monitoring-aset'
                    ? 'bg-primary-600 text-white border-primary-600 shadow-sm'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>1. Monitoring Aset</span>
            </button>

            <button type="button" @click="activeTab = 'verifikasi-rab'"
                class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all shrink-0 flex items-center gap-2 border"
                :class="activeTab === 'verifikasi-rab'
                    ? 'bg-primary-600 text-white border-primary-600 shadow-sm'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>2. Verifikasi RAB</span>
            </button>

            <button type="button" @click="activeTab = 'laporan-resmi'"
                class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all shrink-0 flex items-center gap-2 border"
                :class="activeTab === 'laporan-resmi'
                    ? 'bg-primary-600 text-white border-primary-600 shadow-sm'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span>3. Rekap Laporan</span>
            </button>

            <button type="button" @click="activeTab = 'kelola-petugas'"
                class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all shrink-0 flex items-center gap-2 border"
                :class="activeTab === 'kelola-petugas'
                    ? 'bg-primary-600 text-white border-primary-600 shadow-sm'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>4. Bengkel &amp; Toolman</span>
            </button>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 1: MONITORING EKSEKUTIF MULTI-BENGKEL                -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'monitoring-aset'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-primary-100 text-primary-700 font-black text-sm flex items-center justify-center">1</span>
                        Pengawasan Eksekutif Sarana Prasarana Multi-Jurusan
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Memahami indikator kesehatan inventaris seluruh jurusan di SMK Negeri 3 Yogyakarta secara real-time.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/50 space-y-2">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">Total Nilai &amp; Unit Aset</span>
                        <h4 class="font-bold text-sm text-gray-900">Totalitas Inventaris Sekolah</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Dashboard Waka Sarpras secara otomatis menjumlahkan nilai rupiah perolehan dan kuantitas unit inventaris di seluruh jurusan tanpa perlu rekapitulasi manual.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/50 space-y-2">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">Rasio Kerusakan Alat</span>
                        <h4 class="font-bold text-sm text-gray-900">Deteksi Alat Perlu Perbaikan</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Menampilkan grafik rasio barang dalam kondisi Baik vs Rusak. Membantu Waka Sarpras memetakan bengkel yang membutuhkan pemeliharaan berkala atau penggantian alat.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/50 space-y-2">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">Peringatan Stok BHP</span>
                        <h4 class="font-bold text-sm text-gray-900">Pencegahan Stock-Out Bahan</h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Memberikan sinyal dini atas bahan praktik yang stoknya berada di bawah batas minimum (<code class="font-mono text-primary-700">minimum_stok</code>) agar dapat segera dianggarkan dalam belanja sekolah.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 2: VERIFIKASI DOKUMEN RAB PENGADAAN                 -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'verifikasi-rab'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-primary-100 text-primary-700 font-black text-sm flex items-center justify-center">2</span>
                        Prosedur Telaah &amp; Verifikasi Dokumen RAB
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Hak dan wewenang Waka Sarpras dalam mengevaluasi usulan anggaran belanja bengkel kejuruan.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/40 space-y-1.5 text-xs sm:text-sm">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-600 text-white">Keputusan: Setujui (Approve)</span>
                        </div>
                        <p class="text-gray-700 leading-relaxed text-xs">
                            Digunakan saat usulan barang dan estimasi anggaran telah sesuai dengan prioritas sekolah. Dokumen terkunci permanen, klasifikasi barang terkunci (*anti-tampering*), dan siap dibelanjakan. Staf Toolman dapat melakukan konfirmasi kedatangan barang fisik (*receive*).
                        </p>
                    </div>

                    <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/40 space-y-1.5 text-xs sm:text-sm">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-600 text-white">Keputusan: Minta Perbaikan (Revisi)</span>
                        </div>
                        <p class="text-gray-700 leading-relaxed text-xs">
                            Digunakan jika rincian item, spesifikasi, atau perkiraan harga perlu disesuaikan oleh Toolman. Waka Sarpras wajib menyertakan <strong>catatan telaah (review note)</strong>. Dokumen dikembalikan ke bengkel pemohon untuk diperbaiki.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl border border-rose-200 bg-rose-50/40 space-y-1.5 text-xs sm:text-sm">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-600 text-white">Keputusan: Tolak (Reject)</span>
                        </div>
                        <p class="text-gray-700 leading-relaxed text-xs">
                            Digunakan apabila usulan pengadaan tidak dapat didanai atau tidak sesuai regulasi sekolah. Pengajuan ditutup dan tidak dapat diproses lebih lanjut.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 3: REKAPITULASI LAPORAN MUTASI & KONSUMSI           -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'laporan-resmi'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-primary-100 text-primary-700 font-black text-sm flex items-center justify-center">3</span>
                        Penerbitan Rekapitulasi Laporan Resmi Sekolah
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Laporan akuntabilitas untuk dinas pendidikan, komite sekolah, dan inspeksi audit sarpras.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="border border-gray-200 rounded-xl p-5 space-y-2">
                        <h3 class="font-bold text-sm text-gray-900">Laporan Mutasi Aset Inventaris</h3>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Merekam seluruh arus barang masuk (dari realisasi RAB), pengembalian baik/rusak, dan pencatatan barang hilang di seluruh bengkel dalam periode tertentu. Dapat diekspor ke Excel dan PDF siap cetak.
                        </p>
                    </div>

                    <div class="border border-gray-200 rounded-xl p-5 space-y-2">
                        <h3 class="font-bold text-sm text-gray-900">Laporan Konsumsi Bahan (BHP)</h3>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Merekam volume pemakaian bahan habis pakai per bengkel kejuruan. Membantu Waka Sarpras mengevaluasi efisiensi penggunaan bahan praktik siswa per semester.
                        </p>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-gray-200 text-xs text-gray-700 space-y-1">
                    <p class="font-bold text-gray-900">Standar Kertas Cetak Resmi:</p>
                    <p class="text-gray-600">
                        Seluruh format dokumen cetak resmi (Laporan Mutasi, Konsumsi Bahan, Kartu Barang, dan Bukti Transaksi) telah diatur presisi untuk ukuran kertas <strong>Folio / F4 (215 &times; 330 mm)</strong> sesuai regulasi administrasi sekolah kejuruan.
                    </p>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 4: TATA KELOLA BENGKEL & STAF TOOLMAN                -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'kelola-petugas'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-primary-100 text-primary-700 font-black text-sm flex items-center justify-center">4</span>
                        Tata Kelola Master Unit Bengkel &amp; Penugasan Toolman
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Pengelolaan master organisasi sekolah dan hak akses teknisi bengkel.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/50 space-y-1.5 text-xs sm:text-sm">
                        <h4 class="font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-primary-600"></span>
                            Master Data Unit Bengkel
                        </h4>
                        <p class="text-gray-600 leading-relaxed text-xs">
                            Waka Sarpras dapat mendaftarkan unit bengkel baru (misal penambahan lab kejuruan baru). Kode bengkel bersifat unik (contoh: <code class="font-mono">BGK-TKJ</code>, <code class="font-mono">BGK-AV</code>).
                        </p>
                    </div>

                    <div class="p-4 rounded-xl border border-gray-200 bg-slate-50/50 space-y-1.5 text-xs sm:text-sm">
                        <h4 class="font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            Penugasan &amp; Manajemen Akun Toolman
                        </h4>
                        <p class="text-gray-600 leading-relaxed text-xs">
                            Setiap staf Toolman wajib ditugaskan ke satu unit bengkel tertentu (<code class="font-mono">bengkel_id</code>). Apabila terdapat pergantian staf atau staf lupa password, Waka Sarpras dapat mereset kata sandi staf secara mandiri dari menu Akun Toolman.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
