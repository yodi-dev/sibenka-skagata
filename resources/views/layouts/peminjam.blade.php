<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Katalog Peminjam') - SIBENKA SMKN 3 Yogyakarta</title>

    <!-- Favicon / Logo Tab Browser -->
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('logo.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">

    <!-- Load Tailwind & JS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="bg-slate-50 text-gray-800 font-sans antialiased min-h-screen flex flex-col justify-between selection:bg-primary-500 selection:text-white"
    x-data="{
        cartCount: 0,
        init() {
            const updateCount = () => {
                try {
                    const cart = JSON.parse(localStorage.getItem('sibenka_cart') || '[]');
                    this.cartCount = cart.reduce((acc, item) => acc + (item.qty || 1), 0);
                } catch (e) {
                    this.cartCount = 0;
                }
            };
            updateCount();
            window.addEventListener('cart-updated', updateCount);
            window.addEventListener('storage', updateCount);
        }
    }">

    <!-- TOP NAVBAR (Clean & Responsive) -->
    <header class="bg-white/95 backdrop-blur-md border-b border-gray-200 sticky top-0 z-30 shadow-xs">
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 md:h-20 flex items-center justify-between gap-3 sm:gap-4">

            <!-- Brand / Logo -->
            <a href="{{ route('peminjam.dashboard') }}" class="flex items-center space-x-2.5 sm:space-x-3.5 group">
                <img src="{{ asset('logo.png') }}" alt="Logo SMKN 3 Yogyakarta"
                    class="w-9 h-9 sm:w-12 sm:h-12 md:w-16 md:h-16 object-contain shrink-0 group-hover:scale-105 transition-transform drop-shadow-sm">
                <div>
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <span class="text-base sm:text-xl font-black text-gray-900 tracking-tight">SIBENKA</span>
                        <span
                            class="text-[10px] sm:text-xs font-extrabold px-1.5 sm:px-2 py-0.5 rounded-md bg-primary-50 text-primary-700 border border-primary-200 shadow-2xs">SKAGATA</span>
                    </div>
                    <p class="text-xs text-gray-500 font-medium leading-tight hidden sm:block">Sistem Inventaris &
                        Sirkulasi Bengkel</p>
                </div>
            </a>

            <!-- Navigation Links (Desktop) -->
            <nav class="hidden md:flex items-center space-x-1">
                <a href="{{ route('peminjam.dashboard') }}"
                    class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5 {{ request()->is('peminjam/dashboard*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('peminjam.katalog.index') }}"
                    class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5 {{ request()->is('peminjam/katalog*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                        </path>
                    </svg>
                    Katalog Barang
                </a>
                <a href="{{ route('peminjam.tiket.index') }}"
                    class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5 {{ request()->is('peminjam/tiket*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z">
                        </path>
                    </svg>
                    <span>Tiket Peminjaman</span>
                    @if (auth()->check() &&
                            auth()->user()->peminjamans()->whereIn('status', ['active', 'terlambat'])->exists())
                        <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse ml-0.5"></span>
                    @endif
                </a>
            </nav>

            <!-- User Profile & Action Buttons -->
            <div class="flex items-center space-x-2 sm:space-x-3">

                <!-- Quick Cart Button (Desktop & Mobile) -->
                @if (request()->routeIs('peminjam.katalog.index'))
                    <button type="button" @click="window.dispatchEvent(new CustomEvent('toggle-cart'))"
                        class="relative p-2 text-gray-700 hover:text-primary-600 hover:bg-primary-50 rounded-xl transition-colors flex items-center justify-center"
                        title="Keranjang Peminjaman">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <!-- Cart Badge Counter -->
                        <span x-show="cartCount > 0" x-transition
                            class="absolute -top-1 -right-1 bg-primary-600 text-white font-bold text-[10px] min-w-4.5 h-4.5 px-1 rounded-full flex items-center justify-center shadow-xs"
                            x-text="cartCount"></span>
                    </button>
                @else
                    <a href="{{ route('peminjam.katalog.index', ['open_cart' => 1]) }}"
                        class="relative p-2 text-gray-700 hover:text-primary-600 hover:bg-primary-50 rounded-xl transition-colors flex items-center justify-center"
                        title="Keranjang Peminjaman">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <!-- Cart Badge Counter -->
                        <span x-show="cartCount > 0" x-transition
                            class="absolute -top-1 -right-1 bg-primary-600 text-white font-bold text-[10px] min-w-4.5 h-4.5 px-1 rounded-full flex items-center justify-center shadow-xs"
                            x-text="cartCount"></span>
                    </a>
                @endif

                <!-- Desktop User Section (Profile & Logout) -->
                <div class="hidden md:flex items-center space-x-3">
                    <div class="h-6 w-px bg-gray-200"></div>

                    <!-- User Profile Info -->
                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center space-x-2 p-1 rounded-xl hover:bg-gray-100 transition-colors">
                        <div class="text-right">
                            <p class="text-xs font-bold text-gray-900 leading-tight">
                                {{ auth()->user()->name ?? 'Pengguna' }}</p>
                            <p class="text-[11px] text-gray-500 font-medium">
                                @if (auth()->check() && auth()->user()->isGuru())
                                    Guru &bull; Tenaga Pendidik
                                @else
                                    Siswa &bull; {{ auth()->user()->bengkel->kode ?? 'SMKN 3' }}
                                @endif
                            </p>
                        </div>
                        <div
                            class="w-9 h-9 rounded-full bg-primary-100 text-primary-700 font-bold flex items-center justify-center text-xs border border-primary-200 shadow-2xs">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                    </a>

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors"
                            title="Keluar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="@yield('main_class', 'flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-7 pb-24 md:pb-8')">
        @yield('content')
    </main>

    @if (!View::hasSection('hide_footer'))
        <!-- FOOTER (Desktop View) -->
        <footer class="bg-white border-t border-gray-200 py-5 text-center text-xs text-gray-500 hidden md:block">
            <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
                <p>&copy; 2026 SMK Negeri 3 Yogyakarta. Sistem Inventaris & Sirkulasi Bengkel (Sibenka).</p>
                <p class="text-gray-400 text-[11px]">
                    Bengkel:
                    <strong class="text-gray-700">
                        @if (auth()->check() && auth()->user()->isGuru())
                            Akses Guru (Multi-Bengkel)
                        @else
                            {{ auth()->user()->bengkel->nama ?? 'SMKN 3 Yogyakarta' }}
                        @endif
                    </strong>
                </p>
            </div>
        </footer>
    @endif

    <!-- MOBILE BOTTOM NAVIGATION (Fixed Bottom Bar - App Feel) -->
    <nav
        class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-gray-200 px-3 py-1.5 shadow-lg flex items-center justify-around">
        <!-- Beranda -->
        <a href="{{ route('peminjam.dashboard') }}"
            class="flex-1 flex flex-col items-center justify-center py-1 rounded-xl transition-colors {{ request()->is('peminjam/dashboard*') ? 'text-primary-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                </path>
            </svg>
            <span class="text-[11px] mt-0.5 font-medium">Beranda</span>
        </a>

        <!-- Katalog -->
        <a href="{{ route('peminjam.katalog.index') }}"
            class="flex-1 flex flex-col items-center justify-center py-1 rounded-xl transition-colors {{ request()->is('peminjam/katalog*') ? 'text-primary-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                </path>
            </svg>
            <span class="text-[11px] mt-0.5 font-medium">Katalog</span>
        </a>

        <!-- Tiket Saya -->
        <a href="{{ route('peminjam.tiket.index') }}"
            class="flex-1 flex flex-col items-center justify-center py-1 rounded-xl transition-colors {{ request()->is('peminjam/tiket*') ? 'text-primary-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            <div class="relative">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z">
                    </path>
                </svg>
                @if (auth()->check() &&
                        auth()->user()->peminjamans()->whereIn('status', ['active', 'terlambat'])->exists())
                    <span class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                @endif
            </div>
            <span class="text-[11px] mt-0.5 font-medium">Tiket Saya</span>
        </a>

        <!-- Profil -->
        <a href="{{ route('profile.edit') }}"
            class="flex-1 flex flex-col items-center justify-center py-1 rounded-xl transition-colors {{ request()->is('profile*') || request()->is('peminjam/profile*') ? 'text-primary-600 font-bold' : 'text-gray-500 hover:text-gray-800' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span class="text-[11px] mt-0.5 font-medium">Profil</span>
        </a>
    </nav>

    @include('components.confirm-modal')

    @stack('scripts')
</body>

</html>
