<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login:127.0.0.1');
    }

    public function test_login_berhasil_masuk_ke_beranda(): void
    {
        User::factory()->create([
            'email' => 'david@example.test',
            'pencatat' => 'David NA',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => 'david@example.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('beranda'));
        $this->assertAuthenticated();
    }

    public function test_percobaan_keenam_ditolak_setelah_lima_gagal(): void
    {
        User::factory()->create(['email' => 'david@example.test']);

        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', [
                'email' => 'david@example.test',
                'password' => 'salah',
            ])->assertStatus(302)->assertSessionHasErrors('email');
        }

        $this->post('/login', [
            'email' => 'david@example.test',
            'password' => 'salah',
        ])->assertStatus(302)->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_halaman_terlindungi_mengalihkan_ke_login(): void
    {
        $this->get('/beranda')->assertRedirect(route('login'));
        $this->get('/transaksi')->assertRedirect(route('login'));
    }
}
