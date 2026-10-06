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

class PengembalianController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $bengkelId = $user->bengkel_id;
        if (!$bengkelId) {
            abort(403, 'Akun Toolman Anda belum ditugaskan ke unit bengkel manapun. Silakan hubungi Waka Sarpras.');
        }
        $bengkel = $user->bengkel ?? Bengkel::findOrFail($bengkelId);
        $tab = $request->input('tab', 'aktif'); // 'aktif' | 'riwayat'

        $query = Peminjaman::with(['user', 'bengkel', 'detailPeminjamans.barang.lokasiPenyimpanan', 'diprosesOleh'])
            ->where('bengkel_id', $bengkelId)
            ->whereHas('detailPeminjamans.barang', function ($q) {
                $q->where('jenis_barang', 'inventaris');
            });

        if ($tab === 'riwayat') {
            $query->where('status', 'selesai')->latest('updated_at');
        } else {
            $query->whereIn('status', ['active', 'terlambat', 'menunggu_pengecekan'])->orderBy('batas_kembali');
        }

        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nomor_identitas', 'like', "%{$search}%");
            });
        }

        $peminjamans = $query->paginate(10)->withQueryString();

        $aktifCount = Peminjaman::where('bengkel_id', $bengkelId)
            ->whereIn('status', ['active', 'terlambat', 'menunggu_pengecekan'])
            ->whereHas('detailPeminjamans.barang', function ($q) {
                $q->where('jenis_barang', 'inventaris');
            })->count();

        $riwayatCount = Peminjaman::where('bengkel_id', $bengkelId)
            ->where('status', 'selesai')
            ->whereHas('detailPeminjamans.barang', function ($q) {
                $q->where('jenis_barang', 'inventaris');
            })->count();

        return view('toolman.pengembalian.index', compact('peminjamans', 'bengkel', 'tab', 'aktifCount', 'riwayatCount'));
    }

    public function check($id)
    {
        $user = auth()->user();
        $bengkelId = $user->bengkel_id;
        if (!$bengkelId) {
            abort(403, 'Akun Toolman Anda belum ditugaskan ke unit bengkel manapun. Silakan hubungi Waka Sarpras.');
        }
        $bengkel = $user->bengkel ?? Bengkel::findOrFail($bengkelId);

        $peminjaman = Peminjaman::with(['user', 'bengkel', 'detailPeminjamans.barang.lokasiPenyimpanan'])
            ->where('bengkel_id', $bengkelId)
            ->findOrFail($id);

        if (!in_array($peminjaman->status, ['active', 'terlambat', 'menunggu_pengecekan'])) {
            return redirect()->route('toolman.pengembalian.index')
                ->with('error', "Tiket peminjaman #{$peminjaman->id} sudah tidak dalam status yang dapat dicek fisik (status: {$peminjaman->status}).");
        }

        return view('toolman.pengembalian.check', compact('peminjaman', 'bengkel'));
    }

    public function processCheck(Request $request, $id)
    {
        $user = auth()->user();
        $bengkelId = $user->bengkel_id;
        if (!$bengkelId) {
            abort(403, 'Akun Toolman Anda belum ditugaskan ke unit bengkel manapun. Silakan hubungi Waka Sarpras.');
        }
        $items = $request->input('items', []);

        try {
            $trxCode = DB::transaction(function () use ($id, $bengkelId, $items, $user) {
                // 1. Kunci dan ambil peminjaman di dalam transaksi (Lock Order #1)
                $peminjaman = Peminjaman::with('user')
                    ->where('bengkel_id', $bengkelId)
                    ->where('id', $id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 2. Validasi status peminjaman di DALAM transaksi terkunci
                if (!in_array($peminjaman->status, ['active', 'terlambat', 'menunggu_pengecekan'])) {
                    throw new \DomainException("Tiket peminjaman #{$peminjaman->id} sudah tidak dalam status yang dapat dicek fisik (status saat ini: {$peminjaman->status}).");
                }

                // 3. Ambil detail inventaris yang terkunci
                $inventarisDetails = $peminjaman->detailPeminjamans()
                    ->whereHas('barang', function ($q) {
                        $q->where('jenis_barang', 'inventaris');
                    })
                    ->lockForUpdate()
                    ->get();

                if ($inventarisDetails->isEmpty()) {
                    throw new \DomainException("Tiket peminjaman #{$peminjaman->id} tidak memuat barang inventaris untuk dicek fisik.");
                }

                // 4. Kunci seluruh barang yang terlibat dengan urutan id menaik (Lock Order #2: ORDER BY id ASC)
                $barangIds = $inventarisDetails->pluck('barang_id')->unique()->sort()->values()->all();
                $barangs = Barang::whereIn('id', $barangIds)
                    ->lockForUpdate()
                    ->orderBy('id', 'asc')
                    ->get()
                    ->keyBy('id');

                // 5. Validasi kelengkapan dan kecocokan jumlah untuk setiap detail barang inventaris
                foreach ($inventarisDetails as $detail) {
                    $barang = $barangs->get($detail->barang_id);
                    if (!$barang) {
                        throw new \DomainException("Data barang #{$detail->barang_id} tidak ditemukan.");
                    }

                    if (!isset($items[$detail->id])) {
                        throw new \DomainException("Data inspeksi untuk barang '{$barang->nama}' belum diisi.");
                    }

                    $itemData = $items[$detail->id];
                    $baik = isset($itemData['jumlah_baik']) ? (int) $itemData['jumlah_baik'] : 0;
                    $rusak = isset($itemData['jumlah_rusak']) ? (int) $itemData['jumlah_rusak'] : 0;
                    $hilang = isset($itemData['jumlah_hilang']) ? (int) $itemData['jumlah_hilang'] : 0;

                    if ($baik < 0 || $rusak < 0 || $hilang < 0) {
                        throw new \DomainException("Jumlah kondisi untuk barang '{$barang->nama}' tidak boleh bernilai negatif.");
                    }

                    $totalCheck = $baik + $rusak + $hilang;
                    if ($totalCheck !== (int) $detail->jumlah) {
                        throw new \DomainException("Total kuantitas pemeriksaan barang '{$barang->nama}' (Baik: {$baik} + Rusak: {$rusak} + Hilang: {$hilang} = {$totalCheck}) tidak sesuai dengan jumlah dipinjam ({$detail->jumlah}).");
                    }
                }

                $trxCodeFormatted = '#TRX-' . str_pad($peminjaman->id, 4, '0', STR_PAD_LEFT);
                $peminjamName = $peminjaman->user->name ?? 'Peminjam';

                // 6. Update stok, detail kondisi, dan catat StockMovement secara atomik
                foreach ($inventarisDetails as $detail) {
                    $barang = $barangs->get($detail->barang_id);
                    $itemData = $items[$detail->id];
                    $baik = (int) $itemData['jumlah_baik'];
                    $rusak = (int) $itemData['jumlah_rusak'];
                    $hilang = (int) $itemData['jumlah_hilang'];
                    $catatan = isset($itemData['catatan']) ? trim($itemData['catatan']) : null;

                    // Update stok barang sesuai PRD 3.6:
                    // 1. Barang kembali baik: kembali ke stok_tersedia
                    // 2. Barang kembali rusak: pindah dari stok_dipinjam ke stok_rusak
                    // 3. Barang hilang: mengurangi stok_dipinjam sekaligus stok_total secara permanen
                    $barang->stok_tersedia += $baik;
                    $barang->stok_rusak += $rusak;
                    $barang->stok_dipinjam -= ($baik + $rusak + $hilang);
                    $barang->stok_total -= $hilang;

                    // Mencegah nilai negatif jika terjadi anomali
                    if ($barang->stok_dipinjam < 0) $barang->stok_dipinjam = 0;
                    if ($barang->stok_tersedia < 0) $barang->stok_tersedia = 0;
                    if ($barang->stok_total < 0) $barang->stok_total = 0;

                    $barang->save();

                    // Simpan data kondisi di detail_peminjamans
                    $detail->update([
                        'jumlah_baik'   => $baik,
                        'jumlah_rusak'  => $rusak,
                        'jumlah_hilang' => $hilang,
                    ]);

                    // Catat StockMovement:
                    if ($baik > 0) {
                        StockMovement::create([
                            'barang_id'      => $barang->id,
                            'user_id'        => $user->id,
                            'jenis'          => 'pengembalian_baik',
                            'jumlah'         => $baik,
                            'referensi_tipe' => 'peminjamans',
                            'referensi_id'   => $peminjaman->id,
                            'keterangan'     => "Pengembalian barang kondisi baik {$trxCodeFormatted} dari {$peminjamName}",
                            'created_at'     => now(),
                        ]);
                    }

                    if ($rusak > 0) {
                        $ketRusak = "Pengembalian barang kondisi rusak {$trxCodeFormatted} dari {$peminjamName}";
                        if ($catatan) {
                            $ketRusak .= " (Catatan: {$catatan})";
                        }
                        StockMovement::create([
                            'barang_id'      => $barang->id,
                            'user_id'        => $user->id,
                            'jenis'          => 'pengembalian_rusak',
                            'jumlah'         => $rusak,
                            'referensi_tipe' => 'peminjamans',
                            'referensi_id'   => $peminjaman->id,
                            'keterangan'     => $ketRusak,
                            'created_at'     => now(),
                        ]);
                    }

                    if ($hilang > 0) {
                        $ketHilang = "Barang hilang pada tiket peminjaman {$trxCodeFormatted} oleh {$peminjamName}";
                        if ($catatan) {
                            $ketHilang .= " (Catatan: {$catatan})";
                        }
                        StockMovement::create([
                            'barang_id'      => $barang->id,
                            'user_id'        => $user->id,
                            'jenis'          => 'barang_hilang',
                            'jumlah'         => $hilang,
                            'referensi_tipe' => 'peminjamans',
                            'referensi_id'   => $peminjaman->id,
                            'keterangan'     => $ketHilang,
                            'created_at'     => now(),
                        ]);
                    }
                }

                // 7. Selesaikan transaksi peminjaman
                $peminjaman->update([
                    'status' => 'selesai',
                ]);

                return $trxCodeFormatted;
            });

            return redirect()->route('toolman.pengembalian.index')
                ->with('success', "Pengecekan fisik berhasil! Peminjaman {$trxCode} telah selesai diperiksa dan stok bengkel telah diperbarui.");
        } catch (\DomainException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error("Gagal memproses pengecekan fisik tiket #{$id}: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', "Gagal memproses pengecekan fisik: Terjadi kesalahan sistem atau konflik transaksi.");
        }
    }

    /**
     * Cetak Lembar Bon Pinjam Alat / Bahan Resmi
     * Sesuai format standar Kartu Pinjam.md
     */
    public function printPinjam($id)
    {
        $user = auth()->user();
        $bengkelId = $user->bengkel_id;
        if (!$bengkelId) {
            abort(403, 'Akun Toolman Anda belum ditugaskan ke unit bengkel manapun. Silakan hubungi Waka Sarpras.');
        }

        $peminjaman = Peminjaman::with([
            'user',
            'bengkel',
            'detailPeminjamans.barang.lokasiPenyimpanan',
            'diprosesOleh'
        ])
            ->where('bengkel_id', $bengkelId)
            ->findOrFail($id);

        if (in_array($peminjaman->status, ['pending', 'menunggu_acc', 'ditolak'])) {
            return redirect()->route('toolman.peminjaman.show', $id)
                ->with('error', 'Bukti pinjam tidak dapat dicetak untuk peminjaman yang berstatus ' . ($peminjaman->status === 'ditolak' ? 'Ditolak' : 'Menunggu Persetujuan') . '.');
        }

        $bengkel = $peminjaman->bengkel ?? ($user->bengkel ?? Bengkel::findOrFail($bengkelId));

        return view('toolman.pengembalian.print_pinjam', compact('peminjaman', 'bengkel'));
    }

    /**
     * Cetak Lembar Bukti Pengembalian Alat / Bahan Resmi
     * Sesuai format standar Kartu Pinjam.md
     */
    public function printKembali($id)
    {
        $user = auth()->user();
        $bengkelId = $user->bengkel_id;
        if (!$bengkelId) {
            abort(403, 'Akun Toolman Anda belum ditugaskan ke unit bengkel manapun. Silakan hubungi Waka Sarpras.');
        }

        $peminjaman = Peminjaman::with([
            'user',
            'bengkel',
            'detailPeminjamans.barang.lokasiPenyimpanan',
            'diprosesOleh'
        ])
            ->where('bengkel_id', $bengkelId)
            ->findOrFail($id);

        if ($peminjaman->status !== 'selesai') {
            return redirect()->route('toolman.peminjaman.show', $id)
                ->with('error', 'Bukti pengembalian hanya dapat dicetak setelah barang selesai dikembalikan.');
        }

        $bengkel = $peminjaman->bengkel ?? ($user->bengkel ?? Bengkel::findOrFail($bengkelId));

        return view('toolman.pengembalian.print_kembali', compact('peminjaman', 'bengkel'));
    }
}
