<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use App\Models\Kantong;
use App\Models\Transaksi;
use App\Models\TransaksiBerulang;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    public function index(): View
    {
        return view('pengaturan', [
            'jumlahTransaksi' => Transaksi::query()->count(),
            'jumlahKantong' => Kantong::query()->count(),
            'jumlahJadwal' => TransaksiBerulang::query()->aktif()->count(),
            'jumlahAnggaran' => Anggaran::query()->where('periode', now()->format('Y-m'))->count(),
            'adminTokenSiap' => filled(config('services.hermes.token')),
            'baseUrl' => url('/api'),
            'demoMode' => (bool) config('app.demo_mode'),
        ]);
    }
}
