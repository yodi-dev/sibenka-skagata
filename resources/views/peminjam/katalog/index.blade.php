@extends('layouts.peminjam')

@section('title', 'Katalog Barang')

@section('content')
    @php
        $selectedTipe = request('tipe', '');
        $selectedStatus = request('status', '');
        $selectedBengkelId = request('bengkel_id', $bengkel?->id ?? '');
    @endphp

    <div x-data="katalogApp()" x-cloak
        @keydown.escape.window="detailModalOpen = false; cartDrawerOpen = false; checkoutModalOpen = false;"
        x-effect="document.body.classList.toggle('overflow-hidden', detailModalOpen || cartDrawerOpen || checkoutModalOpen)"
        class="space-y-4 sm:space-y-5">

        <!-- Toast Notification -->
        <div x-show="toast.show" x-transition
            class="fixed top-20 right-4 left-4 sm:left-auto sm:right-6 z-50 max-w-sm bg-white border shadow-xl rounded-2xl p-4 flex items-start gap-3"
            :class="{
                'border-emerald-200 bg-emerald-50/95 text-emerald-900': toast.type === 'success',
                'border-amber-200 bg-amber-50/95 text-amber-900': toast.type === 'warning',
                'border-blue-200 bg-blue-50/95 text-blue-900': toast.type === 'info',
                'border-rose-200 bg-rose-50/95 text-rose-900': toast.type === 'error'
            }">
            <div class="shrink-0 mt-0.5">
                <template x-if="toast.type === 'success'">
                    <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </template>
                <template x-if="toast.type === 'warning' || toast.type === 'error'">
                    <div class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>
                </template>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-xs sm:text-sm font-bold truncate" x-text="toast.title"></h4>
                <p class="text-xs text-gray-600 mt-0.5" x-text="toast.message"></p>
            </div>
            <button @click="toast.show = false" class="text-gray-400 hover:text-gray-600 transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- BANNER HIMBAUAN: PENGGUNAAN ALAT (Dismissable & Non-distracting) -->
        <div x-data="{
            dismissed: localStorage.getItem('sibenka_katalog_tip_closed') === 'true',
            close() {
                this.dismissed = true;
                localStorage.setItem('sibenka_katalog_tip_closed', 'true');
            }
        }" x-show="!dismissed" x-transition
            class="bg-amber-50/80 border border-amber-200/80 rounded-2xl p-3 sm:p-4 flex items-start justify-between gap-3 text-amber-900 shadow-2xs">
            <div class="flex items-start gap-2.5 min-w-0">
                <span
                    class="w-6 h-6 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5 text-xs font-black">
                    !
                </span>
                <div class="text-xs space-y-0.5 min-w-0">
                    <p class="font-bold text-amber-950">Jaga & Rawat Peralatan Praktik Bersama</p>
                    <p class="text-amber-800 text-[11px] leading-relaxed">
                        Gunakan peralatan sesuai SOP keselamatan bengkel dan kembalikan tepat waktu ke meja Toolman dalam
                        keadaan bersih dan lengkap.
                    </p>
                </div>
            </div>
            <button type="button" @click="close()"
                class="p-1 text-amber-600 hover:text-amber-900 rounded-lg hover:bg-amber-100 transition-colors shrink-0"
                title="Tutup pesan ini">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- HEADER & FILTER HUB (Compact & Unified) -->
        <div class="bg-white border border-gray-200 rounded-2xl p-3.5 sm:p-4 shadow-2xs space-y-3">
            <!-- Row 1: Compact Search Input (Opsi A) & Sub-filters -->
            <form method="GET" action="{{ route('peminjam.katalog.index') }}"
                class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
                @if (request('tipe'))
                    <input type="hidden" name="tipe" value="{{ request('tipe') }}">
                @endif

                <!-- Compact Search Input -->
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama alat atau bahan..."
                        class="w-full pl-9 pr-8 py-2 bg-gray-50 focus:bg-white text-gray-900 border border-gray-200 rounded-xl text-xs sm:text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none">
                    @if (request('search'))
                        <a href="{{ route('peminjam.katalog.index', array_merge(request()->query(), ['search' => null, 'page' => 1])) }}"
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600"
                            title="Hapus pencarian">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </a>
                    @endif
                </div>

                <!-- Sub-filters: Bengkel & Status Stok -->
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
                    @if ($isGuru)
                        <select name="bengkel_id" onchange="this.form.submit()"
                            class="text-xs bg-gray-50 border border-gray-200 rounded-xl py-2 px-2.5 font-semibold text-gray-700 focus:ring-primary-500 focus:border-primary-500">
                            @foreach ($bengkels as $b)
                                <option value="{{ $b->id }}"
                                    {{ (string) $selectedBengkelId === (string) $b->id ? 'selected' : '' }}>
                                    {{ $b->nama }} ({{ $b->kode }})
                                </option>
                            @endforeach
                        </select>
                    @else
                        <div
                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-600 shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span
                                class="truncate max-w-[130px] sm:max-w-none">{{ $bengkel->nama ?? 'Bengkel Saya' }}</span>
                        </div>
                    @endif

                    <select name="status" onchange="this.form.submit()"
                        class="text-xs bg-gray-50 border border-gray-200 rounded-xl py-2 px-2.5 font-medium text-gray-700 focus:ring-primary-500 focus:border-primary-500">
                        <option value="" {{ empty($selectedStatus) ? 'selected' : '' }}>Semua Stok</option>
                        <option value="tersedia" {{ $selectedStatus === 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                        <option value="mepet" {{ $selectedStatus === 'mepet' ? 'selected' : '' }}>Stok Menipis</option>
                        <option value="habis" {{ $selectedStatus === 'habis' ? 'selected' : '' }}>Habis / Dipinjam
                        </option>
                    </select>

                    @if (request()->anyFilled(['search', 'status']))
                        <a href="{{ route('peminjam.katalog.index', array_merge($isGuru && request('bengkel_id') ? ['bengkel_id' => request('bengkel_id')] : [], request('tipe') ? ['tipe' => request('tipe')] : [])) }}"
                            class="text-xs text-rose-600 hover:text-rose-700 font-semibold px-2 py-1.5 rounded-lg hover:bg-rose-50 transition-colors shrink-0"
                            title="Reset filter & pencarian">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <!-- Row 2: Category Chips & Total Count -->
            <div class="flex items-center justify-between gap-3 pt-2.5 border-t border-gray-100">
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5 text-xs font-semibold">
                    <!-- Chip: Semua -->
                    <a href="{{ route('peminjam.katalog.index', array_merge(request()->query(), ['tipe' => null, 'page' => 1])) }}"
                        class="px-3 py-1.5 rounded-xl shrink-0 transition-all flex items-center gap-1.5 shadow-2xs {{ empty($selectedTipe) ? 'bg-primary-600 text-white shadow-xs' : 'bg-gray-50 border border-gray-200 text-gray-600 hover:bg-gray-100' }}">
                        <span>Semua</span>
                        <span
                            class="text-[10px] px-1.5 py-0.2 rounded-full {{ empty($selectedTipe) ? 'bg-primary-800 text-white' : 'bg-gray-200/80 text-gray-600' }}">
                            {{ $totalCount }}
                        </span>
                    </a>

                    <!-- Chip: Alat Inventaris -->
                    <a href="{{ route('peminjam.katalog.index', array_merge(request()->query(), ['tipe' => 'inventaris', 'page' => 1])) }}"
                        class="px-3 py-1.5 rounded-xl shrink-0 transition-all flex items-center gap-1.5 shadow-2xs {{ $selectedTipe === 'inventaris' ? 'bg-primary-600 text-white shadow-xs' : 'bg-gray-50 border border-gray-200 text-gray-600 hover:bg-gray-100' }}">
                        <span>Alat Inventaris</span>
                        <span
                            class="text-[10px] px-1.5 py-0.2 rounded-full {{ $selectedTipe === 'inventaris' ? 'bg-primary-800 text-white' : 'bg-gray-200/80 text-gray-600' }}">
                            {{ $inventarisCount }}
                        </span>
                    </a>

                    <!-- Chip: BHP -->
                    <a href="{{ route('peminjam.katalog.index', array_merge(request()->query(), ['tipe' => 'bhp', 'page' => 1])) }}"
                        class="px-3 py-1.5 rounded-xl shrink-0 transition-all flex items-center gap-1.5 shadow-2xs {{ $selectedTipe === 'bhp' ? 'bg-amber-600 text-white shadow-xs' : 'bg-gray-50 border border-gray-200 text-gray-600 hover:bg-gray-100' }}">
                        <span>BHP</span>
                        <span
                            class="text-[10px] px-1.5 py-0.2 rounded-full {{ $selectedTipe === 'bhp' ? 'bg-amber-800 text-white' : 'bg-gray-200/80 text-gray-600' }}">
                            {{ $bhpCount }}
                        </span>
                    </a>
                </div>

                <div class="text-[11px] text-gray-400 shrink-0 hidden sm:block">
                    Total: <strong class="text-gray-700">{{ $barangs->total() }}</strong> barang
                </div>
            </div>
        </div>

        <!-- 3. GRID KATALOG BARANG (2-Kolom Mobile, 3-4 Kolom Desktop) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
            @forelse ($barangs as $item)
                @php
                    $isInventaris = $item->jenis_barang === 'inventaris';
                    $stokTersediaBebas = $item->stok_bebas;
                    $isLowStock = $stokTersediaBebas > 0 && $stokTersediaBebas <= $item->minimum_stok;
                    $isOutOfStock = $stokTersediaBebas <= 0;
                @endphp

                <div
                    class="bg-white border rounded-2xl shadow-2xs overflow-hidden flex flex-col justify-between transition-all hover:shadow-md hover:border-primary-200 group {{ $isOutOfStock ? 'border-gray-200 bg-slate-50/60 opacity-75' : 'border-gray-200' }}">

                    <!-- Card Body -->
                    <div class="p-3 sm:p-4 flex-1 flex flex-col justify-between">
                        <!-- Top: Category Badge & Info Button -->
                        <div class="flex items-center justify-between gap-1 mb-2">
                            <span
                                class="inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold uppercase tracking-wide {{ $isInventaris ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-amber-50 text-amber-700 border border-amber-200/60' }}">
                                {{ $isInventaris ? 'Alat' : 'BHP' }}
                            </span>
                            <button type="button"
                                @click="openItemDetail({
                                id: {{ $item->id }},
                                bengkelId: {{ $item->bengkel_id }},
                                bengkelNama: '{{ addslashes($item->bengkel->nama ?? '') }}',
                                kode: '{{ $item->kode_barang }}',
                                nama: '{{ addslashes($item->nama) }}',
                                tipe: '{{ $item->jenis_barang }}',
                                satuan: '{{ $item->satuan }}',
                                stok: {{ $item->stok_bebas }},
                                stokFisik: {{ $item->stok_tersedia }},
                                stokReserved: {{ $item->stok_reserved }},
                                stokTotal: {{ $item->stok_total }},
                                stokDipinjam: {{ $item->stok_dipinjam }},
                                stokRusak: {{ $item->stok_rusak }},
                                lokasi: '{{ addslashes($item->lokasiPenyimpanan->nama ?? 'Gudang Bengkel') }}',
                                deskripsi: '{{ addslashes($item->deskripsi ?? 'Tidak ada catatan deskripsi tambahan.') }}'
                            })"
                                class="text-gray-400 hover:text-gray-600 p-1 rounded-md hover:bg-gray-100 transition-colors"
                                title="Lihat rincian lengkap">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                        </div>

                        <!-- Name (Clickable) -->
                        <div class="cursor-pointer mb-2"
                            @click="openItemDetail({
                            id: {{ $item->id }},
                            bengkelId: {{ $item->bengkel_id }},
                            bengkelNama: '{{ addslashes($item->bengkel->nama ?? '') }}',
                            kode: '{{ $item->kode_barang }}',
                            nama: '{{ addslashes($item->nama) }}',
                            tipe: '{{ $item->jenis_barang }}',
                            satuan: '{{ $item->satuan }}',
                            stok: {{ $item->stok_bebas }},
                            stokFisik: {{ $item->stok_tersedia }},
                            stokReserved: {{ $item->stok_reserved }},
                            stokTotal: {{ $item->stok_total }},
                            stokDipinjam: {{ $item->stok_dipinjam }},
                            stokRusak: {{ $item->stok_rusak }},
                            lokasi: '{{ addslashes($item->lokasiPenyimpanan->nama ?? 'Gudang Bengkel') }}',
                            deskripsi: '{{ addslashes($item->deskripsi ?? 'Tidak ada catatan deskripsi tambahan.') }}'
                        })">
                            <h3 class="font-bold text-gray-900 text-xs sm:text-sm group-hover:text-primary-600 transition-colors leading-snug line-clamp-2"
                                title="{{ $item->nama }}">
                                {{ $item->nama }}
                            </h3>
                        </div>

                        <!-- Stock Status -->
                        <div class="mt-auto pt-1">
                            @if ($item->stok_bebas > $item->minimum_stok)
                                <div
                                    class="flex items-center gap-1.5 text-[10px] sm:text-[11px] font-semibold text-emerald-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                    <span class="truncate">{{ $item->stok_bebas }} {{ $item->satuan }} bebas</span>
                                </div>
                            @elseif ($isLowStock)
                                <div
                                    class="flex items-center gap-1.5 text-[10px] sm:text-[11px] font-semibold text-amber-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
                                    <span class="truncate">Sisa {{ $item->stok_bebas }} {{ $item->satuan }} bebas</span>
                                </div>
                            @else
                                <div
                                    class="flex items-center gap-1.5 text-[10px] sm:text-[11px] font-medium text-gray-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300 shrink-0"></span>
                                    <span>{{ $item->stok_tersedia > 0 ? 'Habis (Dipesan)' : 'Habis' }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Card Action Footer -->
                    <div class="px-2.5 pb-2.5 sm:px-3 sm:pb-3 pt-0">
                        @if ($item->stok_bebas > 0)
                            <template x-if="!isInCart({{ $item->id }})">
                                <button
                                    @click="addToCart({
                                        id: {{ $item->id }},
                                        bengkelId: {{ $item->bengkel_id }},
                                        kode: '{{ $item->kode_barang }}',
                                        nama: '{{ addslashes($item->nama) }}',
                                        tipe: '{{ $item->jenis_barang }}',
                                        satuan: '{{ $item->satuan }}',
                                        stok: {{ $item->stok_bebas }}
                                    }, 1)"
                                    type="button"
                                    class="w-full py-1.5 sm:py-2 px-2 rounded-xl text-xs font-bold shadow-2xs transition-all flex items-center justify-center gap-1 active:scale-95 {{ $isInventaris ? 'bg-primary-600 hover:bg-primary-700 text-white' : 'bg-amber-600 hover:bg-amber-700 text-white' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span>Pinjam</span>
                                </button>
                            </template>

                            <template x-if="isInCart({{ $item->id }})">
                                <div
                                    class="w-full flex items-center justify-between bg-primary-50 border border-primary-300 rounded-xl px-1.5 py-1">
                                    <button @click="decreaseQty({{ $item->id }})"
                                        class="w-6 h-6 rounded-lg bg-white text-primary-700 font-bold hover:bg-primary-100 flex items-center justify-center shadow-2xs text-xs">
                                        -
                                    </button>
                                    <span class="text-xs font-bold text-primary-900"
                                        x-text="getCartQty({{ $item->id }})"></span>
                                    <button @click="increaseQty({{ $item->id }})"
                                        :disabled="getCartQty({{ $item->id }}) >= {{ $item->stok_bebas }}"
                                        class="w-6 h-6 rounded-lg bg-white text-primary-700 font-bold hover:bg-primary-100 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center shadow-2xs text-xs">
                                        +
                                    </button>
                                </div>
                            </template>
                        @else
                            <button disabled
                                class="w-full py-1.5 sm:py-2 px-2 bg-gray-100 text-gray-400 text-xs font-medium rounded-xl cursor-not-allowed text-center">
                                {{ $item->stok_tersedia > 0 ? 'Habis (Dipesan)' : 'Habis' }}
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div
                    class="col-span-full bg-white rounded-3xl p-8 sm:p-12 text-center border border-gray-200 shadow-xs max-w-md mx-auto">
                    <div
                        class="w-14 h-14 rounded-2xl bg-gray-100 text-gray-400 mx-auto flex items-center justify-center mb-3.5">
                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">Barang Tidak Ditemukan</h3>
                    <p class="text-xs text-gray-500 mt-1">Tidak ada alat atau bahan yang sesuai dengan kriteria pencarian
                        atau filter yang dipilih.</p>
                    @if (request()->anyFilled(['search', 'tipe', 'status']))
                        <a href="{{ route('peminjam.katalog.index') }}"
                            class="mt-4 inline-block px-4 py-2 bg-primary-50 text-primary-700 hover:bg-primary-100 font-semibold rounded-xl text-xs transition-colors">
                            Reset Semua Filter
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($barangs->hasPages())
            <div class="pt-4">
                {{ $barangs->links() }}
            </div>
        @endif

        <!-- 4. FLOATING ACTION CART BAR (Desktop Only - Mobile uses Top Bar Cart) -->
        <div x-show="cart.length > 0" x-transition class="hidden sm:block fixed sm:bottom-6 sm:right-6 z-40 sm:max-w-md">
            <div
                class="bg-gradient-to-r from-gray-900 to-slate-800 text-white rounded-2xl p-3.5 sm:p-4 shadow-2xl border border-white/10 flex items-center justify-between gap-3">
                <div class="flex items-center space-x-3 cursor-pointer" @click="cartDrawerOpen = true">
                    <div
                        class="w-10 h-10 rounded-xl bg-primary-500 text-white flex items-center justify-center font-bold relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span
                            class="absolute -top-1.5 -right-1.5 bg-rose-500 text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-extrabold border-2 border-gray-900"
                            x-text="totalCartCount"></span>
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold leading-tight"
                            x-text="totalCartCount + ' Barang Dipilih'"></h4>
                        <p class="text-[11px] text-gray-300"
                            x-text="cartInventarisCount + ' Inventaris &bull; ' + cartBahanCount + ' BHP'"></p>
                    </div>
                </div>
                <button @click="openCheckoutModal()"
                    class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white text-xs font-bold rounded-xl shadow-xs transition-all shrink-0 flex items-center gap-1.5">
                    <span>Ajukan Tiket</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- 5. MODAL DETAIL BARANG -->
        <div x-show="detailModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div x-show="detailModalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @click="detailModalOpen = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity">
            </div>

            <div class="flex min-h-screen items-center justify-center p-3 sm:p-4 text-center">
                <div x-show="detailModalOpen" x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100"
                    x-transition:leave="ease-in duration-150" x-transition:leave-start="scale-100 opacity-100"
                    x-transition:leave-end="scale-95 opacity-0"
                    class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-lg border border-gray-100">

                    <template x-if="activeItem">
                        <div>
                            <!-- Header Modal -->
                            <div
                                class="p-5 sm:p-6 border-b border-gray-100 flex items-start justify-between gap-3 bg-slate-50/70">
                                <div class="flex items-center space-x-3.5">
                                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-xs shrink-0"
                                        :class="activeItem.tipe === 'inventaris' ? 'bg-emerald-100 text-emerald-700' :
                                            'bg-amber-100 text-amber-700'">
                                        <template x-if="activeItem.tipe === 'inventaris'">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </template>
                                        <template x-if="activeItem.tipe !== 'inventaris'">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </template>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded"
                                                :class="activeItem.tipe === 'inventaris' ? 'bg-emerald-100 text-emerald-800' :
                                                    'bg-amber-100 text-amber-800'"
                                                x-text="activeItem.tipe === 'inventaris' ? 'Alat Inventaris' : 'Bahan Habis Pakai'"></span>
                                            <span class="font-mono text-xs text-gray-400 font-semibold"
                                                x-text="activeItem.kode"></span>
                                        </div>
                                        <h3 class="text-base sm:text-lg font-bold text-gray-900 mt-1 leading-snug"
                                            x-text="activeItem.nama"></h3>
                                    </div>
                                </div>
                                <button @click="detailModalOpen = false"
                                    class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Body Modal -->
                            <div class="p-5 sm:p-6 space-y-4 text-xs sm:text-sm">
                                <div class="grid grid-cols-2 gap-3 bg-gray-50 p-3.5 rounded-xl border border-gray-200">
                                    <div>
                                        <span class="text-gray-500 text-xs block">Lokasi Penyimpanan:</span>
                                        <strong class="text-gray-800" x-text="activeItem.lokasi"></strong>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 text-xs block">Bengkel:</span>
                                        <strong class="text-gray-800" x-text="activeItem.bengkelNama"></strong>
                                    </div>
                                </div>

                                <!-- Stok Breakdown -->
                                <div class="border border-gray-200 rounded-xl p-3.5 space-y-2">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Informasi
                                        Ketersediaan Fisik:</h4>
                                    <div class="grid grid-cols-4 gap-2 text-center pt-1">
                                        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-2">
                                            <span class="text-[10px] text-emerald-800 font-semibold block">Sisa Bebas</span>
                                            <span class="text-base font-black text-emerald-900"
                                                x-text="activeItem.stok"></span>
                                        </div>
                                        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-2">
                                            <span class="text-[10px] text-indigo-800 font-semibold block">Dipesan</span>
                                            <span class="text-base font-black text-indigo-900"
                                                x-text="activeItem.stokReserved || 0"></span>
                                        </div>
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-2">
                                            <span class="text-[10px] text-blue-800 font-semibold block">Dipinjam</span>
                                            <span class="text-base font-black text-blue-900"
                                                x-text="activeItem.stokDipinjam"></span>
                                        </div>
                                        <div class="bg-rose-50 border border-rose-200 rounded-lg p-2">
                                            <span class="text-[10px] text-rose-800 font-semibold block">Rusak</span>
                                            <span class="text-base font-black text-rose-900"
                                                x-text="activeItem.stokRusak"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Deskripsi -->
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Deskripsi /
                                        Catatan Barang:</h4>
                                    <p class="text-gray-600 leading-relaxed bg-gray-50 p-3 rounded-xl border border-gray-100"
                                        x-text="activeItem.deskripsi"></p>
                                </div>
                            </div>

                            <!-- Footer Modal -->
                            <div
                                class="p-4 sm:p-5 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                                <button @click="detailModalOpen = false"
                                    class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-xl text-xs font-semibold hover:bg-gray-50">
                                    Tutup
                                </button>
                                <template x-if="activeItem.stok > 0">
                                    <button
                                        @click="addToCart({
                                        id: activeItem.id,
                                        bengkelId: activeItem.bengkelId,
                                        kode: activeItem.kode,
                                        nama: activeItem.nama,
                                        tipe: activeItem.tipe,
                                        satuan: activeItem.satuan,
                                        stok: activeItem.stok
                                    }, 1); detailModalOpen = false;"
                                        class="px-5 py-2 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center gap-1.5"
                                        :class="activeItem.tipe === 'inventaris' ? 'bg-primary-600 hover:bg-primary-700' :
                                            'bg-amber-600 hover:bg-amber-700'">
                                        <span>+ Tambah ke Keranjang</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- 6. DRAWER KERANJANG PEMINJAMAN -->
        <div x-show="cartDrawerOpen" class="fixed inset-0 z-50 overflow-hidden" role="dialog" aria-modal="true">
            <div x-show="cartDrawerOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="cartDrawerOpen = false"
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"></div>

            <div class="fixed inset-y-0 right-0 max-w-full flex pl-0 sm:pl-10">
                <div x-show="cartDrawerOpen" x-transition:enter="transform transition ease-in-out duration-300"
                    x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-200"
                    x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                    class="w-screen max-w-md bg-white shadow-2xl flex flex-col justify-between">

                    <!-- Drawer Header -->
                    <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-slate-50/70">
                        <div class="flex items-center space-x-2.5">
                            <div
                                class="w-8 h-8 rounded-lg bg-primary-100 text-primary-700 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-base leading-none">Keranjang Peminjaman</h3>
                                <p class="text-xs text-gray-500 mt-1" x-text="totalCartCount + ' item dipilih'"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button x-show="cart.length > 0" @click="clearCart()" type="button"
                                class="text-xs text-rose-600 hover:text-rose-700 font-semibold px-2 py-1 rounded-lg hover:bg-rose-50 transition-colors">
                                Kosongkan
                            </button>
                            <button @click="cartDrawerOpen = false"
                                class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Drawer Body (Items list) -->
                    <div class="p-5 flex-1 overflow-y-auto space-y-3">
                        <template x-if="cart.length === 0">
                            <div class="py-12 text-center text-gray-400">
                                <div
                                    class="w-16 h-16 rounded-2xl bg-gray-100 text-gray-400 mx-auto flex items-center justify-center mb-3">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-bold text-gray-800">Keranjang masih kosong</h4>
                                <p class="text-xs text-gray-500 mt-1">Pilih alat praktik atau bahan dari katalog di
                                    samping.</p>
                            </div>
                        </template>

                        <template x-for="cItem in cart" :key="cItem.id">
                            <div
                                class="p-3.5 bg-white border border-gray-200 rounded-2xl shadow-2xs flex items-center justify-between gap-3">
                                <div class="flex items-center space-x-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                        :class="cItem.tipe === 'inventaris' ? 'bg-emerald-50 text-emerald-700' :
                                            'bg-amber-50 text-amber-700'">
                                        <template x-if="cItem.tipe === 'inventaris'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </template>
                                        <template x-if="cItem.tipe !== 'inventaris'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[9px] font-bold uppercase px-1.5 py-0.2 rounded"
                                                :class="cItem.tipe === 'inventaris' ? 'bg-emerald-100 text-emerald-800' :
                                                    'bg-amber-100 text-amber-800'"
                                                x-text="cItem.tipe === 'inventaris' ? 'Inventaris' : 'BHP'"></span>
                                            <span class="text-[10px] font-mono text-gray-400" x-text="cItem.kode"></span>
                                        </div>
                                        <p class="text-xs sm:text-sm font-bold text-gray-900 truncate mt-0.5"
                                            x-text="cItem.nama"></p>
                                        <p class="text-[11px] text-gray-500"
                                            x-text="'Stok maks: ' + cItem.stok + ' ' + cItem.satuan"></p>
                                    </div>
                                </div>

                                <!-- Stepper & Delete -->
                                <div class="flex items-center space-x-2 shrink-0">
                                    <div
                                        class="flex items-center border border-gray-200 rounded-lg bg-gray-50 overflow-hidden shadow-2xs">
                                        <button @click="decreaseQty(cItem.id)" type="button"
                                            class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-gray-700 hover:bg-gray-200 font-bold text-sm transition-colors">-</button>
                                        <span class="px-2 py-1 text-xs font-bold text-gray-900 min-w-6 text-center"
                                            x-text="cItem.qty"></span>
                                        <button @click="increaseQty(cItem.id)" :disabled="cItem.qty >= cItem.stok"
                                            type="button"
                                            class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-gray-700 hover:bg-gray-200 font-bold text-sm disabled:opacity-30 disabled:cursor-not-allowed transition-colors">+</button>
                                    </div>
                                    <button @click="removeFromCart(cItem.id)"
                                        class="text-gray-400 hover:text-red-600 p-1 transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Drawer Footer Checkout -->
                    <div class="p-5 border-t border-gray-100 bg-slate-50 space-y-3">
                        <div class="bg-white p-3 rounded-xl border border-gray-200 text-xs space-y-1">
                            <div class="flex justify-between text-gray-600">
                                <span>Alat Inventaris (Wajib Kembali):</span>
                                <span class="font-bold text-emerald-700" x-text="cartInventarisCount + ' item'"></span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Bahan Habis Pakai (Permanen):</span>
                                <span class="font-bold text-amber-700" x-text="cartBahanCount + ' item'"></span>
                            </div>
                        </div>

                        <button @click="openCheckoutModal(); cartDrawerOpen = false;" :disabled="cart.length === 0"
                            class="w-full py-3 bg-primary-600 hover:bg-primary-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
                            <span>Lanjut Buat Pengajuan Peminjaman</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. MODAL CHECKOUT / PENGAJUAN NYATA -->
        <div x-show="checkoutModalOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div x-show="checkoutModalOpen" x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" @click="checkoutModalOpen = false"
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"></div>

            <div class="flex min-h-screen items-center justify-center p-3 sm:p-4 text-center">
                <div x-show="checkoutModalOpen" x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100"
                    x-transition:leave="ease-in duration-150" x-transition:leave-start="scale-100 opacity-100"
                    x-transition:leave-end="scale-95 opacity-0"
                    class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-lg border border-gray-100">

                    <form method="POST" action="{{ route('peminjam.pengajuan.store') }}" @submit="isSubmitting = true">
                        @csrf
                        <input type="hidden" name="bengkel_id" :value="cart[0]?.bengkelId || '{{ $bengkel?->id }}'">
                        <input type="hidden" name="items"
                            :value="JSON.stringify(cart.map(c => ({ id: c.id, qty: c.qty })))">

                        <!-- Header -->
                        <div
                            class="bg-gradient-to-r from-slate-900 to-slate-800 text-white p-5 sm:p-6 flex items-center justify-between">
                            <div>
                                <span
                                    class="text-[10px] font-bold tracking-wider uppercase px-2 py-0.5 rounded bg-white/20 text-emerald-300">
                                    SIBENKA SKAGATA
                                </span>
                                <h3 class="text-base sm:text-lg font-bold mt-1">Form Pengajuan Peminjaman</h3>
                                <p class="text-xs text-slate-300 mt-0.5">Pemohon:
                                    <strong>{{ auth()->user()->name }}</strong> &bull;
                                    {{ $isGuru ? 'Guru' : auth()->user()->nomor_identitas ?? 'Siswa' }}
                                </p>
                            </div>
                            <button @click="checkoutModalOpen = false" type="button"
                                class="text-slate-400 hover:text-white p-1 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="p-5 sm:p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                            <!-- Daftar Ringkasan Barang -->
                            <div>
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Barang yang
                                    Diajukan:</h4>
                                <div
                                    class="bg-slate-50 rounded-xl p-3 border border-gray-200 divide-y divide-gray-100 text-xs space-y-2">
                                    <template x-for="item in cart" :key="item.id">
                                        <div class="flex items-center justify-between pt-1.5 first:pt-0">
                                            <div class="flex items-center space-x-2.5">
                                                <div class="w-6 h-6 rounded-md flex items-center justify-center shrink-0"
                                                    :class="item.tipe === 'inventaris' ? 'bg-emerald-100 text-emerald-700' :
                                                        'bg-amber-100 text-amber-700'">
                                                    <template x-if="item.tipe === 'inventaris'">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                    </template>
                                                    <template x-if="item.tipe !== 'inventaris'">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                        </svg>
                                                    </template>
                                                </div>
                                                <span class="font-bold text-gray-900" x-text="item.nama"></span>
                                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded"
                                                    :class="item.tipe === 'inventaris' ? 'bg-emerald-100 text-emerald-800' :
                                                        'bg-amber-100 text-amber-800'"
                                                    x-text="item.tipe === 'inventaris' ? 'Alat' : 'BHP'"></span>
                                            </div>
                                            <span class="font-bold text-gray-800"
                                                x-text="item.qty + ' ' + item.satuan"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Bagian Pengaturan Jadwal Peminjaman -->
                            <div class="bg-amber-50/80 border border-amber-200 rounded-2xl p-4 space-y-3 text-xs">
                                <div class="flex items-center gap-2 text-amber-950 font-bold">
                                    <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>Jadwal Pengambilan & Batas Pengembalian</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700">
                                            Rencana Waktu Pengambilan: <span class="text-red-500">*</span>
                                        </label>
                                        <input type="datetime-local" name="tanggal_pinjam"
                                            x-model="tanggalPinjam"
                                            @change="updateMinBatasKembali()"
                                            min="{{ now()->format('Y-m-d\TH:i') }}"
                                            max="{{ now()->addDays(14)->format('Y-m-d\T23:59') }}"
                                            required
                                            class="mt-1 block w-full text-xs bg-white border-amber-300 text-amber-950 font-semibold rounded-lg shadow-2xs focus:ring-primary-500 focus:border-primary-500">
                                        <p class="text-[10px] text-amber-800 mt-1">Dapat dijadwalkan hingga 14 hari ke depan.</p>
                                    </div>
                                    <template x-if="cartInventarisCount > 0">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700">
                                                Batas Pengembalian (Alat): <span class="text-red-500">*</span>
                                            </label>
                                            <input type="datetime-local" name="batas_kembali"
                                                x-model="batasKembali"
                                                :min="tanggalPinjam"
                                                required
                                                class="mt-1 block w-full text-xs bg-white border-amber-300 text-amber-950 font-bold rounded-lg shadow-2xs focus:ring-primary-500 focus:border-primary-500">
                                            <p class="text-[10px] text-amber-800 mt-1">Wajib dikembalikan sebelum batas waktu.</p>
                                        </div>
                                    </template>
                                    <template x-if="cartInventarisCount === 0">
                                        <div class="flex items-center text-[11px] text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-lg p-2.5">
                                            <span>Barang Bahan Habis Pakai (BHP) tidak perlu dikembalikan.</span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Tujuan Penggunaan / Mata Pelajaran -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Keperluan / Praktik: <span
                                        class="text-red-500">*</span></label>
                                <textarea name="keperluan" rows="3" required minlength="5" maxlength="1000"
                                    placeholder="Jelaskan keperluan peminjaman barang, contoh: Praktik Jaringan Dasar modul konfigurasi routing bersama Pak Yono di Lab 2..."
                                    class="w-full text-xs border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 placeholder:text-gray-400"></textarea>
                                <p class="text-[11px] text-gray-400 mt-1">Minimal 5 karakter. Jelaskan mata pelajaran atau
                                    keperluan penggunaan.</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="p-4 sm:p-5 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="checkoutModalOpen = false" :disabled="isSubmitting"
                                class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-xl text-xs font-semibold hover:bg-gray-50 transition-colors disabled:opacity-50">
                                Batal
                            </button>
                            <button type="submit" :disabled="isSubmitting || cart.length === 0"
                                class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                                <span x-show="!isSubmitting">Kirim Pengajuan</span>
                                <span x-show="isSubmitting" class="flex items-center gap-1.5">
                                    <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span>Mengirim...</span>
                                </span>
                                <svg x-show="!isSubmitting" class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>

    <script>
        function katalogApp() {
            return {
                cart: [],
                detailModalOpen: false,
                activeItem: null,
                cartDrawerOpen: false,
                checkoutModalOpen: false,
                isSubmitting: false,
                @php
                    $defaultBatasKembali =
                        now()->hour >= 15
                            ? now()->addDay()->setTime(16, 0)->format('Y-m-d\TH:i')
                            : now()->setTime(16, 0)->format('Y-m-d\TH:i');
                @endphp
                tanggalPinjam: '{{ now()->format('Y-m-d\TH:i') }}',
                batasKembali: '{{ $defaultBatasKembali }}',
                updateMinBatasKembali() {
                    if (this.batasKembali <= this.tanggalPinjam) {
                        const d = new Date(this.tanggalPinjam);
                        d.setHours(d.getHours() + 4);
                        const pad = (n) => String(n).padStart(2, '0');
                        this.batasKembali = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
                    }
                },
                toast: {
                    show: false,
                    title: '',
                    message: '',
                    type: 'success',
                    timer: null
                },

                init() {
                    this.loadCart();
                    window.addEventListener('toggle-cart', () => {
                        this.cartDrawerOpen = !this.cartDrawerOpen;
                    });
                    if (new URLSearchParams(window.location.search).get('open_cart') === '1') {
                        this.cartDrawerOpen = true;
                    }
                },

                clearCart() {
                    if (this.cart.length === 0) return;
                    window.openConfirmModal({
                        title: 'Kosongkan Keranjang',
                        message: 'Apakah Anda yakin ingin mengosongkan semua barang dari keranjang peminjaman?',
                        subMessage: 'Seluruh alat dan bahan yang telah dipilih akan dihapus dari daftar keranjang Anda.',
                        type: 'danger',
                        confirmText: 'Ya, Kosongkan',
                        cancelText: 'Batal',
                        onConfirm: () => {
                            this.cart = [];
                            this.saveCart();
                            this.showToast('Keranjang Kosong', 'Semua barang dikeluarkan dari keranjang.', 'info');
                        }
                    });
                },

                loadCart() {
                    try {
                        this.cart = JSON.parse(localStorage.getItem('sibenka_cart') || '[]');
                    } catch (e) {
                        this.cart = [];
                    }
                    window.dispatchEvent(new CustomEvent('cart-updated'));
                },

                saveCart() {
                    localStorage.setItem('sibenka_cart', JSON.stringify(this.cart));
                    window.dispatchEvent(new CustomEvent('cart-updated'));
                },

                get totalCartCount() {
                    return this.cart.reduce((sum, item) => sum + item.qty, 0);
                },

                get cartInventarisCount() {
                    return this.cart.filter(i => i.tipe === 'inventaris').reduce((sum, item) => sum + item.qty, 0);
                },

                get cartBahanCount() {
                    return this.cart.filter(i => i.tipe === 'bhp' || i.tipe === 'bahan').reduce((sum, item) => sum +
                        item.qty, 0);
                },

                isInCart(itemId) {
                    return this.cart.some(c => c.id === itemId);
                },

                getCartQty(itemId) {
                    const found = this.cart.find(c => c.id === itemId);
                    return found ? found.qty : 0;
                },

                openItemDetail(item) {
                    this.activeItem = item;
                    this.detailModalOpen = true;
                },

                addToCart(item, qty = 1) {
                    if (item.stok <= 0) return;

                    // Pastikan item tidak bercampur dari bengkel berbeda jika ada
                    if (this.cart.length > 0 && this.cart[0].bengkelId && this.cart[0].bengkelId !== item.bengkelId) {
                        this.showToast('Bengkel Berbeda',
                            'Semua barang dalam 1 pengajuan wajib berasal dari bengkel yang sama. Kosongkan keranjang jika ingin memilih barang dari bengkel ini.',
                            'warning');
                        return;
                    }

                    const existing = this.cart.find(c => c.id === item.id);
                    if (existing) {
                        existing.qty = Math.min(item.stok, existing.qty + qty);
                    } else {
                        this.cart.push({
                            id: item.id,
                            bengkelId: item.bengkelId,
                            kode: item.kode,
                            nama: item.nama,
                            tipe: item.tipe,
                            satuan: item.satuan,
                            stok: item.stok,
                            qty: Math.min(item.stok, qty)
                        });
                    }
                    this.saveCart();
                    this.showToast('Ditambahkan', `${qty} ${item.satuan} ${item.nama} masuk ke keranjang.`, 'success');
                },

                increaseQty(itemId) {
                    const item = this.cart.find(c => c.id === itemId);
                    if (item && item.qty < item.stok) {
                        item.qty++;
                        this.saveCart();
                    }
                },

                decreaseQty(itemId) {
                    const itemIdx = this.cart.findIndex(c => c.id === itemId);
                    if (itemIdx !== -1) {
                        if (this.cart[itemIdx].qty > 1) {
                            this.cart[itemIdx].qty--;
                        } else {
                            this.cart.splice(itemIdx, 1);
                        }
                        this.saveCart();
                    }
                },

                removeFromCart(itemId) {
                    this.cart = this.cart.filter(c => c.id !== itemId);
                    this.saveCart();
                    this.showToast('Item Dihapus', 'Barang dikeluarkan dari keranjang.', 'info');
                },

                openCheckoutModal() {
                    if (this.cart.length === 0) {
                        this.showToast('Keranjang Kosong', 'Pilih barang terlebih dahulu.', 'warning');
                        return;
                    }
                    this.checkoutModalOpen = true;
                },

                prepareFormSubmit() {
                    this.isSubmitting = true;
                },

                showToast(title, message, type = 'success') {
                    if (this.toast.timer) clearTimeout(this.toast.timer);
                    this.toast.title = title;
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.show = true;

                    this.toast.timer = setTimeout(() => {
                        this.toast.show = false;
                    }, 3500);
                }
            };
        }
    </script>
@endsection
