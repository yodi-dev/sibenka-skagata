<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Bengkel;
use App\Models\DetailPengadaan;
use App\Models\Pengadaan;
use App\Models\Peminjaman;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicalGateTest extends TestCase
{
    use RefreshDatabase;

    private function createBengkel(string $kode = 'TKJ', string $nama = 'Teknik Komputer Jaringan'): Bengkel
    {
        return Bengkel::create([
            'kode' => $kode,
            'nama' => $nama,
            'deskripsi' => 'Bengkel Praktik TKJ',
        ]);
    }

    private function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'status' => 'aktif',
        ], $attributes));
    }

    /**
     * 1. Usaha mengubah klasifikasi jenis_barang yang sudah disetujui Waka Sarpras (attempted tampering)
     *    pada saat penerimaan fisik DITOLAK tegas demi integritas data persetujuan.
     */
    public function test_rab_receipt_rejects_attempted_tampering_of_approved_item_classification(): void
    {
        $bengkel = $this->createBengkel('TAV', 'Teknik Audio Video');
        $toolman = $this->createUser(['name' => 'Budi Toolman', 'role' => 'toolman', 'bengkel_id' => $bengkel->id]);
        $waka = $this->createUser(['role' => 'waka']);

        $pengadaan = Pengadaan::create([
            'bengkel_id' => $bengkel->id,
            'dibuat_oleh' => $toolman->id,
            'judul' => 'Pengadaan Osiloskop Digital Approved',
            'status' => 'approved',
            'diajukan_pada' => now()->subDays(2),
            'direview_oleh' => $waka->id,
            'direview_pada' => now()->subDay(),
        ]);

        $detail = DetailPengadaan::create([
            'pengadaan_id' => $pengadaan->id,
            'barang_id' => null,
            'nama_barang' => 'Digital Storage Oscilloscope Dual Channel',
            'jenis_barang' => 'inventaris', // Disetujui resmi sebagai Inventaris
            'minimum_stok' => 2,
            'spesifikasi' => '100MHz 2 Channel',
            'jumlah' => 2,
            'satuan' => 'Unit',
            'harga_satuan' => 3500000,
        ]);

        // Toolman mencoba mengubah klasifikasi dari 'inventaris' menjadi 'bhp' saat receive
        $response = $this->actingAs($toolman)->post(route('toolman.pengadaan.receive', $pengadaan->id), [
            'items_classification' => [
                $detail->id => [
                    'jenis_barang' => 'bhp', // Percobaan manipulasi klasifikasi!
                    'minimum_stok' => 2,
                ],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
        $errorMessage = session('error');
        $this->assertStringContainsString('telah disetujui sebagai Alat Inventaris', $errorMessage);
        $this->assertStringContainsString('tidak dapat diubah saat penerimaan fisik', $errorMessage);

        // Pastikan status pengadaan tidak berubah menjadi selesai
        $pengadaan->refresh();
        $this->assertEquals('approved', $pengadaan->status);

        // Pastikan data detail tidak tertimpa
        $detail->refresh();
        $this->assertEquals('inventaris', $detail->jenis_barang);

        // Pastikan tidak ada barang BHP baru yang terbuat
        $this->assertDatabaseMissing('barangs', [
            'nama' => 'Digital Storage Oscilloscope Dual Channel',
            'jenis_barang' => 'bhp',
        ]);
        $this->assertEquals(0, StockMovement::count());
    }

    /**
     * 2. Usaha mengubah batas minimum stok yang telah disetujui pada RAB saat penerimaan fisik DITOLAK.
     */
    public function test_rab_receipt_rejects_attempted_tampering_of_approved_minimum_stock(): void
    {
        $bengkel = $this->createBengkel('TKJ', 'Teknik Komputer Jaringan');
        $toolman = $this->createUser(['role' => 'toolman', 'bengkel_id' => $bengkel->id]);
        $waka = $this->createUser(['role' => 'waka']);

        $pengadaan = Pengadaan::create([
            'bengkel_id' => $bengkel->id,
            'dibuat_oleh' => $toolman->id,
            'judul' => 'Pengadaan Switch Managed',
            'status' => 'approved',
            'diajukan_pada' => now()->subDay(),
            'direview_oleh' => $waka->id,
            'direview_pada' => now(),
        ]);

        $detail = DetailPengadaan::create([
            'pengadaan_id' => $pengadaan->id,
            'barang_id' => null,
            'nama_barang' => 'Switch Gigabit 24 Port Managed',
            'jenis_barang' => 'inventaris',
            'minimum_stok' => 5, // Ditetapkan minimum 5
            'jumlah' => 3,
            'satuan' => 'Unit',
            'harga_satuan' => 2500000,
        ]);

        // Toolman mencoba mengubah minimum_stok menjadi 0
        $response = $this->actingAs($toolman)->post(route('toolman.pengadaan.receive', $pengadaan->id), [
            'items_classification' => [
                $detail->id => [
                    'jenis_barang' => 'inventaris',
                    'minimum_stok' => 0, // Percobaan manipulasi batas minimum
                ],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Batas minimum stok barang', session('error'));
        $this->assertStringContainsString('telah ditetapkan (5)', session('error'));

        $pengadaan->refresh();
        $this->assertEquals('approved', $pengadaan->status);
        $this->assertDatabaseMissing('barangs', [
            'nama' => 'Switch Gigabit 24 Port Managed',
        ]);
    }

    /**
     * 3. Alur valid item legacy tanpa jenis_barang: konfirmasi Toolman divalidasi, disimpan permanen di RAB,
     *    dan aktor pelaksana dicatat dalam audit trail StockMovement.
     */
    public function test_rab_receipt_valid_legacy_path_stores_confirmation_and_records_actor_audit(): void
    {
        $bengkel = $this->createBengkel('TKR', 'Teknik Kendaraan Ringan');
        $toolman = $this->createUser(['name' => 'Joko Toolman', 'role' => 'toolman', 'bengkel_id' => $bengkel->id]);
        $waka = $this->createUser(['role' => 'waka']);

        $pengadaan = Pengadaan::create([
            'bengkel_id' => $bengkel->id,
            'dibuat_oleh' => $toolman->id,
            'judul' => 'Pengadaan Perlengkapan Oli Mesin Legacy',
            'status' => 'approved',
            'diajukan_pada' => now()->subDays(5),
            'direview_oleh' => $waka->id,
            'direview_pada' => now()->subDays(4),
        ]);

        $detailLegacy = DetailPengadaan::create([
            'pengadaan_id' => $pengadaan->id,
            'barang_id' => null,
            'nama_barang' => 'Cairan Pembersih Karburator Carb Cleaner',
            'jenis_barang' => null, // Data lama sebelum migrasi
            'minimum_stok' => null,
            'jumlah' => 12,
            'satuan' => 'Kaleng',
            'harga_satuan' => 45000,
        ]);

        $response = $this->actingAs($toolman)->post(route('toolman.pengadaan.receive', $pengadaan->id), [
            'items_classification' => [
                $detailLegacy->id => [
                    'jenis_barang' => 'bhp',
                    'minimum_stok' => 4,
                ],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Status selesai
        $pengadaan->refresh();
        $this->assertEquals('selesai', $pengadaan->status);

        // Detail terupdate dan tersimpan permanen
        $detailLegacy->refresh();
        $this->assertEquals('bhp', $detailLegacy->jenis_barang);
        $this->assertEquals(4, $detailLegacy->minimum_stok);
        $this->assertNotNull($detailLegacy->barang_id);

        // Barang baru terbuat dengan kode BHP
        $newBarang = Barang::findOrFail($detailLegacy->barang_id);
        $this->assertEquals('bhp', $newBarang->jenis_barang);
        $this->assertStringStartsWith('BHP-TKR-', $newBarang->kode_barang);
        $this->assertEquals(4, $newBarang->minimum_stok);
        $this->assertEquals(12, $newBarang->stok_total);

        // Audit Trail StockMovement mencatat Toolman pelaksana dan konfirmasi klasifikasi
        $movement = StockMovement::where('barang_id', $newBarang->id)->first();
        $this->assertNotNull($movement);
        $this->assertEquals($toolman->id, $movement->user_id);
        $this->assertEquals(12, $movement->jumlah);
        $this->assertEquals('stok_masuk', $movement->jenis);
        $this->assertStringContainsString('Klasifikasi awal dikonfirmasi oleh Joko Toolman', $movement->keterangan);
    }

    /**
     * 4. Rute publik registrasi memiliki throttling terhadap abuse (maksimal 6 per menit).
     */
    public function test_registration_route_is_throttled_against_abuse(): void
    {
        $bengkel = $this->createBengkel();

        // Uji throttling pada POST /register (batas 6 request per menit)
        for ($i = 1; $i <= 6; $i++) {
            $this->post('/register', [
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'jenis_peminjam' => 'siswa',
                'nomor_identitas' => "NIS{$i}",
                'bengkel_id' => $bengkel->id,
            ]);
        }

        // Request ke-7 harus terkena HTTP 429 Too Many Requests
        $responseBlocked = $this->post('/register', [
            'name' => 'User 7',
            'email' => 'user7@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'jenis_peminjam' => 'siswa',
            'nomor_identitas' => 'NIS7',
            'bengkel_id' => $bengkel->id,
        ]);

        $this->assertEquals(429, $responseBlocked->getStatusCode(), 'POST /register must be rate limited to prevent spam/abuse.');
    }

    /**
     * 5. Penolakan peminjaman oleh Toolman berhasil baik saat request mengirimkan 'alasan_penolakan'
     *    maupun parameter 'alasan' dari modal konfirmasi frontend.
     */
    public function test_toolman_can_reject_loan_with_either_alasan_or_alasan_penolakan(): void
    {
        $bengkel = $this->createBengkel('B-TOLAK', 'Bengkel Uji Tolak');
        $toolman = $this->createUser([
            'role' => 'toolman',
            'bengkel_id' => $bengkel->id,
            'status' => 'aktif',
        ]);
        $peminjam = $this->createUser([
            'role' => 'peminjam',
            'bengkel_id' => $bengkel->id,
            'status' => 'aktif',
        ]);

        // Tiket 1: ditolak menggunakan field 'alasan_penolakan'
        $pinjaman1 = Peminjaman::create([
            'bengkel_id' => $bengkel->id,
            'user_id' => $peminjam->id,
            'tanggal_pinjam' => now(),
            'batas_kembali' => now()->addDays(1),
            'status' => 'pending',
            'keperluan' => 'Praktik Uji 1',
        ]);

        $response1 = $this->actingAs($toolman)->post(route('toolman.peminjaman.reject', $pinjaman1->id), [
            'alasan_penolakan' => 'Barang sedang dalam perawatan berkala',
        ]);

        $response1->assertRedirect(route('toolman.peminjaman.index', ['tab' => 'riwayat']));
        $response1->assertSessionHas('success');
        $pinjaman1->refresh();
        $this->assertEquals('ditolak', $pinjaman1->status);
        $this->assertEquals('Barang sedang dalam perawatan berkala', $pinjaman1->alasan_penolakan);
        $this->assertEquals($toolman->id, $pinjaman1->diproses_oleh);
        $this->assertNotNull($pinjaman1->diproses_pada);

        // Tiket 2: ditolak menggunakan field 'alasan' (simulasi payload frontend confirm-modal)
        $pinjaman2 = Peminjaman::create([
            'bengkel_id' => $bengkel->id,
            'user_id' => $peminjam->id,
            'tanggal_pinjam' => now(),
            'batas_kembali' => now()->addDays(1),
            'status' => 'menunggu_acc',
            'keperluan' => 'Praktik Uji 2',
        ]);

        $response2 = $this->actingAs($toolman)->post(route('toolman.peminjaman.reject', $pinjaman2->id), [
            'alasan' => 'Jadwal peminjaman bentrok dengan praktikum kelas utama',
        ]);

        $response2->assertRedirect(route('toolman.peminjaman.index', ['tab' => 'riwayat']));
        $response2->assertSessionHas('success');
        $pinjaman2->refresh();
        $this->assertEquals('ditolak', $pinjaman2->status);
        $this->assertEquals('Jadwal peminjaman bentrok dengan praktikum kelas utama', $pinjaman2->alasan_penolakan);
        $this->assertEquals($toolman->id, $pinjaman2->diproses_oleh);
        $this->assertNotNull($pinjaman2->diproses_pada);

        // Tiket 3: gagal validasi jika alasan kosong atau kurang dari 3 karakter
        $pinjaman3 = Peminjaman::create([
            'bengkel_id' => $bengkel->id,
            'user_id' => $peminjam->id,
            'tanggal_pinjam' => now(),
            'batas_kembali' => now()->addDays(1),
            'status' => 'pending',
            'keperluan' => 'Praktik Uji 3',
        ]);

        $response3 = $this->actingAs($toolman)->post(route('toolman.peminjaman.reject', $pinjaman3->id), [
            'alasan' => 'ab', // Kurang dari 3 karakter
        ]);

        $response3->assertSessionHasErrors('alasan_penolakan');
        $pinjaman3->refresh();
        $this->assertEquals('pending', $pinjaman3->status);
    }

    /**
     * 6. Toolman tidak dapat mencetak bukti pinjam maupun bukti kembali untuk peminjaman yang ditolak,
     *    dan tombol cetak di halaman detail tidak ditampilkan.
     */
    public function test_toolman_cannot_print_receipts_when_loan_is_rejected(): void
    {
        $bengkel = $this->createBengkel('B-PRINT', 'Bengkel Print Check');
        $toolman = $this->createUser([
            'role' => 'toolman',
            'bengkel_id' => $bengkel->id,
            'status' => 'aktif',
        ]);
        $peminjam = $this->createUser([
            'role' => 'peminjam',
            'bengkel_id' => $bengkel->id,
            'status' => 'aktif',
        ]);

        $pinjamanDitolak = Peminjaman::create([
            'bengkel_id' => $bengkel->id,
            'user_id' => $peminjam->id,
            'tanggal_pinjam' => now(),
            'batas_kembali' => now()->addDays(1),
            'status' => 'ditolak',
            'keperluan' => 'Praktik Uji Ditolak',
            'alasan_penolakan' => 'Alat sedang rusak berat',
            'diproses_oleh' => $toolman->id,
            'diproses_pada' => now(),
        ]);

        // Halaman detail tidak boleh menampilkan tombol cetak bukti pinjam & bukti kembali
        $showResponse = $this->actingAs($toolman)->get(route('toolman.peminjaman.show', $pinjamanDitolak->id));
        $showResponse->assertStatus(200);
        $showResponse->assertDontSee('Cetak Bukti Pinjam');
        $showResponse->assertDontSee('Cetak Bukti Pengembalian');

        // Akses langsung rute print-pinjam harus ditolak & redirect
        $printPinjamResponse = $this->actingAs($toolman)->get(route('toolman.pengembalian.print-pinjam', $pinjamanDitolak->id));
        $printPinjamResponse->assertRedirect(route('toolman.peminjaman.show', $pinjamanDitolak->id));
        $printPinjamResponse->assertSessionHas('error');

        // Akses langsung rute print-kembali harus ditolak & redirect
        $printKembaliResponse = $this->actingAs($toolman)->get(route('toolman.pengembalian.print-kembali', $pinjamanDitolak->id));
        $printKembaliResponse->assertRedirect(route('toolman.peminjaman.show', $pinjamanDitolak->id));
        $printKembaliResponse->assertSessionHas('error');
    }

    /**
     * 7. Ketersediaan cetak bukti pinjam dan bukti kembali sesuai siklus hidup tiket peminjaman:
     *    - Pending: Tidak ada bukti pinjam maupun kembali.
     *    - Active: Ada bukti pinjam, tidak ada bukti kembali.
     *    - Selesai: Ada bukti pinjam dan bukti kembali.
     */
    public function test_toolman_print_receipt_availability_matches_loan_lifecycle(): void
    {
        $bengkel = $this->createBengkel('B-LIFE', 'Bengkel Siklus');
        $toolman = $this->createUser([
            'role' => 'toolman',
            'bengkel_id' => $bengkel->id,
            'status' => 'aktif',
        ]);
        $peminjam = $this->createUser([
            'role' => 'peminjam',
            'bengkel_id' => $bengkel->id,
            'status' => 'aktif',
        ]);

        // A. Status Active (Barang sedang dipinjam)
        $pinjamanActive = Peminjaman::create([
            'bengkel_id' => $bengkel->id,
            'user_id' => $peminjam->id,
            'tanggal_pinjam' => now(),
            'batas_kembali' => now()->addDays(1),
            'status' => 'active',
            'keperluan' => 'Praktik Uji Aktif',
            'diproses_oleh' => $toolman->id,
            'diproses_pada' => now(),
        ]);

        $activeShow = $this->actingAs($toolman)->get(route('toolman.peminjaman.show', $pinjamanActive->id));
        $activeShow->assertSee('Cetak Bukti Pinjam');
        $activeShow->assertDontSee('Cetak Bukti Pengembalian');

        $activePrintPinjam = $this->actingAs($toolman)->get(route('toolman.pengembalian.print-pinjam', $pinjamanActive->id));
        $activePrintPinjam->assertStatus(200);

        $activePrintKembali = $this->actingAs($toolman)->get(route('toolman.pengembalian.print-kembali', $pinjamanActive->id));
        $activePrintKembali->assertRedirect(route('toolman.peminjaman.show', $pinjamanActive->id));
        $activePrintKembali->assertSessionHas('error');

        // B. Status Selesai (Barang sudah dikembalikan)
        $pinjamanSelesai = Peminjaman::create([
            'bengkel_id' => $bengkel->id,
            'user_id' => $peminjam->id,
            'tanggal_pinjam' => now()->subDays(2),
            'batas_kembali' => now()->subDay(),
            'status' => 'selesai',
            'keperluan' => 'Praktik Uji Selesai',
            'diproses_oleh' => $toolman->id,
            'diproses_pada' => now()->subDays(2),
        ]);

        $selesaiShow = $this->actingAs($toolman)->get(route('toolman.peminjaman.show', $pinjamanSelesai->id));
        $selesaiShow->assertSee('Cetak Bukti Pinjam');
        $selesaiShow->assertSee('Cetak Bukti Pengembalian');

        $selesaiPrintPinjam = $this->actingAs($toolman)->get(route('toolman.pengembalian.print-pinjam', $pinjamanSelesai->id));
        $selesaiPrintPinjam->assertStatus(200);

        $selesaiPrintKembali = $this->actingAs($toolman)->get(route('toolman.pengembalian.print-kembali', $pinjamanSelesai->id));
        $selesaiPrintKembali->assertStatus(200);
    }
}
