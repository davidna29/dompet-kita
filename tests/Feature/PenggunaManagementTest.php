<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PenggunaManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_regular_member_account(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'pencatat' => 'David NA']);

        $response = $this->actingAs($admin)->post(route('pengaturan.pengguna.store'), [
            'name' => 'Nurul Azizah',
            'email' => 'NURUL@KELUARGA.TEST',
            'password' => 'DompetKita!2026',
            'password_confirmation' => 'DompetKita!2026',
        ]);

        $response->assertRedirect(route('pengaturan'))
            ->assertSessionHas('sukses');

        $anggota = User::query()->where('email', 'nurul@keluarga.test')->firstOrFail();
        $this->assertSame('Nurul Azizah', $anggota->pencatat);
        $this->assertFalse($anggota->is_admin);
        $this->assertTrue(Hash::check('DompetKita!2026', $anggota->password));
        $this->assertSame(2, User::query()->count());
    }

    public function test_regular_member_cannot_create_users_or_see_user_management(): void
    {
        $anggota = User::factory()->create(['is_admin' => false]);

        $this->actingAs($anggota)
            ->get(route('pengaturan'))
            ->assertOk()
            ->assertDontSee('Tambah anggota');

        $this->post(route('pengaturan.pengguna.store'), [
            'name' => 'Anggota Baru',
            'email' => 'baru@keluarga.test',
            'password' => 'DompetKita!2026',
            'password_confirmation' => 'DompetKita!2026',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'baru@keluarga.test']);
    }

    public function test_admin_must_use_unique_email_and_confirm_password(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['email' => 'sudah@keluarga.test']);

        $this->actingAs($admin)
            ->from(route('pengaturan'))
            ->post(route('pengaturan.pengguna.store'), [
                'name' => 'Anggota Baru',
                'email' => 'SUDAH@KELUARGA.TEST',
                'password' => 'DompetKita!2026',
                'password_confirmation' => 'kata-sandi-lain',
            ])
            ->assertRedirect(route('pengaturan'))
            ->assertSessionHasErrors(['email', 'password']);
    }
}
