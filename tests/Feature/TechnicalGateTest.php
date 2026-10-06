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

    /**
     * 8. Peminjam dapat membuat pengajuan peminjaman terjadwal hingga maksimal 14 hari ke depan.
     */
    public function test_peminjam_can_advance_book_up_to_14_days_ahead(): void
    {
        $bengkel = $this->createBengkel('B-ADV', 'Bengkel Advance Booking');
        $peminjam = $this->createUser([
            'role' => 'peminjam',
            'bengkel_id' => $bengkel->id,
            'status' => 'aktif',
        ]);

        $barang = Barang::create([
            'bengkel_id' => $bengkel->id,
            'kode_barang' => 'ADV-001',
            'nama' => 'Multimeter Digital Advance',
            'jenis_barang' => 'inventaris',
            'satuan' => 'Unit',
            'stok_total' => 5,
            'stok_tersedia' => 5,
            'stok_dipinjam' => 0,
            'stok_rusak' => 0,
            'minimum_stok' => 1,
        ]);

        $jadwalPinjam = now()->addDays(7)->setTime(10, 0);
        $batasKembali = now()->addDays(7)->setTime(16, 0);

        $response = $this->actingAs($peminjam)->post(route('peminjam.pengajuan.store'), [
            'bengkel_id' => $bengkel->id,
            'items' => json_encode([
                ['id' => $barang->id, 'qty' => 2]
            ]),
            'tanggal_pinjam' => $jadwalPinjam->format('Y-m-d H:i:s'),
            'batas_kembali' => $batasKembali->format('Y-m-d H:i:s'),
            'keperluan' => 'Praktikum Pengukuran Listrik Minggu Depan',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('peminjam.tiket.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('peminjamans', [
            'user_id' => $peminjam->id,
            'bengkel_id' => $bengkel->id,
            'status' => 'pending',
            'keperluan' => 'Praktikum Pengukuran Listrik Minggu Depan',
        ]);

        $peminjaman = Peminjaman::where('user_id', $peminjam->id)->first();
        $this->assertEquals($jadwalPinjam->format('Y-m-d H:i:00'), \Carbon\Carbon::parse($peminjaman->tanggal_pinjam)->format('Y-m-d H:i:00'));
    }

    /**
     * 9. Validasi menolak pengajuan jadwal jika melebihi 14 hari ke depan atau di masa lampau.
     */
    public function test_peminjam_cannot_advance_book_more_than_14_days_or_in_the_past(): void
    {
        $bengkel = $this->createBengkel('B-LIM', 'Bengkel Batas Booking');
        $peminjam = $this->createUser([
            'role' => 'peminjam',
            'bengkel_id' => $bengkel->id,
            'status' => 'aktif',
        ]);

        $barang = Barang::create([
            'bengkel_id' => $bengkel->id,
            'kode_barang' => 'LIM-001',
            'nama' => 'Mesin Bor Duduk',
            'jenis_barang' => 'inventaris',
            'satuan' => 'Unit',
            'stok_total' => 3,
            'stok_tersedia' => 3,
            'stok_dipinjam' => 0,
            'stok_rusak' => 0,
            'minimum_stok' => 1,
        ]);

        // A. Pengajuan lebih dari 14 hari ke depan (hari ke-16) harus ditolak
        $jadwalTerlaluJauh = now()->addDays(16)->setTime(10, 0);
        $responseJauh = $this->actingAs($peminjam)->post(route('peminjam.pengajuan.store'), [
            'bengkel_id' => $bengkel->id,
            'items' => json_encode([['id' => $barang->id, 'qty' => 1]]),
            'tanggal_pinjam' => $jadwalTerlaluJauh->format('Y-m-d H:i:s'),
            'batas_kembali' => $jadwalTerlaluJauh->copy()->addHours(4)->format('Y-m-d H:i:s'),
            'keperluan' => 'Peminjaman terlalu jauh hari',
        ]);
        $responseJauh->assertSessionHasErrors('tanggal_pinjam');

        // B. Pengajuan di masa lampau (kemarin) harus ditolak
        $jadwalLampau = now()->subDays(1)->setTime(10, 0);
        $responseLampau = $this->actingAs($peminjam)->post(route('peminjam.pengajuan.store'), [
            'bengkel_id' => $bengkel->id,
            'items' => json_encode([['id' => $barang->id, 'qty' => 1]]),
            'tanggal_pinjam' => $jadwalLampau->format('Y-m-d H:i:s'),
            'batas_kembali' => now()->format('Y-m-d H:i:s'),
            'keperluan' => 'Peminjaman di masa lampau',
        ]);
        $responseLampau->assertSessionHasErrors('tanggal_pinjam');
    }

    /**
     * 10. Dua tahap persetujuan:
     *     - Tahap 1: "Setujui Jadwal" (status 'disetujui', stok_reserved terpotong dari stok_bebas, stok_tersedia fisik utuh).
     *     - Tahap 2: "Serahkan Barang" (status 'active', stok_tersedia fisik baru berkurang, mutasi dicatat).
     */
    public function test_two_stage_approval_schedule_reservation_and_handover(): void
    {
        $bengkel = $this->createBengkel('B-2STG', 'Bengkel Dua Tahap');
        $toolman = $this->createUser([
            'role' => 'toolman',
            'bengkel_id' => $bengkel->id,
            'status' => 'aktif',
        ]);
        $peminjam1 = $this->createUser([
            'role' => 'peminjam',
            'bengkel_id' => $bengkel->id,
            'status' => 'aktif',
        ]);
        $peminjam2 = $this->createUser([
            'role' => 'peminjam',
            'bengkel_id' => $bengkel->id,
            'status' => 'aktif',
        ]);

        $barang = Barang::create([
            'bengkel_id' => $bengkel->id,
            'kode_barang' => 'STG-001',
            'nama' => 'Tang Crimping RJ45 Pro',
            'jenis_barang' => 'inventaris',
            'satuan' => 'Pcs',
            'stok_total' => 5,
            'stok_tersedia' => 5,
            'stok_dipinjam' => 0,
            'stok_rusak' => 0,
            'minimum_stok' => 1,
        ]);

        // Peminjam 1 mengajukan pinjam 3 unit
        $this->actingAs($peminjam1)->post(route('peminjam.pengajuan.store'), [
            'bengkel_id' => $bengkel->id,
            'items' => json_encode([['id' => $barang->id, 'qty' => 3]]),
            'tanggal_pinjam' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'batas_kembali' => now()->addDays(3)->addHours(4)->format('Y-m-d H:i:s'),
            'keperluan' => 'Praktikum Jaringan Peminjam 1',
        ]);

        $tiket1 = Peminjaman::where('user_id', $peminjam1->id)->first();
        $this->assertEquals('pending', $tiket1->status);

        // --- TAHAP 1: Toolman menyetujui jadwal ---
        $responseSetujui = $this->actingAs($toolman)->post(route('toolman.peminjaman.setujui-jadwal', $tiket1->id));
        $responseSetujui->assertRedirect(route('toolman.peminjaman.index', ['tab' => 'pending']));
        $responseSetujui->assertSessionHas('success');

        $tiket1->refresh();
        $barang->refresh();
        $this->assertEquals('disetujui', $tiket1->status);
        $this->assertEquals(5, $barang->stok_tersedia); // Stok fisik BELUM berkurang!
        $this->assertEquals(3, $barang->stok_reserved); // Kuota ter-reserve 3
        $this->assertEquals(2, $barang->stok_bebas);    // Sisa kuota bebas tinggal 2 (5 - 3)

        // Peminjam 2 mencoba mengajukan pinjam 3 unit (harus GAGAL karena sisa kuota bebas hanya 2)
        $responsePinjam2 = $this->actingAs($peminjam2)->post(route('peminjam.pengajuan.store'), [
            'bengkel_id' => $bengkel->id,
            'items' => json_encode([['id' => $barang->id, 'qty' => 3]]),
            'tanggal_pinjam' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'batas_kembali' => now()->addDays(1)->addHours(2)->format('Y-m-d H:i:s'),
            'keperluan' => 'Mencoba serobot alat yang ter-booking',
        ]);
        $responsePinjam2->assertSessionHas('error');
        $this->assertStringContainsString('tidak mencukupi untuk peminjaman baru', session('error'));

        // Peminjam 2 mengajukan pinjam 2 unit (sesuai sisa kuota bebas) -> BERHASIL
        $responsePinjam2Pas = $this->actingAs($peminjam2)->post(route('peminjam.pengajuan.store'), [
            'bengkel_id' => $bengkel->id,
            'items' => json_encode([['id' => $barang->id, 'qty' => 2]]),
            'tanggal_pinjam' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'batas_kembali' => now()->addDays(1)->addHours(2)->format('Y-m-d H:i:s'),
            'keperluan' => 'Meminjam sisa kuota bebas',
        ]);
        $responsePinjam2Pas->assertSessionHas('success');

        // --- TAHAP 2: Pada hari-H, Toolman menyerahkan barang fisik ---
        $responseApprove = $this->actingAs($toolman)->post(route('toolman.peminjaman.approve', $tiket1->id));
        $responseApprove->assertRedirect(route('toolman.peminjaman.index', ['tab' => 'riwayat']));
        $responseApprove->assertSessionHas('success');

        $tiket1->refresh();
        $barang->refresh();
        $this->assertEquals('active', $tiket1->status);
        $this->assertEquals(2, $barang->stok_tersedia); // Stok fisik BARU berkurang (5 - 3 = 2)
        $this->assertEquals(3, $barang->stok_dipinjam);
        $this->assertEquals(0, $barang->stok_reserved); // Tiket bukan lagi 'disetujui' sehingga reserved kembali 0
        $this->assertEquals(2, $barang->stok_bebas);    // Stok bebas konsisten = 2 - 0 = 2

        // Pastikan StockMovement tercatat saat penyerahan fisik
        $this->assertDatabaseHas('stock_movements', [
            'barang_id' => $barang->id,
            'jenis' => 'peminjaman',
            'jumlah' => 3,
        ]);
    }

    /**
     * 11. Toolman dapat menolak/membatalkan tiket yang berstatus 'disetujui', dan kuota reserved otomatis dilepas.
     */
    public function test_toolman_can_reject_scheduled_loan_and_release_reserved_quota(): void
    {
        $bengkel = $this->createBengkel('B-REJ', 'Bengkel Batal Jadwal');
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

        $barang = Barang::create([
            'bengkel_id' => $bengkel->id,
            'kode_barang' => 'REJ-001',
            'nama' => 'Proyektor Mini Praktikum',
            'jenis_barang' => 'inventaris',
            'satuan' => 'Unit',
            'stok_total' => 2,
            'stok_tersedia' => 2,
            'stok_dipinjam' => 0,
            'stok_rusak' => 0,
            'minimum_stok' => 1,
        ]);

        // Ajukan dan setujui jadwal
        $this->actingAs($peminjam)->post(route('peminjam.pengajuan.store'), [
            'bengkel_id' => $bengkel->id,
            'items' => json_encode([['id' => $barang->id, 'qty' => 2]]),
            'tanggal_pinjam' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'batas_kembali' => now()->addDays(5)->addHours(3)->format('Y-m-d H:i:s'),
            'keperluan' => 'Presentasi Proyek Akhir',
        ]);

        $tiket = Peminjaman::where('user_id', $peminjam->id)->first();
        $this->actingAs($toolman)->post(route('toolman.peminjaman.setujui-jadwal', $tiket->id));

        $barang->refresh();
        $this->assertEquals(2, $barang->stok_reserved);
        $this->assertEquals(0, $barang->stok_bebas);

        // Toolman menolak/membatalkan pengajuan terjadwal
        $responseReject = $this->actingAs($toolman)->post(route('toolman.peminjaman.reject', $tiket->id), [
            'alasan_penolakan' => 'Peminjam mengonfirmasi pembatalan peminjaman.',
        ]);
        $responseReject->assertSessionHas('success');

        $tiket->refresh();
        $barang->refresh();
        $this->assertEquals('ditolak', $tiket->status);
        $this->assertEquals(0, $barang->stok_reserved); // Kuota reserved langsung dilepas!
        $this->assertEquals(2, $barang->stok_bebas);    // Kuota bebas kembali utuh 2!
        $this->assertEquals(2, $barang->stok_tersedia); // Stok fisik tetap 2
    }

    /**
     * 11. Halaman cek fisik & pengembalian terbagi dalam 3 tab:
     *     - Menunggu Cek Fisik (default): antrean pengembalian dari peminjam
     *     - Sedang Dipinjam: monitoring alat beredar dengan proteksi tombol cek fisik langsung
     *     - Riwayat: arsip selesai
     */
    public function test_toolman_pengembalian_tabs_and_physical_check_protection(): void
    {
        $bengkel = $this->createBengkel('B-TAB', 'Bengkel Uji Tab');
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

        $barang = Barang::create([
            'bengkel_id' => $bengkel->id,
            'kode_barang' => 'TAB-001',
            'nama' => 'Mesin Bor Duduk',
            'jenis_barang' => 'inventaris',
            'satuan' => 'Unit',
            'stok_total' => 5,
            'stok_tersedia' => 2,
            'stok_dipinjam' => 3,
            'stok_rusak' => 0,
            'minimum_stok' => 1,
            'kondisi_baik' => 5,
            'kondisi_rusak' => 0,
            'kondisi_hilang' => 0,
        ]);

        // 1. Tiket Sedang Dipinjam (active)
        $tiketActive = Peminjaman::create([
            'user_id' => $peminjam->id,
            'bengkel_id' => $bengkel->id,
            'tanggal_pinjam' => now()->subHours(2),
            'batas_kembali' => now()->addHours(2),
            'status' => 'active',
            'keperluan' => 'Praktikum Bor',
            'diproses_oleh' => $toolman->id,
            'diproses_pada' => now()->subHours(2),
        ]);
        $tiketActive->detailPeminjamans()->create([
            'barang_id' => $barang->id,
            'jumlah' => 1,
        ]);

        // 2. Tiket Menunggu Pengecekan
        $tiketMenunggu = Peminjaman::create([
            'user_id' => $peminjam->id,
            'bengkel_id' => $bengkel->id,
            'tanggal_pinjam' => now()->subHours(4),
            'batas_kembali' => now()->subHour(),
            'status' => 'menunggu_pengecekan',
            'keperluan' => 'Praktikum Selesai Cek',
            'diproses_oleh' => $toolman->id,
            'diproses_pada' => now()->subHours(4),
        ]);
        $tiketMenunggu->detailPeminjamans()->create([
            'barang_id' => $barang->id,
            'jumlah' => 1,
        ]);

        // 3. Tiket Selesai
        $tiketSelesai = Peminjaman::create([
            'user_id' => $peminjam->id,
            'bengkel_id' => $bengkel->id,
            'tanggal_pinjam' => now()->subDays(2),
            'batas_kembali' => now()->subDay(),
            'status' => 'selesai',
            'keperluan' => 'Praktikum Kemarin',
            'diproses_oleh' => $toolman->id,
            'diproses_pada' => now()->subDays(2),
        ]);
        $tiketSelesai->detailPeminjamans()->create([
            'barang_id' => $barang->id,
            'jumlah' => 1,
            'jumlah_baik' => 1,
        ]);

        $trxActive = '#TRX-' . str_pad($tiketActive->id, 4, '0', STR_PAD_LEFT);
        $trxMenunggu = '#TRX-' . str_pad($tiketMenunggu->id, 4, '0', STR_PAD_LEFT);
        $trxSelesai = '#TRX-' . str_pad($tiketSelesai->id, 4, '0', STR_PAD_LEFT);

        // Buka halaman default (tanpa tab param -> default: menunggu_pengecekan)
        $resDefault = $this->actingAs($toolman)->get(route('toolman.pengembalian.index'));
        $resDefault->assertStatus(200);
        $resDefault->assertSee('Menunggu Cek Fisik');
        $resDefault->assertSee($trxMenunggu);
        $resDefault->assertSee('Cek Fisik &amp; Konfirmasi Kembali', false);
        $resDefault->assertDontSee($trxActive); // Tiket active tidak muncul di tab default antrean!

        // Buka tab sedang_dipinjam
        $resSedang = $this->actingAs($toolman)->get(route('toolman.pengembalian.index', ['tab' => 'sedang_dipinjam']));
        $resSedang->assertStatus(200);
        $resSedang->assertSee($trxActive);
        $resSedang->assertSee('Sedang Digunakan');
        $resSedang->assertSee('Terima &amp; Cek Fisik Langsung', false);
        $resSedang->assertDontSee($trxMenunggu);

        // Buka tab riwayat
        $resRiwayat = $this->actingAs($toolman)->get(route('toolman.pengembalian.index', ['tab' => 'riwayat']));
        $resRiwayat->assertStatus(200);
        $resRiwayat->assertSee($trxSelesai);
        $resRiwayat->assertSee('Pengecekan Selesai');
        $resRiwayat->assertDontSee($trxMenunggu);

        // Buka form check tiket menunggu pengecekan
        $resCheckView = $this->actingAs($toolman)->get(route('toolman.pengembalian.check', $tiketMenunggu->id));
        $resCheckView->assertStatus(200);
        $resCheckView->assertSee('Menunggu Cek Fisik');

        // Buka form check tiket active
        $resCheckActiveView = $this->actingAs($toolman)->get(route('toolman.pengembalian.check', $tiketActive->id));
        $resCheckActiveView->assertStatus(200);
        $resCheckActiveView->assertSee('Sedang Dipinjam');
    }

    /**
     * 12. Tiket disetujui_jadwal muncul di antrean kerja Toolman & dashboard peminjam,
     *     serta perintah app:reset-demo menormalkan stok_reserved ke 0.
     */
    public function test_disetujui_jadwal_visibility_in_queues_and_reset_command(): void
    {
        $bengkel = $this->createBengkel('B-SYNC', 'Bengkel Uji Sinkron');
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

        $barang = Barang::create([
            'bengkel_id' => $bengkel->id,
            'kode_barang' => 'SYNC-001',
            'nama' => 'Mesin Frais Horizontal',
            'jenis_barang' => 'inventaris',
            'satuan' => 'Unit',
            'stok_total' => 4,
            'stok_tersedia' => 4,
            'stok_dipinjam' => 0,
            'stok_rusak' => 0,
            'minimum_stok' => 1,
            'kondisi_baik' => 4,
            'kondisi_rusak' => 0,
            'kondisi_hilang' => 0,
        ]);

        $tiketJadwal = Peminjaman::create([
            'user_id' => $peminjam->id,
            'bengkel_id' => $bengkel->id,
            'tanggal_pinjam' => now()->addDays(3),
            'batas_kembali' => now()->addDays(3)->addHours(4),
            'status' => 'disetujui',
            'keperluan' => 'Praktik Bubut Terjadwal',
            'diproses_oleh' => $toolman->id,
            'diproses_pada' => now(),
        ]);
        $tiketJadwal->detailPeminjamans()->create([
            'barang_id' => $barang->id,
            'jumlah' => 2,
        ]);

        $barang->refresh();
        $this->assertEquals(2, $barang->stok_reserved);
        $this->assertEquals(2, $barang->stok_bebas);

        $trxJadwal = '#PINJAM-' . str_pad($tiketJadwal->id, 4, '0', STR_PAD_LEFT);

        // 1. Toolman Peminjaman Index (Tab Pending) memuat tiket disetujui jadwal
        $resToolmanIndex = $this->actingAs($toolman)->get(route('toolman.peminjaman.index'));
        $resToolmanIndex->assertStatus(200);
        $resToolmanIndex->assertSee($trxJadwal);
        $resToolmanIndex->assertSee('Jadwal Disetujui');
        $resToolmanIndex->assertSee('Serahkan Barang');

        // 2. Toolman Dashboard memuat tiket disetujui jadwal pada antrean
        $resToolmanDash = $this->actingAs($toolman)->get(route('toolman.dashboard'));
        $resToolmanDash->assertStatus(200);
        $resToolmanDash->assertSee($trxJadwal);

        // 3. Peminjam Dashboard memuat tiket disetujui jadwal pada jadwal ambil
        $resPeminjamDash = $this->actingAs($peminjam)->get(route('peminjam.dashboard'));
        $resPeminjamDash->assertStatus(200);
        $resPeminjamDash->assertSee('Jadwal Disetujui');
        $resPeminjamDash->assertSee('Jadwal Ambil:');

        // 4. Peminjam Tiket Filter Disetujui memuat tiket disetujui jadwal
        $resPeminjamTiket = $this->actingAs($peminjam)->get(route('peminjam.tiket.index', ['status' => 'disetujui']));
        $resPeminjamTiket->assertStatus(200);
        $resPeminjamTiket->assertSee('#TRX-' . str_pad($tiketJadwal->id, 4, '0', STR_PAD_LEFT));

        // 5. Verifikasi app:reset-demo membersihkan transaksi dan menormalkan stok_reserved
        $this->artisan('app:reset-demo --force')->assertSuccessful();
        $barang->refresh();
        $this->assertEquals(0, $barang->stok_reserved);
        $this->assertEquals($barang->stok_tersedia, $barang->stok_bebas);
    }
}

