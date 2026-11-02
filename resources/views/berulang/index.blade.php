@extends('layouts.app')

@section('title', 'Berulang')

@section('content')
    @php
        $labelAturan = ['bulanan' => 'Bulanan', 'mingguan' => 'Mingguan', 'tahunan' => 'Tahunan'];
        $labelStatus = ['terlambat' => 'Terlambat', 'hari-ini' => 'Hari ini', 'segera' => 'Segera', 'akan-datang' => 'Akan datang', 'nonaktif' => 'Nonaktif'];
        $kelasStatus = ['terlambat' => 'tag--keluar', 'hari-ini' => 'bg-ochre-soft text-ochre', 'segera' => 'bg-sky-soft text-sky', 'akan-datang' => 'tag--netral', 'nonaktif' => 'bg-canvas text-muted'];
    @endphp

    <div class="space-y-5">
        <header class="page-head">
            <div>
                <h1 class="page-title">Berulang</h1>
                <p class="page-sub">Jadwal tagihan dan transaksi rutin. Tidak ada baris yang dibuat otomatis: konfirmasi dulu, baru transaksi ditempel.</p>
            </div>
            <a href="{{ route('berulang.create') }}" class="btn btn-primary btn-sm self-start">
                <x-icon name="plus" class="size-4" />
                Tambah jadwal
            </a>
        </header>

        <section class="grid grid-cols-3 divide-x divide-line rounded-[14px] border border-line bg-paper">
            <div class="px-4 py-3">
                <p class="label">Jadwal aktif</p>
                <p class="amount mt-1 text-lg">{{ $jadwal->count() }}</p>
            </div>
            <div class="px-4 py-3">
                <p class="label">Jatuh tempo</p>
                <p class="amount mt-1 text-lg text-ochre">{{ $jatuhTempo->count() }}</p>
            </div>
            <div class="px-4 py-3">
                <p class="label">Perlu konfirmasi</p>
                <p class="amount mt-1 text-lg text-tomato">{{ $jatuhTempo->whereIn('status', ['terlambat', 'hari-ini'])->count() }}</p>
            </div>
        </section>

        @if($jadwal->isEmpty())
            <div class="paper empty">
                <span class="empty__icon"><x-icon name="repeat" class="size-6" /></span>
                <h2 class="mt-4 font-display text-xl font-semibold">Belum ada jadwal berulang</h2>
                <p class="mt-1 max-w-[40ch] text-sm text-muted">Tambahkan listrik, internet, atau SPP supaya jatuh temponya muncul di papan.</p>
                <a href="{{ route('berulang.create') }}" class="btn btn-primary btn-sm mt-4"><x-icon name="plus" class="size-4" /> Tambah jadwal</a>
            </div>
        @else
            <section class="paper overflow-hidden">
                <div class="border-b border-line px-4 py-3.5 sm:px-5">
                    <h2 class="font-display text-base font-semibold">Jadwal</h2>
                    <p class="mt-0.5 text-[11px] text-muted">Konfirmasi membuat transaksi dengan kunci idempoten per tanggal jatuh tempo.</p>
                </div>

                <div class="divide-y divide-line">
                    @foreach($jadwal as $item)
                        <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:px-5" x-data="{ konfirmasi: false }">
                            <div class="flex min-w-0 flex-1 items-start gap-3">
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-canvas text-muted">
                                    <x-icon name="calendar" class="size-4" />
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="truncate text-sm font-bold">{{ $item->nama }}</h3>
                                        <span class="tag {{ $kelasStatus[$item->status] }}">{{ $labelStatus[$item->status] }}</span>
                                    </div>
                                    <p class="mt-1 text-[11px] text-muted">
                                        {{ $item->kategori }} · {{ $labelAturan[$item->aturan] }} · jatuh tempo {{ tanggal_id($item->jatuh_tempo) }}
                                        @if($item->metode) · {{ $item->metode }} @endif
                                        · {{ $item->pencatat }}
                                    </p>
                                    @if($item->terakhir_dikonfirmasi)
                                        <p class="mt-0.5 text-[10px] text-muted">Terakhir dikonfirmasi {{ tanggal_id($item->terakhir_dikonfirmasi) }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                <p class="amount mr-auto text-base sm:mr-3">{{ rupiah($item->jumlah) }}</p>
                                <button type="button" x-on:click="konfirmasi = true" class="btn btn-primary btn-sm" @disabled(! $item->aktif)>
                                    <x-icon name="check" class="size-4" />
                                    Konfirmasi
                                </button>
                                <a href="{{ route('berulang.edit', $item) }}" class="btn btn-ghost btn-sm" aria-label="Ubah {{ $item->nama }}"><x-icon name="edit" class="size-4" /></a>
                            </div>

                            <div x-cloak x-show="konfirmasi" x-transition.opacity.duration.200ms class="fixed inset-0 z-[80] flex items-end justify-center bg-black/45 p-3 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="konfirmasi-{{ $item->id }}">
                                <div x-on:click.outside="konfirmasi = false" class="w-full max-w-md rounded-2xl border border-line bg-paper-raised p-5 shadow-2xl">
                                    <div class="flex items-start gap-3">
                                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-sage-soft text-sage"><x-icon name="circle-check" class="size-5" /></span>
                                        <div>
                                            <h2 id="konfirmasi-{{ $item->id }}" class="font-display text-lg font-semibold">Konfirmasi {{ $item->nama }}</h2>
                                            <p class="mt-1 text-sm text-muted">Jatuh tempo {{ tanggal_id($item->jatuh_tempo) }}. Jumlah boleh diubah sebelum disimpan.</p>
                                        </div>
                                    </div>
                                    <form method="POST" action="{{ route('berulang.konfirmasi', $item) }}" class="mt-4 space-y-3">
                                        @csrf
                                        <div>
                                            <label for="jumlah-{{ $item->id }}" class="label">Jumlah</label>
                                            <input id="jumlah-{{ $item->id }}" name="jumlah" type="text" inputmode="numeric" value="{{ angka_input($item->jumlah) }}" onblur="this.value = window.formatRupiahValue(this.value)" class="input mt-1.5">
                                            <input type="hidden" name="jatuh_tempo" value="{{ $item->jatuh_tempo->format('Y-m-d') }}">
                                        </div>
                                        <p class="text-[11px] leading-relaxed text-muted">Setelah dikonfirmasi, transaksi masuk ke papan dengan sumber UI. Konfirmasi ulang untuk jatuh tempo yang sama tidak membuat baris ganda.</p>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" x-on:click="konfirmasi = false" class="btn btn-ghost btn-sm">Batal</button>
                                            <button type="submit" class="btn btn-primary btn-sm">Konfirmasi & catat</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <p class="text-center text-[11px] text-muted">Jadwal yang dihapus bersifat soft delete; transaksi yang sudah dikonfirmasi tetap ada.</p>
    </div>
@endsection
