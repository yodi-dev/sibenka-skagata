<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Superadmin\DashboardController as SuperadminDashboardController;
use App\Http\Controllers\Superadmin\PengadaanController as SuperadminPengadaanController;
use App\Http\Controllers\Toolman\DashboardController as ToolmanDashboardController;
use App\Http\Controllers\Toolman\BarangController as ToolmanBarangController;
use App\Http\Controllers\Toolman\LokasiController as ToolmanLokasiController;
use App\Http\Controllers\Toolman\SatuanController as ToolmanSatuanController;
use App\Http\Controllers\Toolman\SumberDanaController as ToolmanSumberDanaController;
use App\Http\Controllers\Toolman\PeminjamanController as ToolmanPeminjamanController;
use App\Http\Controllers\Toolman\PengembalianController as ToolmanPengembalianController;
use App\Http\Controllers\Toolman\PengadaanController as ToolmanPengadaanController;
use App\Http\Controllers\Toolman\PeminjamController as ToolmanPeminjamController;
use App\Http\Controllers\Toolman\MutasiController as ToolmanMutasiController;
use App\Http\Controllers\Peminjam\DashboardController as PeminjamDashboardController;
use App\Http\Controllers\Peminjam\KatalogController as PeminjamKatalogController;
use App\Http\Controllers\Peminjam\TiketController as PeminjamTiketController;
use App\Http\Controllers\Peminjam\PengajuanController as PeminjamPengajuanController;


// Halaman awal langsung arahkan ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// Redirect dashboard sesuai role pengguna
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user?->role === 'waka') {
        return redirect()->route('superadmin.dashboard');
    }
    if ($user?->role === 'toolman') {
        return redirect()->route('toolman.dashboard');
    }
    return redirect()->route('peminjam.dashboard');
})->middleware(['auth'])->name('dashboard');


// ==========================================
// 1. ROUTE AUTHENTICATION (LARAVEL BREEZE)
// ==========================================
require __DIR__ . '/auth.php';


// ==========================================
// 2. ROUTE SUPER ADMIN (WAKA SARPRAS)
// ==========================================
Route::prefix('superadmin')->name('superadmin.')->middleware(['auth', 'role:waka'])->group(function () {
    Route::get('/dashboard', [SuperadminDashboardController::class, 'index'])->name('dashboard');


    // Pengadaan (RAB)
    Route::get('/pengadaan', [SuperadminPengadaanController::class, 'index'])->name('pengadaan.index');
    Route::get('/pengadaan/{id}', [SuperadminPengadaanController::class, 'show'])->name('pengadaan.show');
    Route::get('/pengadaan/{id}/print', [SuperadminPengadaanController::class, 'print'])->name('pengadaan.print');
    Route::post('/pengadaan/{id}/review', [SuperadminPengadaanController::class, 'review'])->name('pengadaan.review');


    // Laporan
    Route::get('/laporan/mutasi', [\App\Http\Controllers\Superadmin\LaporanController::class, 'mutasi'])->name('laporan.mutasi');
    Route::get('/laporan/mutasi/excel', [\App\Http\Controllers\Superadmin\LaporanController::class, 'exportMutasiExcel'])->name('laporan.mutasi.excel');
    Route::get('/laporan/mutasi/pdf', [\App\Http\Controllers\Superadmin\LaporanController::class, 'exportMutasiPdf'])->name('laporan.mutasi.pdf');
    Route::get('/laporan/konsumsi', [\App\Http\Controllers\Superadmin\LaporanController::class, 'konsumsi'])->name('laporan.konsumsi');
    Route::get('/laporan/konsumsi/excel', [\App\Http\Controllers\Superadmin\LaporanController::class, 'exportExcel'])->name('laporan.konsumsi.excel');
    Route::get('/laporan/konsumsi/pdf', [\App\Http\Controllers\Superadmin\LaporanController::class, 'exportPdf'])->name('laporan.konsumsi.pdf');

    // Master Data Bengkel
    Route::get('/bengkel', [\App\Http\Controllers\Superadmin\BengkelController::class, 'index'])->name('bengkel.index');
    Route::get('/bengkel/create', [\App\Http\Controllers\Superadmin\BengkelController::class, 'create'])->name('bengkel.create');
    Route::post('/bengkel', [\App\Http\Controllers\Superadmin\BengkelController::class, 'store'])->name('bengkel.store');
    Route::get('/bengkel/edit/{id}', [\App\Http\Controllers\Superadmin\BengkelController::class, 'edit'])->name('bengkel.edit');
    Route::put('/bengkel/{id}', [\App\Http\Controllers\Superadmin\BengkelController::class, 'update'])->name('bengkel.update');
    Route::delete('/bengkel/{id}', [\App\Http\Controllers\Superadmin\BengkelController::class, 'destroy'])->name('bengkel.destroy');

    // Master Data Toolman
    Route::get('/toolman', [\App\Http\Controllers\Superadmin\ToolmanController::class, 'index'])->name('toolman.index');
    Route::get('/toolman/create', [\App\Http\Controllers\Superadmin\ToolmanController::class, 'create'])->name('toolman.create');
    Route::post('/toolman', [\App\Http\Controllers\Superadmin\ToolmanController::class, 'store'])->name('toolman.store');
    Route::get('/toolman/edit/{id}', [\App\Http\Controllers\Superadmin\ToolmanController::class, 'edit'])->name('toolman.edit');
    Route::put('/toolman/{id}', [\App\Http\Controllers\Superadmin\ToolmanController::class, 'update'])->name('toolman.update');
    Route::delete('/toolman/{id}', [\App\Http\Controllers\Superadmin\ToolmanController::class, 'destroy'])->name('toolman.destroy');
    Route::post('/toolman/{id}/reset-password', [\App\Http\Controllers\Superadmin\ToolmanController::class, 'resetPassword'])->name('toolman.reset-password');

    // Profil Waka Sarpras
    Route::get('/profile', function () {
        return redirect()->route('profile.edit');
    })->name('profile');
});


// ==========================================
// 3. ROUTE ADMIN BENGKEL (TOOLMAN)
// ==========================================
Route::prefix('toolman')->name('toolman.')->middleware(['auth', 'role:toolman'])->group(function () {
    Route::get('/dashboard', [ToolmanDashboardController::class, 'index'])->name('dashboard');

    // Manajemen Barang
    Route::get('/barang', [ToolmanBarangController::class, 'index'])->name('barang.index');
    Route::get('/barang/create', [ToolmanBarangController::class, 'create'])->name('barang.create');
    Route::post('/barang', [ToolmanBarangController::class, 'store'])->name('barang.store');
    Route::get('/barang/template-excel', [ToolmanBarangController::class, 'downloadTemplate'])->name('barang.template-excel');
    Route::post('/barang/import', [ToolmanBarangController::class, 'import'])->name('barang.import');
    Route::get('/barang/edit/{id}', [ToolmanBarangController::class, 'edit'])->name('barang.edit');
    Route::put('/barang/{id}', [ToolmanBarangController::class, 'update'])->name('barang.update');
    Route::delete('/barang/{id}', [ToolmanBarangController::class, 'destroy'])->name('barang.destroy');
    Route::get('/barang/{id}/print-kartu', [ToolmanBarangController::class, 'printKartu'])->name('barang.print-kartu');

    // Manajemen Lokasi Penyimpanan
    Route::get('/lokasi', [ToolmanLokasiController::class, 'index'])->name('lokasi.index');
    Route::post('/lokasi', [ToolmanLokasiController::class, 'store'])->name('lokasi.store');
    Route::put('/lokasi/{id}', [ToolmanLokasiController::class, 'update'])->name('lokasi.update');
    Route::delete('/lokasi/{id}', [ToolmanLokasiController::class, 'destroy'])->name('lokasi.destroy');

    // Manajemen Satuan Barang
    Route::get('/satuan', [ToolmanSatuanController::class, 'index'])->name('satuan.index');
    Route::post('/satuan', [ToolmanSatuanController::class, 'store'])->name('satuan.store');
    Route::put('/satuan/{id}', [ToolmanSatuanController::class, 'update'])->name('satuan.update');
    Route::delete('/satuan/{id}', [ToolmanSatuanController::class, 'destroy'])->name('satuan.destroy');

    // Manajemen Sumber Dana
    Route::get('/sumber-dana', [ToolmanSumberDanaController::class, 'index'])->name('sumber-dana.index');
    Route::post('/sumber-dana', [ToolmanSumberDanaController::class, 'store'])->name('sumber-dana.store');
    Route::put('/sumber-dana/{id}', [ToolmanSumberDanaController::class, 'update'])->name('sumber-dana.update');
    Route::delete('/sumber-dana/{id}', [ToolmanSumberDanaController::class, 'destroy'])->name('sumber-dana.destroy');

    // Sirkulasi Peminjaman
    Route::get('/peminjaman', [ToolmanPeminjamanController::class, 'index'])->name('peminjaman.index');
    Route::get('/peminjaman/{id}', [ToolmanPeminjamanController::class, 'show'])->name('peminjaman.show');
    Route::post('/peminjaman/{id}/setujui-jadwal', [ToolmanPeminjamanController::class, 'setujuiJadwal'])->name('peminjaman.setujui-jadwal');
    Route::post('/peminjaman/{id}/approve', [ToolmanPeminjamanController::class, 'approve'])->name('peminjaman.approve');
    Route::post('/peminjaman/{id}/reject', [ToolmanPeminjamanController::class, 'reject'])->name('peminjaman.reject');
    Route::get('/peminjaman/{id}/print-pinjam', [ToolmanPengembalianController::class, 'printPinjam'])->name('peminjaman.print-pinjam');
    Route::get('/peminjaman/{id}/print-kembali', [ToolmanPengembalianController::class, 'printKembali'])->name('peminjaman.print-kembali');

    // Sirkulasi Pengembalian
    Route::get('/pengembalian', [ToolmanPengembalianController::class, 'index'])->name('pengembalian.index');
    Route::get('/pengembalian/{id}/check', [ToolmanPengembalianController::class, 'check'])->name('pengembalian.check');
    Route::post('/pengembalian/{id}/check', [ToolmanPengembalianController::class, 'processCheck'])->name('pengembalian.process-check');
    Route::get('/pengembalian/{id}/print-pinjam', [ToolmanPengembalianController::class, 'printPinjam'])->name('pengembalian.print-pinjam');
    Route::get('/pengembalian/{id}/print-kembali', [ToolmanPengembalianController::class, 'printKembali'])->name('pengembalian.print-kembali');

    // Pengadaan (RAB)
    Route::get('/pengadaan', [ToolmanPengadaanController::class, 'index'])->name('pengadaan.index');
    Route::get('/pengadaan/create', [ToolmanPengadaanController::class, 'create'])->name('pengadaan.create');
    Route::post('/pengadaan', [ToolmanPengadaanController::class, 'store'])->name('pengadaan.store');
    Route::get('/pengadaan/{id}', [ToolmanPengadaanController::class, 'show'])->name('pengadaan.show');
    Route::get('/pengadaan/{id}/edit', [ToolmanPengadaanController::class, 'edit'])->name('pengadaan.edit');
    Route::put('/pengadaan/{id}', [ToolmanPengadaanController::class, 'update'])->name('pengadaan.update');
    Route::post('/pengadaan/{id}/submit', [ToolmanPengadaanController::class, 'submit'])->name('pengadaan.submit');
    Route::delete('/pengadaan/{id}', [ToolmanPengadaanController::class, 'destroy'])->name('pengadaan.destroy');
    Route::post('/pengadaan/{id}/receive', [ToolmanPengadaanController::class, 'receive'])->name('pengadaan.receive');

    // Manajemen Peminjam
    Route::get('/peminjam', [ToolmanPeminjamController::class, 'index'])->name('peminjam.index');
    Route::get('/peminjam/{id}', [ToolmanPeminjamController::class, 'show'])->name('peminjam.show');
    Route::post('/peminjam/{id}/approve', [ToolmanPeminjamController::class, 'approveUser'])->name('peminjam.approve');
    Route::post('/peminjam/{id}/reject', [ToolmanPeminjamController::class, 'rejectUser'])->name('peminjam.reject');
    Route::post('/peminjam/{id}/suspend', [ToolmanPeminjamController::class, 'suspendUser'])->name('peminjam.suspend');
    Route::post('/peminjam/{id}/activate', [ToolmanPeminjamController::class, 'activateUser'])->name('peminjam.activate');

    // Riwayat Stok / Mutasi
    Route::get('/mutasi', [ToolmanMutasiController::class, 'index'])->name('mutasi.index');
    Route::get('/mutasi/export', [ToolmanMutasiController::class, 'export'])->name('mutasi.export');
    Route::get('/mutasi/export-excel', [ToolmanMutasiController::class, 'exportExcel'])->name('mutasi.export-excel');
    Route::get('/mutasi/print', [ToolmanMutasiController::class, 'print'])->name('mutasi.print');

    // Profil Toolman
    Route::get('/profile', function () {
        return redirect()->route('profile.edit');
    })->name('profile');
});


// ==========================================
// 4. ROUTE PEMINJAM (GURU & SISWA)
// ==========================================
Route::prefix('peminjam')->name('peminjam.')->middleware(['auth', 'role:peminjam'])->group(function () {
    Route::get('/dashboard', [PeminjamDashboardController::class, 'index'])->name('dashboard');
    Route::get('/katalog', [PeminjamKatalogController::class, 'index'])->name('katalog.index');

    Route::get('/pengajuan/create', [PeminjamPengajuanController::class, 'create'])->name('pengajuan.create');
    Route::post('/pengajuan', [PeminjamPengajuanController::class, 'store'])->name('pengajuan.store');

    Route::get('/tiket', [PeminjamTiketController::class, 'index'])->name('tiket.index');
    Route::get('/tiket/{id}', [PeminjamTiketController::class, 'show'])->name('tiket.show');
    Route::post('/tiket/{id}/kembalikan', [PeminjamTiketController::class, 'ajukanPengembalian'])->name('tiket.kembalikan');
    Route::get('/tiket/{id}/print-pinjam', [PeminjamTiketController::class, 'printPinjam'])->name('tiket.print-pinjam');
    Route::get('/tiket/{id}/print-kembali', [PeminjamTiketController::class, 'printKembali'])->name('tiket.print-kembali');

    Route::get('/profile', function () {
        return redirect()->route('profile.edit');
    })->name('profile');
});

// Route untuk halaman edit profil dengan deteksi role dinamis
Route::middleware('auth')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});
