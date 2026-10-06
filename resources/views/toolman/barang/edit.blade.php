@extends('layouts.admin')

@section('title', 'Edit Barang - ' . $barang->nama)
@section('header_title', 'Manajemen Barang Bengkel')

@section('content')
    <script>
        function barangEditData() {
            return {
                tipe: '{{ $barang->jenis_barang === 'bhp' ? 'bahan' : 'inventaris' }}',
                bengkelCode: '{{ $bengkel->kode ?? 'BGK' }}',
                kodeBarang: '{{ $barang->kode_barang }}',
                namaBarang: {!! json_encode($barang->nama) !!},
                lokasiId: '{{ $barang->lokasi_penyimpanan_id }}',
                satuan: '{{ $barang->satuan }}',

                // Kuantitas Alat Inventaris
                stokBaik: {{ (int) $barang->stok_tersedia }},
                stokRusakRingan: 0,
                stokRusakBerat: {{ (int) $barang->stok_rusak }},
                stokDipinjam: {{ (int) $barang->stok_dipinjam }},

                // Kuantitas Bahan Habis Pakai
                stokBahan: {{ (float) $barang->stok_tersedia }},
                batasMinimum: {{ (int) $barang->minimum_stok }},

                // Estimasi Harga & Spesifikasi
                estimasiHarga: '{{ old('estimasi_harga', $barang->harga ? (float) $barang->harga : '') }}',
                spesifikasi: {!! json_encode($barang->deskripsi ?? '') !!},

                // Modal state
                showDeleteModal: false,
                toastMessage: '',
                showToast: false,

                setTipe(val) {
                    this.tipe = val;
                },
                totalStokInventaris() {
                    return (parseInt(this.stokBaik) || 0) + (parseInt(this.stokRusakRingan) || 0) + (parseInt(this
                        .stokRusakBerat) || 0);
                },
                triggerToast(msg) {
                    this.toastMessage = msg;
                    this.showToast = true;
                    setTimeout(() => {
                        this.showToast = false;
                    }, 3500);
                },

                // Sumber Dana Modal State & Methods
                showSumberDanaModal: false,
                sdView: 'list', // 'list' | 'form'
                sdFormMode: 'create', // 'create' | 'edit'
                sdFormId: null,
                sdFormKode: '',
                sdFormNama: '',
                sdFormDeskripsi: '',
                sdLoading: false,
                sdError: '',
                sdSuccessMsg: '',
                sumberDanas: {!! json_encode($sumberDanas ?? []) !!},

                openSumberDanaModal(view = 'list') {
                    this.sdView = view;
                    this.sdError = '';
                    this.sdSuccessMsg = '';
                    if (view === 'create') {
                        this.sdFormMode = 'create';
                        this.sdFormId = null;
                        this.sdFormKode = '';
                        this.sdFormNama = '';
                        this.sdFormDeskripsi = '';
                        this.sdView = 'form';
                    }
                    this.showSumberDanaModal = true;
                },
                openEditSumberDana(item) {
                    this.sdFormMode = 'edit';
                    this.sdFormId = item.id;
                    this.sdFormKode = item.kode || '';
                    this.sdFormNama = item.nama || '';
                    this.sdFormDeskripsi = item.deskripsi || '';
                    this.sdError = '';
                    this.sdSuccessMsg = '';
                    this.sdView = 'form';
                },
                async refreshSumberDanas() {
                    try {
                        const res = await fetch('{{ route('toolman.sumber-dana.index') }}', {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        if (res.ok) {
                            const json = await res.json();
                            if (json.success && json.data) {
                                this.sumberDanas = json.data;
                                this.updateSumberDanaSelect();
                            }
                        }
                    } catch (e) {
                        console.error(e);
                    }
                },
                updateSumberDanaSelect(selectedId = null) {
                    const selectEl = document.getElementById('sumber_dana_id');
                    if (!selectEl) return;
                    const currentVal = selectedId || selectEl.value;

                    selectEl.innerHTML = '<option value="">-- Pilih Sumber Dana (Opsional) --</option>';
                    this.sumberDanas.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.text = item.kode ? item.nama + ' (' + item.kode + ')' : item.nama;
                        if (String(item.id) === String(currentVal)) {
                            opt.selected = true;
                        }
                        selectEl.appendChild(opt);
                    });
                    if (selectedId) {
                        selectEl.value = selectedId;
                    }
                },
                async saveSumberDana() {
                    if (!this.sdFormNama.trim()) {
                        this.sdError = 'Nama sumber dana wajib diisi!';
                        return;
                    }
                    this.sdLoading = true;
                    this.sdError = '';
                    try {
                        const url = this.sdFormMode === 'create' ?
                            '{{ route('toolman.sumber-dana.store') }}' :
                            '{{ url('/toolman/sumber-dana') }}/' + this.sdFormId;
                        const method = this.sdFormMode === 'create' ? 'POST' : 'PUT';

                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                kode: this.sdFormKode,
                                nama: this.sdFormNama,
                                deskripsi: this.sdFormDeskripsi
                            })
                        });
                        const data = await response.json();
                        if (!response.ok) {
                            throw new Error(data.message || (data.errors ? Object.values(data.errors).flat().join(
                                ', ') : 'Gagal menyimpan sumber dana'));
                        }

                        await this.refreshSumberDanas();

                        if (this.sdFormMode === 'create') {
                            this.updateSumberDanaSelect(data.data.id);
                            this.sdSuccessMsg = 'Sumber dana "' + data.data.nama +
                                '" berhasil ditambahkan dan langsung dipilih!';
                        } else {
                            this.sdSuccessMsg = 'Sumber dana "' + data.data.nama + '" berhasil diperbarui!';
                        }

                        this.sdView = 'list';
                        setTimeout(() => {
                            this.sdSuccessMsg = '';
                        }, 3000);
                    } catch (err) {
                        this.sdError = err.message;
                    } finally {
                        this.sdLoading = false;
                    }
                },
                async deleteSumberDana(item) {
                    window.openConfirmModal({
                        title: 'Konfirmasi Hapus Sumber Dana',
                        message: `Apakah Anda yakin ingin menghapus sumber dana <strong>"${item.nama}"</strong>?`,
                        subMessage: 'Tindakan ini tidak dapat dibatalkan jika data sumber dana belum terikat ke barang.',
                        type: 'danger',
                        confirmText: 'Ya, Hapus',
                        cancelText: 'Batal',
                        onConfirm: async () => {
                            this.sdLoading = true;
                            this.sdError = '';
                            try {
                                const response = await fetch('{{ url('/toolman/sumber-dana') }}/' + item.id, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                });
                                const data = await response.json();
                                if (!response.ok) {
                                    throw new Error(data.message || 'Gagal menghapus sumber dana');
                                }
                                await this.refreshSumberDanas();
                                this.sdSuccessMsg = 'Sumber dana berhasil dihapus!';
                                setTimeout(() => {
                                    this.sdSuccessMsg = '';
                                }, 3000);
                            } catch (err) {
                                this.sdError = err.message;
                            } finally {
                                this.sdLoading = false;
                            }
                        }
                    });
                }
            };
        }
    </script>

    <div class="max-w-5xl mx-auto space-y-6 pb-12" x-data="barangEditData()">

        <!-- Toast Notification Floating -->
        <div x-show="showToast" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2"
            class="fixed bottom-6 right-6 z-50 bg-gray-900 text-white px-5 py-3 rounded-xl shadow-xl flex items-center gap-3 border border-gray-700 text-sm max-w-md"
            style="display: none;">
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span x-text="toastMessage"></span>
        </div>

        <!-- Breadcrumb & Top Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <!-- Breadcrumb -->
                <nav class="flex items-center text-xs font-medium text-gray-500 mb-2 space-x-2">
                    <a href="/toolman/dashboard" class="hover:text-primary-600 transition-colors">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('toolman.barang.index') }}"
                        class="hover:text-primary-600 transition-colors">Manajemen Barang</a>
                    <span>/</span>
                    <span class="text-gray-400 font-mono">{{ $barang->kode_barang }}</span>
                    <span>/</span>
                    <span class="text-primary-700 font-semibold">Edit Barang</span>
                </nav>
                <div class="flex flex-wrap items-center gap-3">
                    <h3 class="text-2xl font-bold text-gray-900 tracking-tight">Edit Data Barang</h3>
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                        {{ $barang->kode_barang }}
                    </span>
                    @if ($barang->stok_dipinjam > 0)
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 mr-1.5 animate-pulse"></span>
                            Sedang Dipinjam ({{ $barang->stok_dipinjam }} {{ $barang->satuan }})
                        </span>
                    @else
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                            Tersedia
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-1">Perbarui spesifikasi, lokasi simpan fisik, atau sesuaikan status
                    kondisi aset bengkel.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('toolman.barang.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>

        <!-- Callout Banner: Peringatan Barang Sedang Dipinjam (Sesuai Aturan PRD) -->
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl flex items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h5 class="text-sm font-bold text-blue-900">Perhatian: Barang Sedang Aktif Dipinjam</h5>
                    <p class="text-xs text-blue-800 mt-0.5 leading-relaxed">
                        Saat ini sebanyak <strong>1 Unit</strong> sedang dipinjam oleh <span class="font-semibold">Budi
                            Santoso (XI TKJ 1)</span> melalui Tiket <span
                            class="font-mono font-semibold">#TKJ-2026-089</span> (Batas kembali: Hari ini, 16:00 WIB).
                        Sesuai aturan PRD, stok tidak boleh dikurangi di bawah kuantitas yang sedang dipinjam, dan aset
                        tidak dapat dihapus sampai transaksi selesai.
                    </p>
                </div>
            </div>
            <a href="/toolman/sirkulasi/peminjaman"
                class="text-xs font-semibold text-blue-700 hover:text-blue-900 underline shrink-0 whitespace-nowrap">
                Lihat Tiket &rarr;
            </a>
        </div>

        @if (session('success'))
            <div
                class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-800 flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800 flex items-center gap-3">
                <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800 space-y-1">
                <div class="font-bold flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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

        <!-- Form Wrapper -->
        <form action="{{ route('toolman.barang.update', $barang->id) }}" method="POST" enctype="multipart/form-data"
            class="space-y-6" data-confirm="true" data-title="Konfirmasi Perbarui Data Barang"
            data-message="Apakah Anda yakin ingin menyimpan perubahan data dan penyesuaian stok untuk barang <b>{{ addslashes($barang->nama) }}</b>?"
            data-type="primary" data-confirm-text="Ya, Perbarui Barang">
            @csrf
            @method('PUT')

            <!-- SECTION 1: Klasifikasi Tipe Barang -->
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-base font-semibold text-gray-900">Klasifikasi Tipe Barang</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Pencatatan inventaris menggunakan metode quantity-based.</p>
                    </div>
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                        <svg class="w-3.5 h-3.5 mr-1 text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                            </path>
                        </svg>
                        Tipe Terkunci pada Aset
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Option A: Alat Inventaris (Active) -->
                    <div :class="tipe === 'inventaris' ? 'border-primary-500 ring-2 ring-primary-500/20 bg-primary-50/20' :
                        'border-gray-200 bg-gray-50 opacity-60'"
                        class="border-2 rounded-xl p-4 transition-all flex items-start gap-4 cursor-default">
                        <div :class="tipe === 'inventaris' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-500'"
                            class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <h5 class="font-bold text-gray-900 text-sm">Alat Inventaris</h5>
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">
                                    Wajib Dikembalikan
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                Peralatan fisik yang dipinjamkan dan wajib dikembalikan pada hari yang sama. Memiliki
                                pencatatan kondisi fisik (baik/rusak).
                            </p>
                        </div>
                    </div>

                    <!-- Option B: Bahan Habis Pakai (BHP) -->
                    <div :class="tipe === 'bahan' ? 'border-orange-500 ring-2 ring-orange-500/20 bg-orange-50/20' :
                        'border-gray-200 bg-gray-50 opacity-60'"
                        class="border-2 rounded-xl p-4 transition-all flex items-start gap-4 cursor-default">
                        <div :class="tipe === 'bahan' ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-500'"
                            class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                </path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <h5 class="font-bold text-gray-900 text-sm">Bahan Habis Pakai (BHP)</h5>
                                <span
                                    class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-orange-100 text-orange-800">
                                    Stok Berkurang Permanen
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                Material praktik yang habis dikonsumsi peminjam. Memerlukan batas batas limit stok untuk
                                memicu pengajuan RAB otomatis.
                            </p>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="tipe" :value="tipe">
            </div>

            <!-- SECTION 2: Identitas & Lokasi Penyimpanan -->
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-5">
                <div class="border-b border-gray-100 pb-3">
                    <h4 class="text-base font-semibold text-gray-900">Informasi Identitas & Lokasi</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Detail identitas barang dan penempatan fisik di area bengkel.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <!-- Kode Barang (Read-only for existing assets) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="kode_barang" class="block text-sm font-medium text-gray-700">
                                Kode Barang (ID Aset)
                            </label>
                            <span
                                class="text-[11px] font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded flex items-center gap-1">
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                                Tidak Dapat Diubah
                            </span>
                        </div>
                        <div class="relative">
                            <input type="text" id="kode_barang" name="kode_barang" x-model="kodeBarang" readonly
                                class="block w-full font-mono text-sm font-bold text-gray-700 rounded-lg border-gray-200 bg-gray-50 shadow-sm focus:ring-0 focus:border-gray-200 cursor-not-allowed">
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">
                            Kode aset dibuat permanen untuk menjaga konsistensi riwayat peminjaman.
                        </p>
                    </div>

                    <!-- Nama Barang -->
                    <div>
                        <label for="nama_barang" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Nama Barang <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_barang" name="nama_barang" x-model="namaBarang" required
                            class="block w-full text-sm rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm"
                            placeholder="Contoh: Router Mikrotik RB951">
                        <p class="text-[11px] text-gray-400 mt-1">Nama ini tampil di katalog pencarian siswa & guru.</p>
                    </div>

                    <!-- Bengkel Terisolasi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Bengkel / Jurusan Pemilik
                        </label>
                        <div
                            class="flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-600">
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                </path>
                            </svg>
                            <span class="font-medium text-gray-800">{{ $bengkel->nama ?? 'Bengkel' }}</span>
                            <span
                                class="ml-auto text-[10px] bg-primary-100 text-primary-700 px-2 py-0.5 rounded font-semibold uppercase">
                                {{ $bengkel->kode ?? 'TERISOLASI' }}
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Data aset terisolasi pada lingkup bengkel
                            {{ $bengkel->nama ?? '' }} (PRD Scope).</p>
                    </div>

                    <!-- Lokasi Penyimpanan Fisik -->
                    <div>
                        <label for="lokasi_penyimpanan_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Lokasi Penyimpanan <span class="text-red-500">*</span>
                        </label>
                        <select id="lokasi_penyimpanan_id" name="lokasi_penyimpanan_id" x-model="lokasiId" required
                            class="block w-full text-sm rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm bg-white">
                            <option value="">-- Pilih Lokasi Penyimpanan --</option>
                            @foreach ($lokasiPenyimpanans as $lok)
                                <option value="{{ $lok->id }}">{{ $lok->nama }} ({{ $lok->kode }})</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">Lokasi memudahkan Toolman saat menyiapkan serah terima
                            alat.</p>
                    </div>

                    <!-- Sumber Dana -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="sumber_dana_id" class="block text-sm font-medium text-gray-700">
                                Sumber Dana <span class="text-xs text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <button type="button" @click="openSumberDanaModal('list')"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-primary-600 hover:text-primary-700 transition-colors bg-primary-50 hover:bg-primary-100/80 px-2.5 py-1 rounded-md">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span>Kelola Sumber Dana</span>
                            </button>
                        </div>
                        <select id="sumber_dana_id" name="sumber_dana_id"
                            class="block w-full text-sm rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm bg-white">
                            <option value="">-- Pilih Sumber Dana (Opsional) --</option>
                            @foreach ($sumberDanas as $sd)
                                <option value="{{ $sd->id }}"
                                    {{ old('sumber_dana_id', $barang->sumber_dana_id) == $sd->id ? 'selected' : '' }}>
                                    {{ $sd->nama }} {{ $sd->kode ? "({$sd->kode})" : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="flex items-center justify-between mt-1 text-[11px] text-gray-400">
                            <span>Asal anggaran pengadaan (BOS, DAK, Komite, dll).</span>
                            <button type="button" @click="openSumberDanaModal('create')"
                                class="text-primary-600 hover:text-primary-700 font-medium inline-flex items-center hover:underline">
                                + Tambah Baru
                            </button>
                        </div>
                    </div>

                    <!-- Satuan Barang -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="satuan" class="block text-sm font-medium text-gray-700">
                                Satuan Hitung <span class="text-red-500">*</span>
                            </label>
                            <a href="{{ route('toolman.satuan.index') }}" target="_blank"
                                class="text-xs text-primary-600 hover:text-primary-700 font-medium inline-flex items-center hover:underline">
                                + Kelola Satuan
                            </a>
                        </div>
                        <select id="satuan" name="satuan" x-model="satuan" required
                            class="block w-full text-sm rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm">
                            @if (isset($satuans) && $satuans->count() > 0)
                                @foreach ($satuans as $sItem)
                                    <option value="{{ $sItem->nama }}"
                                        {{ $barang->satuan === $sItem->nama ? 'selected' : '' }}>
                                        {{ $sItem->nama }} {{ $sItem->singkatan ? '(' . $sItem->singkatan . ')' : '' }}
                                    </option>
                                @endforeach
                            @else
                                <option value="Unit">Unit</option>
                                <option value="Pcs">Pcs / Buah</option>
                                <option value="Set">Set / Kotak Lengkap</option>
                                <option value="Pack">Pack</option>
                                <option value="Roll">Roll / Gulung</option>
                                <option value="Meter">Meter</option>
                                <option value="Box">Box / Kotak</option>
                                <option value="Lembar">Lembar</option>
                                <option value="Batang">Batang</option>
                                <option value="Liter">Liter</option>
                                <option value="Botol">Botol</option>
                                <option value="Pasang">Pasang</option>
                            @endif
                        </select>
                    </div>

                </div>
            </div>

            <!-- SECTION 3: Kuantitas, Kondisi Fisik, & Stok Limit (Quantity-Based) -->
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h4 class="text-base font-semibold text-gray-900">Manajemen Kuantitas & Kondisi Fisik</h4>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Perbarui jumlah unit berdasarkan hasil pengecekan fisik di bengkel.
                        </p>
                    </div>
                    <span
                        class="text-xs font-semibold px-2.5 py-1 rounded bg-blue-50 text-blue-700 border border-blue-200">
                        1 Unit Sedang Dipinjam
                    </span>
                </div>

                <!-- SUB-SECTION: JIKA ALAT INVENTARIS -->
                <div x-show="tipe === 'inventaris'" class="space-y-4">
                    <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-lg flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-xs text-emerald-800 leading-relaxed">
                            <strong>Aturan Kondisi Fisik:</strong> Unit dengan <span
                                class="font-semibold text-emerald-700">"Kondisi Baik"</span> yang tidak sedang dipinjam
                            dapat langsung dipinjam siswa/guru. Unit rusak dialokasikan ke draf pengadaan/perbaikan.
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Stok Baik -->
                        <div class="p-4 bg-green-50/50 border border-green-200 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <label for="stok_baik" class="text-xs font-bold text-green-900 uppercase tracking-wider">
                                    Kondisi Baik (Total)
                                </label>
                                <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="number" id="stok_baik" name="stok_baik" x-model.number="stokBaik"
                                    min="1" required
                                    class="block w-full text-center text-lg font-bold rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500">
                                <span class="text-xs font-semibold text-gray-500" x-text="satuan">Unit</span>
                            </div>
                            <p class="text-[10px] text-green-700 mt-2 font-medium">1 unit sedang aktif dipinjam.</p>
                        </div>

                        <!-- Stok Rusak Ringan -->
                        <div class="p-4 bg-amber-50/50 border border-amber-200 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <label for="stok_rusak_ringan"
                                    class="text-xs font-bold text-amber-900 uppercase tracking-wider">
                                    Rusak Ringan
                                </label>
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="number" id="stok_rusak_ringan" name="stok_rusak_ringan"
                                    x-model.number="stokRusakRingan" min="0"
                                    class="block w-full text-center text-lg font-bold rounded-lg border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                                <span class="text-xs font-semibold text-gray-500" x-text="satuan">Unit</span>
                            </div>
                            <p class="text-[10px] text-amber-700 mt-2">Dalam perawatan teknisi.</p>
                        </div>

                        <!-- Stok Rusak Berat -->
                        <div class="p-4 bg-red-50/50 border border-red-200 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <label for="stok_rusak_berat"
                                    class="text-xs font-bold text-red-900 uppercase tracking-wider">
                                    Rusak Berat (Afkir)
                                </label>
                                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="number" id="stok_rusak_berat" name="stok_rusak_berat"
                                    x-model.number="stokRusakBerat" min="0"
                                    class="block w-full text-center text-lg font-bold rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500">
                                <span class="text-xs font-semibold text-gray-500" x-text="satuan">Unit</span>
                            </div>
                            <p class="text-[10px] text-red-700 mt-2">Diusulkan untuk penggantian RAB.</p>
                        </div>
                    </div>

                    <!-- Rekap Kuantitas -->
                    <div
                        class="flex flex-col sm:flex-row justify-between items-start sm:items-center px-4 py-3 bg-slate-50 border border-gray-200 rounded-lg text-sm gap-2">
                        <span class="font-medium text-gray-700">Total Kuantitas Fisik Barang:</span>
                        <div class="font-bold text-gray-900 flex items-center gap-2">
                            <span class="text-lg text-primary-700" x-text="totalStokInventaris()">1</span>
                            <span x-text="satuan">Unit</span>
                            <span class="text-xs font-normal text-gray-500">(1 Unit dibawa peminjam, 0 Unit tersedia di
                                rak)</span>
                        </div>
                    </div>
                </div>

                <!-- SUB-SECTION: JIKA BAHAN HABIS PAKAI (BHP) -->
                <div x-show="tipe === 'bahan'" class="space-y-4" style="display: none;">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="stok_bahan" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Kuantitas Sisa Stok <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="number" id="stok_bahan" name="stok_bahan" step="0.1" min="0"
                                    x-model="stokBahan"
                                    class="block w-full text-base font-bold rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm">
                                <span
                                    class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-xs font-semibold text-gray-600 shrink-0"
                                    x-text="satuan">Roll</span>
                            </div>
                        </div>

                        <div>
                            <label for="batas_minimum" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Batas Minimum Stok (Low-Stock Alert) <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="number" id="batas_minimum" name="batas_minimum" step="0.1"
                                    min="0" x-model="batasMinimum"
                                    class="block w-full text-base font-bold rounded-lg border-amber-300 focus:ring-amber-500 focus:border-amber-500 shadow-sm bg-amber-50/30">
                                <span
                                    class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-xs font-semibold text-gray-600 shrink-0"
                                    x-text="satuan">Roll</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- SECTION 4: Spesifikasi Teknis & Estimasi Harga -->
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-5">
                <div class="border-b border-gray-100 pb-3">
                    <h4 class="text-base font-semibold text-gray-900">Spesifikasi Teknis & Estimasi Harga</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Spesifikasi detail untuk acuan peminjam dan generator RAB
                        pengadaan.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="spesifikasi" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Spesifikasi Teknis & Keterangan
                        </label>
                        <textarea id="spesifikasi" name="spesifikasi" rows="4" x-model="spesifikasi"
                            class="block w-full text-sm rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm"></textarea>
                        <p class="text-[11px] text-gray-400 mt-1">Siswa dapat membaca spesifikasi ini saat memilih barang
                            di katalog.</p>
                    </div>

                    <div>
                        <label for="estimasi_harga" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Estimasi Harga Satuan (Acuan RAB)
                        </label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 font-medium text-sm">
                                Rp
                            </div>
                            <input type="number" id="estimasi_harga" name="estimasi_harga" x-model="estimasiHarga"
                                class="block w-full pl-10 text-sm rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm">
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Digunakan untuk mengisi estimasi biaya saat barang
                            diajukan ke RAB pengadaan.</p>
                    </div>
                </div>
            </div>

            <!-- SECTION 5: Log Mutasi & Riwayat Aset (Informasi Audit) -->
            <div
                class="bg-gray-50 border border-gray-200 rounded-xl p-5 shadow-sm text-xs text-gray-600 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="space-y-1">
                    <p class="font-semibold text-gray-800 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Log Mutasi & Riwayat Sirkulasi Aset
                    </p>
                    <p class="text-gray-500">Didaftarkan: <span class="font-medium text-gray-700">10 Jan 2026</span> oleh
                        <span class="font-medium text-gray-700">Toolman TKJ</span> &bull; Terakhir Dicek: <span
                            class="font-medium text-gray-700">28 Feb 2026</span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-gray-300 font-medium text-gray-700">
                        12x Peminjaman Selesai
                    </span>
                </div>
            </div>

            <!-- Action Footer (Sticky Bar) -->
            <div
                class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <!-- Tombol Hapus Aset -->
                    <button type="button" @click="showDeleteModal = true"
                        class="px-4 py-2.5 bg-white border border-red-200 text-red-600 hover:bg-red-50 text-sm font-semibold rounded-lg shadow-sm transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                        Hapus Barang
                    </button>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <a href="{{ route('toolman.barang.index') }}"
                        class="px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg shadow-sm transition-colors text-center">
                        Batal
                    </a>

                    <button type="submit"
                        class="inline-flex items-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </div>

        </form>

        <!-- ============================================================== -->
        <!-- MODAL KONFIRMASI HAPUS BARANG                                  -->
        <!-- ============================================================== -->
        <div x-show="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900/60" @click="showDeleteModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-red-100">
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-900">Hapus Data Barang?</h4>
                                <p class="text-xs text-gray-500 mt-1">
                                    Aset <strong class="text-gray-800">{{ $barang->nama }}
                                        ({{ $barang->kode_barang }})</strong> akan
                                    dihapus permanen dari inventaris bengkel.
                                </p>
                            </div>
                        </div>

                        @if ($barang->stok_dipinjam > 0)
                            <!-- Warning: Sedang dipinjam -->
                            <div
                                class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-800 leading-relaxed">
                                <strong>Tidak Dapat Dihapus:</strong> Barang ini sedang dalam status aktif dipinjam oleh
                                siswa
                                ({{ $barang->stok_dipinjam }} {{ $barang->satuan }}). Anda harus menunggu pengembalian
                                barang dan menyelesaikan tiket terlebih dahulu
                                sebelum dapat menghapus aset ini.
                            </div>
                        @else
                            <p class="mt-4 text-xs text-gray-500">
                                Tindakan ini bersifat permanen dan tidak dapat dibatalkan. Pastikan data barang sudah tidak
                                lagi dibutuhkan.
                            </p>
                        @endif
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                        <button type="button" @click="showDeleteModal = false"
                            class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 text-xs font-semibold rounded-lg shadow-sm transition-colors">
                            Batal
                        </button>
                        @if ($barang->stok_dipinjam > 0)
                            <button type="button" disabled
                                class="px-5 py-2 bg-gray-300 text-gray-500 text-xs font-bold rounded-lg shadow-sm cursor-not-allowed">
                                Hapus Permanen
                            </button>
                        @else
                            <form action="{{ route('toolman.barang.destroy', $barang->id) }}" method="POST"
                                class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                                    Hapus Permanen
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- MODAL CRUD: KELOLA SUMBER DANA                                    -->
        <!-- ================================================================= -->
        <div x-show="showSumberDanaModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div x-show="showSumberDanaModal" x-transition:enter="transition ease-out duration-200 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                @click.away="showSumberDanaModal = false"
                class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-gray-100 space-y-4">

                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-primary-100 text-primary-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-gray-900"
                                x-text="sdView === 'form' ? (sdFormMode === 'create' ? 'Tambah Sumber Dana' : 'Edit Sumber Dana') : 'Kelola Sumber Dana'">
                            </h4>
                            <p class="text-xs text-gray-500 mt-0.5">Daftar sumber dana / mata anggaran sekolah (berlaku
                                lintas bengkel).</p>
                        </div>
                    </div>
                    <button type="button" @click="showSumberDanaModal = false"
                        class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Alert Messages inside modal -->
                <div x-show="sdError" x-cloak
                    class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg flex items-start gap-2">
                    <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span x-text="sdError"></span>
                </div>
                <div x-show="sdSuccessMsg" x-cloak
                    class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-lg flex items-start gap-2">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span x-text="sdSuccessMsg"></span>
                </div>

                <!-- VIEW 1: LIST SUMBER DANA -->
                <div x-show="sdView === 'list'" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500"
                            x-text="'Total: ' + (sumberDanas ? sumberDanas.length : 0) + ' Sumber Dana'"></span>
                        <button type="button" @click="openSumberDanaModal('create')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            <span>Tambah Sumber Dana</span>
                        </button>
                    </div>

                    <div class="border border-gray-200 rounded-xl overflow-hidden max-h-72 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th scope="col" class="px-3 py-2.5 text-left">Nama & Kode</th>
                                    <th scope="col" class="px-3 py-2.5 text-left">Keterangan</th>
                                    <th scope="col" class="px-3 py-2.5 text-center">Barang</th>
                                    <th scope="col" class="px-3 py-2.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <template x-for="item in sumberDanas" :key="item.id">
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-3 py-2.5 font-medium text-gray-900 whitespace-nowrap">
                                            <div class="font-semibold" x-text="item.nama"></div>
                                            <div class="font-mono text-[10px] text-gray-400" x-text="item.kode || '-'">
                                            </div>
                                        </td>
                                        <td class="px-3 py-2.5 text-gray-500 max-w-[160px] truncate"
                                            x-text="item.deskripsi || '-'"></td>
                                        <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-700"
                                                x-text="(item.barangs_count || 0) + ' item'"></span>
                                        </td>
                                        <td class="px-3 py-2.5 text-right whitespace-nowrap space-x-1">
                                            <button type="button" @click="openEditSumberDana(item)"
                                                class="text-primary-600 hover:text-primary-800 p-1 hover:bg-primary-50 rounded transition-colors"
                                                title="Edit">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                    </path>
                                                </svg>
                                            </button>
                                            <button type="button" @click="deleteSumberDana(item)"
                                                :disabled="item.barangs_count > 0 || sdLoading"
                                                :class="item.barangs_count > 0 ? 'text-gray-300 cursor-not-allowed' :
                                                    'text-red-500 hover:text-red-700 hover:bg-red-50'"
                                                class="p-1 rounded transition-colors"
                                                :title="item.barangs_count > 0 ? 'Tidak dapat dihapus karena digunakan ' + item
                                                    .barangs_count + ' barang' : 'Hapus'">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="!sumberDanas || sumberDanas.length === 0">
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-400 text-xs">
                                            Belum ada sumber dana. Klik "Tambah Sumber Dana" untuk menambahkan.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="button" @click="showSumberDanaModal = false"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>

                <!-- VIEW 2: FORM TAMBAH / EDIT SUMBER DANA -->
                <div x-show="sdView === 'form'" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Nama Sumber Dana <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="sdFormNama"
                            placeholder="Contoh: BOS Reguler 2026, Komite Sekolah, DAK Fisik SMK"
                            class="w-full px-3 py-2 text-xs rounded-lg border-gray-300 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Kode Sumber Dana (Opsional)
                        </label>
                        <input type="text" x-model="sdFormKode" placeholder="Contoh: BOS-2026, DAK-2025, KOMITE"
                            class="w-full px-3 py-2 font-mono text-xs uppercase rounded-lg border-gray-300 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Keterangan / Deskripsi (Opsional)
                        </label>
                        <textarea x-model="sdFormDeskripsi" rows="2" placeholder="Catatan peruntukan atau rincian sumber dana..."
                            class="w-full px-3 py-2 text-xs rounded-lg border-gray-300 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <button type="button" @click="sdView = 'list'"
                            class="px-3.5 py-1.5 bg-white border border-gray-300 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-50 transition-colors">
                            &larr; Kembali ke Daftar
                        </button>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="showSumberDanaModal = false"
                                class="px-3 py-1.5 text-gray-500 hover:text-gray-700 text-xs font-medium transition-colors">
                                Batal
                            </button>
                            <button type="button" @click="saveSumberDana()" :disabled="sdLoading"
                                class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors disabled:opacity-50">
                                <svg x-show="sdLoading" class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span
                                    x-text="sdLoading ? 'Menyimpan...' : (sdFormMode === 'create' ? 'Simpan & Pilih' : 'Perbarui')"></span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection
