@extends('layouts.admin')

@section('title', 'Pengecekan Fisik Pengembalian #' . $peminjaman->id)
@section('header_title', 'Sirkulasi - Pengecekan Fisik Pengembalian')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6 pb-12">

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

        @if (isset($errors) && $errors->any())
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800 space-y-1">
                <div class="font-bold flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Terdapat kesalahan pada isian form:</span>
                </div>
                <ul class="list-disc list-inside text-xs text-red-700 pl-7 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Breadcrumb & Top Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <nav class="flex items-center text-xs font-medium text-gray-500 mb-2 space-x-2">
                    <a href="{{ route('toolman.dashboard') }}" class="hover:text-primary-600 transition-colors">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('toolman.pengembalian.index') }}"
                        class="hover:text-primary-600 transition-colors">Pengembalian</a>
                    <span>/</span>
                    <span class="text-primary-700 font-semibold">Cek Fisik #{{ $peminjaman->id }}</span>
                </nav>
                <h3 class="text-2xl font-bold text-gray-900 tracking-tight">Form Pengecekan Kondisi Fisik Alat</h3>
                <p class="text-sm text-gray-500 mt-1">Periksa kelengkapan dan kondisi fisik alat yang dikembalikan oleh
                    peminjam.</p>
            </div>
            <div>
                <a href="{{ route('toolman.pengembalian.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>

        <!-- Info Card Peminjam -->
        <div
            class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div
                    class="h-12 w-12 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-lg">
                    {{ strtoupper(substr($peminjaman->user->name ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <h4 class="text-base font-bold text-gray-900">{{ $peminjaman->user->name ?? '-' }}</h4>
                    <p class="text-xs text-gray-500">
                        {{ $peminjaman->user && $peminjaman->user->isGuru() ? 'Guru' : 'Siswa - ' . ($peminjaman->user->nomor_identitas ?? '-') }}
                        &bull; Bengkel: {{ $peminjaman->bengkel->nama ?? '-' }}
                    </p>
                </div>
            </div>
            <div class="text-left sm:text-right">
                @if ($peminjaman->status === 'menunggu_pengecekan')
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 border border-indigo-200">
                        Menunggu Cek Fisik
                    </span>
                @elseif ($peminjaman->status === 'terlambat' || ($peminjaman->batas_kembali && \Carbon\Carbon::parse($peminjaman->batas_kembali)->isPast()))
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                        Terlambat
                    </span>
                @else
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        Sedang Dipinjam
                    </span>
                @endif
                <p class="text-xs text-gray-500 mt-1">
                    Batas Kembali:
                    {{ $peminjaman->batas_kembali ? \Carbon\Carbon::parse($peminjaman->batas_kembali)->translatedFormat('d M Y, H:i') : 'Hari ini' }}
                </p>
            </div>
        </div>

        <!-- Form Pengecekan -->
        <form method="POST" action="{{ route('toolman.pengembalian.process-check', $peminjaman->id) }}"
            data-confirm="true"
            data-title="Konfirmasi Selesai Pemeriksaan Fisik"
            data-message="Pastikan pemeriksaan fisik telah sesuai dengan kondisi fisik barang yang diterima."
            data-submessage="Setelah disimpan, transaksi peminjaman akan dinyatakan selesai dan stok bengkel diperbarui secara otomatis."
            data-type="success"
            data-confirm-text="Ya, Simpan & Selesaikan"
            class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            @csrf

            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/70 flex justify-between items-center">
                <h4 class="font-bold text-gray-900 text-sm">Inspeksi Barang Inventaris</h4>
                <span class="text-xs text-gray-500">Catat jumlah kondisi: Baik, Rusak, atau Hilang</span>
            </div>

            <div class="divide-y divide-gray-100">
                @foreach ($peminjaman->detailPeminjamans as $detail)
                    @if ($detail->barang && $detail->barang->jenis_barang === 'inventaris')
                        <div class="p-6 space-y-4" x-data="{
                            totalQty: {{ (int) $detail->jumlah }},
                            baik: {{ old("items.{$detail->id}.jumlah_baik", $detail->jumlah) }},
                            rusak: {{ old("items.{$detail->id}.jumlah_rusak", 0) }},
                            hilang: {{ old("items.{$detail->id}.jumlah_hilang", 0) }},
                            catatan: '{{ addslashes(old("items.{$detail->id}.catatan", "")) }}',
                            get currentTotal() {
                                return (parseInt(this.baik) || 0) + (parseInt(this.rusak) || 0) + (parseInt(this.hilang) || 0);
                            },
                            get isValid() {
                                return this.currentTotal === this.totalQty;
                            }
                        }">
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-3">
                                <div>
                                    <h5 class="text-base font-bold text-gray-900">{{ $detail->barang->nama }}</h5>
                                    <p class="text-xs text-gray-500 font-mono">{{ $detail->barang->kode_barang }} &bull;
                                        Lokasi: {{ $detail->barang->lokasiPenyimpanan->nama ?? '-' }}</p>
                                </div>
                                <div class="text-sm font-semibold text-gray-800 bg-slate-100 px-3 py-1 rounded-lg">
                                    Total Dipinjam: {{ $detail->jumlah }} {{ $detail->barang->satuan ?? 'Unit' }}
                                </div>
                            </div>

                            <!-- Live Validation Feedback -->
                            <div x-show="!isValid"
                                class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-800 font-medium flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Total alokasi fisik (<strong x-text="currentTotal"></strong>) belum sama dengan jumlah dipinjam (<strong>{{ $detail->jumlah }}</strong>).</span>
                                </div>
                                <span class="text-red-600 font-bold" x-text="totalQty - currentTotal > 0 ? 'Kurang ' + (totalQty - currentTotal) : 'Kelebihan ' + (currentTotal - totalQty)"></span>
                            </div>

                            <div x-show="isValid"
                                class="p-2.5 bg-green-50 border border-green-200 rounded-xl text-xs text-green-800 font-medium flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Alokasi fisik cocok: total <strong x-text="currentTotal"></strong> {{ $detail->barang->satuan ?? 'Unit' }} terverifikasi.</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <!-- Kondisi Baik -->
                                <div class="bg-green-50/60 p-4 rounded-xl border border-green-200">
                                    <label class="block text-xs font-bold text-green-800 uppercase tracking-wide mb-1">
                                        ✅ Kembali Baik
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input type="number" min="0" :max="totalQty" x-model.number="baik"
                                            name="items[{{ $detail->id }}][jumlah_baik]" required
                                            class="w-full text-sm font-bold text-gray-900 rounded-lg border-green-300 focus:ring-green-500 focus:border-green-500">
                                        <span
                                            class="text-xs text-green-700 font-medium">{{ $detail->barang->satuan ?? 'Unit' }}</span>
                                    </div>
                                    <p class="text-[11px] text-green-600 mt-1">Stok kembali ke ketersediaan</p>
                                </div>

                                <!-- Kondisi Rusak -->
                                <div class="bg-amber-50/60 p-4 rounded-xl border border-amber-200">
                                    <label class="block text-xs font-bold text-amber-800 uppercase tracking-wide mb-1">
                                        ⚠️ Kembali Rusak
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input type="number" min="0" :max="totalQty" x-model.number="rusak"
                                            name="items[{{ $detail->id }}][jumlah_rusak]" required
                                            class="w-full text-sm font-bold text-gray-900 rounded-lg border-amber-300 focus:ring-amber-500 focus:border-amber-500">
                                        <span
                                            class="text-xs text-amber-700 font-medium">{{ $detail->barang->satuan ?? 'Unit' }}</span>
                                    </div>
                                    <p class="text-[11px] text-amber-600 mt-1">Pindah ke stok rusak</p>
                                </div>

                                <!-- Kondisi Hilang -->
                                <div class="bg-red-50/60 p-4 rounded-xl border border-red-200">
                                    <label class="block text-xs font-bold text-red-800 uppercase tracking-wide mb-1">
                                        ❌ Hilang
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input type="number" min="0" :max="totalQty" x-model.number="hilang"
                                            name="items[{{ $detail->id }}][jumlah_hilang]" required
                                            class="w-full text-sm font-bold text-gray-900 rounded-lg border-red-300 focus:ring-red-500 focus:border-red-500">
                                        <span
                                            class="text-xs text-red-700 font-medium">{{ $detail->barang->satuan ?? 'Unit' }}</span>
                                    </div>
                                    <p class="text-[11px] text-red-600 mt-1">Mengurangi total stok permanen</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Catatan Tambahan Kondisi Alat
                                    (Opsional)</label>
                                <input type="text" x-model="catatan" name="items[{{ $detail->id }}][catatan]"
                                    placeholder="Contoh: Port nomor 2 longgar, kabel terkelupas..."
                                    class="w-full text-sm rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                <a href="{{ route('toolman.pengembalian.index') }}"
                    class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg shadow-sm transition-colors">
                    Batal
                </a>
                <button type="submit"
                    class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan & Selesaikan Peminjaman
                </button>
            </div>
        </form>

    </div>
@endsection
