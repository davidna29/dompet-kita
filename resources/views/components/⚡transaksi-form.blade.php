<?php

use App\Models\Kantong;
use App\Models\KategoriMeta;
use App\Models\Transaksi;
use App\Services\TransaksiService;
use App\Support\JumlahParser;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public ?int $transaksiId = null;

    public string $tanggal = '';

    public string $jenis = 'keluar';

    public string $kategori = '';

    public string $jumlah = '';

    public string $keterangan = '';

    public string $metode = '';

    public string $kantongTujuan = '';

    public string $pencatat = '';

    public function mount(?int $transaksiId = null): void
    {
        $this->transaksiId = $transaksiId;
        $this->tanggal = today()->toDateString();
        $this->pencatat = auth()->user()?->pencatat ?? '';

        if ($transaksiId !== null) {
            $transaksi = Transaksi::query()->findOrFail($transaksiId);

            $this->tanggal = $transaksi->tanggal->format('Y-m-d');
            $this->jenis = $transaksi->jenis;
            $this->kategori = $transaksi->kategori;
            $this->jumlah = angka_input($transaksi->jumlah);
            $this->keterangan = $transaksi->keterangan ?? '';
            $this->metode = $transaksi->metode ?? '';
            $this->kantongTujuan = $transaksi->kantong_tujuan ?? '';
            $this->pencatat = $transaksi->pencatat;
        }
    }

    public function simpan(): void
    {
        $this->tanggal = trim($this->tanggal);
        $this->kategori = trim($this->kategori);

        $this->validate([
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jenis' => ['required', 'in:masuk,keluar'],
            'kategori' => ['required', 'string', 'max:50'],
            'jumlah' => ['required', 'string', 'max:30'],
            'metode' => ['nullable', 'string', 'max:30'],
            'kantongTujuan' => ['nullable', 'string', 'max:30'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ], [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date_format' => 'Format tanggal tidak valid.',
            'kategori.required' => 'Kategori wajib diisi.',
            'kategori.max' => 'Kategori maksimal 50 karakter.',
            'jumlah.required' => 'Jumlah wajib diisi.',
            'jumlah.max' => 'Jumlah terlalu panjang.',
        ]);

        $jumlah = JumlahParser::parse($this->jumlah);
        if ($jumlah === null) {
            $this->addError('jumlah', 'Jumlah harus bilangan bulat positif. Contoh: 35.000 atau 35rb.');

            return;
        }

        $isMutasi = mb_strtolower($this->kategori) === 'mutasi';
        if ($isMutasi) {
            if ($this->metode === '' || $this->kantongTujuan === '') {
                $this->addError('kantongTujuan', 'Mutasi wajib memilih kantong asal dan tujuan.');

                return;
            }
            if ($this->metode === $this->kantongTujuan) {
                $this->addError('kantongTujuan', 'Kantong asal dan tujuan harus berbeda.');

                return;
            }
        }

        $data = [
            'tanggal' => $this->tanggal,
            'jenis' => $this->jenis,
            'kategori' => $this->kategori,
            'jumlah' => $jumlah,
            'keterangan' => $this->keterangan,
            'metode' => $this->metode ?: null,
            'kantong_tujuan' => $isMutasi ? $this->kantongTujuan : null,
        ];

        $service = app(TransaksiService::class);

        try {
            if ($this->transaksiId !== null) {
                $transaksi = Transaksi::query()->findOrFail($this->transaksiId);
                $service->perbarui($transaksi, $data, auth()->user(), 'ui');
                $pesan = 'Perubahan transaksi disimpan.';
            } else {
                $transaksi = $service->buat($data, 'ui', auth()->user());
                $pesan = 'Transaksi baru ditempel ke papan.';
            }
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field === 'kantong_tujuan' ? 'kantongTujuan' : $field, $messages[0]);
            }

            return;
        }

        session()->flash('sukses', $pesan);
        session()->flash('transaksi_baru_id', $transaksi->id);

        $this->redirectRoute('transaksi.index', navigate: true);
    }

    public function updatedKategori(): void
    {
        if (mb_strtolower(trim($this->kategori)) === 'mutasi') {
            $this->kategori = 'Mutasi';
            $this->jenis = 'keluar';
        } else {
            $this->kantongTujuan = '';
        }
    }

    #[Computed]
    public function mutasi(): bool
    {
        return mb_strtolower(trim($this->kategori)) === 'mutasi';
    }

    #[Computed]
    public function kategoriOptions(): Collection
    {
        $dipakai = Transaksi::query()
            ->select('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        $meta = KategoriMeta::query()->orderBy('urutan')->pluck('kategori');

        return $dipakai->merge($meta)->unique()->values();
    }

    #[Computed]
    public function kantongOptions(): Collection
    {
        return Kantong::query()->orderBy('urutan')->orderBy('nama')->get();
    }

    #[Computed]
    public function riwayat(): Collection
    {
        if ($this->transaksiId === null) {
            return collect();
        }

        return Transaksi::query()->find($this->transaksiId)?->riwayat()->limit(20)->get() ?? collect();
    }

    #[Computed]
    public function transaksi(): ?Transaksi
    {
        return $this->transaksiId !== null ? Transaksi::query()->find($this->transaksiId) : null;
    }
};
?>

<div>
    <form wire:submit="simpan" class="space-y-5" novalidate>
        {{-- Amount is the first decision: big, tabular, and always plain digits underneath. --}}
        <section class="paper p-5 sm:p-6">
            <div class="flex items-center justify-between gap-3">
                <label for="jumlah" class="label">Jumlah</label>
                <span class="label text-muted">Digit polos · Rupiah</span>
            </div>

            <div class="mt-2 flex items-baseline gap-2">
                <span class="font-mono text-xl text-muted sm:text-2xl">Rp</span>
                <input
                    id="jumlah"
                    type="text"
                    inputmode="numeric"
                    autocomplete="off"
                    wire:model="jumlah"
                    x-data
                    x-on:blur="$el.value = window.formatRupiahValue($el.value); $el.dispatchEvent(new Event('input', { bubbles: true }))"
                    class="amount min-w-0 flex-1 border-0 bg-transparent p-0 text-[clamp(2rem,10vw,3.5rem)] font-semibold tracking-[-0.03em] text-ink outline-none placeholder:text-line-strong focus:ring-0"
                    placeholder="0"
                    autofocus
                >
            </div>

            @error('jumlah')
                <p class="mt-2 text-sm font-semibold text-tomato">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-muted">Contoh: 35.000, 35rb, 1,5jt. Tersimpan sebagai angka polos.</p>
        </section>

        <section class="paper p-5 sm:p-6">
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <span class="label">Jenis</span>
                    <div class="mt-2 grid grid-cols-2 gap-2 rounded-xl bg-canvas p-1">
                        <label class="jenis-option {{ $jenis === 'keluar' ? 'jenis-option--aktif jenis-option--keluar' : '' }}">
                            <input type="radio" value="keluar" wire:model.live="jenis" class="sr-only">
                            <span class="flex items-center justify-center gap-2 py-2 text-sm font-bold">
                                {!! \App\Support\Icon::svg('arrow-up-right', 'size-4') !!} Pengeluaran
                            </span>
                        </label>
                        <label class="jenis-option {{ $jenis === 'masuk' ? 'jenis-option--aktif jenis-option--masuk' : '' }}">
                            <input type="radio" value="masuk" wire:model.live="jenis" class="sr-only">
                            <span class="flex items-center justify-center gap-2 py-2 text-sm font-bold">
                                {!! \App\Support\Icon::svg('arrow-down-left', 'size-4') !!} Pemasukan
                            </span>
                        </label>
                    </div>
                    @error('jenis') <p class="mt-1.5 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="tanggal" class="label">Tanggal</label>
                    <input id="tanggal" type="date" wire:model="tanggal" class="input mt-2">
                    @error('tanggal') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="kategori" class="label">Kategori</label>
                    <input
                        id="kategori"
                        type="text"
                        wire:model.blur="kategori"
                        list="kategori-options"
                        maxlength="50"
                        autocomplete="off"
                        class="input mt-2"
                        placeholder="Makanan, Tagihan, Mutasi…"
                    >
                    <datalist id="kategori-options">
                        @foreach($this->kategoriOptions as $opsi)
                            <option value="{{ $opsi }}"></option>
                        @endforeach
                    </datalist>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach($this->kategoriOptions->take(6) as $opsi)
                            <button type="button" wire:click="$set('kategori', @js($opsi))" class="chip">
                                {{ $opsi }}
                            </button>
                        @endforeach
                        <button type="button" wire:click="$set('kategori', 'Mutasi')" class="chip chip--mutasi">
                            {!! \App\Support\Icon::svg('repeat', 'size-3.5') !!} Mutasi
                        </button>
                    </div>
                    @error('kategori') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="metode" class="label">
                        {{ $this->mutasi ? 'Kantong asal' : 'Kantong' }}
                    </label>
                    <select id="metode" wire:model="metode" class="input mt-2">
                        <option value="">Belum dipilih</option>
                        @foreach($this->kantongOptions as $kantong)
                            <option value="{{ $kantong->nama }}">{{ $kantong->nama }}</option>
                        @endforeach
                    </select>
                    @error('metode') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                </div>

                @if($this->mutasi)
                    <div>
                        <label for="kantongTujuan" class="label">Kantong tujuan</label>
                        <select id="kantongTujuan" wire:model="kantongTujuan" class="input mt-2">
                            <option value="">Pilih tujuan</option>
                            @foreach($this->kantongOptions as $kantong)
                                <option value="{{ $kantong->nama }}">{{ $kantong->nama }}</option>
                            @endforeach
                        </select>
                        @error('kantongTujuan') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <p class="flex items-start gap-2 rounded-xl bg-ochre-soft px-3 py-2.5 text-xs leading-relaxed text-ink">
                            {!! \App\Support\Icon::svg('info', 'mt-0.5 size-4 shrink-0 text-ochre') !!}
                            <span>Mutasi tidak dihitung sebagai belanja. Saldo kantong asal berkurang, saldo tujuan bertambah.</span>
                        </p>
                    </div>
                @endif

                <div class="sm:col-span-2">
                    <label for="keterangan" class="label">Keterangan <span class="normal-case tracking-normal text-muted">(opsional)</span></label>
                    <textarea id="keterangan" wire:model="keterangan" rows="2" maxlength="2000" class="input mt-2 resize-y" placeholder="Misalnya: belanja mingguan di pasar"></textarea>
                    @error('keterangan') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2 flex items-center justify-between gap-3 rounded-xl bg-canvas px-3 py-2.5">
                    <span class="text-xs text-muted">Tercatat sebagai</span>
                    <span class="flex items-center gap-2 text-sm font-bold">
                        <span class="grid size-7 place-items-center rounded-full bg-olive text-[10px] font-bold text-paper">{{ inisial($pencatat) }}</span>
                        {{ $pencatat }}
                        <span class="label !text-[9px]">UI</span>
                    </span>
                </div>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <a href="{{ $transaksiId ? route('transaksi.index') : route('beranda') }}" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="simpan">
                <span wire:loading.remove wire:target="simpan">{!! \App\Support\Icon::svg('pin', 'size-4') !!} {{ $transaksiId ? 'Simpan perubahan' : 'Tempel transaksi' }}</span>
                <span wire:loading wire:target="simpan">Menyimpan…</span>
            </button>
        </div>
    </form>

    @if($this->transaksi?->riwayat->isNotEmpty())
        <section class="mt-8">
            <h2 class="text-lg font-semibold">Riwayat perubahan</h2>
            <p class="mt-1 text-sm text-muted">Siapa mengubah apa, dan kapan. Server menetapkan waktunya.</p>
            <ol class="mt-4 space-y-0 border-l border-line">
                @foreach($this->riwayat as $item)
                    <li class="relative pb-4 pl-5 last:pb-0">
                        <span class="absolute -left-[4.5px] top-1.5 size-2 rounded-full bg-olive"></span>
                        <p class="text-sm font-semibold">
                            {{ ucfirst($item->aksi) }} oleh {{ $item->oleh ?? 'Hermes' }}
                            <span class="font-normal text-muted">· {{ $item->sumber }}</span>
                        </p>
                        <p class="text-xs text-muted">{{ tanggal_id($item->created_at) }}, {{ $item->created_at->format('H:i') }} WIB</p>
                        @if($item->aksi === 'diubah' && $item->sebelum && $item->sesudah)
                            @php
                                $labelField = [
                                    'tanggal' => 'Tanggal',
                                    'jenis' => 'Jenis',
                                    'kategori' => 'Kategori',
                                    'jumlah' => 'Jumlah',
                                    'keterangan' => 'Keterangan',
                                    'pencatat' => 'Pencatat',
                                    'metode' => 'Kantong',
                                    'kantong_tujuan' => 'Kantong tujuan',
                                ];
                                $perubahan = collect($labelField)->filter(fn ($label, $field) => ($item->sebelum[$field] ?? null) !== ($item->sesudah[$field] ?? null));
                                $tampilkan = function (string $field, mixed $nilai): string {
                                    if ($nilai === null || $nilai === '') {
                                        return '—';
                                    }
                                    if ($field === 'jumlah') {
                                        return rupiah($nilai);
                                    }
                                    if ($field === 'tanggal') {
                                        return tanggal_id($nilai);
                                    }
                                    if ($field === 'jenis') {
                                        return $nilai === 'masuk' ? 'Masuk' : 'Keluar';
                                    }
                                    return (string) $nilai;
                                };
                            @endphp
                            @if($perubahan->isNotEmpty())
                                <ul class="mt-1.5 space-y-0.5 text-xs text-muted">
                                    @foreach($perubahan as $field => $label)
                                        <li>
                                            <span class="font-semibold text-ink">{{ $label }}</span>:
                                            {{ $tampilkan($field, $item->sebelum[$field] ?? null) }}
                                            → {{ $tampilkan($field, $item->sesudah[$field] ?? null) }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        @endif
                    </li>
                @endforeach
            </ol>
        </section>
    @endif
</div>
