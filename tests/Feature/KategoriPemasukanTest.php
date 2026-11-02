<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TransaksiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KategoriPemasukanTest extends TestCase
{
    use RefreshDatabase;

    public function test_income_form_shows_income_suggestions_and_keeps_mutasi_outgoing_only(): void
    {
        $user = User::factory()->create(['pencatat' => 'David NA']);

        $form = Livewire::actingAs($user)
            ->test('transaksi-form')
            ->set('jenis', 'masuk')
            ->assertSee('Gaji')
            ->assertSee('Honor')
            ->assertSee('Upah')
            ->assertSee('Bonus')
            ->assertSee('Hadiah')
            ->assertDontSeeHtml('chip--mutasi');

        $form->set('kategori', 'Mutasi')
            ->assertSet('jenis', 'keluar')
            ->assertSeeHtml('chip--mutasi');
    }

    public function test_ui_rejects_a_new_transaction_with_an_unregistered_kantong(): void
    {
        $user = User::factory()->create(['pencatat' => 'David NA']);

        Livewire::actingAs($user)
            ->test('transaksi-form')
            ->set('jenis', 'keluar')
            ->set('kategori', 'Makanan')
            ->set('jumlah', '10000')
            ->set('metode', 'Shoppe')
            ->call('simpan')
            ->assertHasErrors(['metode']);
    }

    public function test_existing_transaction_keeps_its_legacy_kantong_until_reassigned(): void
    {
        $user = User::factory()->create(['pencatat' => 'David NA']);
        $transaksi = app(TransaksiService::class)->buat([
            'tanggal' => today()->toDateString(),
            'jenis' => 'keluar',
            'kategori' => 'Makanan',
            'jumlah' => 10_000,
            'metode' => 'Bank Lama',
        ]);

        Livewire::actingAs($user)
            ->test('transaksi-form', ['transaksiId' => $transaksi->id])
            ->set('keterangan', 'Catatan diperbarui')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertRedirect(route('transaksi.index'));

        $this->assertDatabaseHas('transaksi', [
            'id' => $transaksi->id,
            'metode' => 'Bank Lama',
            'keterangan' => 'Catatan diperbarui',
        ]);
    }
}
