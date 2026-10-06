<?php

namespace App\Http\Controllers\Toolman;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Bengkel;
use App\Models\Peminjaman;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PeminjamanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $bengkelId = $user->bengkel_id;
        if (!$bengkelId) {
            abort(403, 'Akun Toolman Anda belum ditugaskan ke unit bengkel manapun. Silakan hubungi Waka Sarpras.');
        }
        $bengkel = $user->bengkel ?? Bengkel::findOrFail($bengkelId);

        $tab = $request->input('tab', 'pending'); // 'pending' | 'riwayat'

        $query = Peminjaman::with(['user', 'bengkel', 'detailPeminjamans.barang'])
            ->where('bengkel_id', $bengkelId);

        if ($tab === 'pending') {
            $query->whereIn('status', ['pending', 'menunggu_acc'])->latest('tanggal_pinjam');
        } else {
            $query->whereNotIn('status', ['pending', 'menunggu_acc'])->latest('tanggal_pinjam');
        }

        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nomor_identitas', 'like', "%{$search}%");
            });
        }

        $peminjamans = $query->paginate(10)->withQueryString();

        $pendingCount = Peminjaman::where('bengkel_id', $bengkelId)
            ->whereIn('status', ['pending', 'menunggu_acc'])
            ->count();

        $riwayatCount = Peminjaman::where('bengkel_id', $bengkelId)
            ->whereNotIn('status', ['pending', 'menunggu_acc'])
            ->count();

        return view('toolman.peminjaman.index', compact('peminjamans', 'bengkel', 'tab', 'pendingCount', 'riwayatCount'));
    }

    public function show($id)
    {
        $user = auth()->user();
        $bengkelId = $user->bengkel_id;
        if (!$bengkelId) {
            abort(403, 'Akun Toolman Anda belum ditugaskan ke unit bengkel manapun. Silakan hubungi Waka Sarpras.');
        }
        $bengkel = $user->bengkel ?? Bengkel::findOrFail($bengkelId);

        $peminjaman = Peminjaman::with(['user', 'bengkel', 'detailPeminjamans.barang', 'diprosesOleh'])
            ->where('bengkel_id', $bengkelId)
            ->findOrFail($id);

        return view('toolman.peminjaman.show', compact('peminjaman', 'bengkel'));
    }

    public function approve($id)
    {
        $user = auth()->user();
        $bengkelId = $user->bengkel_id;
        if (!$bengkelId) {
            abort(403, 'Akun Toolman Anda belum ditugaskan ke unit bengkel manapun. Silakan hubungi Waka Sarpras.');
        }

        try {
            $trxCode = DB::transaction(function () use ($id, $bengkelId, $user) {
                // 1. Kunci dan ambil tiket peminjaman terlebih dahulu (Lock Order #1)
                $peminjaman = Peminjaman::with('user')
                    ->where('bengkel_id', $bengkelId)
                    ->where('id', $id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 2. Validasi status di DALAM transaksi terkunci
                if (!in_array($peminjaman->status, ['pending', 'menunggu_acc'])) {
                    throw new \DomainException("Tiket peminjaman #{$peminjaman->id} sudah tidak dalam status menunggu persetujuan (status saat ini: {$peminjaman->status}).");
                }

                $details = $peminjaman->detailPeminjamans;
                if ($details->isEmpty()) {
                    throw new \DomainException("Tiket peminjaman #{$peminjaman->id} tidak memiliki rincian barang.");
                }

                // 3. Kunci seluruh barang yang terlibat dengan Lock Order konsisten (ORDER BY id ASC)
                $barangIds = $details->pluck('barang_id')->unique()->sort()->values()->all();
                $barangs = Barang::whereIn('id', $barangIds)
                    ->lockForUpdate()
                    ->orderBy('id', 'asc')
                    ->get()
                    ->keyBy('id');

                // 4. Validasi ketersediaan stok untuk semua item sebelum mengubah data apa pun
                foreach ($details as $detail) {
                    $barang = $barangs->get($detail->barang_id);
                    if (!$barang) {
                        throw new \DomainException("Data barang #{$detail->barang_id} tidak ditemukan.");
                    }
                    if ($barang->stok_tersedia < $detail->jumlah) {
                        throw new \DomainException("Stok barang '{$barang->nama}' tidak mencukupi saat proses approval! (Tersedia: {$barang->stok_tersedia} {$barang->satuan}, Diminta: {$detail->jumlah} {$barang->satuan}).");
                    }
                }

                // 5. Potong stok dan catat StockMovement
                $hasInventaris = false;
                foreach ($details as $detail) {
                    $barang = $barangs->get($detail->barang_id);

                    if ($barang->jenis_barang === 'inventaris') {
                        $hasInventaris = true;
                        $barang->stok_tersedia -= $detail->jumlah;
                        $barang->stok_dipinjam += $detail->jumlah;
                        $barang->save();

                        StockMovement::create([
                            'barang_id'      => $barang->id,
                            'user_id'        => $user->id,
                            'jenis'          => 'peminjaman',
                            'jumlah'         => $detail->jumlah,
                            'referensi_tipe' => 'peminjamans',
                            'referensi_id'   => $peminjaman->id,
                            'keterangan'     => "Peminjaman alat #TRX-" . str_pad($peminjaman->id, 4, '0', STR_PAD_LEFT) . " disetujui untuk " . ($peminjaman->user->name ?? 'Peminjam'),
                            'created_at'     => now(),
                        ]);
                    } else {
                        // BHP: Dikonsumsi habis, stok_tersedia dan stok_total berkurang permanen
                        $barang->stok_tersedia -= $detail->jumlah;
                        $barang->stok_total -= $detail->jumlah;
                        $barang->save();

                        StockMovement::create([
                            'barang_id'      => $barang->id,
                            'user_id'        => $user->id,
                            'jenis'          => 'bhp_keluar',
                            'jumlah'         => $detail->jumlah,
                            'referensi_tipe' => 'peminjamans',
                            'referensi_id'   => $peminjaman->id,
                            'keterangan'     => "Pengambilan BHP #TRX-" . str_pad($peminjaman->id, 4, '0', STR_PAD_LEFT) . " disetujui untuk " . ($peminjaman->user->name ?? 'Peminjam'),
                            'created_at'     => now(),
                        ]);
                    }
                }

                // 6. Update status akhir tiket peminjaman
                $finalStatus = $hasInventaris ? 'active' : 'selesai';
                $peminjaman->update([
                    'status'        => $finalStatus,
                    'diproses_oleh' => $user->id,
                    'diproses_pada' => now(),
                ]);

                return '#TRX-' . str_pad($peminjaman->id, 4, '0', STR_PAD_LEFT);
            });

            return redirect()->route('toolman.peminjaman.index', ['tab' => 'riwayat'])
                ->with('success', "Tiket {$trxCode} berhasil disetujui dan diserahkan kepada peminjam.");
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error("Gagal memproses persetujuan tiket #{$id}: " . $e->getMessage());
            return redirect()->back()->with('error', "Gagal memproses persetujuan: Terjadi kesalahan sistem atau konflik transaksi.");
        }
    }

    public function reject(Request $request, $id)
    {
        $user = auth()->user();
        $bengkelId = $user->bengkel_id;
        if (!$bengkelId) {
            abort(403, 'Akun Toolman Anda belum ditugaskan ke unit bengkel manapun. Silakan hubungi Waka Sarpras.');
        }

        // Normalisasi input dari frontend konfirmasi modal (alasan / alasan_penolakan)
        $alasan = $request->input('alasan_penolakan') ?? $request->input('alasan');
        $request->merge(['alasan_penolakan' => $alasan]);

        $request->validate([
            'alasan_penolakan' => 'required|string|min:3|max:500',
        ], [
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi agar peminjam mengetahui alasan pembatalan.',
            'alasan_penolakan.min'      => 'Alasan penolakan minimal 3 karakter.',
            'alasan_penolakan.max'      => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        try {
            $trxCode = DB::transaction(function () use ($id, $bengkelId, $request, $user) {
                // Kunci dan ambil tiket peminjaman
                $peminjaman = Peminjaman::where('bengkel_id', $bengkelId)
                    ->where('id', $id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!in_array($peminjaman->status, ['pending', 'menunggu_acc'])) {
                    throw new \DomainException("Tiket peminjaman #{$peminjaman->id} sudah tidak dalam status menunggu persetujuan (status saat ini: {$peminjaman->status}).");
                }

                $peminjaman->update([
                    'status'           => 'ditolak',
                    'alasan_penolakan' => $request->input('alasan_penolakan'),
                    'diproses_oleh'    => $user->id,
                    'diproses_pada'    => now(),
                ]);

                return '#TRX-' . str_pad($peminjaman->id, 4, '0', STR_PAD_LEFT);
            });

            return redirect()->route('toolman.peminjaman.index', ['tab' => 'riwayat'])
                ->with('success', "Pengajuan peminjaman {$trxCode} berhasil ditolak.");
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error("Gagal menolak tiket peminjaman #{$id}: " . $e->getMessage());
            return redirect()->back()->with('error', "Gagal memproses penolakan: Terjadi kesalahan sistem atau konflik transaksi.");
        }
    }
}
