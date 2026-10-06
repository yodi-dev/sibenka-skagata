@extends('layouts.admin')

@section('title', 'Edit Usulan RAB - ' . $pengadaan->judul)
@section('header_title', 'Edit / Revisi Usulan RAB')

@section('content')
    @php
        $initialItems = $pengadaan->detailPengadaans
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'barang_id' => $item->barang_id,
                    'nama' => $item->nama_barang,
                    'jenis_barang' => $item->effective_jenis_barang ?? 'inventaris',
                    'minimum_stok' => $item->effective_minimum_stok,
                    'spesifikasi' => $item->spesifikasi ?? '',
                    'jumlah' => (int) $item->jumlah,
                    'satuan' => $item->satuan ?? 'unit',
                    'harga_satuan' => (int) $item->harga_satuan,
                ];
            })
            ->values();
    @endphp

    <div class="max-w-5xl mx-auto space-y-6" x-data="{
        items: {{ Js::from($initialItems) }},
    
        addItem() {
            this.items.push({
                id: null,
                barang_id: null,
                nama: '',
                jenis_barang: 'inventaris',
                minimum_stok: 1,
                spesifikasi: '',
                jumlah: 1,
                satuan: 'unit',
                harga_satuan: 0
            });
        },
    
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            } else {
                window.openAlertModal({
                    title: 'Batas Minimal Item',
                    message: 'Usulan pengadaan harus memiliki minimal 1 item barang.',
                    type: 'warning'
                });
            }
        },
    
        get grandTotal() {
            return this.items.reduce((sum, item) => {
                const qty = parseFloat(item.jumlah) || 0;
                const price = parseFloat(item.harga_satuan) || 0;
                return sum + (qty * price);
            }, 0);
        },
    
        formatRupiah(num) {
            return 'Rp ' + (new Intl.NumberFormat('id-ID').format(num || 0));
        }
    }">

        <!-- Header & Breadcrumb -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <nav class="flex items-center text-xs font-medium text-gray-500 mb-2 space-x-2">
                    <a href="{{ route('toolman.dashboard') }}" class="hover:text-primary-600 transition-colors">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('toolman.pengadaan.index') }}"
                        class="hover:text-primary-600 transition-colors">Pengadaan RAB</a>
                    <span>/</span>
                    <span class="text-gray-800 font-semibold">Edit
                        #RAB-{{ str_pad($pengadaan->id, 4, '0', STR_PAD_LEFT) }}</span>
                </nav>
                <h3 class="text-xl font-bold text-gray-900">Edit & Penyesuaian Usulan RAB</h3>
                <p class="text-sm text-gray-500 mt-0.5">Perbarui rincian kebutuhan barang, perbaiki catatan revisi, atau
                    lengkapi spesifikasi.</p>
            </div>

            <a href="{{ route('toolman.pengadaan.show', $pengadaan->id) }}"
                class="inline-flex items-center px-3.5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg shadow-sm transition-colors">
                &larr; Lihat Detail RAB
            </a>
        </div>

        <!-- Banner Catatan Revisi jika status revisi -->
        @if ($pengadaan->status === 'revisi')
            <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded-r-xl shadow-sm space-y-1">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                    <h4 class="text-sm font-bold text-orange-950">Catatan Waka Sarpras untuk Perbaikan:</h4>
                </div>
                <p class="text-xs text-orange-900 pl-7 leading-relaxed font-medium">
                    "{{ $pengadaan->catatan_review ?? 'Harap lengkapi rincian harga referensi dan spesifikasi teknis barang.' }}"
                </p>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider mb-1">Perhatian: Terjadi Kesalahan Pengisian</p>
                <ul class="text-xs list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Edit Container -->
        <form action="{{ route('toolman.pengadaan.update', $pengadaan->id) }}" method="POST"
            class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            @csrf
            @method('PUT')
            <!-- Section 1: Informasi Header -->
            <div class="p-6 border-b border-gray-200 space-y-5 bg-gray-50/50">
                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Informasi Pengajuan</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Judul -->
                    <div>
                        <label for="judul" class="block text-sm font-medium text-gray-700">Judul Pengajuan <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="judul" name="judul" required value="{{ old('judul', $pengadaan->judul) }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500 text-sm shadow-sm font-medium">
                    </div>

                    <!-- Bengkel (Read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Bengkel / Jurusan</label>
                        <input type="text" disabled value="{{ $pengadaan->bengkel->nama ?? $bengkel->nama }}"
                            class="mt-1 block w-full rounded-lg border-gray-200 bg-gray-100 text-gray-600 text-sm cursor-not-allowed">
                    </div>

                    <!-- Catatan / Keterangan -->
                    <div class="md:col-span-2">
                        <label for="keterangan" class="block text-sm font-medium text-gray-700">
                            Catatan / Keterangan Kebutuhan
                        </label>
                        <textarea id="keterangan" name="keterangan" rows="2"
                            class="mt-1 block w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500 text-sm shadow-sm"
                            placeholder="Keterangan pendukung usulan pengadaan...">{{ old('keterangan', $pengadaan->catatan) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Tabel Item Dinamis -->
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Daftar Kebutuhan Barang</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Edit dan sesuaikan jumlah maupun estimasi harga sesuai
                            arahan revisi.</p>
                    </div>
                    <button type="button" @click="addItem()"
                        class="inline-flex items-center text-xs font-bold text-primary-600 hover:text-primary-700 transition-colors bg-primary-50 px-3 py-1.5 rounded-lg border border-primary-100">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Baris
                    </button>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-slate-50 text-xs font-semibold text-gray-600 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left w-2/5">Nama & Spesifikasi Barang</th>
                                <th class="px-4 py-3 text-center w-28">Jumlah</th>
                                <th class="px-4 py-3 text-center w-24">Satuan</th>
                                <th class="px-4 py-3 text-right w-36">Est. Satuan (Rp)</th>
                                <th class="px-4 py-3 text-right w-36">Subtotal</th>
                                <th class="px-4 py-3 text-center w-14">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white text-sm">
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="group">
                                    <!-- Nama & Spesifikasi -->
                                    <td class="px-4 py-3 align-top">
                                        <input type="hidden" :name="'items[' + index + '][barang_id]'" :value="item.barang_id">
                                        <input type="text" :name="'items[' + index + '][nama]'" x-model="item.nama" placeholder="Nama barang / alat..." required
                                            class="block w-full border-gray-300 rounded-md text-sm focus:ring-primary-500 focus:border-primary-500 mb-1.5 font-medium">
                                        <input type="text" :name="'items[' + index + '][spesifikasi]'" x-model="item.spesifikasi"
                                            placeholder="Spesifikasi teknis, merek, seri..."
                                            class="block w-full border-gray-300 bg-gray-50 rounded-md text-xs focus:ring-primary-500 focus:border-primary-500 text-gray-600">

                                        <!-- Kontrol Tipe Barang & Batas Minimum -->
                                        <template x-if="item.barang_id">
                                            <div class="mt-1.5 flex items-center gap-2">
                                                <input type="hidden" :name="'items[' + index + '][jenis_barang]'" :value="item.jenis_barang">
                                                <input type="hidden" :name="'items[' + index + '][minimum_stok]'" :value="item.minimum_stok">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    Katalog Master: <span class="capitalize ml-1" x-text="item.jenis_barang === 'bhp' ? 'BHP' : 'Inventaris'"></span>
                                                </span>
                                                <template x-if="item.minimum_stok !== null">
                                                    <span class="text-[10px] text-gray-500 font-mono" x-text="'Min: ' + item.minimum_stok"></span>
                                                </template>
                                            </div>
                                        </template>

                                        <template x-if="!item.barang_id">
                                            <div class="mt-1.5 flex items-center gap-2">
                                                <div class="w-1/2">
                                                    <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Tipe Barang <span class="text-red-500">*</span></label>
                                                    <select :name="'items[' + index + '][jenis_barang]'" x-model="item.jenis_barang" required
                                                        class="block w-full border-gray-300 rounded-md text-xs py-1 px-2 focus:ring-primary-500 focus:border-primary-500 bg-white">
                                                        <option value="inventaris">Alat Inventaris</option>
                                                        <option value="bhp">BHP (Bahan Habis Pakai)</option>
                                                    </select>
                                                </div>
                                                <div class="w-1/2">
                                                    <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Batas Min. Stok</label>
                                                    <input type="number" min="0" :name="'items[' + index + '][minimum_stok]'" x-model.number="item.minimum_stok" placeholder="Contoh: 1"
                                                        class="block w-full border-gray-300 rounded-md text-xs py-1 px-2 focus:ring-primary-500 focus:border-primary-500 bg-white">
                                                </div>
                                            </div>
                                        </template>
                                    </td>

                                    <!-- Jumlah -->
                                    <td class="px-4 py-3 align-top">
                                        <input type="number" min="1" :name="'items[' + index + '][jumlah]'" x-model.number="item.jumlah" required
                                            class="block w-full text-center border-gray-300 rounded-md text-sm focus:ring-primary-500 focus:border-primary-500">
                                    </td>

                                    <!-- Satuan -->
                                    <td class="px-4 py-3 align-top">
                                        <input type="text" :name="'items[' + index + '][satuan]'" x-model="item.satuan" placeholder="unit/pcs" required
                                            class="block w-full text-center border-gray-300 rounded-md text-sm focus:ring-primary-500 focus:border-primary-500">
                                    </td>

                                    <!-- Estimasi Satuan -->
                                    <td class="px-4 py-3 align-top">
                                        <input type="number" min="0" step="1000" :name="'items[' + index + '][harga_satuan]'"
                                            x-model.number="item.harga_satuan" required
                                            class="block w-full text-right border-gray-300 rounded-md text-sm focus:ring-primary-500 focus:border-primary-500">
                                    </td>

                                    <!-- Subtotal -->
                                    <td
                                        class="px-4 py-3 align-top text-right font-bold text-gray-800 whitespace-nowrap pt-5">
                                        <span x-text="formatRupiah((item.jumlah || 0) * (item.harga_satuan || 0))"></span>
                                    </td>

                                    <!-- Hapus Baris -->
                                    <td class="px-4 py-3 align-top text-center pt-4">
                                        <button type="button" @click="removeItem(index)"
                                            class="text-gray-400 hover:text-red-600 transition-colors"
                                            title="Hapus baris">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Total Estimasi -->
                <div class="flex justify-end mt-5">
                    <div class="w-full sm:w-80 bg-primary-50 border border-primary-100 rounded-xl p-4 text-right">
                        <p class="text-xs font-semibold text-primary-700 uppercase tracking-wider">Total Estimasi Anggaran
                        </p>
                        <p class="text-2xl font-black text-gray-900 mt-1" x-text="formatRupiah(grandTotal)"></p>
                        <p class="text-[11px] text-gray-500 mt-1" x-text="items.length + ' item terdaftar'"></p>
                    </div>
                </div>
            </div>

            <!-- Section 3: Footer Actions -->
            <div
                class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-3">
                <a href="{{ route('toolman.pengadaan.show', $pengadaan->id) }}"
                    class="text-xs font-semibold text-gray-600 hover:text-gray-900 transition-colors">
                    Batal
                </a>

                <div class="flex items-center gap-3">
                    <button type="submit" name="action" value="draft"
                        class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 text-xs font-semibold rounded-lg shadow-sm transition-colors">
                        Simpan Perubahan
                    </button>
                    <button type="button"
                        @click="window.openConfirmModal({
                            title: 'Kirim Kembali Usulan ke Waka Sarpras',
                            message: 'Apakah Anda yakin ingin mengirimkan kembali usulan perbaikan RAB ini ke Waka Sarpras?',
                            type: 'primary',
                            confirmText: 'Ya, Kirimkan',
                            onConfirm: () => {
                                const form = $el.closest('form');
                                let actionInput = form.querySelector('input[name=action]');
                                if (!actionInput) {
                                    actionInput = document.createElement('input');
                                    actionInput.type = 'hidden';
                                    actionInput.name = 'action';
                                    form.appendChild(actionInput);
                                }
                                actionInput.value = 'submit';
                                form.submit();
                            }
                        })"
                        class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        Kirimkan ke Waka Sarpras
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
