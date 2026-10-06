<?php

namespace App\Console\Commands;

use App\Models\Barang;
use App\Models\DetailPeminjaman;
use App\Models\DetailPengadaan;
use App\Models\Peminjaman;
use App\Models\Pengadaan;
use App\Models\StockMovement;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetDemoDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-demo {--force : Jalankan reset tanpa konfirmasi interaktif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mereset data demo SIBENKA: membersihkan transaksi demo, mengembalikan stok barang, dan menyinkronkan akun demo.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Apakah Anda yakin ingin mereset seluruh data demo SIBENKA? Transaksi pinjam & pengadaan akan dibersihkan.')) {
                $this->info('Reset data demo dibatalkan.');
                return self::SUCCESS;
            }
        }

        $this->info('Memulai pembersihan data transaksi dan pengembalian stok...');

        DB::transaction(function () {
            // 1. Bersihkan transaksi demo
            DetailPeminjaman::query()->delete();
            Peminjaman::query()->delete();
            DetailPengadaan::query()->delete();
            Pengadaan::query()->delete();
            StockMovement::query()->delete();

            // 2. Normalkan kembali stok barang ke posisi awal (stok_reserved otomatis 0 karena transaksi dihapus)
            Barang::query()->update([
                'stok_tersedia' => DB::raw('stok_total'),
                'stok_dipinjam' => 0,
                'stok_rusak'    => 0,
            ]);
        });

        $this->info('Menjalankan ulang DatabaseSeeder untuk memastikan master data dan akun demo tersinkronisasi...');

        // 3. Jalankan DatabaseSeeder
        $this->call(DatabaseSeeder::class);

        $this->newLine();
        $this->info('====================================================');
        $this->info(' [SUKSES] Data Demo SIBENKA Berhasil Direset!');
        $this->info(' Semua akun demo aktif dengan password: password');
        $this->info(' Stok barang telah dikembalikan ke kondisi awal.');
        $this->info('====================================================');

        return self::SUCCESS;
    }
}
