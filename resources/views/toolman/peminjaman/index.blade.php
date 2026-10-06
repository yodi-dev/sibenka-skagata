@extends('layouts.admin')

@section('title', 'Persetujuan Peminjaman')
@section('header_title', 'Sirkulasi - Persetujuan Peminjaman')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">

        @if (session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-800 flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800 flex items-center gap-3">
                <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Header & Tabs -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Antrean Persetujuan</h3>
                <p class="text-sm text-gray-500 mt-1">Cek dan verifikasi pengajuan alat & bahan di
                    {{ $bengkel->nama ?? 'bengkel' }}.</p>
            </div>

            <!-- Search -->
            <form method="GET" action="{{ route('toolman.peminjaman.index') }}" class="w-full sm:w-72">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / NIS..."
                        class="block w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                </div>
            </form>
        </div>

        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                <a href="{{ route('toolman.peminjaman.index', ['tab' => 'pending']) }}"
                    class="{{ $tab === 'pending' ? 'border-primary-500 text-primary-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-3 px-1 border-b-2 text-sm transition-colors">
                    Menunggu Persetujuan ({{ $pendingCount }})
                </a>
                <a href="{{ route('toolman.peminjaman.index', ['tab' => 'riwayat']) }}"
                    class="{{ $tab === 'riwayat' ? 'border-primary-500 text-primary-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-3 px-1 border-b-2 text-sm transition-colors">
                    Riwayat Persetujuan ({{ $riwayatCount }})
                </a>
            </nav>
        </div>

        <!-- List of Request Tickets -->
        <div class="space-y-5">
            @forelse ($peminjamans as $pinjam)
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <!-- Ticket Header -->
                    <div
                        class="px-5 py-3 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                        <div class="flex items-center space-x-3">
                            <div
                                class="h-10 w-10 rounded-full {{ $pinjam->user && $pinjam->user->isGuru() ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }} flex items-center justify-center font-bold">
                                {{ strtoupper(substr($pinjam->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900">{{ $pinjam->user->name ?? '-' }}</h4>
                                <p class="text-xs text-gray-500">
                                    {{ $pinjam->user && $pinjam->user->isGuru() ? 'Guru' : 'Siswa - ' . ($pinjam->user->nomor_identitas ?? '-') }}
                                </p>
                            </div>
                        </div>
                        <div class="text-left sm:text-right">
                            @if ($pinjam->status === 'pending' || $pinjam->status === 'menunggu_acc')
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Menunggu Acc
                                </span>
                            @elseif ($pinjam->status === 'disetujui' || $pinjam->status === 'disetujui_jadwal')
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                    Jadwal Disetujui
                                </span>
                            @elseif ($pinjam->status === 'active' || $pinjam->status === 'aktif')
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Sedang Dipinjam
                                </span>
                            @elseif ($pinjam->status === 'selesai')
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                    Selesai
                                </span>
                            @elseif ($pinjam->status === 'ditolak')
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Ditolak
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 capitalize">
                                    {{ $pinjam->status }}
                                </span>
                            @endif
                            <p class="text-xs text-gray-500 mt-1">
                                Diajukan: {{ \Carbon\Carbon::parse($pinjam->tanggal_pinjam)->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    <!-- Ticket Body -->
                    <div class="px-5 py-4">
                        <h5 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Rincian Permintaan:
                        </h5>
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-gray-600">Nama Barang</th>
                                        <th class="px-4 py-2 text-center font-medium text-gray-600">Jml Diminta</th>
                                        <th class="px-4 py-2 text-center font-medium text-gray-600">Sisa Kuota Bebas</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach ($pinjam->detailPeminjamans as $detail)
                                        <tr>
                                            <td class="px-4 py-2 text-gray-900 font-medium">
                                                {{ $detail->barang->nama ?? '-' }}
                                                <span class="text-xs text-gray-500 font-normal ml-1">
                                                     ({{ $detail->barang && $detail->barang->jenis_barang === 'bhp' ? 'Bahan Habis Pakai' : 'Inventaris' }})
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-center font-bold text-gray-900">
                                                {{ $detail->jumlah }} {{ $detail->barang->satuan ?? 'Unit' }}
                                            </td>
                                            <td
                                                class="px-4 py-2 text-center font-medium {{ ($detail->barang->stok_bebas ?? 0) < $detail->jumlah ? 'text-red-600 font-bold' : 'text-green-600' }}">
                                                {{ $detail->barang->stok_bebas ?? 0 }} {{ $detail->barang->satuan ?? 'Unit' }}
                                                <span class="text-[11px] text-gray-400 block">(Fisik: {{ $detail->barang->stok_tersedia ?? 0 }})</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Info Waktu & Catatan -->
                        <div
                            class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <div>
                                <p class="text-xs text-gray-500">Jadwal Pinjam - Batas Kembali</p>
                                <p class="text-sm font-medium text-gray-900 mt-0.5">
                                    {{ \Carbon\Carbon::parse($pinjam->tanggal_pinjam)->translatedFormat('d M Y, H:i') }}
                                    &mdash;
                                    {{ $pinjam->batas_kembali ? \Carbon\Carbon::parse($pinjam->batas_kembali)->translatedFormat('d M Y, H:i') : 'Hari ini' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Keperluan / Tujuan Penggunaan</p>
                                <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $pinjam->keperluan ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Footer (Actions) -->
                    <div class="px-5 py-3 border-t border-gray-200 bg-white flex flex-wrap justify-end items-center gap-3">
                        <a href="{{ route('toolman.peminjaman.show', $pinjam->id) }}"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                            Lihat Detail Tiket
                        </a>
                        @if (in_array($pinjam->status, ['pending', 'menunggu_acc']))
                            <!-- Tombol Tolak Pengajuan -->
                            <form action="{{ route('toolman.peminjaman.reject', $pinjam->id) }}" method="POST" class="inline"
                                  data-confirm="true"
                                  data-title="Tolak Permohonan Peminjaman"
                                  data-message="Berikan alasan penolakan tiket <b>#PINJAM-{{ str_pad($pinjam->id, 4, '0', STR_PAD_LEFT) }}</b> milik <b>{{ addslashes($pinjam->user->name ?? 'Peminjam') }}</b>:"
                                  data-type="danger"
                                  data-confirm-text="Tolak Permohonan"
                                  data-with-input="true"
                                  data-input-name="alasan_penolakan"
                                  data-input-label="Alasan Penolakan (Wajib):"
                                  data-input-placeholder="Contoh: Alat sedang dalam perbaikan berkala / jadwal bentrok..."
                                  data-input-required="true">
                                @csrf
                                <button type="submit"
                                    class="px-4 py-2 bg-white border border-red-200 text-red-600 hover:bg-red-50 text-sm font-medium rounded-lg shadow-sm transition-colors">
                                    Tolak Pengajuan
                                </button>
                            </form>

                            <!-- Form Setujui Jadwal -->
                            <form action="{{ route('toolman.peminjaman.setujui-jadwal', $pinjam->id) }}" method="POST" class="inline"
                                  data-confirm="true"
                                  data-title="Setujui Jadwal Peminjaman"
                                  data-message="Apakah Anda yakin ingin menyetujui jadwal peminjaman tiket <b>#PINJAM-{{ str_pad($pinjam->id, 4, '0', STR_PAD_LEFT) }}</b>? Kuota barang akan diamankan untuk jadwal peminjam."
                                  data-type="info"
                                  data-confirm-text="Ya, Setujui Jadwal">
                                @csrf
                                <button type="submit"
                                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Setujui Jadwal
                                </button>
                            </form>

                            <!-- Form Approve & Serahkan Langsung -->
                            <form action="{{ route('toolman.peminjaman.approve', $pinjam->id) }}" method="POST" class="inline"
                                  data-confirm="true"
                                  data-title="Setujui Peminjaman & Serahkan Barang"
                                  data-message="Apakah Anda yakin ingin menyetujui peminjaman tiket <b>#PINJAM-{{ str_pad($pinjam->id, 4, '0', STR_PAD_LEFT) }}</b> dan langsung menyerahkan barang fisik sekarang?"
                                  data-type="success"
                                  data-confirm-text="Ya, Serahkan Barang">
                                @csrf
                                <button type="submit"
                                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Serahkan Langsung
                                </button>
                            </form>
                        @elseif ($pinjam->status === 'disetujui' || $pinjam->status === 'disetujui_jadwal')
                            <!-- Tombol Batalkan / Tolak -->
                            <form action="{{ route('toolman.peminjaman.reject', $pinjam->id) }}" method="POST" class="inline"
                                  data-confirm="true"
                                  data-title="Batalkan Persetujuan Jadwal"
                                  data-message="Berikan alasan pembatalan tiket <b>#PINJAM-{{ str_pad($pinjam->id, 4, '0', STR_PAD_LEFT) }}</b>. Kuota yang dipesan akan dilepas kembali:"
                                  data-type="danger"
                                  data-confirm-text="Batalkan Tiket"
                                  data-with-input="true"
                                  data-input-name="alasan_penolakan"
                                  data-input-label="Alasan Pembatalan (Wajib):"
                                  data-input-placeholder="Contoh: Peminjam membatalkan praktikum..."
                                  data-input-required="true">
                                @csrf
                                <button type="submit"
                                    class="px-4 py-2 bg-white border border-red-200 text-red-600 hover:bg-red-50 text-sm font-medium rounded-lg shadow-sm transition-colors">
                                    Batalkan Tiket
                                </button>
                            </form>

                            <!-- Form Serahkan Barang Fisik -->
                            <form action="{{ route('toolman.peminjaman.approve', $pinjam->id) }}" method="POST" class="inline"
                                  data-confirm="true"
                                  data-title="Serahkan Barang Fisik"
                                  data-message="Apakah Anda yakin ingin menyerahkan barang fisik tiket <b>#PINJAM-{{ str_pad($pinjam->id, 4, '0', STR_PAD_LEFT) }}</b> kepada <b>{{ addslashes($pinjam->user->name ?? 'Peminjam') }}</b>? Stok fisik barang akan dipotong."
                                  data-type="success"
                                  data-confirm-text="Ya, Serahkan Barang">
                                @csrf
                                <button type="submit"
                                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Serahkan Barang Fisik
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white p-12 rounded-xl border border-gray-200 text-center">
                    <p class="text-gray-500 text-sm">Tidak ada tiket peminjaman dalam kategori ini.</p>
                </div>
            @endforelse

            <!-- Pagination -->
            @if ($peminjamans->hasPages())
                <div class="pt-2">
                    {{ $peminjamans->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection
