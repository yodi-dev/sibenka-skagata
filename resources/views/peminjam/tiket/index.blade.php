@extends('layouts.peminjam')

@section('title', 'Tiket Peminjaman Saya')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- Flash Notification Alerts -->
        @if (session('success'))
            <script>
                try {
                    localStorage.removeItem('sibenka_cart');
                    window.dispatchEvent(new CustomEvent('cart-updated'));
                } catch (e) {}
            </script>
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

        <!-- Header & Search Controls -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Tiket Peminjaman Saya</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Pantau status verifikasi, batas pengembalian, dan riwayat
                    sirkulasi alat praktik.</p>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('peminjam.tiket.index') }}" class="w-full sm:w-72">
                @if (request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari ID tiket, nama barang..."
                        class="block w-full pl-9 pr-8 py-2 bg-white border border-gray-300 rounded-xl text-xs focus:ring-primary-500 focus:border-primary-500 shadow-2xs">
                    @if (request('search'))
                        <a href="{{ route('peminjam.tiket.index', array_merge(request()->query(), ['search' => null])) }}"
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600">
                            &times;
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Status Tabs Bar -->
        <div
            class="bg-gray-100/80 p-1.5 rounded-2xl border border-gray-200/80 overflow-x-auto no-scrollbar flex items-center gap-1.5 text-xs font-semibold">
            @php
                $tabs = [
                    'all' => ['label' => 'Semua Tiket', 'count' => $counts['all']],
                    'pending' => ['label' => 'Menunggu Acc', 'count' => $counts['pending']],
                    'disetujui' => ['label' => 'Jadwal Disetujui', 'count' => $counts['disetujui'] ?? 0],
                    'active' => ['label' => 'Sedang Dipinjam', 'count' => $counts['active']],
                    'menunggu_pengecekan' => ['label' => 'Cek Fisik', 'count' => $counts['menunggu_pengecekan']],
                    'selesai' => ['label' => 'Selesai', 'count' => $counts['selesai']],
                    'ditolak' => ['label' => 'Ditolak', 'count' => $counts['ditolak']],
                ];
            @endphp

            @foreach ($tabs as $key => $tab)
                <a href="{{ route('peminjam.tiket.index', array_merge(request()->query(), ['status' => $key, 'page' => 1])) }}"
                    class="px-3 py-1.5 rounded-xl shrink-0 transition-colors flex items-center gap-1.5 {{ $filterStatus === $key ? 'bg-primary-600 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900 hover:bg-white/80' }}">
                    <span>{{ $tab['label'] }}</span>
                    <span
                        class="text-[10px] px-1.5 py-0.5 rounded-full {{ $filterStatus === $key ? 'bg-primary-800 text-white' : 'bg-gray-200/80 text-gray-600' }}">
                        {{ $tab['count'] }}
                    </span>
                </a>
            @endforeach
        </div>

        <!-- List of Real Tickets -->
        <div class="space-y-4">
            @forelse ($peminjamans as $tiket)
                @php
                    $isInventaris = $tiket->detailPeminjamans->some(
                        fn($d) => $d->barang && $d->barang->jenis_barang === 'inventaris',
                    );
                    $batasKembali = $tiket->batas_kembali ? \Carbon\Carbon::parse($tiket->batas_kembali) : null;
                    $isPastDeadline = $batasKembali && $batasKembali->isPast();
                    $isLate = $tiket->status === 'terlambat' || ($tiket->status === 'active' && $isPastDeadline);
                    $isDueSoon =
                        $tiket->status === 'active' &&
                        $batasKembali &&
                        !$isPastDeadline &&
                        $batasKembali->diffInHours(now()) <= 6;

                    $statusConfig = match ($tiket->status) {
                        'pending' => [
                            'label' => 'Menunggu Verifikasi',
                            'badge' => 'bg-amber-50 text-amber-800 border-amber-200',
                            'dot' => 'bg-amber-500 animate-pulse',
                            'header' => 'border-amber-100 bg-amber-50/40',
                        ],
                        'disetujui' => [
                            'label' => 'Jadwal Disetujui (Siap Diambil)',
                            'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                            'dot' => 'bg-emerald-500 animate-pulse',
                            'header' => 'border-emerald-100 bg-emerald-50/40',
                        ],
                        'active' => [
                            'label' => $isLate ? 'Terlambat Dikembalikan' : 'Sedang Dipinjam',
                            'badge' => $isLate
                                ? 'bg-red-50 text-red-800 border-red-200'
                                : 'bg-sky-50 text-sky-800 border-sky-200',
                            'dot' => $isLate ? 'bg-red-600 animate-pulse' : 'bg-sky-500 animate-pulse',
                            'header' => $isLate ? 'border-red-100 bg-red-50/50' : 'border-sky-100 bg-sky-50/40',
                        ],
                        'menunggu_pengecekan' => [
                            'label' => 'Menunggu Cek Fisik',
                            'badge' => 'bg-purple-50 text-purple-800 border-purple-200',
                            'dot' => 'bg-purple-600',
                            'header' => 'border-purple-100 bg-purple-50/40',
                        ],
                        'selesai' => [
                            'label' => 'Selesai & Terverifikasi',
                            'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                            'dot' => 'bg-emerald-600',
                            'header' => 'border-gray-200/80 bg-gray-50/60',
                        ],
                        'ditolak' => [
                            'label' => 'Pengajuan Ditolak',
                            'badge' => 'bg-rose-50 text-rose-800 border-rose-200',
                            'dot' => 'bg-rose-600',
                            'header' => 'border-rose-100 bg-rose-50/40',
                        ],
                        default => [
                            'label' => ucfirst($tiket->status),
                            'badge' => 'bg-gray-100 text-gray-800 border-gray-200',
                            'dot' => 'bg-gray-500',
                            'header' => 'border-gray-200 bg-gray-50',
                        ],
                    };
                @endphp

                <div
                    class="bg-white border border-gray-200/90 rounded-2xl shadow-2xs hover:shadow-sm transition-all overflow-hidden flex flex-col">
                    <!-- Header Tiket -->
                    <div
                        class="px-4 sm:px-5 py-3 border-b flex flex-wrap items-center justify-between gap-2.5 {{ $statusConfig['header'] }}">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="text-xs font-mono font-bold text-gray-900 bg-white px-2 py-0.5 rounded-lg border border-gray-200 shadow-2xs">
                                #TRX-{{ str_pad($tiket->id, 4, '0', STR_PAD_LEFT) }}
                            </span>
                            <span class="text-gray-300 hidden sm:inline">&bull;</span>
                            <span
                                class="text-xs font-semibold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200/80">
                                {{ $tiket->bengkel->nama ?? 'Bengkel' }}
                            </span>
                            <span class="text-gray-300 hidden sm:inline">&bull;</span>
                            <span class="text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($tiket->tanggal_pinjam)->translatedFormat('d M Y, H:i') }} WIB
                            </span>
                        </div>

                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $statusConfig['badge'] }} shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $statusConfig['dot'] }}"></span>
                            {{ $statusConfig['label'] }}
                        </span>
                    </div>

                    <!-- Body Tiket -->
                    <div class="p-4 sm:p-5 space-y-4">
                        <!-- Urgensi Waktu & Keperluan -->
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                            <!-- Batas Waktu / Urgensi -->
                            <div>
                                @if ($isLate)
                                    <div
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-bold">
                                        <svg class="w-4 h-4 text-red-600 animate-pulse shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span>Terlambat: Batas {{ $batasKembali->translatedFormat('d M Y, H:i') }} WIB
                                            ({{ $batasKembali->diffForHumans() }})
                                        </span>
                                    </div>
                                @elseif ($isDueSoon)
                                    <div
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold">
                                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Batas Pengembalian: Hari ini, {{ $batasKembali->format('H:i') }} WIB (Sisa
                                            {{ $batasKembali->diffForHumans(now(), ['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE, 'parts' => 1]) }})</span>
                                    </div>
                                @elseif ($batasKembali)
                                    <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Batas Pengembalian: <strong
                                                class="text-gray-800 font-semibold">{{ $batasKembali->translatedFormat('d M Y, H:i') }}
                                                WIB</strong></span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-1.5 text-xs text-emerald-700">
                                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="font-medium">Barang Habis Pakai (BHP) &bull; Bebas pengembalian</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Keperluan Penggunaan -->
                            <div class="text-xs text-gray-500 flex items-center gap-1.5">
                                <span class="text-gray-400 font-medium shrink-0">Keperluan:</span>
                                <span
                                    class="text-gray-800 font-medium italic truncate max-w-xs sm:max-w-md">"{{ $tiket->keperluan }}"</span>
                            </div>
                        </div>

                        <!-- Alasan Penolakan Jika Ada -->
                        @if ($tiket->status === 'ditolak')
                            <div
                                class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2">
                                <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>
                                    <span class="font-bold">Alasan Penolakan:</span>
                                    {{ $tiket->alasan_penolakan ?? 'Kebutuhan bengkel belum terpenuhi.' }}
                                </div>
                            </div>
                        @endif

                        <!-- Rincian Barang yang Diajukan (Grid Kompak) -->
                        <div class="space-y-2">
                            <h4 class="text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                Barang yang Diajukan ({{ $tiket->detailPeminjamans->count() }}):
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach ($tiket->detailPeminjamans as $detail)
                                    <div
                                        class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50/80 border border-gray-150 text-xs">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span
                                                class="w-2 h-2 rounded-full shrink-0 {{ $detail->barang?->jenis_barang === 'inventaris' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                            <span class="font-bold text-gray-900 truncate"
                                                title="{{ $detail->barang->nama ?? 'Barang Terhapus' }}">
                                                {{ $detail->barang->nama ?? 'Barang Terhapus' }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1.5 shrink-0 ml-2">
                                            <span
                                                class="font-bold text-gray-700 bg-white px-2 py-0.5 rounded-lg border border-gray-200 text-[11px] shadow-2xs">
                                                {{ $detail->jumlah }} {{ $detail->barang->satuan ?? 'Unit' }}
                                            </span>
                                            @if ($detail->barang && $detail->barang->jenis_barang === 'bhp')
                                                <span
                                                    class="text-[10px] font-bold text-amber-700 bg-amber-100/80 px-1.5 py-0.5 rounded">BHP</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Footer Tiket: Tombol Aksi -->
                    <div
                        class="px-4 sm:px-5 py-3 border-t border-gray-150 bg-gray-50/50 flex flex-col sm:flex-row justify-between items-center gap-3">
                        <!-- Petunjuk Status Ringkas -->
                        <div class="text-xs text-gray-500 flex items-center gap-2 w-full sm:w-auto">
                            @if ($tiket->status === 'active')
                                <svg class="w-4 h-4 text-sky-600 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sky-800 font-medium text-xs">Kembalikan alat fisik ke Toolman sebelum
                                    batas waktu.</span>
                            @elseif ($tiket->status === 'pending')
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-amber-800 font-medium text-xs">Menunggu persetujuan Toolman bengkel
                                    {{ $tiket->bengkel->nama }}.</span>
                            @elseif ($tiket->status === 'disetujui')
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-emerald-800 font-medium text-xs">Jadwal disetujui & kuota diamankan. Silakan datang ke meja Toolman bengkel {{ $tiket->bengkel->nama }} pada waktu pengambilan.</span>
                            @elseif ($tiket->status === 'menunggu_pengecekan')
                                <svg class="w-4 h-4 text-purple-600 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span class="text-purple-800 font-medium text-xs">Bawa alat fisik ke meja Toolman untuk
                                    pengecekan.</span>
                            @elseif ($tiket->status === 'selesai')
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-emerald-800 font-medium text-xs">Peminjaman selesai & diverifikasi
                                    Toolman.</span>
                            @elseif ($tiket->status === 'ditolak')
                                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span class="text-rose-700 font-medium text-xs">Pengajuan ditolak oleh Toolman.</span>
                            @endif
                        </div>

                        <!-- Aksi Tombol Responsif -->
                        <div class="flex items-center gap-2 w-full sm:w-auto justify-end flex-wrap sm:flex-nowrap">
                            <!-- Bukti Pinjam (Cetak) -->
                            @if (in_array($tiket->status, ['active', 'terlambat', 'menunggu_pengecekan', 'selesai']))
                                <a href="{{ route('peminjam.tiket.print-pinjam', $tiket->id) }}" target="_blank"
                                    class="px-3 py-2 bg-white border border-gray-200 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 text-gray-600 text-xs font-medium rounded-xl shadow-2xs transition-colors flex items-center gap-1.5"
                                    title="Cetak Bon Pinjam">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                        </path>
                                    </svg>
                                    <span class="hidden sm:inline">Bukti Pinjam</span>
                                </a>
                            @endif

                            <!-- Bukti Kembali (Cetak) -->
                            @if ($tiket->status === 'selesai')
                                <a href="{{ route('peminjam.tiket.print-kembali', $tiket->id) }}" target="_blank"
                                    class="px-3 py-2 bg-white border border-gray-200 hover:bg-sky-50 hover:text-sky-700 hover:border-sky-300 text-gray-600 text-xs font-medium rounded-xl shadow-2xs transition-colors flex items-center gap-1.5"
                                    title="Cetak Bukti Pengembalian">
                                    <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="hidden sm:inline">Bukti Kembali</span>
                                </a>
                            @endif

                            <!-- Detail Tiket -->
                            <a href="{{ route('peminjam.tiket.show', $tiket->id) }}"
                                class="px-3.5 py-2 bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 text-xs font-semibold rounded-xl shadow-2xs transition-colors text-center flex-1 sm:flex-none">
                                Detail
                            </a>

                            <!-- Tombol Utama: Ajukan Pengembalian -->
                            @if (in_array($tiket->status, ['active', 'terlambat']))
                                <form method="POST" action="{{ route('peminjam.tiket.kembalikan', $tiket->id) }}"
                                    class="w-full sm:w-auto"
                                    data-confirm="true"
                                    data-title="Konfirmasi Pengembalian Alat"
                                    data-message="Apakah Anda yakin sudah siap mengembalikan alat fisik tiket <strong>#TRX-{{ str_pad($tiket->id, 4, '0', STR_PAD_LEFT) }}</strong> ke meja Toolman?"
                                    data-submessage="Pastikan barang fisik dibawa lengkap beserta kelengkapannya untuk diverifikasi oleh Toolman."
                                    data-type="primary"
                                    data-confirm-text="Ya, Ajukan Pengembalian">
                                    @csrf
                                    <button type="submit"
                                        class="w-full sm:w-auto px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center justify-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                            </path>
                                        </svg>
                                        <span>Ajukan Pengembalian</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="bg-white rounded-3xl p-8 sm:p-12 text-center border border-gray-200 shadow-xs max-w-md mx-auto">
                    <div
                        class="w-14 h-14 rounded-2xl bg-gray-100 text-gray-400 mx-auto flex items-center justify-center mb-3.5">
                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800">Tidak Ada Tiket Peminjaman</h3>
                    <p class="text-xs text-gray-500 mt-1">Belum ada riwayat transaksi peminjaman untuk filter yang dipilih.
                    </p>
                    <a href="{{ route('peminjam.katalog.index') }}"
                        class="mt-4 inline-block px-4 py-2 bg-primary-50 text-primary-700 hover:bg-primary-100 font-semibold rounded-xl text-xs transition-colors">
                        Buka Katalog Barang
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($peminjamans->hasPages())
            <div class="pt-4">
                {{ $peminjamans->links() }}
            </div>
        @endif
    </div>
@endsection
