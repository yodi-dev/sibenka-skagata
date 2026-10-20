<?php

namespace Tests\Feature;

use App\Models\Bengkel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanduanTest extends TestCase
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

    public function test_guest_is_redirected_to_login_when_accessing_any_guide_route(): void
    {
        $this->get(route('panduan.index'))->assertRedirect(route('login'));
        $this->get(route('peminjam.panduan'))->assertRedirect(route('login'));
        $this->get(route('toolman.panduan'))->assertRedirect(route('login'));
        $this->get(route('superadmin.panduan'))->assertRedirect(route('login'));
    }

    public function test_peminjam_is_redirected_to_peminjam_guide_from_dispatcher(): void
    {
        $bengkel = $this->createBengkel();
        $peminjam = $this->createUser([
            'role' => 'peminjam',
            'bengkel_id' => $bengkel->id,
            'jenis_peminjam' => 'siswa',
        ]);

        $response = $this->actingAs($peminjam)->get(route('panduan.index'));
        $response->assertRedirect(route('peminjam.panduan'));

        $guideResponse = $this->actingAs($peminjam)->get(route('peminjam.panduan'));
        $guideResponse->assertOk();
        $guideResponse->assertSee('Buku Panduan & SOP Peminjam');
        $guideResponse->assertSee('Booking');
        $guideResponse->assertSee('Tata Tertib');
    }

    public function test_peminjam_is_forbidden_from_accessing_admin_guides(): void
    {
        $bengkel = $this->createBengkel();
        $peminjam = $this->createUser([
            'role' => 'peminjam',
            'bengkel_id' => $bengkel->id,
            'jenis_peminjam' => 'siswa',
        ]);

        $this->actingAs($peminjam)->get(route('toolman.panduan'))->assertForbidden();
        $this->actingAs($peminjam)->get(route('superadmin.panduan'))->assertForbidden();
    }

    public function test_toolman_is_redirected_to_toolman_guide_from_dispatcher(): void
    {
        $bengkel = $this->createBengkel('TAV', 'Teknik Audio Video');
        $toolman = $this->createUser([
            'role' => 'toolman',
            'bengkel_id' => $bengkel->id,
        ]);

        $response = $this->actingAs($toolman)->get(route('panduan.index'));
        $response->assertRedirect(route('toolman.panduan'));

        $guideResponse = $this->actingAs($toolman)->get(route('toolman.panduan'));
        $guideResponse->assertOk();
        $guideResponse->assertSee('Panduan Kerja Toolman');
        $guideResponse->assertSee('Persetujuan 2-Tahap');
        $guideResponse->assertSee('Cek Fisik');
    }

    public function test_toolman_is_forbidden_from_accessing_superadmin_guide(): void
    {
        $bengkel = $this->createBengkel();
        $toolman = $this->createUser([
            'role' => 'toolman',
            'bengkel_id' => $bengkel->id,
        ]);

        $this->actingAs($toolman)->get(route('superadmin.panduan'))->assertForbidden();
    }

    public function test_superadmin_is_redirected_to_superadmin_guide_from_dispatcher(): void
    {
        $waka = $this->createUser([
            'role' => 'waka',
        ]);

        $response = $this->actingAs($waka)->get(route('panduan.index'));
        $response->assertRedirect(route('superadmin.panduan'));

        $guideResponse = $this->actingAs($waka)->get(route('superadmin.panduan'));
        $guideResponse->assertOk();
        $guideResponse->assertSee('Panduan Waka Sarpras');
        $guideResponse->assertSee('Pengawasan Sarpras');
        $guideResponse->assertSee('Verifikasi');
    }

    public function test_dashboard_and_profile_views_render_guide_links_properly(): void
    {
        $bengkel = $this->createBengkel();
        $peminjam = $this->createUser([
            'role' => 'peminjam',
            'bengkel_id' => $bengkel->id,
            'jenis_peminjam' => 'siswa',
        ]);

        $dashboardResponse = $this->actingAs($peminjam)->get(route('peminjam.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee(route('peminjam.panduan'));

        $profileResponse = $this->actingAs($peminjam)->get(route('profile.edit'));
        $profileResponse->assertOk();
        $profileResponse->assertSee(route('panduan.index'));
    }
}
