<?php

namespace App\Http\Controllers\Peminjam;

use App\Http\Controllers\Controller;
use App\Models\Bengkel;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Bengkel peminjam
        $bengkel = $user->bengkel ?? Bengkel::first();

        // 1. Auto-update status pinjaman aktif peminjam yang sudah melewati tenggat waktu kembali
        Peminjaman::where('user_id', $user->id)
            ->whereIn('status', ['active', 'aktif'])
            ->whereNotNull('batas_kembali')
            ->where('batas_kembali', '<', Carbon::now())
            ->update(['status' => 'terlambat']);

        // 2. Statistik Ringkas Tiket Peminjam
        $countActive = Peminjaman::where('user_id', $user->id)
            ->whereIn('status', ['active', 'terlambat'])
            ->count();

        $countPending = Peminjaman::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'menunggu_acc'])
            ->count();

        $countDisetujui = Peminjaman::where('user_id', $user->id)
            ->whereIn('status', ['disetujui', 'disetujui_jadwal'])
            ->count();

        $countMenungguPengecekan = Peminjaman::where('user_id', $user->id)
            ->where('status', 'menunggu_pengecekan')
            ->count();

        $countSelesai = Peminjaman::where('user_id', $user->id)
            ->where('status', 'selesai')
            ->count();

        $hasOverdue = Peminjaman::where('user_id', $user->id)
            ->where('status', 'terlambat')
            ->exists();

        // 3. Tiket Aktif, Jadwal Disetujui, & Pengembalian Terdekat (Maksimal 5 item)
        $peminjamanAktif = Peminjaman::with(['bengkel', 'detailPeminjamans.barang'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'terlambat', 'menunggu_pengecekan', 'disetujui_jadwal', 'disetujui'])
            ->orderByRaw("CASE WHEN status = 'terlambat' THEN 0 WHEN status = 'active' THEN 1 WHEN status IN ('disetujui', 'disetujui_jadwal') THEN 2 ELSE 3 END")
            ->orderBy('tanggal_pinjam', 'asc')
            ->take(5)
            ->get();

        return view('peminjam.dashboard', compact(
            'user',
            'bengkel',
            'countActive',
            'countPending',
            'countDisetujui',
            'countMenungguPengecekan',
            'countSelesai',
            'hasOverdue',
            'peminjamanAktif'
        ));
    }
}
