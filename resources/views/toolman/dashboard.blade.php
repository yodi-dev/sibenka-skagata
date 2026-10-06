@extends('layouts.admin')

@section('title', 'Dashboard Admin Bengkel')
@section('header_title', 'Dashboard Bengkel - ' . ($bengkel->nama ?? 'Teknik Komputer Jaringan'))

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- Flash Messages -->
        @if (session('success'))
            <div
                class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div
                class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Header & Quick Actions -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-2xl font-bold text-gray-900">Halo, {{ auth()->user()->name }}!</h3>
                <p class="text-sm text-gray-500 mt-1">Berikut adalah ringkasan aktivitas bengkel
                    {{ $bengkel->nama ?? 'bengkel' }} hari ini.</p>
            </div>
        </div>

        @if (($pendingUserCount ?? 0) > 0)
            <div
                class="bg-amber-50 border border-amber-200 p-4 rounded-xl shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-amber-900">
                            {{ $pendingUserCount }} Calon Peminjam Menunggu Persetujuan
                        </p>
                        <p class="text-xs text-amber-700 mt-0.5">
                            Akun baru belum dapat login atau membuat pinjaman sebelum diverifikasi oleh Anda.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 self-end md:self-center shrink-0">
                    <a href="{{ route('toolman.peminjam.index', ['tab' => 'pending']) }}"
                        class="inline-flex items-center px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors">
                        Verifikasi Semua Peminjam &rarr;
                    </a>
                </div>
            </div>
        @endif

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Barang Dipinjam -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center">
                <div class="p-3 rounded-lg bg-blue-50 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Sedang Dipinjam</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $sedangDipinjam }} <span
                            class="text-sm font-normal text-gray-500">Item</span></p>
                </div>
            </div>

            <!-- Card 2: Pengajuan Baru -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center">
                <div class="p-3 rounded-lg bg-yellow-50 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                        </path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Request Baru</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $requestBaru }} <span
                            class="text-sm font-normal text-gray-500">Antrean</span></p>
                </div>
            </div>

            <!-- Card 3: Barang Rusak -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center">
                <div class="p-3 rounded-lg bg-red-50 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Barang Rusak</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $barangRusak }} <span
                            class="text-sm font-normal text-gray-500">Item</span></p>
                </div>
            </div>

            <!-- Card 4: Low Stock -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center">
                <div class="p-3 rounded-lg bg-orange-50 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Stok Menipis</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stokMenipis }} <span
                            class="text-sm font-normal text-gray-500">Barang</span></p>
                </div>
            </div>
        </div>

        <!-- Antrean Permohonan Peminjaman (Quick Action with Verification Modal) -->
        @if (isset($antreanPeminjaman) && $antreanPeminjaman->count() > 0)
            <div class="bg-white border border-amber-200 rounded-xl shadow-sm overflow-hidden">
                <div
                    class="px-5 py-4 border-b border-amber-100 bg-amber-50/50 flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-2.5 w-2.5 relative">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                        </span>
                        <h4 class="font-bold text-gray-900 text-sm sm:text-base">Permohonan Pinjam Menunggu Persetujuan
                            ({{ $antreanPeminjaman->count() }})</h4>
                    </div>
                    <a href="{{ route('toolman.peminjaman.index', ['tab' => 'pending']) }}"
                        class="text-xs font-semibold text-primary-600 hover:text-primary-700">
                        Lihat Semua Antrean ({{ $requestBaru }}) &rarr;
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/50 text-xs uppercase text-gray-500 tracking-wider">
                                <th class="px-5 py-3 font-semibold">Peminjam</th>
                                <th class="px-5 py-3 font-semibold">Daftar Barang Diminta</th>
                                <th class="px-5 py-3 font-semibold">Jadwal Pinjam</th>
                                <th class="px-5 py-3 font-semibold text-right">Verifikasi Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @foreach ($antreanPeminjaman as $pinjam)
                                <tr class="hover:bg-amber-50/20 transition-colors">
                                    <td class="px-5 py-3.5 align-top">
                                        <p class="font-bold text-gray-900">{{ $pinjam->user->name ?? '-' }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $pinjam->user && $pinjam->user->isGuru() ? 'Guru' : $pinjam->user->nomor_identitas ?? 'Siswa' }}
                                        </p>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="text-[11px] text-gray-400 font-mono">
                                                #PINJAM-{{ str_pad($pinjam->id, 4, '0', STR_PAD_LEFT) }}
                                            </span>
                                            @if ($pinjam->status === 'disetujui')
                                                <span class="text-[10px] px-1.5 py-0.2 rounded font-bold bg-emerald-100 text-emerald-800">Di-ACC</span>
                                            @else
                                                <span class="text-[10px] px-1.5 py-0.2 rounded font-bold bg-amber-100 text-amber-800">Menunggu</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 align-top">
                                        <ul class="space-y-1 text-xs">
                                            @foreach ($pinjam->detailPeminjamans as $d)
                                                <li class="flex items-center gap-1.5 text-gray-800 font-medium">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                                                    <span>{{ $d->barang->nama ?? 'Barang' }}</span>
                                                    <span class="font-bold text-gray-900">({{ $d->jumlah }}
                                                        {{ $d->barang->satuan ?? 'unit' }})</span>
                                                    @if (($d->barang->stok_bebas ?? 0) < $d->jumlah)
                                                        <span
                                                            class="text-[10px] px-1.5 py-0.5 rounded bg-red-100 text-red-700 font-bold">Stok Kurang (Sisa {{ $d->barang->stok_bebas ?? 0 }})</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="px-5 py-3.5 align-top text-xs text-gray-600 whitespace-nowrap">
                                        <p class="font-medium text-gray-900">
                                            {{ \Carbon\Carbon::parse($pinjam->tanggal_pinjam)->translatedFormat('d M Y, H:i') }}
                                        </p>
                                        <p class="text-gray-400 mt-0.5">
                                            {{ \Carbon\Carbon::parse($pinjam->tanggal_pinjam)->diffForHumans() }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 align-top text-right whitespace-nowrap space-x-1.5">
                                        <a href="{{ route('toolman.peminjaman.show', $pinjam->id) }}"
                                            class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition-colors"
                                            title="Lihat Rincian Tiket">
                                            Detail
                                        </a>

                                        <!-- Tombol Tolak Modal -->
                                        <form action="{{ route('toolman.peminjaman.reject', $pinjam->id) }}"
                                            method="POST" class="inline" data-confirm="true"
                                            data-title="Tolak Permohonan Peminjaman"
                                            data-message="Berikan alasan penolakan tiket <b>#PINJAM-{{ str_pad($pinjam->id, 4, '0', STR_PAD_LEFT) }}</b> milik <b>{{ addslashes($pinjam->user->name ?? 'Peminjam') }}</b>:"
                                            data-type="danger" data-confirm-text="Tolak Pengajuan" data-with-input="true"
                                            data-input-name="alasan_penolakan" data-input-label="Alasan Penolakan (Wajib):"
                                            data-input-placeholder="Contoh: Alat sedang dipelihara / stok fisik tidak mencukupi..."
                                            data-input-required="true">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center px-2.5 py-1.5 bg-white border border-red-200 text-red-600 hover:bg-red-50 text-xs font-semibold rounded-lg transition-colors">
                                                Tolak
                                            </button>
                                        </form>

                                        @if (in_array($pinjam->status, ['pending', 'menunggu_acc']))
                                            <!-- Tombol Setujui Jadwal -->
                                            <form action="{{ route('toolman.peminjaman.setujui-jadwal', $pinjam->id) }}"
                                                method="POST" class="inline" data-confirm="true"
                                                data-title="Setujui Jadwal Peminjaman"
                                                data-message="Apakah Anda yakin ingin menyetujui jadwal peminjaman tiket <b>#PINJAM-{{ str_pad($pinjam->id, 4, '0', STR_PAD_LEFT) }}</b>? Kuota barang akan diamankan."
                                                data-type="info" data-confirm-text="Ya, Setujui Jadwal">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center px-2.5 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs font-semibold rounded-lg transition-colors"
                                                    title="Setujui Jadwal & Amankan Kuota">
                                                    ACC Jadwal
                                                </button>
                                            </form>

                                            <!-- Tombol Setujui & Serahkan Modal -->
                                            <form action="{{ route('toolman.peminjaman.approve', $pinjam->id) }}"
                                                method="POST" class="inline" data-confirm="true"
                                                data-title="Setujui Peminjaman & Serahkan Barang"
                                                data-message="Apakah Anda yakin ingin menyetujui peminjaman tiket <b>#PINJAM-{{ str_pad($pinjam->id, 4, '0', STR_PAD_LEFT) }}</b> untuk <b>{{ addslashes($pinjam->user->name ?? 'Peminjam') }}</b> dan langsung menyerahkan barang fisik sekarang?"
                                                data-type="success" data-confirm-text="Ya, Serahkan Barang">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Serahkan
                                                </button>
                                            </form>
                                        @elseif ($pinjam->status === 'disetujui')
                                            <!-- Tombol Serahkan Barang Fisik Modal -->
                                            <form action="{{ route('toolman.peminjaman.approve', $pinjam->id) }}"
                                                method="POST" class="inline" data-confirm="true"
                                                data-title="Serahkan Barang Fisik"
                                                data-message="Apakah Anda yakin ingin menyerahkan barang fisik tiket <b>#PINJAM-{{ str_pad($pinjam->id, 4, '0', STR_PAD_LEFT) }}</b> kepada <b>{{ addslashes($pinjam->user->name ?? 'Peminjam') }}</b>? Stok fisik barang akan dipotong."
                                                data-type="success" data-confirm-text="Ya, Serahkan Barang">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Serahkan Fisik
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Data Tables Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

            <!-- Table 1: Barang Kembali Hari Ini / Sedang Dipinjam -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h4 class="font-semibold text-gray-800">Jadwal Pengembalian Hari Ini</h4>
                    <a href="{{ route('toolman.pengembalian.index') }}"
                        class="text-sm font-medium text-primary-600 hover:text-primary-700">Lihat Semua &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 bg-white text-xs uppercase text-gray-500">
                                <th class="px-5 py-3 font-medium">Peminjam</th>
                                <th class="px-5 py-3 font-medium">Barang</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($jadwalPengembalian as $pinjam)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-5 py-3">
                                        <p class="font-medium text-gray-900">{{ $pinjam->user->name ?? '-' }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $pinjam->user->isGuru() ? 'Guru' : $pinjam->user->nomor_identitas ?? 'Siswa' }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-3">
                                        @foreach ($pinjam->detailPeminjamans as $detail)
                                            <p class="font-medium text-gray-800">{{ $detail->barang->nama ?? '-' }}
                                                ({{ $detail->jumlah }} {{ $detail->barang->satuan ?? 'unit' }})
                                            </p>
                                            <p class="text-xs text-gray-500 font-mono">
                                                {{ $detail->barang->kode_barang ?? '' }}</p>
                                        @endforeach
                                    </td>
                                    <td class="px-5 py-3">
                                        @if ($pinjam->status === 'menunggu_pengecekan')
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Menunggu Cek
                                            </span>
                                        @elseif (
                                            $pinjam->status === 'terlambat' ||
                                                ($pinjam->batas_kembali && \Carbon\Carbon::parse($pinjam->batas_kembali)->isPast()))
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Terlambat
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Sedang Dipinjam
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-6 text-center text-gray-500 text-sm">
                                        Tidak ada peminjaman aktif yang menunggu pengembalian saat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Table 2: Low Stock Alert -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h4 class="font-semibold text-gray-800">Peringatan Stok Habis Pakai</h4>
                    <a href="{{ route('toolman.barang.index') }}"
                        class="text-sm font-medium text-primary-600 hover:text-primary-700">Manajemen Stok &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 bg-white text-xs uppercase text-gray-500">
                                <th class="px-5 py-3 font-medium">Nama Bahan / Alat</th>
                                <th class="px-5 py-3 font-medium">Sisa Stok</th>
                                <th class="px-5 py-3 font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($peringatanStok as $item)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-5 py-3">
                                        <p class="font-medium text-gray-900">{{ $item->nama }}</p>
                                        <p class="text-xs text-gray-500">Satuan: {{ $item->satuan }} &bull;
                                            {{ $item->jenis_barang === 'bhp' ? 'Bahan Habis Pakai' : 'Inventaris' }}</p>
                                    </td>
                                    <td class="px-5 py-3">
                                        <p
                                            class="font-bold {{ $item->stok_tersedia <= 0 ? 'text-red-600' : 'text-orange-500' }}">
                                            {{ $item->stok_tersedia }} {{ $item->satuan }}
                                        </p>
                                        <p class="text-xs text-gray-500">Batas min: {{ $item->minimum_stok }}
                                            {{ $item->satuan }}</p>
                                    </td>
                                    <td class="px-5 py-3">
                                        <a href="{{ route('toolman.pengadaan.create') }}"
                                            class="inline-block text-xs font-medium text-primary-600 border border-primary-600 rounded-lg px-2 py-1 hover:bg-primary-50 transition-colors">
                                            + List RAB
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-6 text-center text-gray-500 text-sm">
                                        Semua stok barang dan bahan saat ini dalam kondisi aman.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection
