@extends('layouts.admin')

@section('title', 'Buat Pengajuan RAB')
@section('header_title', 'Pengadaan - Rencana Anggaran Biaya')

@section('content')
    @php
        $limitItemsPayload = collect($limitItems ?? [])
            ->map(function ($item) {
                $rekomendasiQty =
                    $item->stok_rusak > 0
                        ? $item->stok_rusak
                        : max(($item->minimum_stok ?? 5) - $item->stok_tersedia, 1);
                $keteranganStok =
                    $item->stok_rusak > 0
                        ? "Rusak: {$item->stok_rusak} {$item->satuan}"
                        : "Sisa: {$item->stok_tersedia} (Min: {$item->minimum_stok}) {$item->satuan}";

                return [
                    'barang_id' => $item->id,
                    'nama' => $item->nama,
                    'jenis_barang' => $item->jenis_barang,
                    'minimum_stok' => $item->minimum_stok,
                    'spesifikasi' => "Penggantian/penambahan untuk {$item->nama} ({$item->kode_barang})",
                    'jumlah' => $rekomendasiQty,
                    'satuan' => $item->satuan ?? 'unit',
                    'harga_satuan' => 0,
                    'info_stok' => $keteranganStok,
                    'is_alert' => true,
                ];
            })
            ->values();
    @endphp

    <div class="max-w-5xl mx-auto space-y-6" x-data="{
        limitItemsData: {{ Js::from($limitItemsPayload) }},
        items: [{
            barang_id: null,
            nama: '',
            jenis_barang: 'inventaris',
            minimum_stok: 1,
            spesifikasi: '',
            jumlah: 1,
            satuan: 'unit',
            harga_satuan: 0,
            info_stok: '',
            is_alert: false
        }],
    
        addItem() {
            this.items.push({
                barang_id: null,
                nama: '',
                jenis_barang: 'inventaris',
                minimum_stok: 1,
                spesifikasi: '',
                jumlah: 1,
                satuan: 'unit',
                harga_satuan: 0,
                info_stok: '',
                is_alert: false
            });
        },
    
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            } else {
                this.items = [{
                    barang_id: null,
                    nama: '',
                    jenis_barang: 'inventaris',
                    minimum_stok: 1,
                    spesifikasi: '',
                    jumlah: 1,
                    satuan: 'unit',
                    harga_satuan: 0,
                    info_stok: '',
                    is_alert: false
                }];
            }
        },
    
        generateFromLimit() {
            if (this.limitItemsData.length === 0) {
                window.openAlertModal({
                    title: 'Rekomendasi Pengadaan',
                    message: 'Tidak ada barang bengkel yang berada di bawah batas minimum stok atau berstatus rusak saat ini.',
                    type: 'info'
                });
                return;
            }
    
            // Jika hanya ada 1 baris kosong, gantikan dengan data limit
            if (this.items.length === 1 && !this.items[0].nama) {
                this.items = JSON.parse(JSON.stringify(this.limitItemsData));
            } else {
                // Tambahkan data rekomendasi yang belum ada
                const existingIds = this.items.map(i => i.barang_id).filter(Boolean);
                const newItems = this.limitItemsData.filter(i => !existingIds.includes(i.barang_id));
                if (newItems.length === 0) {
                    window.openAlertModal({
                        title: 'Rekomendasi Pengadaan',
                        message: 'Semua barang limit/rusak sudah ada di dalam tabel usulan pengadaan.',
                        type: 'info'
                    });
                    return;
                }
                this.items.push(...JSON.parse(JSON.stringify(newItems)));
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

        <!-- Header & Auto-Generate Button -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('toolman.pengadaan.index') }}"
                        class="text-xs font-semibold text-gray-500 hover:text-primary-600 transition-colors">
                        &larr; Kembali ke Daftar RAB
                    </a>
                    <span class="text-gray-300">&bull;</span>
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ $bengkel->nama ?? 'Bengkel' }}
                    </span>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Buat Pengajuan RAB Baru</h3>
                <p class="text-sm text-gray-500 mt-0.5">Susun draf usulan Rencana Anggaran Biaya (RAB) bengkel untuk diajukan
                    ke Waka Sarpras.</p>
            </div>

            <div>
                <!-- Tombol Pintar "Generate" dengan style stand-out (outline primary) -->
                <button type="button" @click="generateFromLimit()"
                    class="inline-flex items-center px-4 py-2 bg-primary-50 border border-primary-200 hover:bg-primary-100 text-primary-700 text-sm font-semibold rounded-lg shadow-sm transition-colors">
                    <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Generate dari Stok Limit & Rusak ({{ $limitItems->count() }})
                </button>
            </div>
        </div>

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

        <!-- Form Container -->
        <form action="{{ route('toolman.pengadaan.store') }}" method="POST"
            class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            @csrf
            <!-- Section 1: Informasi Umum Pengajuan -->
            <div class="p-6 border-b border-gray-200 space-y-5 bg-gray-50/50">
                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Informasi Umum Pengajuan</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Judul Pengajuan -->
                    <div>
                        <label for="judul" class="block text-sm font-medium text-gray-700">Judul Pengajuan <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="judul" name="judul" required
                            value="{{ old('judul', 'Pengadaan Alat & Bahan Praktik ' . ($bengkel->nama ?? 'Bengkel') . ' ' . date('Y')) }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500 text-sm shadow-sm"
                            placeholder="Contoh: Pengadaan Alat Praktik Jaringan Genap 2026">
                    </div>

                    <!-- Kategori/Tujuan -->
                    <div>
                        <label for="kategori" class="block text-sm font-medium text-gray-700">Kategori / Tujuan
                            Pengadaan</label>
                        <select id="kategori" name="kategori"
                            class="mt-1 block w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500 text-sm shadow-sm">
                            <option value="rutin">Belanja Rutin (Bahan Habis Pakai / BHP)</option>
                            <option value="praktik" selected>Fasilitas Praktik Siswa Baru</option>
                            <option value="perbaikan">Perbaikan / Penggantian Alat Rusak</option>
                            <option value="ukk">Persiapan Uji Kompetensi Keahlian (UKK)</option>
                        </select>
                    </div>

                    <!-- Keterangan Tambahan -->
                    <div class="md:col-span-2">
                        <label for="keterangan" class="block text-sm font-medium text-gray-700">
                            Catatan & Justifikasi Pengajuan (Opsional)
                        </label>
                        <textarea id="keterangan" name="keterangan" rows="2"
                            class="mt-1 block w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500 text-sm shadow-sm"
                            placeholder="Berikan keterangan urgensi atau penjelasan kebutuhan alat/bahan...">{{ old('keterangan') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Daftar Barang (Tabel Dinamis) -->
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Daftar Barang & Rincian
                            Estimasi Biaya</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Cantumkan nama barang, spesifikasi teknis, jumlah, dan
                            estimasi harga satuan pasar.</p>
                    </div>
                    <button type="button" @click="addItem()"
                        class="inline-flex items-center text-xs font-bold text-primary-600 hover:text-primary-700 transition-colors bg-primary-50 px-3 py-1.5 rounded-lg border border-primary-100">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Baris Item
                    </button>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-slate-50 text-xs font-semibold text-gray-600 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left w-2/5">Nama & Spesifikasi Barang</th>
                                <th class="px-4 py-3 text-center w-28">Jumlah</th>
                                <th class="px-4 py-3 text-center w-24">Satuan</th>
                                <th class="px-4 py-3 text-right w-36">Est. Harga Satuan</th>
                                <th class="px-4 py-3 text-right w-36">Subtotal</th>
                                <th class="px-4 py-3 text-center w-14">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white text-sm">
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="group" :class="item.is_alert ? 'bg-amber-50/20' : ''">
                                    <!-- Nama & Spesifikasi -->
                                    <td class="px-4 py-3 align-top">
                                        <input type="hidden" :name="'items[' + index + '][barang_id]'" :value="item.barang_id">
                                        <input type="text" :name="'items[' + index + '][nama]'" x-model="item.nama"
                                            placeholder="Nama barang / alat..." required
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

                                        <template x-if="item.info_stok">
                                            <p class="text-[11px] text-amber-600 font-medium mt-1"
                                                x-text="'Rekomendasi sistem: ' + item.info_stok"></p>
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
                                        <div class="relative">
                                            <span
                                                class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none text-xs text-gray-400">Rp</span>
                                            <input type="number" min="0" step="1000" :name="'items[' + index + '][harga_satuan]'"
                                                x-model.number="item.harga_satuan" required
                                                class="block w-full pl-7 text-right border-gray-300 rounded-md text-sm focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                    </td>

                                    <!-- Subtotal -->
                                    <td
                                        class="px-4 py-3 align-top text-right font-bold text-gray-800 whitespace-nowrap pt-5">
                                        <span x-text="formatRupiah((item.jumlah || 0) * (item.harga_satuan || 0))"></span>
                                    </td>

                                    <!-- Aksi Hapus Baris -->
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

            <!-- Section 3: Footer Action -->
            <div
                class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-3">
                <a href="{{ route('toolman.pengadaan.index') }}"
                    class="text-xs font-semibold text-gray-600 hover:text-gray-900 transition-colors">
                    Batal dan Kembali
                </a>

                <div class="flex items-center gap-3">
                    <button type="submit" name="action" value="draft"
                        class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 text-xs font-semibold rounded-lg shadow-sm transition-colors">
                        Simpan sebagai Draf
                    </button>
                    <button type="button"
                        @click="window.openConfirmModal({
                            title: 'Kirim Usulan RAB ke Waka Sarpras',
                            message: 'Apakah Anda yakin ingin mengajukan usulan RAB ini ke Waka Sarpras? Pastikan seluruh item belanja dan estimasi anggaran telah diperiksa.',
                            type: 'primary',
                            confirmText: 'Ya, Kirim Usulan',
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
                        Kirim Pengajuan ke Waka
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
