<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barangs';

    protected $fillable = [
        'bengkel_id',
        'lokasi_penyimpanan_id',
        'sumber_dana_id',
        'kode_barang',
        'nama',
        'jenis_barang',
        'satuan',
        'harga',
        'stok_total',
        'stok_tersedia',
        'stok_dipinjam',
        'stok_rusak',
        'minimum_stok',
        'deskripsi',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
        ];
    }

    public function bengkel()
    {
        return $this->belongsTo(Bengkel::class);
    }

    public function lokasiPenyimpanan()
    {
        return $this->belongsTo(LokasiPenyimpanan::class);
    }

    public function sumberDana()
    {
        return $this->belongsTo(SumberDana::class);
    }

    public function detailPeminjamans()
    {
        return $this->hasMany(DetailPeminjaman::class);
    }

    public function detailPengadaans()
    {
        return $this->hasMany(DetailPengadaan::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Total unit barang yang sudah di-ACC jadwalnya tapi belum diambil secara fisik.
     */
    public function getStokReservedAttribute(): int
    {
        return (int) $this->detailPeminjamans()
            ->whereHas('peminjaman', function ($q) {
                $q->where('status', 'disetujui');
            })
            ->sum('jumlah');
    }

    /**
     * Sisa kuota stok yang bebas diajukan untuk peminjaman baru.
     */
    public function getStokBebasAttribute(): int
    {
        return max(0, $this->stok_tersedia - $this->stok_reserved);
    }
}
