@extends('layouts.app')

@section('title', 'Anggaran')

@section('content')
    @php
        $labelStatus = ['aman' => 'Aman', 'hampir' => 'Hampir habis', 'lewat' => 'Lewat'];
        $statusKelas = ['aman' => 'tag--masuk', 'hampir' => 'bg-ochre-soft text-ochre', 'lewat' => 'tag--keluar'];
        $total = $hasil['total'];
    @endphp

    <div class="space-y-5">
        <header class="page-head">
            <div>
                <h1 class="page-title">Anggaran</h1>
                <p class="page-sub">Batas belanja per kategori. Mutasi dan pemasukan tidak dihitung. Ambang peringatan bawaan 80% dan 100%.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center rounded-xl border border-line bg-paper p-0.5">
                    <a href="{{ route('anggaran.index', ['periode' => $bulanSebelum]) }}" class="grid size-11 place-items-center rounded-lg text-muted hover:bg-olive-soft hover:text-ink" aria-label="Bulan sebelumnya"><x-icon name="chevron-left" class="size-4" /></a>
                    <span class="px-2 font-mono text-[10px] uppercase tracking-[0.12em]">{{ bulan_id($periode) }}</span>
                    @if($bulanBerjalan)
                        <span class="grid size-9 place-items-center text-line"><x-icon name="pin" class="size-4" /></span>
                    @else
                        <a href="{{ route('anggaran.index', ['periode' => $bulanBerikut]) }}" class="grid size-11 place-items-center rounded-lg text-muted hover:bg-olive-soft hover:text-ink" aria-label="Bulan berikutnya"><x-icon name="chevron-right" class="size-4" /></a>
                    @endif
                </div>
                @if($hasil['dipinjam'])
                    <form method="POST" action="{{ route('anggaran.salin') }}">
                        @csrf
                        <input type="hidden" name="periode" value="{{ $periode }}">
                        <button type="submit" class="btn btn-ghost btn-sm"><x-icon name="download" class="size-4" /> Salin bulan lalu</button>
                    </form>
                @endif
            </div>
        </header>

        @if($hasil['dipinjam'])
            <div class="flex items-start gap-2 rounded-xl border border-butter/60 bg-butter-soft px-4 py-3 text-sm text-ink">
                <x-icon name="info" class="mt-0.5 size-5 shrink-0 text-ochre" />
                <p>Belum ada anggaran untuk {{ bulan_id($periode) }}. Angka di bawah diteruskan dari <span class="font-semibold">{{ bulan_id($hasil['periode_sebelumnya']) }}</span> sampai Anda menyimpannya.</p>
            </div>
        @endif

        <section class="paper-raised p-5 sm:p-6">
            <div class="flex flex-wrap items-end justify-between gap-4 border-b border-line pb-4">
                <div>
                    <h2 class="font-display text-lg font-semibold">Total bulanan</h2>
                    <p class="mt-1 text-xs text-muted">Belanja non-Mutasi · {{ $total['persen'] }}% terpakai</p>
                </div>
                <div class="text-right">
                    <p class="amount text-2xl">{{ rupiah($total['terpakai']) }}</p>
                    <p class="text-xs text-muted">dari {{ rupiah($total['anggaran']) }}</p>
                </div>
            </div>
            <div class="mt-4 progress h-3">
                <div class="progress__bar {{ $total['status'] === 'lewat' ? 'progress__bar--lewat' : ($total['status'] === 'hampir' ? 'progress__bar--hampir' : '') }}" style="--progress: {{ min(100, $total['persen']) / 100 }}"></div>
            </div>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs">
                <span class="tag {{ $statusKelas[$total['status']] }}">{{ $labelStatus[$total['status']] }}</span>
                <span class="{{ $total['sisa'] < 0 ? 'text-tomato font-semibold' : 'text-muted' }}">{{ $total['sisa'] >= 0 ? 'Sisa '.rupiah($total['sisa']) : 'Lewat '.rupiah(abs($total['sisa'])) }}</span>
            </div>

            <details class="group mt-4 border-t border-line pt-4">
                <summary class="flex min-h-10 cursor-pointer list-none items-center justify-between gap-3 text-sm font-bold">
                    <span class="flex items-center gap-2"><x-icon name="edit" class="size-4 text-muted" /> Ubah total</span>
                    <x-icon name="chevron-down" class="size-4 text-muted transition-transform group-open:rotate-180" />
                </summary>
                <form method="POST" action="{{ route('anggaran.store') }}" class="mt-3 grid gap-3 sm:grid-cols-[1fr_110px_1fr_auto]">
                    @csrf
                    <input type="hidden" name="periode" value="{{ $periode }}">
                    <input type="hidden" name="kategori" value="__total__">
                    <div>
                        <label for="total-jumlah" class="label">Batas total</label>
                        <input id="total-jumlah" name="jumlah" type="text" inputmode="numeric" value="{{ angka_input($total['anggaran']) }}" onblur="this.value = window.formatRupiahValue(this.value)" class="input mt-1.5" required>
                    </div>
                    <div>
                        <label for="total-ambang" class="label">Ambang</label>
                        <input id="total-ambang" name="ambang" type="number" min="1" max="100" value="{{ $total['ambang'] }}" class="input mt-1.5">
                        @error('ambang') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                    </div>
                    <div class="hidden sm:block"></div>
                    <div class="flex items-end">
                        <button type="submit" class="btn btn-primary btn-sm h-11 w-full">Simpan</button>
                    </div>
                </form>
            </details>
        </section>

        <section class="paper overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b border-line px-4 py-3.5 sm:px-5">
                <div>
                    <h2 class="font-display text-base font-semibold">Per kategori</h2>
                    <p class="mt-0.5 text-[11px] text-muted">Terpakai dihitung dari transaksi keluar non-Mutasi.</p>
                </div>
                <span class="tag tag--netral">{{ $hasil['rows']->count() }} kategori</span>
            </div>

            @if($hasil['rows']->isEmpty())
                <div class="empty">
                    <span class="empty__icon"><x-icon name="target" class="size-6" /></span>
                    <h3 class="mt-4 font-display text-lg font-semibold">Belum ada anggaran kategori</h3>
                    <p class="mt-1 max-w-[36ch] text-sm text-muted">Mulai dari kategori yang paling sering dipakai, misalnya Makanan atau Tagihan.</p>
                </div>
            @else
                <div class="divide-y divide-line">
                    @foreach($hasil['rows'] as $row)
                        <details class="group">
                            <summary class="flex cursor-pointer list-none items-center gap-3 px-4 py-3 hover:bg-olive-soft/40 sm:px-5">
                                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-canvas text-ink"><x-icon name="target" class="size-4" /></span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center justify-between gap-3">
                                        <span class="truncate text-sm font-bold">{{ $row['kategori'] }}</span>
                                        <span class="amount shrink-0 text-xs">{{ rupiah($row['terpakai']) }} <span class="font-sans text-[10px] font-normal text-muted">/ {{ rupiah($row['anggaran']) }}</span></span>
                                    </span>
                                    <span class="progress mt-2 block h-1.5"><span class="progress__bar {{ $row['status'] === 'lewat' ? 'progress__bar--lewat' : ($row['status'] === 'hampir' ? 'progress__bar--hampir' : '') }}" style="--progress: {{ min(100, $row['persen']) / 100 }}; display:block"></span></span>
                                    <span class="mt-1.5 flex items-center justify-between gap-2 text-[10px] text-muted">
                                        <span class="tag {{ $statusKelas[$row['status']] }}">{{ $labelStatus[$row['status']] }} · {{ $row['persen'] }}%</span>
                                        <span>{{ $row['sisa'] >= 0 ? 'Sisa '.rupiah($row['sisa']) : 'Lewat '.rupiah(abs($row['sisa'])) }}</span>
                                    </span>
                                </span>
                                <x-icon name="chevron-down" class="size-4 shrink-0 text-muted transition-transform group-open:rotate-180" />
                            </summary>
                            <div class="border-t border-line/70 bg-canvas/50 px-4 py-3 sm:px-5">
                                <form method="POST" action="{{ route('anggaran.store') }}" class="grid gap-3 sm:grid-cols-[1fr_110px_auto]">
                                    @csrf
                                    <input type="hidden" name="periode" value="{{ $periode }}">
                                    <input type="hidden" name="kategori" value="{{ $row['kategori'] }}">
                                    <div>
                                        <label for="jumlah-{{ $loop->index }}" class="label">Batas kategori</label>
                                        <input id="jumlah-{{ $loop->index }}" name="jumlah" type="text" inputmode="numeric" value="{{ angka_input($row['anggaran']) }}" onblur="this.value = window.formatRupiahValue(this.value)" class="input mt-1.5" required>
                                    </div>
                                    <div>
                                        <label for="ambang-{{ $loop->index }}" class="label">Ambang</label>
                                        <input id="ambang-{{ $loop->index }}" name="ambang" type="number" min="1" max="100" value="{{ $row['ambang'] }}" class="input mt-1.5">
                                    </div>
                                    <div class="flex items-end gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm h-11">Simpan</button>
                                    </div>
                                </form>
                                @if($row['id'])
                                    <div class="mt-2 flex justify-end">
                                        <x-hapus-form
                                            :action="route('anggaran.destroy', $row['id'])"
                                            title="Hapus anggaran {{ $row['kategori'] }}?"
                                            description="Anggaran dihapus lunak. Transaksi dan kategori tetap ada; anggaran bisa dibuat lagi kapan saja."
                                            label="Hapus anggaran"
                                            confirm="Ya, hapus anggaran"
                                            buttonClass="btn btn-danger btn-sm"
                                        />
                                    </div>
                                @endif
                            </div>
                        </details>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="paper p-4 sm:p-5">
            <h2 class="font-display text-lg font-semibold">Tambah atau perbarui anggaran</h2>
            <p class="mt-1 text-sm text-muted">Pilih kategori atau total bulanan. Menyimpan kategori yang sudah ada akan memperbarui angkanya.</p>
            <form method="POST" action="{{ route('anggaran.store') }}" class="mt-4 grid gap-3 sm:grid-cols-[1fr_180px_110px_auto]">
                @csrf
                <input type="hidden" name="periode" value="{{ $periode }}">
                <div>
                    <label for="kategori" class="label">Kategori</label>
                    <select id="kategori" name="kategori" class="input mt-1.5">
                        <option value="__total__">Total bulanan</option>
                        @foreach($kategori as $item)
                            <option value="{{ $item }}">{{ $item }}</option>
                        @endforeach
                    </select>
                    @error('kategori') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="jumlah" class="label">Batas</label>
                    <input id="jumlah" name="jumlah" type="text" inputmode="numeric" placeholder="1.000.000" onblur="this.value = window.formatRupiahValue(this.value)" class="input mt-1.5" required>
                    @error('jumlah') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="ambang" class="label">Ambang %</label>
                    <input id="ambang" name="ambang" type="number" min="1" max="100" value="80" class="input mt-1.5">
                    @error('ambang') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn btn-primary btn-sm h-11 w-full">Simpan anggaran</button>
                </div>
            </form>
            @error('periode') <p class="mt-2 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
        </section>
    </div>
@endsection
