<!-- Navigation Menu -->
<nav class="flex-1 min-h-0 overflow-y-auto py-4 px-3 space-y-1 sidebar-scroll overscroll-contain">

    @if (
        (auth()->check() && auth()->user()->role === 'waka') ||
            request()->is('superadmin*') ||
            (isset($role) && $role === 'superadmin'))
        <!-- MENU SUPER ADMIN (WAKA SARPRAS) -->
        <a href="/superadmin/dashboard"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->is('superadmin/dashboard') ? 'bg-primary-50 text-primary-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transition-colors">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                </path>
            </svg>
            Dashboard
        </a>

        <!-- Sub-menu Master Data Superadmin -->
        <div class="pt-4 pb-1">
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Master Data</p>
        </div>
        <a href="/superadmin/bengkel"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->is('superadmin/bengkel*') ? 'bg-primary-50 text-primary-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transition-colors">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                </path>
            </svg>
            Data Bengkel
        </a>
        <a href="/superadmin/toolman"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->is('superadmin/toolman*') ? 'bg-primary-50 text-primary-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transition-colors">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                </path>
            </svg>
            Akun Toolman
        </a>

        <!-- Sub-menu Pengadaan & Laporan -->
        <div class="pt-4 pb-1">
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Pengadaan & Laporan</p>
        </div>
        <a href="/superadmin/pengadaan"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->is('superadmin/pengadaan*') ? 'bg-primary-50 text-primary-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transition-colors">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            Persetujuan Pengadaan
        </a>
        <a href="/superadmin/laporan/mutasi"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->is('superadmin/laporan/mutasi*') ? 'bg-primary-50 text-primary-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transition-colors">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
            </svg>
            Mutasi Aset
        </a>
        <a href="/superadmin/laporan/konsumsi"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->is('superadmin/laporan/konsumsi*') ? 'bg-primary-50 text-primary-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transition-colors">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                </path>
            </svg>
            Konsumsi Bahan
        </a>

        <!-- Sub-menu Bantuan Superadmin -->
        <div class="pt-4 pb-1">
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Bantuan</p>
        </div>
        <a href="{{ route('superadmin.panduan') }}"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->is('superadmin/panduan*') ? 'bg-primary-50 text-primary-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transition-colors">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                </path>
            </svg>
            Buku Panduan &amp; SOP
        </a>
    @else
        <!-- ================= MENU ADMIN BENGKEL (TOOLMAN) ================= -->
        @php
            $sbUser = auth()->user();
            $sbBengkelId = $sbUser?->bengkel_id;

            // Hitung badge notifikasi real-time untuk Toolman
            $badgePinjamPending = 0;
            $badgeKembaliCheck = 0;
            $badgePeminjamPending = 0;

            if ($sbBengkelId) {
                $badgePinjamPending = \App\Models\Peminjaman::where('bengkel_id', $sbBengkelId)
                    ->whereIn('status', ['pending', 'menunggu_acc'])
                    ->count();

                $badgeKembaliCheck = \App\Models\Peminjaman::where('bengkel_id', $sbBengkelId)
                    ->where('status', 'menunggu_pengecekan')
                    ->count();

                $badgePeminjamPending = \App\Models\User::where('role', 'peminjam')
                    ->where('status', 'menunggu_acc')
                    ->where(function ($q) use ($sbBengkelId) {
                        $q->where('bengkel_id', $sbBengkelId)->orWhere('jenis_peminjam', 'guru');
                    })
                    ->count();
            }
        @endphp

        <!-- 1. DASHBOARD BENGKEL -->
        <a href="/toolman/dashboard"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->is('toolman/dashboard') ? 'bg-primary-50 text-primary-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transition-colors">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                </path>
            </svg>
            <span>Dashboard</span>
        </a>

        <!-- 2. OPERASIONAL & SIRKULASI (INTI PEKERJAAN TOOLMAN) -->
        <div class="pt-4 pb-1">
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Operasional & Sirkulasi</p>
        </div>

        <!-- Peminjaman -->
        <a href="/toolman/peminjaman"
            class="flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->is('toolman/peminjaman*') ? 'bg-primary-50 text-primary-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transition-colors group">
            <div class="flex items-center min-w-0">
                <svg class="w-5 h-5 mr-3 shrink-0 text-gray-500 group-hover:text-gray-700" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                    </path>
                </svg>
                <span class="truncate">Peminjaman</span>
            </div>
            @if ($badgePinjamPending > 0)
                <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-500 text-white shadow-xs ml-2 animate-pulse"
                    title="{{ $badgePinjamPending }} tiket menunggu persetujuan">
                    {{ $badgePinjamPending }}
                </span>
            @endif
        </a>

        <!-- Pengembalian -->
        <a href="/toolman/pengembalian"
            class="flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->is('toolman/pengembalian*') ? 'bg-primary-50 text-primary-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transition-colors group">
            <div class="flex items-center min-w-0">
                <svg class="w-5 h-5 mr-3 shrink-0 text-gray-500 group-hover:text-gray-700" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                </svg>
                <span class="truncate">Pengembalian</span>
            </div>
            @if ($badgeKembaliCheck > 0)
                <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-500 text-white shadow-xs ml-2"
                    title="{{ $badgeKembaliCheck }} barang menunggu cek fisik">
                    {{ $badgeKembaliCheck }}
                </span>
            @endif
        </a>

        <!-- Manajemen Barang -->
        <a href="/toolman/barang"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->is('toolman/barang*') ? 'bg-primary-50 text-primary-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transition-colors">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <span>Manajemen Barang</span>
        </a>

        <!-- 3. PENGADAAN & MUTASI -->
        <div class="pt-4 pb-1">
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Pengadaan & Mutasi</p>
        </div>

        <!-- Pengadaan (RAB) -->
        <a href="/toolman/pengadaan"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->is('toolman/pengadaan*') ? 'bg-primary-50 text-primary-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transition-colors">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
            </svg>
            <span>Pengadaan (RAB)</span>
        </a>

        <!-- Riwayat Stok / Mutasi -->
        <a href="/toolman/mutasi"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->is('toolman/mutasi*') ? 'bg-primary-50 text-primary-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transition-colors">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                </path>
            </svg>
            <span>Riwayat Mutasi Stok</span>
        </a>

        <!-- 4. MASTER DATA REFERENSI -->
        <div class="pt-4 pb-1">
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Master Data</p>
        </div>

        <!-- Lokasi Penyimpanan -->
        <a href="{{ route('toolman.lokasi.index') }}"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->is('toolman/lokasi*') ? 'bg-primary-50 text-primary-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transition-colors">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span>Lokasi Penyimpanan</span>
        </a>

        <!-- Satuan Barang (Baru) -->
        <a href="{{ route('toolman.satuan.index') }}"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->is('toolman/satuan*') ? 'bg-primary-50 text-primary-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transition-colors">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                </path>
            </svg>
            <span>Satuan Barang</span>
        </a>

        <!-- Sumber Dana (Baru View Page) -->
        <a href="{{ route('toolman.sumber-dana.index') }}"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->is('toolman/sumber-dana*') ? 'bg-primary-50 text-primary-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transition-colors">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                </path>
            </svg>
            <span>Sumber Dana</span>
        </a>

        <!-- Data Peminjam (Siswa & Guru) -->
        <a href="/toolman/peminjam"
            class="flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->is('toolman/peminjam*') || request()->is('toolman/users*')
                ? 'bg-primary-50 text-primary-600 font-semibold'
                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transition-colors group">
            <div class="flex items-center min-w-0">
                <svg class="w-5 h-5 mr-3 shrink-0 text-gray-500 group-hover:text-gray-700" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                    </path>
                </svg>
                <span class="truncate">Data Peminjam</span>
            </div>
            @if ($badgePeminjamPending > 0)
                <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-primary-600 text-white shadow-xs ml-2"
                    title="{{ $badgePeminjamPending }} peminjam baru menunggu persetujuan">
                    {{ $badgePeminjamPending }}
                </span>
            @endif
        </a>

        <!-- 5. BANTUAN & PANDUAN TOOLMAN -->
        <div class="pt-4 pb-1">
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Bantuan</p>
        </div>
        <a href="{{ route('toolman.panduan') }}"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->is('toolman/panduan*') ? 'bg-primary-50 text-primary-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transition-colors">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                </path>
            </svg>
            <span>Buku Panduan &amp; SOP</span>
        </a>
    @endif

    <!-- Bottom spacer for comfortable scrolling -->
    <div class="h-4"></div>
</nav>
