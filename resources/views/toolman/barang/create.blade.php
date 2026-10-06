@extends('layouts.admin')

@section('title', 'Tambah Barang Baru')
@section('header_title', 'Manajemen Barang Bengkel')

@section('content')
    <script>
        function barangCreateData() {
            return {
                tipe: 'inventaris', // 'inventaris' | 'bahan'
                bengkelCode: '{{ $bengkel->kode ?? 'BGK' }}',
                kodeBarang: 'INV-{{ $bengkel->kode ?? 'BGK' }}-' + (Math.floor(Math.random() * 900) + 100),
                satuan: 'Unit',
                // Kuantitas Alat Inventaris
                stokBaik: 1,
                stokRusakRingan: 0,
                stokRusakBerat: 0,
                // Kuantitas Bahan Habis Pakai
                stokBahan: 1,
                batasMinimum: 1,
                // Estimasi Harga
                estimasiHarga: '{{ old('estimasi_harga', '') }}',
                generateKode() {
                    const randomNum = Math.floor(Math.random() * 900) + 100;
                    if (this.tipe === 'inventaris') {
                        this.kodeBarang = 'INV-' + this.bengkelCode + '-' + randomNum;
                    } else {
                        this.kodeBarang = 'BHP-' + this.bengkelCode + '-' + randomNum;
                    }
                },
                setTipe(val) {
                    this.tipe = val;
                    if (val === 'inventaris') {
                        if (this.satuan === 'Roll' || this.satuan === 'Meter') {
                            this.satuan = 'Unit';
                        }
                        if (this.kodeBarang.startsWith('BHP-')) {
                            this.kodeBarang = this.kodeBarang.replace('BHP-', 'INV-');
                        }
                    } else {
                        if (this.satuan === 'Unit') {
                            this.satuan = 'Roll';
                        }
                        if (this.kodeBarang.startsWith('INV-')) {
                            this.kodeBarang = this.kodeBarang.replace('INV-', 'BHP-');
                        }
                    }
                },
                totalStokInventaris() {
                    return (parseInt(this.stokBaik) || 0) + (parseInt(this.stokRusakRingan) || 0) + (parseInt(this
                        .stokRusakBerat) || 0);
                },
                // Quick Tambah Lokasi Modal State & Method
                showLokasiModal: false,
                newLokasiKode: '',
                newLokasiNama: '',
                newLokasiDeskripsi: '',
                lokasiLoading: false,
                lokasiError: '',
                lokasiSuccessMsg: '',
                async submitLokasi() {
                    if (!this.newLokasiKode.trim() || !this.newLokasiNama.trim()) {
                        this.lokasiError = 'Kode dan Nama lokasi wajib diisi!';
                        return;
                    }
                    this.lokasiLoading = true;
                    this.lokasiError = '';
                    try {
                        const response = await fetch('{{ route('toolman.lokasi.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                kode: this.newLokasiKode,
                                nama: this.newLokasiNama,
                                deskripsi: this.newLokasiDeskripsi
                            })
                        });
                        const data = await response.json();
                        if (!response.ok) {
                            throw new Error(data.message || (data.errors ? Object.values(data.errors).flat().join(
                                ', ') : 'Gagal menyimpan lokasi'));
                        }
                        const selectEl = document.getElementById('lokasi_penyimpanan_id');
                        const option = document.createElement('option');
                        option.value = data.data.id;
                        option.text = data.data.nama + ' (' + data.data.kode + ')';
                        option.selected = true;
                        selectEl.appendChild(option);
                        selectEl.value = data.data.id;

                        this.lokasiSuccessMsg = 'Lokasi ' + data.data.nama + ' (' + data.data.kode +
                            ') berhasil dibuat dan langsung dipilih!';
                        this.newLokasiKode = '';
                        this.newLokasiNama = '';
                        this.newLokasiDeskripsi = '';
                        this.showLokasiModal = false;

                        setTimeout(() => {
                            this.lokasiSuccessMsg = '';
                        }, 4000);
                    } catch (err) {
                        this.lokasiError = err.message;
                    } finally {
                        this.lokasiLoading = false;
                    }
                },

                // Quick Tambah Satuan Modal State & Methods
                showSatuanModal: false,
                newSatuanNama: '',
                newSatuanSingkatan: '',
                newSatuanDeskripsi: '',
                satuanLoading: false,
                satuanError: '',
                satuanSuccessMsg: '',

                openSatuanModal() {
                    this.newSatuanNama = '';
                    this.newSatuanSingkatan = '';
                    this.newSatuanDeskripsi = '';
                    this.satuanError = '';
                    this.showSatuanModal = true;
                },

                async submitSatuan() {
                    if (!this.newSatuanNama.trim()) {
                        this.satuanError = 'Nama satuan wajib diisi!';
                        return;
                    }
                    this.satuanLoading = true;
                    this.satuanError = '';

                    try {
                        const res = await fetch('{{ route('toolman.satuan.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                nama: this.newSatuanNama,
                                singkatan: this.newSatuanSingkatan,
                                deskripsi: this.newSatuanDeskripsi
                            })
                        });

                        const data = await res.json();
                        if (!res.ok) {
                            throw new Error(data.message || 'Gagal menyimpan satuan.');
                        }

                        // Tambahkan ke dropdown satuan dan pilih
                        const selectEl = document.getElementById('satuan');
                        const option = document.createElement('option');
                        option.value = data.data.nama;
                        option.text = data.data.nama + (data.data.singkatan ? ' (' + data.data.singkatan + ')' : '');
                        option.selected = true;
                        selectEl.appendChild(option);
                        this.satuan = data.data.nama;

                        this.satuanSuccessMsg = 'Satuan ' + data.data.nama + ' berhasil dibuat dan langsung dipilih!';
                        this.newSatuanNama = '';
                        this.newSatuanSingkatan = '';
                        this.newSatuanDeskripsi = '';
                        this.showSatuanModal = false;

                        setTimeout(() => {
                            this.satuanSuccessMsg = '';
                        }, 4000);
                    } catch (err) {
                        this.satuanError = err.message;
                    } finally {
                        this.satuanLoading = false;
                    }
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
                            this.showSumberDanaModal = false;
                        } else {
                            this.sdSuccessMsg = 'Sumber dana "' + data.data.nama + '" berhasil diperbarui!';
                            this.sdView = 'list';
                        }
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
                },
            };
        }
    </script>

    <div class="max-w-5xl mx-auto space-y-6 pb-12" x-data="barangCreateData()">

        <!-- Breadcrumb & Top Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <!-- Breadcrumb -->
                <nav class="flex items-center text-xs font-medium text-gray-500 mb-2 space-x-2">
                    <a href="/toolman/dashboard" class="hover:text-primary-600 transition-colors">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('toolman.barang.index') }}" class="hover:text-primary-600 transition-colors">Manajemen
                        Barang</a>
                    <span>/</span>
                    <span class="text-primary-700 font-semibold">Tambah Barang Baru</span>
                </nav>
                <h3 class="text-2xl font-bold text-gray-900 tracking-tight">Tambah Barang Baru</h3>
                <p class="text-sm text-gray-500 mt-1">Daftarkan alat inventaris atau bahan habis pakai ke inventaris bengkel
                    jurusan.</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('toolman.barang.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali ke Katalog
                </a>
            </div>
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
        <form action="{{ route('toolman.barang.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
            data-confirm="true" data-title="Konfirmasi Tambah Barang Baru"
            data-message="Pastikan data klasifikasi, kuantitas stok, dan lokasi penyimpanan barang sudah sesuai sebelum disimpan ke master inventaris bengkel."
            data-type="primary" data-confirm-text="Ya, Simpan Barang">
            @csrf

            <!-- SECTION 1: Pilih Tipe Barang (Interactive Segmented Cards) -->
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-base font-semibold text-gray-900">Klasifikasi Tipe Barang</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Tentukan jenis pencatatan sesuai aturan operasional bengkel.
                        </p>
                    </div>
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary-50 text-primary-700 border border-primary-100">
                        Quantity-Based Tracking
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Option A: Alat Inventaris -->
                    <div @click="setTipe('inventaris')"
                        :class="tipe === 'inventaris' ? 'border-primary-500 ring-2 ring-primary-500/20 bg-primary-50/20' :
                            'border-gray-200 hover:border-gray-300 bg-white'"
                        class="cursor-pointer border-2 rounded-xl p-4 transition-all flex items-start gap-4">
                        <div :class="tipe === 'inventaris' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-500'"
                            class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 transition-colors">
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
                                Peralatan fisik (router, tang crimping, tester) yang dipinjamkan dan wajib dikembalikan pada
                                hari yang sama. Memiliki pencatatan kondisi fisik (baik/rusak).
                            </p>
                        </div>
                    </div>

                    <!-- Option B: Bahan Habis Pakai (BHP) -->
                    <div @click="setTipe('bahan')"
                        :class="tipe === 'bahan' ? 'border-orange-500 ring-2 ring-orange-500/20 bg-orange-50/20' :
                            'border-gray-200 hover:border-gray-300 bg-white'"
                        class="cursor-pointer border-2 rounded-xl p-4 transition-all flex items-start gap-4">
                        <div :class="tipe === 'bahan' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-500'"
                            class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 transition-colors">
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
                                Material praktik (kabel UTP, timah solder, konektor RJ45) yang habis terpakai. Memerlukan
                                batas batas limit stok untuk memicu pengajuan RAB otomatis.
                            </p>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="tipe" :value="tipe">
            </div>

            <!-- SECTION 2: Informasi Dasar & Lokasi Barang -->
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-5">
                <div class="border-b border-gray-100 pb-3">
                    <h4 class="text-base font-semibold text-gray-900">Informasi Identitas & Lokasi</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Detail identitas barang dan penempatan fisik di area bengkel.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <!-- Kode Barang -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="kode_barang" class="block text-sm font-medium text-gray-700">
                                Kode Barang <span class="text-red-500">*</span>
                            </label>
                            <button type="button" @click="generateKode()"
                                class="text-xs font-semibold text-primary-600 hover:text-primary-700 flex items-center transition-colors">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                                Acak Kode
                            </button>
                        </div>
                        <div class="relative">
                            <input type="text" id="kode_barang" name="kode_barang" x-model="kodeBarang" required
                                class="block w-full font-mono text-sm font-semibold text-gray-900 rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm"
                                placeholder="INV-TKJ-046">
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">
                            Prefix otomatis: <span class="font-mono text-primary-600">INV-</span> (Alat) atau <span
                                class="font-mono text-orange-600">BHP-</span> (Bahan).
                        </p>
                    </div>

                    <!-- Nama Barang -->
                    <div>
                        <label for="nama_barang" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Nama Barang <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_barang" name="nama_barang" required
                            class="block w-full text-sm rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm"
                            placeholder="Contoh: Router Mikrotik RB951, Kabel LAN Cat6, Crimping Tool...">
                        <p class="text-[11px] text-gray-400 mt-1">Gunakan nama yang umum dikenal siswa dan instruktur
                            bengkel.</p>
                    </div>

                    <!-- Bengkel Terisolasi (PRD: wajib terikat bengkel_id) -->
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
                        <p class="text-[11px] text-gray-400 mt-1">Terkunci otomatis pada hak bengkel akun Anda (PRD Bengkel
                            Scope).</p>
                    </div>

                    <!-- Lokasi Penyimpanan Fisik -->
                    <div>
                        <label for="lokasi_penyimpanan_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Lokasi Penyimpanan <span class="text-red-500">*</span>
                        </label>
                        <select id="lokasi_penyimpanan_id" name="lokasi_penyimpanan_id" required
                            class="block w-full text-sm rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm bg-white">
                            <option value="">-- Pilih Lokasi Penyimpanan --</option>
                            @foreach ($lokasiPenyimpanans as $lokasi)
                                <option value="{{ $lokasi->id }}"
                                    {{ old('lokasi_penyimpanan_id') == $lokasi->id ? 'selected' : '' }}>
                                    {{ $lokasi->nama }} ({{ $lokasi->kode }})
                                </option>
                            @endforeach
                        </select>
                        <div class="flex items-center justify-between mt-1 text-[11px] text-gray-400">
                            <span>Pilih lemari, rak, atau laci penyimpanan di bengkel ini.</span>
                            <button type="button" @click="showLokasiModal = true"
                                class="text-primary-600 hover:text-primary-700 font-medium inline-flex items-center hover:underline">
                                + Tambah Baru
                            </button>
                        </div>

                        <!-- Notifikasi Sukses Tambah Lokasi Cepat -->
                        <div x-show="lokasiSuccessMsg" x-cloak x-transition
                            class="mt-2 p-2.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-xs flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            <span x-text="lokasiSuccessMsg"></span>
                        </div>
                    </div>

                    <!-- Sumber Dana -->
                    <div>
                        <label for="sumber_dana_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Sumber Dana <span class="text-xs text-gray-400 font-normal">(Opsional)</span>
                        </label>
                        <select id="sumber_dana_id" name="sumber_dana_id"
                            class="block w-full text-sm rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm bg-white">
                            <option value="">-- Pilih Sumber Dana (Opsional) --</option>
                            @foreach ($sumberDanas as $sd)
                                <option value="{{ $sd->id }}"
                                    {{ old('sumber_dana_id') == $sd->id ? 'selected' : '' }}>
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

                        <!-- Notifikasi Sukses Tambah Sumber Dana Cepat -->
                        <div x-show="sdSuccessMsg" x-cloak x-transition
                            class="mt-2 p-2.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-xs flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            <span x-text="sdSuccessMsg"></span>
                        </div>
                    </div>

                    <!-- Satuan Barang -->
                    <div>
                        <label for="satuan" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Satuan Hitung <span class="text-red-500">*</span>
                        </label>
                        <select id="satuan" name="satuan" x-model="satuan" required
                            class="block w-full text-sm rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm">
                            @if (isset($satuans) && $satuans->count() > 0)
                                @foreach ($satuans as $sItem)
                                    <option value="{{ $sItem->nama }}">
                                        {{ $sItem->nama }} {{ $sItem->singkatan ? '(' . $sItem->singkatan . ')' : '' }}
                                    </option>
                                @endforeach
                            @else
                                <option value="Unit">Unit</option>
                                <option value="Pcs">Pcs / Buah</option>
                                <option value="Set">Set / Kotak Lengkap</option>
                                <option value="Roll">Roll / Gulung</option>
                                <option value="Meter">Meter</option>
                                <option value="Box">Box / Kotak</option>
                                <option value="Lembar">Lembar</option>
                                <option value="Batang">Batang</option>
                                <option value="Liter">Liter</option>
                                <option value="Botol">Botol</option>
                                <option value="Pack">Pack</option>
                                <option value="Pasang">Pasang</option>
                            @endif
                        </select>
                        <div class="flex items-center justify-between mt-1 text-[11px] text-gray-400">
                            <span>Pilih satuan hitung inventaris atau bahan praktik.</span>
                            <button type="button" @click="openSatuanModal()"
                                class="text-primary-600 hover:text-primary-700 font-medium inline-flex items-center hover:underline">
                                + Tambah Baru
                            </button>
                        </div>

                        <!-- Notifikasi Sukses Tambah Satuan Cepat -->
                        <div x-show="satuanSuccessMsg" x-cloak x-transition
                            class="mt-2 p-2.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-xs flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            <span x-text="satuanSuccessMsg"></span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- SECTION 3: Kuantitas, Kondisi Fisik, & Stok Limit (Quantity-Based) -->
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h4 class="text-base font-semibold text-gray-900">Manajemen Kuantitas & Stok Awal</h4>
                        <p class="text-xs text-gray-500 mt-0.5">
                            <span x-show="tipe === 'inventaris'">Catat jumlah unit berdasarkan kondisi fisik saat barang
                                masuk ke bengkel.</span>
                            <span x-show="tipe === 'bahan'">Catat saldo awal bahan dan tentukan batas limit peringatan
                                restock (RAB).</span>
                        </p>
                    </div>
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
                            <strong>Aturan Inventaris:</strong> Hanya barang dengan kondisi <span
                                class="font-semibold text-emerald-700">"Kondisi Baik"</span> yang dapat dipinjam oleh
                            siswa/guru di katalog. Unit rusak akan otomatis dialokasikan ke antrean perbaikan atau draf RAB.
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Stok Baik -->
                        <div class="p-4 bg-green-50/50 border border-green-200 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <label for="stok_baik" class="text-xs font-bold text-green-900 uppercase tracking-wider">
                                    Kondisi Baik (Siap Pinjam)
                                </label>
                                <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="number" id="stok_baik" name="stok_baik" x-model.number="stokBaik"
                                    min="0" required
                                    class="block w-full text-center text-lg font-bold rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500">
                                <span class="text-xs font-semibold text-gray-500" x-text="satuan">Unit</span>
                            </div>
                            <p class="text-[10px] text-green-700 mt-2">Langsung tampil di katalog peminjam.</p>
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
                            <p class="text-[10px] text-amber-700 mt-2">Masuk antrean perbaikan internal.</p>
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
                            <p class="text-[10px] text-red-700 mt-2">Diusulkan untuk penggantian via RAB.</p>
                        </div>
                    </div>

                    <!-- Total Rekap Kuantitas -->
                    <div
                        class="flex justify-between items-center px-4 py-3 bg-slate-50 border border-gray-200 rounded-lg text-sm">
                        <span class="font-medium text-gray-700">Total Kuantitas Fisik Barang:</span>
                        <div class="font-bold text-gray-900 flex items-center gap-1.5">
                            <span class="text-lg text-primary-700" x-text="totalStokInventaris()">1</span>
                            <span x-text="satuan">Unit</span>
                            <span class="text-xs font-normal text-gray-500">(Quantity-Based Single Entity)</span>
                        </div>
                    </div>
                </div>

                <!-- SUB-SECTION: JIKA BAHAN HABIS PAKAI (BHP) -->
                <div x-show="tipe === 'bahan'" class="space-y-4" style="display: none;">
                    <div class="p-4 bg-orange-50/80 border border-orange-200 rounded-lg flex items-start gap-3">
                        <svg class="w-5 h-5 text-orange-600 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                        <div class="text-xs text-orange-900 leading-relaxed">
                            <strong>Aturan Bahan Habis Pakai:</strong> Stok akan terpotong permanen saat tiket peminjaman
                            disetujui. Tentukan <strong>Batas Minimum Stok</strong> agar sistem otomatis mengingatkan Anda
                            saat stok menipis dan memasukkannya ke generator RAB pengadaan.
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Stok Bahan Awal -->
                        <div>
                            <label for="stok_bahan" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Kuantitas Stok Awal <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="number" id="stok_bahan" name="stok_bahan" step="0.1" min="0.1"
                                    x-model="stokBahan"
                                    class="block w-full text-base font-bold rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm"
                                    placeholder="Contoh: 5 atau 2.5">
                                <span
                                    class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-xs font-semibold text-gray-600 shrink-0"
                                    x-text="satuan">Roll</span>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1">Mendukung angka desimal (contoh: 2.5 Roll kabel).</p>
                        </div>

                        <!-- Batas Minimum Stok (Low Stock Alert Threshold) -->
                        <div>
                            <label for="batas_minimum" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Batas Minimum Stok (Low-Stock Alert) <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="number" id="batas_minimum" name="batas_minimum" step="0.1"
                                    min="0" x-model="batasMinimum"
                                    class="block w-full text-base font-bold rounded-lg border-amber-300 focus:ring-amber-500 focus:border-amber-500 shadow-sm bg-amber-50/30"
                                    placeholder="Contoh: 1">
                                <span
                                    class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-xs font-semibold text-gray-600 shrink-0"
                                    x-text="satuan">Roll</span>
                            </div>
                            <p class="text-[11px] text-amber-700 mt-1 font-medium">
                                Peringatan muncul jika stok sisa &le; batas minimum ini.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- SECTION 4: Spesifikasi Teknis & Estimasi Harga -->
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-5">
                <div class="border-b border-gray-100 pb-3">
                    <h4 class="text-base font-semibold text-gray-900">Spesifikasi Teknis & Estimasi Harga</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Lengkapi spesifikasi untuk acuan peminjam dan referensi
                        pengadaan.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Spesifikasi / Deskripsi Teknis -->
                    <div>
                        <label for="spesifikasi" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Spesifikasi Teknis & Keterangan Tambahan
                        </label>
                        <textarea id="spesifikasi" name="spesifikasi" rows="4"
                            class="block w-full text-sm rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm"
                            placeholder="Tuliskan merk, tipe chipset, nomor model, panjang/kapasitas, atau panduan penggunaan..."></textarea>
                        <p class="text-[11px] text-gray-400 mt-1">Deskripsi ini akan dibaca oleh siswa saat melihat katalog
                            peminjaman.</p>
                    </div>

                    <!-- Estimasi Harga Satuan (Untuk RAB) -->
                    <div>
                        <label for="estimasi_harga" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Estimasi Harga Satuan (Opsional)
                        </label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 font-medium text-sm">
                                Rp
                            </div>
                            <input type="number" id="estimasi_harga" name="estimasi_harga" x-model="estimasiHarga"
                                class="block w-full pl-10 text-sm rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm"
                                placeholder="Contoh: 1850000">
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Digunakan sebagai perkiraan otomatis saat men-generate
                            draf RAB Pengadaan ke Waka Sarpras.</p>
                    </div>
                </div>
            </div>

            <!-- Sticky / Floating Action Footer -->
            <div
                class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center text-xs text-gray-500">
                    <svg class="w-4 h-4 text-gray-400 mr-2 shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Pastikan data fisik dan klasifikasi barang telah dicek langsung di bengkel.</span>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <a href="{{ route('toolman.barang.index') }}"
                        class="px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg shadow-sm transition-colors text-center">
                        Batal
                    </a>

                    <button type="submit" name="action" value="save_and_add"
                        class="px-4 py-2.5 bg-primary-50 border border-primary-200 hover:bg-primary-100 text-primary-700 text-sm font-semibold rounded-lg shadow-sm transition-colors">
                        Simpan & Tambah Lagi
                    </button>

                    <button type="submit" name="action" value="save"
                        class="inline-flex items-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Simpan Barang
                    </button>
                </div>
            </div>

        </form>

        <!-- ================================================================= -->
        <!-- MODAL CEPAT: TAMBAH LOKASI PENYIMPANAN BARU                       -->
        <!-- ================================================================= -->
        <div x-show="showLokasiModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div x-show="showLokasiModal" x-transition:enter="transition ease-out duration-200 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                @click.away="showLokasiModal = false"
                class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 space-y-4">

                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h4 class="text-base font-bold text-gray-900">Tambah Lokasi Baru</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Daftarkan titik simpan di
                            {{ $bengkel->nama ?? 'Bengkel' }}</p>
                    </div>
                    <button type="button" @click="showLokasiModal = false"
                        class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Error Alert inside modal -->
                <div x-show="lokasiError" x-cloak
                    class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg flex items-start gap-2">
                    <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span x-text="lokasiError"></span>
                </div>

                <div class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Kode Lokasi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="newLokasiKode" placeholder="Contoh: LOK-RAK3, LEMARI-B"
                            class="w-full px-3 py-2 font-mono text-xs uppercase rounded-lg border-gray-300 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                        <p class="text-[10px] text-gray-400 mt-0.5">Kode harus unik di bengkel Anda (PRD 3.3).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Nama Lokasi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="newLokasiNama" placeholder="Contoh: Rak Komponen 3, Lemari Alat B"
                            class="w-full px-3 py-2 text-xs rounded-lg border-gray-300 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Keterangan / Posisi Fisik (Opsional)
                        </label>
                        <textarea x-model="newLokasiDeskripsi" rows="2" placeholder="Catatan posisi atau keterangan lokasi..."
                            class="w-full px-3 py-2 text-xs rounded-lg border-gray-300 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                    <button type="button" @click="showLokasiModal = false"
                        class="px-3.5 py-1.5 bg-white border border-gray-300 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="button" @click="submitLokasi()" :disabled="lokasiLoading"
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors disabled:opacity-50">
                        <svg x-show="lokasiLoading" class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span x-text="lokasiLoading ? 'Menyimpan...' : 'Simpan & Pilih'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- MODAL CEPAT: TAMBAH SATUAN BARU                                   -->
        <!-- ================================================================= -->
        <div x-show="showSatuanModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div x-show="showSatuanModal" x-transition:enter="transition ease-out duration-200 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                @click.away="showSatuanModal = false"
                class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 space-y-4">

                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h4 class="text-base font-bold text-gray-900">Tambah Satuan Baru</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Daftarkan jenis satuan hitung barang</p>
                    </div>
                    <button type="button" @click="showSatuanModal = false"
                        class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Error Alert inside modal -->
                <div x-show="satuanError" x-cloak
                    class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg flex items-start gap-2">
                    <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span x-text="satuanError"></span>
                </div>

                <div class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Nama Satuan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="newSatuanNama" placeholder="Contoh: Lembar, Botol, Pack, Roll"
                            class="w-full px-3 py-2 text-xs rounded-lg border-gray-300 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Singkatan / Simbol (Opsional)
                        </label>
                        <input type="text" x-model="newSatuanSingkatan" placeholder="Contoh: lbr, btl, pack, roll"
                            class="w-full px-3 py-2 text-xs rounded-lg border-gray-300 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Keterangan / Peruntukan (Opsional)
                        </label>
                        <textarea x-model="newSatuanDeskripsi" rows="2" placeholder="Catatan peruntukan satuan..."
                            class="w-full px-3 py-2 text-xs rounded-lg border-gray-300 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                    <button type="button" @click="showSatuanModal = false"
                        class="px-3.5 py-1.5 bg-white border border-gray-300 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="button" @click="submitSatuan()" :disabled="satuanLoading"
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors disabled:opacity-50">
                        <svg x-show="satuanLoading" class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span x-text="satuanLoading ? 'Menyimpan...' : 'Simpan & Pilih'"></span>
                    </button>
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

                    <div class="flex items-center justify-end pt-3 border-t border-gray-100">
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
