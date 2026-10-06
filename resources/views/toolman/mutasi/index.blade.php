@extends('layouts.admin')

@section('title', 'Riwayat Mutasi & Pergerakan Stok')
@section('header_title', 'Riwayat Perubahan Stok & Mutasi')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header & Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:hidden">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                        {{ $bengkel->nama ?? 'Bengkel Aktif' }}
                    </span>
                    <span class="text-xs text-gray-400">&bull; Kode: {{ $bengkel->kode ?? 'BGL' }}</span>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 tracking-tight">Log Mutasi & Pergerakan Stok</h3>
                <p class="text-xs text-gray-500 mt-1">Audit log otomatis dari setiap transaksi stok masuk, peminjaman,
                    pengembalian fisik, dan bahan habis pakai (PRD 3.10).</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- Tombol Cetak Dokumen Resmi (Format A4 Cetak Formal) -->
                <a href="{{ route('toolman.mutasi.print', request()->query()) }}" target="_blank"
                    class="inline-flex items-center px-3.5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg shadow-sm transition-colors"
                    title="Buka lembar cetak dokumen resmi berstandar dinas (format A4 landscape)">
                    <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    Cetak Log
                </a>

                <!-- Tombol Ekspor Excel (.xls) Berwarna & Terformat Penuh -->
                <a href="{{ route('toolman.mutasi.export-excel', request()->query()) }}"
                    class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors"
                    title="Unduh laporan dalam format spreadsheet Microsoft Excel dengan kartu ringkasan dan styling penuh">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Ekspor Excel (.xls)
                </a>
            </div>
        </div>

        <!-- 4 Kartu Metrik Ringkasan Mutasi Bengkel -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 print:hidden">
            <!-- Total Log Mutasi -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Catatan Log</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_records']) }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Semua audit transaksi</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-gray-100 text-gray-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                </div>
            </div>

            <!-- Stok Masuk (+) -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Total Stok Masuk</p>
                    <p class="text-2xl font-bold text-emerald-900 mt-1">+{{ number_format($stats['total_masuk']) }}</p>
                    <p class="text-[11px] text-emerald-600/80 mt-0.5">Pengadaan & fisik baru</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10">
                        </path>
                    </svg>
                </div>
            </div>

            <!-- Sirkulasi Keluar (-) -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Sirkulasi Keluar</p>
                    <p class="text-2xl font-bold text-blue-900 mt-1">-{{ number_format($stats['total_keluar']) }}</p>
                    <p class="text-[11px] text-blue-600/80 mt-0.5">Pinjaman aktif & BHP keluar</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                </div>
            </div>

            <!-- Masalah Fisik (Rusak / Hilang) -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Rusak & Hilang</p>
                    <p class="text-2xl font-bold text-rose-900 mt-1">{{ number_format($stats['total_masalah']) }}</p>
                    <p class="text-[11px] text-rose-600/80 mt-0.5">Kerusakan fisik / hilang</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Filter Form Controls -->
        <form method="GET" action="{{ route('toolman.mutasi.index') }}"
            class="bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-sm space-y-4 print:hidden">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
                <!-- Search Barang & Catatan -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Cari Log Mutasi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Nama barang, kode, keterangan, atau pemroses..."
                            class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm sm:text-xs focus:ring-primary-500 focus:border-primary-500 bg-white transition-colors">
                    </div>
                </div>

                <!-- Jenis Mutasi (PRD 3.10) -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Jenis Mutasi</label>
                    <select name="jenis" onchange="this.form.submit()"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm sm:text-xs focus:ring-primary-500 focus:border-primary-500 bg-white transition-colors">
                        <option value="">Semua Jenis Mutasi</option>
                        <option value="stok_masuk" {{ request('jenis') === 'stok_masuk' ? 'selected' : '' }}>Stok Masuk / Pengadaan</option>
                        <option value="peminjaman" {{ request('jenis') === 'peminjaman' ? 'selected' : '' }}>Peminjaman Keluar</option>
                        <option value="pengembalian_baik" {{ request('jenis') === 'pengembalian_baik' ? 'selected' : '' }}>Pengembalian Baik</option>
                        <option value="pengembalian_rusak" {{ request('jenis') === 'pengembalian_rusak' ? 'selected' : '' }}>Pengembalian Rusak</option>
                        <option value="barang_hilang" {{ request('jenis') === 'barang_hilang' ? 'selected' : '' }}>Barang Hilang</option>
                        <option value="bhp_keluar" {{ request('jenis') === 'bhp_keluar' ? 'selected' : '' }}>Pengeluaran BHP</option>
                        <option value="perbaikan" {{ request('jenis') === 'perbaikan' ? 'selected' : '' }}>Perbaikan Alat</option>
                        <option value="penyesuaian" {{ request('jenis') === 'penyesuaian' ? 'selected' : '' }}>Penyesuaian / Opname</option>
                    </select>
                </div>

                <!-- Tipe Barang -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Tipe Barang</label>
                    <select name="tipe" onchange="this.form.submit()"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm sm:text-xs focus:ring-primary-500 focus:border-primary-500 bg-white transition-colors">
                        <option value="">Semua Tipe</option>
                        <option value="inventaris" {{ request('tipe') === 'inventaris' ? 'selected' : '' }}>Inventaris (Alat Praktik)</option>
                        <option value="bhp" {{ in_array(request('tipe'), ['bhp', 'habis_pakai']) ? 'selected' : '' }}>Habis Pakai (BHP)</option>
                    </select>
                </div>

                <!-- Periode Preset -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Periode Waktu</label>
                    <select name="period" onchange="this.form.submit()"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm sm:text-xs focus:ring-primary-500 focus:border-primary-500 bg-white transition-colors">
                        <option value="">Semua Rentang</option>
                        <option value="today" {{ request('period') === 'today' ? 'selected' : '' }}>Hari Ini</option>
                        <option value="week" {{ request('period') === 'week' ? 'selected' : '' }}>7 Hari Terakhir</option>
                        <option value="month" {{ request('period') === 'month' ? 'selected' : '' }}>30 Hari Terakhir</option>
                    </select>
                </div>
            </div>

            <!-- Rentang Tanggal Kustom (Jika tidak menggunakan preset) -->
            @if (!request('period'))
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase mb-1">Mulai Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                            onchange="this.form.submit()"
                            class="block w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-primary-500 focus:border-primary-500 bg-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" onchange="this.form.submit()"
                            class="block w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-primary-500 focus:border-primary-500 bg-white transition-colors">
                    </div>
                </div>
            @endif

            <!-- Hidden Submit for Enter Key Accessibility -->
            <button type="submit" class="hidden" aria-hidden="true"></button>

            @if (request()->anyFilled(['search', 'jenis', 'tipe', 'period', 'start_date', 'end_date']))
                <!-- Status & Tombol Reset Tunggal -->
                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 text-xs text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200 font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Filter aktif diterapkan ({{ $movements->total() }} data ditemukan)
                        </span>
                        <span class="text-xs text-gray-400 hidden sm:inline">&bull; Hasil diperbarui secara dinamis</span>
                    </div>

                    <a href="{{ route('toolman.mutasi.index') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                        Reset Filter
                    </a>
                </div>
            @endif
        </form>

        <!-- Header Cetak Khusus Print Media -->
        <div class="hidden print:block mb-6">
            <h2 class="text-xl font-bold text-gray-900">Laporan Riwayat Mutasi Stok Bengkel</h2>
            <p class="text-sm text-gray-600">Unit Bengkel: {{ $bengkel->nama ?? 'Bengkel' }} ({{ $bengkel->kode ?? 'BGL' }})</p>
            <p class="text-xs text-gray-500 mt-1">Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB &bull; Oleh: {{ auth()->user()->name }}</p>
        </div>

        <!-- Table Log Mutasi -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left">
                    <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3.5">Waktu & ID Log</th>
                            <th class="px-6 py-3.5">Barang & Kode</th>
                            <th class="px-6 py-3.5 text-center">Jenis Mutasi</th>
                            <th class="px-6 py-3.5 text-center">Perubahan Qty</th>
                            <th class="px-6 py-3.5">Petugas / Aktor</th>
                            <th class="px-6 py-3.5">Keterangan & Referensi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse ($movements as $m)
                            @php
                                $jenisConfig = match ($m->jenis) {
                                    'stok_masuk' => [
                                        'label' => 'Stok Masuk',
                                        'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'sign' => '+',
                                        'sign_class' => 'text-emerald-700 font-bold',
                                    ],
                                    'peminjaman' => [
                                        'label' => 'Peminjaman',
                                        'class' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'sign' => '-',
                                        'sign_class' => 'text-blue-700 font-bold',
                                    ],
                                    'pengembalian_baik' => [
                                        'label' => 'Kembali (Baik)',
                                        'class' => 'bg-teal-50 text-teal-700 border-teal-200',
                                        'sign' => '+',
                                        'sign_class' => 'text-teal-700 font-bold',
                                    ],
                                    'pengembalian_rusak' => [
                                        'label' => 'Kembali (Rusak)',
                                        'class' => 'bg-orange-50 text-orange-700 border-orange-200',
                                        'sign' => '',
                                        'sign_class' => 'text-orange-700 font-bold',
                                    ],
                                    'barang_hilang' => [
                                        'label' => 'Barang Hilang',
                                        'class' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'sign' => '-',
                                        'sign_class' => 'text-rose-700 font-bold',
                                    ],
                                    'bhp_keluar' => [
                                        'label' => 'BHP Keluar',
                                        'class' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'sign' => '-',
                                        'sign_class' => 'text-amber-700 font-bold',
                                    ],
                                    'perbaikan' => [
                                        'label' => 'Perbaikan Alat',
                                        'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                        'sign' => '±',
                                        'sign_class' => 'text-indigo-700 font-bold',
                                    ],
                                    'penyesuaian' => match ($m->referensi_tipe) {
                                        'penyesuaian_tambah' => [
                                            'label' => 'Penyesuaian (+)',
                                            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'sign' => '+',
                                            'sign_class' => 'text-emerald-700 font-bold',
                                        ],
                                        'penyesuaian_kurang', 'penyesuaian_rusak_kurang' => [
                                            'label' => 'Penyesuaian (-)',
                                            'class' => 'bg-rose-50 text-rose-700 border-rose-200',
                                            'sign' => '-',
                                            'sign_class' => 'text-rose-700 font-bold',
                                        ],
                                        default => [
                                            'label' => 'Penyesuaian',
                                            'class' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'sign' => '±',
                                            'sign_class' => 'text-purple-700 font-bold',
                                        ],
                                    },
                                    default => [
                                        'label' => ucfirst(str_replace('_', ' ', $m->jenis)),
                                        'class' => 'bg-gray-100 text-gray-700 border-gray-200',
                                        'sign' => '',
                                        'sign_class' => 'text-gray-700',
                                    ],
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <!-- Waktu & ID -->
                                <td class="px-6 py-4 whitespace-nowrap align-top">
                                    <span class="font-mono text-xs text-gray-500 font-semibold block">
                                        #LOG-{{ str_pad($m->id, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <p class="text-xs text-gray-900 font-medium mt-0.5">
                                        {{ $m->created_at->translatedFormat('d M Y') }}
                                    </p>
                                    <p class="text-[11px] text-gray-400">
                                        {{ $m->created_at->format('H:i') }} WIB
                                    </p>
                                </td>

                                <!-- Barang & Kode -->
                                <td class="px-6 py-4 align-top">
                                    @if ($m->barang)
                                        <div class="font-bold text-gray-900">
                                            <a href="{{ route('toolman.barang.edit', $m->barang->id) }}"
                                                class="hover:text-primary-600 transition-colors">
                                                {{ $m->barang->nama }}
                                            </a>
                                        </div>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span
                                                class="font-mono text-xs text-gray-500 font-medium">{{ $m->barang->kode_barang }}</span>
                                            <span class="text-gray-300">&bull;</span>
                                            <span
                                                class="text-[11px] px-1.5 py-0.2 rounded {{ ($m->barang->jenis_barang ?? '') === 'bhp' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }} uppercase font-semibold">
                                                {{ ($m->barang->jenis_barang ?? '') === 'bhp' ? 'BHP' : 'INVENTARIS' }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Barang Terhapus</span>
                                    @endif
                                </td>

                                <!-- Jenis Mutasi -->
                                <td class="px-6 py-4 text-center whitespace-nowrap align-top">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $jenisConfig['class'] }}">
                                        {{ $jenisConfig['label'] }}
                                    </span>
                                </td>

                                <!-- Perubahan Qty -->
                                <td class="px-6 py-4 text-center whitespace-nowrap align-top">
                                    <span class="text-sm font-bold {{ $jenisConfig['sign_class'] }}">
                                        {{ $jenisConfig['sign'] }}{{ $m->jumlah }}
                                    </span>
                                    <span class="text-xs text-gray-500 block">
                                        {{ $m->barang->satuan ?? 'unit' }}
                                    </span>
                                </td>

                                <!-- Petugas / Aktor -->
                                <td class="px-6 py-4 align-top whitespace-nowrap">
                                    <p class="font-medium text-gray-900 text-xs">{{ $m->user->name ?? 'Sistem Otomatis' }}
                                    </p>
                                    <span class="inline-block text-[10px] font-semibold uppercase px-1.5 py-0.2 bg-gray-100 text-gray-600 rounded mt-0.5">
                                        {{ $m->user->role ?? 'SISTEM' }}
                                    </span>
                                </td>

                                <!-- Keterangan & Referensi -->
                                <td class="px-6 py-4 align-top max-w-sm">
                                    <p class="text-xs text-gray-700 leading-relaxed">
                                        {{ $m->keterangan ?? '-' }}
                                    </p>
                                    @if ($m->referensi_tipe && $m->referensi_id)
                                        @if (in_array($m->referensi_tipe, ['peminjaman', 'peminjamans']))
                                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                                <a href="{{ route('toolman.peminjaman.show', $m->referensi_id) }}"
                                                    class="inline-flex items-center text-[10px] font-mono font-bold text-primary-600 hover:text-primary-800 bg-primary-50 px-2 py-0.5 rounded border border-primary-200 transition-colors">
                                                    Tiket #TRX-{{ str_pad($m->referensi_id, 4, '0', STR_PAD_LEFT) }} &rarr;
                                                </a>
                                                @if ($m->jenis === 'stok_keluar')
                                                    <a href="{{ route('toolman.pengembalian.print-pinjam', $m->referensi_id) }}" target="_blank"
                                                        class="inline-flex items-center text-[10px] font-medium text-blue-700 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-1.5 py-0.5 rounded border border-blue-200 transition-colors"
                                                        title="Cetak Bon Pinjam Alat/Bahan">
                                                        <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                                            </path>
                                                        </svg>
                                                        Bukti Pinjam
                                                    </a>
                                                @elseif ($m->jenis === 'stok_masuk')
                                                    <a href="{{ route('toolman.pengembalian.print-kembali', $m->referensi_id) }}" target="_blank"
                                                        class="inline-flex items-center text-[10px] font-medium text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 px-1.5 py-0.5 rounded border border-emerald-200 transition-colors"
                                                        title="Cetak Bukti Pengembalian">
                                                        <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z">
                                                            </path>
                                                        </svg>
                                                        Bukti Kembali
                                                    </a>
                                                @endif
                                            </div>
                                        @else
                                            <span
                                                class="inline-flex items-center text-[10px] font-mono text-gray-500 bg-gray-100 px-2 py-0.5 rounded mt-1">
                                                Ref: {{ ucfirst($m->referensi_tipe) }} #{{ $m->referensi_id }}
                                            </span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="max-w-sm mx-auto">
                                        <div
                                            class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mx-auto text-gray-400 mb-3">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                        </div>
                                        <h4 class="text-sm font-bold text-gray-900">Belum Ada Catatan Mutasi</h4>
                                        <p class="text-xs text-gray-500 mt-1">Tidak ditemukan riwayat mutasi stok untuk
                                            rentang filter yang dipilih.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($movements->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 print:hidden">
                    {{ $movements->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
