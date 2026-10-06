@extends('layouts.peminjam')

@section('title', 'Detail Tiket #' . str_pad($peminjaman->id, 4, '0', STR_PAD_LEFT))

@section('content')
    @php
        $totalItems = $peminjaman->detailPeminjamans->count();
        $totalQty = $peminjaman->detailPeminjamans->sum('jumlah');
        $isInventaris = $peminjaman->detailPeminjamans->some(
            fn($d) => $d->barang && $d->barang->jenis_barang === 'inventaris',
        );
        $isLate =
            $peminjaman->status === 'terlambat' ||
            ($peminjaman->status === 'active' &&
                $peminjaman->batas_kembali &&
                \Carbon\Carbon::parse($peminjaman->batas_kembali)->isPast());

        $statusConfig = match ($peminjaman->status) {
            'pending' => [
                'label' => 'Menunggu Persetujuan Toolman',
                'class' => 'bg-amber-100 text-amber-900 border-amber-300',
                'dot' => 'bg-amber-600 animate-pulse',
                'desc' =>
                    'Pengajuan peminjaman Anda telah terkirim dan sedang menunggu verifikasi serta persetujuan dari Toolman bengkel.',
            ],
            'disetujui' => [
                'label' => 'Jadwal Disetujui (Menunggu Pengambilan)',
                'class' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                'dot' => 'bg-emerald-600 animate-pulse',
                'desc' =>
                    'Jadwal peminjaman dan kuota barang telah disetujui serta diamankan oleh Toolman. Silakan datang ke bengkel pada waktu pengambilan untuk serah terima barang fisik.',
            ],
            'active' => [
                'label' => $isLate ? 'Terlambat Dikembalikan' : 'Sedang Dipinjam (Aktif)',
                'class' => $isLate
                    ? 'bg-red-100 text-red-900 border-red-300'
                    : 'bg-blue-100 text-blue-900 border-blue-300',
                'dot' => $isLate ? 'bg-red-600 animate-pulse' : 'bg-blue-600 animate-pulse',
                'desc' => $isLate
                    ? 'Batas waktu pengembalian alat telah terlewati. Harap segera kembalikan peralatan fisik ke bengkel untuk menghindari sanksi penangguhan akun.'
                    : 'Alat praktik sedang Anda gunakan. Harap jaga kondisi barang dengan baik dan kembalikan sebelum batas waktu yang ditentukan.',
            ],
            'menunggu_pengecekan' => [
                'label' => 'Menunggu Pengecekan Fisik',
                'class' => 'bg-purple-100 text-purple-900 border-purple-300',
                'dot' => 'bg-purple-600',
                'desc' =>
                    'Anda telah mengajukan pengembalian. Silakan bawa peralatan fisik ke meja Toolman untuk pengecekan kondisi baik, rusak, atau kelengkapannya.',
            ],
            'selesai' => [
                'label' => 'Peminjaman Selesai',
                'class' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                'dot' => 'bg-emerald-600',
                'desc' =>
                    'Seluruh rangkaian peminjaman telah selesai dan kondisi barang telah diverifikasi oleh Toolman bengkel.',
            ],
            'ditolak' => [
                'label' => 'Pengajuan Ditolak',
                'class' => 'bg-rose-100 text-rose-900 border-rose-300',
                'dot' => 'bg-rose-600',
                'desc' => 'Pengajuan peminjaman ini tidak dapat disetujui oleh Toolman bengkel.',
            ],
            default => [
                'label' => ucfirst($peminjaman->status),
                'class' => 'bg-gray-100 text-gray-800 border-gray-300',
                'dot' => 'bg-gray-500',
                'desc' => '',
            ],
        };
    @endphp

    <div class="max-w-5xl mx-auto space-y-6 pb-12">

        <!-- Flash Notifications -->
        @if (session('success'))
            <div
                class="bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl p-4 flex items-start gap-3 shadow-xs">
                <div
                    class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="flex-1 text-xs sm:text-sm">
                    <h4 class="font-bold">Berhasil!</h4>
                    <p class="text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-900 rounded-2xl p-4 flex items-start gap-3 shadow-xs">
                <div
                    class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div class="flex-1 text-xs sm:text-sm">
                    <h4 class="font-bold">Peringatan:</h4>
                    <p class="text-rose-700 mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Breadcrumb & Top Action -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <nav class="flex items-center text-xs font-medium text-gray-500 mb-2 space-x-2">
                    <a href="{{ route('peminjam.katalog.index') }}"
                        class="hover:text-primary-600 transition-colors">Katalog</a>
                    <span>/</span>
                    <a href="{{ route('peminjam.tiket.index') }}" class="hover:text-primary-600 transition-colors">Tiket
                        Saya</a>
                    <span>/</span>
                    <span
                        class="text-gray-800 font-semibold">#TRX-{{ str_pad($peminjaman->id, 4, '0', STR_PAD_LEFT) }}</span>
                </nav>
                <div class="flex flex-wrap items-center gap-3">
                    <h3 class="text-2xl font-bold text-gray-900 tracking-tight">
                        Tiket #TRX-{{ str_pad($peminjaman->id, 4, '0', STR_PAD_LEFT) }}
                    </h3>
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border shadow-2xs {{ $statusConfig['class'] }}">
                        <span class="w-2 h-2 rounded-full mr-2 {{ $statusConfig['dot'] }}"></span>
                        {{ $statusConfig['label'] }}
                    </span>
                </div>
                <div class="flex items-center gap-3 text-xs text-gray-600 mt-2">
                    <span class="font-medium text-gray-700">Bengkel: <strong
                            class="text-gray-900">{{ $peminjaman->bengkel->nama }}</strong></span>
                    <span class="text-gray-300">&bull;</span>
                    <span class="text-gray-500">Diajukan:
                        {{ \Carbon\Carbon::parse($peminjaman->tanggal_pinjam)->translatedFormat('d F Y, H:i') }} WIB</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('peminjam.tiket.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl shadow-xs transition-colors">
                    <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali ke Tiket Saya
                </a>

                @if (in_array($peminjaman->status, ['active', 'terlambat', 'menunggu_pengecekan', 'selesai']))
                    <a href="{{ route('peminjam.tiket.print-pinjam', $peminjaman->id) }}" target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 text-gray-700 text-xs font-semibold rounded-xl shadow-xs transition-colors">
                        <svg class="w-4 h-4 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                            </path>
                        </svg>
                        Cetak Bukti Pinjam
                    </a>
                @endif

                @if ($peminjaman->status === 'selesai')
                    <a href="{{ route('peminjam.tiket.print-kembali', $peminjaman->id) }}" target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-300 text-gray-700 text-xs font-semibold rounded-xl shadow-xs transition-colors">
                        <svg class="w-4 h-4 mr-1.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Cetak Bukti Kembali
                    </a>
                @endif

                @if (in_array($peminjaman->status, ['active', 'terlambat']))
                    <form method="POST" action="{{ route('peminjam.tiket.kembalikan', $peminjaman->id) }}"
                        data-confirm="true"
                        data-title="Konfirmasi Pengembalian Alat"
                        data-message="Apakah Anda yakin siap menyerahkan seluruh alat fisik tiket <strong>#TRX-{{ str_pad($peminjaman->id, 4, '0', STR_PAD_LEFT) }}</strong> ke meja Toolman sekarang?"
                        data-submessage="Pastikan semua barang fisik dibawa lengkap beserta kelengkapannya ke ruang bengkel."
                        data-type="primary"
                        data-confirm-text="Ya, Ajukan Pengembalian">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                </path>
                            </svg>
                            Ajukan Pengembalian
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Status Alert Banner -->
        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-xs space-y-3">
            <div class="flex items-start gap-3.5">
                <div
                    class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 border {{ $statusConfig['class'] }}">
                    @if ($peminjaman->status === 'selesai')
                        <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @elseif ($peminjaman->status === 'ditolak')
                        <svg class="w-5 h-5 text-rose-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    @elseif ($isLate)
                        <svg class="w-5 h-5 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    @elseif ($peminjaman->status === 'menunggu_pengecekan')
                        <svg class="w-5 h-5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                            </path>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @endif
                </div>
                <div class="flex-1">
                    <h4 class="text-base font-bold text-gray-900">{{ $statusConfig['label'] }}</h4>
                    <p class="text-xs text-gray-600 mt-0.5 leading-relaxed">{{ $statusConfig['desc'] }}</p>
                </div>
            </div>

            @if ($peminjaman->status === 'ditolak' && $peminjaman->alasan_penolakan)
                <div class="bg-rose-50 border border-rose-200 rounded-xl p-3.5 text-xs text-rose-950 shadow-2xs">
                    <span class="font-bold block mb-0.5">Alasan Penolakan dari Toolman:</span>
                    <p class="italic leading-relaxed font-medium">"{{ $peminjaman->alasan_penolakan }}"</p>
                </div>
            @endif
        </div>

        <!-- 2-Column Info Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Card 1: Informasi Peminjaman -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-xs p-6 space-y-4">
                <h4
                    class="text-sm font-bold text-gray-800 uppercase tracking-wider border-b border-gray-100 pb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Rincian Informasi Pengajuan
                </h4>

                <div class="space-y-3 text-sm">
                    <div
                        class="flex flex-col sm:flex-row justify-between sm:items-center py-2 border-b border-gray-50 gap-1">
                        <span class="text-gray-500 text-xs font-medium">Bengkel Asal Barang:</span>
                        <span class="font-semibold text-gray-900">
                            {{ $peminjaman->bengkel->nama }} ({{ $peminjaman->bengkel->kode }})
                        </span>
                    </div>

                    <div
                        class="flex flex-col sm:flex-row justify-between sm:items-center py-2 border-b border-gray-50 gap-1">
                        <span class="text-gray-500 text-xs font-medium">Waktu Pengajuan:</span>
                        <span class="font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($peminjaman->tanggal_pinjam)->translatedFormat('d F Y, H:i') }} WIB
                        </span>
                    </div>

                    <div
                        class="flex flex-col sm:flex-row justify-between sm:items-center py-2 border-b border-gray-50 gap-1">
                        <span class="text-gray-500 text-xs font-medium">Batas Pengembalian (Deadline):</span>
                        <div>
                            @if ($peminjaman->batas_kembali)
                                <span class="font-bold {{ $isLate ? 'text-rose-600' : 'text-gray-900' }}">
                                    {{ \Carbon\Carbon::parse($peminjaman->batas_kembali)->translatedFormat('d F Y, H:i') }}
                                    WIB
                                </span>
                                @if ($isLate)
                                    <span class="text-[11px] font-bold text-rose-600 block sm:text-right">(Melewati Batas
                                        Waktu)</span>
                                @endif
                            @else
                                <span class="font-semibold text-emerald-700">Tidak Perlu Pengembalian (Hanya BHP)</span>
                            @endif
                        </div>
                    </div>

                    <div class="pt-2">
                        <span class="text-gray-500 text-xs font-medium block mb-1">Keperluan Penggunaan / Praktik:</span>
                        <div
                            class="bg-gray-50 p-3.5 rounded-xl border border-gray-200 text-xs text-gray-800 leading-relaxed italic">
                            "{{ $peminjaman->keperluan }}"
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Status Verifikasi & Petugas -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-6 flex flex-col justify-between space-y-4">
                <div>
                    <h4
                        class="text-sm font-bold text-gray-800 uppercase tracking-wider border-b border-gray-100 pb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Verifikasi Petugas
                    </h4>

                    <div class="space-y-3 pt-2 text-xs">
                        <div>
                            <span class="text-gray-500 block">Petugas Toolman:</span>
                            <strong class="text-gray-900 text-sm block mt-0.5">
                                {{ $peminjaman->diprosesOleh->name ?? 'Belum Ditugaskan' }}
                            </strong>
                            <span class="text-gray-400 text-[11px]">{{ $peminjaman->diprosesOleh->email ?? '-' }}</span>
                        </div>

                        <div class="pt-2 border-t border-gray-100">
                            <span class="text-gray-500 block">Waktu Diproses:</span>
                            <span class="font-medium text-gray-800 mt-0.5 block">
                                {{ $peminjaman->diproses_pada ? \Carbon\Carbon::parse($peminjaman->diproses_pada)->translatedFormat('d F Y, H:i') . ' WIB' : 'Menunggu antrean review' }}
                            </span>
                        </div>

                        <div class="pt-2 border-t border-gray-100 space-y-1.5">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Macam Barang:</span>
                                <span class="font-bold text-gray-900">{{ $totalItems }} Item</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Total Unit Barang:</span>
                                <span class="font-bold text-gray-900">{{ $totalQty }} Satuan</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-500">Status Terkini:</span>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full font-bold {{ $statusConfig['class'] }}">
                        {{ $statusConfig['label'] }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Table Rincian Barang -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden">
            <div
                class="p-5 border-b border-gray-200 flex flex-col sm:flex-row justify-between sm:items-center gap-2 bg-gray-50/70">
                <div>
                    <h4 class="text-sm font-bold text-gray-900">Daftar Barang yang Dipinjam</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Rincian alat inventaris, bahan habis pakai, lokasi penyimpanan,
                        serta hasil pengecekan fisik.</p>
                </div>
                <span
                    class="text-xs font-bold bg-white text-gray-700 px-3 py-1 rounded-xl border border-gray-200 shadow-2xs self-start sm:self-center">
                    {{ $totalItems }} Item Terdaftar
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left">
                    <thead
                        class="bg-slate-50 text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 w-12 text-center">No</th>
                            <th class="px-6 py-4">Nama Barang & Kode</th>
                            <th class="px-6 py-4">Kategori & Lokasi</th>
                            <th class="px-6 py-4 text-center">Jumlah Diminta</th>
                            @if ($peminjaman->status === 'selesai')
                                <th class="px-6 py-4 text-center">Kondisi Pengembalian</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse ($peminjaman->detailPeminjamans as $idx => $detail)
                            @php
                                $barang = $detail->barang;
                                $isInv = $barang?->jenis_barang === 'inventaris';
                            @endphp
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-6 py-4 text-center text-xs text-gray-500 font-mono font-medium">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 text-sm">
                                        {{ $barang->nama ?? 'Barang Terhapus' }}
                                    </div>
                                    <span
                                        class="inline-flex items-center text-[11px] font-mono font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded mt-1 border border-gray-200">
                                        {{ $barang->kode_barang ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        @if ($isInv)
                                            <span
                                                class="inline-flex items-center gap-1.5 text-[10px] font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                                <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                Alat Inventaris
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1.5 text-[10px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                                <svg class="w-3 h-3 text-amber-600 shrink-0" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                                Bahan Habis Pakai
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                            </path>
                                        </svg>
                                        <span>{{ $barang->lokasiPenyimpanan->nama ?? 'Gudang Bengkel' }}</span>
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="font-bold text-gray-900 text-sm">{{ $detail->jumlah }}</span>
                                    <span
                                        class="text-xs font-medium text-gray-500 block">{{ $barang->satuan ?? 'Unit' }}</span>
                                </td>
                                @if ($peminjaman->status === 'selesai')
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if ($isInv)
                                            <div class="inline-flex items-center gap-2 text-xs">
                                                <span
                                                    class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 font-bold border border-emerald-200"
                                                    title="Kondisi Baik">
                                                    {{ $detail->jumlah_baik }} Baik
                                                </span>
                                                @if ($detail->jumlah_rusak > 0)
                                                    <span
                                                        class="px-2 py-0.5 rounded bg-amber-50 text-amber-800 font-bold border border-amber-200"
                                                        title="Kondisi Rusak">
                                                        {{ $detail->jumlah_rusak }} Rusak
                                                    </span>
                                                @endif
                                                @if ($detail->jumlah_hilang > 0)
                                                    <span
                                                        class="px-2 py-0.5 rounded bg-rose-50 text-rose-800 font-bold border border-rose-200"
                                                        title="Hilang">
                                                        {{ $detail->jumlah_hilang }} Hilang
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">BHP Digunakan</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $peminjaman->status === 'selesai' ? '5' : '4' }}"
                                    class="px-6 py-10 text-center text-xs text-gray-500">
                                    Tidak ada item barang terdaftar dalam tiket ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
