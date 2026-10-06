<?php

namespace App\Http\Controllers\Peminjam;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Bengkel;
use App\Models\DetailPeminjaman;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengajuanController extends Controller
{
    /**
     * Redirect direct pengajuan create requests to Katalog with open cart.
     */
    public function create()
    {
        return redirect()->route('peminjam.katalog.index', ['open_cart' => 1]);
    }

    /**
     * Simpan pengajuan peminjaman baru (dari keranjang multi-item atau single item).
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Parse items jika dikirim dalam bentuk JSON (dari keranjang katalog)
        $rawItems = $request->input('items');
        if (is_string($rawItems)) {
            $rawItems = json_decode($rawItems, true);
        }

        // Jika bukan array items, cek single input form
        if (empty($rawItems) && $request->filled('barang_id')) {
            $rawItems = [
                [
                    'barang_id' => $request->input('barang_id'),
                    'jumlah' => $request->input('jumlah', 1),
                ]
            ];
        }

        if (empty($rawItems) || !is_array($rawItems)) {
            return back()->with('error', 'Tidak ada barang yang dipilih untuk diajukan.');
        }

        // Tentukan bengkel transaksi
        if ($user->isGuru()) {
            $bengkelId = $request->input('bengkel_id');
            if (!$bengkelId) {
                $firstItem = Barang::find($rawItems[0]['barang_id'] ?? $rawItems[0]['id'] ?? null);
                $bengkelId = $firstItem?->bengkel_id ?? Bengkel::first()?->id;
            }
        } else {
            $bengkelId = $user->bengkel_id ?? Bengkel::first()?->id;
        }

        // Validasi input keperluan, jadwal pinjam (maks. 14 hari ke depan), & batas waktu
        $maxAdvanceDate = now()->addDays(14)->endOfDay();
        $isTanggalPinjamFilled = $request->filled('tanggal_pinjam');
        $request->validate([
            'keperluan' => 'required|string|min:5|max:1000',
            'tanggal_pinjam' => [
                'nullable',
                'date',
                'after_or_equal:' . now()->subMinutes(15)->format('Y-m-d H:i:s'),
                'before_or_equal:' . $maxAdvanceDate->format('Y-m-d H:i:s'),
            ],
            'batas_kembali' => [
                'nullable',
                'date',
                $isTanggalPinjamFilled ? 'after:tanggal_pinjam' : 'after_or_equal:' . now()->subMinutes(5)->format('Y-m-d H:i:s'),
            ],
        ], [
            'keperluan.required' => 'Keperluan peminjaman wajib diisi.',
            'keperluan.min' => 'Keperluan peminjaman minimal 5 karakter.',
            'tanggal_pinjam.after_or_equal' => 'Jadwal peminjaman/pengambilan tidak boleh di masa lampau.',
            'tanggal_pinjam.before_or_equal' => 'Peminjaman maksimal dapat diajukan 14 hari sebelum hari pelaksanaan praktikum.',
            'batas_kembali.after' => 'Batas waktu pengembalian harus setelah jadwal peminjaman.',
        ]);

        try {
            return DB::transaction(function () use ($user, $bengkelId, $rawItems, $request) {
                $hasInventaris = false;

                // Tentukan tanggal rencana pinjam/ambil
                $tanggalPinjam = $request->filled('tanggal_pinjam')
                    ? \Carbon\Carbon::parse($request->input('tanggal_pinjam'))
                    : now();

                // Validasi setiap barang
                $itemsToInsert = [];
                foreach ($rawItems as $item) {
                    $barangId = $item['barang_id'] ?? $item['id'] ?? null;
                    $qty = (int) ($item['jumlah'] ?? $item['qty'] ?? 1);

                    if (!$barangId || $qty <= 0) continue;

                    $barang = Barang::where('bengkel_id', $bengkelId)->find($barangId);
                    if (!$barang) {
                        throw new \Exception("Barang dengan ID {$barangId} tidak ditemukan pada bengkel terpilih.");
                    }

                    if ($qty > $barang->stok_bebas) {
                        throw new \Exception("Stok untuk barang '{$barang->nama}' tidak mencukupi untuk peminjaman baru (sisa kuota bebas: {$barang->stok_bebas}, diminta: {$qty}).");
                    }

                    if ($barang->jenis_barang === 'inventaris') {
                        $hasInventaris = true;
                    }

                    $itemsToInsert[] = [
                        'barang_id' => $barang->id,
                        'jumlah' => $qty,
                    ];
                }

                if (empty($itemsToInsert)) {
                    throw new \Exception('Daftar barang tidak valid atau kosong.');
                }

                // Batas kembali: Wajib untuk tiket yang berisi barang inventaris
                $batasKembali = $request->filled('batas_kembali')
                    ? \Carbon\Carbon::parse($request->input('batas_kembali'))
                    : null;

                if ($hasInventaris && !$batasKembali) {
                    $batasKembali = (clone $tanggalPinjam)->hour >= 15
                        ? (clone $tanggalPinjam)->addDay()->setTime(16, 0)
                        : (clone $tanggalPinjam)->setTime(16, 0);

                    if ($batasKembali <= $tanggalPinjam) {
                        $batasKembali = (clone $tanggalPinjam)->addHours(4);
                    }
                }

                $peminjaman = Peminjaman::create([
                    'user_id' => $user->id,
                    'bengkel_id' => $bengkelId,
                    'tanggal_pinjam' => $tanggalPinjam,
                    'batas_kembali' => $hasInventaris ? $batasKembali : null,
                    'keperluan' => $request->input('keperluan'),
                    'status' => 'pending',
                ]);

                foreach ($itemsToInsert as $itemData) {
                    DetailPeminjaman::create([
                        'peminjaman_id' => $peminjaman->id,
                        'barang_id' => $itemData['barang_id'],
                        'jumlah' => $itemData['jumlah'],
                        'jumlah_baik' => 0,
                        'jumlah_rusak' => 0,
                        'jumlah_hilang' => 0,
                    ]);
                }

                return redirect()->route('peminjam.tiket.index')
                    ->with('success', "Tiket peminjaman #TRX-" . str_pad($peminjaman->id, 4, '0', STR_PAD_LEFT) . " berhasil diajukan dan sedang menunggu persetujuan Toolman!");
            });
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
