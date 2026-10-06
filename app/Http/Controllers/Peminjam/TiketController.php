<?php

namespace App\Http\Controllers\Peminjam;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TiketController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $filterStatus = $request->input('status', 'all');

        $query = Peminjaman::with(['bengkel', 'detailPeminjamans.barang', 'diprosesOleh'])
            ->where('user_id', $user->id)
            ->latest('tanggal_pinjam');

        if ($filterStatus && $filterStatus !== 'all') {
            if ($filterStatus === 'terlambat') {
                $query->where(function ($q) {
                    $q->where('status', 'terlambat')
                        ->orWhere(function ($sub) {
                            $sub->where('status', 'active')
                                ->whereNotNull('batas_kembali')
                                ->where('batas_kembali', '<', now());
                        });
                });
            } elseif ($filterStatus === 'disetujui') {
                $query->whereIn('status', ['disetujui', 'disetujui_jadwal']);
            } elseif ($filterStatus === 'pending') {
                $query->whereIn('status', ['pending', 'menunggu_acc']);
            } else {
                $query->where('status', $filterStatus);
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $cleanId = ltrim($search, '#');
                if (is_numeric($cleanId)) {
                    $q->where('id', $cleanId);
                }
                $q->orWhere('keperluan', 'like', "%{$search}%")
                    ->orWhereHas('detailPeminjamans.barang', function ($bQ) use ($search) {
                        $bQ->where('nama', 'like', "%{$search}%")
                            ->orWhere('kode_barang', 'like', "%{$search}%");
                    });
            });
        }

        $peminjamans = $query->paginate(10)->withQueryString();

        $counts = [
            'all' => Peminjaman::where('user_id', $user->id)->count(),
            'pending' => Peminjaman::where('user_id', $user->id)->whereIn('status', ['pending', 'menunggu_acc'])->count(),
            'disetujui' => Peminjaman::where('user_id', $user->id)->whereIn('status', ['disetujui', 'disetujui_jadwal'])->count(),
            'active' => Peminjaman::where('user_id', $user->id)->where('status', 'active')->count(),
            'menunggu_pengecekan' => Peminjaman::where('user_id', $user->id)->where('status', 'menunggu_pengecekan')->count(),
            'selesai' => Peminjaman::where('user_id', $user->id)->where('status', 'selesai')->count(),
            'ditolak' => Peminjaman::where('user_id', $user->id)->where('status', 'ditolak')->count(),
        ];

        return view('peminjam.tiket.index', compact('peminjamans', 'filterStatus', 'counts'));
    }

    public function show($id)
    {
        $user = auth()->user();

        $peminjaman = Peminjaman::with([
            'bengkel',
            'detailPeminjamans.barang.lokasiPenyimpanan',
            'diprosesOleh'
        ])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return view('peminjam.tiket.show', compact('peminjaman'));
    }

    public function ajukanPengembalian($id)
    {
        $user = auth()->user();

        try {
            DB::transaction(function () use ($id, $user) {
                $peminjaman = Peminjaman::where('user_id', $user->id)
                    ->where('id', $id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!in_array($peminjaman->status, ['active', 'terlambat'])) {
                    throw new \DomainException('Status tiket tidak memungkinkan untuk pengajuan pengembalian.');
                }

                $peminjaman->update([
                    'status' => 'menunggu_pengecekan',
                ]);
            });

            return back()->with('success', 'Pengajuan pengembalian berhasil! Silakan bawa alat fisik ke meja Toolman bengkel untuk pengecekan kondisi.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error("Gagal mengajukan pengembalian tiket #{$id}: " . $e->getMessage());
            return back()->with('error', 'Gagal memproses pengajuan pengembalian: Terjadi kesalahan sistem.');
        }
    }

    /**
     * Cetak Lembar Bon Pinjam Alat / Bahan Resmi untuk Peminjam
     */
    public function printPinjam($id)
    {
        $user = auth()->user();

        $peminjaman = Peminjaman::with([
            'user',
            'bengkel',
            'detailPeminjamans.barang.lokasiPenyimpanan',
            'diprosesOleh'
        ])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        if (in_array($peminjaman->status, ['pending', 'menunggu_acc', 'ditolak'])) {
            return redirect()->route('peminjam.tiket.show', $id)
                ->with('error', 'Bukti pinjam hanya dapat dicetak setelah pengajuan peminjaman disetujui oleh Toolman.');
        }

        $bengkel = $peminjaman->bengkel;

        return view('toolman.pengembalian.print_pinjam', compact('peminjaman', 'bengkel'));
    }

    /**
     * Cetak Lembar Bukti Pengembalian Alat / Bahan Resmi untuk Peminjam
     */
    public function printKembali($id)
    {
        $user = auth()->user();

        $peminjaman = Peminjaman::with([
            'user',
            'bengkel',
            'detailPeminjamans.barang.lokasiPenyimpanan',
            'diprosesOleh'
        ])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        if ($peminjaman->status !== 'selesai') {
            return redirect()->route('peminjam.tiket.show', $id)
                ->with('error', 'Bukti pengembalian hanya dapat dicetak setelah pengembalian dikonfirmasi selesai oleh Toolman.');
        }

        $bengkel = $peminjaman->bengkel;

        return view('toolman.pengembalian.print_kembali', compact('peminjaman', 'bengkel'));
    }
}
