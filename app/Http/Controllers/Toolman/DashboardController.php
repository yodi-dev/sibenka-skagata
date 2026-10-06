<?php

namespace App\Http\Controllers\Toolman;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Bengkel;
use App\Models\Peminjaman;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $bengkelId = $user->bengkel_id;

        if (!$bengkelId) {
            abort(403, 'Akun Toolman Anda belum ditugaskan ke unit bengkel manapun. Silakan hubungi Waka Sarpras.');
        }

        $bengkel = $user->bengkel ?? Bengkel::findOrFail($bengkelId);

        // 1. Auto-update: Sinkronisasi status pinjaman 'active' yang melewati batas kembali menjadi 'terlambat'
        Peminjaman::where('bengkel_id', $bengkelId)
            ->whereIn('status', ['active', 'aktif'])
            ->whereNotNull('batas_kembali')
            ->where('batas_kembali', '<', Carbon::now())
            ->update(['status' => 'terlambat']);

        // 2. Metrik Statistik Ringkas Utama
        // Sedang Dipinjam: Total unit barang fisik yang sedang dipinjam
        $sedangDipinjam = (int) Barang::where('bengkel_id', $bengkelId)->sum('stok_dipinjam');

        // Request Baru: Antrean permohonan pinjam yang belum selesai diserahkan (pending / menunggu_acc / disetujui / disetujui_jadwal)
        $requestBaru = Peminjaman::where('bengkel_id', $bengkelId)
            ->whereIn('status', ['pending', 'menunggu_acc', 'disetujui', 'disetujui_jadwal'])
            ->count();

        // Barang Rusak: Total unit barang fisik yang tercatat rusak
        $barangRusak = (int) Barang::where('bengkel_id', $bengkelId)->sum('stok_rusak');

        // Stok Menipis: Jumlah item dengan stok tersedia di bawah atau sama dengan minimum stok
        $stokMenipis = Barang::where('bengkel_id', $bengkelId)
            ->whereColumn('stok_tersedia', '<=', 'minimum_stok')
            ->count();

        // 3. Metrik Tambahan Operasional Bengkel
        // Peminjaman inventaris menunggu pengecekan fisik di meja toolman
        $menungguPengecekan = Peminjaman::where('bengkel_id', $bengkelId)
            ->where('status', 'menunggu_pengecekan')
            ->count();

        // Total tiket peminjaman yang sedang aktif / belum tuntas
        $totalPeminjamanAktif = Peminjaman::where('bengkel_id', $bengkelId)
            ->whereIn('status', ['active', 'terlambat'])
            ->count();

        // Peminjam baru yang menunggu persetujuan akun registrasi
        $pendingUserCount = User::where('role', 'peminjam')
            ->where(function ($q) use ($bengkelId) {
                $q->where('bengkel_id', $bengkelId)
                    ->orWhereNull('bengkel_id');
            })
            ->where('status', 'menunggu_acc')
            ->count();

        // 4. Jadwal Pengembalian Hari Ini & Aktif
        // Memuat pinjaman aktif, terlambat, dan menunggu pengecekan untuk barang inventaris
        $jadwalPengembalian = Peminjaman::with([
            'user',
            'detailPeminjamans' => function ($q) {
                $q->with(['barang' => function ($b) {
                    $b->select('id', 'bengkel_id', 'lokasi_penyimpanan_id', 'kode_barang', 'nama', 'jenis_barang', 'satuan');
                }]);
            }
        ])
            ->where('bengkel_id', $bengkelId)
            ->whereIn('status', ['active', 'terlambat', 'menunggu_pengecekan'])
            ->whereHas('detailPeminjamans.barang', function ($q) {
                $q->where('jenis_barang', 'inventaris');
            })
            ->orderByRaw("CASE WHEN status = 'terlambat' THEN 0 WHEN status = 'menunggu_pengecekan' THEN 1 ELSE 2 END")
            ->orderBy('batas_kembali', 'asc')
            ->take(6)
            ->get();

        // 5. Peringatan Stok Habis Pakai & Limit
        $peringatanStok = Barang::with('lokasiPenyimpanan')
            ->where('bengkel_id', $bengkelId)
            ->whereColumn('stok_tersedia', '<=', 'minimum_stok')
            ->orderBy('stok_tersedia', 'asc')
            ->take(6)
            ->get();

        // 6. Antrean Permohonan Peminjaman Menunggu Persetujuan (Quick Approval)
        $antreanPeminjaman = Peminjaman::with([
            'user',
            'detailPeminjamans' => function ($q) {
                $q->with(['barang' => function ($b) {
                    $b->select('id', 'bengkel_id', 'kode_barang', 'nama', 'jenis_barang', 'satuan', 'stok_tersedia');
                }]);
            }
        ])
            ->where('bengkel_id', $bengkelId)
            ->whereIn('status', ['pending', 'menunggu_acc', 'disetujui', 'disetujui_jadwal'])
            ->latest('tanggal_pinjam')
            ->take(5)
            ->get();

        // 7. Calon Peminjam Baru Menunggu Aktivasi Akun
        $calonPeminjamPending = User::where('role', 'peminjam')
            ->where(function ($q) use ($bengkelId) {
                $q->where('bengkel_id', $bengkelId)
                    ->orWhereNull('bengkel_id');
            })
            ->where('status', 'menunggu_acc')
            ->latest()
            ->take(5)
            ->get();

        // 8. Aktivitas Mutasi / Sirkulasi Terbaru
        $aktivitasTerbaru = StockMovement::with(['barang', 'user'])
            ->whereHas('barang', function ($q) use ($bengkelId) {
                $q->where('bengkel_id', $bengkelId);
            })
            ->latest()
            ->take(5)
            ->get();

        // Respons JSON jika diminta melalui AJAX/API
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'bengkel' => $bengkel,
                'metrics' => [
                    'sedang_dipinjam' => $sedangDipinjam,
                    'request_baru' => $requestBaru,
                    'barang_rusak' => $barangRusak,
                    'stok_menipis' => $stokMenipis,
                    'menunggu_pengecekan' => $menungguPengecekan,
                    'total_peminjaman_aktif' => $totalPeminjamanAktif,
                    'pending_user_count' => $pendingUserCount,
                ],
                'antrean_peminjaman' => $antreanPeminjaman,
                'calon_peminjam_pending' => $calonPeminjamPending,
                'jadwal_pengembalian' => $jadwalPengembalian,
                'peringatan_stok' => $peringatanStok,
                'aktivitas_terbaru' => $aktivitasTerbaru,
            ]);
        }

        return view('toolman.dashboard', compact(
            'bengkel',
            'sedangDipinjam',
            'requestBaru',
            'barangRusak',
            'stokMenipis',
            'menungguPengecekan',
            'totalPeminjamanAktif',
            'pendingUserCount',
            'antreanPeminjaman',
            'calonPeminjamPending',
            'jadwalPengembalian',
            'peringatanStok',
            'aktivitasTerbaru'
        ));
    }
}
