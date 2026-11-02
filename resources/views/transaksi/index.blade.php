@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
    @php
        $query = request()->query();
        $adaFilter = $filter['semua'] || $filter['jenis'] || $filter['kategori'] || $filter['pencatat'] || $filter['metode'];
        $bulanLalu = now()->subMonthNoOverflow();
        $tautanHapus = fn (array $kunci) => route('transaksi.index', collect($query)->except($kunci)->all());
    @endphp

    <div class="space-y-5">
        <header class="page-head">
            <div>
                <h1 class="page-title">Transaksi</h1>
                <p class="page-sub">{{ $labelFilter }} · {{ number_format($transaksi->total(), 0, ',', '.') }} baris. Mutasi dipisahkan dari belanja.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('transaksi.ekspor', $query) }}" class="btn btn-ghost btn-sm">
                    <x-icon name="download" class="size-4" />
                    Ekspor CSV
                </a>
                <a href="{{ route('transaksi.create') }}" class="btn btn-primary btn-sm">
                    <x-icon name="plus" class="size-4" />
                    Tambah
                </a>
            </div>
        </header>

        <section class="paper p-3 sm:p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex flex-wrap gap-1.5">
                    <a href="{{ route('transaksi.index') }}" class="chip {{ ! $adaFilter ? 'chip-aktif' : '' }}">Bulan ini</a>
                    <a href="{{ route('transaksi.index', ['dari' => $bulanLalu->copy()->startOfMonth()->toDateString(), 'sampai' => $bulanLalu->copy()->endOfMonth()->toDateString()]) }}" class="chip">Bulan lalu</a>
                    <a href="{{ route('transaksi.index', ['semua' => 1]) }}" class="chip {{ $filter['semua'] ? 'chip-aktif' : '' }}">Semua waktu</a>
                </div>
                <span class="hidden text-[11px] text-muted sm:block">Urut tanggal terbaru</span>
            </div>

            <details class="group mt-3 border-t border-line pt-3" @if($adaFilter) open @endif>
                <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-3 rounded-lg px-1 text-sm font-bold">
                    <span class="flex items-center gap-2">
                        <x-icon name="filter" class="size-4 text-muted" />
                        Filter
                        @if($adaFilter)
                            <span class="tag tag--netral">Aktif</span>
                        @endif
                    </span>
                    <x-icon name="chevron-down" class="size-4 text-muted transition-transform group-open:rotate-180" />
                </summary>

                <form method="GET" action="{{ route('transaksi.index') }}" class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                    <div>
                        <label for="dari" class="label">Dari tanggal</label>
                        <input id="dari" name="dari" type="date" value="{{ $filter['dari'] }}" class="input mt-1.5">
                    </div>
                    <div>
                        <label for="sampai" class="label">Sampai tanggal</label>
                        <input id="sampai" name="sampai" type="date" value="{{ $filter['sampai'] }}" class="input mt-1.5">
                    </div>
                    <div>
                        <label for="jenis" class="label">Jenis</label>
                        <select id="jenis" name="jenis" class="input mt-1.5">
                            <option value="">Semua jenis</option>
                            <option value="keluar" @selected($filter['jenis'] === 'keluar')>Pengeluaran</option>
                            <option value="masuk" @selected($filter['jenis'] === 'masuk')>Pemasukan</option>
                        </select>
                    </div>
                    <div>
                        <label for="kategori" class="label">Kategori</label>
                        <select id="kategori" name="kategori" class="input mt-1.5">
                            <option value="">Semua kategori</option>
                            @foreach($kategori as $item)
                                <option value="{{ $item }}" @selected($filter['kategori'] === $item)>{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="pencatat" class="label">Pencatat</label>
                        <select id="pencatat" name="pencatat" class="input mt-1.5">
                            <option value="">Semua</option>
                            @foreach($pencatat as $item)
                                <option value="{{ $item }}" @selected($filter['pencatat'] === $item)>{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="metode" class="label">Kantong</label>
                        <select id="metode" name="metode" class="input mt-1.5">
                            <option value="">Semua kantong</option>
                            @foreach($kantong as $item)
                                <option value="{{ $item->nama }}" @selected($filter['metode'] === $item->nama)>{{ $item->nama }}</option>
                            @endforeach
                            <option value="__none__" @selected($filter['metode'] === '__none__')>Belum dipilih</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-6">
                        <button type="submit" class="btn btn-primary btn-sm">Terapkan filter</button>
                        @if($adaFilter)
                            <a href="{{ route('transaksi.index') }}" class="btn btn-ghost btn-sm">Bersihkan</a>
                        @endif
                    </div>
                </form>
            </details>

            @if($adaFilter)
                <div class="mt-3 flex flex-wrap items-center gap-1.5 border-t border-line pt-3">
                    @if($filter['jenis'])
                        <a href="{{ $tautanHapus(['jenis']) }}" class="chip">Jenis: {{ $filter['jenis'] === 'masuk' ? 'Pemasukan' : 'Pengeluaran' }} <span class="text-muted">×</span></a>
                    @endif
                    @if($filter['kategori'])
                        <a href="{{ $tautanHapus(['kategori']) }}" class="chip">Kategori: {{ $filter['kategori'] }} <span class="text-muted">×</span></a>
                    @endif
                    @if($filter['pencatat'])
                        <a href="{{ $tautanHapus(['pencatat']) }}" class="chip">Pencatat: {{ $filter['pencatat'] }} <span class="text-muted">×</span></a>
                    @endif
                    @if($filter['metode'])
                        <a href="{{ $tautanHapus(['metode']) }}" class="chip">Kantong: {{ $filter['metode'] === '__none__' ? 'Belum dipilih' : $filter['metode'] }} <span class="text-muted">×</span></a>
                    @endif
                    @if($filter['dari'] || $filter['sampai'])
                        <a href="{{ $tautanHapus(['dari', 'sampai']) }}" class="chip">Tanggal: {{ $labelFilter }} <span class="text-muted">×</span></a>
                    @endif
                    @if($filter['semua'])
                        <a href="{{ $tautanHapus(['semua']) }}" class="chip">Semua waktu <span class="text-muted">×</span></a>
                    @endif
                </div>
            @endif
        </section>

        <section class="grid grid-cols-2 divide-line rounded-[14px] border border-line bg-paper sm:grid-cols-4 sm:divide-x">
            <div class="border-b border-line px-4 py-3 sm:border-b-0">
                <p class="label">Masuk</p>
                <p class="amount mt-1 text-sm text-sage">{{ rupiah($ringkasan['masuk']) }}</p>
            </div>
            <div class="border-b border-line px-4 py-3 sm:border-b-0">
                <p class="label">Belanja</p>
                <p class="amount mt-1 text-sm">{{ rupiah($ringkasan['keluar']) }}</p>
            </div>
            <div class="border-b border-line px-4 py-3 sm:border-b-0">
                <p class="label">Mutasi</p>
                <p class="amount mt-1 text-sm text-ochre">{{ rupiah($ringkasan['mutasi']) }}</p>
            </div>
            <div class="px-4 py-3">
                <p class="label">Selisih</p>
                <p class="amount mt-1 text-sm">{{ rupiah($ringkasan['selisih']) }}</p>
            </div>
        </section>

        <section class="paper overflow-hidden">
            @if($transaksi->isEmpty())
                <div class="empty">
                    <span class="empty__icon"><x-icon name="receipt" class="size-6" /></span>
                    <h2 class="mt-4 font-display text-xl font-semibold">Tidak ada transaksi di filter ini</h2>
                    <p class="mt-1 max-w-[38ch] text-sm text-muted">Coba hapus satu filter, atau tempel transaksi baru ke papan.</p>
                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        <a href="{{ route('transaksi.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="size-4" /> Tambah transaksi</a>
                        @if($adaFilter)
                            <a href="{{ route('transaksi.index') }}" class="btn btn-ghost btn-sm">Bersihkan filter</a>
                        @endif
                    </div>
                </div>
            @else
                @foreach($transaksi->groupBy(fn ($t) => $t->tanggal->toDateString()) as $tanggal => $kelompok)
                    <div class="flex gap-3 border-b border-line last:border-b-0">
                        <div class="w-12 shrink-0 border-r border-line/70 px-1 py-3 text-center">
                            <p class="amount text-lg leading-none">{{ $kelompok->first()->tanggal->day }}</p>
                            <p class="mt-1 font-mono text-[9px] uppercase tracking-[0.08em] text-muted">{{ bulan_singkat($kelompok->first()->tanggal) }}</p>
                            <p class="mt-0.5 text-[9px] text-muted">{{ substr(hari_id($kelompok->first()->tanggal), 0, 3) }}</p>
                        </div>
                        <div class="min-w-0 flex-1">
                            @foreach($kelompok as $t)
                                @php
                                    $meta = $kategoriMeta[$t->kategori] ?? null;
                                    $warna = $meta->warna ?? '#5F7F8E';
                                    $ikon = $meta->ikon ?? \App\Support\Icon::untukKategori($t->kategori);
                                @endphp
                                <a href="{{ route('transaksi.edit', $t) }}" class="slip {{ session('transaksi_baru_id') === $t->id ? 'slip--baru' : '' }}">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-lg" style="background: {{ $warna }}1f; color: {{ $warna }}">
                                        <x-icon :name="$ikon" class="size-4" />
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-center gap-1.5">
                                            <span class="truncate text-sm font-bold">{{ $t->kategori }}</span>
                                            @if($t->is_mutasi)
                                                <span class="tag tag--mutasi">Mutasi</span>
                                            @endif
                                            @if($t->sumber === 'telegram')
                                                <span class="tag tag--netral">Telegram</span>
                                            @endif
                                        </span>
                                        <span class="mt-0.5 block truncate text-[11px] text-muted">
                                            @if($t->is_mutasi)
                                                <span class="font-semibold text-ochre">{{ $t->metode ?: 'Tanpa asal' }} → {{ $t->kantong_tujuan ?: 'Tanpa tujuan' }}</span>
                                                · {{ $t->keterangan ?: 'Tanpa keterangan' }}
                                            @else
                                                {{ $t->keterangan ?: 'Tanpa keterangan' }}
                                                · {{ $t->pencatat }}
                                                @if($t->metode) · {{ $t->metode }} @else · belum ada kantong @endif
                                            @endif
                                        </span>
                                    </span>
                                    <span class="shrink-0 text-right">
                                        <span class="amount block text-sm {{ $t->is_mutasi ? 'text-ochre' : ($t->jenis === 'masuk' ? 'text-sage' : '') }}">{{ $t->jenis === 'masuk' ? '+' : '−' }}{{ rupiah($t->jumlah) }}</span>
                                        <span class="mt-0.5 block text-[9px] text-muted">{{ $t->updated_at?->format('H:i') }} WIB</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </section>

        @if($transaksi->hasPages())
            {{ $transaksi->links('pagination.dompet') }}
        @endif
    </div>
@endsection
