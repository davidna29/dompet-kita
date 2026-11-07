<?php

namespace App\Http\Controllers;

use App\Models\HutangPiutang;
use App\Models\Kantong;
use App\Models\Transaksi;
use App\Services\HutangService;
use App\Support\JumlahParser;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HutangController extends Controller
{
    public function index(Request $request): View
    {
        $jenis = $request->query('jenis');
        if (! in_array($jenis, HutangService::JENIS, true)) {
            $jenis = null;
        }

        $status = $request->query('status');
        if (! in_array($status, ['belum', 'lunas'], true)) {
            $status = null;
        }

        $query = HutangPiutang::query();

        if ($jenis !== null) {
            $query->where('jenis', $jenis);
        }
        if ($status !== null) {
            $query->where('status', $status);
        }

        $daftar = $query
            ->orderByRaw("CASE WHEN status = 'belum' THEN 0 ELSE 1 END")
            ->orderByRaw('CASE WHEN jatuh_tempo IS NULL THEN 1 ELSE 0 END')
            ->orderBy('jatuh_tempo')
            ->orderByDesc('id')
            ->get();

        $aktif = HutangPiutang::query()->where('status', 'belum');

        return view('hutang.index', [
            'daftar' => $daftar,
            'jenis' => $jenis,
            'status' => $status,
            'totalHutang' => (int) (clone $aktif)->where('jenis', 'hutang')->sum('sisa'),
            'totalPiutang' => (int) (clone $aktif)->where('jenis', 'piutang')->sum('sisa'),
            'jatuhTempoDekat' => (clone $aktif)
                ->whereNotNull('jatuh_tempo')
                ->where('jatuh_tempo', '<=', Carbon::today()->addDays(14)->toDateString())
                ->count(),
        ]);
    }

    public function create(): View
    {
        return view('hutang.form');
    }

    public function store(Request $request, HutangService $service): RedirectResponse
    {
        $data = $request->validate([
            'pihak' => ['required', 'string', 'max:50'],
            'jenis' => ['required', 'in:hutang,piutang'],
            'nominal' => ['required', 'string', 'max:30'],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'jatuh_tempo' => ['nullable', 'date_format:Y-m-d'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
        ]);

        $nominal = JumlahParser::parse($data['nominal']);
        if ($nominal === null) {
            return back()->withErrors(['nominal' => 'Nominal harus bilangan bulat positif.'])->withInput();
        }

        $data['nominal'] = $nominal;
        $hutang = $service->buat($data);

        return redirect()
            ->route('hutang.show', $hutang)
            ->with('sukses', 'Catatan berhasil dibuat. Transaksi baru tercatat saat pembayaran dikonfirmasi.');
    }

    public function show(HutangPiutang $hutang): View
    {
        $pembayaran = Transaksi::query()
            ->where('client_key', 'like', 'HUTANG-'.$hutang->id.'-%')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        return view('hutang.show', [
            'hutang' => $hutang,
            'pembayaran' => $pembayaran,
            'kantong' => Kantong::query()->orderBy('urutan')->orderBy('nama')->get(['nama']),
        ]);
    }

    public function bayar(Request $request, HutangPiutang $hutang, HutangService $service): RedirectResponse
    {
        $data = $request->validate([
            'jumlah' => ['required', 'string', 'max:30'],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'metode' => ['nullable', 'string', 'max:30'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
        ]);

        $jumlah = JumlahParser::parse($data['jumlah']);
        if ($jumlah === null) {
            return back()->withErrors(['jumlah' => 'Jumlah bayar harus bilangan bulat positif.'])->withInput();
        }

        $data['jumlah'] = $jumlah;

        try {
            [$hutang] = $service->bayar($hutang, $data, 'ui', $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $pesan = $hutang->status === 'lunas'
            ? 'Pembayaran tercatat. Catatan ini sudah lunas.'
            : 'Pembayaran tercatat. Sisa diperbarui.';

        return redirect()
            ->route('hutang.show', $hutang)
            ->with('sukses', $pesan);
    }

    public function destroy(HutangPiutang $hutang, HutangService $service): RedirectResponse
    {
        $service->hapus($hutang);

        return redirect()
            ->route('hutang.index')
            ->with('sukses', 'Catatan diarsipkan. Transaksi pembayaran yang sudah tercatat tetap tersimpan.');
    }
}
